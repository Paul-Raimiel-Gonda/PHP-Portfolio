<?php
session_start();
include('db.php');

$username = $_SESSION['username'] ?? null;

if (!isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    $stmt_u = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
    $stmt_u->execute(['username' => $_SESSION['username']]);
    $u = $stmt_u->fetch(PDO::FETCH_ASSOC);
    if ($u) $_SESSION['user_id'] = (int)$u['id'];
}

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

$stmt = $pdo->query("SELECT id, user_id, name, title, summary FROM resumes ORDER BY id ASC");
$resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($user_id) {
    usort($resumes, function($a, $b) use ($user_id) {
        if ($a['user_id'] == $user_id) return -1;
        if ($b['user_id'] == $user_id) return 1;
        return 0;
    });
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Resume Directory</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root { --accent:#0ea5ff; --accent-2:#0066cc; --glow:#00f6ff; }

    html,body{
      height:100%;
      margin:0;
      font-family:'Poppins',sans-serif;
      color:#fff;
      background:transparent;
    }

    /* --- THIS IS THE FIX --- */
    #bg-video{
      position:fixed;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      z-index:-9999;
      pointer-events:none;
    }
    .overlay{
      position:fixed;
      inset:0;
      background:rgba(0,0,0,0.55);
      z-index:-9998;
      pointer-events:none;
    }
    /* --- END FIX --- */

    header{display:flex;justify-content:space-between;align-items:center;padding:20px 60px;background:rgba(0,0,0,0.25);box-shadow:0 4px 20px rgba(0,0,0,0.3);}
    .logo{font-weight:700;font-size:22px;color:var(--accent);}
    .username{font-weight:500;color:#9ac7ff;font-size:18px;margin-left:10px;}
    .login-btn{background:var(--accent);color:#fff;padding:10px 20px;text-decoration:none;border-radius:8px;font-weight:600;transition:background .2s;}
    .login-btn:hover{background:#14b8ff;}
    h1{color:var(--accent);text-align:center;font-size:38px;margin-top:40px;}
    p.subtitle{text-align:center;color:#cfdcec;font-size:18px;margin-bottom:30px;}
    .container{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:25px;padding:40px;max-width:1200px;margin:0 auto;position:relative;z-index:1;}
    .card{position:relative;background:rgba(255,255,255,0.04);border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.4);padding:25px 30px;transition:transform .25s,background .25s,box-shadow .25s;text-decoration:none;color:#fff;backdrop-filter:blur(6px);}
    .card:hover{transform:translateY(-6px);background:rgba(255,255,255,0.06);}
    .card h2{color:var(--accent);margin:0 0 6px 0;font-size:22px;}
    .card h4{color:#9ac7ff;font-weight:500;margin:0 0 12px 0;}
    .card p{color:#dfe7f3;font-size:15px;line-height:1.5;margin:0 0 12px 0;}
    .your-resume { border:2px solid var(--glow); box-shadow:0 0 24px var(--glow), inset 0 0 10px rgba(0,246,255,0.12); background:rgba(0,246,255,0.03); }
    .your-resume-badge { position:absolute; top:12px; right:12px; background:linear-gradient(90deg,var(--accent),var(--glow)); color:#000; font-weight:800; font-size:12px; padding:6px 10px; border-radius:8px; box-shadow:0 6px 18px rgba(0,246,255,0.12); text-transform:uppercase; }
    @media (max-width:700px){ header{padding:12px 18px;} h1{font-size:26px;margin-top:18px;} .container{padding:20px;gap:16px;} }
    footer{text-align:center;padding:15px;background:rgba(255,255,255,0.03);margin-top:40px;font-size:14px;position:relative;z-index:1;}
  </style>
</head>
<body>

  <video autoplay muted loop id="bg-video">
    <source src="assets/bg.mp4" type="video/mp4">
  </video>
  <div class="overlay"></div>

  <header>
    <div class="logo">
      <?php if ($is_logged_in): ?>
        Welcome, <span class="username"><?= htmlspecialchars($username ?? '') ?></span>
      <?php else: ?>Resume Directory<?php endif; ?>
    </div>

    <div>
      <?php if ($is_logged_in): ?>
        <a href="logout.php" class="login-btn">Logout</a>
      <?php else: ?>
        <a href="login.php" class="login-btn">Login</a>
      <?php endif; ?>
    </div>
  </header>

  <main>
    <h1>Public Resume Directory</h1>
    <p class="subtitle"><?= $is_logged_in ? 'You are logged in — your resume is highlighted below.' : 'Browse available resumes — click a card to view details.' ?></p>

    <section class="container">
      <?php foreach ($resumes as $r):
        $owner_id = (int)$r['user_id'];
        $is_user_resume = $is_logged_in && $owner_id === (int)$user_id;
        $publicUrl = "public_resume.php?id=" . intval($r['id']);
        $editUrl   = "resume_edit.php?id=" . intval($r['id']);
        $cardHref = $is_user_resume ? $editUrl : $publicUrl;
      ?>
      <a class="card <?= $is_user_resume?'your-resume':'' ?>" href="<?= htmlspecialchars($cardHref) ?>">
        <?php if ($is_user_resume): ?><div class="your-resume-badge">My Resume</div><?php endif; ?>
        <h2><?= htmlspecialchars($r['name']) ?></h2>
        <h4><?= htmlspecialchars($r['title']) ?></h4>
        <p><?= nl2br(htmlspecialchars($r['summary'])) ?></p>
      </a>
      <?php endforeach; ?>
    </section>
  </main>

  <footer>© <?= date("Y") ?> Resume System — All Rights Reserved</footer>
</body>
</html>
