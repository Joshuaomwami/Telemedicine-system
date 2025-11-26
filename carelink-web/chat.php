<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$appointment_id = intval($_GET['appointment_id'] ?? 0);
if ($appointment_id <= 0) die("Invalid appointment id.");

$stmt = $conn->prepare("SELECT id FROM appointments WHERE id=? AND (patient_id=? OR doctor_id=?)");
$stmt->bind_param("iii", $appointment_id, $user_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) die("Not authorized.");

$msgs = $conn->prepare("SELECT m.*, u.first_name, u.role FROM chat_messages m JOIN users u ON m.sender_id=u.id WHERE m.appointment_id=? ORDER BY m.sent_at ASC");
$msgs->bind_param("i", $appointment_id);
$msgs->execute();
$messages = $msgs->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Chat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .chat-window {height:400px;overflow-y:auto;background:#fff;padding:10px;border-radius:10px;}
  </style>
</head>
<body style="background-color:#F0F3BD;">
<div class="container py-4">
  <h4 class="mb-3">Chat - Appointment #<?= $appointment_id ?></h4>
  <div class="chat-window mb-3" id="chatWindow">
    <?php while ($m = $messages->fetch_assoc()): ?>
      <div class="mb-2">
        <strong style="color:<?= $m['role']==='doctor' ? '#028090' : '#02C39A' ?>">
          <?= htmlspecialchars($m['first_name']) ?>:
        </strong>
        <?= nl2br(htmlspecialchars($m['message'])) ?>
        <div class="text-muted small"><?= date("H:i", strtotime($m['sent_at'])) ?></div>
      </div>
    <?php endwhile; ?>
  </div>
  <form method="POST" action="send_message.php" class="d-flex">
    <input type="hidden" name="appointment_id" value="<?= $appointment_id ?>">
    <input type="text" name="message" class="form-control me-2" placeholder="Type your message..." required>
    <button class="btn btn-success">Send</button>
  </form>

  <?php if ($_SESSION['role'] === 'doctor'): ?>
    <a href="doctor_end_call.php?id=<?= $appointment_id ?>" class="btn btn-outline-danger mt-3">End Call</a>
  <?php endif; ?>
</div>

<script>
const cw = document.getElementById('chatWindow');
cw.scrollTop = cw.scrollHeight;
</script>
</body>
</html>
