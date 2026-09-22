<?php
session_start();
require_once "../config/database.php";

/* =========================================================
   ADMIN ACCESS
========================================================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* =========================================================
   HELPERS
========================================================= */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function statusLabel(string $status): string
{
    $labels = [
        'booked'    => 'Booked',
        'pending'   => 'Pending',
        'arrived'   => 'Farmer Arrived',
        'weighed'   => 'Paddy Weighed',
        'accepted'  => 'Accepted',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
    ];

    return $labels[$status] ?? ucfirst($status);
}

function statusClass(string $status): string
{
    return match ($status) {
        'accepted', 'completed' => 'status-success',
        'arrived', 'weighed'    => 'status-info',
        'cancelled'             => 'status-danger',
        'pending', 'booked'     => 'status-warning',
        default                 => 'status-neutral',
    };
}

/* =========================================================
   ACTIVE CENTRES
========================================================= */
$centreListResult = $conn->query("
SELECT
    id,
    centre_name,
    centre_code,
    block,
    district,
    agency_type AS agency,
    venue,
    current_queue
FROM procurement_centres
");

$activeCentres = [];

if ($centreListResult) {
    while ($row = $centreListResult->fetch_assoc()) {
        $activeCentres[] = $row;
    }
}

if (empty($activeCentres)) {
    die("No active procurement centre is available.");
}

/* =========================================================
   CENTRE SELECTION
========================================================= */

$requestedCentreId = (int)($_GET['centre_id'] ?? 0);

if ($requestedCentreId > 0) {

    foreach ($activeCentres as $centre) {

        if ((int)$centre['id'] === $requestedCentreId) {

            $_SESSION['admin_centre_id'] = $requestedCentreId;

            break;
        }
    }
}

$selectedCentreId = (int)($_SESSION['admin_centre_id'] ?? 0);

/*
 * If no centre has been selected yet,
 * automatically select the first active centre.
 */
if ($selectedCentreId <= 0) {

    $selectedCentreId = (int)$activeCentres[0]['id'];

    $_SESSION['admin_centre_id'] = $selectedCentreId;
}

$selectedCentre = null;

foreach ($activeCentres as $centre) {

    if ((int)$centre['id'] === $selectedCentreId) {

        $selectedCentre = $centre;

        break;
    }
}

if (!$selectedCentre) {

    $selectedCentre = $activeCentres[0];

    $selectedCentreId = (int)$selectedCentre['id'];

    $_SESSION['admin_centre_id'] = $selectedCentreId;
}

/* =========================================================
   CENTRE-WISE STATISTICS
========================================================= */

/* Farmers at selected centre */
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT farmer_id) AS total
    FROM bookings
    WHERE centre_id = ?
");

$stmt->bind_param(
    "i",
    $selectedCentreId
);

$stmt->execute();

$totalFarmers = (int)(
    $stmt->get_result()->fetch_assoc()['total'] ?? 0
);

$stmt->close();


/* Total bookings */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE centre_id = ?
");

$stmt->bind_param(
    "i",
    $selectedCentreId
);

$stmt->execute();

$totalBookings = (int)(
    $stmt->get_result()->fetch_assoc()['total'] ?? 0
);

$stmt->close();


/* Pending procurement */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total

    FROM procurement p

    INNER JOIN bookings b
        ON p.booking_id = b.id

    WHERE b.centre_id = ?
      AND p.status = 'pending'
");

$stmt->bind_param(
    "i",
    $selectedCentreId
);

$stmt->execute();

$pendingProcurement = (int)(
    $stmt->get_result()->fetch_assoc()['total'] ?? 0
);

$stmt->close();


