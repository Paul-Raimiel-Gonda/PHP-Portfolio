<?php
session_start();
require 'db.php'; 

if (isset($_SESSION['username'])) {
    header("Location: resume_edit.php"); // ✅ redirect directly to edit page
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
            header("Location: resume_edit.php"); // ✅ go to resume editor instead of home.php
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">

  <style>
    :root{ --accent:#0ea5ff; --accent-2:#0066cc; }
    html,body{height:100%;margin:0;font-family:Poppins,system-ui,Arial;color:#fff;background:transparent;}

    #bg-video{ position:fixed; inset:0; width:100%; height:100%; object-fit:cover; z-index:-3; }
    .bg-overlay{ position:fixed; inset:0; background:linear-gradient(180deg, rgba(6,6,6,0.30), rgba(6,6,6,0.55)); z-index:-2; }

    .auth-full {
      position:fixed;
      inset:0;
      display:flex;
      align-items:center;
      justify-content:center;
      z-index:9999;
      padding:20px;
    }

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
      display: block;
      width: 100%;
      height: 42px;
      padding: 0 12px;
      margin: 10px 0;
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

    @media (max-width:420px){ .auth-card{ padding:20px; } .auth-card h2{ font-size:18px; } }
  </style>
</head>
<body>
  <video autoplay muted loop id="bg-video" preload="auto">
    <source src="assets/bg.mp4" type="video/mp4">
  </video>
  <div class="bg-overlay"></div>

  <div class="auth-full">
    <div class="auth-card">
      <h2>Login</h2>

      <?php if ($message): ?>
        <div class="msg error"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <input name="username" type="text" placeholder="Username" required />
        <input name="password" type="password" placeholder="Password" required />
        <button type="submit">Log In</button>
      </form>

      <p class="small">Don't have an account? <a href="register.php">Register</a></p>
    </div>
  </div>
</body>
</html>
