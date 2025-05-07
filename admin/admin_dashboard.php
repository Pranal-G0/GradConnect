<?php
include 'db_connect.php';

// Base condition to simplify AND logic
$where = "1=1";
$params = [];

// Filter: Main Category
if (!empty($_GET['main_category'])) {
    $main_categories = array_map('intval', $_GET['main_category']);
    $placeholders = implode(',', array_fill(0, count($main_categories), '?'));
    $where .= " AND main_category IN ($placeholders)";
    $params = array_merge($params, $main_categories);
}

// Filter: Year (join with users)
if (!empty($_GET['year'])) {
    $years = array_map('intval', $_GET['year']);
    $placeholders = implode(',', array_fill(0, count($years), '?'));
    $where .= " AND registrationId IN (SELECT registrationId FROM users WHERE year IN ($placeholders))";
    $params = array_merge($params, $years);
}

// Filter: Date Range
if (!empty($_GET['from_date']) && !empty($_GET['to_date'])) {
    $where .= " AND start_date >= ? AND end_date <= ?";
    $params[] = $_GET['from_date'];
    $params[] = $_GET['to_date'];
}

// Base SQL
$sql = "SELECT * FROM achievements WHERE $where";

// Filter: Search box
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $sql .= " AND (
        registrationId LIKE ? OR 
        title LIKE ? OR 
        description LIKE ? OR 
        main_category LIKE ? OR 
        sub_category LIKE ? OR 
        start_date LIKE ? OR 
        end_date LIKE ?
    )";
    for ($i = 0; $i < 7; $i++) {
        $params[] = $search;
    }
}

// Prepare & execute
$stmt = $conn->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// CSV Export
if (isset($_GET['download']) && $_GET['download'] === 'yes') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="achievements.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Registration ID', 'Main Category', 'Sub Category', 'Title', 'Description', 'Start Date', 'End Date']);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['registrationId'],
            $row['main_category'],
            $row['sub_category'],
            $row['title'],
            $row['description'],
            $row['start_date'],
            $row['end_date']
        ]);
    }
    fclose($output);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Achievements</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        h1 {
            margin: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        form {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            margin-right: 10px;
        }
        select, input[type="text"], input[type="date"], button {
            padding: 8px 12px;
            margin: 5px 0;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .alert {
            padding: 15px;
            background-color: #f44336;
            color: white;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle form submission using AJAX
            $('form').on('submit', function(e) {
                e.preventDefault(); // Prevent the form from reloading the page

                // Collect form data
                var formData = $(this).serialize();

                // Send the form data using AJAX
                $.ajax({
                    url: window.location.href,  // Submit to the current page
                    method: 'GET',
                    data: formData,
                    success: function(response) {
                        // Update the table content with the response
                        $('#achievements-table tbody').html($(response).find('#achievements-table tbody').html());
                    },
                    error: function() {
                        alert('Error filtering achievements. Please try again.');
                    }
                });
            });
        });
    </script>
</head>
<body>
    <header>
        <h1>Admin Dashboard - Achievements</h1>
    </header>

    <div class="container">


        <form method="GET" action="">

            <input type="text" name="search" placeholder="Search Registration Id or Keyword..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
            <button type="submit">Search</button><br>
        
        </form>
        <form method="GET" action="">
          

            <label>Main Categories:</label><br>
            <?php
            // Fetch distinct main categories
            $category_query = "SELECT DISTINCT main_category FROM achievements";
            $categories_result = $conn->query($category_query);

            while ($category = $categories_result->fetch_assoc()) {
                echo '<input type="checkbox" name="main_category[]" value="' . htmlspecialchars($category['main_category']) . '"';
                if (isset($_GET['main_category']) && in_array($category['main_category'], $_GET['main_category'])) {
                    echo ' checked';
                }
                echo '> ' . htmlspecialchars($category['main_category']) . '<br>';
            }
            ?>
            
            <label>Year:</label><br>
            <?php
            // Fetch year
            $year_query = "SELECT DISTINCT year FROM users ";
            $year_result = $conn->query($year_query);

            while ($year = $year_result->fetch_assoc()) {
                echo '<input type="checkbox" name="year[]" value="' . htmlspecialchars($year['year']) . '"';
                if (isset($_GET['year']) && in_array($year['year'], $_GET['year'])) {
                    echo ' checked';
                }
                echo '> ' . htmlspecialchars($year['year']) . '<br>';
            }
            ?>
           

                <label>From Date:</label>
                <input type="date" name="from_date" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>"><br>

                <label>To Date:</label>
                <input type="date" name="to_date" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>"><br>
                <button type="submit">Search</button><br>
        
          
 
            

         

            <!-- <button type="submit">Filter</button> -->
            <button type="submit" name="download" value="yes">Download CSV</button>
        </form>

        <table id="achievements-table">
            <thead>
                <tr>
                    <th>Registration ID</th>
                    <th>Main Category</th>
                    <th>Sub Category</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['registrationId']); ?></td>
                        <td><?php echo htmlspecialchars($row['main_category']); ?></td>
                        <td><?php echo htmlspecialchars($row['sub_category']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

