<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Portfolio</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    html, body {
      height: 100%;
      margin: 0;
      font-family: 'Poppins', sans-serif;
      color: #fff;
      overflow: hidden;
    }

    /* Background video */
    #bg-video {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      object-fit: cover;
      z-index: -2;
    }

    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.55);
      z-index: -1;
    }

    /* Landing content */
    .landing {
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 20px;
      animation: fadeIn 1.2s ease forwards;
    }

    .landing h1 {
      font-size: 68px;
      font-weight: 700;
      margin: 0 0 10px;
      letter-spacing: 2px;
      text-transform: uppercase;
      background: linear-gradient(90deg, #0ea5ff, #00ccff, #0066cc);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 0 25px rgba(14,165,255,0.4);
      opacity: 0;
      transform: translateY(-30px);
      animation: slideDown 1.2s ease forwards;
    }

    .landing p {
      font-size: 22px;
      font-weight: 300;
      margin-bottom: 40px;
      color: #d1d5db;
      opacity: 0;
      transform: translateY(20px);
      animation: fadeUp 1.5s ease forwards;
      animation-delay: 0.5s;
    }

    .landing a {
      display: inline-block;
      padding: 14px 40px;
      font-size: 18px;
      font-weight: 600;
      color: #fff;
      background: linear-gradient(90deg, #0066cc, #0ea5ff);
      border-radius: 50px;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 8px 20px rgba(14,165,255,0.25);
      opacity: 0;
      transform: translateY(30px);
      animation: fadeUp 1.5s ease forwards;
      animation-delay: 1s;
    }

    .landing a:hover {
      transform: scale(1.08);
      box-shadow: 0 12px 30px rgba(14,165,255,0.4);
    }

   
    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes slideDown {
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeUp {
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(8px); }
    }
  </style>
</head>
<body>
  <video autoplay muted loop id="bg-video">
    <source src="assets/bg.mp4" type="video/mp4">
  </video>
  <div class="overlay"></div>

  <div class="landing">
    <h1>STUDENT PORTFOLIO</h1>
    <p>My personal showcase of skills and achievements</p>
    <a href="login.php">Start</a>
  </div>
</body>
</html>
