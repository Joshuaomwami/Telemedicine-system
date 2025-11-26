<?php
session_start();
include 'db.php';

// ✅ Only allow logged-in doctors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$status = "loading";
$doctor = null;

// ✅ Fetch doctor profile from DB
$sql = "SELECT * FROM doctors WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // No profile yet → redirect to setup
    header("Location: doctor_setup.php");
    exit();
}

$doctor = $result->fetch_assoc();
$status = $doctor['status'];

// ✅ Redirect if approved
if ($status === "approved") {
    header("Location: doctor_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Profile Status - CareLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8fafc;
      font-family: 'Segoe UI', sans-serif;
    }
    .status-card {
      max-width: 500px;
      border-radius: 1rem;
    }
    .status-card h4 {
      color: #028090;
      font-weight: 700;
    }
    .status-card h5 {
      font-weight: 600;
    }
    .spinner-border {
      width: 3rem;
      height: 3rem;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg" style="background-color:#028090;">
    <div class="container">
      <a class="navbar-brand fw-bold text-white" href="landing.php">
        <i class="bi bi-heart-pulse"></i> CareLink
      </a>
      <div class="ms-auto">
        <a href="landing.php" class="btn btn-outline-light me-2">Home</a>
        <a href="login.php" class="btn btn-light">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Status Card -->
  <div class="d-flex align-items-center justify-content-center py-5">
    <div class="card shadow p-4 text-center status-card">
      <h4><i class="bi bi-person-badge"></i> Doctor Profile Status</h4>

      <?php if ($status === "pending"): ?>
        <h5 class="mt-3 text-warning">
          <i class="bi bi-hourglass-split"></i> Profile Under Review
        </h5>
        <p class="text-muted small">
          Thank you for completing your setup! Our admin team is reviewing your details
          and will approve your account soon.
        </p>

        <?php if (!empty($doctor['license_file_url'])): ?>
          <a href="<?= htmlspecialchars($doctor['license_file_url']) ?>" 
             target="_blank" class="btn btn-outline-primary btn-sm mt-2">
            <i class="bi bi-file-earmark-text"></i> View Uploaded License
          </a>
        <?php endif; ?>

        <div class="mt-4">
          <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <p class="mt-3 text-muted small">
          You’ll receive an email when your profile is approved.
        </p>

      <?php elseif ($status === "rejected"): ?>
        <h5 class="mt-3 text-danger">
          <i class="bi bi-x-circle"></i> Profile Rejected
        </h5>
        <p class="text-muted small">
          Unfortunately, your profile was not approved.
          Please contact support for further assistance.
        </p>
        <a href="doctor_setup.php" class="btn btn-outline-danger btn-sm mt-3">
          <i class="bi bi-arrow-repeat"></i> Resubmit Profile
        </a>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
