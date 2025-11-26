<?php
session_start();
include 'db.php';

// ✅ Only admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Fetch quick stats
$totalDoctors = $conn->query("SELECT COUNT(*) AS count FROM doctors")->fetch_assoc()['count'];
$pendingDoctors = $conn->query("SELECT COUNT(*) AS count FROM doctors WHERE status='pending'")->fetch_assoc()['count'];
$totalPatients = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role='patient'")->fetch_assoc()['count'];
$totalAppointments = $conn->query("SELECT COUNT(*) AS count FROM appointments")->fetch_assoc()['count'];

// ✅ Fetch pending doctors for review
$pendingList = $conn->query("SELECT * FROM doctors WHERE status='pending' ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - CareLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body style="background:#f4f7fa; min-height:100vh;">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand fw-bold" href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> CareLink Admin</a>
      <div class="ms-auto">
        <a href="login.php" class="btn btn-danger btn-sm">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container-fluid py-4">
    <div class="row g-4">

      <!-- Stats Cards -->
      <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center">
          <div class="card-body">
            <i class="bi bi-person-badge fs-2 text-primary"></i>
            <h5 class="fw-bold mt-2"><?= $totalDoctors ?></h5>
            <p class="text-muted">Total Doctors</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center">
          <div class="card-body">
            <i class="bi bi-clock-history fs-2 text-warning"></i>
            <h5 class="fw-bold mt-2"><?= $pendingDoctors ?></h5>
            <p class="text-muted">Pending Approvals</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center">
          <div class="card-body">
            <i class="bi bi-people fs-2 text-success"></i>
            <h5 class="fw-bold mt-2"><?= $totalPatients ?></h5>
            <p class="text-muted">Patients</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0 text-center">
          <div class="card-body">
            <i class="bi bi-calendar-check fs-2 text-danger"></i>
            <h5 class="fw-bold mt-2"><?= $totalAppointments ?></h5>
            <p class="text-muted">Appointments</p>
          </div>
        </div>
      </div>

    </div>

    <!-- Pending Doctors Section -->
    <div class="row mt-5">
      <div class="col-md-8">
        <div class="card shadow-lg border-0">
          <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-hourglass-split"></i> Pending Doctor Approvals</h5>
          </div>
          <div class="card-body">
            <?php if ($pendingList->num_rows > 0): ?>
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Name</th>
                    <th>Specialization</th>
                    <th>License</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($doc = $pendingList->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($doc['full_name'] ?? 'Unknown') ?></td>
                      <td><?= htmlspecialchars($doc['specialization'] ?? '-') ?></td>
                      <td>
                        <?php if (!empty($doc['license_file'])): ?>
                          <a href="<?= htmlspecialchars($doc['license_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-file-earmark-text"></i> View
                          </a>
                        <?php else: ?>
                          <span class="text-muted">No File</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <a href="approve_doctor.php?id=<?= $doc['id'] ?>" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i></a>
                        <a href="reject_doctor.php?id=<?= $doc['id'] ?>" class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i></a>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p class="text-muted mb-0">No pending approvals 🎉</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="bi bi-tools"></i> Quick Actions</h6>
          </div>
          <div class="list-group list-group-flush">
            <a href="manage_doctors.php" class="list-group-item list-group-item-action"><i class="bi bi-person-lines-fill"></i> Manage Doctors</a>
            <a href="manage_patients.php" class="list-group-item list-group-item-action"><i class="bi bi-people"></i> Manage Patients</a>
            <a href="manage_appointments.php" class="list-group-item list-group-item-action"><i class="bi bi-calendar-event"></i> Manage Appointments</a>
          </div>
        </div>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
