<?php
session_start();
include 'db.php';

// ✅ Only doctors allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctorId = $_SESSION['user_id'];

// ✅ Fetch all appointments
$sql = "SELECT a.id, a.appointment_time, a.service, a.status, p.first_name, p.last_name
        FROM appointments a
        JOIN users p ON a.patient_id = p.id
        WHERE a.doctor_id = ?
        ORDER BY a.appointment_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctorId);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Appointments - CareLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f9f9f9; min-height:100vh;">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg" style="background-color:#028090;">
    <div class="container">
      <a class="navbar-brand fw-bold text-white" href="doctor_dashboard.php">CareLink Doctor</a>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link text-white" href="doctor_dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-white fw-bold" href="doctor_appointments.php">Appointments</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="doctor_profile.php">Profile</a></li>
        <li class="nav-item"><a href="login.php" class="btn btn-light btn-sm ms-2">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container py-4">
    <h2 class="fw-bold mb-4" style="color:#05668D;">My Appointments</h2>

    <table class="table table-striped table-hover">
      <thead class="table-dark">
        <tr>
          <th>Date</th>
          <th>Patient</th>
          <th>Phone</th>
          <th>Service</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($appointments)): ?>
          <tr><td colspan="5" class="text-center text-muted">No appointments found</td></tr>
        <?php else: ?>
          <?php foreach ($appointments as $appt): ?>
            <tr>
              <td><?= htmlspecialchars($appt['appointment_time']) ?></td>
              <td><?= htmlspecialchars($appt['first_name'] . " " . $appt['last_name']) ?></td>
              <td><?= htmlspecialchars($appt['phone']) ?></td>
              <td><?= htmlspecialchars($appt['service']) ?></td>
              <td>
                <?php if ($appt['status'] === 'Paid'): ?>
                  <span class="badge bg-success">Paid</span>
                <?php else: ?>
                  <span class="badge bg-secondary"><?= htmlspecialchars($appt['status']) ?></span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
