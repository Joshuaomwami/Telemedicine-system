<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: login.php");
    exit();
}
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user_id"];
    $new_password = $_POST["new_password"];

    if (strlen($new_password) < 6) {
        $_SESSION["message"] = "❌ Password must be at least 6 characters.";
        header("Location: patient-dashboard.php");
        exit();
    }

    // Hash password before saving
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    $sql = "UPDATE users SET password=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashed, $user_id);

    if ($stmt->execute()) {
        $_SESSION["message"] = "✅ Password updated successfully!";
    } else {
        $_SESSION["message"] = "❌ Failed to update password.";
    }
    header("Location: patient-dashboard.php");
    exit();
}
?>
