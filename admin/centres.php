<?php

session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Admin Access
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Activate / Deactivate Centre
|--------------------------------------------------------------------------
*/

if (isset($_GET["toggle"]) && is_numeric($_GET["toggle"])) {

    $centre_id = (int) $_GET["toggle"];

    $stmt = $conn->prepare(
        "UPDATE procurement_centres
         SET status = IF(status = 'active', 'inactive', 'active')
         WHERE id = ?"
    );

    $stmt->bind_param("i", $centre_id);
    $stmt->execute();
    $stmt->close();

    header("Location: centres.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Fetch Centres
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        centre_name,
        centre_code,
        centre_type,
        agency_type,
        district,
        block,
        venue,
        purchase_officer,
        officer_mobile,
        purchase_date,
        slot_available,
        total_capacity,
        current_bookings,
        current_queue,
        status,
        last_updated
    FROM procurement_centres
    ORDER BY
        status = 'active' DESC,
        district ASC,
        centre_name ASC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Centre Management | KrishiSetu</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        .admin-page {
            min-height: 100vh;
            background: var(--background);
        }

        .admin-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 24px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-brand {
            color: var(--primary);
            font-size: 27px;
            font-weight: 700;
        }

        .admin-subtitle {
            color: var(--text-secondary);
            margin-top: 5px;
            font-size: 14px;
        }

        .logout-btn {
            text-decoration: none;
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 11px 20px;
            border-radius: var(--radius-md);
            font-weight: 600;
        }

        .logout-btn:hover {
            background: var(--primary);
            color: white;
        }

        .page-container {
            max-width: 1250px;
            margin: 0 auto;
            padding: 55px 25px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 28px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .page-title {
            font-size: 34px;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .page-description {
            color: var(--text-secondary);
            margin-bottom: 35px;
        }

        .centre-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .centre-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 25px;
            box-shadow: var(--shadow-sm);
        }

        .centre-top {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 18px;
        }

        .centre-name {
            color: var(--text-primary);
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .centre-code {
            color: var(--text-light);
            font-size: 13px;
        }

        .status-badge {
            height: fit-content;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-active {
            background: #e8f5ee;
            color: var(--success);
        }

        .status-inactive {
            background: #fbeaea;
            color: var(--danger);
        }

        .centre-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 20px;
        }

        .detail-box {
            background: var(--background);
            border-radius: var(--radius-md);
            padding: 13px;
        }

        .detail-label {
            display: block;
            color: var(--text-light);
            font-size: 12px;
            margin-bottom: 4px;
        }

        .detail-value {
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 600;
        }

        .centre-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat {
            text-align: center;
            background: var(--primary-light);
            border-radius: var(--radius-md);
            padding: 13px 8px;
        }

        .stat-number {
            display: block;
            color: var(--primary);
            font-size: 20px;
            font-weight: 700;
        }

        .stat-label {
            display: block;
            color: var(--text-secondary);
            font-size: 11px;
            margin-top: 3px;
        }

        .centre-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            border-top: 1px solid var(--border);
            padding-top: 18px;
        }

        .purchase-date {
            color: var(--text-secondary);
            font-size: 13px;
        }

        .toggle-btn {
            text-decoration: none;
            padding: 9px 15px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
        }

        .deactivate-btn {
            color: var(--danger);
            border: 1px solid #e4aaaa;
            background: #fff8f8;
        }

        .activate-btn {
            color: var(--success);
            border: 1px solid #a8d7bd;
            background: #f4fbf7;
        }

        .empty-state {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 50px;
            text-align: center;
            color: var(--text-secondary);
        }

        .prototype-note {
            margin-top: 30px;
            padding: 16px 18px;
            background: var(--accent-light);
            border: 1px solid #f0d69d;
            border-radius: var(--radius-md);
            color: #765615;
            font-size: 13px;
            line-height: 1.6;
        }

        @media (max-width: 850px) {

            .centre-grid {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 600px) {

            .admin-header {
                padding: 20px;
            }

            .page-container {
                padding: 35px 15px;
            }

            .page-title {
                font-size: 28px;
            }

            .centre-details {
                grid-template-columns: 1fr;
            }

            .centre-top {
                flex-direction: column;
            }

            .centre-footer {
                flex-direction: column;
                align-items: flex-start;
            }

        }

    </style>

</head>


<body>

<div class="admin-page">


    <!-- Header -->

    <header class="admin-header">

        <div>

            <div class="admin-brand">
                KrishiSetu
            </div>

            <div class="admin-subtitle">
                Admin Portal
            </div>

        </div>

        <a href="../logout.php" class="logout-btn">
            Logout
        </a>

    </header>


    <!-- Main -->

    <main class="page-container">


        <a href="dashboard.php" class="back-link">
            ← Back to Admin Dashboard
        </a>


        <h1 class="page-title">
            Centre Management
        </h1>

        <p class="page-description">
            View procurement centres and manage their availability.
        </p>


        <?php if ($result && $result->num_rows > 0): ?>

            <div class="centre-grid">

                <?php while ($centre = $result->fetch_assoc()): ?>

                    <?php

                    $status_class =
                        $centre["status"] === "active"
                        ? "status-active"
                        : "status-inactive";

                    $occupancy = 0;

                    if ((int)$centre["total_capacity"] > 0) {

                        $occupancy =
                            round(
                                (
                                    (int)$centre["current_bookings"]
                                    /
                                    (int)$centre["total_capacity"]
                                ) * 100
                            );

                    }

                    ?>

                    <div class="centre-card">


                        <!-- Top -->

                        <div class="centre-top">

                            <div>

                                <div class="centre-name">
                                    <?= htmlspecialchars($centre["centre_name"]) ?>
                                </div>

                                <div class="centre-code">
                                    Code:
                                    <?= htmlspecialchars($centre["centre_code"]) ?>
                                </div>

                            </div>


                            <span class="status-badge <?= $status_class ?>">

                                <?= ucfirst(htmlspecialchars($centre["status"])) ?>

                            </span>

                        </div>


                        <!-- Details -->

                        <div class="centre-details">


                            <div class="detail-box">

                                <span class="detail-label">
                                    Centre Type
                                </span>

                                <span class="detail-value">
                                    <?= htmlspecialchars($centre["centre_type"] ?: "—") ?>
                                </span>

                            </div>


                            <div class="detail-box">

                                <span class="detail-label">
                                    Agency
                                </span>

                                <span class="detail-value">
                                    <?= htmlspecialchars($centre["agency_type"] ?: "—") ?>
                                </span>

                            </div>


                            <div class="detail-box">

                                <span class="detail-label">
                                    District
                                </span>

                                <span class="detail-value">
                                    <?= htmlspecialchars($centre["district"] ?: "—") ?>
                                </span>

                            </div>


                            <div class="detail-box">

                                <span class="detail-label">
                                    Block
                                </span>

                                <span class="detail-value">
                                    <?= htmlspecialchars($centre["block"] ?: "—") ?>
                                </span>

                            </div>


                            <div class="detail-box">

                                <span class="detail-label">
                                    Venue
                                </span>

                                <span class="detail-value">
                                    <?= htmlspecialchars($centre["venue"] ?: "—") ?>
                                </span>

                            </div>


                            <div class="detail-box">

                                <span class="detail-label">
                                    Purchase Officer
                                </span>

                                <span class="detail-value">
                                    <?= htmlspecialchars($centre["purchase_officer"] ?: "—") ?>
                                </span>

                            </div>


                        </div>


                        <!-- Stats -->

                        <div class="centre-stats">


                            <div class="stat">

                                <span class="stat-number">
                                    <?= (int)$centre["current_queue"] ?>
                                </span>

                                <span class="stat-label">
                                    Queue
                                </span>

                            </div>


                            <div class="stat">

                                <span class="stat-number">
                                    <?= (int)$centre["current_bookings"] ?>
                                </span>

                                <span class="stat-label">
                                    Bookings
                                </span>

                            </div>


                            <div class="stat">

                                <span class="stat-number">
                                    <?= $occupancy ?>%
                                </span>

                                <span class="stat-label">
                                    Occupancy
                                </span>

                            </div>


                        </div>


                        <!-- Footer -->

                        <div class="centre-footer">


                            <div class="purchase-date">

                                Purchase date:
                                <strong>

                                    <?php

                                    if (!empty($centre["purchase_date"])) {

                                        echo date(
                                            "d M Y",
                                            strtotime($centre["purchase_date"])
                                        );

                                    } else {

                                        echo "Not scheduled";

                                    }

                                    ?>

                                </strong>

                                <br>

                                Slot:
                                <strong>

                                    <?= $centre["slot_available"]
                                        ? "Available"
                                        : "Unavailable"
                                    ?>

                                </strong>

                            </div>


                            <?php if ($centre["status"] === "active"): ?>

                                <a
                                    href="centres.php?toggle=<?= (int)$centre["id"] ?>"
                                    class="toggle-btn deactivate-btn"
                                    onclick="return confirm('Deactivate this procurement centre?');"
                                >
                                    Deactivate
                                </a>

                            <?php else: ?>

                                <a
                                    href="centres.php?toggle=<?= (int)$centre["id"] ?>"
                                    class="toggle-btn activate-btn"
                                    onclick="return confirm('Activate this procurement centre?');"
                                >
                                    Activate
                                </a>

                            <?php endif; ?>


                        </div>


                    </div>

                <?php endwhile; ?>

            </div>


        <?php else: ?>

            <div class="empty-state">

                No procurement centres found.

            </div>

        <?php endif; ?>


        <div class="prototype-note">

            <strong>Prototype data:</strong>
            The procurement centre records currently shown are
            demonstration records based on the official West Bengal
            e-Paddy centre data structure. Production deployment will
            use authorised government data/API integration.

        </div>


    </main>

</div>

</body>

</html>