<?php

session_start();

require_once "../config/database.php";
require_once "../config/language.php";

/* Farmer access only */
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "farmer") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/*
 * Notification-page specific translations.
 * The common farmer-panel text comes from config/language.php.
 */
$notificationPageText = [

    "en" => [
        "title" => "Notifications | KrishiSetu",
        "back_dashboard" => "← Back to Dashboard",
        "heading" => "Notifications",
        "subtitle" => "Stay updated about your procurement activities.",
        "no_notifications" => "No Notifications",
        "no_notifications_desc" => "You don't have any notifications yet.",
        "back_to_dashboard" => "Back to Dashboard",

        "booking_confirmed" => "Booking Confirmed",
        "arrival_recorded" => "Arrival Recorded",
        "paddy_weighed" => "Paddy Weighed",
        "procurement_accepted" => "Procurement Accepted",
        "payment_generated" => "Payment Generated",
        "payment_updated" => "Payment Updated",
        "queue_update" => "Queue Update",
        "schedule_update" => "Schedule Update"
    ],

    "hi" => [
        "title" => "सूचनाएँ | कृषिसेतु",
        "back_dashboard" => "← डैशबोर्ड पर वापस जाएँ",
        "heading" => "सूचनाएँ",
        "subtitle" => "अपनी खरीद गतिविधियों की जानकारी पाते रहें।",
        "no_notifications" => "कोई सूचना नहीं",
        "no_notifications_desc" => "अभी आपके लिए कोई सूचना नहीं है।",
        "back_to_dashboard" => "डैशबोर्ड पर वापस जाएँ",

        "booking_confirmed" => "बुकिंग की पुष्टि हो गई",
        "arrival_recorded" => "किसान के पहुँचने की पुष्टि",
        "paddy_weighed" => "धान का वजन दर्ज किया गया",
        "procurement_accepted" => "खरीद स्वीकार की गई",
        "payment_generated" => "भुगतान तैयार किया गया",
        "payment_updated" => "भुगतान अपडेट किया गया",
        "queue_update" => "कतार अपडेट",
        "schedule_update" => "समय-सारणी अपडेट"
    ],

    "bn" => [
        "title" => "বিজ্ঞপ্তি | কৃষিসেতু",
        "back_dashboard" => "← ড্যাশবোর্ডে ফিরে যান",
        "heading" => "বিজ্ঞপ্তি",
        "subtitle" => "আপনার ক্রয় সংক্রান্ত কার্যকলাপের আপডেট পান।",
        "no_notifications" => "কোনও বিজ্ঞপ্তি নেই",
        "no_notifications_desc" => "এখনও আপনার জন্য কোনও বিজ্ঞপ্তি নেই।",
        "back_to_dashboard" => "ড্যাশবোর্ডে ফিরে যান",

        "booking_confirmed" => "বুকিং নিশ্চিত হয়েছে",
        "arrival_recorded" => "কৃষকের পৌঁছানোর তথ্য রেকর্ড হয়েছে",
        "paddy_weighed" => "ধানের ওজন রেকর্ড হয়েছে",
        "procurement_accepted" => "ক্রয় গ্রহণ করা হয়েছে",
        "payment_generated" => "পেমেন্ট তৈরি হয়েছে",
        "payment_updated" => "পেমেন্ট আপডেট হয়েছে",
        "queue_update" => "সারি আপডেট",
        "schedule_update" => "সময়সূচি আপডেট"
    ]
];

$lang = $currentLanguage;

function notificationText(string $key): string
{
    global $notificationPageText, $lang;

    return $notificationPageText[$lang][$key]
        ?? $notificationPageText["en"][$key]
        ?? $key;
}

/*
 * Translate known notification titles.
 * Unknown titles are kept exactly as stored in the database.
 */
function translateNotificationTitle(string $title): string
{
    $title = trim($title);

    $map = [
        "Booking Confirmed" => "booking_confirmed",
        "Arrival Recorded" => "arrival_recorded",
        "Paddy Weighed" => "paddy_weighed",
        "Procurement Accepted" => "procurement_accepted",
        "Payment Generated" => "payment_generated",
        "Payment Updated" => "payment_updated",
        "Queue Update" => "queue_update",
        "Schedule Update" => "schedule_update"
    ];

    if (isset($map[$title])) {
        return notificationText($map[$title]);
    }

    return $title;
}


