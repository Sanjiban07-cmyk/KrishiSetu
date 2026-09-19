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


$booking_id = (int)($_POST["booking_id"] ?? 0);
$status = $_POST["status"] ?? "";


/* Allowed statuses */
$allowed_statuses = [
    "pending",
    "arrived",
    "weighed",
    "accepted"
];


if ($booking_id <= 0 || !in_array($status, $allowed_statuses, true)) {
    die("Invalid procurement update.");
}


/* Check whether procurement record already exists */

$stmt = $conn->prepare(
    "SELECT id
     FROM procurement
     WHERE booking_id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $booking_id);
$stmt->execute();

$result = $stmt->get_result();

$procurement = $result->fetch_assoc();

$stmt->close();


/* Update existing record */

if ($procurement) {

    $stmt = $conn->prepare(
        "UPDATE procurement
         SET status = ?
         WHERE booking_id = ?"
    );

    $stmt->bind_param(
        "si",
        $status,
        $booking_id
    );

    $stmt->execute();

    $stmt->close();

}


/* Create record if it does not exist */

else {

    $stmt = $conn->prepare(
        "INSERT INTO procurement
         (booking_id, status)
         VALUES (?, ?)"
    );

    $stmt->bind_param(
        "is",
        $booking_id,
        $status
    );

    $stmt->execute();

    $stmt->close();

}


/* Return to management page */

header("Location: procurement.php");
exit;

?>