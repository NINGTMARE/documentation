<?php
session_start();
// Check if the user is logged in and is a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: index.php");
    exit();
}

// Include the database connection
include 'db_config.php';

$message = "";

// Fetch all departments for the dropdown
$dept_query = $conn->prepare("SELECT id, name FROM departments");
$dept_query->execute();
$departments = $dept_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch employees based on the selected department
$employees = [];
if (isset($_POST['department_id']) && !empty($_POST['department_id'])) {
    $department_id = $_POST['department_id'];
    $emp_query = $conn->prepare("
    SELECT u.id, CONCAT(u.name) AS employee_name
    FROM users u
    INNER JOIN department_members dm ON u.id = dm.user_id
    WHERE dm.department_id = ?
");
    $emp_query->bind_param("i", $department_id);
    $emp_query->execute();
    $employees = $emp_query->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch tasks for the selected employee
$tasks = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id']) && !empty($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];
    $task_query = $conn->prepare("
        SELECT task_title, task_description, deadline, status, assigned_at 
        FROM tasks 
        WHERE employee_id = ?
    ");
    $task_query->bind_param("i", $employee_id);
    $task_query->execute();
    $tasks = $task_query->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #004e92, #00c3ff);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100%;
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
            transition: 0.3s;
        }
        .sidebar ul li a:hover {
            color: #ffeb3b;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
        }
        table {
            background-color: #fff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Employee Reports</h2>
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

    <div class="content">
        <h1 class="text-center">Employee Report</h1>

        <!-- Filter Form -->
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="department_id" class="form-label">Select Department</label>
                    <select name="department_id" id="department_id" class="form-select" onchange="this.form.submit()" required>
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id']; ?>" <?= isset($_POST['department_id']) && $_POST['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($employees)): ?>
                <div class="col-md-4">
                    <label for="employee_id" class="form-label">Select Employee</label>
                    <select name="employee_id" id="employee_id" class="form-select" required>
                        <option value="">-- Select Employee --</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= $employee['id']; ?>" <?= isset($_POST['employee_id']) && $_POST['employee_id'] == $employee['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($employee['employee_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-4 align-self-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </div>
        </form>

        <!-- Display Tasks -->
        <?php if (!empty($tasks)): ?>
            <h3>Tasks Assigned</h3>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Task Title</th>
                        <th>Description</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Assigned On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?= htmlspecialchars($task['task_title']); ?></td>
                            <td><?= htmlspecialchars($task['task_description']); ?></td>
                            <td><?= htmlspecialchars($task['deadline']); ?></td>
                            <td><?= htmlspecialchars($task['status'] ?? 'Pending'); ?></td>
                            <td><?= htmlspecialchars($task['assigned_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'])): ?>
            <p class="text-danger">No tasks found for this employee.</p>
        <?php endif; ?>
    </div>
</body>
</html>
