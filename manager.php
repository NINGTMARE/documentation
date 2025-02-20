<?php
session_start();

// Check if the user is logged in as manager
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header("Location: index.php");
    exit();
}

// Include database connection
include 'db_config.php';

// Fetch manager information from session
$manager_id = $_SESSION['user_id'];
$name = $conn->query("SELECT * FROM users WHERE id = '$manager_id'")->fetch_assoc();
// Fetch manager's department(s) with team member count
$departments = $conn->query("
    SELECT 
        d.name, 
        d.description, 
        COUNT(dm.user_id) AS team_count 
    FROM departments d
    LEFT JOIN department_members dm ON d.id = dm.department_id
    WHERE d.manager_id = (SELECT id FROM managers WHERE user_id = '$manager_id')
    GROUP BY d.id
")->fetch_all(MYSQLI_ASSOC);

// Fetch all team members in the department(s)
$team_members = $conn->query("
    SELECT 
        u.id, 
        u.name, 
        u.role, 
        u.email 
    FROM users u 
    JOIN department_members dm ON u.id = dm.user_id 
    WHERE dm.department_id IN (
        SELECT id 
        FROM departments 
        WHERE manager_id = (SELECT id FROM managers WHERE user_id = '$manager_id')
    )
")->fetch_all(MYSQLI_ASSOC);



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
        d.name AS department_name 
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


// Close DB connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fc;
            color: #495057;
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

        .content {
            margin-left: 260px;
            padding: 20px;
        }

    
    /* General Styling */
    body {
        font-family: 'Roboto', sans-serif;
        background-color: #f4f7fc;
        color: #495057;
    }

    .card {
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        background-color: #ffffff;
        padding: 20px;
        margin-bottom: 20px;
    }

    h3 {
        color: #004e92; /* Blue headings */
        font-weight: 700;
        margin-bottom: 20px;
    }

    /* Table Styling */
    .table {
        background-color: #ffffff;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .table thead th {
        background-color: #004e92; /* Blue header */
        color: white;
        text-align: center;
        padding: 10px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .table tbody tr {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease-in-out;
    }

    .table tbody tr:hover {
        background-color: #f0f8ff; /* Light blue */
        transform: scale(1.02);
    }

    .table tbody td {
        text-align: center;
        padding: 10px;
        color: #495057;
    }

    .table tbody td:first-child {
        font-weight: bold;
        color: #004e92;
    }

    .table tbody td:last-child {
        font-weight: bold;
    }

    /* Responsive Table */
    @media (max-width: 768px) {
        .table {
            font-size: 14px;
        }

        .table thead {
            display: none; /* Hide header */
        }

        .table tbody tr {
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .table tbody td {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .table tbody td:last-child {
            border-bottom: none;
        }

        .table tbody td::before {
            content: attr(data-label); /* Use "data-label" for field names */
            font-weight: bold;
            color: #004e92;
        }
    }

    /* Button Styling */
    .btn {
        border-radius: 20px;
        padding: 8px 15px;
        font-size: 14px;
        transition: all 0.3s ease-in-out;
    }

    .btn-primary {
        background-color: #00bcd4; /* Teal */
        border: none;
    }

    .btn-primary:hover {
        background-color: #0097a7; /* Dark teal */
    }

    .btn-success {
        background-color: #4caf50; /* Green */
        border: none;
    }

    .btn-success:hover {
        background-color: #388e3c; /* Darker green */
    }

    .btn-danger {
        background-color: #f44336; /* Red */
        border: none;
    }

    .btn-danger:hover {
        background-color: #d32f2f; /* Dark red */
    }


        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                min-height: auto;
                margin-bottom: 20px;
            }

            .content {
                margin-left: 0;
            }

            .card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Manager Dashboard</h2>
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
        <h1 class="text-center mb-4">Welcome <?= htmlspecialchars($name['name']); ?> to Your Dashboard</h1>

        
    
        <div class="card p-4">
            <h3>Your Departments</h3>
            <ul>
                <?php foreach ($departments as $department): ?>
                    <li>
                        <strong><?= $department['name']; ?></strong> 
                            (Team Members: <?= $department['team_count']; ?>)
                            <br>
                        <?= $department['description']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<div class="content">
    <div class="card p-4">
        <h3>Attendance Records</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Team Member</th>
                        <th>Department</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><?= $record['date']; ?></td>
                            <td><?= $record['team_member_name']; ?></td>
                            <td><?= $record['department_name']; ?></td>
                            <td><?= $record['status']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
