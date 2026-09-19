<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit;
}

$centre_id = isset($_GET['centre_id']) ? (int)$_GET['centre_id'] : 0;

if ($centre_id <= 0) {
    die("Invalid procurement centre.");
}

/* Get centre information */
$stmt = $conn->prepare("
    SELECT *
    FROM procurement_centres
    WHERE id = ? AND status = 'active'
");
$stmt->bind_param("i", $centre_id);
$stmt->execute();

$result = $stmt->get_result();
$centre = $result->fetch_assoc();

if (!$centre) {
    die("Procurement centre not found.");
}

/* Get available slots */
$stmt = $conn->prepare("
    SELECT *
    FROM slots
    WHERE centre_id = ?
      AND status = 'available'
      AND booked_count < capacity
      AND slot_date >= CURDATE()
    ORDER BY slot_date ASC, start_time ASC
");
$stmt->bind_param("i", $centre_id);
$stmt->execute();

$slots = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Book Procurement Slot - KrishiSetu</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .booking-page {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .booking-header {
            margin-bottom: 25px;
        }

        .booking-header h1 {
            color: #17352a;
            margin-bottom: 8px;
        }

        .booking-header p {
            color: #61716a;
        }

        .centre-box {
            background: #ffffff;
            border: 1px solid #dfe7e2;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .centre-box h2 {
            margin-top: 0;
            color: #087443;
        }

        .centre-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .info-item {
            padding: 12px;
            background: #f7f9f7;
            border-radius: 8px;
        }

        .info-item strong {
            display: block;
            color: #17352a;
            margin-bottom: 4px;
        }

        .info-item span {
            color: #61716a;
        }

        .slots-box {
            background: #ffffff;
            border: 1px solid #dfe7e2;
            border-radius: 14px;
            padding: 25px;
        }

        .slots-box h2 {
            color: #17352a;
            margin-top: 0;
        }

        .slot-list {
            display: grid;
            gap: 14px;
        }

        .slot-option {
            border: 1px solid #dfe7e2;
            border-radius: 10px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .slot-option:hover {
            border-color: #087443;
            background: #f7fbf8;
        }

        .slot-info strong {
            display: block;
            color: #17352a;
            margin-bottom: 5px;
        }

        .slot-info span {
            color: #61716a;
            font-size: 14px;
        }

        .slot-radio input {
            width: 20px;
            height: 20px;
            accent-color: #087443;
        }

        .book-btn {
            margin-top: 20px;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #087443;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .book-btn:hover {
            background: #055c35;
        }

        .no-slots {
            padding: 20px;
            background: #fff7e6;
            border: 1px solid #f2b84b;
            border-radius: 10px;
            color: #795600;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #087443;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 700px) {
            .centre-info {
                grid-template-columns: 1fr;
            }

            .slot-option {
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

<div class="booking-page">

    <a href="centres.php" class="back-link">
        ← Back to Procurement Centres
    </a>

    <div class="booking-header">
        <h1>Book Procurement Slot</h1>
        <p>Select a suitable date and time slot for paddy procurement.</p>
    </div>

    <!-- Centre Information -->
    <div class="centre-box">

        <h2>
            <?= htmlspecialchars($centre['centre_name']) ?>
        </h2>

        <div class="centre-info">

            <div class="info-item">
                <strong>Centre Code</strong>
                <span><?= htmlspecialchars($centre['centre_code']) ?></span>
            </div>

            <div class="info-item">
                <strong>Centre Type</strong>
                <span><?= htmlspecialchars($centre['centre_type']) ?></span>
            </div>

            <div class="info-item">
                <strong>District</strong>
                <span><?= htmlspecialchars($centre['district']) ?></span>
            </div>

            <div class="info-item">
                <strong>Block</strong>
                <span><?= htmlspecialchars($centre['block']) ?></span>
            </div>

            <div class="info-item">
                <strong>Agency</strong>
                <span><?= htmlspecialchars($centre['agency_type']) ?></span>
            </div>

            <div class="info-item">
                <strong>Venue</strong>
                <span><?= htmlspecialchars($centre['venue']) ?></span>
            </div>

        </div>

    </div>

    <!-- Slots -->
    <div class="slots-box">

        <h2>Available Slots</h2>

        <?php if ($slots->num_rows > 0): ?>

            <form action="booking_process.php" method="POST">

                <input
                    type="hidden"
                    name="centre_id"
                    value="<?= $centre_id ?>"
                >

                <div class="slot-list">

                    <?php while ($slot = $slots->fetch_assoc()): ?>

                        <?php
                        $remaining = $slot['capacity'] - $slot['booked_count'];
                        ?>

                        <label class="slot-option">

                            <div class="slot-info">

                                <strong>
                                    <?= date("d M Y", strtotime($slot['slot_date'])) ?>
                                </strong>

                                <span>
                                    <?= date("h:i A", strtotime($slot['start_time'])) ?>
                                    -
                                    <?= date("h:i A", strtotime($slot['end_time'])) ?>
                                    &nbsp; • &nbsp;
                                    <?= $remaining ?> slots remaining
                                </span>

                            </div>

                            <div class="slot-radio">
                                <input
                                    type="radio"
                                    name="slot_id"
                                    value="<?= $slot['id'] ?>"
                                    required
                                >
                            </div>

                        </label>

                    <?php endwhile; ?>

                </div>

                <button type="submit" class="book-btn">
                    Confirm Booking
                </button>

            </form>

        <?php else: ?>

            <div class="no-slots">
                No available procurement slots are currently available for this centre.
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>