/* Current queue */
$stmt = $conn->prepare("
    SELECT current_queue

    FROM procurement_centres

    WHERE id = ?

    LIMIT 1
");

$stmt->bind_param(
    "i",
    $selectedCentreId
);

$stmt->execute();

$currentQueue = (int)(
    $stmt->get_result()->fetch_assoc()['current_queue'] ?? 0
);

$stmt->close();


/* =========================================================
   SLOT STATISTICS
========================================================= */

$stmt = $conn->prepare("
    SELECT

        COUNT(*) AS total,

        COALESCE(
            SUM(status = 'available'),
            0
        ) AS available,

        COALESCE(
            SUM(status = 'full'),
            0
        ) AS full

    FROM slots

    WHERE centre_id = ?
      AND slot_date >= CURDATE()
");

$stmt->bind_param(
    "i",
    $selectedCentreId
);

$stmt->execute();

$slotStats =
    $stmt->get_result()->fetch_assoc()
    ?: [];

$stmt->close();


$totalSlots =
    (int)($slotStats['total'] ?? 0);

$availableSlots =
    (int)($slotStats['available'] ?? 0);

$fullSlots =
    (int)($slotStats['full'] ?? 0);


/* =========================================================
   REMAINING CAPACITY
========================================================= */

$stmt = $conn->prepare("
    SELECT
        COALESCE(
            SUM(capacity - booked_count),
            0
        ) AS remaining_capacity

    FROM slots

    WHERE centre_id = ?
      AND slot_date >= CURDATE()
      AND status = 'available'
      AND booked_count < capacity
");

$stmt->bind_param(
    "i",
    $selectedCentreId
);

$stmt->execute();

$remainingCapacity = (int)(
    $stmt
        ->get_result()
        ->fetch_assoc()['remaining_capacity']
        ?? 0
);

$stmt->close();


/* =========================================================
   NEXT AVAILABLE SLOT
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        slot_date,
        start_time,
        end_time,
        capacity,
        booked_count,
        status

    FROM slots

    WHERE centre_id = ?
      AND slot_date >= CURDATE()
      AND status = 'available'
      AND booked_count < capacity

    ORDER BY
        slot_date ASC,
        start_time ASC

    LIMIT 1
");

$stmt->bind_param(
    "i",
    $selectedCentreId
);

$stmt->execute();

$nextSlot =
    $stmt->get_result()->fetch_assoc()
    ?: null;

$stmt->close();


/* =========================================================
   RECENT BOOKINGS
========================================================= */

$stmt = $conn->prepare("
    SELECT

        b.id,
        b.booking_token,
        b.status AS booking_status,
        b.created_at,

        u.name AS farmer_name,
        u.mobile,

        s.slot_date,
        s.start_time,
        s.end_time

    FROM bookings b

    INNER JOIN farmers f
        ON b.farmer_id = f.id

    INNER JOIN users u
        ON f.user_id = u.id

    INNER JOIN slots s
        ON b.slot_id = s.id

    WHERE b.centre_id = ?

    ORDER BY b.created_at DESC

    LIMIT 8
");

$stmt->bind_param(
    "i",
    $selectedCentreId
);

$stmt->execute();

$recentBookings =
    $stmt->get_result();


/* =========================================================
   DISPLAY VALUES
========================================================= */

$centreName =
    $selectedCentre['centre_name']
    ?? 'Procurement Centre';

$centreCode =
    $selectedCentre['centre_code']
    ?? '';

$centreBlock =
    $selectedCentre['block']
    ?? '';

$centreDistrict =
    $selectedCentre['district']
    ?? '';

$centreAgency =
    $selectedCentre['agency']
    ?? '';

$centreVenue =
    $selectedCentre['venue']
    ?? '';

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admin Dashboard | KrishiSetu
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        :root {

            --primary: #087443;
            --primary-dark: #055c35;
            --primary-light: #e8f5ee;

            --accent: #f2b84b;
            --accent-light: #fff7e6;

            --background: #f7f9f7;
            --surface: #ffffff;

            --text: #17352a;
            --muted: #61716a;
            --light: #8a9892;

            --border: #dfe7e2;

            --danger: #c93434;
            --info: #2878b5;

            --shadow:
                0 8px 24px
                rgba(23, 53, 42, 0.06);
        }


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            background:
                var(--background);

            color:
                var(--text);

            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }


        a {
            color: inherit;
        }


        /* =================================================
           MAIN SHELL
        ================================================= */

        .admin-shell {

            min-height:
                100vh;
        }


        /* =================================================
           TOP BAR
        ================================================= */

        .topbar {

            position:
                sticky;

            top: 0;

            z-index: 20;

            height:
                74px;

            background:
                var(--surface);

            border-bottom:
                1px solid var(--border);

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            padding:
                0 28px;
        }


        .brand {

            display:
                flex;

            align-items:
                center;

            gap:
                12px;

            text-decoration:
                none;
        }


        .brand-mark {

            width:
                42px;

            height:
                42px;

            border-radius:
                12px;

            background:
                var(--primary-light);

            display:
                grid;

            place-items:
                center;

            font-size:
                23px;
        }


        .brand-name {

            font-size:
                21px;

            font-weight:
                800;

            color:
                var(--primary);

            line-height:
                1;
        }


        .brand-subtitle {

            margin-top:
                4px;

            color:
                var(--muted);

            font-size:
                12px;
        }


        .topbar-right {

            display:
                flex;

            align-items:
                center;

            gap:
                14px;
        }


        .today-pill {

            display:
                flex;

            align-items:
                center;

            gap:
                8px;

            padding:
                9px 13px;

            background:
                var(--primary-light);

            border-radius:
                10px;

            color:
                var(--primary-dark);

            font-size:
                13px;
        }


        .admin-user {

            display:
                flex;

            align-items:
                center;

            gap:
                9px;

            font-weight:
                700;

            font-size:
                14px;
        }


        .avatar {

            width:
                34px;

            height:
                34px;

            border-radius:
                50%;

            background:
                var(--primary);

            color:
                white;

            display:
                grid;

            place-items:
                center;
        }


        .logout-link {

            text-decoration:
                none;

            color:
                var(--danger);

            font-weight:
                700;

            font-size:
                13px;
        }


        /* =================================================
           LAYOUT
        ================================================= */

        .layout {

            display:
                grid;

            grid-template-columns:
                220px minmax(0, 1fr);

            min-height:
                calc(100vh - 74px);
        }


        /* =================================================
           SIDEBAR
        ================================================= */

        .sidebar {

            background:
                #ffffff;

            border-right:
                1px solid var(--border);

            padding:
                22px 12px;
        }


        .nav-title {

            padding:
                0 12px 10px;

            color:
                var(--light);

            text-transform:
                uppercase;

            letter-spacing:
                .08em;

            font-size:
                10px;

            font-weight:
                800;
        }


        .nav-link {

            display:
                flex;

            align-items:
                center;

            gap:
                11px;

            padding:
                12px 13px;

            margin-bottom:
                5px;

            border-radius:
                9px;

            text-decoration:
                none;

            color:
                #40554d;

            font-size:
                14px;

            font-weight:
                600;
        }


        .nav-link:hover {

            background:
                var(--primary-light);

            color:
                var(--primary-dark);
        }


        .nav-link.active {

            background:
                var(--primary-light);

            color:
                var(--primary-dark);

            box-shadow:
                inset 3px 0 0
                var(--primary);
        }


        .nav-icon {

            width:
                24px;

            text-align:
                center;

            font-size:
                17px;
        }


        .sidebar-bottom {

            margin-top:
                28px;

            padding-top:
                18px;

            border-top:
                1px solid var(--border);
        }


        /* =================================================
           MAIN
        ================================================= */

        .main {

            width:
                100%;

            max-width:
                1440px;

            margin:
                0 auto;

            padding:
                32px 38px 60px;
        }


        /* =================================================
           PAGE HEADER
        ================================================= */

        .page-head {

            display:
                flex;

            align-items:
                flex-start;

            justify-content:
                space-between;

            gap:
                20px;

            margin-bottom:
                24px;
        }


        .page-title {

            margin:
                0;

            font-size:
                34px;

            line-height:
                1.15;

            letter-spacing:
                -0.02em;
        }


        .page-description {

            margin:
                9px 0 0;

            color:
                var(--muted);

            font-size:
                15px;

            line-height:
                1.55;
        }


        .centre-status {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            padding:
                8px 12px;

            border-radius:
                999px;

            background:
                var(--primary-light);

            color:
                var(--primary-dark);

            font-size:
                12px;

            font-weight:
                800;

            white-space:
                nowrap;
        }


        .dot {

            width:
                8px;

            height:
                8px;

            border-radius:
                50%;

            background:
                #15945b;
        }


        /* =================================================
           CENTRE SELECTOR
        ================================================= */

        .centre-selector {

            background:
                var(--surface);

            border:
                1px solid var(--border);

            border-radius:
                16px;

            box-shadow:
                var(--shadow);

            padding:
                22px;

            margin-bottom:
                24px;
        }


        .selector-grid {

            display:
                grid;

            grid-template-columns:
                auto minmax(0, 1fr) auto;

            gap:
                18px;

            align-items:
                center;
        }


        .selector-icon {

            width:
                60px;

            height:
                60px;

            border-radius:
                50%;

            background:
                var(--primary-light);

            display:
                grid;

            place-items:
                center;

            font-size:
                27px;
        }


        .field-label {

            display:
                block;

            margin-bottom:
                8px;

            font-size:
                12px;

            font-weight:
                800;

            color:
                #315248;
        }


        .centre-select {

            width:
                100%;

            height:
                48px;

            border:
                1px solid #cfdcd5;

            border-radius:
                9px;

            padding:
                0 14px;

            background:
                white;

            color:
                var(--text);

            font-size:
                14px;

            outline:
                none;
        }


        .centre-select:focus {

            border-color:
                var(--primary);

            box-shadow:
                0 0 0 3px
                rgba(8, 116, 67, .10);
        }


        .switch-btn {

            height:
                48px;

            padding:
                0 22px;

            border:
                0;

            border-radius:
                9px;

            background:
                var(--primary);

            color:
                white;

            font-weight:
                800;

            cursor:
                pointer;
        }


        .switch-btn:hover {

            background:
                var(--primary-dark);
        }


        .working-at {

            margin:
                13px 0 0 78px;

            color:
                var(--muted);

            font-size:
                13px;
        }


        .working-at strong {

            color:
                var(--text);
        }


        /* =================================================
           METRIC CARDS
        ================================================= */

        .metrics-grid {

            display:
                grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap:
                16px;

            margin-bottom:
                24px;
        }


        .metric-card {

            background:
                var(--surface);

            border:
                1px solid var(--border);

            border-radius:
                14px;

            padding:
                18px;

            min-height:
                128px;

            box-shadow:
                var(--shadow);
        }


        .metric-top {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                flex-start;

            gap:
                12px;
        }


        .metric-icon {

            width:
                42px;

            height:
                42px;

            border-radius:
                11px;

            background:
                var(--primary-light);

            display:
                grid;

            place-items:
                center;

            font-size:
                19px;
        }


        .metric-label {

            color:
                var(--muted);

            font-size:
                13px;

            font-weight:
                600;

            margin-top:
                2px;
        }


        .metric-number {

            margin-top:
                14px;

            font-size:
                29px;

            line-height:
                1;

            font-weight:
                800;

            color:
                var(--primary-dark);
        }


        .metric-note {

            margin-top:
                7px;

            color:
                var(--light);

            font-size:
                11px;
        }


        .metric-warning .metric-icon {
            background:
                var(--accent-light);
        }


        .metric-danger .metric-icon {
            background:
                #fff0f0;
        }


        .metric-info .metric-icon {
            background:
                #eef6fb;
        }


        /* =================================================
           LOWER CONTENT
        ================================================= */

        .lower-grid {

            display:
                grid;

            grid-template-columns:
                minmax(0, 1.35fr)
                minmax(320px, .85fr);

            gap:
                18px;
        }


        .panel {

            background:
                var(--surface);

            border:
                1px solid var(--border);

            border-radius:
                15px;

            box-shadow:
                var(--shadow);

            overflow:
                hidden;
        }


        .panel-head {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                15px;

            padding:
                19px 20px;

            border-bottom:
                1px solid var(--border);
        }


        .panel-title {

            margin:
                0;

            font-size:
                17px;
        }


        .panel-subtitle {

            margin:
                4px 0 0;

            color:
                var(--muted);

            font-size:
                12px;
        }


        .view-link {

            text-decoration:
                none;

            color:
                var(--primary);

            font-size:
                12px;

            font-weight:
                800;

            white-space:
                nowrap;
        }


        /* =================================================
           BOOKINGS
        ================================================= */

        .booking-list {

            padding:
                4px 20px 8px;
        }


        .booking-row {

            display:
                grid;

            grid-template-columns:
                minmax(0, 1.25fr)
                minmax(130px, .8fr)
                auto;

            gap:
                18px;

            align-items:
                center;

            padding:
                16px 0;

            border-bottom:
                1px solid #edf1ee;
        }


        .booking-row:last-child {
            border-bottom:
                0;
        }


        .farmer-name {

            font-weight:
                800;

            font-size:
                14px;
        }


        .booking-token {

            margin-top:
                5px;

            color:
                var(--muted);

            font-family:
                Consolas,
                monospace;

            font-size:
                11px;
        }


        .schedule {

            color:
                #425b51;

            font-size:
                12px;

            line-height:
                1.5;
        }


        .status-badge {

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            padding:
                6px 9px;

            border-radius:
                999px;

            font-size:
                11px;

            font-weight:
                800;

            white-space:
                nowrap;
        }


        .status-success {

            color:
                #087443;

            background:
                #e8f5ee;
        }


        .status-info {

            color:
                #21648f;

            background:
                #eef6fb;
        }


        .status-warning {

            color:
                #9a6300;

            background:
                #fff7e6;
        }


        .status-danger {

            color:
                #a52d2d;

            background:
                #fff0f0;
        }


        .status-neutral {

            color:
                #5c6863;

            background:
                #f0f3f1;
        }


        .empty-state {

            padding:
                42px 20px;

            text-align:
                center;

            color:
                var(--muted);

            font-size:
                13px;
        }


        /* =================================================
           CENTRE INFORMATION
        ================================================= */

        .centre-info {

            padding:
                20px;
        }


        .centre-info-head {

            display:
                flex;

            align-items:
                flex-start;

            justify-content:
                space-between;

            gap:
                15px;

            margin-bottom:
                15px;
        }


        .centre-info-name {

            margin:
                0;

            font-size:
                18px;

            line-height:
                1.3;
        }


        .centre-code {

            margin-top:
                5px;

            color:
                var(--muted);

            font-family:
                Consolas,
                monospace;

            font-size:
                11px;
        }


        .info-table {

            border-top:
                1px solid var(--border);
        }


        .info-line {

            display:
                flex;

            justify-content:
                space-between;

            gap:
                20px;

            padding:
                12px 0;

            border-bottom:
                1px solid #edf1ee;

            font-size:
                12px;
        }


        .info-line span:first-child {

            color:
                var(--muted);
        }


        .info-line span:last-child {

            text-align:
                right;

            font-weight:
                700;
        }


        .next-slot {

            margin-top:
                18px;

            padding:
                15px;

            border-radius:
                11px;

            background:
                var(--primary-light);
        }


        .next-slot-label {

            color:
                var(--primary-dark);

            font-size:
                11px;

            font-weight:
                800;

            text-transform:
                uppercase;

            letter-spacing:
                .05em;
        }


        .next-slot-time {

            margin-top:
                7px;

            font-size:
                14px;

            font-weight:
                800;

            color:
                var(--text);
        }


        .capacity-bar {

            height:
                7px;

            margin-top:
                11px;

            border-radius:
                99px;

            background:
                #dbe8e1;

            overflow:
                hidden;
        }


        .capacity-fill {

            height:
                100%;

            width:
                100%;

            background:
                var(--primary);

            border-radius:
                inherit;
        }


        .capacity-note {

            margin-top:
                7px;

            color:
                var(--muted);

            font-size:
                11px;
        }


        /* =================================================
           RESPONSIVE
        ================================================= */

        @media (max-width: 1100px) {

            .metrics-grid {

                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }


            .lower-grid {

                grid-template-columns:
                    1fr;
            }
        }


        @media (max-width: 820px) {

            .layout {

                grid-template-columns:
                    1fr;
            }


            .sidebar {

                display:
                    none;
            }


            .main {

                padding:
                    25px 18px 45px;
            }


            .topbar {

                padding:
                    0 17px;
            }


            .today-pill {

                display:
                    none;
            }


            .selector-grid {

                grid-template-columns:
                    1fr;
            }


            .selector-icon {

                display:
                    none;
            }


            .working-at {

                margin-left:
                    0;
            }
        }


        @media (max-width: 600px) {

            .topbar {

                height:
                    66px;
            }


            .brand-name {

                font-size:
                    18px;
            }


            .brand-subtitle,
            .admin-user {

                display:
                    none;
            }


            .page-head {

                display:
                    block;
            }


            .centre-status {

                margin-top:
                    14px;
            }


            .page-title {

                font-size:
                    28px;
            }


            .metrics-grid {

                grid-template-columns:
                    1fr;
            }


            .booking-row {

                grid-template-columns:
                    1fr;

                gap:
                    8px;
            }


            .booking-row
            .status-badge {

                justify-self:
                    start;
            }


            .info-line {

                align-items:
                    flex-start;

                flex-direction:
                    column;

                gap:
                    4px;
            }


            .info-line span:last-child {

                text-align:
                    left;
            }


            .switch-btn {

                width:
                    100%;
            }
        }

    </style>

</head>


<body>

<div class="admin-shell">


    <!-- =================================================
         HEADER
    ================================================= -->

    <header class="topbar">

        <a
            href="dashboard.php"
            class="brand"
        >

            <div class="brand-mark">
                🌾
            </div>

            <div>

                <div class="brand-name">
                    KrishiSetu
                </div>

                <div class="brand-subtitle">
                    Admin Portal
                </div>

            </div>

        </a>


        <div class="topbar-right">

            <div class="today-pill">

                📅

                <strong>
                    Today
                </strong>

                <?= date("d M Y") ?>

            </div>


            <div class="admin-user">

                <div class="avatar">
                    A
                </div>

                <span>
                    Admin
                </span>

            </div>


            <a
                href="../logout.php"
                class="logout-link"
            >
                Logout
            </a>

        </div>

    </header>


    <div class="layout">


        <!-- =================================================
             SIDEBAR
        ================================================= -->

        <aside class="sidebar">

            <div class="nav-title">
                Administration
            </div>


            <a
                href="dashboard.php"
                class="nav-link active"
            >

                <span class="nav-icon">
                    ⌂
                </span>

                Dashboard

            </a>


            <!-- Global centre management -->

            <a
                href="centres.php"
                class="nav-link"
            >

                <span class="nav-icon">
                    ▣
                </span>

                Centre Management

            </a>


            <!-- Selected-centre operations -->

            <a
                href="procurement.php"
                class="nav-link"
            >

                <span class="nav-icon">
                    ▤
                </span>

                Procurement

            </a>


            <a
                href="slots.php"
                class="nav-link"
            >

                <span class="nav-icon">
                    ▦
                </span>

                Slots

            </a>


            <a
                href="payment.php"
                class="nav-link"
            >

                <span class="nav-icon">
                    ₹
                </span>

                Payments

            </a>


            <a
                href="farmers.php"
                class="nav-link"
            >

                <span class="nav-icon">
                    ♙
                </span>

                Farmers

            </a>


            <div class="sidebar-bottom">

                <a
                    href="../login.php"
                    class="nav-link"
                >

                    <span class="nav-icon">
                        ↪
                    </span>

                    Logout

                </a>

            </div>

        </aside>


        <!-- =================================================
             MAIN
        ================================================= -->

        <main class="main">


            <!-- PAGE HEADER -->

            <div class="page-head">

                <div>

                    <h1 class="page-title">
                        Admin Dashboard
                    </h1>

                    <p class="page-description">
                        Manage one procurement centre at a time
                        and monitor its live operations.
                    </p>

                </div>


                <div class="centre-status">

                    <span class="dot"></span>

                    Working Centre Active

                </div>

            </div>


            <!-- =================================================
                 CENTRE SELECTOR
            ================================================= -->

            <section class="centre-selector">

                <form method="GET">

                    <div class="selector-grid">


                        <div class="selector-icon">
                            🏢
                        </div>


                        <div>

                            <label
                                class="field-label"
                                for="centre_id"
                            >
                                Select Procurement Centre
                            </label>


                            <select
                                name="centre_id"
                                id="centre_id"
                                class="centre-select"
                            >

                                <?php foreach (
                                    $activeCentres
                                    as $centre
                                ): ?>

                                    <option
                                        value="<?= (int)$centre['id'] ?>"
                                        <?= (int)$centre['id']
                                            === $selectedCentreId
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >

                                        <?= e(
                                            $centre['centre_name']
                                        ) ?>

                                        —

                                        <?= e(
                                            $centre['block']
                                        ) ?>,

                                        <?= e(
                                            $centre['district']
                                        ) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <button
                            type="submit"
                            class="switch-btn"
                        >

                            ⇄ Switch Centre

                        </button>

                    </div>


                    <div class="working-at">

                        📍 Currently working at:

                        <strong>
                            <?= e($centreName) ?>
                        </strong>

                        —

                        <?= e($centreBlock) ?>,

                        <?= e($centreDistrict) ?>

                    </div>

                </form>

            </section>


            <!-- =================================================
                 METRIC CARDS
            ================================================= -->

            <section class="metrics-grid">


                <!-- Farmers -->

                <div class="metric-card">

                    <div class="metric-top">

                        <div class="metric-label">
                            Farmers at Centre
                        </div>

                        <div class="metric-icon">
                            👥
                        </div>

                    </div>


                    <div class="metric-number">
                        <?= $totalFarmers ?>
                    </div>


                    <div class="metric-note">
                        Farmers with bookings
                    </div>

                </div>


                <!-- Queue -->

                <div class="metric-card metric-warning">

                    <div class="metric-top">

                        <div class="metric-label">
                            Current Queue
                        </div>

                        <div class="metric-icon">
                            ⏱
                        </div>

                    </div>


                    <div class="metric-number">
                        <?= $currentQueue ?>
                    </div>


                    <div class="metric-note">
                        Farmers currently waiting
                    </div>

                </div>


                <!-- Bookings -->

                <div class="metric-card">

                    <div class="metric-top">

                        <div class="metric-label">
                            Total Bookings
                        </div>

                        <div class="metric-icon">
                            📅
                        </div>

                    </div>


                    <div class="metric-number">
                        <?= $totalBookings ?>
                    </div>


                    <div class="metric-note">
                        All bookings at this centre
                    </div>

                </div>


                <!-- Pending -->

                <div class="metric-card metric-danger">

                    <div class="metric-top">

                        <div class="metric-label">
                            Pending Procurement
                        </div>

                        <div class="metric-icon">
                            📋
                        </div>

                    </div>


                    <div class="metric-number">
                        <?= $pendingProcurement ?>
                    </div>


                    <div class="metric-note">
                        Waiting for processing
                    </div>

                </div>


                <!-- Total slots -->

                <div class="metric-card">

                    <div class="metric-top">

                        <div class="metric-label">
                            Total Active Slots
                        </div>

                        <div class="metric-icon">
                            🗓
                        </div>

                    </div>


                    <div class="metric-number">
                        <?= $totalSlots ?>
                    </div>


                    <div class="metric-note">
                        Future slots configured
                    </div>

                </div>


                <!-- Available -->

                <div class="metric-card metric-info">

                    <div class="metric-top">

                        <div class="metric-label">
                            Available Slots
                        </div>

                        <div class="metric-icon">
                            ◷
                        </div>

                    </div>


                    <div class="metric-number">
                        <?= $availableSlots ?>
                    </div>


                    <div class="metric-note">
                        Slots accepting bookings
                    </div>

                </div>


                <!-- Full -->

                <div class="metric-card metric-danger">

                    <div class="metric-top">

                        <div class="metric-label">
                            Full Slots
                        </div>

                        <div class="metric-icon">
                            🔒
                        </div>

                    </div>


                    <div class="metric-number">
                        <?= $fullSlots ?>
                    </div>


                    <div class="metric-note">
                        Completely booked slots
                    </div>

                </div>


                <!-- Capacity -->

                <div class="metric-card metric-info">

                    <div class="metric-top">

                        <div class="metric-label">
                            Remaining Capacity
                        </div>

                        <div class="metric-icon">
                            ◉
                        </div>

                    </div>


                    <div class="metric-number">
                        <?= $remainingCapacity ?>
                    </div>


                    <div class="metric-note">
                        Available booking capacity
                    </div>

                </div>

            </section>


            <!-- =================================================
                 LOWER CONTENT
            ================================================= -->

            <section class="lower-grid">


                <!-- =================================================
                     RECENT BOOKINGS
                ================================================= -->

                <div class="panel">

                    <div class="panel-head">

                        <div>

                            <h2 class="panel-title">
                                Recent Bookings
                            </h2>

                            <p class="panel-subtitle">
                                Latest booking activity
                                for the selected centre.
                            </p>

                        </div>


                        <a
                            href="procurement.php"
                            class="view-link"
                        >
                            Open Procurement →
                        </a>

                    </div>


                    <div class="booking-list">

                        <?php if (
                            $recentBookings->num_rows > 0
                        ): ?>


                            <?php while (
                                $booking =
                                $recentBookings->fetch_assoc()
                            ): ?>


                                <div class="booking-row">


                                    <div>

                                        <div class="farmer-name">

                                            <?= e(
                                                $booking['farmer_name']
                                            ) ?>

                                        </div>


                                        <div class="booking-token">

                                            <?= e(
                                                $booking['booking_token']
                                            ) ?>

                                        </div>

                                    </div>


                                    <div class="schedule">

                                        <?= date(
                                            "d M Y",
                                            strtotime(
                                                $booking['slot_date']
                                            )
                                        ) ?>

                                        <br>

                                        <?= date(
                                            "h:i A",
                                            strtotime(
                                                $booking['start_time']
                                            )
                                        ) ?>

                                        –

                                        <?= date(
                                            "h:i A",
                                            strtotime(
                                                $booking['end_time']
                                            )
                                        ) ?>

                                    </div>


                                    <div>

                                        <span
                                            class="status-badge
                                            <?= e(
                                                statusClass(
                                                    $booking[
                                                        'booking_status'
                                                    ]
                                                )
                                            ) ?>"
                                        >

                                            <?= e(
                                                statusLabel(
                                                    $booking[
                                                        'booking_status'
                                                    ]
                                                )
                                            ) ?>

                                        </span>

                                    </div>

                                </div>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <div class="empty-state">

                                No bookings have been
                                recorded for this centre yet.

                            </div>


                        <?php endif; ?>

                    </div>

                </div>


                <!-- =================================================
                     CENTRE INFORMATION
                ================================================= -->

                <div class="panel">


                    <div class="panel-head">

                        <div>

                            <h2 class="panel-title">
                                Centre Information
                            </h2>

                            <p class="panel-subtitle">
                                Current working centre details.
                            </p>

                        </div>


                        <span class="centre-status">

                            <span class="dot"></span>

                            Active

                        </span>

                    </div>


                    <div class="centre-info">


                        <div class="centre-info-head">

                            <div>

                                <h3 class="centre-info-name">

                                    <?= e(
                                        $centreName
                                    ) ?>

                                </h3>


                                <div class="centre-code">

                                    <?= e(
                                        $centreCode
                                    ) ?>

                                </div>

                            </div>

                        </div>


                        <div class="info-table">


                            <div class="info-line">

                                <span>
                                    Agency
                                </span>

                                <span>
                                    <?= e(
                                        $centreAgency
                                        ?: '—'
                                    ) ?>
                                </span>

                            </div>


                            <div class="info-line">

                                <span>
                                    Block
                                </span>

                                <span>
                                    <?= e(
                                        $centreBlock
                                        ?: '—'
                                    ) ?>
                                </span>

                            </div>


                            <div class="info-line">

                                <span>
                                    District
                                </span>

                                <span>
                                    <?= e(
                                        $centreDistrict
                                        ?: '—'
                                    ) ?>
                                </span>

                            </div>


                            <div class="info-line">

                                <span>
                                    Venue
                                </span>

                                <span>
                                    <?= e(
                                        $centreVenue
                                        ?: '—'
                                    ) ?>
                                </span>

                            </div>


                            <div class="info-line">

                                <span>
                                    Current Queue
                                </span>

                                <span>

                                    <?= $currentQueue ?>

                                    farmer<?=

                                        $currentQueue === 1
                                            ? ''
                                            : 's'

                                    ?>

                                </span>

                            </div>


                        </div>


                        <!-- NEXT SLOT -->

                        <div class="next-slot">

                            <div class="next-slot-label">
                                Next Available Slot
                            </div>


                            <?php if ($nextSlot): ?>


                                <div class="next-slot-time">

                                    <?= date(
                                        "d M Y",
                                        strtotime(
                                            $nextSlot['slot_date']
                                        )
                                    ) ?>

                                    ·

                                    <?= date(
                                        "h:i A",
                                        strtotime(
                                            $nextSlot['start_time']
                                        )
                                    ) ?>

                                    –

                                    <?= date(
                                        "h:i A",
                                        strtotime(
                                            $nextSlot['end_time']
                                        )
                                    ) ?>

                                </div>


                                <div class="capacity-bar">

                                    <div class="capacity-fill"></div>

                                </div>


                                <div class="capacity-note">

                                    <?= max(
                                        0,
                                        (int)$nextSlot['capacity']
                                        -
                                        (int)$nextSlot['booked_count']
                                    ) ?>

                                    places remaining in this slot.

                                </div>


                            <?php else: ?>


                                <div class="next-slot-time">
                                    No available slot
                                </div>


                                <div class="capacity-note">

                                    Create or open a future slot
                                    from Slot Management.

                                </div>


                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>

</body>

</html>