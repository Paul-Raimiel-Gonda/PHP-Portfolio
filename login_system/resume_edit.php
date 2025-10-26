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
    die("Resume (id=1) not found. Please ensure a row with id=1 exists in the resumes table.");
}


function decode_or_lines($value, $default = []) {
    if ($value === null || $value === '') return $default;
    $d = json_decode($value, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($d)) return $d;
  
    $lines = array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $value)));
    return $lines ?: $default;
}


$skills = decode_or_lines($resume['skills'], []);
$achievements = decode_or_lines($resume['achievements'], []);
$experience = decode_or_lines($resume['professional_experience'], []);
$organizations = decode_or_lines($resume['organization'], []);
$education = decode_or_lines($resume['education'], []);
$additional_info = decode_or_lines($resume['additional_info'], []);



if (!empty($achievements) && is_array($achievements) && isset($achievements[0]) && is_string($achievements[0])) {
 
    $achievements = array_map(function($t){ return ['title'=> $t, 'description'=> '']; }, $achievements);
}
if (!empty($experience) && is_array($experience) && isset($experience[0]) && is_string($experience[0])) {
    $experience = array_map(function($t){ return ['role'=> '', 'details'=> $t]; }, $experience);
}
if (!empty($organizations) && is_array($organizations) && isset($organizations[0]) && is_string($organizations[0])) {
    $organizations = array_map(function($t){ return ['name'=> $t, 'position'=>'','year'=>'']; }, $organizations);
}
if (!empty($education) && is_array($education) && isset($education[0]) && is_string($education[0])) {
    $education = array_map(function($t){ return ['degree'=> $t, 'school'=>'', 'time'=>'']; }, $education);
}
if (!empty($additional_info) && is_array($additional_info) && isset($additional_info[0]) && is_string($additional_info[0])) {
    $additional_info = array_map(function($t){ return ['title'=>'', 'content'=>$t]; }, $additional_info);
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $training = trim($_POST['training'] ?? '');

  
    $post_skills = array_values(array_filter(array_map('trim', (array)($_POST['skills'] ?? []))));
    // Achievements: arrays of title/description
    $post_ach = [];
    $ach_titles = $_POST['achievements_title'] ?? [];
    $ach_descs = $_POST['achievements_desc'] ?? [];
    for ($i=0; $i < max(count($ach_titles), count($ach_descs)); $i++) {
        $t = trim($ach_titles[$i] ?? '');
        $d = trim($ach_descs[$i] ?? '');
        if ($t !== '' || $d !== '') $post_ach[] = ['title'=>$t, 'description'=>$d];
    }

    // Experience
    $post_exp = [];
    $exp_roles = $_POST['exp_role'] ?? [];
    $exp_details = $_POST['exp_details'] ?? [];
    for ($i=0; $i < max(count($exp_roles), count($exp_details)); $i++) {
        $r = trim($exp_roles[$i] ?? '');
        $d = trim($exp_details[$i] ?? '');
        if ($r !== '' || $d !== '') $post_exp[] = ['role'=>$r, 'details'=>$d];
    }

    // Organizations
    $post_orgs = [];
    $org_names = $_POST['org_name'] ?? [];
    $org_pos = $_POST['org_position'] ?? [];
    $org_year = $_POST['org_year'] ?? [];
    for ($i=0; $i < max(count($org_names), count($org_pos), count($org_year)); $i++) {
        $n = trim($org_names[$i] ?? '');
        $p = trim($org_pos[$i] ?? '');
        $y = trim($org_year[$i] ?? '');
        if ($n !== '' || $p !== '' || $y !== '') $post_orgs[] = ['name'=>$n,'position'=>$p,'year'=>$y];
    }

    // Education
    $post_edu = [];
    $edu_degree = $_POST['edu_degree'] ?? [];
    $edu_school = $_POST['edu_school'] ?? [];
    $edu_time = $_POST['edu_time'] ?? [];
    for ($i=0; $i < max(count($edu_degree), count($edu_school), count($edu_time)); $i++) {
        $deg = trim($edu_degree[$i] ?? '');
        $sch = trim($edu_school[$i] ?? '');
        $tm = trim($edu_time[$i] ?? '');
        if ($deg !== '' || $sch !== '' || $tm !== '') $post_edu[] = ['degree'=>$deg,'school'=>$sch,'time'=>$tm];
    }

    // Additional info (title + content)
    $post_add = [];
    $add_title = $_POST['add_title'] ?? [];
    $add_content = $_POST['add_content'] ?? [];
    for ($i=0; $i < max(count($add_title), count($add_content)); $i++) {
        $t = trim($add_title[$i] ?? '');
        $c = trim($add_content[$i] ?? '');
        if ($t !== '' || $c !== '') $post_add[] = ['title'=>$t,'content'=>$c];
    }

  
    $updates = [];
    $params = [];
  
    $updates[] = "name = :name"; $params['name']=$name;
    $updates[] = "title = :title"; $params['title']=$title;
    $updates[] = "summary = :summary"; $params['summary']=$summary;
    $updates[] = "training = :training"; $params['training']=$training;

    $updates[] = "skills = :skills"; $params['skills'] = json_encode($post_skills, JSON_UNESCAPED_UNICODE);
    $updates[] = "achievements = :achievements"; $params['achievements'] = json_encode($post_ach, JSON_UNESCAPED_UNICODE);
    $updates[] = "professional_experience = :professional_experience"; $params['professional_experience'] = json_encode($post_exp, JSON_UNESCAPED_UNICODE);
    $updates[] = "organization = :organization"; $params['organization'] = json_encode($post_orgs, JSON_UNESCAPED_UNICODE);
    $updates[] = "education = :education"; $params['education'] = json_encode($post_edu, JSON_UNESCAPED_UNICODE);
    $updates[] = "additional_info = :additional_info"; $params['additional_info'] = json_encode($post_add, JSON_UNESCAPED_UNICODE);

    $params['id'] = $resume_id;
    $sql = "UPDATE resumes SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);


    $stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = :id");
    $stmt->execute(['id' => $resume_id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);

 
    $skills = decode_or_lines($resume['skills'], []);
    $achievements = decode_or_lines($resume['achievements'], []);
    $experience = decode_or_lines($resume['professional_experience'], []);
    $organizations = decode_or_lines($resume['organization'], []);
    $education = decode_or_lines($resume['education'], []);
    $additional_info = decode_or_lines($resume['additional_info'], []);

   
    if (!empty($achievements) && is_array($achievements) && isset($achievements[0]) && is_string($achievements[0])) {
        $achievements = array_map(function($t){ return ['title'=> $t, 'description'=> '']; }, $achievements);
    }
    if (!empty($experience) && is_array($experience) && isset($experience[0]) && is_string($experience[0])) {
        $experience = array_map(function($t){ return ['role'=> '', 'details'=> $t]; }, $experience);
    }
    if (!empty($organizations) && is_array($organizations) && isset($organizations[0]) && is_string($organizations[0])) {
        $organizations = array_map(function($t){ return ['name'=> $t, 'position'=>'','year'=>'']; }, $organizations);
    }
    if (!empty($education) && is_array($education) && isset($education[0]) && is_string($education[0])) {
        $education = array_map(function($t){ return ['degree'=> $t, 'school'=>'', 'time'=>'']; }, $education);
    }
    if (!empty($additional_info) && is_array($additional_info) && isset($additional_info[0]) && is_string($additional_info[0])) {
        $additional_info = array_map(function($t){ return ['title'=>'', 'content'=>$t]; }, $additional_info);
    }

    $message = "The resume has been updated successfully!";
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
    :root { --accent:#0ea5ff; --accent-2:#0066cc; }
    html,body { height:100%; margin:0; font-family:'Poppins',sans-serif; color:#fff; background:transparent; overflow-x:hidden; }
    #bg-video { position:fixed; inset:0; width:100%; height:100%; object-fit:cover; z-index:-3; }
    .overlay { position:fixed; inset:0; background:linear-gradient(180deg, rgba(0,0,0,0.35), rgba(0,0,0,0.75)); z-index:-2; }

    .container {
      width:100%; max-width:900px; margin:60px auto;
      background:rgba(255,255,255,0.06); border-radius:12px;
      padding:28px; backdrop-filter:blur(10px); box-shadow:0 20px 40px rgba(0,0,0,0.6);
    }

    h2 { text-align:center; margin-top:0; margin-bottom:14px; font-weight:700; font-size:24px; color:#fff; }
    label { display:block; margin-bottom:6px; font-weight:600; color:#ddd; font-size:13px; }
    input, textarea {
      width:100%; padding:10px; margin-bottom:14px; border:none; border-radius:8px;
      background:#fff; font-size:14px; color:#111; box-sizing:border-box;
    }
    textarea { resize:vertical; min-height:70px; }

    .row { display:flex; gap:16px; align-items:flex-start; }
    .col { flex:1; }

    .section-block { margin-bottom:18px; }
    .items { margin-top:8px; }

    .item {
      background: rgba(255,255,255,0.95);
      padding:12px; border-radius:8px; margin-bottom:10px; color:#111; position:relative;
    }
    .item .remove {
      position:absolute; right:10px; top:10px; background:#ff6b6b; color:#fff; border:none; padding:6px 8px; border-radius:6px; cursor:pointer;
    }

    .btn-inline { display:inline-block; padding:8px 12px; border-radius:8px; border:none; cursor:pointer; font-weight:700 }
    .add { background: linear-gradient(90deg,#04c2c9,#0ea5ff); color:#fff; margin-bottom:12px; }
    .save { background:linear-gradient(90deg,var(--accent-2),var(--accent)); color:#fff; width:100%; padding:12px; border-radius:8px; border:none; font-weight:700; cursor:pointer; }

    .msg { text-align:center; background:rgba(0,255,153,0.12); border:1px solid rgba(0,255,153,0.25); color:#7ef5b8; padding:10px; border-radius:8px; margin-bottom:16px; }

    .footer-links { text-align:center; margin-top:16px; color:#ddd; }
    a { color:var(--accent); text-decoration:none; font-weight:600; }

    @media(max-width:760px){
      .row { flex-direction:column; }
      .container { margin:30px 12px; padding:20px; }
    }
  </style>
</head>
<body>
  <video autoplay muted loop id="bg-video"><source src="assets/bg.mp4" type="video/mp4"></video>
  <div class="overlay"></div>

  <div class="container">
    <h2>Edit Resume</h2>

    <?php if ($message): ?><div class="msg"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST" id="resumeForm">
      <div class="section-block">
        <label for="name">Full Name</label>
        <input id="name" name="name" value="<?= htmlspecialchars($resume['name'] ?? '') ?>">
      </div>

      <div class="section-block">
        <label for="title">Title / Headline</label>
        <input id="title" name="title" value="<?= htmlspecialchars($resume['title'] ?? '') ?>">
      </div>

      <div class="section-block">
        <label for="summary">Summary</label>
        <textarea id="summary" name="summary"><?= htmlspecialchars($resume['summary'] ?? '') ?></textarea>
      </div>

      <!-- SKILLS -->
      <div class="section-block">
        <label>Skills</label>
        <button type="button" class="btn-inline add" id="addSkill">+ Add Skill</button>
        <div id="skillsList" class="items">
          <?php foreach ($skills as $i => $s): ?>
            <div class="item">
              <button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
              <input name="skills[]" value="<?= htmlspecialchars(is_scalar($s) ? $s : ($s['name'] ?? '')) ?>" placeholder="Skill name">
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ACHIEVEMENTS -->
      <div class="section-block">
        <label>Achievements</label>
        <button type="button" class="btn-inline add" id="addAchievement">+ Add Achievement</button>
        <div id="achList" class="items">
          <?php foreach ($achievements as $i => $a): 
                $t = htmlspecialchars($a['title'] ?? '');
                $d = htmlspecialchars($a['description'] ?? '');
          ?>
            <div class="item">
              <button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
              <label>Title</label>
              <input name="achievements_title[]" value="<?= $t ?>">
              <label>Description</label>
              <textarea name="achievements_desc[]"><?= $d ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- EXPERIENCE -->
      <div class="section-block">
        <label>Trainings</label>
        <button type="button" class="btn-inline add" id="addExp">+ Add Experience</button>
        <div id="expList" class="items">
          <?php foreach ($experience as $i => $e):
                $r = htmlspecialchars($e['role'] ?? '');
                $d = htmlspecialchars($e['details'] ?? '');
          ?>
            <div class="item">
              <button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
              <label>Role / Title</label>
              <input name="exp_role[]" value="<?= $r ?>">
              <label>Details</label>
              <textarea name="exp_details[]"><?= $d ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ORGANIZATIONS -->
      <div class="section-block">
        <label>Organizations</label>
        <button type="button" class="btn-inline add" id="addOrg">+ Add Organization</button>
        <div id="orgList" class="items">
          <?php foreach ($organizations as $i => $o):
                $n = htmlspecialchars($o['name'] ?? '');
                $p = htmlspecialchars($o['position'] ?? '');
                $y = htmlspecialchars($o['year'] ?? '');
          ?>
            <div class="item">
              <button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
              <label>Name</label>
              <input name="org_name[]" value="<?= $n ?>">
              <div class="row">
                <div class="col">
                  <label>Position</label>
                  <input name="org_position[]" value="<?= $p ?>">
                </div>
                <div class="col">
                  <label>Year / Range</label>
                  <input name="org_year[]" value="<?= $y ?>">
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- EDUCATION -->
      <div class="section-block">
        <label>Education</label>
        <button type="button" class="btn-inline add" id="addEdu">+ Add Education</button>
        <div id="eduList" class="items">
          <?php foreach ($education as $i => $ed):
                $deg = htmlspecialchars($ed['degree'] ?? '');
                $sch = htmlspecialchars($ed['school'] ?? '');
                $tm = htmlspecialchars($ed['time'] ?? '');
          ?>
            <div class="item">
              <button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
              <label>Degree / Program</label>
              <input name="edu_degree[]" value="<?= $deg ?>">
              <label>School / Institution</label>
              <input name="edu_school[]" value="<?= $sch ?>">
              <label>Year / Range</label>
              <input name="edu_time[]" value="<?= $tm ?>">
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ADDITIONAL INFO -->
      <div class="section-block">
        <label>Additional Info</label>
        <button type="button" class="btn-inline add" id="addAdd">+ Add Additional Info</button>
        <div id="addList" class="items">
          <?php foreach ($additional_info as $i => $ad):
                 $at = htmlspecialchars($ad['title'] ?? '');
                 $ac = htmlspecialchars($ad['content'] ?? '');
          ?>
            <div class="item">
              <button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
              <label>Title</label>
              <input name="add_title[]" value="<?= $at ?>">
              <label>Content</label>
              <textarea name="add_content[]"><?= $ac ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

  
      <button type="submit" class="save">Save Changes</button>
    </form>

    <div class="footer-links">
      <a href="public_resume.php?id=1">View Public Resume</a> |
      <a href="logout.php">Logout</a>
    </div>
  </div>

<script>
 
  function createSkill(value='') {
    const div = document.createElement('div'); div.className='item';
    div.innerHTML = `<button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
      <input name="skills[]" value="${value.replace(/"/g,'&quot;')}">`;
    return div;
  }
  function createAchievement(title='', desc='') {
    const div = document.createElement('div'); div.className='item';
    div.innerHTML = `<button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
      <label>Title</label><input name="achievements_title[]" value="${title.replace(/"/g,'&quot;')}">
      <label>Description</label><textarea name="achievements_desc[]">${desc}</textarea>`;
    return div;
  }
  function createExp(role='', details='') {
    const div = document.createElement('div'); div.className='item';
    div.innerHTML = `<button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
      <label>Role / Title</label><input name="exp_role[]" value="${role.replace(/"/g,'&quot;')}">
      <label>Details</label><textarea name="exp_details[]">${details}</textarea>`;
    return div;
  }
  function createOrg(name='', position='', year='') {
    const div = document.createElement('div'); div.className='item';
    div.innerHTML = `<button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
      <label>Name</label><input name="org_name[]" value="${name.replace(/"/g,'&quot;')}">
      <div class="row"><div class="col"><label>Position</label><input name="org_position[]" value="${position.replace(/"/g,'&quot;')}"></div>
      <div class="col"><label>Year / Range</label><input name="org_year[]" value="${year.replace(/"/g,'&quot;')}"></div></div>`;
    return div;
  }
  function createEdu(deg='', school='', time='') {
    const div = document.createElement('div'); div.className='item';
    div.innerHTML = `<button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
      <label>Degree / Program</label><input name="edu_degree[]" value="${deg.replace(/"/g,'&quot;')}">
      <label>School / Institution</label><input name="edu_school[]" value="${school.replace(/"/g,'&quot;')}">
      <label>Year / Range</label><input name="edu_time[]" value="${time.replace(/"/g,'&quot;')}">`;
    return div;
  }
  function createAdd(title='', content='') {
    const div = document.createElement('div'); div.className='item';
    div.innerHTML = `<button type="button" class="remove" onclick="this.parentElement.remove()">Remove</button>
      <label>Title</label><input name="add_title[]" value="${title.replace(/"/g,'&quot;')}">
      <label>Content</label><textarea name="add_content[]">${content}</textarea>`;
    return div;
  }

  document.getElementById('addSkill').addEventListener('click', ()=> {
    document.getElementById('skillsList').appendChild(createSkill(''));
  });
  document.getElementById('addAchievement').addEventListener('click', ()=> {
    document.getElementById('achList').appendChild(createAchievement('',''));
  });
  document.getElementById('addExp').addEventListener('click', ()=> {
    document.getElementById('expList').appendChild(createExp('',''));
  });
  document.getElementById('addOrg').addEventListener('click', ()=> {
    document.getElementById('orgList').appendChild(createOrg('','',''));
  });
  document.getElementById('addEdu').addEventListener('click', ()=> {
    document.getElementById('eduList').appendChild(createEdu('','',''));
  });
  document.getElementById('addAdd').addEventListener('click', ()=> {
    document.getElementById('addList').appendChild(createAdd('',''));
  });
</script>
</body>
</html>
