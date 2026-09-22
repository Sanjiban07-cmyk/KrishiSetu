<?php

session_start();

require_once "../config/database.php";
require_once "../config/language.php";


/* =========================================================
   FARMER ACCESS
========================================================= */

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "farmer"
) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];


/* =========================================================
   HELPER
========================================================= */

function e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}


/* =========================================================
   LANGUAGE
========================================================= */

$currentLanguage = $_SESSION["language"] ?? "en";


$translations = [

    "en" => [

        "title" => "Payment Status",

        "subtitle" =>
            "Track your procurement payment from processing to completion.",

        "back_dashboard" =>
            "Back to Dashboard",

        "payment" =>
            "Procurement Payment",

        "booking_token" =>
            "Booking Token",

        "pending" =>
            "Pending",

        "processing" =>
            "Processing",

        "paid" =>
            "Paid",

        "failed" =>
            "Failed",

        "payment_amount" =>
            "Payment Amount",

        "amount_pending" =>
            "Payment amount will be confirmed after procurement is completed.",

        "crop" =>
            "Crop",

        "quantity" =>
            "Quantity",

        "centre" =>
            "Procurement Centre",

        "venue" =>
            "Venue",

        "reference" =>
            "Payment Reference",

        "paid_on" =>
            "Paid On",

        "not_available" =>
            "Not available yet",

        "not_completed" =>
            "Payment not completed",

        "payment_progress" =>
            "Payment Progress",

        "payment_generated" =>
            "Payment Record",

        "payment_processing" =>
            "Payment Processing",

        "payment_completed" =>
            "Payment Completed",

        "payment_recorded" =>
            "Your payment record has been created.",

        "payment_being_processed" =>
            "Your payment is currently being processed.",

        "payment_success" =>
            "Your payment has been completed successfully.",

        "payment_failed" =>
            "There was an issue processing this payment.",

        "procurement_information" =>
            "Procurement Information",

        "procurement_information_desc" =>
            "Details related to the procurement used to calculate your payment.",

        "no_payment" =>
            "No Payment Record Yet",

        "no_payment_desc" =>
            "Your payment information will appear here after your procurement is processed.",

        "find_centre" =>
            "Find Procurement Centre",

        "active" =>
            "Active",

        "completed" =>
            "Completed"

    ],


    "hi" => [

        "title" =>
            "भुगतान स्थिति",

        "subtitle" =>
            "प्रोसेसिंग से पूरा होने तक अपने खरीद भुगतान को ट्रैक करें।",

        "back_dashboard" =>
            "डैशबोर्ड पर वापस जाएँ",

        "payment" =>
            "खरीद भुगतान",

        "booking_token" =>
            "बुकिंग टोकन",

        "pending" =>
            "लंबित",

        "processing" =>
            "प्रोसेसिंग",

        "paid" =>
            "भुगतान हो गया",

        "failed" =>
            "विफल",

        "payment_amount" =>
            "भुगतान राशि",

        "amount_pending" =>
            "खरीद पूरी होने के बाद भुगतान राशि की पुष्टि की जाएगी।",

        "crop" =>
            "फसल",

        "quantity" =>
            "मात्रा",

        "centre" =>
            "खरीद केंद्र",

        "venue" =>
            "स्थान",

        "reference" =>
            "भुगतान संदर्भ",

        "paid_on" =>
            "भुगतान की तारीख",

        "not_available" =>
            "अभी उपलब्ध नहीं",

        "not_completed" =>
            "भुगतान अभी पूरा नहीं हुआ है",

        "payment_progress" =>
            "भुगतान प्रगति",

        "payment_generated" =>
            "भुगतान रिकॉर्ड",

        "payment_processing" =>
            "भुगतान प्रोसेसिंग",

        "payment_completed" =>
            "भुगतान पूर्ण",

        "payment_recorded" =>
            "आपका भुगतान रिकॉर्ड बनाया गया है।",

        "payment_being_processed" =>
            "आपका भुगतान अभी प्रोसेस किया जा रहा है।",

        "payment_success" =>
            "आपका भुगतान सफलतापूर्वक पूरा हो गया है।",

        "payment_failed" =>
            "इस भुगतान को प्रोसेस करने में समस्या हुई।",

        "procurement_information" =>
            "खरीद जानकारी",

        "procurement_information_desc" =>
            "आपके भुगतान की गणना से संबंधित खरीद विवरण।",

        "no_payment" =>
            "अभी कोई भुगतान रिकॉर्ड नहीं",

        "no_payment_desc" =>
            "आपकी खरीद प्रोसेस होने के बाद भुगतान की जानकारी यहाँ दिखाई देगी।",

        "find_centre" =>
            "खरीद केंद्र खोजें",

        "active" =>
            "सक्रिय",

        "completed" =>
            "पूर्ण"

    ],


    "bn" => [

        "title" =>
            "পেমেন্ট অবস্থা",

        "subtitle" =>
            "প্রসেসিং থেকে সম্পূর্ণ হওয়া পর্যন্ত আপনার ক্রয়ের পেমেন্ট ট্র্যাক করুন।",

        "back_dashboard" =>
            "ড্যাশবোর্ডে ফিরে যান",

        "payment" =>
            "ক্রয় পেমেন্ট",

        "booking_token" =>
            "বুকিং টোকেন",

        "pending" =>
            "অপেক্ষমাণ",

        "processing" =>
            "প্রসেসিং",

        "paid" =>
            "পেমেন্ট সম্পন্ন",

        "failed" =>
            "ব্যর্থ",

        "payment_amount" =>
            "পেমেন্টের পরিমাণ",

        "amount_pending" =>
            "ক্রয় সম্পূর্ণ হওয়ার পরে পেমেন্টের পরিমাণ নিশ্চিত করা হবে।",

        "crop" =>
            "ফসল",

        "quantity" =>
            "পরিমাণ",

        "centre" =>
            "ক্রয় কেন্দ্র",

        "venue" =>
            "স্থান",

        "reference" =>
            "পেমেন্ট রেফারেন্স",

        "paid_on" =>
            "পেমেন্টের তারিখ",

        "not_available" =>
            "এখনও উপলব্ধ নয়",

        "not_completed" =>
            "পেমেন্ট এখনও সম্পূর্ণ হয়নি",

        "payment_progress" =>
            "পেমেন্টের অগ্রগতি",

        "payment_generated" =>
            "পেমেন্ট রেকর্ড",

        "payment_processing" =>
            "পেমেন্ট প্রসেসিং",

        "payment_completed" =>
            "পেমেন্ট সম্পূর্ণ",

        "payment_recorded" =>
            "আপনার পেমেন্ট রেকর্ড তৈরি হয়েছে।",

        "payment_being_processed" =>
            "আপনার পেমেন্ট বর্তমানে প্রসেস করা হচ্ছে।",

        "payment_success" =>
            "আপনার পেমেন্ট সফলভাবে সম্পন্ন হয়েছে।",

        "payment_failed" =>
            "এই পেমেন্ট প্রসেস করতে সমস্যা হয়েছে।",

        "procurement_information" =>
            "ক্রয় সংক্রান্ত তথ্য",

        "procurement_information_desc" =>
            "আপনার পেমেন্টের হিসাবের সঙ্গে সম্পর্কিত ক্রয়ের তথ্য।",

        "no_payment" =>
            "এখনও কোনো পেমেন্ট রেকর্ড নেই",

        "no_payment_desc" =>
            "আপনার ক্রয় প্রসেস হওয়ার পরে পেমেন্টের তথ্য এখানে দেখা যাবে।",

        "find_centre" =>
            "ক্রয় কেন্দ্র খুঁজুন",

        "active" =>
            "সক্রিয়",

        "completed" =>
            "সম্পন্ন"

    ]

];


