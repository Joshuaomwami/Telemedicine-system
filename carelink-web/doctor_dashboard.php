<?php
session_start();
include 'db.php';

// ✅ Only allow logged-in doctors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctorId = $_SESSION['user_id'];

// ✅ Fetch doctor’s appointments
$sql = "SELECT a.id, a.appointment_time, a.service, a.status, p.first_name, p.last_name
        FROM appointments a
        JOIN users p ON a.patient_id = p.id
        WHERE a.doctor_id = ?
        ORDER BY a.appointment_time ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctorId);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

// Stats
$totalAppointments = count($appointments);
$totalPatients = count(array_unique(array_column($appointments, 'first_name')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Dashboard - CareLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f9fb;
      font-family: "Segoe UI", sans-serif;
    }
    .navbar {
      background: #028090;
    }
    .navbar .nav-link, .navbar-brand {
      color: #fff !important;
    }
    .stats-card {
      border-radius: 15px;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: 0.3s;
    }
    .stats-card:hover {
      transform: translateY(-4px);
    }
    .stats-card h3 {
      color: #028090;
      font-weight: bold;
    }
    .table thead {
      background: #05668D;
      color: #fff;
    }
    .modal-header {
      background: #028090;
      color: #fff;
    }
    .btn-primary {
      background-color: #028090;
      border: none;
    }
    .btn-primary:hover {
      background-color: #05668D;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand fw-bold" href="doctor_dashboard.php">CareLink Doctor</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="doctor_dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="doctor_appointments.php">Appointments</a></li>
          <li class="nav-item"><a class="nav-link" href="doctor_profile.php">Profile</a></li>
          <li class="nav-item"><a href="login.php" class="btn btn-light btn-sm ms-2">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Dashboard Content -->
  <div class="container py-5">
    <h2 class="fw-bold mb-4" style="color:#05668D;">👨‍⚕️ Doctor Dashboard</h2>

    <!-- Stats -->
    <div class="row mb-5 g-4">
      <div class="col-md-6">
        <div class="stats-card p-4 text-center">
          <i class="bi bi-calendar-check display-6 text-success mb-2"></i>
          <h5>Upcoming Appointments</h5>
          <h3><?= $totalAppointments ?></h3>
        </div>
      </div>
      <div class="col-md-6">
        <div class="stats-card p-4 text-center">
          <i class="bi bi-people display-6 text-primary mb-2"></i>
          <h5>Total Patients</h5>
          <h3><?= $totalPatients ?></h3>
        </div>
      </div>
    </div>

    <!-- Appointments Table -->
    <div class="card shadow-sm p-4">
      <h5 class="fw-bold mb-3" style="color:#05668D;">📅 Patient Appointments</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Patient</th>
              <th>Date</th>
              <th>Service</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($appointments)): ?>
              <tr>
                <td colspan="5" class="text-center text-muted">No appointments found</td>
              </tr>
            <?php else: ?>
              <?php foreach ($appointments as $appt): ?>
                <tr>
                  <td><?= htmlspecialchars($appt['first_name'] . " " . $appt['last_name']) ?></td>
                  <td><?= htmlspecialchars($appt['appointment_time']) ?></td>
                  <td><?= htmlspecialchars($appt['service']) ?></td>
                  <td>
                    <?php if ($appt['status'] === 'Paid'): ?>
                      <span class="badge bg-success">Paid</span>
                    <?php elseif ($appt['status'] === 'In Chat'): ?>
                      <span class="badge bg-info text-dark">In Chat</span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><?= htmlspecialchars($appt['status'] ?: 'Pending') ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($appt['status'] === 'Paid' || $appt['status'] === 'In Chat'): ?>
                      <button class="btn btn-sm btn-primary start-chat-btn"
                        data-id="<?= $appt['id'] ?>"
                        data-patient="<?= htmlspecialchars($appt['first_name'] . ' ' . $appt['last_name']) ?>">
                        <i class="bi bi-camera-video"></i> Start Call
                      </button>
                    <?php else: ?>
                      <button class="btn btn-sm btn-secondary">Start Call</button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Chat Modal -->
  <div class="modal fade" id="chatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Consultation with <span id="chatPatientName"></span></h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="height:300px; overflow-y:auto;">
          <div class="text-muted">💬 Chat messages will appear here...</div>
        </div>
        <div class="modal-footer">
          <input type="text" class="form-control" placeholder="Type a message...">
          <button class="btn btn-primary ms-2">Send</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // ✅ Start Chat (AJAX)
    document.querySelectorAll('.start-chat-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        const appointmentId = this.getAttribute('data-id');
        const patientName = this.getAttribute('data-patient');

        fetch('start_chat.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'appointment_id=' + appointmentId
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert('Chat session started with ' + patientName);
            location.reload();
          } else {
            alert('Error: ' + (data.error || 'Unable to start chat'));
          }
        })
        .catch(() => alert('Network error'));
      });
    });

    // ✅ Chat modal (UI only for now)
    const chatModal = document.getElementById('chatModal');
    chatModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const patientName = button?.getAttribute('data-patient');
      document.getElementById('chatPatientName').textContent = patientName || '';
    });
  </script>
</body>
</html>
