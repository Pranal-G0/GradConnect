<?php
include 'db_connect.php';



// Handle selected columns
$selected_columns = isset($_GET['selected_columns']) ? $_GET['selected_columns'] : [];

$columns_map = [
    'registrationId' => 'a.registrationId',
    'name' => 'u.name',
    'year' => 'u.year',
    'main_category' => 'a.main_category',
    'sub_category' => 'a.sub_category',
    'title' => 'a.title',
    'description' => 'a.description',
    'start_date' => 'a.start_date',
    'end_date' => 'a.end_date'
];

// Initialize the columns to display
$columns_to_display = [];

if (!empty($selected_columns) && is_array($selected_columns)) {
    foreach ($selected_columns as $col) {
        if (isset($columns_map[$col])) {
            $columns_to_display[$col] = $columns_map[$col];
        }
    }
}

// Default to all columns if no specific columns selected
if (empty($columns_to_display)) {
    $columns_to_display = $columns_map;
}

// Building the SELECT clause dynamically based on selected columns
$select_clause = implode(', ', $columns_to_display);
$sql = "SELECT $select_clause FROM achievements a LEFT JOIN users u ON a.registrationId = u.registrationId";

// Filter conditions
$whereClauses = [];
$params = [];
$types = '';

// Filter by main category
if (!empty($_GET['main_category'])) {
    $inClause = implode("','", array_map([$conn, 'real_escape_string'], $_GET['main_category']));
    $whereClauses[] = "a.main_category IN ('$inClause')";
}

// Filter by year (based on users table)
if (!empty($_GET['year'])) {
    $years = array_map([$conn, 'real_escape_string'], $_GET['year']);
    $quotedYears = "'" . implode("','", $years) . "'";
    $yearCondition = "a.registrationId IN (SELECT registrationId FROM users WHERE year IN ($quotedYears))";
    $whereClauses[] = $yearCondition;
}
// Gender filter
if (!empty($_GET['gender'])) {
    $genders = array_map([$conn, 'real_escape_string'], $_GET['gender']);
    $quotedGenders = "'" . implode("','", $genders) . "'";
    $whereClauses[] = "u.gender IN ($quotedGenders)";
}

// Course filter
if (!empty($_GET['course'])) {
    $courses = array_map([$conn, 'real_escape_string'], $_GET['course']);
    $quotedCourses = "'" . implode("','", $courses) . "'";
   $whereClauses[] = "u.course_id IN (select id from courses where course in ($quotedCourses))";

}
// Filter by date range
if (!empty($_GET['from_date']) && !empty($_GET['to_date'])) {
    $whereClauses[] = "(a.start_date BETWEEN ? AND ? OR a.end_date BETWEEN ? AND ?)";
    $params[] = $_GET['from_date'];
    $params[] = $_GET['to_date'];
    $params[] = $_GET['from_date'];
    $params[] = $_GET['to_date'];
    $types .= 'ssss';
}

// Filter by search term
if (!empty($_GET['search'])) {
    $search = "%{$_GET['search']}%";
    $whereClauses[] = "(a.registrationId LIKE ? OR a.title LIKE ? OR a.description LIKE ? OR a.main_category LIKE ? OR a.sub_category LIKE ? OR a.start_date LIKE ? OR a.end_date LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= 'sssssss';
}

// Adding WHERE clause if needed
if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

// Preparing and executing the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Output HTML table
if ($result->num_rows > 0): ?>
    <table id="achievements-table" border="1" cellspacing="0" cellpadding="8" style="width: 100%; border-collapse: collapse;">
        <thead style="background-color: #0056b3; color: white;">
            <tr>
                <th>Sr. No.</th>
                <?php foreach ($columns_to_display as $col => $dbCol): ?>
                    <th><?php echo ucfirst(str_replace('_', ' ', $col)); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php $sn = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <?php foreach ($columns_to_display as $col => $dbCol): ?>
                        <td><?php echo htmlspecialchars($row[$col]); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No results found.</p>
<?php endif; ?>
