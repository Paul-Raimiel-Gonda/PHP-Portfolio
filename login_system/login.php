<?php
session_start();
require 'db.php'; // uses $pdo from your db.php

// if already logged in
if (isset($_SESSION['username'])) {
    header("Location: home.php");
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $message = "All fields are required!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            header("Location: home.php");
            exit();
        } else {
            $message = "Invalid Username or Password";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Login — Student Portfolio</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <!-- minimal font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <!-- Self-contained auth CSS (won't touch your global styles) -->
  <style>
    :root{ --accent:#0ea5ff; --accent-2:#0066cc; }
    html,body{height:100%;margin:0;font-family:Poppins,system-ui,Arial;color:#fff;background:transparent;}
    /* background video fallback: if you already have bg video elsewhere, it's OK */
    #bg-video{ position:fixed; inset:0; width:100%; height:100%; object-fit:cover; z-index:-3; }
    .bg-overlay{ position:fixed; inset:0; background:linear-gradient(180deg, rgba(6,6,6,0.30), rgba(6,6,6,0.55)); z-index:-2; }

    /* wrapper centers card with inline fallback priority */
    .auth-full {
      position:fixed;
      inset:0;
      display:flex;
      align-items:center;
      justify-content:center;
      z-index:9999;
      padding:20px;
    }

    /* card — locally scoped and high specificity */
    .auth-card{
      width:360px;
      max-width:100%;
      background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03));
      border-radius:14px;
      padding:28px;
      backdrop-filter:blur(8px);
      box-shadow: 0 20px 50px rgba(0,0,0,0.6);
      border:1px solid rgba(255,255,255,0.06);
      color:#fff;
      z-index:10000;
      text-align:center;
    }

    .auth-card h2{ margin:0 0 12px 0; font-weight:700; font-size:20px; }
   .auth-card input {
  display: block;        /* force each input to its own line */
  width: 100%;           /* match button width */
  max-width: 100%;       /* prevent overflow */
  height: 42px;          /* align height with button */
  padding: 0 12px;
  margin: 10px 0;        /* keep spacing like before */
  border-radius: 8px;
  border: none;
  font-size: 15px;
  box-sizing: border-box;
}

    .auth-card button{
      width:100%; padding:12px; margin-top:8px; border-radius:10px; border:none; background:linear-gradient(90deg,var(--accent-2),var(--accent)); color:#fff; font-weight:700; cursor:pointer;
      box-shadow:0 10px 30px rgba(14,165,255,0.12);
    }
    .auth-card .small{ color:rgba(255,255,255,0.9); margin-top:12px; font-size:14px; }
    .auth-card a{ color:var(--accent); font-weight:700; text-decoration:none; }
    .auth-card .msg{ margin:10px 0 0; padding:8px 10px; border-radius:6px; font-weight:600; }
    .auth-card .msg.error{ background: rgba(255,30,30,0.08); color:#ff6b6b; border:1px solid rgba(255,30,30,0.12); }
    /* small responsive tweak */
    @media (max-width:420px){ .auth-card{ padding:20px; } .auth-card h2{ font-size:18px; } }
  </style>
</head>
<body>

  <!-- keep your existing bg video path -->
  <video autoplay muted loop id="bg-video" preload="auto">
    <source src="assets/bg.mp4" type="video/mp4">
  </video>
  <div class="bg-overlay"></div>

  <div class="auth-full" role="main" aria-labelledby="login-title">
    <div class="auth-card" aria-live="polite">
      <h2 id="login-title">Login</h2>

      <?php if ($message): ?>
        <div class="msg error"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <form method="POST" action="" style="margin-top:12px;">
        <input name="username" type="text" placeholder="Username" autocomplete="username" required />
        <input name="password" type="password" placeholder="Password" autocomplete="current-password" required />
        <button type="submit">Log In</button>
      </form>

      <p class="small">Don't have an account? <a href="register.php">Register</a></p>
    </div>
  </div>

  <!-- tiny fallback script: ensures wrapper styles if something mutates them -->
  <script>
    (function(){
      var wrapper = document.querySelector('.auth-full');
      var card = document.querySelector('.auth-card');
      function enforce(){
        if(wrapper){
          wrapper.style.position='fixed';
          wrapper.style.inset='0';
          wrapper.style.display='flex';
          wrapper.style.alignItems='center';
          wrapper.style.justifyContent='center';
          wrapper.style.zIndex='9999';
        }
        if(card){
          card.style.zIndex='10000';
        }
      }
      window.addEventListener('load', enforce);
      setTimeout(enforce, 250);
    })();
  </script>
</body>
</html>
