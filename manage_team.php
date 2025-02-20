<?php
session_start();

// Ensure the user is logged in as a manager
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header("Location: index.php");
    exit();
}

// Include database configuration
include 'db_config.php';

// Fetch manager's information
$manager_id = $_SESSION['user_id'];

// Fetch manager's department(s)
$departments = $conn->query("
    SELECT * FROM departments 
    WHERE manager_id = (SELECT id FROM managers WHERE user_id = '$manager_id')
")->fetch_all(MYSQLI_ASSOC);

// Fetch team members with department info
$team_members = $conn->query("
    SELECT 
        u.id AS user_id, 
        u.name AS user_name, 
        u.email AS user_email, 
        u.role AS user_role, 
        d.name AS department_name 
    FROM 
        users u
    JOIN 
        department_members dm ON u.id = dm.user_id
    JOIN 
        departments d ON dm.department_id = d.id
    WHERE 
        d.manager_id = (SELECT id FROM managers WHERE user_id = '$manager_id')
")->fetch_all(MYSQLI_ASSOC);

// Fetch unassigned users with the role "user"
$unassigned_users = $conn->query("
    SELECT id, name, email 
    FROM users 
    WHERE role = 'user' 
    AND id NOT IN (SELECT user_id FROM department_members)
")->fetch_all(MYSQLI_ASSOC);

// Close DB connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Team</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fc;
            color: #495057;
        }
        .sidebar {
            fflex: 1;
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
            width: 80%;
            margin-left: 260px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        h1, h3 {
            color: #004e92;
        }
        .btn-primary, .btn-success {
            background-color: #00bcd4;
            border: none;
        }
        .btn-primary:hover, .btn-success:hover {
            background-color: #0097a7;
        }
        .btn-danger {
            background-color: #f44336;
            border: none;
        }
        .btn-danger:hover {
            background-color: #d32f2f;
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
    <div class="container my-5">
        <h1 class="text-center mb-4">Manage Team</h1>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Add Team Member Button -->
        <div class="mb-4 text-end">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                Add New Member
            </button>
        </div>

        <!-- Team Members Table -->
        <div class="card p-4">
            <h3>Team Members</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($team_members as $member): ?>
                            <tr>
                                <td><?= htmlspecialchars($member['user_name']); ?></td>
                                <td><?= htmlspecialchars($member['user_role']); ?></td>
                                <td><?= htmlspecialchars($member['user_email']); ?></td>
                                <td><?= htmlspecialchars($member['department_name']); ?></td>
                                <td>
                                    <a href="edit_member.php?id=<?= $member['user_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="remove_member.php?id=<?= $member['user_id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to remove this member?');">
                                       Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($team_members)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No team members found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="add_member.php" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMemberModalLabel">Add New Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="user" class="form-label">User</label>
                            <select class="form-select" id="user" name="user_id" required>
                                <option value="" disabled selected>Select a user</option>
                                <?php foreach ($unassigned_users as $user): ?>
                                    <option value="<?= $user['id']; ?>"><?= htmlspecialchars($user['name']); ?> (<?= htmlspecialchars($user['email']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department_id" required>
                                <option value="" disabled selected>Select a department</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?= $department['id']; ?>"><?= htmlspecialchars($department['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Add Member</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
