<?php

session_start();

require_once "config/database.php";

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}


// Get login details
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";


// Basic validation
if ($mobile === "" || $password === "") {
    die("Please enter your mobile number and password.");
}


// Find user by mobile number
$stmt = $conn->prepare(
    "SELECT id, name, mobile, password, role, language
     FROM users
     WHERE mobile = ?
     LIMIT 1"
);

$stmt->bind_param("s", $mobile);
$stmt->execute();

$result = $stmt->get_result();


// Check user exists
if ($result->num_rows !== 1) {
    $stmt->close();
    die("Invalid mobile number or password.");
}


$user = $result->fetch_assoc();

$stmt->close();


// Verify password
if (!password_verify($password, $user["password"])) {
    die("Invalid mobile number or password.");
}


// Only farmers use the farmer dashboard for now
if ($user["role"] !== "farmer") {
    die("This account does not have farmer access.");
}


// Create session
session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["name"] = $user["name"];
$_SESSION["mobile"] = $user["mobile"];
$_SESSION["role"] = $user["role"];
$_SESSION["language"] = $user["language"];


// Redirect to farmer dashboard
header("Location: farmer/dashboard.php");
exit;

?>