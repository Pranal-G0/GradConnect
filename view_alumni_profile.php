<?php
include 'admin/db_connect.php';
$alumni_id = $_GET['id'];
$alumni = $conn->query("SELECT *, CONCAT(firstname, ' ', middlename, ' ', lastname) as full_name FROM alumnus_bio WHERE id = $alumni_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $alumni['full_name'] ?>'s Alumni Profile</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f4f9;
      margin: 0;
      padding: 20px;
      color: #333;
    }

    .profile-container {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 30px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      border-radius: 10px;
    }

    h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 10px;
    }

    h4 {
      margin-top: 30px;
      color: #2980b9;
      border-bottom: 2px solid #ddd;
      padding-bottom: 5px;
    }

    p, li {
      line-height: 1.6;
      font-size: 16px;
    }

    ul {
      list-style: none;
      padding-left: 0;
    }

    ul li {
      background: #f1f1f1;
      margin-bottom: 10px;
      padding: 10px 15px;
      border-left: 4px solid #2980b9;
      border-radius: 5px;
    }

    a {
      color: #3498db;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    hr {
      margin: 40px 0;
      border: 0;
      height: 1px;
      background: #ccc;
    }

    .section {
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="profile-container">
    <h2><?php echo $alumni['full_name'] ?>'s Alumni Profile</h2>

  <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo $alumni['email'] ?>
" class="btn btn-info btn-sm">Message</a>

    <div class="section">
      <h4>Personal Info</h4>
      <p><strong>Email:</strong> <?php echo $alumni['email'] ?></p>
      <p><strong>Name:</strong> <?php echo $alumni['full_name'] ?></p>
      <p><strong>Passout Year:</strong> <?php echo $alumni['batch'] ?></p>
    </div>

    <div class="section">
      <h4>Education Timeline</h4>
      <ul>
        <?php
        $edu = $conn->query("SELECT * FROM alumni_education WHERE alumni_id = $alumni_id ORDER BY start_year DESC");
        while($row = $edu->fetch_assoc()):
        ?>
        <li>
          <strong><?php echo $row['degree'].' in '.$row['field_of_study'] ?></strong><br>
          <?php echo $row['institution'] ?> (<?php echo $row['start_year'].' - '.$row['end_year'] ?>)<br>
          Score: <?php echo $row['score'] ?>
        </li>
        <?php endwhile; ?>
      </ul>
    </div>

    <div class="section">
      <h4>Experience Timeline</h4>
      <ul>
        <?php
        $exp = $conn->query("SELECT * FROM alumni_experience WHERE alumni_id = $alumni_id ORDER BY start_year DESC");
        while($row = $exp->fetch_assoc()):
        ?>
        <li>
          <strong><?php echo $row['position'] ?></strong> at <strong><?php echo $row['company'] ?></strong><br>
          <?php echo $row['start_year'].' - '.$row['end_year'] ?><br>
          <?php echo $row['description'] ?>
        </li>
        <?php endwhile; ?>
      </ul>
    </div>

    <div class="section">
      <h4>Skills & Certifications</h4>
      <ul>
        <?php
        $skills = $conn->query("SELECT * FROM alumni_skills WHERE alumni_id = $alumni_id");
        while($row = $skills->fetch_assoc()):
        ?>
        <li>
          <strong>Skill:</strong> <?php echo $row['skill_name'] ?><br>
          <strong>Certification:</strong> <?php echo $row['certification_name'] ?>
        </li>
        <?php endwhile; ?>
      </ul>
    </div>

    <div class="section">
      <h4>Projects</h4>
      <ul>
        <?php
        $projects = $conn->query("SELECT * FROM alumni_projects WHERE alumni_id = $alumni_id");
        while($row = $projects->fetch_assoc()):
        ?>
        <li>
          <strong><?php echo $row['project_title'] ?></strong><br>
          <?php echo $row['description'] ?><br>
          <?php if($row['link']) echo "Link: <a href='{$row['link']}' target='_blank'>{$row['link']}</a>" ?>
        </li>
        <?php endwhile; ?>
      </ul>
    </div>

    <div class="section">
      <h4>Achievements</h4>
      <ul>
        <?php
        $achievements = $conn->query("SELECT * FROM alumni_achievements WHERE alumni_id = $alumni_id");
        while($row = $achievements->fetch_assoc()):
        ?>
        <li>
          <strong><?php echo $row['title'] ?></strong>: <?php echo $row['description'] ?>
        </li>
        <?php endwhile; ?>
      </ul>
    </div>

  </div>
</body>
</html>
