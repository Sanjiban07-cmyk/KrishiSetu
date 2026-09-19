<?php

session_start();

require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";

if ($mobile === "" || $password === "") {
    die("Please enter your mobile number and password.");
}

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

if (!password_verify($password, $user["password"])) {
    die("Invalid mobile number or password.");
}

session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["name"] = $user["name"];
$_SESSION["mobile"] = $user["mobile"];
$_SESSION["role"] = $user["role"];
$_SESSION["language"] = $user["language"];

/* Redirect according to role */

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