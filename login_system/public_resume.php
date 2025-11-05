<?php
session_start();
include('db.php');

// ✅ Dynamic resume selection
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = :id");
$stmt->execute(['id' => $id]);
$resume = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resume) {
  echo "<h2 style='text-align:center;margin-top:50px;color:red;'>Resume not found.</h2>";
  exit;
}

// ✅ Check if logged-in user is the owner
$is_owner = false;
if (isset($_SESSION['username'])) {
  $user_stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
  $user_stmt->execute(['username' => $_SESSION['username']]);
  $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
  if ($user && $user['id'] == $resume['user_id']) {
    $is_owner = true;
  }
}

// ✅ Helper functions
function toArray($value) {
  if (is_null($value) || $value === '') return [];
  if (is_array($value)) return $value;
  $decoded = json_decode($value, true);
  if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
  return [ $value ];
}

function renderItem($item, $section = '') {
  if (is_null($item) || $item === '') return '';
  if (!is_array($item)) {
    $text = nl2br(htmlspecialchars((string)$item));
    return "<div class='sub-card'><p>{$text}</p></div>";
  }

  $out = "<div class='sub-card'>";
  if (isset($item['name']) || isset($item['title'])) {
    $heading = htmlspecialchars($item['name'] ?? $item['title']);
    $class = strtolower($section) === 'experience' ? 'role' : '';
    $out .= "<h3 class='sub-head {$class}'>{$heading}</h3>";
  }

  foreach ($item as $k => $v) {
    if (in_array($k, ['name','title'])) continue;
    $vtext = is_array($v) ? nl2br(htmlspecialchars(implode("\n", $v))) : nl2br(htmlspecialchars((string)$v));
    if (strtolower($section) !== 'experience') {
      $label = ucfirst(str_replace('_',' ', $k));
      $out .= "<p class='sub-field'><strong>{$label}:</strong> {$vtext}</p>";
    } else {
      $out .= "<p class='sub-field'>{$vtext}</p>";
    }
  }

  return $out . "</div>";
}

