<?php
session_start();
require_once "db.php";

// Ensure patient is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Get appointment ID from URL
if (!isset($_GET['id'])) {
    echo "<div style='text-align:center; padding:2rem;'>
            <h4>No appointment data found.</h4>
            <a href='patient_dashboard.php' class='btn btn-primary mt-3'>Back to Dashboard</a>
          </div>";
    exit();
}

$appointment_id = intval($_GET['id']);

// Fetch appointment details (⚡ fix: alias appointment_id)
$stmt = $conn->prepare("SELECT a.id AS appointment_id, a.*, u.first_name, u.last_name, d.specialization 
                        FROM appointments a
                        JOIN users u ON a.doctor_id = u.id
                        LEFT JOIN doctors d ON d.user_id = u.id
                        WHERE a.id = ? AND a.patient_id = ?");
$stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if (!$appointment) {
    echo "<div style='text-align:center; padding:2rem;'>
            <h4>Appointment not found.</h4>
            <a href='patient_dashboard.php' class='btn btn-primary mt-3'>Back to Dashboard</a>
          </div>";
    exit();
}

// Format date/time
$dateStr = date("l, F j, Y", strtotime($appointment['appointment_time']));
$timeStr = date("g:i A", strtotime($appointment['appointment_time']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Appointment Confirmation - CareLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
    // ✅ Auto-refresh status every 5 seconds
    setInterval(() => {
      $.get("check_status.php?id=<?= $appointment['appointment_id'] ?>", function(data) {
        $("#status-text").text(data);
        if (data.toLowerCase() === "paid") {
          $("#payment-btn").hide();
        }
      });
    }, 5000);
  </script>
</head>
<body style="background-color:#F0F3BD; min-height:100vh;">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg px-4" style="background-color: #028090;">
    <div class="container">
      <span class="navbar-brand fw-bold text-white">CareLink</span>
      <a class="btn btn-light btn-sm" href="patient-dashboard.php">← Back to Dashboard</a>
    </div>
  </nav>

  <!-- Confirmation Card -->
<div class="container py-5">
  <div class="card shadow border-0 p-4 mx-auto text-center" style="max-width:600px;">
    <h4 class="fw-bold mb-4" style="color:#05668D;">Appointment Confirmed</h4>

    <p class="fw-bold text-success" style="color:#02C39A;">
      Your appointment has been booked successfully!
    </p>

    <div class="text-start mt-4">
      <p><strong>Doctor:</strong> Dr. <?= htmlspecialchars($appointment['first_name'] . " " . $appointment['last_name']) ?> (<?= htmlspecialchars($appointment['specialization']) ?>)</p>
      <p><strong>Date:</strong> <?= $dateStr ?></p>
      <p><strong>Time:</strong> <?= $timeStr ?></p>
      <p><strong>Description:</strong> <?= !empty($appointment['service']) ? htmlspecialchars($appointment['service']) : "-" ?></p>
      <p><strong>Status:</strong> <span id="status-text"><?= ucfirst($appointment['status']) ?></span></p>
    </div>

    <!-- Payment Note -->
    <?php if ($appointment['status'] !== "Paid"): ?>
    <div class="alert alert-warning mt-4 text-start">
      <strong>Note:</strong> You have <b>30 minutes</b> to complete the payment after booking.  
      If payment is not completed within this time, the appointment will be <b>automatically cancelled</b>.
    </div>

    <!-- ✅ Fixed Payment Button -->
    <a id="payment-btn" href="payment.php?id=<?= $appointment['appointment_id'] ?>" 
       class="btn btn-success mt-3 w-100">
       Proceed to Payment
    </a>
    <?php endif; ?>

    <a href="patient-dashboard.php" class="btn mt-3 w-100 text-white" style="background-color:#00A896;">
      Back to Dashboard
    </a>
  </div>
</div>
</body>
</html>
