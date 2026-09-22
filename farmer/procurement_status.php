<?php

session_start();

/* =========================================================
   FARMER ACCESS
========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] !== 'farmer'
) {
    header("Location: ../login.php");
    exit;
}


/* =========================================================
   DATABASE + LANGUAGE
========================================================= */

require_once "../config/database.php";
require_once "../config/language.php";

$user_id = (int)$_SESSION['user_id'];

$currentLanguage = $_SESSION['language'] ?? 'en';


/* =========================================================
   ESCAPE HELPER
========================================================= */

function e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* =========================================================
   PAGE TRANSLATIONS
========================================================= */

$pageText = [

    'en' => [

        'page_title' => 'Procurement Status',

        'page_subtitle' =>
            'Track your procurement journey from booking to acceptance.',

        'back_dashboard' =>
            'Back to Dashboard',

        'booking_token' =>
            'Booking Token',

        'booking_confirmed' =>
            'Booking Confirmed',

        'booking_confirmed_desc' =>
            'Your procurement slot has been successfully booked.',

        'farmer_arrived' =>
            'Farmer Arrived',

        'farmer_arrived_desc' =>
            'Your arrival at the procurement centre has been recorded.',

        'paddy_weighed' =>
            'Paddy Weighed',

        'paddy_weighed_desc' =>
            'Your paddy has been weighed and the quantity has been recorded.',

        'procurement_accepted' =>
            'Procurement Accepted',

        'procurement_accepted_desc' =>
            'Your paddy has been accepted for procurement.',

        'scheduled' =>
            'Scheduled',

        'current_status' =>
            'Current Status',

        'next_step' =>
            'Next Step',

        'procurement_centre' =>
            'Procurement Centre',

        'venue' =>
            'Venue',

        'scheduled_date' =>
            'Scheduled Date',

        'time' =>
            'Time',

        'crop' =>
            'Crop',

        'quantity' =>
            'Quantity',

        'status' =>
            'Status',

        'booking_details' =>
            'Booking Details',

        'booking_details_desc' =>
            'Information about your scheduled procurement visit.',

        'what_happens_next' =>
            "What's Next?",

        'next_arrival' =>
            'Visit the procurement centre at your scheduled date and time.',

        'next_weight' =>
            'After arrival, the centre staff will record the weight of your paddy.',

        'next_accept' =>
            'After weighing, your procurement will be reviewed and accepted.',

        'completed_title' =>
            'Procurement Process Completed',

        'completed_desc' =>
            'Your procurement has been accepted successfully.',

        'no_record' =>
            'No Procurement Record',

        'no_record_desc' =>
            "You don't have an active procurement booking yet.",

        'find_centre' =>
            'Find Procurement Centre',

        'active' =>
            'Active',

        'completed' =>
            'Completed',

        'pending' =>
            'Pending',

        'cancelled' =>
            'Cancelled',

        'cancelled_title' =>
            'Booking Cancelled',

        'cancelled_desc' =>
            'This procurement booking has been cancelled.',

        'view_booking' =>
            'View My Booking'

    ],


    'hi' => [

        'page_title' =>
            'खरीद स्थिति',

        'page_subtitle' =>
            'बुकिंग से खरीद स्वीकार होने तक अपनी प्रक्रिया को ट्रैक करें।',

        'back_dashboard' =>
            'डैशबोर्ड पर वापस जाएँ',

        'booking_token' =>
            'बुकिंग टोकन',

        'booking_confirmed' =>
            'बुकिंग की पुष्टि',

        'booking_confirmed_desc' =>
            'आपका खरीद स्लॉट सफलतापूर्वक बुक हो गया है।',

        'farmer_arrived' =>
            'किसान पहुँच गया',

        'farmer_arrived_desc' =>
            'खरीद केंद्र पर आपका पहुँचना दर्ज कर लिया गया है।',

        'paddy_weighed' =>
            'धान का वजन दर्ज',

        'paddy_weighed_desc' =>
            'आपके धान का वजन दर्ज कर लिया गया है।',

        'procurement_accepted' =>
            'खरीद स्वीकार की गई',

        'procurement_accepted_desc' =>
            'आपका धान खरीद के लिए स्वीकार कर लिया गया है।',

        'scheduled' =>
            'निर्धारित',

        'current_status' =>
            'वर्तमान स्थिति',

        'next_step' =>
            'अगला चरण',

        'procurement_centre' =>
            'खरीद केंद्र',

        'venue' =>
            'स्थान',

        'scheduled_date' =>
            'निर्धारित तारीख',

        'time' =>
            'समय',

        'crop' =>
            'फसल',

        'quantity' =>
            'मात्रा',

        'status' =>
            'स्थिति',

        'booking_details' =>
            'बुकिंग विवरण',

        'booking_details_desc' =>
            'आपकी निर्धारित खरीद यात्रा की जानकारी।',

        'what_happens_next' =>
            'अब आगे क्या होगा?',

        'next_arrival' =>
            'निर्धारित तारीख और समय पर खरीद केंद्र पर पहुँचें।',

        'next_weight' =>
            'पहुँचने के बाद केंद्र कर्मचारी आपके धान का वजन दर्ज करेंगे।',

        'next_accept' =>
            'वजन दर्ज होने के बाद आपकी खरीद की जाँच करके उसे स्वीकार किया जाएगा।',

        'completed_title' =>
            'खरीद प्रक्रिया पूर्ण',

        'completed_desc' =>
            'आपकी खरीद सफलतापूर्वक स्वीकार कर ली गई है।',

        'no_record' =>
            'कोई खरीद रिकॉर्ड नहीं',

        'no_record_desc' =>
            'अभी आपकी कोई सक्रिय खरीद बुकिंग नहीं है।',

        'find_centre' =>
            'खरीद केंद्र खोजें',

        'active' =>
            'सक्रिय',

        'completed' =>
            'पूर्ण',

        'pending' =>
            'लंबित',

        'cancelled' =>
            'रद्द',

        'cancelled_title' =>
            'बुकिंग रद्द',

        'cancelled_desc' =>
            'यह खरीद बुकिंग रद्द कर दी गई है।',

        'view_booking' =>
            'मेरी बुकिंग देखें'

    ],


    'bn' => [

        'page_title' =>
            'ক্রয় অবস্থা',

        'page_subtitle' =>
            'বুকিং থেকে ক্রয় গ্রহণ পর্যন্ত আপনার প্রক্রিয়া ট্র্যাক করুন।',

        'back_dashboard' =>
            'ড্যাশবোর্ডে ফিরে যান',

        'booking_token' =>
            'বুকিং টোকেন',

        'booking_confirmed' =>
            'বুকিং নিশ্চিত',

        'booking_confirmed_desc' =>
            'আপনার ক্রয় স্লট সফলভাবে বুক করা হয়েছে।',

        'farmer_arrived' =>
            'কৃষক পৌঁছেছেন',

        'farmer_arrived_desc' =>
            'ক্রয় কেন্দ্রে আপনার পৌঁছানো রেকর্ড করা হয়েছে।',

        'paddy_weighed' =>
            'ধানের ওজন নেওয়া হয়েছে',

        'paddy_weighed_desc' =>
            'আপনার ধানের ওজন এবং পরিমাণ রেকর্ড করা হয়েছে।',

        'procurement_accepted' =>
            'ক্রয় গ্রহণ করা হয়েছে',

        'procurement_accepted_desc' =>
            'আপনার ধান ক্রয়ের জন্য গ্রহণ করা হয়েছে।',

        'scheduled' =>
            'নির্ধারিত',

        'current_status' =>
            'বর্তমান অবস্থা',

        'next_step' =>
            'পরবর্তী ধাপ',

        'procurement_centre' =>
            'ক্রয় কেন্দ্র',

        'venue' =>
            'স্থান',

        'scheduled_date' =>
            'নির্ধারিত তারিখ',

        'time' =>
            'সময়',

        'crop' =>
            'ফসল',

        'quantity' =>
            'পরিমাণ',

        'status' =>
            'অবস্থা',

        'booking_details' =>
            'বুকিংয়ের বিবরণ',

        'booking_details_desc' =>
            'আপনার নির্ধারিত ক্রয় যাত্রার তথ্য।',

        'what_happens_next' =>
            'এরপর কী হবে?',

        'next_arrival' =>
            'নির্ধারিত তারিখ এবং সময়ে ক্রয় কেন্দ্রে পৌঁছান।',

        'next_weight' =>
            'পৌঁছানোর পরে কেন্দ্রের কর্মীরা আপনার ধানের ওজন রেকর্ড করবেন।',

        'next_accept' =>
            'ওজন নেওয়ার পরে আপনার ক্রয় যাচাই করে গ্রহণ করা হবে।',

        'completed_title' =>
            'ক্রয় প্রক্রিয়া সম্পন্ন',

        'completed_desc' =>
            'আপনার ক্রয় সফলভাবে গ্রহণ করা হয়েছে।',

        'no_record' =>
            'কোনো ক্রয় রেকর্ড নেই',

        'no_record_desc' =>
            'এখনও আপনার কোনো সক্রিয় ক্রয় বুকিং নেই।',

        'find_centre' =>
            'ক্রয় কেন্দ্র খুঁজুন',

        'active' =>
            'সক্রিয়',

        'completed' =>
            'সম্পন্ন',

        'pending' =>
            'অপেক্ষমাণ',

        'cancelled' =>
            'বাতিল',

        'cancelled_title' =>
            'বুকিং বাতিল',

        'cancelled_desc' =>
            'এই ক্রয় বুকিংটি বাতিল করা হয়েছে।',

        'view_booking' =>
            'আমার বুকিং দেখুন'

    ]

];


