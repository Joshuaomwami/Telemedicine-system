<?php
session_start();
require_once "db.php"; // ✅ database connection

// Ensure patient is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// Fetch approved doctors
$doctors = [];
$sql = "SELECT u.id, u.first_name, u.last_name, d.specialization, d.fee_per_hour 
        FROM users u 
        JOIN doctors d ON u.id = d.user_id 
        WHERE u.role = 'doctor' AND d.status = 'approved'";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $description = $_POST['description'];
    $patient_id = $_SESSION['user_id'];

    if (empty($doctor_id) || empty($date) || empty($time)) {
        $error = "❌ Please fill all required fields.";
    } else {
        $appointment_time = $date . " " . $time;

        $stmt = $conn->prepare("INSERT INTO appointments 
            (doctor_id, patient_id, service, appointment_time, status) 
            VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("iiss", $doctor_id, $patient_id, $description, $appointment_time);

        if ($stmt->execute()) {
            header("Location: appointment_confirmation.php?id=" . $stmt->insert_id);
            exit();
        } else {
            $error = "❌ Failed to book appointment. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Appointment - CareLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #F0F3BD; min-height: 100vh;">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg px-4" style="background-color: #028090;">
    <div class="container">
      <span class="navbar-brand fw-bold text-white">CareLink</span>
      <a class="btn btn-light btn-sm" href="patient-dashboard.php">Dashboard</a>
    </div>
  </nav>

  <div class="container py-5">
    <div class="card shadow border-0 p-4 mx-auto" style="max-width: 600px;">
      <h4 class="fw-bold mb-4 text-center" style="color: #05668D;">Book New Appointment</h4>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <!-- Doctor Select -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Select Doctor</label>
          <select class="form-select" name="doctor_id" required>
            <option value="">-- Choose Doctor --</option>
            <?php foreach ($doctors as $doc): ?>
              <option value="<?= $doc['id'] ?>">
                 Dr. <?= htmlspecialchars($doc['first_name'] . " " . $doc['last_name']) ?> – 
                <?= htmlspecialchars($doc['specialization']) ?> 
                (KSH <?= number_format($doc['fee_per_hour'], 0) ?>/hr)
              </option>
            <?php endforeach; ?>

          </select>
        </div>

        <!-- Date -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Date</label>
          <input type="date" class="form-control" name="date" required>
        </div>

        <!-- Time -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Time</label>
          <input type="time" class="form-control" name="time" required>
        </div>

        <!-- Description -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Describe Your Condition</label>
          <textarea class="form-control" name="description" placeholder="E.g. headache, flu symptoms..."></textarea>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn w-100 text-white" style="background-color: #02C39A;">
          Confirm Appointment
        </button>
      </form>
    </div>
  </div>
</body>
</html>
