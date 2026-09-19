<?php
session_start();

require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Smart Recommendation Engine
|--------------------------------------------------------------------------
| The score is transparent and rule-based.
|
| Queue             = 30%
| Slot availability = 25%
| Remaining capacity= 20%
| Distance          = 15%
| Purchase date     = 10%
|--------------------------------------------------------------------------
*/

$today = date('Y-m-d');

$sql = "
    SELECT
        id,
        centre_name,
        centre_code,
        centre_type,
        agency_type,
        address,
        venue,
        purchase_date,
        slot_available,
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
    ORDER BY current_queue ASC
";

$result = $conn->query($sql);

$centres = [];


/* -------------------------------------------------
   Calculate recommendation score
------------------------------------------------- */

foreach ($result as $centre) {

    $queue = (int)$centre['current_queue'];
    $capacity = (int)$centre['total_capacity'];
    $bookings = (int)$centre['current_bookings'];

    /*
     * 1. Queue score
     * Lower queue = better.
     */
    $queueScore = max(0, 30 - min($queue, 30));


    /*
     * 2. Slot availability
     */
    $slotScore = ((int)$centre['slot_available'] === 1)
        ? 25
        : 0;


    /*
     * 3. Remaining capacity
     */
    if ($capacity > 0) {

        $remainingPercentage =
            (($capacity - $bookings) / $capacity) * 100;

        $capacityScore =
            ($remainingPercentage / 100) * 20;

    } else {

        $capacityScore = 0;
    }


    /*
     * 4. Distance
     *
     * Farmer coordinates are currently not stored,
     * so prototype distance score uses a neutral value.
     *
     * Once GPS is available this section can use
     * the actual Haversine distance.
     */
    $distanceScore = 7.5;


    /*
     * 5. Purchase date
     *
     * Earlier available purchase date gets a higher score.
     */
    $purchaseScore = 0;

    if (!empty($centre['purchase_date'])) {

        $purchaseDate = strtotime($centre['purchase_date']);
        $todayDate = strtotime($today);

        $daysAway = floor(
            ($purchaseDate - $todayDate) / 86400
        );

        if ($daysAway <= 0) {
            $purchaseScore = 10;
        } elseif ($daysAway === 1) {
            $purchaseScore = 9;
        } elseif ($daysAway === 2) {
            $purchaseScore = 8;
        } elseif ($daysAway === 3) {
            $purchaseScore = 7;
        } else {
            $purchaseScore = 5;
        }
    }


    /*
     * Final score
     */
    $totalScore =
        $queueScore +
        $slotScore +
        $capacityScore +
        $distanceScore +
        $purchaseScore;


    $centre['queue_score'] = $queueScore;
    $centre['slot_score'] = $slotScore;
    $centre['capacity_score'] = $capacityScore;
    $centre['distance_score'] = $distanceScore;
    $centre['purchase_score'] = $purchaseScore;
    $centre['total_score'] = round($totalScore, 1);


    $centres[] = $centre;
}


/* -------------------------------------------------
   Sort by recommendation score
------------------------------------------------- */

usort($centres, function ($a, $b) {

    return $b['total_score'] <=> $a['total_score'];

});


$recommended = $centres[0] ?? null;