$txt = $pageText[$currentLanguage]
    ?? $pageText['en'];


function pt(string $key): string
{
    global $txt;

    return $txt[$key] ?? $key;
}


/* =========================================================
   GET LATEST BOOKING + PROCUREMENT
========================================================= */

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

if (!$stmt) {
    die("Unable to load procurement status.");
}

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$data = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   DETERMINE CURRENT STATUS
========================================================= */

$bookingStatus =
    strtolower(
        trim(
            $data['booking_status'] ?? ''
        )
    );

$procurementStatus =
    strtolower(
        trim(
            $data['procurement_status'] ?? ''
        )
    );


/*
 * Procurement status takes priority.
 *
 * If procurement does not exist yet,
 * booking status is used.
 */

if ($bookingStatus === 'cancelled') {

    $status = 'cancelled';

} elseif (in_array(
    $procurementStatus,
    ['pending', 'arrived', 'weighed', 'accepted'],
    true
)) {

    $status = $procurementStatus;

} else {

    $status = 'pending';

}


/* =========================================================
   STATUS ORDER
========================================================= */

$statusOrder = [

    'pending' => 1,

    'arrived' => 2,

    'weighed' => 3,

    'accepted' => 4

];


$currentStep =
    $statusOrder[$status] ?? 1;


/* =========================================================
   STATUS CONTENT
========================================================= */

