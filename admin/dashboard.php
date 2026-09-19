<?php
session_start();
require_once "../config/database.php";

/*
 * Admin protection
 */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


/* =========================
   DASHBOARD COUNTS
   ========================= */

$farmerResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'farmer'
");

$totalFarmers = $farmerResult->fetch_assoc()['total'];


$centreResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM procurement_centres
    WHERE status = 'active'
");

$totalCentres = $centreResult->fetch_assoc()['total'];


$bookingResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings
");

$totalBookings = $bookingResult->fetch_assoc()['total'];


$pendingResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM procurement
    WHERE status = 'pending'
");

$pendingProcurement = $pendingResult->fetch_assoc()['total'];


/* =========================
   RECENT BOOKINGS
   ========================= */

$recentBookings = $conn->query("
    SELECT
        b.booking_token,
        b.status AS booking_status,
        u.name AS farmer_name,
        u.mobile,
        pc.centre_name,
        s.slot_date,
        s.start_time,
        s.end_time

    FROM bookings b

    JOIN farmers f
        ON b.farmer_id = f.id

    JOIN users u
        ON f.user_id = u.id

    JOIN procurement_centres pc
        ON b.centre_id = pc.id

    JOIN slots s
        ON b.slot_id = s.id

    ORDER BY b.created_at DESC

    LIMIT 10
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | KrishiSetu</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        body {
            margin: 0;
            background: #f7f9f7;
            color: #17352a;
            font-family: Arial, sans-serif;
        }

        .admin-page {
            max-width: 1200px;
            margin: auto;
            padding: 30px 20px 60px;
        }

        /* HEADER */

        .admin-header {
            background: white;
            border-bottom: 1px solid #dfe7e2;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            color: #087443;
            font-size: 24px;
            font-weight: bold;
        }

        .admin-label {
            color: #61716a;
            font-size: 14px;
            margin-top: 3px;
        }

        .logout {
            text-decoration: none;
            color: #087443;
            border: 1px solid #087443;
            padding: 9px 16px;
            border-radius: 8px;
            font-weight: 600;
        }

        /* TITLE */

        .page-title {
            margin-top: 35px;
        }

        .page-title h1 {
            margin-bottom: 8px;
        }

        .page-title p {
            color: #61716a;
        }

        /* STAT CARDS */

        .stats {
            display: grid;
            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-top: 30px;
        }

        .stat-card {
            background: white;
            border: 1px solid #dfe7e2;
            border-radius: 14px;
            padding: 24px;
        }

        .stat-title {
            color: #61716a;
            font-size: 14px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #087443;
            margin-top: 10px;
        }

        /* SECTION */

        .section {
            background: white;
            border: 1px solid #dfe7e2;
            border-radius: 16px;
            margin-top: 25px;
            overflow: hidden;
        }

        .section-header {
            padding: 22px 24px;
            border-bottom: 1px solid #dfe7e2;
        }

        .section-header h2 {
            margin: 0;
        }

        .section-header p {
            margin-bottom: 0;
            color: #61716a;
        }

        /* TABLE */

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #edf1ee;
            white-space: nowrap;
        }

        th {
            background: #f7f9f7;
            font-size: 14px;
        }

        td {
            color: #61716a;
            font-size: 14px;
        }

        .token {
            color: #087443;
            font-weight: bold;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: #e8f5ee;
            color: #087443;
            font-weight: 600;
            font-size: 12px;
        }

        /* QUICK ACTIONS */

        .actions {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);

            gap: 18px;

            padding: 24px;
        }

        .action {
            border: 1px solid #dfe7e2;
            border-radius: 12px;
            padding: 20px;
            text-decoration: none;
            color: #17352a;
            transition: 0.2s;
        }

        .action:hover {
            border-color: #087443;
        }

        .action h3 {
            margin-top: 0;
            color: #087443;
        }

        .action p {
            color: #61716a;
            margin-bottom: 0;
        }

        @media (max-width: 900px) {

            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .actions {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 600px) {

            .admin-header {
                padding: 15px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>


<!-- HEADER -->

<div class="admin-header">

    <div>

        <div class="brand">
            KrishiSetu
        </div>

        <div class="admin-label">
            Admin Portal
        </div>

    </div>

    <a href="../login.php"
       class="logout">

        Logout

    </a>

</div>


<div class="admin-page">


    <!-- TITLE -->

    <div class="page-title">

        <h1>
            Admin Dashboard
        </h1>

        <p>
            Monitor farmers, procurement centres and booking activity.
        </p>

    </div>


    <!-- STATISTICS -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-title">
                Registered Farmers
            </div>

            <div class="stat-number">
                <?= $totalFarmers ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Active Centres
            </div>

            <div class="stat-number">
                <?= $totalCentres ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Total Bookings
            </div>

            <div class="stat-number">
                <?= $totalBookings ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Pending Procurement
            </div>

            <div class="stat-number">
                <?= $pendingProcurement ?>
            </div>

        </div>


    </div>


    <!-- RECENT BOOKINGS -->

    <div class="section">

        <div class="section-header">

            <h2>
                Recent Bookings
            </h2>

            <p>
                Latest farmer procurement bookings.
            </p>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>Token</th>

                        <th>Farmer</th>

                        <th>Mobile</th>

                        <th>Centre</th>

                        <th>Date</th>

                        <th>Time</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($recentBookings->num_rows > 0): ?>

                    <?php while ($row = $recentBookings->fetch_assoc()): ?>

                        <tr>

                            <td class="token">
                                <?= htmlspecialchars($row['booking_token']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['farmer_name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['mobile']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($row['centre_name']) ?>
                            </td>

                            <td>
                                <?= date(
                                    "d M Y",
                                    strtotime($row['slot_date'])
                                ) ?>
                            </td>

                            <td>

                                <?= date(
                                    "h:i A",
                                    strtotime($row['start_time'])
                                ) ?>

                                -

                                <?= date(
                                    "h:i A",
                                    strtotime($row['end_time'])
                                ) ?>

                            </td>

                            <td>

                                <span class="status">

                                    <?= htmlspecialchars(
                                        $row['booking_status']
                                    ) ?>

                                </span>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="7">
                            No bookings found.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- QUICK ACTIONS -->

    <div class="section">

        <div class="section-header">

            <h2>
                Quick Actions
            </h2>

            <p>
                Manage the procurement system.
            </p>

        </div>


        <div class="actions">


            <a href="procurement.php"
               class="action">

                <h3>
                    Procurement Management
                </h3>

                <p>
                    Update farmer procurement status.
                </p>

            </a>


            <a href="centres.php"
               class="action">

                <h3>
                    Centre Management
                </h3>

                <p>
                    View and manage procurement centres.
                </p>

            </a>


            <a href="farmers.php"
               class="action">

                <h3>
                    Farmer Records
                </h3>

                <p>
                    View registered farmer information.
                </p>

            </a>
            <a href="payment.php" class="action">
    <h3>Payment Management</h3>
    <p>Update farmer payment status.</p>
</a>


        </div>

    </div>


</div>

</body>

</html>