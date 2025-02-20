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
$user_id = $_SESSION['user_id']; // Ensure session contains valid user_id

$dept_query = $conn->prepare("
    SELECT d.id AS department_id, d.name AS department_name
    FROM department_members dm
    JOIN departments d ON dm.department_id = d.id
    WHERE dm.user_id = ?
");

$dept_query->bind_param("i", $user_id);
$dept_query->execute();
$dept_result = $dept_query->get_result();

if ($dept_result->num_rows > 0) {
    $department = $dept_result->fetch_assoc();
    $department_id = $department['department_id'];
    $department_name = $department['department_name'];
} else {
    die("<div class='alert alert-danger'>You are not associated with any department. Contact the administrator.</div>");
}


// Check if attendance is already marked for today
$date_today = date('Y-m-d');
$check_query = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
$check_query->bind_param("is", $user_id, $date_today);
$check_query->execute();
$result = $check_query->get_result();
$is_attendance_marked = $result->num_rows > 0;

// Mark attendance when form is submitted  
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_attendance_marked) {  
    $status = $_POST['status']; // 'present', 'absent', or 'leave'  
    
    if (in_array($status, ['present', 'absent', 'leave'])) {  
        $arrival_time = date('H:i:s'); // Capture the current time  
        $insert_query = $conn->prepare("INSERT INTO attendance (user_id, department_id, date, status, arrival_time) VALUES (?, ?, ?, ?, ?)");  
        $insert_query->bind_param("iisss", $user_id, $department_id, $date_today, $status, $arrival_time);  
        
        if ($insert_query->execute()) {  
            $message = "<div class='alert alert-success'>Attendance marked successfully for today at $arrival_time.</div>";  
        } else {  
            $message = "<div class='alert alert-danger'>Failed to mark attendance. Please try again.</div>";  
        }  
    } else {  
        $message = "<div class='alert alert-danger'>Invalid attendance status selected.</div>";  
    }  
}
// Fetch attendance summary for the user
// Fetch attendance summary for the user  
$summary_query = $conn->prepare("  
   SELECT   
    COUNT(*) AS total_days,   
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS days_present,  
    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS days_absent,  
    SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) AS days_on_leave,  
    GROUP_CONCAT(CASE WHEN status = 'present' THEN arrival_time END ORDER BY date) AS arrival_times  
FROM attendance  
WHERE user_id = ? AND department_id = ?  
");  
$summary_query->bind_param("ii", $user_id, $department_id);  
$summary_query->execute();  
$attendance_summary = $summary_query->get_result()->fetch_assoc();  
$total_days = $attendance_summary['total_days'] ?? 0;  
$days_present = $attendance_summary['days_present'] ?? 0;  
$arrival_times = $attendance_summary['arrival_times'] ?? '';
$attendance_rate = $total_days > 0 ? round(($days_present / $total_days) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
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
            margin-left: 250px;
            border: none;
            background:  transparent;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
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

        .attendance-summary {
            background: #e7f3ff;
            border: 1px solid #b3d8ff;
            padding: 15px;
            border-radius: 10px;
            color: #004085;
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

    <div class="container">
        <h1 class="text-center mb-4">Employee Attendance</h1>
        <h3 class="text-center">Department: <?= htmlspecialchars($department_name); ?></h3>
        <?= $message; ?>
        <!-- Attendance Form -->
        <?php if ($is_attendance_marked): ?>
            <div class="alert alert-info">You have already marked your attendance for today.</div>
        <?php else: ?>
            <form method="POST">
                <label for="status" class="form-label">Select Attendance Status</label>
                <select class="form-select mb-3" id="status" name="status" required>
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="leave">Leave</option>
                </select>
                <button type="submit" class="btn btn-primary">Mark Attendance</button>
            </form>
        <?php endif; ?>
        <!-- Attendance Summary -->
        <div>  
            <h3>Attendance Summary</h3>  
            <p>Total Days: <?= $total_days; ?></p>  
            <p>Days Present: <?= $days_present; ?></p>  
            <p>Attendance Rate: <?= $attendance_rate; ?>%</p>  
            <?php if ($arrival_times): ?>  
                <p>Arrival Times: <?= htmlspecialchars($arrival_times); ?></p>  
            <?php endif; ?>  
        </div>
    </div>
</body>
</html>
