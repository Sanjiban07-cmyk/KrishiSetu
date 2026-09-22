<?php

session_start();

require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";

/*
 * Language selected on login page
 */
$selectedLanguage = $_POST["language"] ?? "";

$allowedLanguages = ["en", "hi", "bn"];

if (!in_array($selectedLanguage, $allowedLanguages, true)) {
    $selectedLanguage = "en";
}

if ($mobile === "" || $password === "") {
    die("Please enter your mobile number and password.");
}

/*
 * Get user
 */
$stmt = $conn->prepare(
    "SELECT id, name, mobile, password, role, language
     FROM users
     WHERE mobile = ?
     LIMIT 1"
);

$stmt->bind_param("s", $mobile);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    die("Invalid mobile number or password.");
}

$user = $result->fetch_assoc();

$stmt->close();

/*
 * Verify password
 */
if (!password_verify($password, $user["password"])) {
    die("Invalid mobile number or password.");
}

/*
 * Regenerate session after successful login
 */
session_regenerate_id(true);

/*
 * Save user information
 */
$_SESSION["user_id"] = $user["id"];
$_SESSION["name"] = $user["name"];
$_SESSION["mobile"] = $user["mobile"];
$_SESSION["role"] = $user["role"];

/*
 * Save selected language globally in session
 */
$_SESSION["language"] = $selectedLanguage;

/*
 * Save selected language in database
 * so it remains the user's language preference
 * for future logins as well.
 */
$languageStmt = $conn->prepare(
    "UPDATE users
     SET language = ?
     WHERE id = ?"
);

$languageStmt->bind_param(
    "si",
    $selectedLanguage,
    $user["id"]
);

$languageStmt->execute();
$languageStmt->close();

/*
 * Redirect according to role
 */

if ($user["role"] === "admin") {
    header("Location: admin/dashboard.php");
    exit;
}

if ($user["role"] === "farmer") {
    header("Location: farmer/dashboard.php");
    exit;
}

die("Invalid account role.");

?>