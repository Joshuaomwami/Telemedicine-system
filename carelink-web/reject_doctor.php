<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$doctorId = intval($_GET['id']);

$sql = "UPDATE doctors SET status='rejected' WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctorId);
$stmt->execute();

$_SESSION['success'] = "Doctor rejected successfully!";
header("Location: admin_dashboard.php");
exit();
?>
