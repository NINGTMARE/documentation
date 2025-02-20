<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch logged-in admin's name
$user_id = $_SESSION['user_id'];
$name = $conn->query("SELECT * FROM users WHERE id = '$user_id'")->fetch_assoc();

// Fetch user statistics
$total_users = $conn->query("SELECT COUNT(*) AS total_users FROM users")->fetch_assoc()['total_users'];
$pending_users = $conn->query("SELECT COUNT(*) AS pending_users FROM users WHERE status = 'pending'")->fetch_assoc()['pending_users'];
$approved_users = $conn->query("SELECT COUNT(*) AS approved_users FROM users WHERE status = 'approved'")->fetch_assoc()['approved_users'];
$banned_users = $conn->query("SELECT COUNT(*) AS banned_users FROM users WHERE status = 'banned'")->fetch_assoc()['banned_users'];

// Fetch role-based statistics
$admin_count = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'")->fetch_assoc()['count'] ?? 0;
$manager_count = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'manager'")->fetch_assoc()['count'] ?? 0;
$employee_count = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetch_assoc()['count'] ?? 0;

// Handle approval or rejection of pending managers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $conn->query("UPDATE users SET status = 'approved' WHERE id = $user_id");
        $_SESSION['success'] = "User approved successfully.";
    } elseif ($action === 'reject') {
        $conn->query("UPDATE users SET status = 'banned' WHERE id = $user_id");
        $_SESSION['success'] = "User rejected successfully.";
    }
    header("Location: admin.php");
    exit();
}

// Fetch pending managers
$pending_managers = $conn->query("SELECT * FROM users WHERE role = 'manager' AND status = 'pending'");

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
       body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fc;
            color: #343a40;
            transition: background-color 0.3s, color 0.3s;
        }

        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }

        /* Sidebar Styling */
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

        /* Main Content Styling */
        .main-content {
            flex: 3;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1.5s ease-out;
        }

        .main-content h1 {
            margin-bottom: 20px;
            font-size: 28px;
            animation: fadeIn 1s ease-out;
        }

        .notifications {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .notifications-dropdown {
            position: relative;
/*            display: inline-block;*/
            margin-left: 85%;
            margin-bottom: 5%;
        }

        .notifications-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            z-index: 1000;
            width: 300px;
        }

        .notifications-dropdown:hover .notifications-dropdown-content {
            display: block;
            animation: fadeIn 0.5s ease-out;
        }

        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #f1f1f1;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background-color: #f9f9f9;
        }

        /* Card Styling */
        .card {
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .card h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 36px;
            font-weight: bold;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        #roleChart {
            margin-top: 20px;
        }
        }
        .notification-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: red;
            color: white;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="manageuser.php"><i class="fas fa-users"></i> Manage Users</a></li>
                <li><a href="report.php"><i class="fas fa-file-alt"></i> Reports</a></li>
                <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>


        <!-- Main Content -->
        <div class="main-content">
            <h1>Welcome, <?= htmlspecialchars($name['name']); ?>!</h1>

            <!-- Notification Dropdown -->
            <div class="notifications-dropdown">
                <button class="btn btn-primary position-relative">
                    Notifications <i class="fas fa-bell"></i>
                    <?php if ($pending_managers->num_rows > 0): ?>
                        <span class="notification-badge"><?= $pending_managers->num_rows; ?></span>
                    <?php endif; ?>
                </button>
                <div class="notifications-dropdown-content">
                    <?php if ($pending_managers->num_rows > 0): ?>
                        <?php while ($row = $pending_managers->fetch_assoc()): ?>
                            <div class="notification-item">
                                <strong><?= htmlspecialchars($row['name']); ?></strong> (<?= htmlspecialchars($row['email']); ?>) requested approval.
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="notification-item">No new notifications.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <h3>Total Users</h3>
                        <p><?= $total_users; ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <h3>Pending Users</h3>
                        <p><?= $pending_users; ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <h3>Approved Users</h3>
                        <p><?= $approved_users; ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <h3>Banned Users</h3>
                        <p><?= $banned_users; ?></p>
                    </div>
                </div>
            </div>


            <!-- Role Histogram -->
            <h3 class="mt-5">User Role Distribution</h3>
            <canvas id="roleChart"></canvas>

            <!-- <!-- Pending Manager Approval 
            <h3 class="mt-5">Pending Manager Approval</h3>
            <div class="table-responsive">
               <div class="table-responsive">
    <form action="approve_user.php" method="POST">
    <table class="table table-bordered table-hover text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Action</th>
                <th>Note (Optional)</th>
            </tr>
        </thead>
        <tbody>
            <//?php if ($pending_managers->num_rows > 0): ?>
                <//?php while ($row = $pending_managers->fetch_assoc()): ?>
                    <tr>
                        <td><//?= $row['id']; ?></td>
                        <td><//?= htmlspecialchars($row['name']); ?></td>
                        <td><//?= htmlspecialchars($row['email']); ?></td>
                        <td><span class="badge bg-warning text-dark"><//?= htmlspecialchars($row['status']); ?></span></td>
                        <td>
                            <input type="hidden" name="manager_id" value="<//?= $row['id']; ?>">
                            <select name="action" class="form-select" required>
                                <option value="approve">Approve</option>
                                <option value="reject">Reject</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="note" class="form-control" placeholder="Add a note (optional)">
                        </td>
                    </tr>
                <//?php endwhile; ?>
            <//?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No pending managers found.</td>
                </tr>
            <//?php endif; ?>
        </tbody>
    </table>
    <div class="text-center">
        <button type="submit" class="btn btn-primary">Submit Approval</button>
    </div>
</form>
 -->
</div>

        </div>
    </div>

    <script>
        const ctx = document.getElementById('roleChart').getContext('2d');
        const roleChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Admins', 'Managers', 'Employees'],
                datasets: [{
                    label: 'Number of Users',
                    data: [<?= $admin_count; ?>, <?= $manager_count; ?>, <?= $employee_count; ?>],
                    backgroundColor: ['#1e90ff', '#28a745', '#ffc107'],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        function approveUser(userId) {
            if (confirm('Are you sure you want to approve this user?')) {
                window.location.href = `approve_user.php?id=${userId}`;
            }
        }

        function rejectUser(userId) {
            if (confirm('Are you sure you want to reject this user?')) {
                window.location.href = `reject_user.php?id=${userId}`;
            }
        }
    </script>
</body>
</html>
