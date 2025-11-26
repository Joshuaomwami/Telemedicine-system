<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "patient") {
    header("Location: login.php");
    exit();
}
include "db.php";

$user_id = $_SESSION["user_id"];

// Fetch patient profile
$sql = "SELECT * FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Fetch appointments (including status for 'in_call')
$sql = "SELECT a.*, 
               u.first_name AS doctor_first, 
               u.last_name AS doctor_last, 
               d.specialization
        FROM appointments a
        LEFT JOIN users u ON a.doctor_id = u.id
        LEFT JOIN doctors d ON a.doctor_id = d.user_id
        WHERE a.patient_id=?
        ORDER BY a.appointment_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>CareLink - Patient Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body style="background-color:#F0F3BD;">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg px-4" style="background-color:#028090;">
  <div class="container">
    <span class="navbar-brand fw-bold text-white">CareLink</span>
    <div class="ms-auto d-flex align-items-center">
      <span class="me-3 text-white">Welcome, <?= htmlspecialchars($profile["first_name"]) ?></span>
      <a href="landing.php" class="btn btn-light btn-sm me-2">Home</a>
      <a href="login.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>

<!-- Main Content -->
<div class="container py-4">
  <!-- Book Appointment -->
  <div class="mb-4">
    <a href="book_appointment.php" class="btn text-white" style="background-color:#02C39A;">+ Book New Appointment</a>
  </div>

  <!-- Tabs -->
  <ul class="nav nav-tabs mb-4">
    <li class="nav-item">
      <button class="nav-link active" id="appointments-tab" onclick="showTab('appointments')">Appointments</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="profile-tab" onclick="showTab('profile')">Profile</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="settings-tab" onclick="showTab('settings')">Settings</button>
    </li>
  </ul>

  <!-- Appointments -->
  <div id="appointments" class="tab-content">
    <div class="row g-4">
      <?php if ($appointments->num_rows == 0): ?>
        <p class="text-muted">📅 You have no appointments yet. Book one to get started.</p>
      <?php else: ?>
        <?php while ($appt = $appointments->fetch_assoc()): ?>
          <div class="col-md-6">
            <div class="card shadow-sm border-0 p-3 h-100">
              <h6 class="fw-bold mb-1">
                Dr. <?= htmlspecialchars($appt["doctor_first"] . " " . $appt["doctor_last"]) ?>
              </h6>
              <p class="mb-1 text-muted"><?= $appt["specialization"] ?: "General Practitioner" ?></p>
              <p class="mb-1"><strong>When:</strong> <?= date("M d, Y h:i A", strtotime($appt["appointment_time"])) ?></p>
              <p class="mb-2">
                <strong>Status:</strong>
                <?php
                  $badge = "secondary";
                  if ($appt["status"] == "pending") $badge = "warning text-dark";
                  if ($appt["status"] == "confirmed") $badge = "success";
                  if ($appt["status"] == "in_call") $badge = "info";
                  if ($appt["status"] == "cancelled") $badge = "danger";
                  if ($appt["status"] == "paid") $badge = "primary";
                ?>
                <span class="badge bg-<?= $badge ?>"><?= ucfirst($appt["status"]) ?></span>
              </p>

              <!-- Action Buttons -->
              <div>
                <?php if ($appt["status"] == "pending"): ?>
                  <button class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#rescheduleModal" onclick="setReschedule(<?= $appt['id'] ?>)">Reschedule</button>
                  <a href="cancel_appointment.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-outline-danger me-2">Cancel</a>
                  <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#payModal" onclick="setPayment(<?= $appt['id'] ?>)">Pay</button>

                <?php elseif ($appt["status"] == "in_call"): ?>
                  <!-- ✅ When doctor starts call -->
                  <a href="chat.php?appointment_id=<?= $appt['id'] ?>" class="btn btn-sm btn-primary">Join Chat</a>

                <?php elseif ($appt["status"] == "confirmed"): ?>
                  <a href="cancel_appointment.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-outline-danger">Cancel</a>

                <?php else: ?>
                  <span class="text-muted small">No actions available</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Profile -->
  <div id="profile" class="tab-content d-none">
    <div class="card shadow-sm border-0 p-3">
      <h5 class="fw-bold mb-3" style="color:#05668D;">My Profile</h5>
      <p><strong>First Name:</strong> <?= htmlspecialchars($profile["first_name"]) ?></p>
      <p><strong>Last Name:</strong> <?= htmlspecialchars($profile["last_name"]) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($profile["email"]) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($profile["phone"] ?: "Not provided") ?></p>
    </div>
  </div>

  <!-- Settings -->
  <div id="settings" class="tab-content d-none">
    <div class="card shadow-sm border-0 p-3">
      <h5 class="fw-bold mb-3" style="color:#05668D;">Settings</h5>
      <button class="btn me-2 text-white" style="background-color:#00A896;" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
      <button class="btn text-white" style="background-color:#05668D;" data-bs-toggle="modal" data-bs-target="#passwordModal">Change Password</button>
    </div>
  </div>
</div>

<!-- Modals (same as before) -->
<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="reschedule_appointment.php" class="modal-content">
      <div class="modal-header" style="background-color:#028090; color:white;">
        <h5 class="modal-title">Reschedule Appointment</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="appointment_id" id="reschedule_id">
        <label for="new_time" class="form-label">New Date & Time</label>
        <input type="datetime-local" name="new_time" id="new_time" class="form-control" required>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="payModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="payment.php" class="modal-content">
      <div class="modal-header" style="background-color:#02C39A; color:white;">
        <h5 class="modal-title">Pay for Appointment</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="appointment_id" id="payment_id">
        <p>Enter your test M-Pesa number (e.g. 2547XXXXXXXX):</p>
        <input type="text" name="phone" class="form-control mb-2" required placeholder="2547XXXXXXXX">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-success">Confirm Payment</button>
      </div>
    </form>
  </div>
</div>

<script>
function showTab(tab) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.add('d-none'));
  document.getElementById(tab).classList.remove('d-none');
  document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
  document.getElementById(tab+'-tab').classList.add('active');
}

function setReschedule(id) {
  document.getElementById("reschedule_id").value = id;
}

function setPayment(id) {
  document.getElementById("payment_id").value = id;
}
</script>

</body>
</html>
