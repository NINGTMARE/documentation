<?php
session_start();

// Ensure the user is logged in as a user
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}

// Include the database connection
include 'db_config.php';

// Fetch employee details from the database
$user_id = $_SESSION['user_id'];
$employee = $conn->query("SELECT * FROM users WHERE id = '$user_id'")->fetch_assoc();

// Fetch upcoming tasks for the employee (if any)
$tasks = $conn->query("
    SELECT * FROM tasks
    WHERE employee_id = '$user_id' AND deadline >= CURDATE()
    ORDER BY deadline ASC
")->fetch_all(MYSQLI_ASSOC);

// Fetch employee attendance summary
$attendance = $conn->query("
    SELECT COUNT(*) AS total_days, 
           SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS days_present
    FROM attendance
    WHERE user_id = '$user_id'
")->fetch_assoc();

$arrival_times = []; // Initialize the variable

$query = $conn->prepare("
    SELECT arrival_time
    FROM attendance
    WHERE user_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

while ($row = $result->fetch_assoc()) {
    $arrival_times[] = $row['arrival_time'];
}

// Close the DB connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
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
            font-size: 22px;
            margin-bottom: 20px;
            color: #ffeb3b;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 12px 20px;
        }

        .sidebar ul li a {
            color: #f8f9fa;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar ul li a:hover {
            color: #ffeb3b;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .content {
            margin-left: 240px;
            padding: 20px;
        }

        .card {
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            margin-bottom: 15px;
            color: #007bff;
        }

        .card p, .card ul {
            color: #495057;
        }

        .welcome-header {
            margin-bottom: 20px;
        }

        .welcome-header h1 {
            font-size: 28px;
            color: #343a40;
        }

        .welcome-header p {
            color: #6c757d;
        }

        .tasks-list li {
            margin-bottom: 10px;
        }

        .tasks-list li strong {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Employee Dashboard</h2>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a></li>
            <li><a href="tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
            <li><a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="welcome-header">
            <h1>Welcome, <?= htmlspecialchars($employee['name']); ?>!</h1>
            <p>Your Role: <?= htmlspecialchars($employee['role']); ?></p>
        </div>

        <!-- Employee Attendance Summary -->
        <div class="card">
            <h3>Attendance Summary</h3>
            <p>Total Days: <?= htmlspecialchars($attendance['total_days']); ?></p>
            <p>Days Present: <?= htmlspecialchars($attendance['days_present']); ?></p>
            <p>Attendance Rate: 
                <?= $attendance['total_days'] > 0 
                    ? round(($attendance['days_present'] / $attendance['total_days']) * 100, 2) . '%' 
                    : 'N/A'; ?>
            </p>
            <?php if ($arrival_times): ?>  
                <p>Arrival Times: <?= htmlspecialchars(implode(', ', $arrival_times)); ?></p>  
            <?php endif; ?>  
        </div>

        <!-- Upcoming Tasks -->
        <div class="card">
            <h3>Upcoming Tasks</h3>
            <?php if (!empty($tasks)): ?>
                <ul class="tasks-list">
                    <?php foreach ($tasks as $task): ?>
                        <li>
                            <strong><?= htmlspecialchars($task['task_title']); ?></strong><br>
                            Due Date: <?= htmlspecialchars($task['deadline']); ?><br>
                            Status: <?= htmlspecialchars($task['status']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>You don't have any upcoming tasks.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
