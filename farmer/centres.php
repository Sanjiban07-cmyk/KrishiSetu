<?php

session_start();

require_once "../config/database.php";
require_once "../config/language.php";


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


$user_id = (int)$_SESSION['user_id'];



/* =========================================================
   HELPERS
========================================================= */

function e($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


function calculateDistance(
    $lat1,
    $lng1,
    $lat2,
    $lng2
): ?float {

    if (
        $lat1 === null ||
        $lng1 === null ||
        $lat2 === null ||
        $lng2 === null ||
        $lat1 === '' ||
        $lng1 === '' ||
        $lat2 === '' ||
        $lng2 === ''
    ) {
        return null;
    }

    $earthRadius = 6371;

    $lat1 = deg2rad((float)$lat1);
    $lat2 = deg2rad((float)$lat2);

    $deltaLat =
        deg2rad((float)$lat2 - (float)$lat1);

    $deltaLng =
        deg2rad((float)$lng2 - (float)$lng1);

    $a =
        sin($deltaLat / 2) *
        sin($deltaLat / 2)

        +

        cos($lat1) *
        cos($lat2) *
        sin($deltaLng / 2) *
        sin($deltaLng / 2);

    $c =
        2 *
        atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

    return $earthRadius * $c;
}


function formatDateValue(?string $date): string
{
    if (!$date) {
        return "—";
    }

    $time = strtotime($date);

    if (!$time) {
        return "—";
    }

    return date("d M Y", $time);
}


function queueClass(int $queue): string
{
    if ($queue <= 10) {
        return "good";
    }

    if ($queue <= 25) {
        return "medium";
    }

    return "high";
}


/* =========================================================
   LOCAL PAGE TRANSLATIONS

   Kept here so this page never displays raw translation keys.
========================================================= */

$pageText = [

    'en' => [

        'back_dashboard' =>
            '← Dashboard',

        'portal' =>
            'Farmer Portal',

        'title' =>
            'Find Procurement Centres',

        'subtitle' =>
            'Compare procurement centres, queue conditions, capacity and purchase schedules before booking.',

        'notice_title' =>
            'Prototype Notice',

        'notice' =>
            'The current centre records are demo data created for the KrishiSetu prototype. Production deployment can use authorised West Bengal e-Paddy data/API integration.',

        'search_title' =>
            'Search Procurement Centres',

        'district' =>
            'District',

        'block' =>
            'Block',

        'search' =>
            'Search',

        'all_districts' =>
            'All Districts',

        'all_blocks' =>
            'All Blocks',

        'search_placeholder' =>
            'Centre name, code or village...',

        'apply_filters' =>
            'Search Centres',

        'clear' =>
            'Clear',

        'available' =>
            'Available Centres',

        'centres_found' =>
            'centres found',

        'centre_found' =>
            'centre found',

        'centre_code' =>
            'Centre Code',

        'location' =>
            'Location',

        'agency' =>
            'Agency',

        'purchase_date' =>
            'Purchase Date',

        'distance' =>
            'Distance',

        'queue' =>
            'Current Queue',

        'capacity' =>
            'Capacity',

        'remaining' =>
            'remaining',

        'low_queue' =>
            'Low Queue',

        'moderate_queue' =>
            'Moderate Queue',

        'high_queue' =>
            'High Queue',

        'slots_available' =>
            'Slots Available',

        'view_book' =>
            'View Centre & Book Slot',

        'open' =>
            'Open',

        'limited' =>
            'Limited',

        'no_slots' =>
            'No Slots',

        'no_centres' =>
            'No procurement centres found',

        'no_centres_text' =>
            'Try changing the district, block or search term.',

        'clear_filters' =>
            'Clear Filters',

        'location_unavailable' =>
            'Location unavailable',

        'km_away' =>
            'km away',

        'farmers' =>
            'farmers',

        'today' =>
            'Today',

        'schedule' =>
            'Purchase Schedule'
    ],


    'hi' => [

        'back_dashboard' =>
            '← डैशबोर्ड',

        'portal' =>
            'किसान पोर्टल',

        'title' =>
            'खरीद केंद्र खोजें',

        'subtitle' =>
            'बुकिंग से पहले खरीद केंद्रों, कतार, क्षमता और खरीद कार्यक्रम की तुलना करें।',

        'notice_title' =>
            'प्रोटोटाइप सूचना',

        'notice' =>
            'वर्तमान केंद्र रिकॉर्ड कृषिसेतु प्रोटोटाइप के लिए डेमो डेटा हैं। वास्तविक उपयोग में अधिकृत पश्चिम बंगाल ई-पैडी डेटा/API का उपयोग किया जा सकता है।',

        'search_title' =>
            'खरीद केंद्र खोजें',

        'district' =>
            'जिला',

        'block' =>
            'ब्लॉक',

        'search' =>
            'खोजें',

        'all_districts' =>
            'सभी जिले',

        'all_blocks' =>
            'सभी ब्लॉक',

        'search_placeholder' =>
            'केंद्र का नाम, कोड या गाँव...',

        'apply_filters' =>
            'केंद्र खोजें',

        'clear' =>
            'साफ करें',

        'available' =>
            'उपलब्ध केंद्र',

        'centres_found' =>
            'केंद्र मिले',

        'centre_found' =>
            'केंद्र मिला',

        'centre_code' =>
            'केंद्र कोड',

        'location' =>
            'स्थान',

        'agency' =>
            'एजेंसी',

        'purchase_date' =>
            'खरीद तारीख',

        'distance' =>
            'दूरी',

        'queue' =>
            'वर्तमान कतार',

        'capacity' =>
            'क्षमता',

        'remaining' =>
            'शेष',

        'low_queue' =>
            'कम कतार',

        'moderate_queue' =>
            'मध्यम कतार',

        'high_queue' =>
            'अधिक कतार',

        'slots_available' =>
            'स्लॉट उपलब्ध',

        'view_book' =>
            'केंद्र देखें और स्लॉट बुक करें',

        'open' =>
            'उपलब्ध',

        'limited' =>
            'सीमित',

        'no_slots' =>
            'स्लॉट नहीं',

        'no_centres' =>
            'कोई खरीद केंद्र नहीं मिला',

        'no_centres_text' =>
            'जिला, ब्लॉक या खोज शब्द बदलकर देखें।',

        'clear_filters' =>
            'फिल्टर साफ करें',

        'location_unavailable' =>
            'स्थान उपलब्ध नहीं',

        'km_away' =>
            'किमी दूर',

        'farmers' =>
            'किसान',

        'today' =>
            'आज',

        'schedule' =>
            'खरीद कार्यक्रम'
    ],


    'bn' => [

        'back_dashboard' =>
            '← ড্যাশবোর্ড',

        'portal' =>
            'কৃষক পোর্টাল',

        'title' =>
            'ক্রয় কেন্দ্র খুঁজুন',

        'subtitle' =>
            'বুকিংয়ের আগে ক্রয় কেন্দ্র, সারি, ক্ষমতা এবং ক্রয় সময়সূচি তুলনা করুন।',

        'notice_title' =>
            'প্রোটোটাইপ তথ্য',

        'notice' =>
            'বর্তমান কেন্দ্রের তথ্য কৃষিসেতু প্রোটোটাইপের জন্য ডেমো ডেটা। বাস্তব ব্যবহারে অনুমোদিত পশ্চিমবঙ্গ ই-প্যাডি ডেটা/API ব্যবহার করা যেতে পারে।',

        'search_title' =>
            'ক্রয় কেন্দ্র খুঁজুন',

        'district' =>
            'জেলা',

        'block' =>
            'ব্লক',

        'search' =>
            'অনুসন্ধান',

        'all_districts' =>
            'সব জেলা',

        'all_blocks' =>
            'সব ব্লক',

        'search_placeholder' =>
            'কেন্দ্রের নাম, কোড বা গ্রাম...',

        'apply_filters' =>
            'কেন্দ্র খুঁজুন',

        'clear' =>
            'পরিষ্কার',

        'available' =>
            'উপলব্ধ কেন্দ্র',

        'centres_found' =>
            'টি কেন্দ্র পাওয়া গেছে',

        'centre_found' =>
            'টি কেন্দ্র পাওয়া গেছে',

        'centre_code' =>
            'কেন্দ্র কোড',

        'location' =>
            'স্থান',

        'agency' =>
            'এজেন্সি',

        'purchase_date' =>
            'ক্রয়ের তারিখ',

        'distance' =>
            'দূরত্ব',

        'queue' =>
            'বর্তমান সারি',

        'capacity' =>
            'ক্ষমতা',

        'remaining' =>
            'বাকি',

        'low_queue' =>
            'কম সারি',

        'moderate_queue' =>
            'মাঝারি সারি',

        'high_queue' =>
            'বেশি সারি',

        'slots_available' =>
            'স্লট উপলব্ধ',

        'view_book' =>
            'কেন্দ্র দেখুন ও স্লট বুক করুন',

        'open' =>
            'উপলব্ধ',

        'limited' =>
            'সীমিত',

        'no_slots' =>
            'স্লট নেই',

        'no_centres' =>
            'কোনও ক্রয় কেন্দ্র পাওয়া যায়নি',

        'no_centres_text' =>
            'জেলা, ব্লক অথবা অনুসন্ধানের শব্দ পরিবর্তন করে দেখুন।',

        'clear_filters' =>
            'ফিল্টার পরিষ্কার করুন',

        'location_unavailable' =>
            'অবস্থান পাওয়া যায়নি',

        'km_away' =>
            'কিমি দূরে',

        'farmers' =>
            'কৃষক',

        'today' =>
            'আজ',

        'schedule' =>
            'ক্রয় সময়সূচি'
    ]
];


$currentLanguage =
    $_SESSION['language'] ?? 'en';

if (
    !isset($pageText[$currentLanguage])
) {
    $currentLanguage = 'en';
}


function ct(string $key): string
{
    global $pageText, $currentLanguage;

    return
        $pageText[$currentLanguage][$key]
        ??
        $pageText['en'][$key]
        ??
        $key;
}



/* =========================================================
   GET FARMER LOCATION
========================================================= */

$farmerLat = null;
$farmerLng = null;

$farmerStmt = $conn->prepare("
    SELECT latitude, longitude
    FROM farmers
    WHERE user_id = ?
    LIMIT 1
");

$farmerStmt->bind_param(
    "i",
    $user_id
);

$farmerStmt->execute();

$farmerResult =
    $farmerStmt->get_result();

$farmer =
    $farmerResult->fetch_assoc();

$farmerStmt->close();


if ($farmer) {

    $farmerLat =
        $farmer['latitude'] !== null
            ? (float)$farmer['latitude']
            : null;

    $farmerLng =
        $farmer['longitude'] !== null
            ? (float)$farmer['longitude']
            : null;
}



/* =========================================================
   FILTERS
========================================================= */

$district =
    trim($_GET['district'] ?? '');

$block =
    trim($_GET['block'] ?? '');

$search =
    trim($_GET['search'] ?? '');



/* =========================================================
   GET CENTRES

   IMPORTANT:
   agency_type is the actual database column.
========================================================= */

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
";


$params = [];
$types = "";


if ($district !== '') {

    $sql .= "
        AND district = ?
    ";

    $params[] =
        $district;

    $types .= "s";
}


if ($block !== '') {

    $sql .= "
        AND block = ?
    ";

    $params[] =
        $block;

    $types .= "s";
}


if ($search !== '') {

    $sql .= "
        AND (
            centre_name LIKE ?
            OR centre_code LIKE ?
            OR village LIKE ?
            OR block LIKE ?
        )
    ";

    $searchTerm =
        "%" . $search . "%";

    $params[] =
        $searchTerm;

    $params[] =
        $searchTerm;

    $params[] =
        $searchTerm;

    $params[] =
        $searchTerm;

    $types .= "ssss";
}


$sql .= "
    ORDER BY
        current_queue ASC,
        current_bookings ASC,
        centre_name ASC
";


$stmt =
    $conn->prepare($sql);


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}


$stmt->execute();


$result =
    $stmt->get_result();


$centres = [];


while ($row = $result->fetch_assoc()) {

    $centreId =
        (int)$row['id'];


    /* -----------------------------------------------------
       REAL AVAILABLE SLOT COUNT
    ----------------------------------------------------- */

    $slotStmt = $conn->prepare("
        SELECT
            COUNT(*) AS slot_count,
            COALESCE(
                SUM(
                    GREATEST(
                        capacity - booked_count,
                        0
                    )
                ),
                0
            ) AS remaining_capacity
        FROM slots
        WHERE centre_id = ?
          AND status = 'available'
          AND slot_date >= CURDATE()
          AND booked_count < capacity
    ");

    $slotStmt->bind_param(
        "i",
        $centreId
    );

    $slotStmt->execute();

    $slotData =
        $slotStmt
            ->get_result()
            ->fetch_assoc();

    $slotStmt->close();


    $row['available_slot_count'] =
        (int)($slotData['slot_count'] ?? 0);


    $row['slot_remaining_capacity'] =
        (int)($slotData['remaining_capacity'] ?? 0);


    /* -----------------------------------------------------
       DISTANCE
    ----------------------------------------------------- */

    $row['distance'] =
        calculateDistance(
            $farmerLat,
            $farmerLng,
            $row['latitude'],
            $row['longitude']
        );


    /* -----------------------------------------------------
       OCCUPANCY
    ----------------------------------------------------- */

    $capacity =
        (int)$row['total_capacity'];

    $bookings =
        (int)$row['current_bookings'];


    if ($capacity > 0) {

        $row['occupancy'] =
            min(
                100,
                round(
                    ($bookings / $capacity) * 100
                )
            );

    } else {

        $row['occupancy'] = 0;
    }


    /* -----------------------------------------------------
       STATUS
    ----------------------------------------------------- */

    if (
        $row['available_slot_count'] <= 0
    ) {

        $row['availability_status'] =
            'none';

    } elseif (
        $row['slot_remaining_capacity'] < 10
    ) {

        $row['availability_status'] =
            'limited';

    } else {

        $row['availability_status'] =
            'open';
    }


    $centres[] =
        $row;
}


$stmt->close();



/* =========================================================
   FILTER OPTIONS
========================================================= */

$filterResult =
    $conn->query("
        SELECT DISTINCT
            district,
            block
        FROM procurement_centres
        WHERE status = 'active'
        ORDER BY
            district,
            block
    ");


$districts = [];
$blocks = [];


while (
    $row =
    $filterResult->fetch_assoc()
) {

    if (
        !empty($row['district'])
    ) {

        $districts[] =
            $row['district'];
    }


    if (
        !empty($row['block'])
    ) {

        $blocks[] =
            $row['block'];
    }
}


$districts =
    array_values(
        array_unique($districts)
    );


$blocks =
    array_values(
        array_unique($blocks)
    );

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
    <?= e(ct('title')) ?> | KrishiSetu
</title>

<link
    rel="stylesheet"
    href="../assets/css/style.css"
>


<style>

/* =========================================================
   BASE
========================================================= */

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    background:
        #f7f9f7;

    color:
        #17352a;

    font-family:
        Arial,
        Helvetica,
        sans-serif;
}


a {
    text-decoration: none;
}


/* =========================================================
   HEADER
========================================================= */

.centre-header {

    background:
        #ffffff;

    border-bottom:
        1px solid #dfe7e2;

    position:
        sticky;

    top:
        0;

    z-index:
        100;
}


.centre-header-inner {

    max-width:
        1180px;

    margin:
        auto;

    padding:
        13px 20px;

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
        #087443;

    font-size:
        22px;

    font-weight:
        800;
}


.brand-mark {

    width:
        40px;

    height:
        40px;

    border-radius:
        11px;

    display:
        grid;

    place-items:
        center;

    background:
        #e8f5ee;

    font-size:
        22px;
}


.header-right {

    display:
        flex;

    align-items:
        center;

    gap:
        15px;
}


.portal-label {

    color:
        #61716a;

    font-size:
        14px;
}


.back-btn {

    color:
        #087443;

    border:
        1px solid #cfe0d5;

    background:
        #ffffff;

    border-radius:
        9px;

    padding:
        10px 15px;

    font-weight:
        700;
}


.back-btn:hover {

    background:
        #e8f5ee;
}


/* =========================================================
   PAGE
========================================================= */

.page {

    max-width:
        1180px;

    margin:
        0 auto;

    padding:
        34px 20px 60px;
}


.page-title {

    margin:
        0 0 8px;

    font-size:
        34px;

    line-height:
        1.2;
}


.page-subtitle {

    margin:
        0;

    max-width:
        850px;

    color:
        #61716a;

    font-size:
        16px;

    line-height:
        1.6;
}


/* =========================================================
   PROTOTYPE NOTICE
========================================================= */

.prototype-note {

    margin:
        24px 0;

    padding:
        15px 18px;

    background:
        #fff7e6;

    border:
        1px solid #efd89f;

    border-radius:
        12px;

    color:
        #74591f;

    font-size:
        13px;

    line-height:
        1.55;
}


.prototype-note strong {

    color:
        #654800;
}


/* =========================================================
   SEARCH PANEL
========================================================= */

.filters {

    background:
        #ffffff;

    border:
        1px solid #dfe7e2;

    border-radius:
        17px;

    padding:
        22px;

    margin-bottom:
        32px;

    box-shadow:
        0 4px 15px
        rgba(25,65,45,.04);
}


.filter-title {

    font-size:
        18px;

    font-weight:
        800;

    margin-bottom:
        17px;
}


.filter-grid {

    display:
        grid;

    grid-template-columns:
        1fr 1fr 1.5fr auto;

    gap:
        12px;

    align-items:
        end;
}


.filter-group {

    display:
        flex;

    flex-direction:
        column;

    gap:
        7px;
}


.filter-group label {

    font-size:
        12px;

    color:
        #61716a;

    font-weight:
        700;
}


.filter-group input,
.filter-group select {

    width:
        100%;

    height:
        46px;

    padding:
        0 13px;

    border:
        1px solid #d6e1db;

    border-radius:
        9px;

    background:
        #ffffff;

    color:
        #17352a;

    font-size:
        14px;

    outline:
        none;
}


.filter-group input:focus,
.filter-group select:focus {

    border-color:
        #087443;

    box-shadow:
        0 0 0 3px
        rgba(8,116,67,.08);
}


.filter-btn {

    height:
        46px;

    border:
        0;

    border-radius:
        9px;

    padding:
        0 19px;

    background:
        #087443;

    color:
        #ffffff;

    font-weight:
        800;

    cursor:
        pointer;

    white-space:
        nowrap;
}


.filter-btn:hover {

    background:
        #055c35;
}


.clear-link {

    display:
        inline-block;

    margin-top:
        11px;

    color:
        #087443;

    font-size:
        13px;

    font-weight:
        700;
}


/* =========================================================
   RESULTS HEADER
========================================================= */

.results-header {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        15px;

    margin-bottom:
        15px;
}


.results-header h2 {

    margin:
        0;

    font-size:
        24px;
}


.results-count {

    color:
        #61716a;

    font-size:
        14px;
}


/* =========================================================
   CENTRE GRID
========================================================= */

.centre-grid {

    display:
        grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap:
        18px;
}


/* =========================================================
   CENTRE CARD
========================================================= */

.centre-card {

    background:
        #ffffff;

    border:
        1px solid #dfe7e2;

    border-radius:
        17px;

    overflow:
        hidden;

    display:
        flex;

    flex-direction:
        column;

    min-width:
        0;

    transition:
        transform .15s ease,
        box-shadow .15s ease,
        border-color .15s ease;
}


.centre-card:hover {

    transform:
        translateY(-2px);

    border-color:
        #b8d3c3;

    box-shadow:
        0 9px 25px
        rgba(20,70,45,.08);
}


/* =========================================================
   CARD TOP
========================================================= */

.card-top {

    padding:
        18px 18px 14px;

    border-bottom:
        1px solid #edf1ee;
}


.badges {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        flex-start;

    gap:
        8px;

    margin-bottom:
        13px;
}


.type-badge {

    display:
        inline-block;

    background:
        #e8f5ee;

    color:
        #087443;

    padding:
        6px 9px;

    border-radius:
        7px;

    font-size:
        11px;

    font-weight:
        800;

    line-height:
        1.3;
}


.availability-badge {

    padding:
        6px 9px;

    border-radius:
        7px;

    font-size:
        11px;

    font-weight:
        800;

    white-space:
        nowrap;
}


.availability-badge.open {

    background:
        #e8f5ee;

    color:
        #087443;
}


.availability-badge.limited {

    background:
        #fff7e6;

    color:
        #9a6900;
}


.availability-badge.none {

    background:
        #fdecec;

    color:
        #b52e2e;
}


.centre-name {

    margin:
        0 0 6px;

    font-size:
        19px;

    line-height:
        1.3;
}


.centre-code {

    color:
        #8a9892;

    font-size:
        12px;
}


/* =========================================================
   CARD BODY
========================================================= */

.card-body {

    padding:
        17px 18px;

    flex:
        1;
}


.data-row {

    display:
        flex;

    gap:
        10px;

    padding:
        9px 0;

    border-bottom:
        1px solid #f0f3f1;
}


.data-row:last-child {

    border-bottom:
        0;
}


.data-icon {

    width:
        30px;

    height:
        30px;

    flex:
        0 0 30px;

    border-radius:
        8px;

    background:
        #f2f6f3;

    display:
        grid;

    place-items:
        center;

    font-size:
        15px;
}


.data-content {

    min-width:
        0;
}


.data-label {

    display:
        block;

    color:
        #8a9892;

    font-size:
        11px;

    margin-bottom:
        3px;
}


.data-value {

    display:
        block;

    color:
        #17352a;

    font-size:
        13px;

    font-weight:
        700;

    overflow-wrap:
        anywhere;
}


/* =========================================================
   QUEUE
========================================================= */

.queue-row {

    margin-top:
        13px;

    padding:
        12px;

    background:
        #f7faf8;

    border-radius:
        10px;
}


.queue-head {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        10px;

    margin-bottom:
        8px;
}


.queue-label {

    color:
        #61716a;

    font-size:
        12px;

    font-weight:
        700;
}


.queue-number {

    font-size:
        15px;

    font-weight:
        800;
}


.queue-status {

    font-size:
        11px;

    font-weight:
        800;
}


.queue-status.good {

    color:
        #16834d;
}


.queue-status.medium {

    color:
        #b87900;
}


.queue-status.high {

    color:
        #c93434;
}


/* =========================================================
   CAPACITY
========================================================= */

.capacity-head {

    display:
        flex;

    justify-content:
        space-between;

    gap:
        10px;

    margin:
        13px 0 7px;
}


.capacity-label {

    color:
        #61716a;

    font-size:
        11px;
}


.capacity-value {

    font-size:
        11px;

    font-weight:
        800;
}


.capacity-bar {

    height:
        7px;

    background:
        #e8eeea;

    border-radius:
        99px;

    overflow:
        hidden;
}


.capacity-fill {

    height:
        100%;

    background:
        #087443;

    border-radius:
        99px;
}


/* =========================================================
   DISTANCE
========================================================= */

.distance-line {

    margin-top:
        12px;

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    padding:
        10px 12px;

    background:
        #f5f9f6;

    border-radius:
        9px;
}


.distance-label {

    color:
        #61716a;

    font-size:
        12px;
}


.distance-value {

    color:
        #087443;

    font-size:
        13px;

    font-weight:
        800;
}


/* =========================================================
   CARD FOOTER
========================================================= */

.card-footer {

    padding:
        14px 18px 18px;
}


.book-btn {

    width:
        100%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    min-height:
        44px;

    background:
        #087443;

    color:
        #ffffff;

    border-radius:
        9px;

    font-size:
        13px;

    font-weight:
        800;

    transition:
        background .15s ease;
}


.book-btn:hover {

    background:
        #055c35;
}


.no-book-btn {

    width:
        100%;

    display:
        block;

    text-align:
        center;

    padding:
        12px;

    border-radius:
        9px;

    background:
        #f1f3f2;

    color:
        #7b8580;

    font-size:
        13px;

    font-weight:
        700;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {

    background:
        #ffffff;

    border:
        1px solid #dfe7e2;

    border-radius:
        17px;

    padding:
        50px 25px;

    text-align:
        center;
}


.empty-icon {

    width:
        60px;

    height:
        60px;

    margin:
        0 auto 15px;

    border-radius:
        50%;

    background:
        #e8f5ee;

    display:
        grid;

    place-items:
        center;

    font-size:
        27px;
}


.empty-state h3 {

    margin:
        0 0 8px;

    font-size:
        21px;
}


.empty-state p {

    max-width:
        450px;

    margin:
        0 auto 18px;

    color:
        #61716a;

    line-height:
        1.55;

    font-size:
        14px;
}


.empty-btn {

    display:
        inline-block;

    background:
        #087443;

    color:
        #ffffff;

    padding:
        10px 15px;

    border-radius:
        8px;

    font-weight:
        700;

    font-size:
        13px;
}


/* =========================================================
   BOTTOM NOTICE
========================================================= */

.bottom-note {

    margin-top:
        25px;

    color:
        #8a9892;

    font-size:
        12px;

    text-align:
        center;

    line-height:
        1.5;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 950px) {

    .centre-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }


    .filter-grid {

        grid-template-columns:
            1fr 1fr;
    }


    .filter-group:nth-child(3) {

        grid-column:
            1 / 2;
    }


    .filter-btn {

        width:
            100%;
    }
}


@media (max-width: 680px) {

    .centre-header-inner {

        padding:
            11px 15px;
    }


    .portal-label {

        display:
            none;
    }


    .page {

        padding:
            25px 15px 45px;
    }


    .page-title {

        font-size:
            28px;
    }


    .page-subtitle {

        font-size:
            14px;
    }


    .filter-grid {

        grid-template-columns:
            1fr;
    }


    .filter-group:nth-child(3) {

        grid-column:
            auto;
    }


    .centre-grid {

        grid-template-columns:
            1fr;
    }


    .results-header {

        align-items:
            flex-start;

        flex-direction:
            column;

        gap:
            5px;
    }
}


@media (max-width: 430px) {

    .brand {

        font-size:
            19px;
    }


    .brand-mark {

        width:
            35px;

        height:
            35px;
    }


    .filters {

        padding:
            17px;
    }


    .card-top,
    .card-body {

        padding-left:
            15px;

        padding-right:
            15px;
    }


    .card-footer {

        padding-left:
            15px;

        padding-right:
            15px;
    }
}

</style>

</head>


<body>


<!-- =====================================================
     HEADER
====================================================== -->

<header class="centre-header">

    <div class="centre-header-inner">


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


        <div class="header-right">

            <span class="portal-label">
                <?= e(ct('portal')) ?>
            </span>


            <a
                href="dashboard.php"
                class="back-btn"
            >
                <?= e(ct('back_dashboard')) ?>
            </a>

        </div>

    </div>

</header>



<main class="page">


<!-- =====================================================
     PAGE INTRO
====================================================== -->

<section>

    <h1 class="page-title">
        <?= e(ct('title')) ?>
    </h1>


    <p class="page-subtitle">
        <?= e(ct('subtitle')) ?>
    </p>

</section>



<!-- =====================================================
     PROTOTYPE NOTICE
====================================================== -->

<div class="prototype-note">

    <strong>
        <?= e(ct('notice_title')) ?>:
    </strong>

    <?= e(ct('notice')) ?>

</div>



<!-- =====================================================
     SEARCH / FILTERS
====================================================== -->

<section class="filters">


    <div class="filter-title">

        <?= e(ct('search_title')) ?>

    </div>


    <form method="GET">


        <div class="filter-grid">


            <!-- DISTRICT -->

            <div class="filter-group">

                <label>
                    <?= e(ct('district')) ?>
                </label>


                <select name="district">

                    <option value="">

                        <?= e(
                            ct('all_districts')
                        ) ?>

                    </option>


                    <?php foreach ($districts as $item): ?>

                        <option
                            value="<?= e($item) ?>"
                            <?= $district === $item
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= e($item) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>



            <!-- BLOCK -->

            <div class="filter-group">

                <label>
                    <?= e(ct('block')) ?>
                </label>


                <select name="block">

                    <option value="">

                        <?= e(
                            ct('all_blocks')
                        ) ?>

                    </option>


                    <?php foreach ($blocks as $item): ?>

                        <option
                            value="<?= e($item) ?>"
                            <?= $block === $item
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= e($item) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>



            <!-- SEARCH -->

            <div class="filter-group">

                <label>
                    <?= e(ct('search')) ?>
                </label>


                <input
                    type="text"
                    name="search"
                    value="<?= e($search) ?>"
                    placeholder="<?= e(
                        ct('search_placeholder')
                    ) ?>"
                >

            </div>



            <!-- BUTTON -->

            <button
                type="submit"
                class="filter-btn"
            >

                <?= e(ct('apply_filters')) ?>

            </button>


        </div>


        <?php if (
            $district !== '' ||
            $block !== '' ||
            $search !== ''
        ): ?>

            <a
                href="centres.php"
                class="clear-link"
            >
                <?= e(ct('clear')) ?>
            </a>

        <?php endif; ?>


    </form>

</section>



<!-- =====================================================
     RESULTS HEADER
====================================================== -->

<div class="results-header">


    <h2>

        <?= e(ct('available')) ?>

    </h2>


    <div class="results-count">

        <?= count($centres) ?>

        <?=
            count($centres) === 1
                ? e(ct('centre_found'))
                : e(ct('centres_found'))
        ?>

    </div>


</div>



<!-- =====================================================
     CENTRES
====================================================== -->

<?php if (!empty($centres)): ?>


<div class="centre-grid">


<?php foreach ($centres as $centre): ?>


    <?php

    $queue =
        (int)$centre['current_queue'];

    $capacity =
        (int)$centre['total_capacity'];

    $bookings =
        (int)$centre['current_bookings'];

    $remaining =
        max(
            0,
            $capacity - $bookings
        );


    $queueType =
        queueClass($queue);


    if ($queueType === 'good') {

        $queueText =
            ct('low_queue');

    } elseif ($queueType === 'medium') {

        $queueText =
            ct('moderate_queue');

    } else {

        $queueText =
            ct('high_queue');
    }


    $availability =
        $centre['availability_status'];


    if ($availability === 'open') {

        $availabilityText =
            ct('open');

    } elseif ($availability === 'limited') {

        $availabilityText =
            ct('limited');

    } else {

        $availabilityText =
            ct('no_slots');
    }


    ?>


    <article class="centre-card">


        <!-- CARD TOP -->

        <div class="card-top">


            <div class="badges">


                <span class="type-badge">

                    <?= e(
                        $centre['centre_type']
                        ?: 'Procurement Centre'
                    ) ?>

                </span>


                <span
                    class="
                        availability-badge
                        <?= e($availability) ?>
                    "
                >

                    <?= e($availabilityText) ?>

                </span>


            </div>



            <h3 class="centre-name">

                <?= e(
                    $centre['centre_name']
                ) ?>

            </h3>


            <div class="centre-code">

                <?= e(ct('centre_code')) ?>:

                <?= e(
                    $centre['centre_code']
                ) ?>

            </div>


        </div>



        <!-- CARD BODY -->

        <div class="card-body">


            <!-- LOCATION -->

            <div class="data-row">


                <div class="data-icon">
                    📍
                </div>


                <div class="data-content">

                    <span class="data-label">
                        <?= e(ct('location')) ?>
                    </span>


                    <span class="data-value">

                        <?= e(
                            $centre['village']
                            ?: $centre['address']
                            ?: '—'
                        ) ?>


                        <?php if (
                            !empty($centre['district'])
                        ): ?>

                            ,
                            <?= e(
                                $centre['district']
                            ) ?>

                        <?php endif; ?>


                    </span>

                </div>

            </div>



            <!-- AGENCY -->

            <div class="data-row">


                <div class="data-icon">
                    🏢
                </div>


                <div class="data-content">

                    <span class="data-label">
                        <?= e(ct('agency')) ?>
                    </span>


                    <span class="data-value">

                        <?= e(
                            $centre['agency_type']
                            ?: '—'
                        ) ?>

                    </span>

                </div>

            </div>



            <!-- PURCHASE DATE -->

            <div class="data-row">


                <div class="data-icon">
                    📅
                </div>


                <div class="data-content">

                    <span class="data-label">
                        <?= e(
                            ct('purchase_date')
                        ) ?>
                    </span>


                    <span class="data-value">

                        <?= e(
                            formatDateValue(
                                $centre['purchase_date']
                            )
                        ) ?>

                    </span>

                </div>

            </div>



            <!-- QUEUE -->

            <div class="queue-row">


                <div class="queue-head">


                    <span class="queue-label">

                        <?= e(ct('queue')) ?>

                    </span>


                    <span
                        class="
                            queue-status
                            <?= e($queueType) ?>
                        "
                    >

                        <?= e($queueText) ?>

                    </span>


                </div>


                <div>

                    <span class="queue-number">

                        <?= $queue ?>

                        <small
                            style="
                                font-size:11px;
                                color:#61716a;
                                font-weight:600;
                            "
                        >

                            <?= e(
                                ct('farmers')
                            ) ?>

                        </small>

                    </span>

                </div>

            </div>



            <!-- CAPACITY -->

            <div class="capacity-head">

                <span class="capacity-label">

                    <?= e(ct('capacity')) ?>

                </span>


                <span class="capacity-value">

                    <?= $bookings ?>
                    /
                    <?= $capacity ?>

                    &nbsp;·&nbsp;

                    <?= $remaining ?>

                    <?= e(ct('remaining')) ?>

                </span>

            </div>


            <div class="capacity-bar">

                <div
                    class="capacity-fill"
                    style="
                        width:
                        <?= (int)$centre['occupancy'] ?>%;
                    "
                ></div>

            </div>



            <!-- DISTANCE -->

            <div class="distance-line">


                <span class="distance-label">

                    <?= e(ct('distance')) ?>

                </span>


                <span class="distance-value">


                    <?php if (
                        $centre['distance'] !== null
                    ): ?>

                        📍

                        <?= number_format(
                            $centre['distance'],
                            1
                        ) ?>

                        <?= e(
                            ct('km_away')
                        ) ?>


                    <?php else: ?>

                        <?= e(
                            ct(
                                'location_unavailable'
                            )
                        ) ?>

                    <?php endif; ?>


                </span>

            </div>


        </div>



        <!-- CARD FOOTER -->

        <div class="card-footer">


            <?php if (
                $centre['available_slot_count'] > 0
            ): ?>


                <a
                    href="
                        booking.php?centre_id=
                        <?= (int)$centre['id'] ?>
                    "
                    class="book-btn"
                >

                    <?= e(
                        ct('view_book')
                    ) ?>

                    →

                </a>


            <?php else: ?>


                <span class="no-book-btn">

                    <?= e(
                        ct('no_slots')
                    ) ?>

                </span>


            <?php endif; ?>


        </div>


    </article>


<?php endforeach; ?>


</div>


<?php else: ?>


<!-- =====================================================
     EMPTY
====================================================== -->

<section class="empty-state">


    <div class="empty-icon">
        🔎
    </div>


    <h3>

        <?= e(
            ct('no_centres')
        ) ?>

    </h3>


    <p>

        <?= e(
            ct('no_centres_text')
        ) ?>

    </p>


    <a
        href="centres.php"
        class="empty-btn"
    >

        <?= e(
            ct('clear_filters')
        ) ?>

    </a>


</section>


<?php endif; ?>



<!-- =====================================================
     FOOTER NOTE
====================================================== -->

<div class="bottom-note">

    <?= e(ct('notice')) ?>

</div>


</main>


</body>

</html>