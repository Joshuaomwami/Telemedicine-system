<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
  header("Location: login.php");
  exit();
}

$appt_id = intval($_GET['id'] ?? 0);
if ($appt_id <= 0) die("Invalid appointment ID.");

$doctor_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ?");
$stmt->bind_param("ii", $appt_id, $doctor_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) die("Unauthorized.");
$stmt->close();

$u = $conn->prepare("UPDATE appointments SET chat_active = 0 WHERE id = ?");
$u->bind_param("i", $appt_id);
$u->execute();
$u->close();

header("Location: doctor_dashboard.php");
exit;
