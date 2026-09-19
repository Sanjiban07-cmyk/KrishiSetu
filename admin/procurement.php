<?php

session_start();

require_once "../config/database.php";

/* Admin access only */
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

/* Get all bookings with procurement status */
$sql = "
    SELECT
        b.id AS booking_id,
        b.booking_token,
        b.status AS booking_status,

        u.name AS farmer_name,
        u.mobile AS farmer_mobile,

        pc.centre_name,

        s.slot_date,
        s.start_time,
        s.end_time,

        p.id AS procurement_id,
        p.crop_name,
        p.quantity,
        p.unit,
        p.status AS procurement_status,
        p.updated_at

    FROM bookings b

    INNER JOIN users u
        ON b.farmer_id = u.id

    INNER JOIN procurement_centres pc
        ON b.centre_id = pc.id

    INNER JOIN slots s
        ON b.slot_id = s.id

    LEFT JOIN procurement p
        ON b.id = p.booking_id

    ORDER BY b.id DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Procurement Management | KrishiSetu</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        body {
            background: var(--background);
        }

        .admin-page {
            min-height: 100vh;
        }

        .admin-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 22px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            color: var(--primary);
            font-size: 25px;
            font-weight: 700;
        }

        .brand-subtitle {
            color: var(--text-secondary);
            font-size: 14px;
            margin-top: 3px;
        }

        .logout-btn {
            padding: 10px 18px;
            border: 1px solid var(--primary);
            border-radius: 10px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .logout-btn:hover {
            background: var(--primary-light);
        }

        .page-container {
            max-width: 1250px;
            margin: 0 auto;
            padding: 50px 25px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .page-title {
            font-size: 32px;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .page-description {
            color: var(--text-secondary);
            margin-bottom: 30px;
        }

        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow-x: auto;
            box-shadow: var(--shadow-sm);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
        }

        th {
            background: var(--background);
            color: var(--text-primary);
            text-align: left;
            padding: 16px;
            font-size: 14px;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            color: var(--text-secondary);
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .token {
            color: var(--primary);
            font-weight: 700;
        }

        .farmer-name {
            color: var(--text-primary);
            font-weight: 600;
        }

        .status-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .status-select {
            padding: 9px 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            font-size: 14px;
        }

        .update-btn {
            border: none;
            background: var(--primary);
            color: white;
            padding: 9px 13px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .update-btn:hover {
            background: var(--primary-dark);
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .pending {
            background: #fff7e6;
            color: #b06b00;
        }

        .arrived {
            background: #eaf4ff;
            color: #2878b5;
        }

        .weighed {
            background: #f1ebff;
            color: #7048a8;
        }

        .accepted {
            background: #e8f5ee;
            color: #16834d;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: var(--text-secondary);
        }

        @media (max-width: 700px) {

            .admin-header {
                padding: 18px;
            }

            .page-container {
                padding: 35px 15px;
            }

            .page-title {
                font-size: 26px;
            }

        }

    </style>

</head>

<body>

<div class="admin-page">

    <!-- Header -->

    <header class="admin-header">

        <div>

            <div class="brand">
                KrishiSetu
            </div>

            <div class="brand-subtitle">
                Admin Portal
            </div>

        </div>

        <a href="../login.php"
           class="logout-btn">
            Logout
        </a>

    </header>


    <!-- Main -->

    <main class="page-container">

        <a href="dashboard.php"
           class="back-link">
            ← Back to Admin Dashboard
        </a>


        <h1 class="page-title">
            Procurement Management
        </h1>

        <p class="page-description">
            Monitor farmer procurement and update the current procurement status.
        </p>


        <div class="table-card">

            <?php if ($result && $result->num_rows > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>Booking Token</th>

                            <th>Farmer</th>

                            <th>Mobile</th>

                            <th>Centre</th>

                            <th>Schedule</th>

                            <th>Current Status</th>

                            <th>Update Status</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <?php

                        $current_status =
                            $row["procurement_status"]
                            ?? "pending";

                        ?>

                        <tr>

                            <td>

                                <div class="token">
                                    <?= htmlspecialchars($row["booking_token"]) ?>
                                </div>

                            </td>


                            <td>

                                <div class="farmer-name">
                                    <?= htmlspecialchars($row["farmer_name"]) ?>
                                </div>

                            </td>


                            <td>
                                <?= htmlspecialchars($row["farmer_mobile"]) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars($row["centre_name"]) ?>
                            </td>


                            <td>

                                <?= date(
                                    "d M Y",
                                    strtotime($row["slot_date"])
                                ) ?>

                                <br>

                                <?= date(
                                    "h:i A",
                                    strtotime($row["start_time"])
                                ) ?>

                                -
                                <?= date(
                                    "h:i A",
                                    strtotime($row["end_time"])
                                ) ?>

                            </td>


                            <td>

                                <span class="status-badge <?= htmlspecialchars($current_status) ?>">

                                    <?= ucfirst($current_status) ?>

                                </span>

                            </td>


                            <td>

                                <form
                                    action="procurement_update.php"
                                    method="POST"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?= (int)$row["booking_id"] ?>"
                                    >

                                    <select
                                        name="status"
                                        class="status-select"
                                    >

                                        <option
                                            value="pending"
                                            <?= $current_status === "pending" ? "selected" : "" ?>
                                        >
                                            Pending
                                        </option>

                                        <option
                                            value="arrived"
                                            <?= $current_status === "arrived" ? "selected" : "" ?>
                                        >
                                            Farmer Arrived
                                        </option>

                                        <option
                                            value="weighed"
                                            <?= $current_status === "weighed" ? "selected" : "" ?>
                                        >
                                            Paddy Weighed
                                        </option>

                                        <option
                                            value="accepted"
                                            <?= $current_status === "accepted" ? "selected" : "" ?>
                                        >
                                            Procurement Accepted
                                        </option>

                                    </select>


                                    <button
                                        type="submit"
                                        class="update-btn"
                                    >
                                        Update
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty-state">

                    <h3>
                        No procurement records found
                    </h3>

                    <p>
                        Farmer bookings will appear here.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </main>

</div>

</body>

</html>