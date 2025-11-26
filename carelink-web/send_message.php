<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$appointment_id = intval($_POST['appointment_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($appointment_id <= 0 || $message === '') {
  header("Location: chat.php?appointment_id=$appointment_id");
  exit;
}

// confirm user belongs to this appointment
$stmt = $conn->prepare("SELECT id FROM appointments WHERE id=? AND (patient_id=? OR doctor_id=?)");
$stmt->bind_param("iii", $appointment_id, $user_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) die("Unauthorized.");

$insert = $conn->prepare("INSERT INTO chat_messages (appointment_id, sender_id, message) VALUES (?, ?, ?)");
$insert->bind_param("iis", $appointment_id, $user_id, $message);
$insert->execute();

header("Location: chat.php?appointment_id=$appointment_id");
exit;
