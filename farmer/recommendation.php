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


$userId = (int)$_SESSION['user_id'];


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


function formatDateValue(?string $date): string
{
    if (empty($date)) {
        return "—";
    }

    $time = strtotime($date);

    if (!$time) {
        return "—";
    }

    return date("d M Y", $time);
}


function formatTimeValue(?string $time): string
{
    if (empty($time)) {
        return "—";
    }

    $timestamp = strtotime($time);

    if (!$timestamp) {
        return "—";
    }

    return date("h:i A", $timestamp);
}


function calculateDistance(
    ?float $lat1,
    ?float $lng1,
    ?float $lat2,
    ?float $lng2
): ?float {

    if (
        $lat1 === null ||
        $lng1 === null ||
        $lat2 === null ||
        $lng2 === null
    ) {
        return null;
    }

    $earthRadius = 6371;

    $lat1Rad = deg2rad($lat1);
    $lat2Rad = deg2rad($lat2);

    $deltaLat =
        deg2rad($lat2 - $lat1);

    $deltaLng =
        deg2rad($lng2 - $lng1);

    $a =
        sin($deltaLat / 2) *
        sin($deltaLat / 2)
        +
        cos($lat1Rad) *
        cos($lat2Rad) *
        sin($deltaLng / 2) *
        sin($deltaLng / 2);

    $a = min(1, max(0, $a));

    $c =
        2 *
        atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

    return $earthRadius * $c;
}


/* =========================================================
   PAGE TRANSLATIONS
========================================================= */