/* -------------------------------------------------
   Helper
------------------------------------------------- */

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function formatDate($date)
{
    if (empty($date)) {
        return "Not available";
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

    <title>Smart Recommendation | KrishiSetu</title>

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

        /* HEADER */

        .page-header {
            background: #ffffff;
            border-bottom: 1px solid #dfe7e2;
        }

        .header-inner {
            max-width: 1180px;
            margin: auto;
            padding: 15px 20px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;

            text-decoration: none;
            color: #087443;

            font-size: 22px;
            font-weight: 800;
        }

        .brand-icon {
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

        .portal {
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
        }


        /* PAGE */

        .page {
            max-width: 1180px;
            margin: auto;

            padding: 35px 20px 60px;
        }

        .page-title {
            margin: 0 0 8px;

            font-size: 32px;
        }

        .page-subtitle {
            color: #61716a;

            line-height: 1.6;

            margin-top: 0;
        }


        /* RECOMMENDED CARD */

        .recommended-card {

            margin-top: 28px;

            background: #ffffff;

            border: 2px solid #087443;

            border-radius: 16px;

            padding: 25px;

            position: relative;
        }

        .recommended-label {

            display: inline-block;

            background: #087443;

            color: white;

            padding: 7px 12px;

            border-radius: 7px;

            font-size: 12px;

            font-weight: 700;

            margin-bottom: 14px;
        }

        .recommended-content {

            display: grid;

            grid-template-columns: 1fr auto;

            gap: 30px;

            align-items: center;
        }

        .recommended-name {

            font-size: 25px;

            margin: 0 0 7px;
        }

        .recommended-code {

            color: #8a9892;

            font-size: 13px;

            margin-bottom: 18px;
        }

        .recommendation-reason {

            color: #61716a;

            line-height: 1.6;

            margin-bottom: 18px;
        }

        .score-circle {

            width: 105px;
            height: 105px;

            border-radius: 50%;

            background: #e8f5ee;

            border: 7px solid #087443;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            color: #087443;
        }

        .score-number {

            font-size: 27px;

            font-weight: 800;
        }

        .score-label {

            font-size: 11px;

            color: #61716a;
        }

        .recommendation-button {

            display: inline-block;

            text-decoration: none;

            background: #087443;

            color: white;

            padding: 12px 20px;

            border-radius: 8px;

            font-weight: 700;

            font-size: 14px;
        }

        .recommendation-button:hover {

            background: #055c35;

        }


        /* REASONS */

        .reason-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 12px;

            margin-top: 22px;
        }

        .reason-box {

            background: #f7f9f7;

            border-radius: 9px;

            padding: 13px;
        }

        .reason-title {

            color: #61716a;

            font-size: 12px;

            margin-bottom: 5px;
        }

        .reason-value {

            font-size: 17px;

            font-weight: 700;

            color: #17352a;
        }


        /* HOW SCORE WORKS */

        .section-title {

            margin-top: 38px;

            margin-bottom: 15px;

            font-size: 22px;
        }

        .score-info {

            background: #ffffff;

            border: 1px solid #dfe7e2;

            border-radius: 14px;

            padding: 20px;
        }

        .score-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 11px 0;

            border-bottom: 1px solid #edf1ee;

        }

        .score-row:last-child {

            border-bottom: none;

        }

        .score-factor {

            font-weight: 600;

        }

        .score-weight {

            color: #087443;

            font-weight: 700;

        }


        /* OTHER CENTRES */

        .centre-list {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 16px;

        }

        .centre-card {

            background: #ffffff;

            border: 1px solid #dfe7e2;

            border-radius: 14px;

            padding: 20px;
        }

        .rank {

            display: inline-block;

            background: #f2b84b;

            color: #17352a;

            font-weight: 800;

            font-size: 12px;

            padding: 5px 8px;

            border-radius: 5px;

            margin-bottom: 10px;
        }

        .centre-card h3 {

            margin: 0 0 5px;

            font-size: 18px;
        }

        .centre-card-code {

            color: #8a9892;

            font-size: 12px;

            margin-bottom: 14px;
        }

        .mini-info {

            display: flex;

            justify-content: space-between;

            padding: 8px 0;

            font-size: 13px;

            border-bottom: 1px solid #edf1ee;
        }

        .mini-info:last-child {

            border-bottom: none;

        }

        .mini-label {

            color: #61716a;

        }

        .mini-value {

            font-weight: 700;

        }


        /* PROTOTYPE NOTE */

        .prototype-note {

            margin-top: 25px;

            background: #fff7e6;

            border: 1px solid #f1d79e;

            border-radius: 10px;

            padding: 14px 17px;

            color: #765614;

            font-size: 14px;

            line-height: 1.5;
        }


        /* MOBILE */

        @media (max-width: 800px) {

            .recommended-content {

                grid-template-columns: 1fr;

            }

            .score-circle {

                margin-bottom: 5px;
            }

            .reason-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }

            .centre-list {

                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 550px) {

            .header-inner {

                padding: 12px 15px;
            }

            .portal {

                display: none;
            }

            .page {

                padding: 25px 15px 45px;
            }

            .page-title {

                font-size: 26px;
            }

            .reason-grid {

                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<header class="page-header">

    <div class="header-inner">

        <a href="dashboard.php" class="brand">

            <div class="brand-icon">
                🌾
            </div>

            <span>KrishiSetu</span>

        </a>

        <div class="header-right">

            <span class="portal">
                Farmer Portal
            </span>

            <a
                href="dashboard.php"
                class="back-btn"
            >
                ← Dashboard
            </a>

        </div>

    </div>

</header>


<main class="page">


    <h1 class="page-title">
        Smart Recommendation
    </h1>

    <p class="page-subtitle">
        KrishiSetu compares active procurement centres
        and recommends the option that best balances
        queue, availability, capacity and schedule.
    </p>


    <?php if ($recommended): ?>


        <!-- RECOMMENDED CENTRE -->

        <section class="recommended-card">

            <span class="recommended-label">
                ⭐ Recommended for You
            </span>


            <div class="recommended-content">

                <div>

                    <h2 class="recommended-name">

                        <?= e($recommended['centre_name']) ?>

                    </h2>

                    <div class="recommended-code">

                        Centre Code:
                        <?= e($recommended['centre_code']) ?>

                    </div>


                    <p class="recommendation-reason">

                        This centre currently has a
                        <strong>
                            <?= (int)$recommended['current_queue'] ?>
                            farmer queue
                        </strong>,
                        has
                        <strong>
                            <?= (int)$recommended['slot_available'] === 1
                                ? 'slots available'
                                : 'no available slots'
                            ?>
                        </strong>,
                        and has
                        <strong>
                            <?= max(
                                0,
                                (int)$recommended['total_capacity']
                                - (int)$recommended['current_bookings']
                            ) ?>
                            remaining capacity
                        </strong>.
                        Based on these factors, it currently
                        receives the highest KrishiSetu score.
                    </p>


                    <a
                        href="booking.php?centre_id=<?= (int)$recommended['id'] ?>"
                        class="recommendation-button"
                    >
                        View & Book This Centre →
                    </a>

                </div>


                <div class="score-circle">

                    <span class="score-number">
                        <?= e($recommended['total_score']) ?>
                    </span>

                    <span class="score-label">
                        / 100 Score
                    </span>

                </div>

            </div>


            <!-- SCORE BREAKDOWN -->

            <div class="reason-grid">

                <div class="reason-box">

                    <div class="reason-title">
                        Queue
                    </div>

                    <div class="reason-value">
                        <?= e($recommended['queue_score']) ?>/30
                    </div>

                </div>


                <div class="reason-box">

                    <div class="reason-title">
                        Availability
                    </div>

                    <div class="reason-value">
                        <?= e($recommended['slot_score']) ?>/25
                    </div>

                </div>


                <div class="reason-box">

                    <div class="reason-title">
                        Capacity
                    </div>

                    <div class="reason-value">
                        <?= number_format(
                            $recommended['capacity_score'],
                            1
                        ) ?>/20
                    </div>

                </div>


                <div class="reason-box">

                    <div class="reason-title">
                        Schedule
                    </div>

                    <div class="reason-value">
                        <?= e($recommended['purchase_score']) ?>/10
                    </div>

                </div>

            </div>

        </section>


        <!-- SCORE EXPLANATION -->

        <h2 class="section-title">
            How KrishiSetu decides
        </h2>


        <section class="score-info">

            <div class="score-row">

                <span class="score-factor">
                    Queue condition
                </span>

                <span class="score-weight">
                    30%
                </span>

            </div>


            <div class="score-row">

                <span class="score-factor">
                    Slot availability
                </span>

                <span class="score-weight">
                    25%
                </span>

            </div>


            <div class="score-row">

                <span class="score-factor">
                    Remaining capacity
                </span>

                <span class="score-weight">
                    20%
                </span>

            </div>


            <div class="score-row">

                <span class="score-factor">
                    Distance
                </span>

                <span class="score-weight">
                    15%
                </span>

            </div>


            <div class="score-row">

                <span class="score-factor">
                    Purchase schedule
                </span>

                <span class="score-weight">
                    10%
                </span>

            </div>

        </section>


        <!-- OTHER CENTRES -->

        <h2 class="section-title">
            Other Options
        </h2>


        <div class="centre-list">

            <?php

            $rank = 1;

            foreach ($centres as $centre):

                if ($centre['id'] == $recommended['id']) {
                    continue;
                }

                $rank++;

            ?>

                <article class="centre-card">

                    <span class="rank">
                        #<?= $rank ?> Recommended
                    </span>


                    <h3>
                        <?= e($centre['centre_name']) ?>
                    </h3>


                    <div class="centre-card-code">

                        <?= e($centre['centre_code']) ?>

                    </div>


                    <div class="mini-info">

                        <span class="mini-label">
                            Recommendation Score
                        </span>

                        <span class="mini-value">
                            <?= e($centre['total_score']) ?>/100
                        </span>

                    </div>


                    <div class="mini-info">

                        <span class="mini-label">
                            Current Queue
                        </span>

                        <span class="mini-value">
                            <?= (int)$centre['current_queue'] ?>
                            farmers
                        </span>

                    </div>


                    <div class="mini-info">

                        <span class="mini-label">
                            Capacity
                        </span>

                        <span class="mini-value">

                            <?= (int)$centre['current_bookings'] ?>
                            /
                            <?= (int)$centre['total_capacity'] ?>

                        </span>

                    </div>


                    <div class="mini-info">

                        <span class="mini-label">
                            Purchase Date
                        </span>

                        <span class="mini-value">

                            <?= e(
                                formatDate(
                                    $centre['purchase_date']
                                )
                            ) ?>

                        </span>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>


    <?php else: ?>


        <section class="recommended-card">

            <h2>
                No active procurement centres
            </h2>

            <p>
                There are currently no active centres
                available for recommendation.
            </p>

        </section>


    <?php endif; ?>


    <div class="prototype-note">

        <strong>Prototype Notice:</strong>
        This recommendation engine uses transparent
        rule-based scoring for the hackathon prototype.
        Production deployment can use authorised
        West Bengal e-Paddy data feeds and real farmer
        location data.

    </div>


</main>

</body>

</html>