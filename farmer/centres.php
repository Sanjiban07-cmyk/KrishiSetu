<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* -----------------------------
   Get farmer information
----------------------------- */
$farmer = null;

$stmt = $conn->prepare("
    SELECT village, district, state, latitude, longitude
    FROM farmers
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$farmer = $result->fetch_assoc();

$stmt->close();


/* -----------------------------
   Filters
----------------------------- */
$district = trim($_GET['district'] ?? '');
$block = trim($_GET['block'] ?? '');
$search = trim($_GET['search'] ?? '');


/* -----------------------------
   Get procurement centres
----------------------------- */
$sql = "
    SELECT
        id,
        centre_name,
        centre_code,
        centre_type,
        agency_type,
        address,
        venue,
        purchase_officer,
        officer_mobile,
        purchase_date,
        slot_available,
        official_source,
        village,
        district,
        block,
        state,
        latitude,
        longitude,
        total_capacity,
        current_bookings,
        current_queue,
        status
    FROM procurement_centres
    WHERE status = 'active'
";

$params = [];
$types = "";

if ($district !== '') {
    $sql .= " AND district = ?";
    $params[] = $district;
    $types .= "s";
}

if ($block !== '') {
    $sql .= " AND block = ?";
    $params[] = $block;
    $types .= "s";
}

if ($search !== '') {
    $sql .= "
        AND (
            centre_name LIKE ?
            OR centre_code LIKE ?
            OR village LIKE ?
            OR block LIKE ?
        )
    ";

    $searchTerm = "%" . $search . "%";

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;

    $types .= "ssss";
}

$sql .= " ORDER BY current_queue ASC, current_bookings ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$centresResult = $stmt->get_result();

$centres = [];

while ($row = $centresResult->fetch_assoc()) {
    $centres[] = $row;
}

$stmt->close();


/* -----------------------------
   Calculate distance
----------------------------- */
function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    if (
        $lat1 === null ||
        $lon1 === null ||
        $lat2 === null ||
        $lon2 === null
    ) {
        return null;
    }

    $earthRadius = 6371;

    $lat1 = deg2rad((float)$lat1);
    $lat2 = deg2rad((float)$lat2);

    $latDiff = deg2rad((float)$lat2 - (float)$lat1);
    $lonDiff = deg2rad((float)$lon2 - (float)$lon1);

    $a = sin($latDiff / 2) * sin($latDiff / 2)
        + cos($lat1) * cos($lat2)
        * sin($lonDiff / 2) * sin($lonDiff / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}


/* -----------------------------
   Add distance to centres
----------------------------- */
$farmerLat = $farmer['latitude'] ?? null;
$farmerLon = $farmer['longitude'] ?? null;

foreach ($centres as &$centre) {

    $centre['distance'] = calculateDistance(
        $farmerLat,
        $farmerLon,
        $centre['latitude'],
        $centre['longitude']
    );

    $capacity = (int)$centre['total_capacity'];
    $bookings = (int)$centre['current_bookings'];

    if ($capacity > 0) {
        $centre['occupancy'] = round(($bookings / $capacity) * 100);
    } else {
        $centre['occupancy'] = 0;
    }
}

unset($centre);


/* -----------------------------
   Get unique districts/blocks
----------------------------- */
$filterResult = $conn->query("
    SELECT DISTINCT district, block
    FROM procurement_centres
    WHERE status = 'active'
    ORDER BY district, block
");

$districts = [];
$blocks = [];

while ($row = $filterResult->fetch_assoc()) {

    if (!empty($row['district'])) {
        $districts[] = $row['district'];
    }

    if (!empty($row['block'])) {
        $blocks[] = $row['block'];
    }
}

$districts = array_values(array_unique($districts));
$blocks = array_values(array_unique($blocks));


/* -----------------------------
   Helper functions
----------------------------- */
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function queueClass($queue)
{
    $queue = (int)$queue;

    if ($queue <= 10) {
        return "queue-good";
    }

    if ($queue <= 25) {
        return "queue-medium";
    }

    return "queue-high";
}

function queueText($queue)
{
    $queue = (int)$queue;

    if ($queue <= 10) {
        return "Low queue";
    }

    if ($queue <= 25) {
        return "Moderate queue";
    }

    return "High queue";
}

function formatDate($date)
{
    if (empty($date)) {
        return "Schedule not available";
    }

    return date("d M Y", strtotime($date));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Find Procurement Centres | KrishiSetu</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        body {
            margin: 0;
            background: #f7f9f7;
            color: #17352a;
            font-family: Arial, Helvetica, sans-serif;
        }

        .centre-header {
            background: #ffffff;
            border-bottom: 1px solid #dfe7e2;
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .centre-header-inner {
            max-width: 1180px;
            margin: auto;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #087443;
            font-weight: 800;
            font-size: 22px;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            background: #e8f5ee;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .portal-label {
            color: #61716a;
            font-size: 14px;
        }

        .back-btn {
            text-decoration: none;
            color: #087443;
            border: 1px solid #cfe0d6;
            padding: 9px 15px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            background: #ffffff;
        }

        .page {
            max-width: 1180px;
            margin: auto;
            padding: 35px 20px 60px;
        }

        .page-title {
            margin-bottom: 8px;
            font-size: 32px;
        }

        .page-subtitle {
            color: #61716a;
            margin-top: 0;
            line-height: 1.6;
        }

        .prototype-note {
            margin: 25px 0;
            background: #fff7e6;
            border: 1px solid #f1d79e;
            border-radius: 10px;
            padding: 14px 17px;
            color: #765614;
            font-size: 14px;
            line-height: 1.5;
        }

        .filters {
            background: #ffffff;
            border: 1px solid #dfe7e2;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 28px;
        }

        .filter-title {
            font-weight: 700;
            margin-bottom: 15px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1.5fr auto;
            gap: 12px;
        }

        .filter-group label {
            display: block;
            font-size: 13px;
            color: #61716a;
            margin-bottom: 6px;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px 12px;
            border: 1px solid #d3dfd8;
            border-radius: 8px;
            background: #ffffff;
            color: #17352a;
            outline: none;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: #087443;
        }

        .filter-btn {
            align-self: end;
            padding: 11px 20px;
            border: none;
            border-radius: 8px;
            background: #087443;
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        .filter-btn:hover {
            background: #055c35;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .results-count {
            color: #61716a;
            font-size: 14px;
        }

        .centre-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .centre-card {
            background: #ffffff;
            border: 1px solid #dfe7e2;
            border-radius: 14px;
            padding: 20px;
            transition: 0.2s ease;
        }

        .centre-card:hover {
            transform: translateY(-2px);
            border-color: #b7d1c2;
        }

        .centre-top {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
        }

        .centre-type {
            display: inline-block;
            background: #e8f5ee;
            color: #087443;
            padding: 6px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }

        .availability {
            font-size: 12px;
            font-weight: 700;
            padding: 6px 9px;
            border-radius: 6px;
        }

        .available {
            background: #e8f5ee;
            color: #16834d;
        }

        .unavailable {
            background: #fdecec;
            color: #c93434;
        }

        .centre-name {
            font-size: 19px;
            margin: 0 0 5px;
            line-height: 1.35;
        }

        .centre-code {
            color: #8a9892;
            font-size: 12px;
            margin-bottom: 17px;
        }

        .info-row {
            display: flex;
            gap: 10px;
            margin: 11px 0;
            font-size: 13px;
            line-height: 1.4;
        }

        .info-icon {
            width: 20px;
            flex-shrink: 0;
        }

        .info-label {
            color: #61716a;
        }

        .info-value {
            color: #17352a;
            font-weight: 600;
        }

        .queue-box {
            margin: 17px 0;
            padding: 13px;
            border-radius: 9px;
            background: #f7f9f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .queue-left {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .queue-label {
            color: #61716a;
            font-size: 12px;
        }

        .queue-number {
            font-size: 21px;
            font-weight: 800;
        }

        .queue-status {
            font-size: 12px;
            font-weight: 700;
        }

        .queue-good {
            color: #16834d;
        }

        .queue-medium {
            color: #d88900;
        }

        .queue-high {
            color: #c93434;
        }

        .capacity-bar {
            height: 6px;
            background: #e3ebe6;
            border-radius: 20px;
            overflow: hidden;
            margin-top: 7px;
        }

        .capacity-fill {
            height: 100%;
            background: #087443;
            border-radius: 20px;
        }

        .centre-action {
            width: 100%;
            display: block;
            text-align: center;
            text-decoration: none;
            box-sizing: border-box;
            padding: 11px;
            border-radius: 8px;
            background: #087443;
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
        }

        .centre-action:hover {
            background: #055c35;
        }

        .no-results {
            background: #ffffff;
            border: 1px solid #dfe7e2;
            border-radius: 14px;
            padding: 50px 20px;
            text-align: center;
            color: #61716a;
        }

        .no-results-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        @media (max-width: 900px) {

            .centre-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filter-grid {
                grid-template-columns: 1fr 1fr;
            }

        }

        @media (max-width: 600px) {

            .centre-header-inner {
                padding: 12px 15px;
            }

            .portal-label {
                display: none;
            }

            .page {
                padding: 25px 15px 45px;
            }

            .page-title {
                font-size: 26px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .centre-grid {
                grid-template-columns: 1fr;
            }

            .results-header {
                align-items: flex-start;
                flex-direction: column;
                gap: 5px;
            }

        }

    </style>

</head>

<body>

<header class="centre-header">

    <div class="centre-header-inner">

        <a href="dashboard.php" class="brand">

            <div class="brand-mark">
                🌾
            </div>

            <span>KrishiSetu</span>

        </a>

        <div class="header-right">

            <span class="portal-label">
                Farmer Portal
            </span>

            <a href="dashboard.php" class="back-btn">
                ← Dashboard
            </a>

        </div>

    </div>

</header>


<main class="page">

    <h1 class="page-title">
        Find Procurement Centres
    </h1>

    <p class="page-subtitle">
        Find nearby procurement centres, check queue conditions,
        capacity and purchase schedules before visiting.
    </p>


    <div class="prototype-note">

        <strong>Prototype Notice:</strong>
        The current centre records are demo data created for the
        KrishiSetu hackathon prototype. Production deployment will
        use authorised West Bengal e-Paddy data/API integration.

    </div>


    <!-- FILTERS -->

    <section class="filters">

        <div class="filter-title">
            Search Procurement Centres
        </div>

        <form method="GET">

            <div class="filter-grid">

                <div class="filter-group">

                    <label>District</label>

                    <select name="district">

                        <option value="">
                            All Districts
                        </option>

                        <?php foreach ($districts as $item): ?>

                            <option
                                value="<?= e($item) ?>"
                                <?= $district === $item ? 'selected' : '' ?>
                            >
                                <?= e($item) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="filter-group">

                    <label>Block</label>

                    <select name="block">

                        <option value="">
                            All Blocks
                        </option>

                        <?php foreach ($blocks as $item): ?>

                            <option
                                value="<?= e($item) ?>"
                                <?= $block === $item ? 'selected' : '' ?>
                            >
                                <?= e($item) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="filter-group">

                    <label>Search</label>

                    <input
                        type="text"
                        name="search"
                        placeholder="Centre name, code or village..."
                        value="<?= e($search) ?>"
                    >

                </div>


                <button
                    type="submit"
                    class="filter-btn"
                >
                    Search
                </button>

            </div>

        </form>

    </section>


    <!-- RESULTS -->

    <div class="results-header">

        <h2>
            Available Centres
        </h2>

        <div class="results-count">

            <?= count($centres) ?>
            centre<?= count($centres) !== 1 ? 's' : '' ?> found

        </div>

    </div>


    <?php if (empty($centres)): ?>

        <div class="no-results">

            <div class="no-results-icon">
                🔎
            </div>

            <h3>
                No procurement centres found
            </h3>

            <p>
                Try changing your district, block or search term.
            </p>

        </div>

    <?php else: ?>

        <div class="centre-grid">

            <?php foreach ($centres as $centre): ?>

                <article class="centre-card">

                    <div class="centre-top">

                        <span class="centre-type">

                            <?= e($centre['centre_type']) ?>

                        </span>

                        <?php if ((int)$centre['slot_available'] === 1): ?>

                            <span class="availability available">
                                ✓ Slots Available
                            </span>

                        <?php else: ?>

                            <span class="availability unavailable">
                                ✕ No Slots
                            </span>

                        <?php endif; ?>

                    </div>


                    <h3 class="centre-name">

                        <?= e($centre['centre_name']) ?>

                    </h3>


                    <div class="centre-code">

                        Centre Code:
                        <?= e($centre['centre_code']) ?>

                    </div>


                    <div class="info-row">

                        <div class="info-icon">📍</div>

                        <div>

                            <div class="info-label">
                                Location
                            </div>

                            <div class="info-value">

                                <?= e($centre['block']) ?>,
                                <?= e($centre['district']) ?>

                            </div>

                        </div>

                    </div>


                    <div class="info-row">

                        <div class="info-icon">🏢</div>

                        <div>

                            <div class="info-label">
                                Agency
                            </div>

                            <div class="info-value">

                                <?= e($centre['agency_type']) ?>

                            </div>

                        </div>

                    </div>


                    <div class="info-row">

                        <div class="info-icon">📅</div>

                        <div>

                            <div class="info-label">
                                Purchase Date
                            </div>

                            <div class="info-value">

                                <?= e(formatDate($centre['purchase_date'])) ?>

                            </div>

                        </div>

                    </div>


                    <?php if ($centre['distance'] !== null): ?>

                        <div class="info-row">

                            <div class="info-icon">🧭</div>

                            <div>

                                <div class="info-label">
                                    Distance
                                </div>

                                <div class="info-value">

                                    <?= number_format($centre['distance'], 1) ?>
                                    km

                                </div>

                            </div>

                        </div>

                    <?php else: ?>

                        <div class="info-row">

                            <div class="info-icon">🧭</div>

                            <div>

                                <div class="info-label">
                                    Location
                                </div>

                                <div class="info-value">
                                    Location data available
                                </div>

                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- QUEUE -->

                    <div class="queue-box">

                        <div class="queue-left">

                            <span class="queue-label">
                                Current Queue
                            </span>

                            <span class="queue-number">

                                <?= (int)$centre['current_queue'] ?>

                                farmers

                            </span>

                        </div>

                        <span
                            class="queue-status <?= e(queueClass($centre['current_queue'])) ?>"
                        >

                            <?= e(queueText($centre['current_queue'])) ?>

                        </span>

                    </div>


                    <!-- CAPACITY -->

                    <div class="info-row">

                        <div class="info-icon">📊</div>

                        <div style="width:100%;">

                            <div
                                style="
                                    display:flex;
                                    justify-content:space-between;
                                "
                            >

                                <span class="info-label">
                                    Today's Capacity
                                </span>

                                <span class="info-value">

                                    <?= (int)$centre['current_bookings'] ?>
                                    /
                                    <?= (int)$centre['total_capacity'] ?>

                                </span>

                            </div>

                            <div class="capacity-bar">

                                <div
                                    class="capacity-fill"
                                    style="width:<?= min(100, (int)$centre['occupancy']) ?>%;"
                                ></div>

                            </div>

                        </div>

                    </div>


                    <a
                        href="booking.php?centre_id=<?= (int)$centre['id'] ?>"
                        class="centre-action"
                    >
                        View Centre & Book Slot →
                    </a>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

</body>
</html>