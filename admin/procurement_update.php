<?php

session_start();

require_once "../config/database.php";

/* Admin access only */
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

/* Only POST requests */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: procurement.php");
    exit;
}


/* -------------------------------------------------
   GET FORM DATA
------------------------------------------------- */

$booking_id = (int)($_POST["booking_id"] ?? 0);

$status = $_POST["status"] ?? "";

$crop_name = trim($_POST["crop_name"] ?? "");

$quantity = (float)($_POST["quantity"] ?? 0);


/* -------------------------------------------------
   ALLOWED STATUSES
------------------------------------------------- */

$allowed_statuses = [
    "pending",
    "arrived",
    "weighed",
    "accepted"
];


/* -------------------------------------------------
   CROP RATES
   Demo rates for prototype
------------------------------------------------- */

$crop_rates = [

    "Paddy" => 45.00,

    "Wheat" => 30.00,

    "Maize" => 22.00,

    "Mustard" => 55.00

];


/* -------------------------------------------------
   VALIDATION
------------------------------------------------- */

if (
    $booking_id <= 0 ||
    !in_array($status, $allowed_statuses, true)
) {
    die("Invalid procurement update.");
}


if (!isset($crop_rates[$crop_name])) {
    die("Invalid crop type.");
}


/*
   Quantity is required when weighing or accepting.
   It can remain empty before the weighing stage.
*/

if (
    ($status === "weighed" || $status === "accepted")
    && $quantity <= 0
) {
    die("Quantity must be greater than zero.");
}


/* -------------------------------------------------
   AUTO CALCULATE PAYMENT
------------------------------------------------- */

$rate = $crop_rates[$crop_name];

$payment_amount = $quantity * $rate;


/* -------------------------------------------------
   CHECK PROCUREMENT RECORD
------------------------------------------------- */

$stmt = $conn->prepare(
    "SELECT id, status
     FROM procurement
     WHERE booking_id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $booking_id);

$stmt->execute();

$result = $stmt->get_result();

$procurement = $result->fetch_assoc();

$stmt->close();


/* Store previous status */

$old_status = $procurement["status"] ?? null;


/* -------------------------------------------------
   UPDATE PROCUREMENT
------------------------------------------------- */

if ($procurement) {

    $procurement_id = (int)$procurement["id"];

    $stmt = $conn->prepare(
        "UPDATE procurement
         SET crop_name = ?,
             quantity = ?,
             unit = 'kg',
             status = ?
         WHERE booking_id = ?"
    );

    $stmt->bind_param(
        "sdsi",
        $crop_name,
        $quantity,
        $status,
        $booking_id
    );

    $stmt->execute();

    $stmt->close();

}


/* -------------------------------------------------
   CREATE PROCUREMENT RECORD
------------------------------------------------- */

else {

    $stmt = $conn->prepare(
        "INSERT INTO procurement
         (booking_id, crop_name, quantity, unit, status)
         VALUES (?, ?, ?, 'kg', ?)"
    );

    $stmt->bind_param(
        "isds",
        $booking_id,
        $crop_name,
        $quantity,
        $status
    );

    $stmt->execute();

    $procurement_id = $conn->insert_id;

    $stmt->close();

}


/* -------------------------------------------------
   CREATE / UPDATE PAYMENT
------------------------------------------------- */

/*
   One payment record is maintained for each
   procurement record.
*/

$stmt = $conn->prepare(
    "SELECT id
     FROM payments
     WHERE procurement_id = ?
     LIMIT 1"
);

$stmt->bind_param(
    "i",
    $procurement_id
);

$stmt->execute();

$paymentResult = $stmt->get_result();

$payment = $paymentResult->fetch_assoc();

$stmt->close();


if ($payment) {

    /* Update existing payment */

    $payment_id = (int)$payment["id"];

    $stmt = $conn->prepare(
        "UPDATE payments
         SET amount = ?
         WHERE id = ?"
    );

    $stmt->bind_param(
        "di",
        $payment_amount,
        $payment_id
    );

    $stmt->execute();

    $stmt->close();

} else {

    /* Create new payment */

    $stmt = $conn->prepare(
        "INSERT INTO payments
         (procurement_id, amount, status)
         VALUES (?, ?, 'pending')"
    );

    $stmt->bind_param(
        "id",
        $procurement_id,
        $payment_amount
    );

    $stmt->execute();

    $stmt->close();

}


/* -------------------------------------------------
   CREATE FARMER NOTIFICATION
------------------------------------------------- */

/* Only notify when status actually changes */

if ($old_status !== $status) {

    /* Get farmer information */

    $stmt = $conn->prepare(
        "SELECT
            f.user_id,
            pc.centre_name
         FROM bookings b

         JOIN farmers f
            ON b.farmer_id = f.id

         JOIN procurement_centres pc
            ON b.centre_id = pc.id

         WHERE b.id = ?

         LIMIT 1"
    );

    $stmt->bind_param(
        "i",
        $booking_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $booking = $result->fetch_assoc();

    $stmt->close();


    if ($booking) {

        $user_id = (int)$booking["user_id"];

        $centre_name = $booking["centre_name"];


        /* Notification content */

        $notifications = [

            "pending" => [
                "title" => "Booking Confirmed",
                "message" => "Your procurement booking has been confirmed at " . $centre_name . ".",
                "type" => "booking"
            ],

            "arrived" => [
                "title" => "Arrival Recorded",
                "message" => "Your arrival at " . $centre_name . " has been recorded.",
                "type" => "procurement"
            ],

            "weighed" => [
                "title" => "Paddy Weighed",
                "message" => "Your paddy has been weighed at " . $centre_name . ".",
                "type" => "procurement"
            ],

            "accepted" => [
                "title" => "Procurement Accepted",
                "message" => "Your paddy has been accepted for procurement at " . $centre_name . ".",
                "type" => "success"
            ]

        ];


        if (isset($notifications[$status])) {

            $notification = $notifications[$status];


            $stmt = $conn->prepare(
                "INSERT INTO notifications
                (user_id, title, message, type)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "isss",
                $user_id,
                $notification["title"],
                $notification["message"],
                $notification["type"]
            );

            $stmt->execute();

            $stmt->close();

        }

    }

}


/* -------------------------------------------------
   RETURN TO PROCUREMENT MANAGEMENT
------------------------------------------------- */

header("Location: procurement.php");

exit;

?>