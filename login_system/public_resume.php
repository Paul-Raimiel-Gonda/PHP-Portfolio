<?php
session_start();
include('db.php'); // ✅ use PDO connection

// Always show the single shared resume (id = 1)
$id = 1;

$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = :id");
$stmt->execute(['id' => $id]);
$resume = $stmt->fetch(PDO::FETCH_ASSOC);

// If no resume found, show message
if (!$resume) {
  echo "<h2 style='text-align:center;margin-top:50px;color:red;'>Resume not found.</h2>";
  exit;
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title><?= htmlspecialchars($resume['name'] ?? 'Student Portfolio') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&family=Montserrat:wght@600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    /* 🔹 Highlighted Edit Resume button */
    .edit-box {
      display: inline-block;
      background: linear-gradient(90deg, #0ea5ff, #0077ff);
      color: #fff !important;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      box-shadow: 0 0 15px rgba(14,165,255,0.6);
      transition: all 0.3s ease;
    }
    .edit-box:hover {
      background: linear-gradient(90deg, #0077ff, #0ea5ff);
      transform: scale(1.05);
      box-shadow: 0 0 25px rgba(14,165,255,0.9);
    }
  </style>
</head>
<body>

  <video autoplay muted loop id="bg-video" preload="auto">
    <source src="assets/bg.mp4" type="video/mp4">
  </video>

  <div class="bg-overlay"></div>

  <header class="site-header">
    <div class="container header-inner">
      <div class="logo"><?= htmlspecialchars($resume['name'] ?? 'Name Not Set') ?></div>
      <nav class="main-nav">
        <a href="#about">About</a>
        <a href="#skills">Skills</a>
        <a href="#training">Training</a>
        <a href="#orgs">Organizations</a>
        <a href="#education">Education</a>
        <a class="edit-box" href="login.php">Edit Resume</a>
      </nav>
    </div>
  </header>

  <section id="hero" class="hero-section">
    <div class="container hero-inner">
      <h1 class="hero-title">
        <span class="hero-name-accent"><?= htmlspecialchars($resume['name'] ?? '') ?></span>
      </h1>
      <p class="hero-subtitle"><?= htmlspecialchars($resume['title'] ?? '') ?></p>
      <p class="hero-summary"><?= htmlspecialchars($resume['summary'] ?? '') ?></p>
      <a class="cta-btn" href="#about">Explore Portfolio</a>
    </div>
  </section>

  <main>
    <section id="about" class="section">
      <div class="container grid">
        <div class="card about-card">
          <h2>About</h2>
          <p><?= htmlspecialchars($resume['summary'] ?? '') ?></p>
        </div>

        <div class="card skills-card">
          <h2>Skills</h2>
          <p><?= nl2br(htmlspecialchars($resume['skills'] ?? '')) ?></p>
        </div>
      </div>
    </section>

    <section id="training" class="section">
      <div class="container">
        <h2>Training</h2>
        <p><?= nl2br(htmlspecialchars($resume['training'] ?? '')) ?></p>
      </div>
    </section>

    <section id="orgs" class="section">
      <div class="container">
        <h2>Organizations</h2>
        <p><?= nl2br(htmlspecialchars($resume['organization'] ?? '')) ?></p>
      </div>
    </section>

    <section id="education" class="section">
      <div class="container">
        <h2>Education</h2>
        <p><?= nl2br(htmlspecialchars($resume['education'] ?? '')) ?></p>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-inner">
      <div>© <?= date("Y") ?> <?= htmlspecialchars($resume['name'] ?? '') ?></div>
      <div class="footer-links">
        <a href="login.php" class="edit-box">Edit Resume</a>
      </div>
    </div>
  </footer>

</body>
</html>
