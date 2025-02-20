<?php
session_start();
include 'db_config.php';

// Ensure the user is logged in as a manager
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Fetch user information
$query = $conn->prepare("
    SELECT 
        u.name AS employee_name, 
        u.email, 
        u.role, 
        d.name AS department_name
    FROM 
        users u
    LEFT JOIN 
        departments d 
    ON 
        u.department_id = d.id
    WHERE 
        u.id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$profile = $result->fetch_assoc();

// If no profile is found, handle gracefully
if (!$profile) {
    die("<div style='text-align:center; margin-top:20px;'>
        <h3>User not found.</h3>
        <a href='logout.php' style='color:blue;'>Logout</a>
    </div>");
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate input
    if (!empty($password) && $password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Update query
        $update_query = $conn->prepare("
            UPDATE users 
            SET name = ?, email = ?, password = ? 
            WHERE id = ?
        ");
        $hashed_password = !empty($password) ? password_hash($password, PASSWORD_BCRYPT) : $profile['password'];
        $update_query->bind_param("sssi", $name, $email, $hashed_password, $user_id);

        if ($update_query->execute()) {
            $success_message = "Profile updated successfully!";
            // Refresh profile data
            $query->execute();
            $profile = $query->get_result()->fetch_assoc();
        } else {
            $error_message = "Failed to update profile. Please try again.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
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
            margin-top: 50px;
            max-width: 600px;
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            width: 100%;
        }
        .alert {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="sidebar">
        <h2>Manager Profile</h2>
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
    <div class="container">
        <h2>Update Profile</h2>

        <!-- Success and Error Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Profile Update Form -->
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($profile['employee_name']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($profile['email']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Leave blank to keep current password">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</body>
</html>