$t = $translations[$currentLanguage]
    ?? $translations["en"];


function pt(string $key): string
{
    global $t;

    return $t[$key] ?? $key;
}


/* =========================================================
   GET FARMER PAYMENT RECORDS
========================================================= */

$sql = "
    SELECT

        p.id AS payment_id,
        p.amount,
        p.payment_reference,
        p.status AS payment_status,
        p.paid_at,

        pr.crop_name,
        pr.quantity,
        pr.unit,
        pr.status AS procurement_status,

        b.booking_token,

        pc.centre_name,
        pc.venue

    FROM payments p

    INNER JOIN procurement pr
        ON p.procurement_id = pr.id

    INNER JOIN bookings b
        ON pr.booking_id = b.id

    INNER JOIN farmers f
        ON b.farmer_id = f.id

    INNER JOIN procurement_centres pc
        ON b.centre_id = pc.id

    WHERE f.user_id = ?

    ORDER BY p.id DESC
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Unable to load payment information.");
}

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();


/* =========================================================
   STATUS HELPERS
========================================================= */

function paymentStatusClass(string $status): string
{
    return match ($status) {

        "paid" =>
            "status-paid",

        "processing" =>
            "status-processing",

        "failed" =>
            "status-failed",

        default =>
            "status-pending"
    };
}


