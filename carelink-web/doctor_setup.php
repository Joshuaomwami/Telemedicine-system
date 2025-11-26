<?php
session_start();
include 'db.php';

// ✅ Only allow logged-in doctors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $specialization = trim($_POST['specialization']);
    $feePerHour = trim($_POST['feePerHour']);
    $experience = trim($_POST['experience']);
    $bio = trim($_POST['bio']);
    $userId = $_SESSION['user_id'];
    $licenseFile = null;

    // ✅ File Upload Handling
    if (isset($_FILES['licenseFile']) && $_FILES['licenseFile']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/licenses/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmp = $_FILES['licenseFile']['tmp_name'];
        $fileName = $userId . "_" . time() . "_" . basename($_FILES['licenseFile']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            $licenseFile = $targetFile;
        } else {
            $message = "❌ Failed to upload license file.";
        }
    }

    if ($licenseFile) {
      // ✅ Insert or update doctor profile
      $stmt = $conn->prepare("REPLACE INTO doctors 
          (user_id, specialization, fee_per_hour, experience, bio, license_file, status, created_at, updated_at) 
          VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())");
      $stmt->bind_param("ssisss", $userId, $specialization, $feePerHour, $experience, $bio, $licenseFile);
  
      if ($stmt->execute()) {
          $_SESSION["message"] = "✅ Profile submitted for review!";
          header("Location: doctor_dashboard.php");
          exit();
      } else {
          $message = "❌ Database error: " . $stmt->error;
      }
  }
  
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Setup - CareLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body style="background-color:#F0F3BD;">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg" style="background-color:#028090;">
  <div class="container">
    <a class="navbar-brand fw-bold text-white" href="landing.php">CareLink</a>
    <div class="ms-auto">
      <a href="landing.php" class="btn btn-light btn-sm me-2">← Dashboard</a>
      <a href="login.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>

<!-- Page Content -->
<div class="container py-5"> 
  <div class="text-center mb-4">
    <h1 class="fw-bold" style="color:#05668D;"> Complete Your Profile</h1>
    <p class="text-muted">Provide your professional details to get verified.</p>
  </div>

  <div class="card shadow-lg border-0 rounded-4 p-4 mx-auto" style="max-width:700px;">
    <h3 class="fw-bold text-center mb-4" style="color:#028090;">Doctor Setup</h3>

    <?php if ($message): ?>
      <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
      <!-- Specialization -->
      <div class="mb-3">
        <label class="form-label fw-bold">Specialization</label>
        <select name="specialization" class="form-select" required>
          <option value="">-- Select your specialization --</option>
          <option value="Dermatology">Dermatology</option>
          <option value="Reproductive Health">Reproductive Health</option>
          <option value="Cardiology">Cardiology</option>
        </select>
      </div>

      <!-- Fee & Experience -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label fw-bold">Fee per Hour (KSH)</label>
          <input type="number" name="feePerHour" class="form-control" placeholder="e.g. 2000" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-bold">Years of Experience</label>
          <input type="number" name="experience" class="form-control" placeholder="e.g. 5" required>
        </div>
      </div>

      <!-- Bio -->
      <div class="mb-3">
        <label class="form-label fw-bold">Bio</label>
        <textarea name="bio" class="form-control" rows="4" placeholder="Write a brief bio about yourself..." required></textarea>
      </div>

      <!-- File Upload -->
      <div class="mb-3">
        <label class="form-label fw-bold">Medical License & Verification Documents</label>
        <input type="file" name="licenseFile" class="form-control" required>
      </div>

      <div class="alert alert-warning small">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong> Note:</strong> Your documents will be reviewed by our admin team.  
        You’ll receive an email once your profile is approved.
      </div>

      <div class="text-center">
        <button type="submit" class="btn btn-lg text-white px-5" style="background-color:#02C39A;">
          Submit for Review
        </button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