/*
 * Translate notification messages while preserving
 * dynamic information such as centre names and amounts.
 */
function translateNotificationMessage(
    string $title,
    string $message
): string {

    global $lang;

    $title = trim($title);
    $message = trim($message);

    /*
     * English
     * Keep the original database message unchanged.
     */
    if ($lang === "en") {
        return $message;
    }


    /*
     * ARRIVAL RECORDED
     */
    if ($title === "Arrival Recorded") {

        if (preg_match(
            '/^Your arrival at (.+?) has been recorded\.$/i',
            $message,
            $matches
        )) {

            $centre = $matches[1];

            if ($lang === "hi") {
                return "आपका " . $centre .
                       " पर पहुँचना दर्ज कर लिया गया है।";
            }

            if ($lang === "bn") {
                return $centre .
                       "-এ আপনার পৌঁছানোর তথ্য রেকর্ড করা হয়েছে।";
            }
        }

        if ($lang === "hi") {
            return "आपका केंद्र पर पहुँचना दर्ज कर लिया गया है।";
        }

        if ($lang === "bn") {
            return "আপনার কেন্দ্রে পৌঁছানোর তথ্য রেকর্ড করা হয়েছে।";
        }
    }


    /*
     * PADDY WEIGHED
     */
    if ($title === "Paddy Weighed") {

        if (preg_match(
            '/^Your paddy has been weighed at (.+?)\.$/i',
            $message,
            $matches
        )) {

            $centre = $matches[1];

            if ($lang === "hi") {
                return "आपके धान का वजन " . $centre .
                       " पर दर्ज कर लिया गया है।";
            }

            if ($lang === "bn") {
                return $centre .
                       "-এ আপনার ধানের ওজন রেকর্ড করা হয়েছে।";
            }
        }

        if ($lang === "hi") {
            return "आपके धान का वजन दर्ज कर लिया गया है।";
        }

        if ($lang === "bn") {
            return "আপনার ধানের ওজন রেকর্ড করা হয়েছে।";
        }
    }


    /*
     * PROCUREMENT ACCEPTED
     */
    if ($title === "Procurement Accepted") {

        if (preg_match(
            '/^Your paddy has been accepted for procurement at (.+?)\.$/i',
            $message,
            $matches
        )) {

            $centre = $matches[1];

            if ($lang === "hi") {
                return "आपका धान " . $centre .
                       " पर खरीद के लिए स्वीकार कर लिया गया है।";
            }

            if ($lang === "bn") {
                return $centre .
                       "-এ আপনার ধান ক্রয়ের জন্য গ্রহণ করা হয়েছে।";
            }
        }

        if ($lang === "hi") {
            return "आपका धान खरीद के लिए स्वीकार कर लिया गया है।";
        }

        if ($lang === "bn") {
            return "আপনার ধান ক্রয়ের জন্য গ্রহণ করা হয়েছে।";
        }
    }


    /*
     * BOOKING CONFIRMED
     */
    if ($title === "Booking Confirmed") {

        if ($lang === "hi") {
            return "आपकी खरीद बुकिंग की पुष्टि हो गई है।";
        }

        if ($lang === "bn") {
            return "আপনার ক্রয় বুকিং নিশ্চিত হয়েছে।";
        }
    }


    /*
     * PAYMENT GENERATED
     */
    if ($title === "Payment Generated") {

        if ($lang === "hi") {
            return "आपका भुगतान तैयार कर दिया गया है।";
        }

        if ($lang === "bn") {
            return "আপনার পেমেন্ট তৈরি করা হয়েছে।";
        }
    }


    /*
     * PAYMENT UPDATED
     */
    if ($title === "Payment Updated") {

        if ($lang === "hi") {
            return "आपके भुगतान की स्थिति अपडेट की गई है।";
        }

        if ($lang === "bn") {
            return "আপনার পেমেন্টের অবস্থা আপডেট করা হয়েছে।";
        }
    }


    /*
     * QUEUE UPDATE
     */
    if ($title === "Queue Update") {

        if ($lang === "hi") {
            return "आपकी कतार की स्थिति अपडेट की गई है।";
        }

        if ($lang === "bn") {
            return "আপনার সারির অবস্থার আপডেট করা হয়েছে।";
        }
    }


    /*
     * SCHEDULE UPDATE
     */
    if ($title === "Schedule Update") {

        if ($lang === "hi") {
            return "आपके स्लॉट की समय-सारणी अपडेट की गई है।";
        }

        if ($lang === "bn") {
            return "আপনার স্লটের সময়সূচি আপডেট করা হয়েছে।";
        }
    }


    /*
     * Unknown notification:
     * never destroy the original message.
     */
    return $message;
}

