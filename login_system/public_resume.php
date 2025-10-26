<?php
session_start();
include('db.php');

$id = 1;
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = :id");
$stmt->execute(['id' => $id]);
$resume = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resume) {
  echo "<h2 style='text-align:center;margin-top:50px;color:red;'>Resume not found.</h2>";
  exit;
}

function toArray($value) {
    if (is_null($value) || $value === '') return [];
    if (is_array($value)) return $value;
    if (!is_string($value)) return [ (string)$value ];
    $decoded = json_decode($value, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded;
    }
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
        // For experience roles, make the heading bold
        if (strtolower($section) === 'experience') {
            $out .= "<h3 class='sub-head role'>{$heading}</h3>";
        } else {
            $out .= "<h3 class='sub-head'>{$heading}</h3>";
        }
    }

    if (strtolower($section) === 'experience') {
        foreach ($item as $k => $v) {
            if ($k === 'name' || $k === 'title') continue;
            $vtext = is_array($v) ? nl2br(htmlspecialchars(implode("\n", $v))) : nl2br(htmlspecialchars((string)$v));
            $out .= "<p class='sub-field'>{$vtext}</p>";
        }
        $out .= "</div>";
        return $out;
    }

    foreach ($item as $k => $v) {
        if ($k === 'name' || $k === 'title') continue;
        $vtext = is_array($v) ? nl2br(htmlspecialchars(implode("\n", $v))) : nl2br(htmlspecialchars((string)$v));
        $label = ucfirst(str_replace('_',' ', $k));
        $out .= "<p class='sub-field'><strong>{$label}:</strong> {$vtext}</p>";
    }

    $out .= "</div>";
    return $out;
}

function renderList($data, $title, $id = '') {
    $items = toArray($data);
    if (empty($items)) return;
    $idAttr = $id ? "id=\"{$id}\"" : "";

    echo "<section class='section' {$idAttr}><div class='container single-card-container'><div class='card'><h2>" . htmlspecialchars($title) . "</h2>";

    if (strtolower($title) === 'skills') {
        $flat = [];
        foreach ($items as $it) {
            if (is_array($it)) {
                foreach ($it as $sub) {
                    if (is_string($sub)) {
                        $parts = array_map('trim', explode(',', $sub));
                        foreach ($parts as $p) if ($p !== '') $flat[] = $p;
                    }
                }
            } else {
                $parts = array_map('trim', explode(',', (string)$it));
                foreach ($parts as $p) if ($p !== '') $flat[] = $p;
            }
        }
        if (!empty($flat)) {
            echo "<div class='skills-wrap'>";
            foreach ($flat as $skill) {
                $s = htmlspecialchars($skill);
                echo "<span class='skill-pill'>{$s}</span>";
            }
            echo "</div>";
        }
        echo "</div></div></section>";
        return;
    }

    foreach ($items as $item) {
        echo renderItem($item, $title);
    }

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
    .hero-section{text-align:center;padding:100px 20px;}
    .hero-section h1{font-size:40px;margin-bottom:10px;color:#0ea5ff;} /* Name in blue */
    .hero-section p{font-size:18px;max-width:700px;margin:0 auto 10px;}
    .section{padding:30px 20px;}
    .container{max-width:900px;margin:0 auto;}
    .card{background:rgba(255,255,255,0.05);padding:25px 30px;border-radius:14px;
          box-shadow:0 10px 40px rgba(0,0,0,0.4);margin-bottom:25px;}
    .card h2{text-align:left;margin-top:0;color:#0ea5ff;}
    .sub-card{background:rgba(255,255,255,0.03);margin-top:12px;padding:14px 16px;border-radius:10px;border:1px solid rgba(255,255,255,0.02);}
    .sub-card p{margin:0;line-height:1.5;color:#dfe7f3;}
    .sub-head{margin:0 0 8px 0;color:#fff;font-size:16px;}
    .sub-field{margin:6px 0;color:#cfdcec;}
    .role { font-weight:700; color:#fff; } /* Role bold only */

    /* Blue SKILLS pills */
    .skills-wrap { display:flex; flex-wrap:wrap; gap:12px; margin-top:14px; }
    .skill-pill {
      display:inline-block;
      padding:10px 18px;
      border-radius:999px;
      background:#0ea5ff; /* Blue skill containers */
      color:#fff;
      font-weight:600;
      box-shadow: 0 6px 18px rgba(0,170,255,0.2);
      white-space:nowrap;
    }

    footer{background:rgba(255,255,255,0.05);padding:10px 0;text-align:center;font-size:14px;margin-top:30px;}
    @media (max-width:700px) {
      .site-header { padding:12px 18px; flex-direction:column; gap:12px; align-items:flex-start; }
      .hero-section { padding:60px 12px; }
      .container { padding: 0 12px; }
      .skills-wrap { justify-content:flex-start; }
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
      <a href="#skills">Skills</a>
      <a href="#achievements">Achievements</a>
      <a href="#experience">Experience</a>
      <a href="#orgs">Organizations</a>
      <a href="#education">Education</a>
      <a href="#additional">Additional Info</a>
      <a href="resume_edit.php" style="background:#0ea5ff;color:#fff;padding:6px 12px;border-radius:6px;">Edit Resume</a>
    </nav>
  </header>

  <section class="hero-section">
    <h1><?= htmlspecialchars($resume['name'] ?? '') ?></h1>
    <p><strong><?= htmlspecialchars($resume['title'] ?? '') ?></strong></p>
    <p><?= nl2br(htmlspecialchars($resume['summary'] ?? '')) ?></p>
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
