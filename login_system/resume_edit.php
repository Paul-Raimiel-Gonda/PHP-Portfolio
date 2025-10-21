<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$resume_id = 1;

$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = :id");
$stmt->execute(['id' => $resume_id]);
$resume = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resume) {
    die("Shared resume (id=1) not found. Please ensure a row with id=1 exists in the resumes table.");
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['name','title','summary','skills','training','education','organization'];

    $updates = [];
    $params = [];

    foreach ($fields as $f) {
        if (isset($_POST[$f])) {
            $value = trim((string)$_POST[$f]);
            if ($value !== '' && (!isset($resume[$f]) || $value !== (string)$resume[$f])) {
                $updates[] = "$f = :$f";
                $params[$f] = $value;
            }
        }
    }

    if (count($updates) > 0) {
        $params['id'] = $resume_id;
        $sql = "UPDATE resumes SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = :id");
        $stmt->execute(['id' => $resume_id]);
        $resume = $stmt->fetch(PDO::FETCH_ASSOC);

        $message = "The shared resume has been updated successfully!";
    } else {
        $message = "No changes were made.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Edit Resume — Student Portfolio</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --accent:#0ea5ff;
      --accent-2:#0066cc;
    }
    html,body {
      height:100%;
      margin:0;
      font-family:'Poppins',sans-serif;
      color:#fff;
      background:transparent;
      overflow-x:hidden;
    }
    #bg-video {
      position:fixed;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      z-index:-3;
    }
    .overlay {
      position:fixed;
      inset:0;
      background:linear-gradient(180deg, rgba(0,0,0,0.35), rgba(0,0,0,0.75));
      z-index:-2;
    }
    .container {
      width:100%;
      max-width:750px;
      margin:80px auto;
      background:rgba(255,255,255,0.06);
      border-radius:16px;
      padding:40px 50px;
      backdrop-filter:blur(10px);
      box-shadow:0 20px 40px rgba(0,0,0,0.6);
      box-sizing:border-box;
    }
    h2 {
      text-align:center;
      margin-top:0;
      margin-bottom:20px;
      font-weight:700;
      font-size:24px;
    }
    label {
      display:block;
      margin-bottom:6px;
      font-weight:600;
      color:#ddd;
    }
    input, textarea {
      width:100%;
      padding:12px;
      margin-bottom:18px;
      border:none;
      border-radius:10px;
      background:#fff;
      font-size:15px;
      color:#111;
      box-sizing:border-box;
    }
    textarea { resize:vertical; height:90px; }
    button {
      width:100%;
      padding:14px;
      border:none;
      border-radius:10px;
      background:linear-gradient(90deg,var(--accent-2),var(--accent));
      color:#fff;
      font-weight:700;
      cursor:pointer;
      box-shadow:0 10px 25px rgba(14,165,255,0.25);
    }
    .msg {
      text-align:center;
      background:rgba(0,255,153,0.12);
      border:1px solid rgba(0,255,153,0.25);
      color:#7ef5b8;
      padding:10px;
      border-radius:8px;
      margin-bottom:20px;
    }
    p.footer-links {
      text-align:center;
      margin-top:20px;
    }
    a {
      color:var(--accent);
      text-decoration:none;
      font-weight:600;
    }
    a:hover {
      text-decoration:underline;
    }
    @media(max-width:600px){
      .container { padding:30px 24px; }
    }
  </style>
</head>
<body>
  <video autoplay muted loop id="bg-video">
    <source src="assets/bg.mp4" type="video/mp4">
  </video>
  <div class="overlay"></div>

  <div class="container">
    <h2>Edit Shared Resume</h2>

    <?php if ($message): ?>
      <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label for="name">Name</label>
      <input id="name" type="text" name="name" value="<?= htmlspecialchars($resume['name'] ?? '') ?>">

      <label for="title">Title</label>
      <input id="title" type="text" name="title" value="<?= htmlspecialchars($resume['title'] ?? '') ?>">

      <label for="summary">Summary</label>
      <textarea id="summary" name="summary"><?= htmlspecialchars($resume['summary'] ?? '') ?></textarea>

      <label for="skills">Skills</label>
      <textarea id="skills" name="skills"><?= htmlspecialchars($resume['skills'] ?? '') ?></textarea>

      <label for="training">Training</label>
      <textarea id="training" name="training"><?= htmlspecialchars($resume['training'] ?? '') ?></textarea>

      <label for="education">Education</label>
      <textarea id="education" name="education"><?= htmlspecialchars($resume['education'] ?? '') ?></textarea>

      <label for="organization">Organization</label>
      <textarea id="organization" name="organization"><?= htmlspecialchars($resume['organization'] ?? '') ?></textarea>

      <button type="submit">Save Changes</button>
    </form>

    <p class="footer-links">
      <a href="public_resume.php?id=1">View Public Resume</a> |
      <a href="logout.php">Logout</a>
    </p>
  </div>
</body>
</html>
