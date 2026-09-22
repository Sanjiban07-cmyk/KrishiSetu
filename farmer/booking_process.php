<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$centre_id = isset($_POST['centre_id']) ? (int) $_POST['centre_id'] : 0;
$slot_id = isset($_POST['slot_id']) ? (int) $_POST['slot_id'] : 0;

if ($centre_id <= 0 || $slot_id <= 0) {
    die("Invalid booking request.");
}

/*
 * Find the farmer record using the logged-in user's ID.
 */
$stmt = $conn->prepare("
    SELECT id
    FROM farmers
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$farmer_result = $stmt->get_result();
$farmer = $farmer_result->fetch_assoc();

if (!$farmer) {
    die("Farmer profile not found.");
}

$farmer_id = (int) $farmer['id'];

/*
 * Start transaction so two farmers cannot book
 * the same last available slot at the same time.
 */
$conn->begin_transaction();

try {

    /*
     * Lock the selected slot while processing the booking.
     */
    $stmt = $conn->prepare("
        SELECT *
        FROM slots
        WHERE id = ?
          AND centre_id = ?
        FOR UPDATE
    ");

    $stmt->bind_param("ii", $slot_id, $centre_id);
    $stmt->execute();

    $slot_result = $stmt->get_result();
    $slot = $slot_result->fetch_assoc();

    if (!$slot) {
        throw new Exception("Selected slot was not found.");
    }

   /*
 * Check whether the slot is still available.
 */
if (
    $slot['status'] !== 'available' ||
    $slot['booked_count'] >= $slot['capacity']
) {
    throw new Exception("Sorry, this slot is already full.");
}

/*
 * Check whether the slot date is still valid.
 */
if ($slot['slot_date'] < date('Y-m-d')) {
    throw new Exception("Sorry, this procurement slot has expired.");
}

    /*
     * Check whether this farmer already booked this slot.
     */
    $stmt = $conn->prepare("
        SELECT id
        FROM bookings
        WHERE farmer_id = ?
          AND slot_id = ?
          AND status = 'booked'
        LIMIT 1
    ");

    $stmt->bind_param("ii", $farmer_id, $slot_id);
    $stmt->execute();

    $existing_result = $stmt->get_result();

    if ($existing_result->num_rows > 0) {
        throw new Exception("You have already booked this slot.");
    }

    /*
     * Generate booking token.
     */
    $booking_token = "KS-" . date("Ymd") . "-" . strtoupper(bin2hex(random_bytes(4)));

    /*
     * Create booking.
     */
    $stmt = $conn->prepare("
        INSERT INTO bookings
        (
            farmer_id,
            centre_id,
            slot_id,
            booking_token,
            status
        )
        VALUES (?, ?, ?, ?, 'booked')
    ");

    $stmt->bind_param(
        "iiis",
        $farmer_id,
        $centre_id,
        $slot_id,
        $booking_token
    );

    if (!$stmt->execute()) {
        throw new Exception("Unable to create booking.");
    }

    /*
     * Increase booked count.
     */
    $new_booked_count = $slot['booked_count'] + 1;

    $new_status = (
        $new_booked_count >= $slot['capacity']
    ) ? 'full' : 'available';

    $stmt = $conn->prepare("
        UPDATE slots
        SET booked_count = ?,
            status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "isi",
        $new_booked_count,
        $new_status,
        $slot_id
    );

    if (!$stmt->execute()) {
        throw new Exception("Unable to update slot.");
    }

    /*
     * Everything succeeded.
     */
    $conn->commit();

    /*
     * Get centre details for confirmation.
     */
    $stmt = $conn->prepare("
        SELECT centre_name, venue, district, block
        FROM procurement_centres
        WHERE id = ?
    ");

    $stmt->bind_param("i", $centre_id);
    $stmt->execute();

    $centre_result = $stmt->get_result();
    $centre = $centre_result->fetch_assoc();

} catch (Exception $e) {

    $conn->rollback();

    $error_message = $e->getMessage();
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

    <title>Booking Confirmation - KrishiSetu</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .confirmation-page {
            max-width: 700px;
            margin: 70px auto;
            padding: 20px;
        }

        .confirmation-card {
            background: #ffffff;
            border: 1px solid #dfe7e2;
            border-radius: 16px;
            padding: 35px;
            text-align: center;
        }

        .success-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #e8f5ee;
            color: #087443;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
        }

        .confirmation-card h1 {
            color: #087443;
            margin-bottom: 10px;
        }

        .confirmation-card p {
            color: #61716a;
        }

        .booking-token {
            margin: 25px 0;
            padding: 18px;
            background: #f7f9f7;
            border: 1px dashed #087443;
            border-radius: 10px;
        }

        .booking-token small {
            display: block;
            color: #61716a;
            margin-bottom: 7px;
        }

        .booking-token strong {
            font-size: 24px;
            color: #17352a;
            letter-spacing: 1px;
        }

        .details {
            text-align: left;
            margin-top: 25px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #dfe7e2;
            gap: 20px;
        }

        .detail-row span:first-child {
            color: #61716a;
        }

        .detail-row span:last-child {
            color: #17352a;
            font-weight: 600;
            text-align: right;
        }

        .btn {
            display: inline-block;
            margin-top: 25px;
            padding: 13px 22px;
            background: #087443;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn:hover {
            background: #055c35;
        }

        .error-box {
            background: #fff1f1;
            border: 1px solid #c93434;
            color: #a52222;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="confirmation-page">

    <div class="confirmation-card">

        <?php if (isset($error_message)): ?>

            <div class="error-box">
                <?= htmlspecialchars($error_message) ?>
            </div>

            <a href="centres.php" class="btn">
                Back to Centres
            </a>

        <?php else: ?>

            <div class="success-icon">
                ✓
            </div>

            <h1>Booking Confirmed!</h1>

            <p>
                Your procurement slot has been successfully booked.
            </p>

            <div class="booking-token">

                <small>Booking Token</small>

                <strong>
                    <?= htmlspecialchars($booking_token) ?>
                </strong>

            </div>

            <div class="details">

                <div class="detail-row">
                    <span>Procurement Centre</span>
                    <span>
                        <?= htmlspecialchars($centre['centre_name']) ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span>Date</span>
                    <span>
                        <?= date("d M Y", strtotime($slot['slot_date'])) ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span>Time</span>
                    <span>
                        <?= date("h:i A", strtotime($slot['start_time'])) ?>
                        -
                        <?= date("h:i A", strtotime($slot['end_time'])) ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span>Venue</span>
                    <span>
                        <?= htmlspecialchars($centre['venue']) ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span>Status</span>
                    <span>
                        Confirmed
                    </span>
                </div>

            </div>

            <a href="dashboard.php" class="btn">
                Go to Dashboard
            </a>

        <?php endif; ?>

    </div>

</div>

</body>

</html>