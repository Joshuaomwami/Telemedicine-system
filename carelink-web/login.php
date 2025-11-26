<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'patient') {
                header("Location: patient-dashboard.php");
                exit();
            } elseif ($user['role'] === 'doctor') {
                if ($user['status'] === 'pending') {
                    header("Location: doctor_pending.php");
                } elseif ($user['status'] === 'approved') {
                    header("Location: doctor_dashboard.php");
                } elseif ($user['status'] === 'rejected') {
                    $error = "Your doctor application was rejected. Contact support.";
                } else {
                    header("Location: doctor_dashboard.php");
                }
                exit();
            } elseif ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CareLink Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #F0F3BD;
    }
    .login-card {
      max-width: 420px;
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
        <a href="signup.php">Sign up</a>
      </div>
    </div>
  </nav>

  <!-- Login Card -->
  <div class="login-card">
    <div class="text-center mb-3">
      <i class="bi bi-heart-fill" style="font-size:2rem; color:#02C39A;"></i>
      <h3 class="fw-bold" style="color:#05668D;">Welcome Back</h3>
      <p class="text-muted mb-0">Sign in to your account</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
      </div>

      <div class="mb-3">
        <label>Password</label>
        <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
      </div>

      <button type="submit" class="btn btn-custom w-100 mt-3">Login</button>
    </form>

    <div class="text-center mt-3">
      <p class="mb-1">Don't have an account? 
        <a href="signup.php" style="color:#02C39A; font-weight:600;">Sign up</a>
      </p>
      <a href="forgot-password.php" style="color:#00A896; font-size:14px;">Forgot your password?</a>
    </div>
  </div>

</body>
</html>
