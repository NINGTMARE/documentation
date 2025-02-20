<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

// Database connection
include '../db_config.php';

$user_id = $_SESSION['user_id'];
$message = "";
// Fetch user's department
$dept_query = $conn->prepare("
    SELECT d.id AS department_id, d.name AS department_name
    FROM department_members dm
    JOIN departments d ON dm.department_id = d.id
    WHERE dm.user_id = ?
");
$dept_query->bind_param("i", $user_id);
$dept_query->execute();
$dept_result = $dept_query->get_result();

if ($dept_result->num_rows === 0) {
    die("<div class='alert alert-danger'>You are not associated with any department. Contact the administrator.</div>");
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];
$department_name = $department['department_name'];

// Fetch tasks for the user
$tasks_query = $conn->prepare("
    SELECT t.id, t.task_title, t.task_description, t.status, t.deadline
    FROM tasks t
    WHERE t.department_id = ? AND (t.employee_id = ? OR t.employee_id IS NULL)
    ORDER BY t.deadline ASC
");
$tasks_query->bind_param("ii", $department_id, $user_id);
$tasks_query->execute();
$tasks_result = $tasks_query->get_result();

// Update task status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['status'])) {
    $task_id = intval($_POST['task_id']);
    $status = $_POST['status'];

    if (in_array($status, ['Pending', 'In Progress', 'Completed'])) {
        $update_task_query = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND employee_id = ?");
        $update_task_query->bind_param("sii", $status, $task_id, $user_id);

        if ($update_task_query->execute()) {
            $message = "<div class='alert alert-success'>Task status updated successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to update task status. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            padding: 20px;
        }
        .sidebar {
            width: 220px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            background: linear-gradient(135deg, #004e92, #00c3ff);
            color: #fff;
            padding-top: 20px;
        }

        .sidebar h2 {
            text-align: center;
            color: #ffeb3b;
            font-size: 22px;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 12px 20px;
            text-align: left;
        }

        .sidebar ul li a {
            color: #f8f9fa;
            text-decoration: none;
            display: block;
            font-size: 16px;
        }

        .sidebar ul li a:hover {
            color: #ffeb3b;
            border-radius: 4px;
        }

        .container {
            width: 80%;
            margin-left: 220px;
            border: none;
            background:  transparent;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        h1, h3 {
            color: #007bff;
        }
        .btn-submit {
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="sidebar">
        <h2>Employee Dashboard</h2>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="attendance.php"><i class="fas fa-chart-line"></i> Attendance</a></li>
            <li><a href="tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
            <li><a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="container card">
    <?= $message; ?>
        <h1 class="text-center mb-4">My Tasks</h1>
        <h3 class="text-center">Department: <?= htmlspecialchars($department_name); ?></h3>

        <?php if ($tasks_result->num_rows > 0): ?>
            <?php while ($task = $tasks_result->fetch_assoc()): ?>
                <div class="card p-4">
                    <h3><?= htmlspecialchars($task['task_title']); ?></h3>
                    <p><?= htmlspecialchars($task['task_description']); ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($task['status']); ?></p>
                    <p><strong>Due Date:</strong> <?= htmlspecialchars($task['deadline']); ?></p>
                    <?php if ($task['status'] !== 'Completed'): ?>
                        <form method="POST">
                            <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                            <select class="form-select mb-3" name="status" required>
                                <option value="Pending" <?= $task['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?= $task['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                            <button type="submit" class="btn btn-submit">Update Status</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">No tasks assigned to you in this department.</div>
        <?php endif; ?>
    </div>
</body>
</html>