/* Get notifications */
$stmt = $conn->prepare(
    "SELECT id, title, message, type, is_read, created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

$stmt->close();

/* Mark all notifications as read */
$stmt = $conn->prepare(
    "UPDATE notifications
     SET is_read = 1
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLanguage) ?>">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars(notificationText("title")) ?></title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        body {
            margin: 0;
            background: #f7f9f7;
            color: #17352a;
            font-family: Arial, sans-serif;
        }

        .notification-page {
            max-width: 900px;
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

        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .notification {
            background: #ffffff;
            border: 1px solid #dfe7e2;
            border-radius: 14px;
            padding: 20px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .notification-icon {
            width: 45px;
            height: 45px;
            min-width: 45px;
            border-radius: 50%;
            background: #e8f5ee;
            color: #087443;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .notification-content {
            flex: 1;
        }

        .notification-content h3 {
            margin: 0 0 7px;
            color: #17352a;
            font-size: 17px;
        }

        .notification-content p {
            margin: 0 0 8px;
            color: #61716a;
            line-height: 1.5;
        }

        .notification-time {
            font-size: 13px;
            color: #8a9892;
        }

        .empty {
            background: #ffffff;
            border: 1px solid #dfe7e2;
            border-radius: 16px;
            padding: 50px 25px;
            text-align: center;
        }

        .empty-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .empty h2 {
            margin-bottom: 8px;
        }

        .empty p {
            color: #61716a;
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

        @media (max-width: 600px) {

            .notification-page {
                margin: 25px auto;
                padding: 15px;
            }

            .notification {
                padding: 15px;
            }

        }

    </style>

</head>

<body>

<div class="notification-page">

    <a href="dashboard.php" class="back-link">
        <?= htmlspecialchars(notificationText("back_dashboard")) ?>
    </a>

    <h1>
        <?= htmlspecialchars(notificationText("heading")) ?>
    </h1>

    <p class="subtitle">
        <?= htmlspecialchars(notificationText("subtitle")) ?>
    </p>

    <?php if (count($notifications) > 0): ?>

        <div class="notification-list">

            <?php foreach ($notifications as $notification): ?>

                <div class="notification">

                    <div class="notification-icon">

                        <?php

                        if ($notification["type"] === "success") {
                            echo "✓";
                        } elseif ($notification["type"] === "booking") {
                            echo "📅";
                        } else {
                            echo "🔔";
                        }

                        ?>

                    </div>

                    <div class="notification-content">

                        <h3>
                            <?= htmlspecialchars(
                                translateNotificationTitle($notification["title"])
                            ) ?>
                        </h3>

                       <p>
    <?= htmlspecialchars(
        translateNotificationMessage(
            $notification["title"],
            $notification["message"]
        )
    ) ?>
</p>

                        <div class="notification-time">

                            <?= date(
                                "d M Y, h:i A",
                                strtotime($notification["created_at"])
                            ) ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            <div class="empty-icon">
                🔔
            </div>

            <h2>
                <?= htmlspecialchars(notificationText("no_notifications")) ?>
            </h2>

            <p>
                <?= htmlspecialchars(notificationText("no_notifications_desc")) ?>
            </p>

            <a href="dashboard.php" class="btn">
                <?= htmlspecialchars(notificationText("back_to_dashboard")) ?>
            </a>

        </div>

    <?php endif; ?>

</div>

</body>

</html>
