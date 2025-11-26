<?php
session_start();
require_once "db.php";

// ✅ Only patients can pay
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// ✅ Ensure appointment ID exists
if (!isset($_GET['id'])) {
    die("No appointment selected.");
}
$appointment_id = intval($_GET['id']);

// ✅ Get appointment details
$stmt = $conn->prepare("
    SELECT a.id AS appointment_id, a.*, d.fee_per_hour, u.first_name, u.last_name 
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.user_id
    JOIN users u ON a.doctor_id = u.id
    WHERE a.id = ? AND a.patient_id = ?
");
$stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    die("Appointment not found.");
}

$message = "";
$status = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phone = trim($_POST['phone']);

    // ✅ Validate phone format (e.g., 254712345678)
    if (!preg_match("/^2547\d{8}$/", $phone)) {
        $message = "❌ Invalid phone number. Use format 2547XXXXXXXX.";
        $status = "failed";
    } else {
        $message = "✅ Payment successful (Simulation Mode). Redirecting to Dashboard...";
        $status = "paid";
    }

    // ✅ Update appointment status
    $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE id=? AND patient_id=?");
    $stmt->bind_param("sii", $status, $appointment_id, $_SESSION['user_id']);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>M-Pesa Payment - CareLink</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #FFFDD0;
    }
    .payment-card {
      max-width: 420px;
      margin: 50px auto;
      background: white;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      padding: 25px;
    }
    .btn-pay {
      background-color: #66D9A8;
      color: white;
      font-weight: 600;
      border: none;
      width: 100%;
    }
    .btn-pay:hover {
      background-color: #57c89a;
    }
    .instructions {
      background: #FFF9C4;
      border-radius: 10px;
      padding: 15px;
      margin-top: 15px;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="payment-card">
    <h4 class="text-center text-success mb-3">M-Pesa Payment</h4>

    <?php if ($message): ?>
      <div class="alert <?= ($status === 'paid') ? 'alert-success' : 'alert-danger' ?> text-center">
        <?= htmlspecialchars($message) ?>
      </div>

      <?php if ($status === 'paid'): ?>
        <script>
          setTimeout(() => {
            window.location.href = "patient-dashboard.php";
          }, 3000);
        </script>
      <?php endif; ?>

      <a href="patient-dashboard.php" class="btn btn-primary w-100 mt-2">Back to Dashboard</a>

    <?php else: ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">M-Pesa Phone Number</label>
          <input type="text" name="phone" class="form-control text-center" placeholder="2547XXXXXXXX" required>
        </div>

        <div class="instructions">
          <strong>Payment Instructions:</strong>
          <ol class="mt-2 mb-1">
            <li>Enter your M-Pesa registered phone number</li>
            <li>Click <b>Pay Now</b> to simulate payment</li>
            <li>This is a test mode — no real transaction</li>
          </ol>
          <hr>
          <p><b>Till Number:</b> 5555555<br>
          <b>Amount:</b> KSH <?= number_format($appointment['fee_per_hour'], 0) ?></p>
        </div>

        <button type="submit" class="btn btn-pay mt-3">Pay Now</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
