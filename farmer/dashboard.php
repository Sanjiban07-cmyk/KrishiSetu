<?php

session_start();

// Protect dashboard
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "farmer") {
    header("Location: ../login.php");
    exit;
}

$name = $_SESSION["name"];
$language = $_SESSION["language"];

/* Get latest booking for this farmer */
require_once "../config/database.php";

$userId = $_SESSION["user_id"];

$latestBooking = null;

$stmt = $conn->prepare("
    SELECT
        b.booking_token,
        b.status AS booking_status,
        pc.centre_name,
        s.slot_date,
        s.start_time,
        s.end_time,
        p.status AS procurement_status
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
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $latestBooking = $result->fetch_assoc();
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Farmer Dashboard | KrishiSetu</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        body {
            background: var(--background);
        }

        /* Navbar */

        .dashboard-navbar {
            background: white;
            border-bottom: 1px solid var(--border);
        }

        .dashboard-nav-inner {
            width: min(92%, var(--container-width));
            margin: auto;
            min-height: 72px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;
        }

        .dashboard-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dashboard-logo {
            width: 40px;
            height: 40px;
        }

        .dashboard-logo svg {
            width: 100%;
            height: 100%;
        }

        .dashboard-brand-name {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
        }

        .dashboard-brand-subtitle {
            display: block;
            font-size: 11px;
            color: var(--text-secondary);
        }

        .dashboard-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .logout-btn {
            padding: 8px 14px;
            border: 1px solid var(--primary);
            border-radius: var(--radius-md);
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
        }

        .logout-btn:hover {
            background: var(--primary-light);
        }


        /* Main */

        .dashboard-main {
            padding: 45px 0 60px;
        }

        .dashboard-container {
            width: min(92%, var(--container-width));
            margin: auto;
        }

        .welcome-section {
            margin-bottom: 30px;
        }

        .welcome-section h1 {
            font-size: 30px;
            margin-bottom: 5px;
        }

        .welcome-section p {
            color: var(--text-secondary);
        }


        /* Quick action cards */

        .dashboard-grid {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }

        .dashboard-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 24px;

            box-shadow: var(--shadow-sm);

            transition: 0.2s ease;
        }

        .dashboard-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .dashboard-card-icon {
            width: 48px;
            height: 48px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: var(--primary-light);
            border-radius: 10px;

            margin-bottom: 18px;
        }

        .dashboard-card-icon img {
            width: 27px;
            height: 27px;
        }

        .dashboard-card h3 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .dashboard-card p {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 18px;
        }

        .dashboard-card-link {
            color: var(--primary);
            font-size: 14px;
            font-weight: 600;
        }


        /* Information section */

        .info-section {
            margin-top: 35px;

            display: grid;
            grid-template-columns:
                2fr 1fr;

            gap: 20px;
        }

        .info-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 25px;
        }

        .info-card h2 {
            font-size: 20px;
            margin-bottom: 8px;
        }

        .info-card p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .status-empty {
            margin-top: 20px;
            padding: 20px;

            background: var(--background);
            border-radius: var(--radius-md);

            text-align: center;
            color: var(--text-secondary);
            font-size: 14px;
        }


        /* Mobile */

        @media (max-width: 900px) {

            .dashboard-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .info-section {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 600px) {

            .dashboard-nav-inner {
                min-height: 65px;
            }

            .dashboard-brand-subtitle {
                display: none;
            }

            .user-name {
                display: none;
            }

            .dashboard-main {
                padding: 30px 0 45px;
            }

            .welcome-section h1 {
                font-size: 25px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<!-- Navbar -->

<header class="dashboard-navbar">

    <div class="dashboard-nav-inner">


        <!-- Brand -->

        <a href="../index.php"
           class="dashboard-brand">

            <span class="dashboard-logo">

                <svg viewBox="0 0 48 48"
                     xmlns="http://www.w3.org/2000/svg">

                    <path
                        d="M39 7C25 8 13 14 10 26c-2 8 3 13 10 12
                           11-1 17-12 19-31Z"
                        fill="#087443"
                    />

                    <path
                        d="M10 39c7-9 14-15 24-20"
                        fill="none"
                        stroke="#055c35"
                        stroke-width="3"
                        stroke-linecap="round"
                    />

                    <path
                        d="M22 25c-1-5 0-10 4-14"
                        fill="none"
                        stroke="#f2b84b"
                        stroke-width="2.5"
                        stroke-linecap="round"
                    />

                </svg>

            </span>


            <span>

                <span class="dashboard-brand-name">
                    KrishiSetu
                </span>

                <span class="dashboard-brand-subtitle">
                    Farmer Portal
                </span>

            </span>

        </a>


        <!-- User -->

        <div class="dashboard-user">

            <span class="user-name">
                Welcome, <?php echo htmlspecialchars($name); ?>
            </span>

            <a href="logout.php"
               class="logout-btn">
                Logout
            </a>

        </div>

    </div>

</header>



<!-- Main Dashboard -->

<main class="dashboard-main">

    <div class="dashboard-container">


        <!-- Welcome -->

        <section class="welcome-section">

            <h1>
                Farmer Dashboard
            </h1>

            <p>
                Access procurement services and manage your bookings from one place.
            </p>

        </section>



        <!-- Main Actions -->

        <section class="dashboard-grid">


            <!-- Find Centres -->

            <a href="centres.php"
               class="dashboard-card">

                <div class="dashboard-card-icon">

                    <img
                        src="../assets/icons/location.svg"
                        alt="Find Centres">

                </div>

                <h3>
                    Find Procurement Centres
                </h3>

                <p>
                    Discover nearby authorised procurement centres
                    and check their availability.
                </p>

                <span class="dashboard-card-link">
                    Find Centres →
                </span>

            </a>



            <!-- Recommendation -->

            <a href="recommendation.php"
               class="dashboard-card">

                <div class="dashboard-card-icon">

                    <img
                        src="../assets/icons/recommendation.svg"
                        alt="Recommendation">

                </div>

                <h3>
                    Smart Recommendation
                </h3>

                <p>
                    Compare distance, queue, capacity and slot
                    availability.
                </p>

                <span class="dashboard-card-link">
                    Get Recommendation →
                </span>

            </a>



            <!-- Booking -->

            <a href="centres.php"
               class="dashboard-card">

                <div class="dashboard-card-icon">

                    <img
                        src="../assets/icons/calendar.svg"
                        alt="Book Slot">

                </div>

                <h3>
                    Book Your Slot
                </h3>

                <p>
                    Select an available procurement slot and
                    receive your booking token.
                </p>

                <span class="dashboard-card-link">
                    Book Slot →
                </span>

            </a>



            <!-- My Booking -->

            <a href="my_booking.php"
               class="dashboard-card">

                <div class="dashboard-card-icon">

                    <img
                        src="../assets/icons/status.svg"
                        alt="My Booking">

                </div>

                <h3>
                    My Booking
                </h3>

                <p>
                    View your current booking and token details.
                </p>

                <span class="dashboard-card-link">
                    View Booking →
                </span>

            </a>



            <!-- Procurement Status -->

            <a href="procurement_status.php"
               class="dashboard-card">

                <div class="dashboard-card-icon">

                    <img
                        src="../assets/icons/status.svg"
                        alt="Procurement Status">

                </div>

                <h3>
                    Procurement Status
                </h3>

                <p>
                    Track the progress of your procurement process.
                </p>

                <span class="dashboard-card-link">
                    Track Status →
                </span>

            </a>



            <!-- Payment -->

            <a href="payment_status.php"
               class="dashboard-card">

                <div class="dashboard-card-icon">

                    <img
                        src="../assets/icons/status.svg"
                        alt="Payment Status">

                </div>

                <h3>
                    Payment Status
                </h3>

                <p>
                    Check the current status of your procurement payment.
                </p>

                <span class="dashboard-card-link">
                    View Payment →
                </span>

            </a>

        </section>



        <!-- Information -->

        <section class="info-section">


            <div class="info-card">

                <h2>
                    Your Latest Activity
                </h2>

                <p>
                    Your booking and procurement activity will appear here.
                </p>

                <?php if ($latestBooking): ?>

    <div class="status-empty" style="text-align:left;">

        <strong style="color:var(--primary);">
            <?= htmlspecialchars($latestBooking["booking_token"]) ?>
        </strong>

        <p style="margin-top:10px;">
            <strong>Centre:</strong>
            <?= htmlspecialchars($latestBooking["centre_name"]) ?>
        </p>

        <p>
            <strong>Date:</strong>
            <?= date("d M Y", strtotime($latestBooking["slot_date"])) ?>
        </p>

        <p>
            <strong>Time:</strong>
            <?= date("h:i A", strtotime($latestBooking["start_time"])) ?>
            -
            <?= date("h:i A", strtotime($latestBooking["end_time"])) ?>
        </p>

        <p>
            <strong>Booking Status:</strong>
            <?= htmlspecialchars(ucfirst($latestBooking["booking_status"])) ?>
        </p>

        <?php if (!empty($latestBooking["procurement_status"])): ?>

            <p>
                <strong>Procurement:</strong>
                <?= htmlspecialchars(
                    ucfirst($latestBooking["procurement_status"])
                ) ?>
            </p>

        <?php endif; ?>

    </div>

<?php else: ?>

    <div class="status-empty">
        No active booking yet.
    </div>

<?php endif; ?>
            </div>


            <div class="info-card">

                <h2>
                    Need Help?
                </h2>

                <p>
                    KrishiSetu helps you find a suitable procurement
                    centre and available slot.
                </p>

            </div>


        </section>

    </div>

</main>


</body>
</html>