$pageText = [

    'en' => [

        'portal' =>
            'Farmer Portal',

        'dashboard' =>
            '← Dashboard',

        'title' =>
            'Smart Recommendation',

        'subtitle' =>
            'KrishiSetu compares active procurement centres and recommends an option based on queue, slot availability, remaining capacity and purchase schedule.',

        'recommended' =>
            '⭐ Recommended for You',

        'centre_code' =>
            'Centre Code',

        'queue_reason' =>
            'farmers currently in the queue',

        'slots_available' =>
            'slots available',

        'no_slots' =>
            'no available slots',

        'remaining_capacity' =>
            'remaining capacity',

        'recommendation_reason' =>
            'This centre currently offers the strongest combination of the factors used by KrishiSetu.',

        'recommended_slot' =>
            'Recommended Slot',

        'estimated_wait' =>
            'Estimated Waiting Time',

        'minutes' =>
            'minutes',

        'view_book' =>
            'View & Book This Centre',

        'score' =>
            'Recommendation Score',

        'out_of' =>
            '/ 100',

        'queue' =>
            'Queue',

        'availability' =>
            'Slot Availability',

        'capacity' =>
            'Remaining Capacity',

        'schedule' =>
            'Purchase Schedule',

        'how_decides' =>
            'How KrishiSetu Decides',

        'how_decides_text' =>
            'The recommendation is rule-based and uses the following factors. Distance is shown separately for information and is not included in the visible recommendation score.',

        'queue_condition' =>
            'Queue Condition',

        'other_options' =>
            'Other Options',

        'other_options_text' =>
            'You can still choose any other active centre.',

        'distance' =>
            'Distance',

        'current_queue' =>
            'Current Queue',

        'capacity_used' =>
            'Capacity',

        'remaining' =>
            'remaining',

        'purchase_date' =>
            'Purchase Date',

        'best_slot' =>
            'Best Available Slot',

        'farmers' =>
            'farmers',

        'km_away' =>
            'km away',

        'open' =>
            'Slots Available',

        'limited' =>
            'Limited Availability',

        'no_slots_status' =>
            'No Slots Available',

        'view_centre' =>
            'View & Book',

        'prototype_notice' =>
            'Prototype Notice',

        'prototype_text' =>
            'This recommendation engine uses transparent rule-based logic for the KrishiSetu prototype. Production deployment can use authorised West Bengal e-Paddy data and real-time centre information.',

        'no_centres' =>
            'No active procurement centres are currently available.',

        'back_dashboard' =>
            'Back to Dashboard',

        'location_unavailable' =>
            'Location unavailable',

        'low_queue' =>
            'Low queue',

        'moderate_queue' =>
            'Moderate queue',

        'high_queue' =>
            'High queue'
    ],


    'hi' => [

        'portal' =>
            'किसान पोर्टल',

        'dashboard' =>
            '← डैशबोर्ड',

        'title' =>
            'स्मार्ट सुझाव',

        'subtitle' =>
            'कृषिसेतु कतार, स्लॉट की उपलब्धता, शेष क्षमता और खरीद कार्यक्रम के आधार पर सक्रिय खरीद केंद्रों की तुलना करता है।',

        'recommended' =>
            '⭐ आपके लिए सुझाया गया',

        'centre_code' =>
            'केंद्र कोड',

        'queue_reason' =>
            'किसान वर्तमान कतार में',

        'slots_available' =>
            'स्लॉट उपलब्ध',

        'no_slots' =>
            'कोई स्लॉट उपलब्ध नहीं',

        'remaining_capacity' =>
            'शेष क्षमता',

        'recommendation_reason' =>
            'यह केंद्र कृषिसेतु द्वारा उपयोग किए जाने वाले कारकों का सबसे अच्छा संयोजन प्रदान करता है।',

        'recommended_slot' =>
            'सुझाया गया स्लॉट',

        'estimated_wait' =>
            'अनुमानित प्रतीक्षा समय',

        'minutes' =>
            'मिनट',

        'view_book' =>
            'केंद्र देखें और बुक करें',

        'score' =>
            'सुझाव स्कोर',

        'out_of' =>
            '/ 100',

        'queue' =>
            'कतार',

        'availability' =>
            'स्लॉट उपलब्धता',

        'capacity' =>
            'शेष क्षमता',

        'schedule' =>
            'खरीद कार्यक्रम',

        'how_decides' =>
            'कृषिसेतु कैसे सुझाव देता है',

        'how_decides_text' =>
            'यह सुझाव नियम-आधारित है। दूरी केवल जानकारी के लिए दिखाई जाती है और दिखाई देने वाले सुझाव स्कोर में शामिल नहीं है।',

        'queue_condition' =>
            'कतार की स्थिति',

        'other_options' =>
            'अन्य विकल्प',

        'other_options_text' =>
            'आप किसी अन्य सक्रिय केंद्र को भी चुन सकते हैं।',

        'distance' =>
            'दूरी',

        'current_queue' =>
            'वर्तमान कतार',

        'capacity_used' =>
            'क्षमता',

        'remaining' =>
            'शेष',

        'purchase_date' =>
            'खरीद तारीख',

        'best_slot' =>
            'सबसे अच्छा उपलब्ध स्लॉट',

        'farmers' =>
            'किसान',

        'km_away' =>
            'किमी दूर',

        'open' =>
            'स्लॉट उपलब्ध',

        'limited' =>
            'सीमित उपलब्धता',

        'no_slots_status' =>
            'स्लॉट उपलब्ध नहीं',

        'view_centre' =>
            'केंद्र देखें और बुक करें',

        'prototype_notice' =>
            'प्रोटोटाइप सूचना',

        'prototype_text' =>
            'यह सुझाव प्रणाली कृषिसेतु प्रोटोटाइप के लिए पारदर्शी नियम-आधारित लॉजिक का उपयोग करती है। वास्तविक उपयोग में अधिकृत पश्चिम बंगाल ई-पैडी डेटा और वास्तविक समय केंद्र जानकारी का उपयोग किया जा सकता है।',

        'no_centres' =>
            'वर्तमान में कोई सक्रिय खरीद केंद्र उपलब्ध नहीं है।',

        'back_dashboard' =>
            'डैशबोर्ड पर वापस जाएँ',

        'location_unavailable' =>
            'स्थान उपलब्ध नहीं',

        'low_queue' =>
            'कम कतार',

        'moderate_queue' =>
            'मध्यम कतार',

        'high_queue' =>
            'अधिक कतार'
    ],


    'bn' => [

        'portal' =>
            'কৃষক পোর্টাল',

        'dashboard' =>
            '← ড্যাশবোর্ড',

        'title' =>
            'স্মার্ট সুপারিশ',

        'subtitle' =>
            'কৃষিসেতু সারি, স্লটের প্রাপ্যতা, অবশিষ্ট ক্ষমতা এবং ক্রয় সময়সূচির ভিত্তিতে সক্রিয় ক্রয় কেন্দ্রগুলির তুলনা করে।',

        'recommended' =>
            '⭐ আপনার জন্য সুপারিশ',

        'centre_code' =>
            'কেন্দ্র কোড',

        'queue_reason' =>
            'কৃষক বর্তমানে সারিতে',

        'slots_available' =>
            'স্লট উপলব্ধ',

        'no_slots' =>
            'কোনও স্লট উপলব্ধ নেই',

        'remaining_capacity' =>
            'অবশিষ্ট ক্ষমতা',

        'recommendation_reason' =>
            'এই কেন্দ্রটি কৃষিসেতুর ব্যবহৃত বিষয়গুলির সবচেয়ে ভালো সমন্বয় প্রদান করছে।',

        'recommended_slot' =>
            'সুপারিশকৃত স্লট',

        'estimated_wait' =>
            'আনুমানিক অপেক্ষার সময়',

        'minutes' =>
            'মিনিট',

        'view_book' =>
            'কেন্দ্র দেখুন ও বুক করুন',

        'score' =>
            'সুপারিশ স্কোর',

        'out_of' =>
            '/ 100',

        'queue' =>
            'সারি',

        'availability' =>
            'স্লটের প্রাপ্যতা',

        'capacity' =>
            'অবশিষ্ট ক্ষমতা',

        'schedule' =>
            'ক্রয় সময়সূচি',

        'how_decides' =>
            'কৃষিসেতু কীভাবে সুপারিশ করে',

        'how_decides_text' =>
            'এই সুপারিশ নিয়ম-ভিত্তিক। দূরত্ব শুধুমাত্র তথ্যের জন্য দেখানো হয় এবং দৃশ্যমান সুপারিশ স্কোরে অন্তর্ভুক্ত নয়।',

        'queue_condition' =>
            'সারির অবস্থা',

        'other_options' =>
            'অন্যান্য বিকল্প',

        'other_options_text' =>
            'আপনি অন্য কোনও সক্রিয় কেন্দ্রও বেছে নিতে পারেন।',

        'distance' =>
            'দূরত্ব',

        'current_queue' =>
            'বর্তমান সারি',

        'capacity_used' =>
            'ক্ষমতা',

        'remaining' =>
            'বাকি',

        'purchase_date' =>
            'ক্রয়ের তারিখ',

        'best_slot' =>
            'সেরা উপলব্ধ স্লট',

        'farmers' =>
            'কৃষক',

        'km_away' =>
            'কিমি দূরে',

        'open' =>
            'স্লট উপলব্ধ',

        'limited' =>
            'সীমিত প্রাপ্যতা',

        'no_slots_status' =>
            'স্লট উপলব্ধ নেই',

        'view_centre' =>
            'কেন্দ্র দেখুন ও বুক করুন',

        'prototype_notice' =>
            'প্রোটোটাইপ তথ্য',

        'prototype_text' =>
            'এই সুপারিশ ব্যবস্থা কৃষিসেতু প্রোটোটাইপের জন্য স্বচ্ছ নিয়ম-ভিত্তিক লজিক ব্যবহার করে। বাস্তব ব্যবহারে অনুমোদিত পশ্চিমবঙ্গ ই-প্যাডি ডেটা এবং রিয়েল-টাইম কেন্দ্রের তথ্য ব্যবহার করা যেতে পারে।',

        'no_centres' =>
            'বর্তমানে কোনও সক্রিয় ক্রয় কেন্দ্র উপলব্ধ নেই।',

        'back_dashboard' =>
            'ড্যাশবোর্ডে ফিরে যান',

        'location_unavailable' =>
            'অবস্থান পাওয়া যায়নি',

        'low_queue' =>
            'কম সারি',

        'moderate_queue' =>
            'মাঝারি সারি',

        'high_queue' =>
            'বেশি সারি'
    ]
];


