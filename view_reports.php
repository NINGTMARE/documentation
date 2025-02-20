<?php
session_start();

// Ensure the user is logged in as a manager
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header("Location: index.php");
    exit();
}

include 'db_config.php';

$search_name = "";
$start_date = "";
$end_date = "";
$limit = 10; // Reports per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Handle Deletion of a Report
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = $conn->prepare("DELETE FROM work_reports WHERE id = ?");
    $delete_query->bind_param("i", $delete_id);
    $delete_query->execute();
    header("Location: view_reports.php");
    exit();
}

// Handle Search and Date Filters
$where_conditions = [];
$params = [];
$types = "";

// Filter by Employee Name
if (isset($_GET['search_name']) && $_GET['search_name'] !== "") {
    $search_name = $_GET['search_name'];
    $where_conditions[] = "u.name LIKE ?";
    $params[] = "%$search_name%";
    $types .= "s";
}

// Filter by Date Range
if (isset($_GET['start_date']) && $_GET['start_date'] !== "") {
    $start_date = $_GET['start_date'];
    $where_conditions[] = "wr.submission_date >= ?";
    $params[] = $start_date;
    $types .= "s";
}
if (isset($_GET['end_date']) && $_GET['end_date'] !== "") {
    $end_date = $_GET['end_date'];
    $where_conditions[] = "wr.submission_date <= ?";
    $params[] = $end_date;
    $types .= "s";
}

// Construct the Query
$where_clause = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";
$query = $conn->prepare("
    SELECT wr.id, u.name AS employee_name, wr.report_title, wr.report_description, wr.submission_date
    FROM work_reports wr
    JOIN users u ON wr.employee_id = u.id
    $where_clause
    ORDER BY wr.submission_date DESC
    LIMIT ? OFFSET ?
");

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Bind parameters dynamically
$query->bind_param($types, ...$params);
$query->execute();
$reports = $query->get_result()->fetch_all(MYSQLI_ASSOC);

// Count total reports for pagination
$count_query_string = "
    SELECT COUNT(*) AS total
    FROM work_reports wr
    JOIN users u ON wr.employee_id = u.id
    $where_clause
";

$count_query = $conn->prepare($count_query_string);

// Bind only filter parameters, not LIMIT or OFFSET
$bind_types = substr($types, 0, -2); // Remove "ii" for LIMIT and OFFSET
$bind_params = array_slice($params, 0, -2);

if (!empty($bind_params)) {
    $count_query->bind_param($bind_types, ...$bind_params);
}

$count_query->execute();
$total_reports = $count_query->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_reports / $limit);


// Export Reports to CSV
if (isset($_GET['export']) && $_GET['export'] === "csv") {
    header('Content-Type: text/txt');
    header('Content-Disposition: attachment; filename="work_reports.txt"');

    $output = fopen("php://output", "w");
    fputcsv($output, ['#', 'Employee Name', 'Report Title', 'Description', 'Submission Date']);

    $export_query = $conn->prepare("
        SELECT u.name AS employee_name, wr.report_title, wr.report_description, wr.submission_date
        FROM work_reports wr
        JOIN users u ON wr.employee_id = u.id
        $where_clause
    ");
    if (!empty($types)) {
        $export_query->bind_param($types, ...array_slice($params, 0, -3));
    }
    $export_query->execute();
    $export_result = $export_query->get_result();

    $count = 1;
    while ($row = $export_result->fetch_assoc()) {
        fputcsv($output, [$count++, $row['employee_name'], $row['report_title'], $row['report_description'], $row['submission_date']]);
    }
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Work Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General Body Styling */
body {
    background-color: #f8f9fa;
    font-family: Arial, sans-serif;
    color: #333;
}
.sidebar {
            flex: 1;
            max-width: 250px;
            background: linear-gradient(135deg, #004e92, #00c3ff); /* Blue-to-teal gradient */
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            height: 100vh;
            position: fixed;
        }

        .sidebar h2 {
            text-align: center;
            color: #ffeb3b; /* Yellow title */
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
            animation: slideIn 1s ease-out;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .sidebar ul li a:hover {
            color: #ffeb3b; /* Yellow hover color */
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }
/* Container Styling */
.container {
    width: 80%;
    margin-left: 250px;
    background-color: #ffffff;
    padding: 20px 30px;
    border-radius: 10px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}

/* Heading */
h2 {
    font-size: 2rem;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #007bff;
    display: inline-block;
    margin-bottom: 20px;
}

/* Form Inputs and Buttons */
form .form-control {
    border-radius: 5px;
    border: 1px solid #ced4da;
    transition: border-color 0.3s;
}

form .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

form .btn {
    border-radius: 5px;
    font-size: 14px;
    padding: 8px 12px;
}

form .btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

form .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

form .btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

form .btn:hover {
    opacity: 0.9;
}

/* Table Styling */
.table {
    margin-top: 10px;
    border: 1px solid #dee2e6;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    overflow: hidden;
}

.table th {
    background-color: #007bff;
    color: white;
    text-align: center;
    font-size: 14px;
    padding: 10px;
}

.table td {
    text-align: center;
    vertical-align: middle;
    padding: 10px;
    font-size: 14px;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: #f9f9f9;
}

.table-striped tbody tr:hover {
    background-color: #f1f1f1;
}

/* Action Buttons */
.btn-danger {
    font-size: 13px;
    padding: 5px 10px;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

/* Pagination */
.pagination {
    margin-top: 15px;
    justify-content: center;
}

.page-link {
    color: #007bff;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}

.page-item .page-link:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
}

</style>
</head>
<body>
<div class="sidebar">
        <h2>View Work Reports</h2>
        <ul>
            <li><a href="manager.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="manage_departments.php"><i class="fas fa-cogs"></i> Manage Departments</a></li>
            <li><a href="manage_team.php"><i class="fas fa-users"></i> Manage Team</a></li>
            <li><a href="view_reports.php"><i class="fas fa-chart-line"></i> View Reports</a></li>
            <li><a href="generate_report.php"><i class="fas fa-file-alt"></i> Generate Reports</a></li>
            <li><a href="manager_task.php"><i class="fas fa-tasks"></i> Assign Tasks</a></li>
            <li><a href="employee_report.php"><i class="fas fa-file-alt"></i> Employee Reports</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
</div>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Work Reports</h2>

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
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="view_reports.php" class="btn btn-secondary">Reset</a>
                <a href="view_reports.php?<?= http_build_query($_GET); ?>&export=csv" class="btn btn-success">Export CSV</a>
            </div>
        </form>

        <!-- Reports Table -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee Name</th>
                    <th>Report Title</th>
                    <th>Description</th>
                    <th>Submission Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $key => $report): ?>
                    <tr>
                        <td><?= $offset + $key + 1; ?></td>
                        <td><?= htmlspecialchars($report['employee_name']); ?></td>
                        <td><?= htmlspecialchars($report['report_title']); ?></td>
                        <td><?= htmlspecialchars($report['report_description']); ?></td>
                        <td><?= htmlspecialchars($report['submission_date']); ?></td>
                        <td>
                            <a href="view_reports.php?delete_id=<?= $report['id']; ?>" onclick="return confirm('Are you sure you want to delete this report?');" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</body>
</html>
