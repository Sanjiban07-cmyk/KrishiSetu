<?php

// Database connection
require_once "../config/database.php";

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit;
}


// Get form data
$name = trim($_POST["name"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$village = trim($_POST["village"] ?? "");
$district = trim($_POST["district"] ?? "");
$state = trim($_POST["state"] ?? "");
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";
$language = $_POST["language"] ?? "en";


// Basic validation
if (
    $name === "" ||
    $mobile === "" ||
    $village === "" ||
    $district === "" ||
    $state === "" ||
    $password === ""
) {
    die("Please fill in all required fields.");
}


// Validate mobile number
if (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
    die("Please enter a valid 10-digit mobile number.");
}


// Validate password
if (strlen($password) < 6) {
    die("Password must contain at least 6 characters.");
}


// Confirm password
if ($password !== $confirm_password) {
    die("Passwords do not match.");
}


// Validate language
$allowed_languages = ["en", "bn", "hi"];

if (!in_array($language, $allowed_languages, true)) {
    $language = "en";
}


// Check whether mobile already exists
$check_stmt = $conn->prepare(
    "SELECT id FROM users WHERE mobile = ? LIMIT 1"
);

$check_stmt->bind_param("s", $mobile);
$check_stmt->execute();

$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    $check_stmt->close();

    die(
        "An account with this mobile number already exists. "
        . "Please use another number or login."
    );
}

$check_stmt->close();


// Securely hash password
$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


// Start database transaction
$conn->begin_transaction();

try {

    // Insert user
    $user_stmt = $conn->prepare(
        "INSERT INTO users
        (name, mobile, password, role, language)
        VALUES (?, ?, ?, 'farmer', ?)"
    );

    $user_stmt->bind_param(
        "ssss",
        $name,
        $mobile,
        $hashed_password,
        $language
    );

    $user_stmt->execute();

    // Get newly created user ID
    $user_id = $conn->insert_id;

    $user_stmt->close();


    // Insert farmer profile
    $farmer_stmt = $conn->prepare(
        "INSERT INTO farmers
        (user_id, village, district, state)
        VALUES (?, ?, ?, ?)"
    );

    $farmer_stmt->bind_param(
        "isss",
        $user_id,
        $village,
        $district,
        $state
    );

    $farmer_stmt->execute();

    $farmer_stmt->close();


    // Everything successful
    $conn->commit();

    // Redirect to login page
    header("Location: ../login.php?registered=1");
    exit;

} catch (Exception $e) {

    // Undo database changes if something fails
    $conn->rollback();

    die(
        "Registration failed. Please try again."
    );
}
?>