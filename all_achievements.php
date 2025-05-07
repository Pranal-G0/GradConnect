<?php 
include 'admin/db_connect.php'; 
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Achievements</title>
    <style>
        body {
            font-family: sans-serif;
            background-color:rgb(30, 28, 28);
            padding: 80px;
            margin: 10px;
        }

        .filter-bar {
            padding: 20px;
            margin-bottom: 30px;
        }

        .filter-bar form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .filter-bar input[type="text"],
        .filter-bar select {
            padding: 10px;
            border: 1px solid #d1d5da;
            border-radius: 6px;
            font-size: 14px;
            width: 200px;
        }

        .filter-bar button {
            padding: 10px 20px;
            background-color: #2da44e;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        .filter-bar button:hover {
            background-color: #218739;
        }

        .section-title {
            font-size: 28px;
            font-weight: bold;
            margin: 30px 0 20px;
            color:rgb(255,255,255) ;
            border-bottom: 3px solid #007bff;
            display: inline-block;
            padding-bottom: 5px;
        }

        .achievement-section {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            
            background-color: ##343a40;
        }

        .card {
            background-color: #ffffff;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(27,31,35,.04), 0 1px 2px rgba(27,31,35,.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(27, 31, 35, 0.15);
        }

        .card h4 {
            font-size: 18px;
            color: #0969da;
            margin: 0 0 10px;
        }

        .card p {
            font-size: 14px;
            color: #57606a;
            margin: 5px 0;
        }

        .project-link a {
            color: #1f883d;
            text-decoration: none;
            font-weight: 500;
        }

        .project-link a:hover {
            text-decoration: underline;
        }

        .by-line {
            margin-top: 10px;
            font-size: 13px;
            color: #6a737d;
            background: #eaeef2;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
            font-style: normal;
        }
    </style>
</head>
<body>

<!-- Search and Filter Bar -->
<div class="filter-bar">
    <form method="GET" action="index.php">
      <input type="hidden" name="page" value="all_achievements">
        <input type="text" name="search" placeholder="Search achievements..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
        <!-- <select name="type">
            <option value="all" <?php echo (!isset($_GET['type']) || $_GET['type'] == 'all') ? 'selected' : ''; ?>>All</option>
            <option value="student" <?php echo (isset($_GET['type']) && $_GET['type'] == 'student') ? 'selected' : ''; ?>>Student</option>
            <option value="alumni" <?php echo (isset($_GET['type']) && $_GET['type'] == 'alumni') ? 'selected' : ''; ?>>Alumni</option>
        </select> -->
        <button type="submit">Search</button>
    </form>
</div>

<?php
$search_query = '';
$type = $_GET['type'] ?? 'all';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $search_query = " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}


// Get Achievements
$achievement_query = '';
if (!empty($search)) {
    $achievement_query = " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}

// if ($type === 'student') {
//     $achievement_sql = "SELECT a.*, CONCAT(s.firstname, ' ', s.middlename, ' ', s.lastname) AS name, 'student' AS type
//                         FROM student_achievements a
//                         JOIN student_bio s ON s.id = a.student_id
//                         WHERE 1=1 $achievement_query";
// } elseif ($type === 'alumni') {
//     $achievement_sql = "SELECT a.*, CONCAT(al.firstname, ' ', al.middlename, ' ', al.lastname) AS name, 'alumni' AS type
//                         FROM alumni_achievements a
//                         JOIN alumnus_bio al ON al.id = a.alumni_id
//                         WHERE 1=1 $achievement_query";
// }
 else {
    $achievement_sql = "
        SELECT * FROM (
            (SELECT a.*, CONCAT(s.firstname, ' ', s.middlename, ' ', s.lastname) AS name, 'student' AS type
            FROM student_achievements a
            JOIN student_bio s ON s.id = a.student_id)
            UNION
            (SELECT a.*, CONCAT(al.firstname, ' ', al.middlename, ' ', al.lastname) AS name, 'alumni' AS type
            FROM alumni_achievements a
            JOIN alumnus_bio al ON al.id = a.alumni_id)
        ) AS all_achievements
        WHERE 1=1";

    if (!empty($search)) {
        $achievement_sql .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
    }
}
$achievements = $conn->query($achievement_sql);
?>


<!-- Achievements Section -->
<div class="section-title">Achievements</div>
<div class="achievement-section">
<?php if ($achievements->num_rows > 0): ?>
    <?php while($row = $achievements->fetch_assoc()): ?>
        <div class="card">
            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
            <p class="by-line">By <?php echo ucfirst($row['type']); ?>: <?php echo ucwords($row['name']); ?></p>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No achievements found matching your criteria.</p>
<?php endif; ?>
</div>

</body>
</html>
