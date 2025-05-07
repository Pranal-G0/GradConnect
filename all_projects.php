<?php 
include 'admin/db_connect.php'; 
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Projects</title>
    <style>
    body {
            font-family: sans-serif;
            background: #f8f9fa;
            padding: 50px;
            background-color:rgb(30, 28, 28);
        }

    .filter-bar {
      padding: 70px;
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
            margin-bottom: 20px;
            color:rgb(255, 255, 255);
            border-bottom: 3px solid #007bff;
            display: inline-block;
            padding-bottom: 5px;
            f
        }

    .project-section {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        
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

    <!-- <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 20px;
        }
        .project-section {
            margin-bottom: 50px;
        }
        .section-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #343a40;
            border-bottom: 3px solid #007bff;
            display: inline-block;
            padding-bottom: 5px;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .card h4 {
            margin: 0 0 10px;
            color: #007bff;

        }
        .card p {
            margin: 5px 0;
        }
        .project-link a {
            color: #28a745;
            text-decoration: none;
        }
        .project-link a:hover {
            text-decoration: underline;
        }
        .by-line {
            font-style: italic;
            color: #6c757d;
        }
        .filter-bar {
            margin-bottom: 20px;
        }
        .filter-bar select, .filter-bar input {
            padding: 10px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
    </style> -->
</head>
<body>

    <!-- Search and Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="index.php">
          <input type="hidden" name="page" value="all_projects">
            <input type="text" name="search" placeholder="Search projects..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
            <select name="type">
                <option value="all" <?php echo (!isset($_GET['type']) || $_GET['type'] == 'all') ? 'selected' : ''; ?>>All Projects</option>
                <option value="student" <?php echo (isset($_GET['type']) && $_GET['type'] == 'student') ? 'selected' : ''; ?>>Student Projects</option>
                <option value="alumni" <?php echo (isset($_GET['type']) && $_GET['type'] == 'alumni') ? 'selected' : ''; ?>>Alumni Projects</option>
            </select>
            <button type="submit" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px;">Search</button>
        </form>
    </div>

    <?php
    // Prepare search and filter conditions
    
        // Prepare search and filter conditions
        $search_query = '';
        $type = isset($_GET['type']) ? $_GET['type'] : 'all';

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $conn->real_escape_string($_GET['search']);
            $search_query = " AND (p.project_title LIKE '%$search%' OR p.description LIKE '%$search%')";
        }

        if ($type === 'student') {
            $sql = "SELECT p.*, CONCAT(s.firstname, ' ', s.middlename, ' ', s.lastname) AS name, 'student' AS type
                    FROM student_projects p
                    JOIN student_bio s ON s.id = p.student_id
                    WHERE 1=1 $search_query";
        } elseif ($type === 'alumni') {
            $sql = "SELECT p.*, CONCAT(a.firstname, ' ', a.middlename, ' ', a.lastname) AS name, 'alumni' AS type
                    FROM alumni_projects p
                    JOIN alumnus_bio a ON a.id = p.alumni_id
                    WHERE 1=1 $search_query";
        } else {
            // Use subquery with alias for UNION
            $sql = "
                SELECT * FROM (
                    (SELECT p.*, CONCAT(s.firstname, ' ', s.middlename, ' ', s.lastname) AS name, 'student' AS type
                    FROM student_projects p
                    JOIN student_bio s ON s.id = p.student_id)
                    UNION
                    (SELECT p.*, CONCAT(a.firstname, ' ', a.middlename, ' ', a.lastname) AS name, 'alumni' AS type
                    FROM alumni_projects p
                    JOIN alumnus_bio a ON a.id = p.alumni_id)
                ) AS all_projects
                WHERE 1=1";

            if (!empty($search_query)) {
                // Adjust search query because alias `p` doesn't exist in outer scope
                $search = $conn->real_escape_string($_GET['search']);
                $sql .= " AND (project_title LIKE '%$search%' OR description LIKE '%$search%')";
            }
        }

    // Execute query
    $projects = $conn->query($sql);
    ?>

    <!-- Display Projects -->
    <div class="project-section">
        <div class="section-title">All Projects</div>
        <?php if($projects->num_rows > 0): ?>
            <?php while($row = $projects->fetch_assoc()): ?>
            <div class="card">
                <h4><?php echo htmlspecialchars($row['project_title']); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                <?php if(!empty($row['link'])): ?>
                <p class="project-link"><strong>Link:</strong> <a href="<?php echo $row['link']; ?>" target="_blank"><?php echo $row['link']; ?></a></p>
                <?php endif; ?>
                <p class="by-line">By <?php echo ucfirst($row['type']); ?>: <?php echo ucwords($row['name']); ?></p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No projects found matching your criteria.</p>
        <?php endif; ?>
    </div>

</body>
</html>
