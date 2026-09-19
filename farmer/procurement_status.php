<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/*
 * Get the farmer's latest booking and procurement information.
 */
$sql = "
    SELECT
        b.id AS booking_id,
        b.booking_token,
        b.status AS booking_status,

        pc.centre_name,
        pc.venue,

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

    JOIN farmers f
        ON b.farmer_id = f.id

    JOIN procurement_centres pc
        ON b.centre_id = pc.id

    JOIN slots s
        ON b.slot_id = s.id

    LEFT JOIN procurement p
        ON p.booking_id = b.id

    WHERE f.user_id = ?

    ORDER BY b.created_at DESC

    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

$status = $data['procurement_status'] ?? 'pending';

/*
 * Status information
 */
$statusSteps = [
    'pending' => [
        'title' => 'Booking Confirmed',
        'description' => 'Your procurement booking is confirmed. Please visit the centre at your scheduled time.'
    ],

    'arrived' => [
        'title' => 'Farmer Arrived',
        'description' => 'Your arrival at the procurement centre has been recorded.'
    ],

    'weighed' => [
        'title' => 'Paddy Weighed',
        'description' => 'Your paddy has been weighed at the procurement centre.'
    ],

    'accepted' => [
        'title' => 'Procurement Accepted',
        'description' => 'Your paddy has been accepted for procurement.'
    ]
];

$currentTitle = $statusSteps[$status]['title'] ?? 'Booking Confirmed';
$currentDescription = $statusSteps[$status]['description'] ?? 'Your procurement booking is active.';

$order = [
    'pending' => 1,
    'arrived' => 2,
    'weighed' => 3,
    'accepted' => 4
];

$currentStep = $order[$status] ?? 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Procurement Status | KrishiSetu</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        body {
            margin: 0;
            background: #f7f9f7;
            color: #17352a;
            font-family: Arial, sans-serif;
        }

        .status-page {
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

        .status-card {
            background: #ffffff;
            border: 1px solid #dfe7e2;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        }

        .booking-token {
            background: #e8f5ee;
            border: 1px dashed #087443;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            margin-bottom: 30px;
        }

        .token-label {
            font-size: 14px;
            color: #61716a;
        }

        .token {
            margin-top: 5px;
            font-size: 24px;
            font-weight: bold;
            color: #087443;
        }

        .current-status {
            text-align: center;
            margin-bottom: 35px;
        }

        .status-icon {
            width: 70px;
            height: 70px;
            margin: auto;
            border-radius: 50%;
            background: #e8f5ee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #087443;
            font-size: 34px;
            font-weight: bold;
        }

        .current-status h2 {
            margin: 15px 0 8px;
            color: #087443;
        }

        .current-status p {
            color: #61716a;
        }

        /* Timeline */

        .timeline {
            position: relative;
            margin-top: 30px;
        }

        .timeline-step {
            display: flex;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 25px;
        }

        .step-circle {
            width: 34px;
            height: 34px;
            min-width: 34px;
            border-radius: 50%;
            border: 2px solid #dfe7e2;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8a9892;
            background: white;
            font-weight: bold;
        }

        .timeline-step.completed .step-circle,
        .timeline-step.active .step-circle {
            background: #087443;
            border-color: #087443;
            color: white;
        }

        .step-content h3 {
            margin: 0 0 5px;
            font-size: 17px;
        }

        .step-content p {
            margin: 0;
            color: #61716a;
            font-size: 14px;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 25px;
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

        .no-data {
            text-align: center;
            background: white;
            border: 1px solid #dfe7e2;
            border-radius: 16px;
            padding: 45px;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
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

            .status-card {
                padding: 20px;
            }

        }

    </style>

</head>

<body>

<div class="status-page">

    <a href="dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>

    <h1>Procurement Status</h1>

    <p class="subtitle">
        Track your paddy procurement process step by step.
    </p>


    <?php if ($data): ?>

        <div class="status-card">

            <!-- Booking Token -->

            <div class="booking-token">

                <div class="token-label">
                    Booking Token
                </div>

                <div class="token">
                    <?= htmlspecialchars($data['booking_token']) ?>
                </div>

            </div>


            <!-- Current Status -->

            <div class="current-status">

                <div class="status-icon">
                    ✓
                </div>

                <h2>
                    <?= htmlspecialchars($currentTitle) ?>
                </h2>

                <p>
                    <?= htmlspecialchars($currentDescription) ?>
                </p>

            </div>


            <!-- Timeline -->

            <div class="timeline">

                <?php

                $steps = [
                    1 => [
                        'name' => 'Booking Confirmed',
                        'description' => 'Your procurement slot has been booked.'
                    ],

                    2 => [
                        'name' => 'Farmer Arrived',
                        'description' => 'Arrival at the procurement centre.'
                    ],

                    3 => [
                        'name' => 'Paddy Weighed',
                        'description' => 'Your paddy has been weighed.'
                    ],

                    4 => [
                        'name' => 'Procurement Accepted',
                        'description' => 'Your paddy has been accepted.'
                    ]
                ];

                foreach ($steps as $number => $step):

                    $class = '';

                    if ($number < $currentStep) {
                        $class = 'completed';
                    }

                    if ($number == $currentStep) {
                        $class = 'active';
                    }

                ?>

                    <div class="timeline-step <?= $class ?>">

                        <div class="step-circle">
                            <?= $number ?>
                        </div>

                        <div class="step-content">

                            <h3>
                                <?= $step['name'] ?>
                            </h3>

                            <p>
                                <?= $step['description'] ?>
                            </p>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>


            <!-- Booking Details -->

            <div class="details">

                <div class="detail">

                    <strong>Procurement Centre</strong>

                    <span>
                        <?= htmlspecialchars($data['centre_name']) ?>
                    </span>

                </div>


                <div class="detail">

                    <strong>Venue</strong>

                    <span>
                        <?= htmlspecialchars($data['venue']) ?>
                    </span>

                </div>


                <div class="detail">

                    <strong>Scheduled Date</strong>

                    <span>
                        <?= date(
                            "d M Y",
                            strtotime($data['slot_date'])
                        ) ?>
                    </span>

                </div>


                <div class="detail">

                    <strong>Time</strong>

                    <span>

                        <?= date(
                            "h:i A",
                            strtotime($data['start_time'])
                        ) ?>

                        -

                        <?= date(
                            "h:i A",
                            strtotime($data['end_time'])
                        ) ?>

                    </span>

                </div>


                <?php if ($data['crop_name']): ?>

                    <div class="detail">

                        <strong>Crop</strong>

                        <span>
                            <?= htmlspecialchars($data['crop_name']) ?>
                        </span>

                    </div>

                <?php endif; ?>


                <?php if ($data['quantity']): ?>

                    <div class="detail">

                        <strong>Quantity</strong>

                        <span>

                            <?= htmlspecialchars($data['quantity']) ?>

                            <?= htmlspecialchars($data['unit'] ?? '') ?>

                        </span>

                    </div>

                <?php endif; ?>


            </div>

        </div>

    <?php else: ?>

        <div class="no-data">

            <h2>No Procurement Record</h2>

            <p>
                You don't have an active procurement booking yet.
            </p>

            <a href="centres.php" class="btn">
                Find Procurement Centre
            </a>

        </div>

    <?php endif; ?>

</div>

</body>

</html>