function renderList($data, $title, $id = '') {
  $items = toArray($data);
  if (empty($items)) return;
  $idAttr = $id ? "id=\"{$id}\"" : "";
  echo "<section class='section' {$idAttr}><div class='container single-card-container'><div class='card'><h2>" . htmlspecialchars($title) . "</h2>";

  if (strtolower($title) === 'skills') {
    $flat = [];
    foreach ($items as $it) {
      $parts = is_array($it) ? array_map('trim', explode(',', implode(',', $it))) : array_map('trim', explode(',', $it));
      foreach ($parts as $p) if ($p !== '') $flat[] = $p;
    }
    echo "<div class='skills-wrap'>";
    foreach ($flat as $skill) echo "<span class='skill-pill'>" . htmlspecialchars($skill) . "</span>";
    echo "</div></div></div></section>";
    return;
  }

  foreach ($items as $item) echo renderItem($item, $title);
  echo "</div></div></section>";
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title><?= htmlspecialchars($resume['name'] ?? 'Student Portfolio') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body {margin:0;font-family:'Poppins',sans-serif;color:#fff;}
    #bg-video {position:fixed;inset:0;width:100%;height:100%;object-fit:cover;z-index:-2;}
    .bg-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:-1;}
    .site-header{padding:20px 50px;display:flex;justify-content:space-between;align-items:center;background:rgba(0,0,0,0.25);}
    .logo{font-weight:700;font-size:20px; color:#0ea5ff;} 
    .main-nav a{margin:0 10px;color:#0ea5ff;text-decoration:none;font-weight:600;}
    .main-nav a:hover{text-decoration:underline;}
    .hero-section{
      display:flex;
      justify-content:center;
      align-items:center;
      text-align:left;
      padding:100px 40px;
      gap:60px;
      max-width:1100px;
      margin:0 auto;
    }
    .hero-text{
      flex:1;
      text-align:left;
      max-width:600px;
    }
    .hero-text h1{font-size:40px;margin-bottom:10px;color:#0ea5ff;} 
    .hero-text p{font-size:18px;margin:0 0 10px;}
    .profile-pic{
      width:240px;
      height:240px;
      object-fit:cover;
      border-radius:14px;
      box-shadow:0 0 25px rgba(0,0,0,0.4);
    }
    .edit-btn{
      display:inline-block;
      margin-left:15px;
      padding:8px 16px;
      background:#0ea5ff;
      color:#fff !important; /* ✅ Force white text */
      text-decoration:none;
      border-radius:6px;
      font-weight:600;
    }
    .edit-btn:hover{
      background:#14b8ff;
      color:#fff !important; /* ✅ Stay white on hover */
    }
    .section{padding:30px 20px;}
    .container{max-width:900px;margin:0 auto;}
    .card{background:rgba(255,255,255,0.05);padding:25px 30px;border-radius:14px;
          box-shadow:0 10px 40px rgba(0,0,0,0.4);margin-bottom:25px;}
    .card h2{text-align:left;margin-top:0;color:#0ea5ff;}
    .sub-card{background:rgba(255,255,255,0.03);margin-top:12px;padding:14px 16px;border-radius:10px;border:1px solid rgba(255,255,255,0.02);}
    .sub-card p{margin:0;line-height:1.5;color:#dfe7f3;}
    .sub-head{margin:0 0 8px 0;color:#fff;font-size:16px;}
    .sub-field{margin:6px 0;color:#cfdcec;}
    .role { font-weight:700; color:#fff; } 
    .skills-wrap { display:flex; flex-wrap:wrap; gap:12px; margin-top:14px; }
    .skill-pill {
      display:inline-block;
      padding:10px 18px;
      border-radius:999px;
      background:#0ea5ff; 
      color:#fff;
      font-weight:600;
      box-shadow: 0 6px 18px rgba(0,170,255,0.2);
      white-space:nowrap;
    }
    footer{background:rgba(255,255,255,0.05);padding:10px 0;text-align:center;font-size:14px;margin-top:30px;}
    @media (max-width:700px) {
      .hero-section{flex-direction:column;text-align:center;}
      .profile-pic{width:160px;height:160px;}
      .hero-text{text-align:center;}
    }
  </style>
</head>
<body>
  <video autoplay muted loop id="bg-video">
    <source src="assets/bg.mp4" type="video/mp4">
  </video>
  <div class="bg-overlay"></div>

  <header class="site-header">
    <div class="logo"><?= htmlspecialchars($resume['name'] ?? 'Name Not Set') ?></div>
    <nav class="main-nav">
      <a href="resume_home.php">🏠 Home</a>
      <a href="#skills">Skills</a>
      <a href="#achievements">Achievements</a>
      <a href="#experience">Experience</a>
      <a href="#orgs">Organizations</a>
      <a href="#education">Education</a>
      <a href="#additional">Additional Info</a>
      <?php if ($is_owner): ?>
        <a href="resume_edit.php?id=<?= $resume['id'] ?>" class="edit-btn">✏️ Edit Resume</a>
      <?php else: ?>
        <a href="login.php" class="edit-btn">🔒 Log In to Edit</a>
      <?php endif; ?>
    </nav>
  </header>

  <section class="hero-section">
    <div class="hero-text">
      <h1><?= htmlspecialchars($resume['name'] ?? '') ?></h1>
      <p><strong><?= htmlspecialchars($resume['title'] ?? '') ?></strong></p>
      <p><?= nl2br(htmlspecialchars($resume['summary'] ?? '')) ?></p>
    </div>
    <?php if (!empty($resume['profile_image'])): ?>
      <img src="<?= htmlspecialchars($resume['profile_image']) ?>" alt="Profile Picture" class="profile-pic">
    <?php endif; ?>
  </section>

  <main>
    <?php
      renderList($resume['skills'], "Skills", "skills");
      renderList($resume['achievements'], "Achievements", "achievements");
      renderList($resume['professional_experience'], "Experience", "experience");
      renderList($resume['organization'], "Organizations", "orgs");
      renderList($resume['education'], "Education", "education");
      renderList($resume['additional_info'], "Additional Info", "additional");
    ?>
  </main>

  <footer>
    © <?= date("Y") ?> <?= htmlspecialchars($resume['name'] ?? '') ?> — All Rights Reserved
  </footer>
</body>
</html>