$statusContent = [

    'pending' => [

        'title' =>
            pt('booking_confirmed'),

        'description' =>
            pt('booking_confirmed_desc'),

        'icon' =>
            '✓'

    ],

    'arrived' => [

        'title' =>
            pt('farmer_arrived'),

        'description' =>
            pt('farmer_arrived_desc'),

        'icon' =>
            '🚜'

    ],

    'weighed' => [

        'title' =>
            pt('paddy_weighed'),

        'description' =>
            pt('paddy_weighed_desc'),

        'icon' =>
            '⚖'

    ],

    'accepted' => [

        'title' =>
            pt('procurement_accepted'),

        'description' =>
            pt('completed_desc'),

        'icon' =>
            '✓'

    ],

    'cancelled' => [

        'title' =>
            pt('cancelled_title'),

        'description' =>
            pt('cancelled_desc'),

        'icon' =>
            '×'

    ]

];


$currentStatusContent =
    $statusContent[$status]
    ?? $statusContent['pending'];


/* =========================================================
   PROGRESS STEPS
========================================================= */

$steps = [

    1 => [

        'key' =>
            'booking_confirmed',

        'description' =>
            'booking_confirmed_desc'

    ],

    2 => [

        'key' =>
            'farmer_arrived',

        'description' =>
            'farmer_arrived_desc'

    ],

    3 => [

        'key' =>
            'paddy_weighed',

        'description' =>
            'paddy_weighed_desc'

    ],

    4 => [

        'key' =>
            'procurement_accepted',

        'description' =>
            'completed_desc'

    ]

];


