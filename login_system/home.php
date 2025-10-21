<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

 
$name_first = "Paul Raimiel";
$name_last  = "Gonda";
$title      = "Computer Science Student";
$summary    = "A passionate and dedicated student with experience in video game and software development, computer graphics and programming. I enjoy building interactive experiences, optimizing assets, and working with teams to ship polished projects.";

$contacts = [
    "linkedin" => "https://www.linkedin.com/in/paul-raimiel-gonda-932490375/",
    "github"   => "https://github.com/Paul-Raimiel-Gonda",
    "email"    => "raigonda0987@gmail.com",
    "phone"    => "+63 977 709 3713"
];

$skills = ["C++", "Java", "Unity", "Blender", "SQL", "Git"];

$training = [
    [
        "event" => "UPLB Game Development Workshop and Game Jam",
        "place" => "Discord and Zoom",
        "time"  => "May 2025",
        "tasks" => [
            "Developed 2D and 3D gameplay features using Unity and C#.",
            "Created and optimized game assets such as sprites, textures, and 3D models.",
            "Collaborated with a small dev team using Agile methods and version control."
        ]
     ],
    [
         "event" => "MOVEin Campus: Blockchain Fundamentals and Applications Seminar",
    "place" => "Batangas State University - STEER HUB",
    "time"  => "September 2025",
    "tasks" => [
        "Explored fundamentals and practical applications of blockchain technology.",
        "Learned about smart contracts, decentralized apps (DApps), and cryptocurrency basics.",
        "Participated in hands-on activities such as deploying own nfts."
        ]
    ]
];

$education = [
    [
        "degree" => "Bachelor of Science in Computer Science",
        "school" => "Batangas State University TNEU - Alangilan Campus",
        "time"   => "2023 — Present"
    ]
];

$organizations = [
    [
        "name"     => "College of Informatics and Computing Sciences Student Council (CICS-SC)",
        "position" => "Technical Committee Member | Graphics",
        "year"     => "2025 — Present"
    ],
    [
        "name"     => "Association of Committed Computer Science Students (ACCESS)",
        "position" => "Technical Committee Member | Graphics",
        "year"     => "2025 — Present"
    ]
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title><?= htmlspecialchars($name_first . ' ' . $name_last) ?> — Portfolio</title>

  
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&family=Montserrat:wght@600;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

  <video autoplay muted loop id="bg-video" preload="auto">
    <source src="assets/bg.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>

 
  <div class="bg-overlay"></div>


  <header class="site-header">
    <div class="container header-inner">
      <div class="logo"><?= htmlspecialchars($name_first) ?> <span class="logo-accent"><?= htmlspecialchars($name_last) ?></span></div>
      <nav class="main-nav">
        <a href="#hero">Home</a>
        <a href="#about">About</a>
        <a href="#skills">Skills</a>
        <a href="#training">Training</a>
        <a href="#orgs">Organizations</a>
        <a href="#education">Education</a>
        <a class="logout" href="logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <section id="hero" class="hero-section">
    <div class="container hero-inner">
      <h1 class="hero-title">
        <span class="hero-name-regular"><?= htmlspecialchars($name_first) ?></span>
        <span class="hero-name-accent"><?= htmlspecialchars($name_last) ?></span>
      </h1>
      <p class="hero-subtitle"><?= htmlspecialchars($title) ?></p>
      <p class="hero-summary"><?= htmlspecialchars($summary) ?></p>
      <a class="cta-btn" href="#about">Explore Portfolio</a>
    </div>
  </section>


  <main>
    <section id="about" class="section">
      <div class="container grid">
        <div class="card about-card">
          <h2>About</h2>
          <p><?= htmlspecialchars($summary) ?></p>

          <h3>Contact</h3>
          <ul class="contact-list">
            <li><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($contacts['email']) ?>"><?= htmlspecialchars($contacts['email']) ?></a></li>
            <li><strong>Phone:</strong> <?= htmlspecialchars($contacts['phone']) ?></li>
            <li><strong>GitHub:</strong> <a href="<?= htmlspecialchars($contacts['github']) ?>" target="_blank" rel="noopener noreferrer">View GitHub</a></li>
            <li><strong>LinkedIn:</strong> <a href="<?= htmlspecialchars($contacts['linkedin']) ?>" target="_blank" rel="noopener noreferrer">View LinkedIn</a></li>
          </ul>
        </div>

        <div class="card skills-card">
          <h2>Skills</h2>
          <div class="skills-grid">
            <?php foreach ($skills as $s): ?>
              <div class="skill-item"><?= htmlspecialchars($s) ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

  
    <section id="training" class="section">
      <div class="container">
        <h2>Seminars & Training</h2>
        <div class="cards">
          <?php foreach ($training as $t): ?>
            <article class="card">
              <h3><?= htmlspecialchars($t['event']) ?></h3>
              <p class="muted"><em><?= htmlspecialchars($t['place']) ?> · <?= htmlspecialchars($t['time']) ?></em></p>
              <ul>
                <?php foreach ($t['tasks'] as $task): ?>
                  <li><?= htmlspecialchars($task) ?></li>
                <?php endforeach; ?>
              </ul>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section id="orgs" class="section">
      <div class="container">
        <h2>Organizations</h2>
        <div class="cards grid-2">
          <?php foreach ($organizations as $org): ?>
            <article class="card">
              <h3><?= htmlspecialchars($org['name']) ?></h3>
              <p class="muted"><?= htmlspecialchars($org['position']) ?></p>
              <p class="muted"><?= htmlspecialchars($org['year']) ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

  
    <section id="education" class="section">
      <div class="container">
        <h2>Education</h2>
        <?php foreach ($education as $edu): ?>
          <article class="card">
            <h3><?= htmlspecialchars($edu['degree']) ?></h3>
            <p class="muted"><?= htmlspecialchars($edu['school']) ?> · <?= htmlspecialchars($edu['time']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-inner">
      <div>© <?= date("Y") ?> <?= htmlspecialchars($name_first . ' ' . $name_last) ?></div>
      <div class="footer-links">
        <a href="<?= htmlspecialchars($contacts['github']) ?>" target="_blank" rel="noopener">GitHub</a>
        <a href="<?= htmlspecialchars($contacts['linkedin']) ?>" target="_blank" rel="noopener">LinkedIn</a>
        <a href="mailto:<?= htmlspecialchars($contacts['email']) ?>"><?= htmlspecialchars($contacts['email']) ?></a>
      </div>
    </div>
  </footer>

  <script>
    document.querySelectorAll('a[href^="#"]').forEach(link=>{
      link.addEventListener('click', e=>{
        e.preventDefault();
        const target = document.querySelector(link.getAttribute('href'));
        if(!target) return;
        const y = target.getBoundingClientRect().top + window.scrollY - 80; 
        window.scrollTo({ top: y, behavior: 'smooth' });
      });
    });
  </script>
</body>
</html>