function paymentStatusText(string $status): string
{
    return match ($status) {

        "paid" =>
            pt("paid"),

        "processing" =>
            pt("processing"),

        "failed" =>
            pt("failed"),

        default =>
            pt("pending")
    };
}


function paymentCurrentStep(string $status): int
{
    return match ($status) {

        "processing" =>
            2,

        "paid" =>
            3,

        "failed" =>
            2,

        default =>
            1
    };
}

?>

<!DOCTYPE html>

<html
    lang="<?= e($currentLanguage) ?>"
>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= e(pt("title")) ?> | KrishiSetu
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        :root {

            --green: #087443;
            --green-dark: #055c35;
            --green-light: #e8f5ee;

            --background: #f7f9f7;
            --surface: #ffffff;

            --text: #17352a;
            --muted: #61716a;
            --light: #8a9892;

            --border: #dfe7e2;

            --warning: #d88900;
            --warning-bg: #fff7e6;

            --blue: #2878b5;
            --blue-bg: #edf6ff;

            --danger: #c93434;
            --danger-bg: #fff0f0;

            --shadow:
                0 8px 28px rgba(20, 65, 45, 0.07);

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
                "Noto Sans",
                "Noto Sans Devanagari",
                sans-serif;

        }


        /* =====================================================
           NAVBAR
        ===================================================== */

        .navbar {

            height: 74px;

            background:
                var(--surface);

            border-bottom:
                1px solid var(--border);

        }


        .nav-inner {

            width:
                min(94%, 1120px);

            height: 100%;

            margin: auto;

            display: flex;

            align-items: center;

            justify-content: space-between;

        }


        .brand {

            display: flex;

            align-items: center;

            gap: 11px;

        }


        .brand-icon {

            width: 42px;
            height: 42px;

            border-radius: 12px;

            background:
                var(--green-light);

            display: flex;

            align-items: center;

            justify-content: center;

        }


        .brand-icon svg {

            width: 29px;
            height: 29px;

        }


        .brand-name {

            color:
                var(--green);

            font-size: 21px;

            font-weight: 800;

        }


        .brand-subtitle {

            display: block;

            color:
                var(--muted);

            font-size: 11px;

            margin-top: 2px;

        }


        .nav-right {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .language {

            padding:
                7px 11px;

            border:
                1px solid var(--border);

            border-radius: 20px;

            color:
                var(--muted);

            font-size: 12px;

            font-weight: 600;

        }


        .back-btn {

            padding:
                9px 14px;

            border:
                1px solid var(--green);

            border-radius: 9px;

            color:
                var(--green);

            font-size: 13px;

            font-weight: 700;

            text-decoration: none;

            transition:
                0.2s ease;

        }


        .back-btn:hover {

            background:
                var(--green);

            color:
                white;

        }


        /* =====================================================
           PAGE
        ===================================================== */

        .page {

            width:
                min(94%, 1050px);

            margin:
                auto;

            padding:
                35px 0 60px;

        }


        .heading {

            margin-bottom:
                25px;

        }


        .heading h1 {

            margin:
                0 0 7px;

            font-size:
                31px;

            line-height:
                1.2;

        }


        .heading p {

            margin:
                0;

            color:
                var(--muted);

            font-size:
                14px;

            line-height:
                1.6;

        }


        /* =====================================================
           PAYMENT CARD
        ===================================================== */

        .payment-card {

            background:
                var(--surface);

            border:
                1px solid var(--border);

            border-radius:
                20px;

            box-shadow:
                var(--shadow);

            overflow:
                hidden;

            margin-bottom:
                22px;

        }


        /* =====================================================
           CARD HEADER
        ===================================================== */

        .payment-header {

            padding:
                22px 26px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                20px;

            border-bottom:
                1px solid var(--border);

        }


        .payment-heading {

            display:
                flex;

            align-items:
                center;

            gap:
                13px;

        }


        .payment-icon {

            width:
                44px;

            height:
                44px;

            border-radius:
                12px;

            background:
                var(--green-light);

            color:
                var(--green);

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                21px;

        }


        .payment-title {

            margin:
                0 0 4px;

            font-size:
                18px;

            font-weight:
                800;

        }


        .token {

            color:
                var(--muted);

            font-size:
                12px;

        }


        .token strong {

            color:
                var(--text);

        }


        /* =====================================================
           STATUS BADGE
        ===================================================== */

        .status {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                6px;

            padding:
                8px 13px;

            border-radius:
                20px;

            font-size:
                12px;

            font-weight:
                800;

            white-space:
                nowrap;

        }


        .status-dot {

            width:
                7px;

            height:
                7px;

            border-radius:
                50%;

            background:
                currentColor;

        }


        .status-pending {

            background:
                var(--warning-bg);

            color:
                var(--warning);

        }


        .status-processing {

            background:
                var(--blue-bg);

            color:
                var(--blue);

        }


        .status-paid {

            background:
                var(--green-light);

            color:
                var(--green);

        }


        .status-failed {

            background:
                var(--danger-bg);

            color:
                var(--danger);

        }


        /* =====================================================
           AMOUNT
        ===================================================== */

        .amount-section {

            padding:
                24px 26px;

        }


        .amount-box {

            background:
                linear-gradient(
                    120deg,
                    #eff9f2,
                    #e6f5eb
                );

            border:
                1px solid #d3e8da;

            border-radius:
                15px;

            padding:
                22px;

        }


        .amount-label {

            color:
                var(--muted);

            font-size:
                12px;

            margin-bottom:
                7px;

        }


        .amount {

            color:
                var(--green);

            font-size:
                34px;

            font-weight:
                800;

        }


        .amount-note {

            margin-top:
                7px;

            color:
                var(--muted);

            font-size:
                12px;

            line-height:
                1.5;

        }


        /* =====================================================
           PAYMENT PROGRESS
        ===================================================== */

        .progress-section {

            padding:
                0 26px 25px;

        }


        .section-title {

            margin:
                0 0 15px;

            font-size:
                16px;

            font-weight:
                800;

        }


        .payment-progress {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            position:
                relative;

        }


        .progress-line {

            position:
                absolute;

            top:
                17px;

            left:
                16.66%;

            right:
                16.66%;

            height:
                3px;

            background:
                #e1e8e4;

        }


        .progress-fill {

            height:
                100%;

            background:
                var(--green);

            transition:
                0.3s ease;

        }


        .progress-item {

            position:
                relative;

            z-index:
                2;

            text-align:
                center;

        }


        .progress-circle {

            width:
                36px;

            height:
                36px;

            margin:
                auto auto 8px;

            border-radius:
                50%;

            background:
                white;

            border:
                2px solid #dfe7e2;

            color:
                var(--light);

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                12px;

            font-weight:
                800;

        }


        .progress-item.done
        .progress-circle,

        .progress-item.active
        .progress-circle {

            background:
                var(--green);

            border-color:
                var(--green);

            color:
                white;

        }


        .progress-label {

            display:
                block;

            color:
                var(--text);

            font-size:
                11px;

            font-weight:
                700;

        }


        /* =====================================================
           INFORMATION
        ===================================================== */

        .information {

            padding:
                0 26px 28px;

        }


        .info-heading {

            margin-bottom:
                14px;

        }


        .info-heading h2 {

            margin:
                0 0 5px;

            font-size:
                18px;

        }


        .info-heading p {

            margin:
                0;

            color:
                var(--muted);

            font-size:
                12px;

        }


        .info-grid {

            display:
                grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap:
                12px;

        }


        .info-box {

            padding:
                15px;

            background:
                #fafcfb;

            border:
                1px solid var(--border);

            border-radius:
                11px;

        }


        .info-label {

            display:
                block;

            color:
                var(--light);

            font-size:
                11px;

            margin-bottom:
                6px;

        }


        .info-value {

            color:
                var(--text);

            font-size:
                13px;

            font-weight:
                700;

            line-height:
                1.45;

        }


        .reference {

            color:
                var(--green);

            word-break:
                break-word;

        }


        /* =====================================================
           EMPTY STATE
        ===================================================== */

        .empty {

            background:
                white;

            border:
                1px solid var(--border);

            border-radius:
                20px;

            box-shadow:
                var(--shadow);

            padding:
                55px 25px;

            text-align:
                center;

        }


        .empty-icon {

            width:
                65px;

            height:
                65px;

            margin:
                0 auto 15px;

            border-radius:
                50%;

            background:
                var(--green-light);

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                28px;

        }


        .empty h2 {

            margin:
                0 0 8px;

            font-size:
                21px;

        }


        .empty p {

            margin:
                0;

            color:
                var(--muted);

            font-size:
                13px;

        }


        .primary-btn {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            margin-top:
                20px;

            padding:
                11px 17px;

            background:
                var(--green);

            color:
                white;

            border-radius:
                9px;

            font-size:
                13px;

            font-weight:
                800;

            text-decoration:
                none;

        }


        .primary-btn:hover {

            background:
                var(--green-dark);

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 700px) {

            .navbar {
                height: 66px;
            }


            .brand-subtitle,
            .language {
                display: none;
            }


            .brand-name {
                font-size: 19px;
            }


            .back-btn {
                padding:
                    8px 10px;

                font-size:
                    12px;
            }


            .page {

                width:
                    92%;

                padding:
                    25px 0 45px;

            }


            .heading h1 {

                font-size:
                    26px;

            }


            .payment-header {

                align-items:
                    flex-start;

                flex-direction:
                    column;

                padding:
                    18px;

            }


            .amount-section,
            .progress-section,
            .information {

                padding-left:
                    18px;

                padding-right:
                    18px;

            }


            .payment-header {

                padding:
                    18px;

            }


            .payment-heading {

                align-items:
                    flex-start;

            }


            .status {

                align-self:
                    flex-start;

            }


            .amount {

                font-size:
                    29px;

            }


            .info-grid {

                grid-template-columns:
                    1fr;

            }


            .progress-label {

                font-size:
                    9px;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<header class="navbar">

    <div class="nav-inner">


        <div class="brand">

            <div class="brand-icon">

                <svg
                    viewBox="0 0 48 48"
                    xmlns="http://www.w3.org/2000/svg"
                >

                    <path
                        d="M39 7C25 8 13 14 10 26c-2 8 3 13 10 12 11-1 17-12 19-31Z"
                        fill="#087443"
                    />

                    <path
                        d="M10 39c7-9 14-15 24-20"
                        fill="none"
                        stroke="#055c35"
                        stroke-width="3"
                        stroke-linecap="round"
                    />

                </svg>

            </div>


            <div>

                <div class="brand-name">
                    KrishiSetu
                </div>

                <span class="brand-subtitle">
                    Farmer Portal
                </span>

            </div>

        </div>


        <div class="nav-right">

            <span class="language">

                <?php

                echo e(
                    $currentLanguage === "hi"
                        ? "हिन्दी"
                        : (
                            $currentLanguage === "bn"
                                ? "বাংলা"
                                : "English"
                        )
                );

                ?>

            </span>


            <a
                href="dashboard.php"
                class="back-btn"
            >

                ←
                <?= e(pt("back_dashboard")) ?>

            </a>

        </div>

    </div>

</header>



<!-- =========================================================
     MAIN
========================================================= -->

<main class="page">


    <section class="heading">

        <h1>
            <?= e(pt("title")) ?>
        </h1>

        <p>
            <?= e(pt("subtitle")) ?>
        </p>

    </section>



    <?php if ($result->num_rows > 0): ?>


        <?php while ($row = $result->fetch_assoc()): ?>


            <?php

            $paymentStatus =
                strtolower(
                    trim(
                        $row["payment_status"]
                        ?? "pending"
                    )
                );


            $step =
                paymentCurrentStep(
                    $paymentStatus
                );


            $statusClass =
                paymentStatusClass(
                    $paymentStatus
                );


            $statusLabel =
                paymentStatusText(
                    $paymentStatus
                );


            $amount =
                (float)(
                    $row["amount"]
                    ?? 0
                );


            $quantity =
                (float)(
                    $row["quantity"]
                    ?? 0
                );


            $unit =
                $row["unit"]
                ?? "kg";


            ?>



            <!-- =================================================
                 PAYMENT CARD
            ================================================== -->

            <article class="payment-card">


                <!-- HEADER -->

                <div class="payment-header">


                    <div class="payment-heading">


                        <div class="payment-icon">

                            ₹

                        </div>


                        <div>

                            <div class="payment-title">

                                <?= e(
                                    pt("payment")
                                ) ?>

                            </div>


                            <div class="token">

                                <?= e(
                                    pt("booking_token")
                                ) ?>:

                                <strong>

                                    <?= e(
                                        $row["booking_token"]
                                    ) ?>

                                </strong>

                            </div>

                        </div>

                    </div>



                    <span
                        class="
                            status
                            <?= e($statusClass) ?>
                        "
                    >

                        <span
                            class="status-dot"
                        ></span>

                        <?= e(
                            $statusLabel
                        ) ?>

                    </span>


                </div>



                <!-- AMOUNT -->

                <div class="amount-section">


                    <div class="amount-box">


                        <div class="amount-label">

                            <?= e(
                                pt("payment_amount")
                            ) ?>

                        </div>


                        <div class="amount">

                            ₹<?= number_format(
                                $amount,
                                2
                            ) ?>

                        </div>


                        <?php if (
                            $paymentStatus ===
                            "pending"
                            &&
                            $amount <= 0
                        ): ?>

                            <div class="amount-note">

                                <?= e(
                                    pt("amount_pending")
                                ) ?>

                            </div>

                        <?php endif; ?>


                    </div>

                </div>



                <!-- PAYMENT PROGRESS -->

                <div class="progress-section">


                    <h3 class="section-title">

                        <?= e(
                            pt("payment_progress")
                        ) ?>

                    </h3>


                    <div class="payment-progress">


                        <?php

                        $fill =
                            0;

                        if ($step === 2) {
                            $fill = 50;
                        }

                        if ($step === 3) {
                            $fill = 100;
                        }

                        ?>


                        <div class="progress-line">

                            <div
                                class="progress-fill"
                                style="
                                    width:
                                    <?= $fill ?>%;
                                "
                            ></div>

                        </div>



                        <!-- STEP 1 -->

                        <div
                            class="
                                progress-item
                                <?= $step >= 1
                                    ? "done"
                                    : ""
                                ?>
                            "
                        >

                            <div class="progress-circle">

                                <?= $step > 1
                                    ? "✓"
                                    : "1" ?>

                            </div>


                            <span class="progress-label">

                                <?= e(
                                    pt("payment_generated")
                                ) ?>

                            </span>

                        </div>



                        <!-- STEP 2 -->

                        <div
                            class="
                                progress-item
                                <?= $step >= 2
                                    ? "active"
                                    : ""
                                ?>
                            "
                        >

                            <div class="progress-circle">

                                <?= $step > 2
                                    ? "✓"
                                    : "2" ?>

                            </div>


                            <span class="progress-label">

                                <?= e(
                                    pt("payment_processing")
                                ) ?>

                            </span>

                        </div>



                        <!-- STEP 3 -->

                        <div
                            class="
                                progress-item
                                <?= $step >= 3
                                    ? "active"
                                    : ""
                                ?>
                            "
                        >

                            <div class="progress-circle">

                                <?=
                                    $paymentStatus ===
                                    "paid"
                                        ? "✓"
                                        : "3"
                                ?>

                            </div>


                            <span class="progress-label">

                                <?= e(
                                    pt("payment_completed")
                                ) ?>

                            </span>

                        </div>


                    </div>

                </div>



                <!-- INFORMATION -->

                <div class="information">


                    <div class="info-heading">

                        <h2>

                            <?= e(
                                pt(
                                    "procurement_information"
                                )
                            ) ?>

                        </h2>


                        <p>

                            <?= e(
                                pt(
                                    "procurement_information_desc"
                                )
                            ) ?>

                        </p>

                    </div>



                    <div class="info-grid">


                        <!-- CROP -->

                        <div class="info-box">

                            <span class="info-label">

                                <?= e(
                                    pt("crop")
                                ) ?>

                            </span>


                            <span class="info-value">

                                <?= e(
                                    $row["crop_name"]
                                    ?? "Paddy"
                                ) ?>

                            </span>

                        </div>



                        <!-- QUANTITY -->

                        <div class="info-box">

                            <span class="info-label">

                                <?= e(
                                    pt("quantity")
                                ) ?>

                            </span>


                            <span class="info-value">

                                <?= e(
                                    number_format(
                                        $quantity,
                                        2
                                    )
                                ) ?>

                                <?= e(
                                    $unit
                                ) ?>

                            </span>

                        </div>



                        <!-- CENTRE -->

                        <div class="info-box">

                            <span class="info-label">

                                <?= e(
                                    pt("centre")
                                ) ?>

                            </span>


                            <span class="info-value">

                                <?= e(
                                    $row["centre_name"]
                                ) ?>

                            </span>

                        </div>



                        <!-- VENUE -->

                        <div class="info-box">

                            <span class="info-label">

                                <?= e(
                                    pt("venue")
                                ) ?>

                            </span>


                            <span class="info-value">

                                <?= e(
                                    $row["venue"]
                                    ?? "—"
                                ) ?>

                            </span>

                        </div>



                        <!-- REFERENCE -->

                        <div class="info-box">

                            <span class="info-label">

                                <?= e(
                                    pt("reference")
                                ) ?>

                            </span>


                            <span
                                class="
                                    info-value
                                    reference
                                "
                            >

                                <?php if (
                                    !empty(
                                        $row[
                                            "payment_reference"
                                        ]
                                    )
                                ): ?>

                                    <?= e(
                                        $row[
                                            "payment_reference"
                                        ]
                                    ) ?>

                                <?php else: ?>

                                    <?= e(
                                        pt(
                                            "not_available"
                                        )
                                    ) ?>

                                <?php endif; ?>

                            </span>

                        </div>



                        <!-- PAID ON -->

                        <div class="info-box">

                            <span class="info-label">

                                <?= e(
                                    pt("paid_on")
                                ) ?>

                            </span>


                            <span class="info-value">


                                <?php if (
                                    !empty(
                                        $row["paid_at"]
                                    )
                                ): ?>

                                    <?= e(
                                        date(
                                            "d M Y, h:i A",
                                            strtotime(
                                                $row[
                                                    "paid_at"
                                                ]
                                            )
                                        )
                                    ) ?>

                                <?php else: ?>

                                    <?= e(
                                        pt(
                                            "not_completed"
                                        )
                                    ) ?>

                                <?php endif; ?>


                            </span>

                        </div>


                    </div>

                </div>


            </article>


        <?php endwhile; ?>


    <?php else: ?>


        <!-- =================================================
             EMPTY
        ================================================== -->

        <section class="empty">


            <div class="empty-icon">

                ₹

            </div>


            <h2>

                <?= e(
                    pt("no_payment")
                ) ?>

            </h2>


            <p>

                <?= e(
                    pt("no_payment_desc")
                ) ?>

            </p>


            <a
                href="centres.php"
                class="primary-btn"
            >

                <?= e(
                    pt("find_centre")
                ) ?>

                →

            </a>


        </section>


    <?php endif; ?>


</main>


</body>

</html>

<?php

$stmt->close();

?>