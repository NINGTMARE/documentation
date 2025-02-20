<?php
session_start();

// Check if the user is logged in and is a manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: index.php");
    exit();
}

// Database connection
include 'db_config.php';

$manager_id = $_SESSION['user_id'];
$message = "";

// Fetch manager's department(s)
$dept_query = $conn->prepare("
    SELECT id, name 
    FROM departments 
    WHERE manager_id = (SELECT id FROM managers WHERE user_id = ?)
");
$dept_query->bind_param("i", $manager_id);
$dept_query->execute();
$departments = $dept_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch employees in the department
$employees = [];
if (isset($_POST['department_id']) && $_POST['department_id']) {
    $department_id = $_POST['department_id'];
    // Query the department_member table
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

// Assign a task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_task'])) {
    $department_id = $_POST['department_id'];
    $employee_id = $_POST['employee_id'];
    $task_title = $_POST['task_title'];
    $task_description = $_POST['task_description'];
    $deadline = $_POST['deadline'];

    if ($department_id && $employee_id && $task_title && $deadline) {
        $insert_query = $conn->prepare("
            INSERT INTO tasks (employee_id, department_id, task_title, task_description, deadline)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert_query->bind_param("iisss", $employee_id, $department_id, $task_title, $task_description, $deadline);

        if ($insert_query->execute()) {
            $message = "<div class='alert alert-success'>Task assigned successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to assign task. Please try again.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            color: #333;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #004e92, #00c3ff);
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
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

        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        h1 {
            color: #007bff;
            margin-bottom: 20px;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: bold;
        }

        .form-select,
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px;
        }

        .btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Manage Team Members</h2>
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

    <div class="main-content">
        <h1 class="text-center">Assign Task to Employee</h1>
        <?= $message; ?>

        <!-- Form to Select Department -->
        <form method="POST">
            <div class="mb-3">
                <label for="department_id" class="form-label">Select Department</label>
                <select name="department_id" id="department_id" class="form-select" onchange="this.form.submit()" required>
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= $department['id']; ?>" <?= isset($department_id) && $department_id == $department['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (!empty($employees)): ?>
    <form method="POST">
        <input type="hidden" name="department_id" value="<?= $department_id; ?>">

        <div class="mb-3">
            <label for="employee_id" class="form-label">Select Employee</label>
            <select name="employee_id" id="employee_id" class="form-select" required>
                <option value="">-- Select Employee --</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= $employee['id']; ?>">
                        <?= htmlspecialchars($employee['employee_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="task_title" class="form-label">Task Title</label>
            <input type="text" name="task_title" id="task_title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="task_description" class="form-label">Task Description</label>
            <textarea name="task_description" id="task_description" class="form-control" rows="4"></textarea>
        </div>

        <div class="mb-3">
            <label for="deadline" class="form-label">Deadline</label>
            <input type="date" name="deadline" id="deadline" class="form-control" required>
        </div>

        <button type="submit" name="assign_task" class="btn btn-primary">Assign Task</button>
    </form>
<?php else: ?>
    <p class="text-danger">No employees found in this department.</p>
<?php endif; ?>

    </div>
</body>
</html>
