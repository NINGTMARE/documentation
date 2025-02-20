<?php
session_start();

// ... (Manager role check remains the same) ...

include 'db_config.php';

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

//Sanitize Input
$search_name = isset($_GET['search_name']) ? filter_var($_GET['search_name'], FILTER_SANITIZE_STRING) : '';
$start_date = isset($_GET['start_date']) ? filter_var($_GET['start_date'], FILTER_SANITIZE_STRING) : '';
$end_date = isset($_GET['end_date']) ? filter_var($_GET['end_date'], FILTER_SANITIZE_STRING) : '';



// Handle Deletion (unchanged, but should add error handling)
if (isset($_GET['delete_id'])) {
    // ... (Deletion code remains the same) ...
}



// Construct the Query (Improved for efficiency)
$where_clause = "";
$params = [];
$types = "";


if (!empty($search_name)) {
  $where_clause .= "AND u.name LIKE ?";
  $params[] = "%{$search_name}%";
  $types .= "s";
}

if (!empty($start_date)) {
  $where_clause .= "AND wr.submission_date >= ?";
  $params[] = $start_date;
  $types .= "s";
}

if (!empty($end_date)) {
  $where_clause .= "AND wr.submission_date <= ?";
  $params[] = $end_date;
  $types .= "s";
}


$query = $conn->prepare("
    SELECT SQL_CALC_FOUND_ROWS wr.id, dm.name AS employee_name, wr.report_title, wr.report_description, wr.submission_date
    FROM work_reports wr
    JOIN department_members dm ON wr.employee_id = dm.user_id
    WHERE 1=1
    {$where_clause}
    ORDER BY wr.submission_date DESC
    LIMIT ? OFFSET ?
");

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// ... (Binding parameters and fetching reports remains the same) ...


// Count total reports (more efficient - single query)
$total_reports = $conn->query("SELECT FOUND_ROWS()")->fetch_array()[0];
$total_pages = ceil($total_reports / $limit);


// ... (CSV export remains similar but needs further security improvements) ...
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <!-- Search and Filter Form -->
<form method="GET" class="row mb-4">
    <div class="col-md-3">
        <input type="text" name="search_name" placeholder="Search by Employee Name" value="<?= htmlspecialchars($search_name); ?>" class="form-control">
    </div>
    <div class="col-md-3">
        <input type="date" name="start_date" value="<?= htmlspecialchars($start_date); ?>" class="form-control">
    </div>
    <div class="col-md-3">
        <input type="date" name="end_date" value="<?= htmlspecialchars($end_date); ?>" class="form-control">
    </div>
    <div class="col-md-3">
        <select name="department_id" class="form-control">
            <option value="">All Departments</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?= $department['id']; ?>" <?= isset($_GET['department_id']) && $_GET['department_id'] == $department['id'] ? 'selected' : ''; ?>><?= $department['name']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="view_reports.php" class="btn btn-secondary">Reset</a>
        <a href="view_reports.php?<?= http_build_query($_GET); ?>&export=csv" class="btn btn-success">Export CSV</a>
    </div>
</form>

</body>
</html>