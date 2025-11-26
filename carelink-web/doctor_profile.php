<?php
session_start();
include 'db.php';

// ✅ Only doctors allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$doctorId = $_SESSION['user_id'];

// ✅ Fetch profile
$sql = "SELECT * FROM doctors WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctorId);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

// ✅ Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialization = $_POST['specialization'];
    $fee = $_POST['fee_per_hour'];
    $experience = $_POST['experience'];
    $bio = $_POST['bio'];

    // Handle file upload
    $licenseFile = $doctor['license_file'];
    if (!empty($_FILES['licenseFile']['name'])) {
        $targetDir = "uploads/licenses/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = $doctorId . "_" . basename($_FILES["licenseFile"]["name"]);
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES["licenseFile"]["tmp_name"], $targetFile);
        $licenseFile = $targetFile;
    }

    $update = $conn->prepare("UPDATE doctors SET specialization=?, fee_per_hour=?, experience=?, bio=?, license_file=? WHERE user_id=?");
    $update->bind_param("sdsssi", $specialization, $fee, $experience, $bio, $licenseFile, $doctorId);
    $update->execute();

    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: doctor_profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Profile - CareLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body style="background:#f4f7fa; min-height:100vh;">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark" style="background-color:#028090;">
    <div class="container">
      <a class="navbar-brand fw-bold" href="doctor_dashboard.php"><i class="bi bi-heart-pulse"></i> CareLink Doctor</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link text-white" href="doctor_dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="doctor_appointments.php">Appointments</a></li>
          <li class="nav-item"><a class="nav-link active fw-bold" style="color:#ffd166;" href="doctor_profile.php">Profile</a></li>
          <li class="nav-item"><a href="login.php" class="btn btn-light btn-sm ms-2">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8">

        <div class="card shadow-lg border-0">
          <div class="card-header text-white" style="background:#05668D;">
            <h4 class="mb-0"><i class="bi bi-person-circle"></i> My Profile</h4>
          </div>
          <div class="card-body p-4">

            <?php if (isset($_SESSION['success'])): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
              <div class="mb-3">
                <label class="form-label fw-semibold">Specialization</label>
                <input type="text" name="specialization" class="form-control" 
                  value="<?= htmlspecialchars($doctor['specialization'] ?? '') ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Fee per Hour (KSH)</label>
                <input type="number" name="fee_per_hour" class="form-control" 
                  value="<?= htmlspecialchars($doctor['fee_per_hour'] ?? '') ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Experience (years)</label>
                <input type="number" name="experience" class="form-control" 
                  value="<?= htmlspecialchars($doctor['experience'] ?? '') ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Bio</label>
                <textarea name="bio" class="form-control" rows="4" required><?= htmlspecialchars($doctor['bio'] ?? '') ?></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Medical License</label><br>
                <?php if (!empty($doctor['license_file'])): ?>
                  <a href="<?= htmlspecialchars($doctor['license_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mb-2">
                    <i class="bi bi-file-earmark-text"></i> View Current License
                  </a><br>
                <?php endif; ?>
                <input type="file" name="licenseFile" class="form-control">
              </div>

              <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-save"></i> Update Profile
              </button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