$currentLanguage =
    $_SESSION['language'] ?? 'en';


if (
    !isset($pageText[$currentLanguage])
) {
    $currentLanguage = 'en';
}


function rt(string $key): string
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
   FARMER GPS
========================================================= */

$farmerLat =
    isset($_GET['lat'])
        ? (float)$_GET['lat']
        : null;

$farmerLng =
    isset($_GET['lng'])
        ? (float)$_GET['lng']
        : null;


/* =========================================================
   GET ACTIVE CENTRES
========================================================= */

$result = $conn->query("
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
");


$centres = [];


/* =========================================================
   CALCULATE RECOMMENDATION

   Visible score:
   Queue             = 35
   Slot availability = 35
   Capacity          = 20
   Purchase schedule = 10

   TOTAL             = 100

   Distance is NOT scored.
   It is only displayed.
========================================================= */

foreach ($result as $centre) {

    $queue =
        max(
            0,
            (int)$centre['current_queue']
        );


    $capacity =
        max(
            0,
            (int)$centre['total_capacity']
        );


    $bookings =
        max(
            0,
            (int)$centre['current_bookings']
        );


    /* -----------------------------------------------------
       1. QUEUE — 35 POINTS

       0 queue = 35
       35+ queue = 0
    ----------------------------------------------------- */

    $queueScore =
        max(
            0,
            35 - min($queue, 35)
        );


    /* -----------------------------------------------------
       2. SLOT AVAILABILITY — 35 POINTS

       This uses the centre's existing slot_available flag.
    ----------------------------------------------------- */

    $slotScore =
        ((int)$centre['slot_available'] === 1)
            ? 35
            : 0;


    /* -----------------------------------------------------
       3. REMAINING CAPACITY — 20 POINTS
    ----------------------------------------------------- */

    if ($capacity > 0) {

        $remainingCapacity =
            max(
                0,
                $capacity - $bookings
            );

        $remainingPercentage =
            ($remainingCapacity / $capacity) * 100;

        $capacityScore =
            ($remainingPercentage / 100) * 20;

    } else {

        $remainingCapacity = 0;

        $capacityScore = 0;
    }


    /* -----------------------------------------------------
       4. PURCHASE SCHEDULE — 10 POINTS
    ----------------------------------------------------- */

    $purchaseScore = 0;


    if (!empty($centre['purchase_date'])) {

        $purchaseTimestamp =
            strtotime(
                $centre['purchase_date']
            );

        $todayTimestamp =
            strtotime(
                date('Y-m-d')
            );


        $daysAway =
            floor(
                (
                    $purchaseTimestamp
                    -
                    $todayTimestamp
                )
                /
                86400
            );


        if ($daysAway <= 0) {

            $purchaseScore = 10;

        } elseif ($daysAway === 1) {

            $purchaseScore = 9;

        } elseif ($daysAway === 2) {

            $purchaseScore = 8;

        } elseif ($daysAway === 3) {

            $purchaseScore = 7;

        } elseif ($daysAway <= 7) {

            $purchaseScore = 6;

        } else {

            $purchaseScore = 4;
        }
    }


    /* -----------------------------------------------------
       FINAL VISIBLE SCORE
    ----------------------------------------------------- */

    $totalScore =
        $queueScore
        +
        $slotScore
        +
        $capacityScore
        +
        $purchaseScore;


    $centre['queue_score'] =
        round(
            $queueScore,
            1
        );


    $centre['slot_score'] =
        round(
            $slotScore,
            1
        );


    $centre['capacity_score'] =
        round(
            $capacityScore,
            1
        );


    $centre['purchase_score'] =
        round(
            $purchaseScore,
            1
        );


    $centre['total_score'] =
        round(
            $totalScore,
            1
        );


    $centre['remaining_capacity'] =
        $remainingCapacity;


    /* -----------------------------------------------------
       REAL DISTANCE — DISPLAY ONLY
    ----------------------------------------------------- */

    $centre['distance_km'] =
        calculateDistance(
            $farmerLat,
            $farmerLng,
            !empty($centre['latitude'])
                ? (float)$centre['latitude']
                : null,
            !empty($centre['longitude'])
                ? (float)$centre['longitude']
                : null
        );


    /* -----------------------------------------------------
       CAPACITY PERCENTAGE
    ----------------------------------------------------- */

    if ($capacity > 0) {

        $centre['occupancy'] =
            min(
                100,
                round(
                    (
                        $bookings
                        /
                        $capacity
                    )
                    *
                    100
                )
            );

    } else {

        $centre['occupancy'] = 0;
    }


    /* -----------------------------------------------------
       QUEUE LABEL
    ----------------------------------------------------- */

    if ($queue <= 10) {

        $centre['queue_label'] =
            rt('low_queue');

        $centre['queue_class'] =
            'good';

    } elseif ($queue <= 25) {

        $centre['queue_label'] =
            rt('moderate_queue');

        $centre['queue_class'] =
            'medium';

    } else {

        $centre['queue_label'] =
            rt('high_queue');

        $centre['queue_class'] =
            'high';
    }


    /* -----------------------------------------------------
       AVAILABLE SLOTS

       Use admin-managed slots as the actual source.
    ----------------------------------------------------- */

    $slotStmt =
        $conn->prepare("
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
                ) AS remaining_slot_capacity
            FROM slots
            WHERE centre_id = ?
              AND status = 'available'
              AND slot_date >= CURDATE()
              AND booked_count < capacity
        ");


    $centreId =
        (int)$centre['id'];


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


    $centre['available_slot_count'] =
        (int)(
            $slotData['slot_count']
            ??
            0
        );


    $centre['slot_remaining_capacity'] =
        (int)(
            $slotData['remaining_slot_capacity']
            ??
            0
        );


    $centres[] =
        $centre;
}


/* =========================================================
   SORT BY VISIBLE RECOMMENDATION SCORE
========================================================= */

usort(
    $centres,
    function ($a, $b) {

        if (
            $a['total_score']
            ==
            $b['total_score']
        ) {

            return
                $a['current_queue']
                <=>
                $b['current_queue'];
        }

        return
            $b['total_score']
            <=>
            $a['total_score'];
    }
);


$recommended =
    $centres[0] ?? null;


/* =========================================================
   GET BEST SLOT FOR EACH CENTRE
========================================================= */

foreach ($centres as &$centre) {

    $centreId =
        (int)$centre['id'];


    $slotStmt =
        $conn->prepare("
            SELECT
                id,
                slot_date,
                start_time,
                end_time,
                capacity,
                booked_count
            FROM slots
            WHERE centre_id = ?
              AND status = 'available'
              AND booked_count < capacity
              AND slot_date >= CURDATE()
            ORDER BY
                slot_date ASC,
                start_time ASC
            LIMIT 1
        ");


    $slotStmt->bind_param(
        "i",
        $centreId
    );


    $slotStmt->execute();


    $slotResult =
        $slotStmt->get_result();


    $centre['best_slot'] =
        $slotResult->fetch_assoc()
        ?: null;


    $slotStmt->close();
}


unset($centre);


/* =========================================================
   REFRESH RECOMMENDED CENTRE
   The best_slot value was added above, so we must
   refresh $recommended from the updated centres array.
========================================================= */

$recommended = $centres[0] ?? null;


/* =========================================================
   ESTIMATED WAIT
========================================================= */

if ($recommended) {
    $estimatedWaitMinutes =
        (int)$recommended['current_queue']
        *
        5;

} else {

    $estimatedWaitMinutes = 0;
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
    <?= e(rt('title')) ?> | KrishiSetu
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

.page-header {

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


.header-inner {

    max-width:
        1180px;

    margin:
        auto;

    padding:
        13px 20px;

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

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


.brand-icon {

    width:
        40px;

    height:
        40px;

    border-radius:
        11px;

    background:
        #e8f5ee;

    display:
        grid;

    place-items:
        center;

    font-size:
        21px;
}


.header-right {

    display:
        flex;

    align-items:
        center;

    gap:
        14px;
}


.portal {

    color:
        #61716a;

    font-size:
        14px;
}


.back-btn {

    color:
        #087443;

    border:
        1px solid #cfe0d6;

    padding:
        9px 14px;

    border-radius:
        9px;

    font-size:
        14px;

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
        auto;

    padding:
        34px 20px 60px;
}


.page-title {

    margin:
        0 0 7px;

    font-size:
        34px;

    line-height:
        1.2;
}


.page-subtitle {

    max-width:
        850px;

    margin:
        0;

    color:
        #61716a;

    line-height:
        1.6;

    font-size:
        15px;
}


/* =========================================================
   RECOMMENDED CARD
========================================================= */

.recommended-card {

    margin-top:
        26px;

    background:
        #ffffff;

    border:
        2px solid #087443;

    border-radius:
        18px;

    overflow:
        hidden;

    box-shadow:
        0 8px 25px
        rgba(8,116,67,.06);
}


.recommended-top {

    padding:
        24px;

    display:
        grid;

    grid-template-columns:
        minmax(0, 1fr) 125px;

    gap:
        25px;

    align-items:
        center;
}


.recommended-label {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        5px;

    background:
        #087443;

    color:
        #ffffff;

    padding:
        7px 11px;

    border-radius:
        8px;

    font-size:
        12px;

    font-weight:
        800;

    margin-bottom:
        13px;
}


.recommended-name {

    margin:
        0 0 5px;

    font-size:
        25px;

    line-height:
        1.3;
}


.recommended-code {

    color:
        #8a9892;

    font-size:
        13px;

    margin-bottom:
        15px;
}


.recommendation-reason {

    margin:
        0 0 13px;

    color:
        #61716a;

    line-height:
        1.55;

    font-size:
        14px;
}


.recommendation-reason strong {

    color:
        #17352a;
}


.recommended-slot {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        8px;

    background:
        #f1f8f4;

    border:
        1px solid #d7eadf;

    border-radius:
        9px;

    padding:
        9px 11px;

    color:
        #087443;

    font-size:
        13px;

    margin-bottom:
        9px;
}


.waiting-time {

    color:
        #61716a;

    font-size:
        13px;

    margin-bottom:
        15px;
}


.waiting-time strong {

    color:
        #17352a;
}


.recommendation-button {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #087443;

    color:
        #ffffff;

    padding:
        11px 17px;

    border-radius:
        9px;

    font-size:
        13px;

    font-weight:
        800;
}


.recommendation-button:hover {

    background:
        #055c35;
}


/* =========================================================
   SCORE CIRCLE
========================================================= */

.score-circle {

    width:
        112px;

    height:
        112px;

    border-radius:
        50%;

    border:
        7px solid #087443;

    background:
        #e8f5ee;

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        center;

    align-items:
        center;

    justify-self:
        end;
}


.score-number {

    color:
        #087443;

    font-size:
        28px;

    font-weight:
        900;

    line-height:
        1;
}


.score-small {

    margin-top:
        5px;

    color:
        #61716a;

    font-size:
        11px;
}


/* =========================================================
   SCORE BREAKDOWN
========================================================= */

.score-breakdown {

    border-top:
        1px solid #e3ece7;

    padding:
        17px 24px;

    display:
        grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap:
        12px;

    background:
        #fbfdfb;
}


.score-box {

    background:
        #ffffff;

    border:
        1px solid #e2eae5;

    border-radius:
        10px;

    padding:
        12px 13px;
}


.score-box-title {

    color:
        #61716a;

    font-size:
        11px;

    margin-bottom:
        5px;
}


.score-box-value {

    color:
        #087443;

    font-size:
        17px;

    font-weight:
        800;
}


.score-box-limit {

    color:
        #8a9892;

    font-size:
        11px;
}


/* =========================================================
   HOW DECIDES
========================================================= */

.section-title {

    margin:
        34px 0 7px;

    font-size:
        22px;
}


.section-description {

    margin:
        0 0 15px;

    color:
        #61716a;

    font-size:
        13px;

    line-height:
        1.5;
}


.score-info {

    background:
        #ffffff;

    border:
        1px solid #dfe7e2;

    border-radius:
        15px;

    padding:
        7px 20px;
}


.score-row {

    min-height:
        52px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        15px;

    border-bottom:
        1px solid #edf1ee;
}


.score-row:last-child {

    border-bottom:
        0;
}


.score-factor {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    font-size:
        14px;

    font-weight:
        700;
}


.factor-dot {

    width:
        8px;

    height:
        8px;

    border-radius:
        50%;

    background:
        #087443;
}


.score-weight {

    color:
        #087443;

    font-weight:
        800;
}


/* =========================================================
   OTHER OPTIONS HEADER
========================================================= */

.options-header {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        end;

    gap:
        15px;

    margin-top:
        34px;

    margin-bottom:
        15px;
}


.options-header .section-title {

    margin:
        0;
}


.options-count {

    color:
        #8a9892;

    font-size:
        12px;
}


/* =========================================================
   OTHER CENTRES
========================================================= */

.centre-list {

    display:
        grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap:
        17px;
}


.centre-card {

    background:
        #ffffff;

    border:
        1px solid #dfe7e2;

    border-radius:
        15px;

    padding:
        18px;

    transition:
        box-shadow .15s ease,
        border-color .15s ease;
}


.centre-card:hover {

    border-color:
        #bdd5c6;

    box-shadow:
        0 7px 20px
        rgba(20,70,45,.06);
}


.rank {

    display:
        inline-block;

    background:
        #fff1cd;

    color:
        #795600;

    padding:
        5px 8px;

    border-radius:
        6px;

    font-size:
        11px;

    font-weight:
        800;

    margin-bottom:
        10px;
}


.centre-card h3 {

    margin:
        0 0 4px;

    font-size:
        18px;

    line-height:
        1.3;
}


.centre-card-code {

    color:
        #8a9892;

    font-size:
        12px;

    margin-bottom:
        13px;
}


.mini-info {

    min-height:
        39px;

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        12px;

    border-bottom:
        1px solid #edf1ee;

    font-size:
        12px;
}


.mini-info:last-of-type {

    border-bottom:
        0;
}


.mini-label {

    color:
        #61716a;
}


.mini-value {

    color:
        #17352a;

    font-weight:
        800;

    text-align:
        right;
}


.distance-value {

    color:
        #087443;
}


.card-action {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        12px;

    margin-top:
        14px;
}


.status-pill {

    display:
        inline-block;

    padding:
        5px 8px;

    border-radius:
        6px;

    font-size:
        10px;

    font-weight:
        800;
}


.status-open {

    color:
        #087443;

    background:
        #e8f5ee;
}


.status-limited {

    color:
        #8b6200;

    background:
        #fff7e6;
}


.status-none {

    color:
        #a52d2d;

    background:
        #fdecec;
}


.small-book-btn {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        5px;

    background:
        #087443;

    color:
        #ffffff;

    padding:
        9px 12px;

    border-radius:
        8px;

    font-size:
        11px;

    font-weight:
        800;
}


.small-disabled {

    background:
        #f0f2f1;

    color:
        #7d8882;

    cursor:
        default;
}


/* =========================================================
   PROTOTYPE NOTICE
========================================================= */

.prototype-note {

    margin-top:
        28px;

    padding:
        14px 17px;

    background:
        #fff7e6;

    border:
        1px solid #efd89f;

    border-radius:
        11px;

    color:
        #74591f;

    font-size:
        12px;

    line-height:
        1.55;
}


.prototype-note strong {

    color:
        #624700;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-card {

    margin-top:
        25px;

    background:
        #ffffff;

    border:
        1px solid #dfe7e2;

    border-radius:
        16px;

    padding:
        45px 25px;

    text-align:
        center;
}


.empty-icon {

    font-size:
        32px;

    margin-bottom:
        10px;
}


.empty-card h2 {

    margin:
        0 0 7px;
}


.empty-card p {

    color:
        #61716a;

    margin:
        0 0 17px;
}


.empty-button {

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

    font-size:
        13px;

    font-weight:
        800;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 850px) {

    .recommended-top {

        grid-template-columns:
            1fr;
    }


    .score-circle {

        justify-self:
            start;
    }


    .score-breakdown {

        grid-template-columns:
            repeat(2, 1fr);
    }


    .centre-list {

        grid-template-columns:
            1fr;
    }
}


@media (max-width: 600px) {

    .header-inner {

        padding:
            11px 15px;
    }


    .portal {

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


    .recommended-top {

        padding:
            19px;
    }


    .score-breakdown {

        padding:
            15px;

        grid-template-columns:
            1fr 1fr;
    }


    .recommended-name {

        font-size:
            21px;
    }


    .options-header {

        align-items:
            flex-start;

        flex-direction:
            column;

        gap:
            4px;
    }
}


@media (max-width: 430px) {

    .score-breakdown {

        grid-template-columns:
            1fr;
    }


    .card-action {

        align-items:
            stretch;

        flex-direction:
            column;
    }


    .small-book-btn {

        justify-content:
            center;
    }
}

</style>

</head>


<body>


<!-- =====================================================
     HEADER
====================================================== -->

<header class="page-header">

    <div class="header-inner">


        <a
            href="dashboard.php"
            class="brand"
        >

            <span class="brand-icon">
                🌾
            </span>

            <span>
                KrishiSetu
            </span>

        </a>


        <div class="header-right">

            <span class="portal">
                <?= e(rt('portal')) ?>
            </span>


            <a
                href="dashboard.php"
                class="back-btn"
            >
                <?= e(rt('dashboard')) ?>
            </a>

        </div>

    </div>

</header>



<main class="page">


<?php if ($recommended): ?>


<!-- =====================================================
     PAGE INTRO
====================================================== -->

<h1 class="page-title">

    <?= e(rt('title')) ?>

</h1>


<p class="page-subtitle">

    <?= e(rt('subtitle')) ?>

</p>



<!-- =====================================================
     RECOMMENDED CENTRE
====================================================== -->

<section class="recommended-card">


    <div class="recommended-top">


        <div>


            <span class="recommended-label">

                <?= e(rt('recommended')) ?>

            </span>


            <h2 class="recommended-name">

                <?= e(
                    $recommended['centre_name']
                ) ?>

            </h2>


            <div class="recommended-code">

                <?= e(rt('centre_code')) ?>:

                <?= e(
                    $recommended['centre_code']
                ) ?>

            </div>


            <p class="recommendation-reason">

                <strong>
                    <?= (int)$recommended['current_queue'] ?>
                </strong>

                <?= e(rt('queue_reason')) ?>,

                <strong>

                    <?php if (
                        $recommended['available_slot_count'] > 0
                    ): ?>

                        <?= (int)$recommended[
                            'available_slot_count'
                        ] ?>

                        <?= e(
                            rt('slots_available')
                        ) ?>

                    <?php else: ?>

                        <?= e(
                            rt('no_slots')
                        ) ?>

                    <?php endif; ?>

                </strong>,

                <strong>

                    <?= (int)$recommended[
                        'remaining_capacity'
                    ] ?>

                </strong>

                <?= e(
                    rt('remaining_capacity')
                ) ?>.

            </p>


            <div class="recommended-slot">


                <span>
                    📅
                </span>


                <span>

                    <strong>
                        <?= e(
                            rt('recommended_slot')
                        ) ?>:
                    </strong>


                    <?php if (
                        $recommended['best_slot']
                    ): ?>

                        <?= e(
                            formatDateValue(
                                $recommended[
                                    'best_slot'
                                ]['slot_date']
                            )
                        ) ?>

                        ·

                        <?= e(
                            formatTimeValue(
                                $recommended[
                                    'best_slot'
                                ]['start_time']
                            )
                        ) ?>

                        -

                        <?= e(
                            formatTimeValue(
                                $recommended[
                                    'best_slot'
                                ]['end_time']
                            )
                        ) ?>

                    <?php else: ?>

                        <?= e(
                            rt('no_slots')
                        ) ?>

                    <?php endif; ?>

                </span>

            </div>


            <div class="waiting-time">

                🕐

                <?= e(
                    rt('estimated_wait')
                ) ?>:

                <strong>
                    ~<?= $estimatedWaitMinutes ?>
                    <?= e(rt('minutes')) ?>
                </strong>

            </div>


            <a
                href="
                    booking.php?centre_id=
                    <?= (int)$recommended['id'] ?>
                    <?=
                        $recommended['best_slot']
                        ? '&slot_id=' .
                          (int)$recommended[
                              'best_slot'
                          ]['id']
                        : ''
                    ?>
                "
                class="recommendation-button"
            >

                <?= e(rt('view_book')) ?>

                →

            </a>


        </div>



        <!-- SCORE -->

        <div class="score-circle">

            <span class="score-number">

                <?= e(
                    $recommended['total_score']
                ) ?>

            </span>


            <span class="score-small">

                <?= e(rt('out_of')) ?>

            </span>

        </div>


    </div>



    <!-- =================================================
         SCORE BREAKDOWN
    ================================================== -->

    <div class="score-breakdown">


        <div class="score-box">

            <div class="score-box-title">

                <?= e(
                    rt('queue')
                ) ?>

            </div>

            <div class="score-box-value">

                <?= e(
                    $recommended['queue_score']
                ) ?>

                <span class="score-box-limit">
                    / 35
                </span>

            </div>

        </div>



        <div class="score-box">

            <div class="score-box-title">

                <?= e(
                    rt('availability')
                ) ?>

            </div>

            <div class="score-box-value">

                <?= e(
                    $recommended['slot_score']
                ) ?>

                <span class="score-box-limit">
                    / 35
                </span>

            </div>

        </div>



        <div class="score-box">

            <div class="score-box-title">

                <?= e(
                    rt('capacity')
                ) ?>

            </div>

            <div class="score-box-value">

                <?= e(
                    $recommended['capacity_score']
                ) ?>

                <span class="score-box-limit">
                    / 20
                </span>

            </div>

        </div>



        <div class="score-box">

            <div class="score-box-title">

                <?= e(
                    rt('schedule')
                ) ?>

            </div>

            <div class="score-box-value">

                <?= e(
                    $recommended['purchase_score']
                ) ?>

                <span class="score-box-limit">
                    / 10
                </span>

            </div>

        </div>


    </div>


</section>



<!-- =====================================================
     HOW KRISHISETU DECIDES
====================================================== -->

<h2 class="section-title">

    <?= e(
        rt('how_decides')
    ) ?>

</h2>


<p class="section-description">

    <?= e(
        rt('how_decides_text')
    ) ?>

</p>


<section class="score-info">


    <div class="score-row">

        <span class="score-factor">

            <span class="factor-dot"></span>

            <?= e(
                rt('queue_condition')
            ) ?>

        </span>


        <span class="score-weight">
            35%
        </span>

    </div>



    <div class="score-row">

        <span class="score-factor">

            <span class="factor-dot"></span>

            <?= e(
                rt('availability')
            ) ?>

        </span>


        <span class="score-weight">
            35%
        </span>

    </div>



    <div class="score-row">

        <span class="score-factor">

            <span class="factor-dot"></span>

            <?= e(
                rt('capacity')
            ) ?>

        </span>


        <span class="score-weight">
            20%
        </span>

    </div>



    <div class="score-row">

        <span class="score-factor">

            <span class="factor-dot"></span>

            <?= e(
                rt('schedule')
            ) ?>

        </span>


        <span class="score-weight">
            10%
        </span>

    </div>


</section>



<!-- =====================================================
     OTHER OPTIONS
====================================================== -->

<div class="options-header">


    <div>

        <h2 class="section-title">

            <?= e(
                rt('other_options')
            ) ?>

        </h2>


        <p
            class="section-description"
            style="margin-bottom:0;"
        >

            <?= e(
                rt('other_options_text')
            ) ?>

        </p>

    </div>


    <span class="options-count">

        <?= max(
            0,
            count($centres) - 1
        ) ?>

        other centre(s)

    </span>


</div>



<div class="centre-list">


<?php

$rank = 1;

foreach ($centres as $centre):

    if (
        (int)$centre['id']
        ===
        (int)$recommended['id']
    ) {
        continue;
    }

    $rank++;


    if (
        $centre['available_slot_count'] > 0
    ) {

        $statusClass =
            $centre['slot_remaining_capacity'] < 10
                ? 'status-limited'
                : 'status-open';

        $statusText =
            $centre['slot_remaining_capacity'] < 10
                ? rt('limited')
                : rt('open');

    } else {

        $statusClass =
            'status-none';

        $statusText =
            rt('no_slots_status');
    }

?>


<article class="centre-card">


    <span class="rank">

        #<?= $rank ?>

        &nbsp;

        <?= e(
            rt('recommended')
        ) ?>

    </span>


    <h3>

        <?= e(
            $centre['centre_name']
        ) ?>

    </h3>


    <div class="centre-card-code">

        <?= e(
            $centre['centre_code']
        ) ?>

    </div>



    <!-- SCORE -->

    <div class="mini-info">

        <span class="mini-label">

            <?= e(
                rt('score')
            ) ?>

        </span>


        <span class="mini-value">

            <?= e(
                $centre['total_score']
            ) ?>/100

        </span>

    </div>



    <!-- DISTANCE -->

    <div class="mini-info">

        <span class="mini-label">

            <?= e(
                rt('distance')
            ) ?>

        </span>


        <span
            class="
                mini-value
                distance-value
            "
        >

            <?php if (
                $centre['distance_km'] !== null
            ): ?>

                <?= number_format(
                    $centre['distance_km'],
                    1
                ) ?>

                <?= e(
                    rt('km_away')
                ) ?>

            <?php else: ?>

                <?= e(
                    rt(
                        'location_unavailable'
                    )
                ) ?>

            <?php endif; ?>

        </span>

    </div>



    <!-- QUEUE -->

    <div class="mini-info">

        <span class="mini-label">

            <?= e(
                rt('current_queue')
            ) ?>

        </span>


        <span class="mini-value">

            <?= (int)$centre[
                'current_queue'
            ] ?>

            <?= e(
                rt('farmers')
            ) ?>

        </span>

    </div>



    <!-- WAIT -->

    <div class="mini-info">

        <span class="mini-label">

            <?= e(
                rt('estimated_wait')
            ) ?>

        </span>


        <span class="mini-value">

            ~<?= (int)$centre[
                'current_queue'
            ] * 5 ?>

            <?= e(
                rt('minutes')
            ) ?>

        </span>

    </div>



    <!-- CAPACITY -->

    <div class="mini-info">

        <span class="mini-label">

            <?= e(
                rt('capacity_used')
            ) ?>

        </span>


        <span class="mini-value">

            <?= (int)$centre[
                'current_bookings'
            ] ?>

            /

            <?= (int)$centre[
                'total_capacity'
            ] ?>

            ·

            <?= (int)$centre[
                'remaining_capacity'
            ] ?>

            <?= e(
                rt('remaining')
            ) ?>

        </span>

    </div>



    <!-- DISTANCE / PURCHASE -->

    <div class="mini-info">

        <span class="mini-label">

            <?= e(
                rt('purchase_date')
            ) ?>

        </span>


        <span class="mini-value">

            <?= e(
                formatDateValue(
                    $centre[
                        'purchase_date'
                    ]
                )
            ) ?>

        </span>

    </div>



    <!-- ACTION -->

    <div class="card-action">


        <span
            class="
                status-pill
                <?= e(
                    $statusClass
                ) ?>
            "
        >

            <?= e(
                $statusText
            ) ?>

        </span>


        <?php if (
            $centre['available_slot_count']
            >
            0
        ): ?>


            <a
                href="
                    booking.php?centre_id=
                    <?= (int)$centre['id'] ?>
                    <?=
                        $centre['best_slot']
                        ? '&slot_id=' .
                          (int)$centre[
                              'best_slot'
                          ]['id']
                        : ''
                    ?>
                "
                class="small-book-btn"
            >

                <?= e(
                    rt('view_centre')
                ) ?>

                →

            </a>


        <?php else: ?>


            <span
                class="
                    small-book-btn
                    small-disabled
                "
            >

                <?= e(
                    rt('no_slots_status')
                ) ?>

            </span>


        <?php endif; ?>


    </div>


</article>


<?php endforeach; ?>


</div>



<!-- =====================================================
     PROTOTYPE NOTICE
====================================================== -->

<div class="prototype-note">

    <strong>

        <?= e(
            rt('prototype_notice')
        ) ?>:

    </strong>

    <?= e(
        rt('prototype_text')
    ) ?>

</div>



<?php else: ?>


<!-- =====================================================
     NO CENTRES
====================================================== -->

<section class="empty-card">


    <div class="empty-icon">
        🌾
    </div>


    <h2>

        <?= e(
            rt('no_centres')
        ) ?>

    </h2>


    <p>

        <?= e(
            rt('no_centres')
        ) ?>

    </p>


    <a
        href="dashboard.php"
        class="empty-button"
    >

        <?= e(
            rt('back_dashboard')
        ) ?>

    </a>


</section>


<?php endif; ?>


</main>


<script>

/*
 * ========================================================
 * FARMER LOCATION
 *
 * If coordinates are not already present in the URL,
 * request browser location once.
 * ========================================================
 */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const params =
            new URLSearchParams(
                window.location.search
            );


        if (
            params.has("lat") &&
            params.has("lng")
        ) {
            return;
        }


        if (
            !navigator.geolocation
        ) {

            console.log(
                "Geolocation is not supported."
            );

            return;
        }


        navigator.geolocation.getCurrentPosition(

            function (position) {

                const lat =
                    position.coords.latitude;

                const lng =
                    position.coords.longitude;


                const newUrl =
                    window.location.pathname
                    +
                    "?lat="
                    +
                    encodeURIComponent(lat)
                    +
                    "&lng="
                    +
                    encodeURIComponent(lng);


                window.location.replace(
                    newUrl
                );
            },


            function (error) {

                console.log(
                    "Location permission not available:",
                    error.message
                );

                /*
                 * Do not block the page.
                 * The recommendation page still works
                 * without distance information.
                 */
            },


            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );

    }
);

</script>


</body>

</html>