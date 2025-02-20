<?php
session_start();

// Check if the user is logged in as a manager
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header("Location: index.php");
    exit();
}

// Include database connection
include 'db_config.php';

// Fetch form data
$department_id = $_POST['department_id'] ?? null;
$team_member_id = $_POST['team_member_id'] ?? null;
$date_range = $_POST['date_range'] ?? null;

// Parse date range if provided
$date_condition = '';
if ($date_range) {
    [$start_date, $end_date] = explode(' to ', $date_range);
    $date_condition = "AND a.date BETWEEN '$start_date' AND '$end_date'";
}

// Build query based on selected filters
$query = "
    SELECT 
        a.date, 
        a.status, 
        u.name AS team_member_name, 
        d.name AS department_name, 
        a.arrival_time
    FROM attendance a
    JOIN users u ON a.user_id = u.id
    JOIN departments d ON a.department_id = d.id
    WHERE d.manager_id = (SELECT id FROM managers WHERE user_id = '{$_SESSION['user_id']}')
";

if ($department_id) {
    $query .= " AND a.department_id = '$department_id'";
}
if ($team_member_id) {
    $query .= " AND a.user_id = '$team_member_id'";
}
$query .= " $date_condition ORDER BY a.date DESC";

// Fetch records
$attendance_records = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

// Export CSV
if (isset($_POST['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attendance_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Status', 'Team Member', 'Department', 'Arrival Time']);
    foreach ($attendance_records as $record) {
        fputcsv($output, [$record['date'], $record['status'], $record['team_member_name'], $record['department_name'], $record['arrival_time']]);
    }
    fclose($output);
    exit();
}

// Export PDF
if (isset($_POST['export_pdf'])) {
    require('fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Attendance Report', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 10);
    foreach ($attendance_records as $record) {
        $pdf->Cell(0, 10, "{$record['date']} - {$record['status']} - {$record['team_member_name']} - {$record['department_name']} - {$record['arrival_time']}", 0, 1);
    }
    $pdf->Output('D', 'attendance_report.pdf');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
body {
    font-family: 'Roboto', sans-serif;
    background-color: #f4f7fc;
    color: #495057;
    
}
.sidebar {
    flex: 1;
    max-width: 250px;
     background: linear-gradient(135deg, #004e92, #00c3ff);
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
            color: #ffeb3b;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
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
            color: #ffeb3b;
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

.container {
    max-width: 1100px;
    margin: 0 auto;
    margin-left: 260px;
    padding: 20px;
}

.card {
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    background-color: #fff;
    padding: 20px;
    margin-bottom: 30px;
}

.card h3 {
    color: #004e92;
    margin-bottom: 20px;
    font-weight: bold;
}

.form-label {
    
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.table th, 
.table td {
    text-align: left;
    padding: 12px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

.table thead th {
    background-color: #004e92;
    color: #fff;
    font-weight: bold;
    text-transform: uppercase;
}

.table-hover tbody tr:hover {
    background-color: #c8e6f3;
}

.table-responsive {
    overflow-x: auto;
    border-radius: 10px;
}

.alert {
    border-radius: 10px;
    padding: 15px;
    font-size: 16px;
    margin-bottom: 20px;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.progress {
    height: 20px;
    border-radius: 10px;
    background-color: #e9ecef;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(135deg, #004e92, #00c3ff);
    transition: width 0.4s ease;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    list-style: none;
    padding: 0;
}

.pagination li {
    margin: 0 5px;
}

.pagination a {
    text-decoration: none;
    color: #004e92;
    padding: 8px 15px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.pagination a:hover, 
.pagination a.active {
    background-color: #004e92;
    color: white;
    border-color: #004e92;
}

@media (max-width: 768px) {
    .table {
        font-size: 14px;
    }
    .sidebar {
    position: relative;
    min-height: auto;
    margin-bottom: 20px;
     }
    .btn {
        font-size: 14px;
        padding: 8px 16px;
    }
}

</style>
</head>
<body>
    <div class="sidebar">
        <h2>Attendance Report</h2>
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
    <div class="container mt-4">
        <h3 class="text-center">Attendance Report</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Team Member</th>
                    <th>Department</th>
                    <th>Arrival Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><?= $record['date']; ?></td>
                        <td><?= $record['status']; ?></td>
                        <td><?= $record['team_member_name']; ?></td>
                        <td><?= $record['department_name']; ?></td>
                        <td><?= $record['arrival_time']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form method="POST">
            <button type="submit" name="export_csv" class="btn btn-success">Export as CSV</button>
            <button type="submit" name="export_pdf" class="btn btn-danger">Export as PDF</button>
        </form>
    </div>
</body>
</html>
