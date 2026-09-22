<?php

session_start();

/* =========================================================
   FARMER ACCESS PROTECTION
========================================================= */

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "farmer"
) {
    header("Location: ../login.php");
    exit;
}


/* =========================================================
   LANGUAGE SYSTEM
========================================================= */

require_once "../config/database.php";
require_once "../config/language.php";

$name = $_SESSION["name"] ?? "Farmer";
$userId = (int)$_SESSION["user_id"];

$currentLanguage = $_SESSION["language"] ?? "en";


/* =========================================================
   SMALL DASHBOARD TRANSLATION HELPER
   These are only additional dashboard UI phrases.
========================================================= */

$dashboardText = [

    "en" => [

        "welcome_back" => "Welcome back, {name}",
        "dashboard_intro" =>
            "Manage your procurement journey easily and efficiently with KrishiSetu.",

        "today_question" =>
            "What would you like to do today?",

        "today_description" =>
            "Choose a service to manage your procurement journey.",

        "recommended" =>
            "Recommended",

        "smart_title" =>
            "Smart Recommendation",

        "smart_desc" =>
            "Compare queue, slot availability, capacity and purchase schedule.",

        "smart_action" =>
            "Get Recommendation",

        "booking_title" =>
            "Book Your Slot",

        "booking_desc" =>
            "Find a procurement centre and choose an available slot.",

        "booking_action" =>
            "Book Slot",

        "my_booking_title" =>
            "My Booking",

        "my_booking_desc" =>
            "View your booking token, centre and appointment details.",

        "my_booking_action" =>
            "View Booking",

        "procurement_title" =>
            "Procurement Status",

        "procurement_desc" =>
            "Track your procurement journey from arrival to acceptance.",

        "procurement_action" =>
            "Track Status",

        "payment_title" =>
            "Payment Status",

        "payment_desc" =>
            "Check the current payment status of your procurement.",

        "payment_action" =>
            "View Payment",

        "latest_activity" =>
            "Latest Activity",

        "latest_activity_desc" =>
            "Your latest booking and procurement information.",

        "booking_token" =>
            "Booking Token",

        "centre" =>
            "Centre",

        "date" =>
            "Date",

        "time" =>
            "Time",

        "booking_status" =>
            "Booking Status",

        "procurement" =>
            "Procurement",

        "view_details" =>
            "View Booking Details",

        "no_booking" =>
            "You don't have a booking yet.",

        "start_booking" =>
            "Find a centre and book your procurement slot.",

        "need_help" =>
            "Need Help?",

        "need_help_desc" =>
            "KrishiSetu helps you find a suitable procurement centre and available slot.",

        "get_help" =>
            "Get Help",

        "farmer_portal" =>
            "Farmer Portal",

        "notifications" =>
            "Notifications",

        "logout" =>
            "Logout",

        "dashboard" =>
            "Dashboard",

        "recommendation" =>
            "Smart Recommendation",

        "book_slot" =>
            "Book Slot",

        "my_booking" =>
            "My Booking",

        "procurement_status" =>
            "Procurement Status",

        "payment_status" =>
            "Payment Status",

        "language_name" =>
            "English"

    ],

    "hi" => [

        "welcome_back" =>
            "वापसी पर स्वागत है, {name}",

        "dashboard_intro" =>
            "कृषिसेतु के साथ अपनी खरीद प्रक्रिया को आसानी और कुशलता से प्रबंधित करें।",

        "today_question" =>
            "आज आप क्या करना चाहते हैं?",

        "today_description" =>
            "अपनी खरीद प्रक्रिया को प्रबंधित करने के लिए एक सेवा चुनें।",

        "recommended" =>
            "सुझावित",

        "smart_title" =>
            "स्मार्ट सुझाव",

        "smart_desc" =>
            "कतार, स्लॉट उपलब्धता, क्षमता और खरीद कार्यक्रम की तुलना करें।",

        "smart_action" =>
            "सुझाव प्राप्त करें",

        "booking_title" =>
            "अपना स्लॉट बुक करें",

        "booking_desc" =>
            "खरीद केंद्र खोजें और उपलब्ध स्लॉट चुनें।",

        "booking_action" =>
            "स्लॉट बुक करें",

        "my_booking_title" =>
            "मेरी बुकिंग",

        "my_booking_desc" =>
            "अपना बुकिंग टोकन, केंद्र और अपॉइंटमेंट विवरण देखें।",

        "my_booking_action" =>
            "बुकिंग देखें",

        "procurement_title" =>
            "खरीद स्थिति",

        "procurement_desc" =>
            "किसान के पहुँचने से खरीद स्वीकार होने तक अपनी प्रक्रिया ट्रैक करें।",

        "procurement_action" =>
            "स्थिति देखें",

        "payment_title" =>
            "भुगतान स्थिति",

        "payment_desc" =>
            "अपनी खरीद के वर्तमान भुगतान की स्थिति देखें।",

        "payment_action" =>
            "भुगतान देखें",

        "latest_activity" =>
            "नवीनतम गतिविधि",

        "latest_activity_desc" =>
            "आपकी नवीनतम बुकिंग और खरीद की जानकारी।",

        "booking_token" =>
            "बुकिंग टोकन",

        "centre" =>
            "केंद्र",

        "date" =>
            "तारीख",

        "time" =>
            "समय",

        "booking_status" =>
            "बुकिंग स्थिति",

        "procurement" =>
            "खरीद",

        "view_details" =>
            "बुकिंग विवरण देखें",

        "no_booking" =>
            "अभी आपकी कोई बुकिंग नहीं है।",

        "start_booking" =>
            "केंद्र खोजें और अपना खरीद स्लॉट बुक करें।",

        "need_help" =>
            "मदद चाहिए?",

        "need_help_desc" =>
            "कृषिसेतु आपको उपयुक्त खरीद केंद्र और उपलब्ध स्लॉट खोजने में मदद करता है।",

        "get_help" =>
            "मदद लें",

        "farmer_portal" =>
            "किसान पोर्टल",

        "notifications" =>
            "सूचनाएँ",

        "logout" =>
            "लॉगआउट",

        "dashboard" =>
            "डैशबोर्ड",

        "recommendation" =>
            "स्मार्ट सुझाव",

        "book_slot" =>
            "स्लॉट बुक करें",

        "my_booking" =>
            "मेरी बुकिंग",

        "procurement_status" =>
            "खरीद स्थिति",

        "payment_status" =>
            "भुगतान स्थिति",

        "language_name" =>
            "हिन्दी"

    ],

    "bn" => [

        "welcome_back" =>
            "আবার স্বাগতম, {name}",

        "dashboard_intro" =>
            "কৃষিসেতুর মাধ্যমে আপনার ক্রয় প্রক্রিয়া সহজে এবং দক্ষতার সঙ্গে পরিচালনা করুন।",

        "today_question" =>
            "আজ আপনি কী করতে চান?",

        "today_description" =>
            "আপনার ক্রয় প্রক্রিয়া পরিচালনা করতে একটি পরিষেবা বেছে নিন।",

        "recommended" =>
            "প্রস্তাবিত",

        "smart_title" =>
            "স্মার্ট সুপারিশ",

        "smart_desc" =>
            "সারি, স্লটের প্রাপ্যতা, ক্ষমতা এবং ক্রয় সূচি তুলনা করুন।",

        "smart_action" =>
            "সুপারিশ দেখুন",

        "booking_title" =>
            "আপনার স্লট বুক করুন",

        "booking_desc" =>
            "ক্রয় কেন্দ্র খুঁজুন এবং একটি উপলব্ধ স্লট বেছে নিন।",

        "booking_action" =>
            "স্লট বুক করুন",

        "my_booking_title" =>
            "আমার বুকিং",

        "my_booking_desc" =>
            "আপনার বুকিং টোকেন, কেন্দ্র এবং অ্যাপয়েন্টমেন্টের তথ্য দেখুন।",

        "my_booking_action" =>
            "বুকিং দেখুন",

        "procurement_title" =>
            "ক্রয় অবস্থা",

        "procurement_desc" =>
            "কৃষক পৌঁছানো থেকে ক্রয় গ্রহণ পর্যন্ত আপনার প্রক্রিয়া ট্র্যাক করুন।",

        "procurement_action" =>
            "অবস্থা দেখুন",

        "payment_title" =>
            "পেমেন্ট অবস্থা",

        "payment_desc" =>
            "আপনার ক্রয়ের বর্তমান পেমেন্ট অবস্থা দেখুন।",

        "payment_action" =>
            "পেমেন্ট দেখুন",

        "latest_activity" =>
            "সর্বশেষ কার্যকলাপ",

        "latest_activity_desc" =>
            "আপনার সর্বশেষ বুকিং এবং ক্রয় সংক্রান্ত তথ্য।",

        "booking_token" =>
            "বুকিং টোকেন",

        "centre" =>
            "কেন্দ্র",

        "date" =>
            "তারিখ",

        "time" =>
            "সময়",

        "booking_status" =>
            "বুকিং অবস্থা",

        "procurement" =>
            "ক্রয়",

        "view_details" =>
            "বুকিংয়ের বিবরণ দেখুন",

        "no_booking" =>
            "এখনও আপনার কোনো বুকিং নেই।",

        "start_booking" =>
            "একটি কেন্দ্র খুঁজে আপনার ক্রয় স্লট বুক করুন।",

        "need_help" =>
            "সাহায্য দরকার?",

        "need_help_desc" =>
            "কৃষিসেতু আপনাকে উপযুক্ত ক্রয় কেন্দ্র এবং উপলব্ধ স্লট খুঁজে পেতে সাহায্য করে।",

        "get_help" =>
            "সাহায্য নিন",

        "farmer_portal" =>
            "কৃষক পোর্টাল",

        "notifications" =>
            "বিজ্ঞপ্তি",

        "logout" =>
            "লগআউট",

        "dashboard" =>
            "ড্যাশবোর্ড",

        "recommendation" =>
            "স্মার্ট সুপারিশ",

        "book_slot" =>
            "স্লট বুক করুন",

        "my_booking" =>
            "আমার বুকিং",

        "procurement_status" =>
            "ক্রয় অবস্থা",

        "payment_status" =>
            "পেমেন্ট অবস্থা",

        "language_name" =>
            "বাংলা"

    ]

];

