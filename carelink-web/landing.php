<?php
include 'db.php'; // database connection

$message = "";
$message_type = ""; // "success" or "danger"

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $message_text = $conn->real_escape_string($_POST['message']);

    $sql = "INSERT INTO messages (name, email, message, created_at) 
            VALUES ('$name', '$email', '$message_text', NOW())";

    if ($conn->query($sql) === TRUE) {
        $message = "Message sent successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to send message!";
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CareLink - Accessible Healthcare</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --sand: #F0F3BD;
      --mint: #02C39A;
      --mint-2: #00A896;
      --teal: #028090;
      --denim: #05668D;
    }

    body {
      background-color: var(--sand);
      font-family: "Segoe UI", system-ui, -apple-system, Roboto, "Helvetica Neue", Arial;
      color: #222;
    }

    /* Navbar */
    .navbar {
      background-color: var(--teal);
    }
    .navbar .navbar-brand {
      color: #fff;
      font-weight: 700;
    }
    .navbar .nav-link {
      color: #fff !important;
      font-weight: 600;
      margin-left: .35rem;
      margin-right: .35rem;
    }
    .navbar .nav-link:hover {
      color: var(--sand) !important;
    }
    .btn-custom {
      background-color: var(--mint);
      color: #fff;
      font-weight: 700;
    }
    .btn-custom:hover {
      background-color: var(--teal);
      color: #fff;
    }

    /* Hero */
    .hero {
      background: url('images/img-2001.jpg') center/cover no-repeat;
      padding: 100px 20px;
      position: relative;
      color: white;
      text-align: center;
    }
    .hero::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(2,128,138,0.45); /* subtle teal overlay */
    }
    .hero-content { position: relative; z-index: 2; max-width: 900px; margin: auto; }
    .hero h1 {
      color: var(--sand);
      font-weight: 800;
      font-size: clamp(1.8rem, 4.5vw, 3.5rem);
    }
    .hero p { color: rgba(255,255,255,0.93); font-size: 1.05rem; margin-top: .75rem; }

    /* Cards, services, contact */
    .card-cta { border-radius: 12px; box-shadow: 0 8px 30px rgba(2,128,138,0.08); }
    .about-img img { border-radius: 12px; width: 100%; height: auto; object-fit: cover; }
    .service { transition: transform .18s ease, box-shadow .18s ease; }
    .service:hover { transform: translateY(-6px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    .service i { font-size: 2rem; color: var(--mint); }

    .contact form { background: #fff; padding: 1.2rem; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.06); }

    footer { background: var(--denim); color: #fff; padding: 14px 0; text-align:center; margin-top: 40px; border-top-left-radius: 8px; border-top-right-radius: 8px; }

    /* small helpers */
    .nav-item .btn-login {
      padding: .4rem .8rem;
      border-radius: 8px;
      font-weight: 700;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="#">CareLink</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
              aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item">
            <a class="nav-link" href="#about">About</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="#services">Services</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="#contact">Contact</a>
          </li>

          <li class="nav-item ms-lg-3">
            <a class="btn btn-custom nav-link btn-login" href="login.php" role="button">Login</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- flash message (from contact form) -->
  <div class="container mt-3">
    <?php if (!empty($message)): ?>
      <div class="alert alert-<?= ($message_type === 'success') ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
  </div>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1>Accessible Healthcare, Anytime.</h1>
      <p>Connect with certified healthcare professionals from the comfort of your home. Fast, reliable, and secure telemedicine consultations.</p>
      <a href="login.php" class="btn btn-light fw-bold mt-3">Get Started</a>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="about container py-5">
    <div class="row align-items-center g-4">
      <div class="col-md-6">
        <h2 style="color:var(--denim); font-weight:700; text-align:left;">About Us</h2>
        <p class="lead">CareLink is a revolutionary telemedicine platform making healthcare accessible, convenient, and affordable. We specialize in skin conditions and sexual & reproductive health, connecting patients with verified healthcare professionals through secure video consultations.</p>
      </div>
      <div class="col-md-6 about-img">
        <img src="images/img-2002.jpg" alt="About CareLink">
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section id="services" class="services py-4">
    <div class="container">
      <h2 class="text-center" style="color:var(--denim); font-weight:700;">Our Services</h2>
      <div class="service-boxes row row-cols-1 row-cols-md-2 g-4 mt-3">
        <div class="col">
          <div class="service p-4 h-100 card-cta">
            <div class="text-center">
              <i class="bi bi-shield-check"></i>
              <h3 class="mt-3" style="color:var(--teal);">Skin Condition Consultation</h3>
              <p>Get expert dermatological advice for acne, eczema, psoriasis, and other skin concerns from certified specialists.</p>
            </div>
          </div>
        </div>

        <div class="col">
          <div class="service p-4 h-100 card-cta">
            <div class="text-center">
              <i class="bi bi-heart"></i>
              <h3 class="mt-3" style="color:var(--teal);">Sexual & Reproductive Health</h3>
              <p>Confidential consultations with specialized healthcare providers for sexual health, family planning, and reproductive concerns.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="contact py-4">
    <div class="container">
      <h2 class="text-center" style="color:var(--denim); font-weight:700;">Contact Us</h2>

      <form id="contactForm" action="landing.php" method="POST" class="mx-auto mt-3" style="max-width:680px;">
        <div class="mb-3">
          <label class="form-label" for="name">Name</label>
          <input id="name" type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input id="email" type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label" for="message">Message</label>
          <textarea id="message" name="message" rows="4" class="form-control" required></textarea>
        </div>

        <button type="submit" class="btn btn-custom w-100">Send Message</button>
      </form>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <p class="mb-0">&copy; <span id="year"></span> CareLink. All rights reserved.</p>
  </footer>

  <script>
    document.getElementById("year").textContent = new Date().getFullYear();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
