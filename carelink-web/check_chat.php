<?php
session_start();
require_once "db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['error' => 'not_logged_in']);
  exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'patient') {
  $stmt = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ? AND chat_active = 1");
  $stmt->bind_param("i", $user_id);
} elseif ($role === 'doctor') {
  $stmt = $conn->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND chat_active = 1");
  $stmt->bind_param("i", $user_id);
} else {
  echo json_encode(['active' => []]);
  exit;
}

$stmt->execute();
$res = $stmt->get_result();
$active = [];
while ($r = $res->fetch_assoc()) $active[] = (int)$r['id'];

echo json_encode(['active' => $active]);
