<?php
session_start();

require_once "../config/database.php";
require_once "../config/language.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../login.php");
    exit;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$centre_id = (int)($_GET['centre_id'] ?? 0);
$recommendedSlotId = (int)($_GET['slot_id'] ?? 0);

if ($centre_id <= 0) {
    die("Invalid procurement centre.");
}


/* =========================================================
   GET CENTRE
========================================================= */

$centreStmt = $conn->prepare("
    SELECT
        id,
        centre_name,
        centre_code,
        centre_type,
        district,
        block,
       agency_type,
        venue,
        address,
        current_queue,
        total_capacity,
        current_bookings
    FROM procurement_centres
    WHERE id = ?
      AND status = 'active'
    LIMIT 1
");

$centreStmt->bind_param("i", $centre_id);
$centreStmt->execute();

$centre = $centreStmt->get_result()->fetch_assoc();

$centreStmt->close();

if (!$centre) {
    die("Procurement centre not found.");
}


/* =========================================================
   GET AVAILABLE SLOTS
========================================================= */

$slotStmt = $conn->prepare("
    SELECT
        id,
        slot_date,
        start_time,
        end_time,
        capacity,
        booked_count,
        status
    FROM slots
    WHERE centre_id = ?
      AND status = 'available'
      AND booked_count < capacity
      AND slot_date >= CURDATE()
    ORDER BY slot_date ASC, start_time ASC
");

$slotStmt->bind_param("i", $centre_id);
$slotStmt->execute();

$slotResult = $slotStmt->get_result();

$slots = [];

$remainingCapacity = 0;

while ($row = $slotResult->fetch_assoc()) {

    $row['remaining'] =
        max(
            0,
            (int)$row['capacity'] -
            (int)$row['booked_count']
        );

    $remainingCapacity += $row['remaining'];

    $slots[] = $row;
}

$slotStmt->close();


/* =========================================================
   BASIC CENTRE STATS
========================================================= */

$availableSlotCount = count($slots);

$currentQueue =
    max(
        0,
        (int)($centre['current_queue'] ?? 0)
    );

$totalCapacity =
    max(
        0,
        (int)($centre['total_capacity'] ?? 0)
    );

$currentBookings =
    max(
        0,
        (int)($centre['current_bookings'] ?? 0)
    );


/* =========================================================
   CENTRE STATUS
========================================================= */

if ($availableSlotCount === 0) {

    $centreStatusKey = 'no_slots';
    $centreStatusClass = 'closed';

} elseif ($remainingCapacity < 10) {

    $centreStatusKey = 'limited';
    $centreStatusClass = 'limited';

} else {

    $centreStatusKey = 'open';
    $centreStatusClass = 'open';
}


/* =========================================================
   LOCAL TRANSLATIONS
   These prevent missing-key text on this page.
========================================================= */

$bookingTranslations = [

    'en' => [

        'back_to_centres' =>
            '← Back to Procurement Centres',

        'title' =>
            'Book Procurement Slot',

        'subtitle' =>
            'Choose a suitable date and time for your paddy procurement visit.',

        'centre_status_open' =>
            'Open · Slots Available',

        'centre_status_limited' =>
            'Limited Availability',

        'centre_status_no_slots' =>
            'No Slots Available',

        'centre_code' =>
            'Centre Code',

        'centre_type' =>
            'Centre Type',

        'district' =>
            'District',

        'block' =>
            'Block',

        'agency' =>
            'Agency',

        'venue' =>
            'Venue',

        'address' =>
            'Address',

        'current_queue' =>
            'Current Queue',

        'farmers' =>
            'farmers',

        'remaining_capacity' =>
            'Remaining Capacity',

        'available_capacity' =>
            'available places',

        'queue_wait' =>
            'Estimated Queue Wait',

        'minutes' =>
            'min',

        'slots' =>
            'Available Slots',

        'slot_subtitle' =>
            'Select one available date and time for your procurement visit.',

        'recommended' =>
            'Recommended',

        'slots_left' =>
            'places left',

        'confirm' =>
            'Confirm Booking',

        'no_slots_title' =>
            'No slots available',

        'no_slots_text' =>
            'This centre currently has no bookable procurement slots. Please choose another centre.',

        'view_other' =>
            'View Other Centres →',

        'notice' =>
            'Please arrive at the selected centre during your booked slot. Your booking token will be generated after confirmation.'
    ],


    'hi' => [

        'back_to_centres' =>
            '← खरीद केंद्रों पर वापस जाएँ',

        'title' =>
            'खरीद स्लॉट बुक करें',

        'subtitle' =>
            'धान खरीद के लिए अपनी सुविधानुसार तारीख और समय चुनें।',

        'centre_status_open' =>
            'उपलब्ध · स्लॉट खुले हैं',

        'centre_status_limited' =>
            'सीमित उपलब्धता',

        'centre_status_no_slots' =>
            'कोई स्लॉट उपलब्ध नहीं',

        'centre_code' =>
            'केंद्र कोड',

        'centre_type' =>
            'केंद्र का प्रकार',

        'district' =>
            'जिला',

        'block' =>
            'ब्लॉक',

        'agency' =>
            'एजेंसी',

        'venue' =>
            'स्थान',

        'address' =>
            'पता',

        'current_queue' =>
            'वर्तमान कतार',

        'farmers' =>
            'किसान',

        'remaining_capacity' =>
            'शेष क्षमता',

        'available_capacity' =>
            'स्थान उपलब्ध',

        'queue_wait' =>
            'अनुमानित कतार प्रतीक्षा',

        'minutes' =>
            'मिनट',

        'slots' =>
            'उपलब्ध स्लॉट',

        'slot_subtitle' =>
            'अपनी खरीद यात्रा के लिए एक उपलब्ध तारीख और समय चुनें।',

        'recommended' =>
            'सुझाया गया',

        'slots_left' =>
            'स्थान शेष',

        'confirm' =>
            'बुकिंग की पुष्टि करें',

        'no_slots_title' =>
            'कोई स्लॉट उपलब्ध नहीं है',

        'no_slots_text' =>
            'इस केंद्र पर अभी कोई बुक करने योग्य खरीद स्लॉट नहीं है। कृपया दूसरा केंद्र चुनें।',

        'view_other' =>
            'अन्य केंद्र देखें →',

        'notice' =>
            'कृपया अपने बुक किए गए स्लॉट के समय चयनित केंद्र पर पहुँचें। पुष्टि के बाद आपका बुकिंग टोकन बनाया जाएगा।'
    ],


    'bn' => [

        'back_to_centres' =>
            '← ক্রয় কেন্দ্রগুলিতে ফিরে যান',

        'title' =>
            'ক্রয় স্লট বুক করুন',

        'subtitle' =>
            'ধান ক্রয়ের জন্য আপনার সুবিধামতো তারিখ ও সময় বেছে নিন।',

        'centre_status_open' =>
            'উপলব্ধ · স্লট খোলা আছে',

        'centre_status_limited' =>
            'সীমিত প্রাপ্যতা',

        'centre_status_no_slots' =>
            'কোনও স্লট উপলব্ধ নেই',

        'centre_code' =>
            'কেন্দ্র কোড',

        'centre_type' =>
            'কেন্দ্রের ধরন',

        'district' =>
            'জেলা',

        'block' =>
            'ব্লক',

        'agency' =>
            'এজেন্সি',

        'venue' =>
            'স্থান',

        'address' =>
            'ঠিকানা',

        'current_queue' =>
            'বর্তমান সারি',

        'farmers' =>
            'কৃষক',

        'remaining_capacity' =>
            'অবশিষ্ট ক্ষমতা',

        'available_capacity' =>
            'স্থান উপলব্ধ',

        'queue_wait' =>
            'আনুমানিক সারি অপেক্ষা',

        'minutes' =>
            'মিনিট',

        'slots' =>
            'উপলব্ধ স্লট',

        'slot_subtitle' =>
            'আপনার ক্রয় যাত্রার জন্য একটি উপলব্ধ তারিখ ও সময় বেছে নিন।',

        'recommended' =>
            'প্রস্তাবিত',

        'slots_left' =>
            'স্থান বাকি',

        'confirm' =>
            'বুকিং নিশ্চিত করুন',

        'no_slots_title' =>
            'কোনও স্লট উপলব্ধ নেই',

        'no_slots_text' =>
            'এই কেন্দ্রে বর্তমানে কোনও বুকযোগ্য ক্রয় স্লট নেই। অনুগ্রহ করে অন্য কেন্দ্র বেছে নিন।',

        'view_other' =>
            'অন্য কেন্দ্র দেখুন →',

        'notice' =>
            'আপনার বুক করা সময়ে নির্বাচিত কেন্দ্রে পৌঁছান। নিশ্চিত করার পরে আপনার বুকিং টোকেন তৈরি হবে।'
    ]
];


$lang = $_SESSION['language'] ?? 'en';

if (!isset($bookingTranslations[$lang])) {
    $lang = 'en';
}


function bt(string $key): string
{
    global $bookingTranslations, $lang;

    return
        $bookingTranslations[$lang][$key]
        ??
        $bookingTranslations['en'][$key]
        ??
        $key;
}


/* =========================================================
   STATUS / HELPERS
========================================================= */

$statusText =
    bt(
        'centre_status_' .
        $centreStatusKey
    );

$estimatedWait =
    $currentQueue * 5;


function formatDateSafe(?string $date): string
{
    if (!$date) {
        return '—';
    }

    $timestamp = strtotime($date);

    return $timestamp
        ? date('d M Y', $timestamp)
        : '—';
}


function formatTimeSafe(?string $time): string
{
    if (!$time) {
        return '—';
    }

    $timestamp = strtotime($time);

    return $timestamp
        ? date('h:i A', $timestamp)
        : '—';
}

?>

<!DOCTYPE html>

<html lang="<?= e($lang) ?>">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    <?= e(bt('title')) ?> | KrishiSetu
</title>

<link
    rel="stylesheet"
    href="../assets/css/style.css"
>

<style>

/* =========================================================
   PAGE
========================================================= */

:root {

    --green: #087443;
    --green-dark: #055c35;
    --green-light: #e8f5ee;

    --bg: #f7f9f7;

    --text: #17352a;
    --muted: #61716a;

    --border: #dfe7e2;

    --amber: #f2b84b;
    --amber-bg: #fff7e6;

    --danger: #c93434;
}


* {
    box-sizing: border-box;
}


body {

    margin: 0;

    background:
        var(--bg);

    color:
        var(--text);

    font-family:
        Arial,
        Helvetica,
        sans-serif;
}


/* =========================================================
   TOP BAR
========================================================= */

.topbar {

    background:
        #ffffff;

    border-bottom:
        1px solid var(--border);
}


.topbar-inner {

    max-width:
        1120px;

    margin:
        0 auto;

    padding:
        14px 20px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        20px;
}


.brand {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    color:
        var(--green);

    text-decoration:
        none;

    font-size:
        22px;

    font-weight:
        800;
}


.brand-mark {

    width:
        38px;

    height:
        38px;

    display:
        grid;

    place-items:
        center;

    background:
        var(--green-light);

    border-radius:
        10px;

    font-size:
        21px;
}


.portal {

    color:
        var(--muted);

    font-size:
        14px;
}


/* =========================================================
   MAIN
========================================================= */

.page {

    max-width:
        1120px;

    margin:
        0 auto;

    padding:
        30px 20px 60px;
}


.back-link {

    display:
        inline-flex;

    align-items:
        center;

    color:
        var(--green);

    text-decoration:
        none;

    font-weight:
        700;

    margin-bottom:
        24px;
}


.hero {

    margin-bottom:
        24px;
}


.hero h1 {

    margin:
        0 0 8px;

    font-size:
        34px;

    line-height:
        1.2;
}


.hero p {

    margin:
        0;

    color:
        var(--muted);

    font-size:
        16px;

    line-height:
        1.6;
}


/* =========================================================
   COMMON CARDS
========================================================= */

.centre-card,
.slots-card,
.notice {

    background:
        #ffffff;

    border:
        1px solid var(--border);

    border-radius:
        18px;
}


/* =========================================================
   CENTRE CARD
========================================================= */

.centre-card {

    overflow:
        hidden;

    margin-bottom:
        24px;
}


.centre-head {

    padding:
        24px 26px;

    border-bottom:
        1px solid var(--border);

    display:
        flex;

    justify-content:
        space-between;

    gap:
        20px;

    align-items:
        flex-start;
}


.centre-title-wrap h2 {

    margin:
        0 0 7px;

    font-size:
        25px;

    color:
        var(--green);
}


.centre-code {

    color:
        var(--muted);

    font-size:
        14px;
}


.status-pill {

    flex:
        0 0 auto;

    padding:
        9px 14px;

    border-radius:
        999px;

    font-size:
        13px;

    font-weight:
        800;

    white-space:
        nowrap;
}


.status-pill.open {

    background:
        var(--green-light);

    color:
        var(--green);
}


.status-pill.limited {

    background:
        var(--amber-bg);

    color:
        #946600;
}


.status-pill.closed {

    background:
        #fdecec;

    color:
        var(--danger);
}


.centre-body {

    padding:
        22px 26px 26px;
}


/* =========================================================
   QUICK STATS
========================================================= */

.quick-stats {

    display:
        grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap:
        12px;

    margin-bottom:
        20px;
}


.quick-stat {

    background:
        #f5f8f6;

    border:
        1px solid #e6ece8;

    border-radius:
        12px;

    padding:
        15px;
}


.quick-stat-label {

    display:
        block;

    color:
        var(--muted);

    font-size:
        12px;

    margin-bottom:
        6px;
}


.quick-stat-value {

    display:
        block;

    color:
        var(--text);

    font-size:
        20px;

    font-weight:
        800;
}


.quick-stat-value small {

    font-size:
        12px;

    font-weight:
        600;

    color:
        var(--muted);
}


/* =========================================================
   CENTRE INFORMATION
========================================================= */

.info-grid {

    display:
        grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap:
        12px;
}


.info-item {

    background:
        #fbfcfb;

    border:
        1px solid #e8eeea;

    border-radius:
        12px;

    padding:
        14px 15px;

    min-width:
        0;
}


.info-label {

    display:
        block;

    color:
        var(--muted);

    font-size:
        12px;

    margin-bottom:
        6px;
}


.info-value {

    display:
        block;

    font-size:
        15px;

    font-weight:
        700;

    overflow-wrap:
        anywhere;
}


/* =========================================================
   SLOTS
========================================================= */

.slots-card {

    padding:
        26px;
}


.section-head {

    display:
        flex;

    align-items:
        flex-end;

    justify-content:
        space-between;

    gap:
        20px;

    margin-bottom:
        18px;
}


.section-head h2 {

    margin:
        0 0 5px;

    font-size:
        23px;
}


.section-head p {

    margin:
        0;

    color:
        var(--muted);

    font-size:
        14px;
}


.slot-count {

    color:
        var(--green);

    background:
        var(--green-light);

    padding:
        7px 11px;

    border-radius:
        999px;

    font-size:
        12px;

    font-weight:
        800;

    white-space:
        nowrap;
}


.slot-list {

    display:
        grid;

    gap:
        12px;
}


.slot-option {

    position:
        relative;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        18px;

    padding:
        17px 18px;

    border:
        1px solid var(--border);

    border-radius:
        13px;

    cursor:
        pointer;

    transition:
        border-color .15s ease,
        background .15s ease,
        box-shadow .15s ease;
}


.slot-option:hover {

    border-color:
        #9fcab1;

    background:
        #fbfefc;
}


.slot-option:has(input:checked) {

    border:
        2px solid var(--green);

    background:
        #f3faf6;

    box-shadow:
        0 3px 12px
        rgba(8,116,67,.08);
}


.slot-option input {

    position:
        absolute;

    opacity:
        0;

    pointer-events:
        none;
}


.slot-main {

    display:
        flex;

    align-items:
        center;

    gap:
        14px;

    min-width:
        0;
}


.calendar-icon {

    width:
        44px;

    height:
        44px;

    flex:
        0 0 44px;

    border-radius:
        10px;

    background:
        var(--green-light);

    display:
        grid;

    place-items:
        center;

    font-size:
        21px;
}


.slot-date {

    font-weight:
        800;

    font-size:
        16px;

    margin-bottom:
        5px;
}


.slot-time {

    color:
        var(--muted);

    font-size:
        14px;
}


.slot-right {

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

    flex:
        0 0 auto;
}


.recommended-tag {

    background:
        var(--amber);

    color:
        #4d3500;

    padding:
        5px 8px;

    border-radius:
        6px;

    font-size:
        11px;

    font-weight:
        800;
}


.remaining-tag {

    color:
        var(--green);

    font-size:
        13px;

    font-weight:
        700;
}


.radio-ui {

    width:
        20px;

    height:
        20px;

    border:
        2px solid #aebbb4;

    border-radius:
        50%;

    position:
        relative;
}


.slot-option:has(input:checked)
.radio-ui {

    border-color:
        var(--green);
}


.slot-option:has(input:checked)
.radio-ui::after {

    content:
        "";

    position:
        absolute;

    width:
        10px;

    height:
        10px;

    border-radius:
        50%;

    background:
        var(--green);

    top:
        3px;

    left:
        3px;
}


/* =========================================================
   CONFIRM BUTTON
========================================================= */

.confirm-btn {

    width:
        100%;

    margin-top:
        18px;

    padding:
        15px 20px;

    border:
        0;

    border-radius:
        10px;

    background:
        var(--green);

    color:
        #ffffff;

    font-size:
        16px;

    font-weight:
        800;

    cursor:
        pointer;
}


.confirm-btn:hover {

    background:
        var(--green-dark);
}


/* =========================================================
   NO SLOTS
========================================================= */

.no-slots {

    text-align:
        center;

    padding:
        35px 20px;

    background:
        var(--amber-bg);

    border:
        1px solid #f0d79f;

    border-radius:
        12px;
}


.no-slots h3 {

    margin:
        0 0 8px;

    color:
        #795600;
}


.no-slots p {

    margin:
        0 0 18px;

    color:
        #7a6a45;

    line-height:
        1.5;
}


.secondary-btn {

    display:
        inline-block;

    color:
        var(--green);

    border:
        1px solid var(--green);

    padding:
        10px 15px;

    border-radius:
        8px;

    text-decoration:
        none;

    font-weight:
        700;

    background:
        #ffffff;
}


/* =========================================================
   NOTICE
========================================================= */

.notice {

    margin-top:
        18px;

    padding:
        15px 18px;

    background:
        #f7faf8;

    color:
        var(--muted);

    font-size:
        13px;

    line-height:
        1.55;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 760px) {

    .page {

        padding:
            24px 15px 45px;
    }


    .topbar-inner {

        padding:
            12px 15px;
    }


    .portal {

        display:
            none;
    }


    .hero h1 {

        font-size:
            28px;
    }


    .centre-head {

        flex-direction:
            column;
    }


    .quick-stats {

        grid-template-columns:
            1fr;
    }


    .info-grid {

        grid-template-columns:
            1fr;
    }


    .slots-card,
    .centre-body {

        padding:
            20px;
    }


    .section-head {

        align-items:
            flex-start;

        flex-direction:
            column;
    }


    .slot-option {

        align-items:
            flex-start;
    }


    .slot-main {

        align-items:
            flex-start;
    }


    .slot-right {

        margin-left:
            auto;

        flex-direction:
            column;

        align-items:
            flex-end;
    }
}


@media (max-width: 480px) {

    .brand {

        font-size:
            20px;
    }


    .brand-mark {

        width:
            34px;

        height:
            34px;
    }


    .slot-main {

        gap:
            10px;
    }


    .calendar-icon {

        width:
            38px;

        height:
            38px;

        flex-basis:
            38px;

        font-size:
            18px;
    }


    .slot-date {

        font-size:
            14px;
    }


    .slot-time {

        font-size:
            12px;
    }


    .remaining-tag {

        font-size:
            11px;
    }
}

</style>

</head>


<body>


<header class="topbar">

    <div class="topbar-inner">

        <a
            href="dashboard.php"
            class="brand"
        >

            <span class="brand-mark">
                🌾
            </span>

            <span>
                KrishiSetu
            </span>

        </a>


        <span class="portal">
            Farmer Portal
        </span>

    </div>

</header>


<main class="page">


    <a
        href="centres.php"
        class="back-link"
    >
        <?= e(bt('back_to_centres')) ?>
    </a>


    <section class="hero">

        <h1>
            <?= e(bt('title')) ?>
        </h1>

        <p>
            <?= e(bt('subtitle')) ?>
        </p>

    </section>


    <!-- =================================================
         CENTRE
    ================================================== -->

    <section class="centre-card">


        <div class="centre-head">


            <div class="centre-title-wrap">

                <h2>
                    <?= e($centre['centre_name']) ?>
                </h2>


                <div class="centre-code">

                    <?= e(bt('centre_code')) ?>:

                    <strong>
                        <?= e($centre['centre_code']) ?>
                    </strong>

                </div>

            </div>


            <span
                class="status-pill <?= e($centreStatusClass) ?>"
            >
                <?= e($statusText) ?>
            </span>


        </div>


        <div class="centre-body">


            <!-- QUICK STATS -->

            <div class="quick-stats">


                <div class="quick-stat">

                    <span class="quick-stat-label">
                        <?= e(bt('current_queue')) ?>
                    </span>

                    <span class="quick-stat-value">

                        <?= $currentQueue ?>

                        <small>
                            <?= e(bt('farmers')) ?>
                        </small>

                    </span>

                </div>


                <div class="quick-stat">

                    <span class="quick-stat-label">
                        <?= e(bt('remaining_capacity')) ?>
                    </span>

                    <span class="quick-stat-value">

                        <?= $remainingCapacity ?>

                        <small>
                            <?= e(bt('available_capacity')) ?>
                        </small>

                    </span>

                </div>


                <div class="quick-stat">

                    <span class="quick-stat-label">
                        <?= e(bt('queue_wait')) ?>
                    </span>

                    <span class="quick-stat-value">

                        ~<?= $estimatedWait ?>

                        <small>
                            <?= e(bt('minutes')) ?>
                        </small>

                    </span>

                </div>


            </div>


            <!-- CENTRE INFORMATION -->

            <div class="info-grid">


                <div class="info-item">

                    <span class="info-label">
                        <?= e(bt('centre_type')) ?>
                    </span>

                    <span class="info-value">
                        <?= e($centre['centre_type'] ?? '—') ?>
                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        <?= e(bt('district')) ?>
                    </span>

                    <span class="info-value">
                        <?= e($centre['district'] ?? '—') ?>
                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        <?= e(bt('block')) ?>
                    </span>

                    <span class="info-value">
                        <?= e($centre['block'] ?? '—') ?>
                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        <?= e(bt('agency')) ?>
                    </span>

                    <span class="info-value">
                        <?= e($centre['agency_type'] ?? '—') ?>
                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        <?= e(bt('venue')) ?>
                    </span>

                    <span class="info-value">
                        <?= e($centre['venue'] ?? '—') ?>
                    </span>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        <?= e(bt('address')) ?>
                    </span>

                    <span class="info-value">
                        <?= e($centre['address'] ?? '—') ?>
                    </span>

                </div>


            </div>

        </div>

    </section>


    <!-- =================================================
         AVAILABLE SLOTS
    ================================================== -->

    <section class="slots-card">


        <div class="section-head">


            <div>

                <h2>
                    <?= e(bt('slots')) ?>
                </h2>

                <p>
                    <?= e(bt('slot_subtitle')) ?>
                </p>

            </div>


            <span class="slot-count">

                <?= $availableSlotCount ?>

                <?= e(bt('slots')) ?>

            </span>


        </div>


        <?php if ($availableSlotCount > 0): ?>


            <form
                action="booking_process.php"
                method="POST"
            >


                <input
                    type="hidden"
                    name="centre_id"
                    value="<?= $centre_id ?>"
                >


                <div class="slot-list">


                    <?php foreach ($slots as $slot): ?>


                        <?php

                        $isRecommended =
                            $recommendedSlotId ===
                            (int)$slot['id'];

                        ?>


                        <label class="slot-option">


                            <input
                                type="radio"
                                name="slot_id"
                                value="<?= (int)$slot['id'] ?>"
                                <?= $isRecommended ? 'checked' : '' ?>
                                required
                            >


                            <div class="slot-main">


                                <div class="calendar-icon">
                                    📅
                                </div>


                                <div>


                                    <div class="slot-date">

                                        <?= e(
                                            formatDateSafe(
                                                $slot['slot_date']
                                            )
                                        ) ?>

                                    </div>


                                    <div class="slot-time">

                                        <?= e(
                                            formatTimeSafe(
                                                $slot['start_time']
                                            )
                                        ) ?>

                                        –

                                        <?= e(
                                            formatTimeSafe(
                                                $slot['end_time']
                                            )
                                        ) ?>

                                    </div>


                                </div>


                            </div>


                            <div class="slot-right">


                                <?php if ($isRecommended): ?>

                                    <span class="recommended-tag">

                                        ★

                                        <?= e(
                                            bt('recommended')
                                        ) ?>

                                    </span>

                                <?php endif; ?>


                                <span class="remaining-tag">

                                    <?= (int)$slot['remaining'] ?>

                                    <?= e(
                                        bt('slots_left')
                                    ) ?>

                                </span>


                                <span class="radio-ui"></span>


                            </div>


                        </label>


                    <?php endforeach; ?>


                </div>


                <button
                    type="submit"
                    class="confirm-btn"
                >

                    <?= e(bt('confirm')) ?>

                    →

                </button>


            </form>


        <?php else: ?>


            <div class="no-slots">


                <h3>
                    <?= e(bt('no_slots_title')) ?>
                </h3>


                <p>
                    <?= e(bt('no_slots_text')) ?>
                </p>


                <a
                    href="centres.php"
                    class="secondary-btn"
                >
                    <?= e(bt('view_other')) ?>
                </a>


            </div>


        <?php endif; ?>


    </section>


    <!-- INFORMATION NOTICE -->

    <div class="notice">

        ℹ️

        <?= e(bt('notice')) ?>

    </div>


</main>


</body>

</html>