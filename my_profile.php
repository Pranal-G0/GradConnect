<?php
// my_profile.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'admin/db_connect.php'; // your db connection file

if(!isset($_SESSION['login_id'])){
    header("Location: login.php");
    exit;
}

$registrationId = $_SESSION['registrationId'] ?? '';

// Query to fetch personal info based on the student's registration ID
$query = "SELECT * FROM achievements WHERE registrationId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $registrationId);
$stmt->execute();
$result = $stmt->get_result();

$achievements = [];
while ($row = $result->fetch_assoc()) {
    $achievements[] = $row;
}
// Query to fetch achievements based on the student's registration ID
$query = "SELECT name , username FROM users WHERE registrationId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $registrationId);
$stmt->execute();
$result = $stmt->get_result();

$personalinfo = [];
while ($row = $result->fetch_assoc()) {
    $personalinfo[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <style>
        /* Reset some default margins */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f8;
            color: #333;
            padding-top: 60px;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 80px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px 20px;
            margin: 2px;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            transition: background 0.3s;
            background: rgba(255, 255, 255, 0.7);
            color: rgb(60, 56, 73);
            border-radius: 16px;
        }

        .sidebar a:hover {
            background: #34495e;
            color: white;
        }

        .main {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        .main h1 {
            font-size: 32px;
            margin-bottom: 20px;
            color: rgb(255, 255, 255);
        }

        .main p {
            font-size: 18px;
            background: #ffffff;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.05);
            width: fit-content;
        }

        /* Achievements Section */
        .achievements {
            margin-top: 20px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.05);
        }

        .achievements h3 {
            margin-bottom: 15px;
            font-size: 24px;
        }

        .achievement-item {
            padding: 15px;
            background-color: #ecf0f1;
            border-radius: 8px;
            box-shadow: 0px 0px 5px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }

        .achievement-item strong {
            font-weight: bold;
        }

        /* Grid Layout for Achievements */
        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .personalInfo {
            margin-top: 20px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.05);
        }
        /* Optional: Responsive sidebar collapse for mobile */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main {
                margin-left: 200px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>My Profile</h2>
    <a class="sidebar-items" href="index.php?page=my_profile">Dashboard</a>
    <a class="sidebar-items" href="index.php?page=add_achievement">Add Achievements</a>
</div>

<div class="main">
    <h1> <?php echo "Welcome back " . $_SESSION['login_name'] . "!" ?></h1>
    <p>Registration ID: <strong><?php echo htmlspecialchars($registrationId); ?></strong></p>

        <!-- personal info Section -->
     <div class="personalInfo">
        <h3>Personal Information</h3>

        <?php if (count($personalinfo) > 0): ?>
           
                <?php foreach ($personalinfo as $personalinfoitem): ?>
                    <div class="personalinfo-item">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($personalinfoitem['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($personalinfoitem['username']); ?></p>
                        
                    </div>
                <?php endforeach; ?>
            
        <?php else: ?>
            <p>You have not added any achievements yet.</p>
        <?php endif; ?>
    </div>


    <!-- Achievements Section -->
    <div class="achievements">
        <h3>Your Achievements</h3>

        <?php if (count($achievements) > 0): ?>
            <div class="achievements-grid">
                <?php foreach ($achievements as $achievement): ?>
                    <div class="achievement-item">
                        <p><strong>Title:</strong> <?php echo htmlspecialchars($achievement['title']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($achievement['description']); ?></p>
                        <p><strong>From </strong> <?php echo htmlspecialchars($achievement['start_date']); ?> <strong>To </strong> <?php echo htmlspecialchars($achievement['end_date']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($achievement['status']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($achievement['location']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>You have not added any achievements yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
