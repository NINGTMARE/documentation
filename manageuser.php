<?php
@include 'db_config.php'; // Database configuration

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch users and managers
$sql = "SELECT id, name, email, status, role FROM users WHERE role = 'user' OR role = 'manager'";
$result = $conn->query($sql);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $user_id = intval($_POST['user_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    $csrf_token = $_POST['csrf_token'];

    // Check CSRF token validity
    if ($csrf_token !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    // Update user status
    $update_sql = "UPDATE users SET status = '$new_status' WHERE id = $user_id";
    if ($conn->query($update_sql) === TRUE) {
        $_SESSION['message'] = "User status updated successfully!";
    } else {
        $_SESSION['message'] = "Error updating user: " . $conn->error;
    }

    // Redirect to avoid form re-submission
    header("Location: manageuser.php");
    exit();
}

// Handle add user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);
    $status = $conn->real_escape_string($_POST['status']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    $add_sql = "INSERT INTO users (name, email, role, status, password) VALUES ('$name', '$email', '$role', '$status', '$password')";
    if ($conn->query($add_sql) === TRUE) {
        $_SESSION['message'] = "User added successfully!";
    } else {
        $_SESSION['message'] = "Error adding user: " . $conn->error;
    }

    header("Location: manageuser.php");
    exit();
}

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    $delete_sql = "DELETE FROM users WHERE id = $user_id";
    if ($conn->query($delete_sql) === TRUE) {
        $_SESSION['message'] = "User deleted successfully!";
    } else {
        $_SESSION['message'] = "Error deleting user: " . $conn->error;
    }

    header("Location: manageuser.php");
    exit();
}

// Generate a CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f0f2f5;
        }

        .sidebar {
            flex: 1;
            max-width: 250px;
            background: linear-gradient(135deg, #6c63ff, #1e90ff);
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .sidebar h2 {
            margin-bottom: 20px;
            text-align: center;
            font-size: 24px;
            animation: fadeIn 1s ease-out;
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
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .sidebar ul li a:hover {
            color: #ffdd59;
        }

        .btn-custom {
            background-color: #00bcd4;
            color: #fff;
        }

        .btn-custom:hover {
            background-color: #0288d1;
        }

        .toast {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .modal-content {
            background-color: #343a40;
            color: #fff;
        }

        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar">
                <h2>Manage Employee & Manager</h2>
                <ul class="nav flex-column">
                    <li class="nav-item"><a href="admin.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a href="#" class="nav-link active"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li class="nav-item"><a href="report.php" class="nav-link"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cogs"></i> ADD Departments</a></li>
                   <!-- <li><a class="nav-link text-white" href="profile1.php"><i class="fas fa-user"></i> Profile</a></li> -->
                    <li class="nav-item"><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="container mt-4">

                    <!-- Notifications (Using Toast for real-time notifications) -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="toast align-items-center text-white bg-info border-0" role="alert" aria-live="assertive" aria-atomic="true" id="notificationToast">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <?= htmlspecialchars($_SESSION['message']); ?>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>

                    <!-- Add User Button -->
                    <button class="btn btn-custom mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-user-plus"></i> Add New User</button>

                    <!-- Users Table -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">Manage Users</div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered" id="userTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['id']); ?></td>
                                                <td><?= htmlspecialchars($row['name']); ?></td>
                                                <td><?= htmlspecialchars($row['email']); ?></td>
                                                <td><?= htmlspecialchars($row['status']); ?></td>
                                                <td>
                                                    <!-- Status Update Form -->
                                                    <form method="POST" onsubmit="return confirm('Are you sure you want to update this user?');" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['id']); ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                                        <select name="status" class="form-select form-select-sm">
                                                            <option value="approved" <?= $row['status'] == 'approved' ? 'selected' : ''; ?>>approved</option>
                                                            <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : ''; ?>>pending</option>
                                                            <option value="banned" <?= $row['status'] == 'banned' ? 'selected' : ''; ?>>Banned</option>
                                                        </select>
                                                        <button type="submit" name="update_status" class="btn btn-primary btn-sm mt-2"><i class="fas fa-sync"></i> Update</button>
                                                    </form>
                                                    <!-- Delete Button -->
                                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['id']); ?>">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm mt-2"><i class="fas fa-trash-alt"></i> Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No users found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel"><i class="fas fa-user-plus"></i> Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user">User</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="approved">approved</option>
                                <option value="pending">pending</option>
                                <option value="banned">Banned</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="add_user" class="btn btn-custom"><i class="fas fa-check"></i> Add User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Toast JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Show Toast notification -->
    <script>
        var myToast = document.getElementById('notificationToast');
        if (myToast) {
            var toast = new bootstrap.Toast(myToast);
            toast.show();
        }
    </script>
</body>
</html>
