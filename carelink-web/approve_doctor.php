<?php
session_start();
include 'db.php';

// ✅ Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Check if doctor ID provided
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$doctorId = intval($_GET['id']);

// ✅ Approve doctor
$sql = "UPDATE doctors SET status='approved' WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctorId);
$stmt->execute();

// Redirect back with success message
$_SESSION['success'] = "Doctor approved successfully!";
header("Location: admin_dashboard.php");
exit();
?>