/* =========================================================
   NEXT STEP
========================================================= */

$nextStepText = '';

if ($status === 'pending') {

    $nextStepText = pt('next_arrival');

} elseif ($status === 'arrived') {

    $nextStepText = pt('next_weight');

} elseif ($status === 'weighed') {

    $nextStepText = pt('next_accept');

} elseif ($status === 'accepted') {

    $nextStepText = pt('completed_desc');

}


/* =========================================================
   DATE / TIME
========================================================= */

$formattedDate = '';

$formattedStartTime = '';

$formattedEndTime = '';

if ($data) {

    $formattedDate = date(
        'd M Y',
        strtotime($data['slot_date'])
    );

    $formattedStartTime = date(
        'h:i A',
        strtotime($data['start_time'])
    );

    $formattedEndTime = date(
        'h:i A',
        strtotime($data['end_time'])
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
        <?= e(pt('page_title')) ?> | KrishiSetu
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

            --ks-bg: #f6f9f7;
            --ks-white: #ffffff;

            --ks-text: #17352a;
            --ks-muted: #61716a;
            --ks-light: #8a9892;

            --ks-border: #dfe7e2;

            --ks-warning: #d88900;
            --ks-warning-light: #fff7e6;

            --ks-danger: #c93434;
            --ks-danger-light: #fff0f0;

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
        }


        /* =====================================================
           NAVBAR
        ===================================================== */

        .ks-navbar {

            height: 74px;

            background: white;

            border-bottom:
                1px solid var(--ks-border);

        }


        .ks-nav-inner {

            width: min(94%, 1180px);

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

            text-decoration: none;

        }


        .ks-logo {

            width: 42px;
            height: 42px;

            border-radius: 12px;

            background:
                var(--ks-green-light);

            display: flex;

            align-items: center;

            justify-content: center;

        }


        .ks-logo svg {

            width: 30px;
            height: 30px;

        }


        .ks-brand-name {

            color:
                var(--ks-green);

            font-size: 21px;

            font-weight: 800;

        }


        .ks-brand-subtitle {

            display: block;

            margin-top: 2px;

            color:
                var(--ks-muted);

            font-size: 11px;

        }


        .ks-nav-right {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .ks-language {

            padding: 7px 11px;

            border:
                1px solid var(--ks-border);

            border-radius: 20px;

            color:
                var(--ks-muted);

            font-size: 12px;

            font-weight: 600;

        }


        .ks-back {

            padding: 9px 14px;

            border:
                1px solid var(--ks-green);

            border-radius: 9px;

            color:
                var(--ks-green);

            font-size: 13px;

            font-weight: 700;

            transition: 0.2s ease;

        }


        .ks-back:hover {

            background:
                var(--ks-green);

            color: white;

        }


        /* =====================================================
           PAGE
        ===================================================== */

        .status-page {

            width: min(94%, 1050px);

            margin: auto;

            padding: 34px 0 60px;

        }


        .page-heading {

            margin-bottom: 24px;

        }


        .page-heading h1 {

            margin: 0 0 7px;

            font-size: 31px;

            line-height: 1.2;

        }


        .page-heading p {

            margin: 0;

            color:
                var(--ks-muted);

            font-size: 14px;

            line-height: 1.6;

        }


        /* =====================================================
           MAIN STATUS CARD
        ===================================================== */

        .status-card {

            background:
                var(--ks-white);

            border:
                1px solid var(--ks-border);

            border-radius: 20px;

            box-shadow:
                var(--ks-shadow);

            overflow: hidden;

        }


        /* =====================================================
           TOKEN HEADER
        ===================================================== */

        .token-section {

            padding: 22px 26px;

            background:
                linear-gradient(
                    110deg,
                    #f0f9f3,
                    #e7f5eb
                );

            border-bottom:
                1px solid #dbe9df;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

        }


        .token-label {

            color:
                var(--ks-muted);

            font-size: 12px;

            margin-bottom: 5px;

        }


        .token {

            color:
                var(--ks-green);

            font-size: 22px;

            font-weight: 800;

            letter-spacing: 0.5px;

        }


        .token-status {

            padding: 8px 13px;

            border-radius: 20px;

            background:
                white;

            color:
                var(--ks-green);

            border:
                1px solid #cfe4d7;

            font-size: 12px;

            font-weight: 800;

        }


        .token-status.cancelled {

            color:
                var(--ks-danger);

            border-color:
                #efcccc;

            background:
                var(--ks-danger-light);

        }


        /* =====================================================
           CURRENT STATUS
        ===================================================== */

        .current-status {

            padding: 30px 26px 22px;

            text-align: center;

        }


        .status-icon {

            width: 64px;
            height: 64px;

            margin: auto;

            border-radius: 50%;

            background:
                var(--ks-green-light);

            color:
                var(--ks-green);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 28px;

            font-weight: 800;

        }


        .status-icon.cancelled {

            background:
                var(--ks-danger-light);

            color:
                var(--ks-danger);

        }


        .current-status h2 {

            margin: 14px 0 7px;

            color:
                var(--ks-text);

            font-size: 24px;

        }


        .current-status p {

            max-width: 650px;

            margin: auto;

            color:
                var(--ks-muted);

            font-size: 14px;

            line-height: 1.6;

        }


        /* =====================================================
           PROGRESS TRACKER
        ===================================================== */

        .progress-wrapper {

            padding: 12px 28px 30px;

        }


        .progress-track {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            position: relative;

        }


        .progress-line {

            position: absolute;

            top: 20px;

            left: 12.5%;

            right: 12.5%;

            height: 3px;

            background:
                #e2e9e5;

            z-index: 0;

        }


        .progress-line-fill {

            height: 100%;

            background:
                var(--ks-green);

            width: 0%;

            transition:
                width 0.3s ease;

        }


        .progress-step {

            position: relative;

            z-index: 2;

            text-align: center;

        }


        .step-circle {

            width: 42px;
            height: 42px;

            margin: 0 auto 10px;

            border-radius: 50%;

            border:
                3px solid #e1e8e4;

            background:
                white;

            color:
                var(--ks-light);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 14px;

            font-weight: 800;

        }


        .progress-step.completed .step-circle,
        .progress-step.active .step-circle {

            background:
                var(--ks-green);

            border-color:
                var(--ks-green);

            color: white;

        }


        .progress-step.active .step-circle {

            box-shadow:
                0 0 0 6px
                rgba(8, 116, 67, 0.10);

        }


        .step-title {

            display: block;

            color:
                var(--ks-text);

            font-size: 12px;

            font-weight: 800;

        }


        .step-description {

            display: block;

            max-width: 150px;

            margin: 5px auto 0;

            color:
                var(--ks-light);

            font-size: 11px;

            line-height: 1.4;

        }


        .progress-step.completed
        .step-title {

            color:
                var(--ks-green);

        }


        /* =====================================================
           NEXT STEP
        ===================================================== */

        .next-step {

            margin:
                0 26px 26px;

            padding: 17px 18px;

            border:
                1px solid #cfe4d7;

            border-radius: 12px;

            background:
                #f4fbf6;

            display: flex;

            align-items: center;

            gap: 13px;

        }


        .next-icon {

            width: 38px;
            height: 38px;

            flex-shrink: 0;

            border-radius: 50%;

            background:
                var(--ks-green);

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 17px;

        }


        .next-content strong {

            display: block;

            margin-bottom: 3px;

            color:
                var(--ks-green);

            font-size: 12px;

        }


        .next-content span {

            color:
                var(--ks-muted);

            font-size: 13px;

            line-height: 1.5;

        }


        /* =====================================================
           DETAILS
        ===================================================== */

        .details-section {

            padding: 0 26px 28px;

        }


        .details-header {

            margin-bottom: 15px;

        }


        .details-header h2 {

            margin: 0 0 5px;

            font-size: 19px;

        }


        .details-header p {

            margin: 0;

            color:
                var(--ks-muted);

            font-size: 12px;

        }


        .details-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 12px;

        }


        .detail {

            padding: 16px;

            border:
                1px solid var(--ks-border);

            border-radius: 11px;

            background:
                #fafcfb;

        }


        .detail-label {

            display: block;

            margin-bottom: 6px;

            color:
                var(--ks-light);

            font-size: 11px;

            font-weight: 600;

        }


        .detail-value {

            color:
                var(--ks-text);

            font-size: 13px;

            font-weight: 700;

            line-height: 1.45;

        }


        /* =====================================================
           COMPLETED BANNER
        ===================================================== */

        .completed-banner {

            margin:
                0 26px 26px;

            padding: 18px;

            border:
                1px solid #c9e4d3;

            border-radius: 12px;

            background:
                #f1faf4;

        }


        .completed-banner strong {

            display: block;

            margin-bottom: 5px;

            color:
                var(--ks-green);

            font-size: 14px;

        }


        .completed-banner span {

            color:
                var(--ks-muted);

            font-size: 13px;

        }


        /* =====================================================
           CANCELLED BANNER
        ===================================================== */

        .cancelled-banner {

            margin:
                0 26px 26px;

            padding: 18px;

            border:
                1px solid #edcccc;

            border-radius: 12px;

            background:
                var(--ks-danger-light);

        }


        .cancelled-banner strong {

            display: block;

            margin-bottom: 5px;

            color:
                var(--ks-danger);

        }


        .cancelled-banner span {

            color:
                var(--ks-muted);

            font-size: 13px;

        }


        /* =====================================================
           NO DATA
        ===================================================== */

        .no-data {

            background: white;

            border:
                1px solid var(--ks-border);

            border-radius: 18px;

            padding: 55px 25px;

            text-align: center;

            box-shadow:
                var(--ks-shadow);

        }


        .no-data-icon {

            width: 60px;
            height: 60px;

            margin: 0 auto 15px;

            border-radius: 50%;

            background:
                var(--ks-green-light);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 25px;

        }


        .no-data h2 {

            margin: 0 0 8px;

            font-size: 21px;

        }


        .no-data p {

            margin: 0;

            color:
                var(--ks-muted);

            font-size: 13px;

        }


        .primary-btn {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            margin-top: 20px;

            padding: 11px 16px;

            background:
                var(--ks-green);

            color: white;

            border-radius: 9px;

            font-size: 13px;

            font-weight: 800;

        }


        .primary-btn:hover {

            background:
                var(--ks-green-dark);

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 700px) {

            .ks-navbar {
                height: 66px;
            }


            .ks-brand-subtitle,
            .ks-language {
                display: none;
            }


            .ks-brand-name {
                font-size: 19px;
            }


            .ks-nav-right {
                gap: 7px;
            }


            .ks-back {
                padding: 8px 10px;
                font-size: 12px;
            }


            .status-page {

                width: 92%;

                padding:
                    25px 0 45px;

            }


            .page-heading h1 {

                font-size: 26px;

            }


            .token-section {

                align-items:
                    flex-start;

                flex-direction:
                    column;

                padding:
                    19px;

            }


            .token {

                font-size: 18px;

            }


            .current-status {

                padding:
                    25px 18px 18px;

            }


            .current-status h2 {

                font-size: 21px;

            }


            .progress-wrapper {

                padding:
                    12px 15px 25px;

            }


            .progress-track {

                grid-template-columns:
                    repeat(4, 1fr);

            }


            .progress-line {

                left: 12%;
                right: 12%;

            }


            .step-circle {

                width: 34px;
                height: 34px;

                border-width: 2px;

                font-size: 12px;

            }


            .step-title {

                font-size: 10px;

            }


            .step-description {

                display: none;

            }


            .next-step {

                margin:
                    0 17px 20px;

            }


            .details-section {

                padding:
                    0 17px 22px;

            }


            .details-grid {

                grid-template-columns:
                    1fr;

            }


            .completed-banner,
            .cancelled-banner {

                margin:
                    0 17px 20px;

            }

        }


        @media (max-width: 430px) {

            .step-title {

                font-size: 9px;

            }


            .progress-line {

                top: 17px;

            }


            .step-circle {

                width: 32px;
                height: 32px;

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
                    Farmer Portal
                </span>

            </span>

        </a>


        <div class="ks-nav-right">

            <span class="ks-language">

                <?php

                echo e(
                    $currentLanguage === 'hi'
                        ? 'हिन्दी'
                        : (
                            $currentLanguage === 'bn'
                                ? 'বাংলা'
                                : 'English'
                        )
                );

                ?>

            </span>


            <a
                href="dashboard.php"
                class="ks-back"
            >

                ←
                <?= e(pt('back_dashboard')) ?>

            </a>

        </div>

    </div>

</header>



<!-- =========================================================
     MAIN
========================================================= -->

<main class="status-page">


    <!-- PAGE HEADING -->

    <div class="page-heading">

        <h1>
            <?= e(pt('page_title')) ?>
        </h1>

        <p>
            <?= e(pt('page_subtitle')) ?>
        </p>

    </div>


    <?php if ($data): ?>


        <section class="status-card">


            <!-- =================================================
                 BOOKING TOKEN
            ================================================== -->

            <div class="token-section">

                <div>

                    <div class="token-label">
                        <?= e(pt('booking_token')) ?>
                    </div>

                    <div class="token">

                        <?= e(
                            $data['booking_token']
                        ) ?>

                    </div>

                </div>


                <span
                    class="
                        token-status
                        <?= $status === 'cancelled'
                            ? 'cancelled'
                            : ''
                        ?>
                    "
                >

                    <?php

                    if ($status === 'cancelled') {

                        echo e(
                            pt('cancelled')
                        );

                    } elseif ($status === 'accepted') {

                        echo e(
                            pt('completed')
                        );

                    } else {

                        echo e(
                            pt('active')
                        );

                    }

                    ?>

                </span>

            </div>



            <!-- =================================================
                 CURRENT STATUS
            ================================================== -->

            <div class="current-status">

                <div
                    class="
                        status-icon
                        <?= $status === 'cancelled'
                            ? 'cancelled'
                            : ''
                        ?>
                    "
                >

                    <?= e(
                        $currentStatusContent['icon']
                    ) ?>

                </div>


                <h2>

                    <?= e(
                        $currentStatusContent['title']
                    ) ?>

                </h2>


                <p>

                    <?= e(
                        $currentStatusContent['description']
                    ) ?>

                </p>

            </div>



            <?php if ($status !== 'cancelled'): ?>


                <!-- =============================================
                     PROGRESS
                ============================================== -->

                <div class="progress-wrapper">

                    <div class="progress-track">


                        <?php

                        $fillPercentage = 0;

                        if ($currentStep > 1) {

                            $fillPercentage =
                                (
                                    ($currentStep - 1)
                                    / 3
                                ) * 100;

                        }

                        ?>


                        <div class="progress-line">

                            <div
                                class="progress-line-fill"
                                style="
                                    width:
                                    <?= $fillPercentage ?>%;
                                "
                            ></div>

                        </div>


                        <?php foreach (
                            $steps as $number => $step
                        ): ?>

                            <?php

                            if (
                                $number <
                                $currentStep
                            ) {

                                $stepClass =
                                    'completed';

                            } elseif (
                                $number ===
                                $currentStep
                            ) {

                                $stepClass =
                                    'active';

                            } else {

                                $stepClass =
                                    '';

                            }

                            ?>


                            <div
                                class="
                                    progress-step
                                    <?= $stepClass ?>
                                "
                            >

                                <div class="step-circle">

                                    <?php

                                    if (
                                        $number <
                                        $currentStep
                                    ) {

                                        echo '✓';

                                    } else {

                                        echo $number;

                                    }

                                    ?>

                                </div>


                                <span class="step-title">

                                    <?= e(
                                        pt(
                                            $step['key']
                                        )
                                    ) ?>

                                </span>


                                <span
                                    class="step-description"
                                >

                                    <?= e(
                                        pt(
                                            $step['description']
                                        )
                                    ) ?>

                                </span>

                            </div>

                        <?php endforeach; ?>


                    </div>

                </div>


                <!-- =============================================
                     NEXT STEP
                ============================================== -->

                <?php if ($status !== 'accepted'): ?>

                    <div class="next-step">

                        <div class="next-icon">
                            →
                        </div>

                        <div class="next-content">

                            <strong>
                                <?= e(
                                    pt('next_step')
                                ) ?>
                            </strong>

                            <span>

                                <?= e(
                                    $nextStepText
                                ) ?>

                            </span>

                        </div>

                    </div>

                <?php else: ?>

                    <div class="completed-banner">

                        <strong>

                            ✓
                            <?= e(
                                pt('completed_title')
                            ) ?>

                        </strong>

                        <span>

                            <?= e(
                                pt('completed_desc')
                            ) ?>

                        </span>

                    </div>

                <?php endif; ?>


            <?php else: ?>


                <!-- =============================================
                     CANCELLED
                ============================================== -->

                <div class="cancelled-banner">

                    <strong>

                        ×
                        <?= e(
                            pt('cancelled_title')
                        ) ?>

                    </strong>

                    <span>

                        <?= e(
                            pt('cancelled_desc')
                        ) ?>

                    </span>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 BOOKING DETAILS
            ================================================== -->

            <div class="details-section">


                <div class="details-header">

                    <h2>
                        <?= e(
                            pt('booking_details')
                        ) ?>
                    </h2>

                    <p>
                        <?= e(
                            pt('booking_details_desc')
                        ) ?>
                    </p>

                </div>


                <div class="details-grid">


                    <div class="detail">

                        <span class="detail-label">
                            <?= e(
                                pt('procurement_centre')
                            ) ?>
                        </span>

                        <span class="detail-value">

                            <?= e(
                                $data['centre_name']
                            ) ?>

                        </span>

                    </div>


                    <div class="detail">

                        <span class="detail-label">
                            <?= e(
                                pt('venue')
                            ) ?>
                        </span>

                        <span class="detail-value">

                            <?= e(
                                $data['venue']
                            ) ?>

                        </span>

                    </div>


                    <div class="detail">

                        <span class="detail-label">
                            <?= e(
                                pt('scheduled_date')
                            ) ?>
                        </span>

                        <span class="detail-value">

                            <?= e(
                                $formattedDate
                            ) ?>

                        </span>

                    </div>


                    <div class="detail">

                        <span class="detail-label">
                            <?= e(
                                pt('time')
                            ) ?>
                        </span>

                        <span class="detail-value">

                            <?= e(
                                $formattedStartTime
                            ) ?>

                            -

                            <?= e(
                                $formattedEndTime
                            ) ?>

                        </span>

                    </div>


                    <?php if (
                        !empty(
                            $data['crop_name']
                        )
                    ): ?>

                        <div class="detail">

                            <span class="detail-label">
                                <?= e(
                                    pt('crop')
                                ) ?>
                            </span>

                            <span class="detail-value">

                                <?= e(
                                    $data['crop_name']
                                ) ?>

                            </span>

                        </div>

                    <?php endif; ?>


                    <?php if (
                        !empty(
                            $data['quantity']
                        )
                    ): ?>

                        <div class="detail">

                            <span class="detail-label">
                                <?= e(
                                    pt('quantity')
                                ) ?>
                            </span>

                            <span class="detail-value">

                                <?= e(
                                    $data['quantity']
                                ) ?>

                                <?= e(
                                    $data['unit'] ?? ''
                                ) ?>

                            </span>

                        </div>

                    <?php endif; ?>


                </div>

            </div>


        </section>


    <?php else: ?>


        <!-- =================================================
             NO BOOKING
        ================================================== -->

        <section class="no-data">

            <div class="no-data-icon">
                📅
            </div>


            <h2>
                <?= e(
                    pt('no_record')
                ) ?>
            </h2>


            <p>
                <?= e(
                    pt('no_record_desc')
                ) ?>
            </p>


            <a
                href="centres.php"
                class="primary-btn"
            >

                <?= e(
                    pt('find_centre')
                ) ?>

                →

            </a>

        </section>

    <?php endif; ?>


</main>


</body>

</html>