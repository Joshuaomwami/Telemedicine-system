<?php
session_start();
include 'db.php';

$error = "";
$info = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = strtolower(trim($_POST['role']));
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // validations
    if (!$role) {
        $error = "Please select a role.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $status = ($role === "doctor") ? "pending" : "approved";

        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $firstName, $lastName, $email, $phone, $hashedPassword, $role, $status);

        if ($stmt->execute()) {
            $info = "🎉 Account created successfully! You can now log in.";
        } else {
            $error = "Signup failed: " . $conn->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CareLink Signup</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #F0F3BD;
    }
    .signup-card {
      max-width: 500px;
      margin: 60px auto;
      padding: 2rem;
      border-radius: 1rem;
      background: #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .btn-custom {
      background-color: #02C39A;
      color: #fff;
      font-weight: 600;
    }
    .btn-custom:hover {
      background-color: #028090;
      color: #fff;
    }
    .navbar {
      background-color: #028090;
    }
    .navbar a {
      color: #fff;
      font-weight: 500;
      text-decoration: none;
      margin-left: 15px;
    }
    .navbar a:hover {
      color: #F0F3BD;
    }
    label {
      font-weight: 600;
      color: #05668D;
      margin-top: 10px;
    }
    select, input {
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg px-4">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold text-white" href="landing.php">
        <i class="bi bi-heart-fill"></i> CareLink
      </a>
      <div class="ms-auto">
        <a href="landing.php">Home</a>
        <a href="login.php">Login</a>
      </div>
    </div>
  </nav>

  <!-- Signup Form -->
  <div class="signup-card">
    <div class="text-center mb-3">
      <i class="bi bi-heart-fill" style="color:#02C39A; font-size:2rem;"></i>
      <h3 style="color:#02C39A; font-weight:bold;">CareLink</h3>
      <h4 style="color:#028090; font-weight:bold;">Join CareLink</h4>
      <p class="text-muted">Create your account to get started</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if ($info): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($info) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="POST" action="signup.php">
      <div class="mb-3">
        <label>Role</label>
        <select class="form-select" name="role" required>
          <option value="">Select your role</option>
          <option>Doctor</option>
          <option>Patient</option>
          <option>Admin</option>
        </select>
      </div>

      <div class="mb-3">
        <label>First Name</label>
        <input type="text" class="form-control" name="first_name" placeholder="First name" required>
      </div>

      <div class="mb-3">
        <label>Last Name</label>
        <input type="text" class="form-control" name="last_name" placeholder="Last name" required>
      </div>

      <div class="mb-3">
        <label>Email</label>
        <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
      </div>

      <div class="mb-3">
        <label>Phone Number</label>
        <input type="tel" class="form-control" name="phone" placeholder="+254 700 000 000">
      </div>

      <div class="mb-3">
        <label>Password</label>
        <input type="password" class="form-control" name="password" placeholder="Create a password" required>
      </div>

      <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm your password" required>
      </div>

      <button type="submit" class="btn btn-custom w-100">Create Account</button>
    </form>

    <div class="text-center mt-3">
      <small>Already have an account? <a href="login.php" style="color:#02C39A; font-weight:600;">Sign in</a></small>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
