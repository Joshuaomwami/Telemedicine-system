<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: login.php");
    exit();
}
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $phone = trim($_POST["phone"]);

    $sql = "UPDATE users SET first_name=?, last_name=?, phone=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $first_name, $last_name, $phone, $user_id);

    if ($stmt->execute()) {
        $_SESSION["message"] = "✅ Profile updated successfully!";
    } else {
        $_SESSION["message"] = "❌ Failed to update profile.";
    }
    header("Location: patient-dashboard.php");
    exit();
}
?>