$dt = $dashboardText[$currentLanguage] ?? $dashboardText["en"];

function dt(string $key, array $replace = []): string
{
    global $dt;

    $text = $dt[$key] ?? $key;

    foreach ($replace as $placeholder => $value) {
        $text = str_replace(
            "{" . $placeholder . "}",
            (string)$value,
            $text
        );
    }

    return $text;
}


/* =========================================================
   GET LATEST BOOKING
========================================================= */

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


/* =========================================================
   GET UNREAD NOTIFICATIONS
========================================================= */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS unread_count
    FROM notifications
    WHERE user_id = ?
    AND is_read = 0
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$notificationResult = $stmt->get_result();
$notificationData = $notificationResult->fetch_assoc();

$unreadNotifications =
    (int)($notificationData["unread_count"] ?? 0);

$stmt->close();


/* =========================================================
   BOOKING STATUS
========================================================= */

$bookingStatusKey =
    strtolower($latestBooking["booking_status"] ?? "");

$procurementStatusKey =
    strtolower($latestBooking["procurement_status"] ?? "");


/* =========================================================
   ESCAPE HELPER
========================================================= */

function e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

<!DOCTYPE html>

<html lang="<?= e($currentLanguage) ?>">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= e(t("title_farmer_dashboard")) ?>
        | KrishiSetu
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        :root {
            --ks-green: #087443;
            --ks-green-dark: #055c35;
            --ks-green-light: #e8f5ee;

            --ks-blue: #2878b5;
            --ks-blue-light: #edf6fc;

            --ks-purple: #7654d6;
            --ks-purple-light: #f3efff;

            --ks-orange: #d88900;
            --ks-orange-light: #fff6e6;

            --ks-teal: #087f83;
            --ks-teal-light: #eaf8f8;

            --ks-bg: #f6f9f7;
            --ks-white: #ffffff;

            --ks-text: #17352a;
            --ks-muted: #61716a;
            --ks-light: #8a9892;

            --ks-border: #dfe7e2;

            --ks-shadow:
                0 8px 28px rgba(20, 65, 45, 0.07);
        }


        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            background: var(--ks-bg);
            color: var(--ks-text);
            font-family:
                Arial,
                "Noto Sans",
                "Noto Sans Devanagari",
                sans-serif;
        }


        a {
            text-decoration: none;
            color: inherit;
        }


        /* =====================================================
           TOP NAVBAR
        ===================================================== */

        .ks-navbar {
            height: 74px;
            background: var(--ks-white);
            border-bottom: 1px solid var(--ks-border);

            position: sticky;
            top: 0;
            z-index: 100;
        }


        .ks-nav-inner {
            width: min(94%, 1280px);
            height: 100%;
            margin: auto;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }


        .ks-brand {
            display: flex;
            align-items: center;
            gap: 11px;
        }


        .ks-logo {
            width: 42px;
            height: 42px;

            border-radius: 12px;

            background: var(--ks-green-light);

            display: flex;
            align-items: center;
            justify-content: center;
        }


        .ks-logo svg {
            width: 31px;
            height: 31px;
        }


        .ks-brand-name {
            font-size: 22px;
            font-weight: 800;
            color: var(--ks-green);
            line-height: 1.1;
        }


        .ks-brand-subtitle {
            display: block;
            margin-top: 3px;

            color: var(--ks-muted);
            font-size: 11px;
        }


        .ks-user-area {
            display: flex;
            align-items: center;
            gap: 13px;
        }


        .ks-welcome-user {
            font-size: 14px;
            font-weight: 700;
            color: var(--ks-text);
        }


        .ks-language {
            padding: 8px 11px;

            border: 1px solid var(--ks-border);
            border-radius: 20px;

            background: #fff;
            color: var(--ks-muted);

            font-size: 13px;
            font-weight: 600;
        }


        .ks-notification {
            width: 42px;
            height: 42px;

            border: 1px solid var(--ks-border);
            border-radius: 50%;

            background: #fff;

            display: flex;
            align-items: center;
            justify-content: center;

            color: var(--ks-green);

            position: relative;

            transition: 0.2s ease;
        }


        .ks-notification:hover {
            background: var(--ks-green-light);
        }


        .ks-notification svg {
            width: 21px;
            height: 21px;
        }


        .ks-notification-badge {
            position: absolute;

            top: -3px;
            right: -3px;

            min-width: 19px;
            height: 19px;

            padding: 0 4px;

            border-radius: 20px;

            background: #c93434;
            color: white;

            border: 2px solid white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 10px;
            font-weight: 800;
        }


        .ks-logout {
            padding: 9px 16px;

            border: 1px solid var(--ks-green);
            border-radius: 9px;

            color: var(--ks-green);

            font-size: 13px;
            font-weight: 700;

            transition: 0.2s ease;
        }


        .ks-logout:hover {
            background: var(--ks-green);
            color: white;
        }


        /* =====================================================
           PAGE
        ===================================================== */

        .ks-page {
            width: min(94%, 1280px);
            margin: auto;

            padding: 34px 0 60px;
        }


        /* =====================================================
           WELCOME HERO
        ===================================================== */

        .ks-hero {
            min-height: 205px;

            padding: 30px 34px;

            border: 1px solid #dcebe2;
            border-radius: 20px;

            background:
                linear-gradient(
                    110deg,
                    #ffffff 0%,
                    #f0f9f3 62%,
                    #e7f5eb 100%
                );

            position: relative;
            overflow: hidden;

            box-shadow: var(--ks-shadow);
        }


        .ks-hero::after {
            content: "";

            position: absolute;

            right: -80px;
            bottom: -130px;

            width: 420px;
            height: 300px;

            background: #dcefe2;

            border-radius: 50%;

            opacity: 0.55;
        }


        .ks-hero-content {
            position: relative;
            z-index: 2;

            max-width: 760px;
        }


        .ks-hero-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            padding: 7px 12px;

            border-radius: 20px;

            background: var(--ks-green-light);
            color: var(--ks-green);

            font-size: 13px;
            font-weight: 700;
        }


        .ks-hero h1 {
            margin: 15px 0 8px;

            font-size: 34px;
            line-height: 1.2;

            color: var(--ks-text);
        }


        .ks-hero h1 span {
            color: var(--ks-green);
        }


        .ks-hero p {
            margin: 0;

            color: var(--ks-muted);

            font-size: 15px;
            line-height: 1.65;
        }


        .ks-hero-pill {
            display: inline-block;

            margin-top: 17px;

            padding: 7px 13px;

            border-radius: 20px;

            background: white;

            border: 1px solid #d8e9de;

            color: var(--ks-green);

            font-size: 12px;
            font-weight: 700;
        }


        /* =====================================================
           SECTION HEADING
        ===================================================== */

        .ks-section-heading {
            margin: 34px 0 17px;
        }


        .ks-section-heading h2 {
            margin: 0 0 5px;

            font-size: 23px;
            color: var(--ks-text);
        }


        .ks-section-heading p {
            margin: 0;

            color: var(--ks-muted);
            font-size: 14px;
        }


        /* =====================================================
           SERVICE CARDS
        ===================================================== */

        .ks-service-grid {
            display: grid;

            grid-template-columns:
                repeat(6, 1fr);

            gap: 16px;
        }


        .ks-service-card {
            min-height: 215px;

            padding: 21px;

            background: white;

            border: 1px solid var(--ks-border);
            border-radius: 16px;

            box-shadow: 0 5px 20px rgba(20, 65, 45, 0.045);

            display: flex;
            flex-direction: column;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                border-color 0.2s ease;
        }


        .ks-service-card:hover {
            transform: translateY(-4px);

            box-shadow:
                0 13px 30px
                rgba(20, 65, 45, 0.10);

            border-color: #b9d7c5;
        }


        .ks-service-card.recommended {
            border-color: #9bc9ae;
            background:
                linear-gradient(
                    145deg,
                    #ffffff,
                    #f5fbf7
                );
        }


        .ks-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            gap: 10px;
        }


        .ks-card-icon {
            width: 48px;
            height: 48px;

            flex-shrink: 0;

            border-radius: 12px;

            display: flex;
            align-items: center;
            justify-content: center;
        }


        .ks-card-icon img {
            width: 27px;
            height: 27px;
        }


        .icon-green {
            background: var(--ks-green-light);
        }


        .icon-blue {
            background: var(--ks-blue-light);
        }


        .icon-purple {
            background: var(--ks-purple-light);
        }


        .icon-orange {
            background: var(--ks-orange-light);
        }


        .icon-teal {
            background: var(--ks-teal-light);
        }


        .ks-recommended-badge {
            padding: 5px 8px;

            border-radius: 20px;

            background: var(--ks-green);

            color: white;

            font-size: 10px;
            font-weight: 800;

            white-space: nowrap;
        }


        .ks-service-card h3 {
            margin: 17px 0 8px;

            font-size: 17px;
            line-height: 1.3;

            color: var(--ks-text);
        }


        .ks-service-card p {
            margin: 0;

            color: var(--ks-muted);

            font-size: 13px;
            line-height: 1.55;
        }


        .ks-card-action {
            margin-top: auto;
            padding-top: 18px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            color: var(--ks-green);

            font-size: 13px;
            font-weight: 800;
        }


        .ks-arrow {
            width: 31px;
            height: 31px;

            border-radius: 50%;

            background: var(--ks-green);

            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 16px;
        }


        /* =====================================================
           LATEST BOOKING + HELP
        ===================================================== */

        .ks-lower-grid {
            display: grid;

            grid-template-columns:
                2fr 1fr;

            gap: 18px;

            margin-top: 28px;
        }


        .ks-panel {
            background: white;

            border: 1px solid var(--ks-border);
            border-radius: 17px;

            box-shadow:
                0 5px 20px
                rgba(20, 65, 45, 0.045);

            overflow: hidden;
        }


        .ks-panel-header {
            padding: 20px 22px;

            border-bottom: 1px solid #edf1ee;
        }


        .ks-panel-header h2 {
            margin: 0 0 5px;

            font-size: 19px;
        }


        .ks-panel-header p {
            margin: 0;

            color: var(--ks-muted);

            font-size: 13px;
        }


        .ks-booking-body {
            padding: 21px 22px;
        }


        .ks-token-row {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            padding-bottom: 17px;

            border-bottom: 1px solid #edf1ee;
        }


        .ks-token-label {
            color: var(--ks-muted);
            font-size: 12px;
        }


        .ks-token {
            margin-top: 4px;

            color: var(--ks-green);

            font-size: 17px;
            font-weight: 800;
            letter-spacing: 0.4px;
        }


        .ks-status-pill {
            display: inline-flex;
            align-items: center;

            padding: 7px 10px;

            border-radius: 20px;

            background: var(--ks-green-light);
            color: var(--ks-green);

            font-size: 11px;
            font-weight: 800;
        }


        .ks-booking-details {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 13px;

            margin-top: 18px;
        }


        .ks-detail {
            padding: 13px;

            border-radius: 10px;

            background: #f8faf8;
        }


        .ks-detail-label {
            display: block;

            color: var(--ks-light);

            font-size: 11px;

            margin-bottom: 5px;
        }


        .ks-detail-value {
            color: var(--ks-text);

            font-size: 13px;
            font-weight: 700;

            line-height: 1.4;
        }


        .ks-view-booking {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            margin-top: 18px;

            padding: 10px 14px;

            border-radius: 9px;

            background: var(--ks-green);
            color: white;

            font-size: 12px;
            font-weight: 800;
        }


        .ks-view-booking:hover {
            background: var(--ks-green-dark);
        }


        .ks-empty {
            padding: 28px 20px;

            text-align: center;

            color: var(--ks-muted);
        }


        .ks-empty-icon {
            width: 52px;
            height: 52px;

            margin: 0 auto 13px;

            border-radius: 50%;

            background: var(--ks-green-light);

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 23px;
        }


        .ks-empty strong {
            display: block;

            margin-bottom: 5px;

            color: var(--ks-text);

            font-size: 14px;
        }


        .ks-empty p {
            margin: 0;

            font-size: 12px;
            line-height: 1.5;
        }


        /* =====================================================
           HELP PANEL
        ===================================================== */

        .ks-help-body {
            padding: 24px;
        }


        .ks-help-icon {
            width: 48px;
            height: 48px;

            border-radius: 12px;

            background: var(--ks-green-light);

            display: flex;
            align-items: center;
            justify-content: center;

            color: var(--ks-green);

            font-size: 23px;

            margin-bottom: 17px;
        }


        .ks-help-body h3 {
            margin: 0 0 8px;

            font-size: 18px;
        }


        .ks-help-body p {
            margin: 0;

            color: var(--ks-muted);

            font-size: 13px;
            line-height: 1.6;
        }


        .ks-help-button {
            display: inline-flex;

            margin-top: 19px;

            padding: 9px 13px;

            border: 1px solid var(--ks-green);

            border-radius: 8px;

            color: var(--ks-green);

            font-size: 12px;
            font-weight: 800;
        }


        /* =====================================================
           MOBILE NAV
        ===================================================== */

        .ks-mobile-nav {
            display: none;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {

            .ks-service-grid {
                grid-template-columns:
                    repeat(3, 1fr);
            }

        }


        @media (max-width: 800px) {

            .ks-navbar {
                height: 68px;
            }


            .ks-page {
                padding-top: 24px;
            }


            .ks-welcome-user {
                display: none;
            }


            .ks-language {
                display: none;
            }


            .ks-hero {
                padding: 25px;
            }


            .ks-hero h1 {
                font-size: 28px;
            }


            .ks-service-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }


            .ks-lower-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 560px) {

            .ks-nav-inner {
                width: 92%;
            }


            .ks-brand-name {
                font-size: 19px;
            }


            .ks-brand-subtitle {
                display: none;
            }


            .ks-logout {
                padding: 8px 11px;
            }


            .ks-page {
                width: 92%;
                padding-bottom: 40px;
            }


            .ks-hero {
                min-height: auto;

                padding: 22px;

                border-radius: 16px;
            }


            .ks-hero h1 {
                font-size: 25px;
            }


            .ks-hero p {
                font-size: 13px;
            }


            .ks-section-heading {
                margin-top: 27px;
            }


            .ks-section-heading h2 {
                font-size: 20px;
            }


            .ks-service-grid {
                grid-template-columns: 1fr;
            }


            .ks-service-card {
                min-height: 180px;
            }


            .ks-booking-details {
                grid-template-columns: 1fr;
            }


            .ks-token-row {
                align-items: flex-start;
                flex-direction: column;
            }


            .ks-notification {
                width: 39px;
                height: 39px;
            }


            .ks-logout {
                font-size: 12px;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<header class="ks-navbar">

    <div class="ks-nav-inner">


        <!-- BRAND -->

        <a
            href="../index.php"
            class="ks-brand"
        >

            <span class="ks-logo">

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

                <span class="ks-brand-name">
                    KrishiSetu
                </span>

                <span class="ks-brand-subtitle">
                    <?= e(dt("farmer_portal")) ?>
                </span>

            </span>

        </a>


        <!-- USER AREA -->

        <div class="ks-user-area">

            <span class="ks-welcome-user">

                <?= e(dt("welcome_back", [
                    "name" => $name
                ])) ?>

            </span>


            <span class="ks-language">

                <?= e(dt("language_name")) ?>

            </span>


            <!-- NOTIFICATIONS -->

            <a
                href="notifications.php"
                class="ks-notification"
                title="<?= e(dt("notifications")) ?>"
                aria-label="<?= e(dt("notifications")) ?>"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >

                    <path
                        d="M18 8C18 4.69 15.76 2 12 2C8.24 2 6 4.69 6 8C6 13 4 14 4 16H20C20 14 18 13 18 8Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M10 20C10.5 21 11 21.5 12 21.5C13 21.5 13.5 21 14 20"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />

                </svg>


                <?php if ($unreadNotifications > 0): ?>

                    <span class="ks-notification-badge">

                        <?= $unreadNotifications ?>

                    </span>

                <?php endif; ?>

            </a>


            <!-- LOGOUT -->

            <a
                href="logout.php"
                class="ks-logout"
            >

                <?= e(dt("logout")) ?>

            </a>

        </div>

    </div>

</header>



<!-- =========================================================
     MAIN PAGE
========================================================= -->

<main class="ks-page">


    <!-- =====================================================
         WELCOME HERO
    ====================================================== -->

    <section class="ks-hero">

        <div class="ks-hero-content">

            <span class="ks-hero-label">

                🌱

                <?= e(dt("farmer_portal")) ?>

            </span>


            <h1>

                <?= e(dt("welcome_back", [
                    "name" => $name
                ])) ?>

            </h1>


            <p>

                <?= e(dt("dashboard_intro")) ?>

            </p>


            <span class="ks-hero-pill">

                🌾 Strong Farmers, Stronger Tomorrow

            </span>

        </div>

    </section>



    <!-- =====================================================
         SERVICES
    ====================================================== -->

    <section class="ks-section-heading">

        <h2>
            <?= e(dt("today_question")) ?>
        </h2>

        <p>
            <?= e(dt("today_description")) ?>
        </p>

    </section>


    <section class="ks-service-grid">


        <!-- SMART RECOMMENDATION -->

        <a
            href="recommendation.php"
            class="ks-service-card recommended"
        >

            <div class="ks-card-top">

                <span class="ks-card-icon icon-green">

                    <img
                        src="../assets/icons/recommendation.svg"
                        alt=""
                    >

                </span>


                <span class="ks-recommended-badge">

                    ⭐ <?= e(dt("recommended")) ?>

                </span>

            </div>


            <h3>
                <?= e(dt("smart_title")) ?>
            </h3>


            <p>
                <?= e(dt("smart_desc")) ?>
            </p>


            <span class="ks-card-action">

                <?= e(dt("smart_action")) ?>

                <span class="ks-arrow">
                    →
                </span>

            </span>

        </a>



        <!-- BOOK SLOT -->

        <a
            href="centres.php"
            class="ks-service-card"
        >

            <div class="ks-card-top">

                <span class="ks-card-icon icon-blue">

                    <img
                        src="../assets/icons/calendar.svg"
                        alt=""
                    >

                </span>

            </div>


            <h3>
                <?= e(dt("booking_title")) ?>
            </h3>


            <p>
                <?= e(dt("booking_desc")) ?>
            </p>


            <span class="ks-card-action">

                <?= e(dt("booking_action")) ?>

                <span
                    class="ks-arrow"
                    style="background:#2878b5;"
                >
                    →
                </span>

            </span>

        </a>



        <!-- MY BOOKING -->

        <a
            href="my_booking.php"
            class="ks-service-card"
        >

            <div class="ks-card-top">

                <span class="ks-card-icon icon-purple">

                    <img
                        src="../assets/icons/status.svg"
                        alt=""
                    >

                </span>

            </div>


            <h3>
                <?= e(dt("my_booking_title")) ?>
            </h3>


            <p>
                <?= e(dt("my_booking_desc")) ?>
            </p>


            <span class="ks-card-action">

                <?= e(dt("my_booking_action")) ?>

                <span
                    class="ks-arrow"
                    style="background:#7654d6;"
                >
                    →
                </span>

            </span>

        </a>



        <!-- PROCUREMENT -->

        <a
            href="procurement_status.php"
            class="ks-service-card"
        >

            <div class="ks-card-top">

                <span class="ks-card-icon icon-orange">

                    <img
                        src="../assets/icons/status.svg"
                        alt=""
                    >

                </span>

            </div>


            <h3>
                <?= e(dt("procurement_title")) ?>
            </h3>


            <p>
                <?= e(dt("procurement_desc")) ?>
            </p>


            <span class="ks-card-action">

                <?= e(dt("procurement_action")) ?>

                <span
                    class="ks-arrow"
                    style="background:#d88900;"
                >
                    →
                </span>

            </span>

        </a>



        <!-- PAYMENT -->

        <a
            href="payment_status.php"
            class="ks-service-card"
        >

            <div class="ks-card-top">

                <span class="ks-card-icon icon-teal">

                    <img
                        src="../assets/icons/status.svg"
                        alt=""
                    >

                </span>

            </div>


            <h3>
                <?= e(dt("payment_title")) ?>
            </h3>


            <p>
                <?= e(dt("payment_desc")) ?>
            </p>


            <span class="ks-card-action">

                <?= e(dt("payment_action")) ?>

                <span
                    class="ks-arrow"
                    style="background:#087f83;"
                >
                    →
                </span>

            </span>

        </a>

    </section>



    <!-- =====================================================
         LOWER INFORMATION
    ====================================================== -->

    <section class="ks-lower-grid">


        <!-- LATEST BOOKING -->

        <div class="ks-panel">

            <div class="ks-panel-header">

                <h2>
                    <?= e(dt("latest_activity")) ?>
                </h2>

                <p>
                    <?= e(dt("latest_activity_desc")) ?>
                </p>

            </div>


            <?php if ($latestBooking): ?>

                <div class="ks-booking-body">


                    <!-- TOKEN + STATUS -->

                    <div class="ks-token-row">

                        <div>

                            <div class="ks-token-label">
                                <?= e(dt("booking_token")) ?>
                            </div>

                            <div class="ks-token">

                                <?= e(
                                    $latestBooking["booking_token"]
                                ) ?>

                            </div>

                        </div>


                        <span class="ks-status-pill">

                            <?= e(
                                translateStatus(
                                    $bookingStatusKey
                                )
                            ) ?>

                        </span>

                    </div>


                    <!-- DETAILS -->

                    <div class="ks-booking-details">


                        <div class="ks-detail">

                            <span class="ks-detail-label">
                                <?= e(dt("centre")) ?>
                            </span>

                            <span class="ks-detail-value">

                                <?= e(
                                    $latestBooking["centre_name"]
                                ) ?>

                            </span>

                        </div>


                        <div class="ks-detail">

                            <span class="ks-detail-label">
                                <?= e(dt("date")) ?>
                            </span>

                            <span class="ks-detail-value">

                                <?= e(
                                    date(
                                        "d M Y",
                                        strtotime(
                                            $latestBooking["slot_date"]
                                        )
                                    )
                                ) ?>

                            </span>

                        </div>


                        <div class="ks-detail">

                            <span class="ks-detail-label">
                                <?= e(dt("time")) ?>
                            </span>

                            <span class="ks-detail-value">

                                <?= e(
                                    date(
                                        "h:i A",
                                        strtotime(
                                            $latestBooking["start_time"]
                                        )
                                    )
                                ) ?>

                                -

                                <?= e(
                                    date(
                                        "h:i A",
                                        strtotime(
                                            $latestBooking["end_time"]
                                        )
                                    )
                                ) ?>

                            </span>

                        </div>


                        <?php if (
                            !empty(
                                $latestBooking["procurement_status"]
                            )
                        ): ?>

                            <div class="ks-detail">

                                <span class="ks-detail-label">
                                    <?= e(dt("procurement")) ?>
                                </span>

                                <span class="ks-detail-value">

                                    <?= e(
                                        translateStatus(
                                            $procurementStatusKey
                                        )
                                    ) ?>

                                </span>

                            </div>

                        <?php else: ?>

                            <div class="ks-detail">

                                <span class="ks-detail-label">
                                    <?= e(dt("booking_status")) ?>
                                </span>

                                <span class="ks-detail-value">

                                    <?= e(
                                        translateStatus(
                                            $bookingStatusKey
                                        )
                                    ) ?>

                                </span>

                            </div>

                        <?php endif; ?>

                    </div>


                    <a
                        href="my_booking.php"
                        class="ks-view-booking"
                    >

                        <?= e(dt("view_details")) ?>

                        →

                    </a>

                </div>


            <?php else: ?>


                <div class="ks-empty">

                    <div class="ks-empty-icon">
                        📅
                    </div>

                    <strong>
                        <?= e(dt("no_booking")) ?>
                    </strong>

                    <p>
                        <?= e(dt("start_booking")) ?>
                    </p>

                    <a
                        href="centres.php"
                        class="ks-view-booking"
                    >
                        <?= e(dt("booking_action")) ?>
                        →
                    </a>

                </div>


            <?php endif; ?>

        </div>



        <!-- HELP -->

        <div class="ks-panel">

            <div class="ks-help-body">

                <div class="ks-help-icon">
                    ?
                </div>


                <h3>
                    <?= e(dt("need_help")) ?>
                </h3>


                <p>
                    <?= e(dt("need_help_desc")) ?>
                </p>


                <a
                    href="notifications.php"
                    class="ks-help-button"
                >

                    <?= e(dt("get_help")) ?>

                    →

                </a>

            </div>

        </div>

    </section>

</main>


</body>

</html>