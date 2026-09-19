<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        b.booking_token,
        b.status AS booking_status,
        pc.centre_name,
        pc.centre_code,
        pc.venue,
        s.slot_date,
        s.start_time,
        s.end_time
    FROM bookings b
    JOIN farmers f ON b.farmer_id = f.id
    JOIN procurement_centres pc ON b.centre_id = pc.id
    JOIN slots s ON b.slot_id = s.id
    WHERE f.user_id = ?
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Booking | KrishiSetu</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        body {
            background: #f7f9f7;
            margin: 0;
            font-family: Arial, sans-serif;
            color: #17352a;
        }

        .booking-page {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
        }

        .back-link {
            color: #087443;
            text-decoration: none;
            font-weight: 600;
        }

        h1 {
            margin-top: 30px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #61716a;
            margin-bottom: 30px;
        }

        .booking-card {
            background: #ffffff;
            border: 1px solid #dfe7e2;
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        }

        .token {
            background: #e8f5ee;
            border: 1px dashed #087443;
            padding: 18px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
        }

        .token-label {
            color: #61716a;
            font-size: 14px;
        }

        .token-value {
            color: #087443;
            font-size: 24px;
            font-weight: bold;
            margin-top: 6px;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail {
            background: #f7f9f7;
            padding: 15px;
            border-radius: 10px;
        }

        .detail strong {
            display: block;
            margin-bottom: 6px;
        }

        .detail span {
            color: #61716a;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            background: #e8f5ee;
            color: #087443;
            font-weight: bold;
            text-transform: capitalize;
        }

        .no-booking {
            background: white;
            border: 1px solid #dfe7e2;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            color: #61716a;
        }

        .btn {
            display: inline-block;
            margin-top: 25px;
            background: #087443;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 700px) {
            .details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="booking-page">

    <a href="dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>

    <h1>My Booking</h1>
    <p class="subtitle">
        View your procurement booking and slot details.
    </p>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($booking = $result->fetch_assoc()): ?>

            <div class="booking-card">

                <div class="token">
                    <div class="token-label">Booking Token</div>
                    <div class="token-value">
                        <?= htmlspecialchars($booking['booking_token']) ?>
                    </div>
                </div>

                <div class="details">

                    <div class="detail">
                        <strong>Procurement Centre</strong>
                        <span>
                            <?= htmlspecialchars($booking['centre_name']) ?>
                        </span>
                    </div>

                    <div class="detail">
                        <strong>Centre Code</strong>
                        <span>
                            <?= htmlspecialchars($booking['centre_code']) ?>
                        </span>
                    </div>

                    <div class="detail">
                        <strong>Date</strong>
                        <span>
                            <?= date("d M Y", strtotime($booking['slot_date'])) ?>
                        </span>
                    </div>

                    <div class="detail">
                        <strong>Time</strong>
                        <span>
                            <?= date("h:i A", strtotime($booking['start_time'])) ?>
                            -
                            <?= date("h:i A", strtotime($booking['end_time'])) ?>
                        </span>
                    </div>

                    <div class="detail">
                        <strong>Venue</strong>
                        <span>
                            <?= htmlspecialchars($booking['venue']) ?>
                        </span>
                    </div>

                    <div class="detail">
                        <strong>Status</strong>
                        <span class="status">
                            <?= htmlspecialchars($booking['booking_status']) ?>
                        </span>
                    </div>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="no-booking">
            <h2>No Booking Found</h2>
            <p>You have not made any procurement booking yet.</p>

            <a href="centres.php" class="btn">
                Find Procurement Centre
            </a>
        </div>

    <?php endif; ?>

</div>

</body>
</html>