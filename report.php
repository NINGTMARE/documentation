<?php
include('db_config.php');
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Filters
$whereClauses = [];
$params = [];
if (!empty($_GET['name'])) {
    $whereClauses[] = "name LIKE ?";
    $params[] = '%' . $_GET['name'] . '%';
}
if (!empty($_GET['status'])) {
    $whereClauses[] = "status = ?";
    $params[] = $_GET['status'];
}
if (!empty($_GET['role'])) {
    $whereClauses[] = "role = ?";
    $params[] = $_GET['role'];
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Construct the SQL
$sql = "SELECT id, name, email, role, status, created_at FROM users";
if ($whereClauses) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}
$sql .= " LIMIT $offset, $limit";

$stmt = $conn->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Total records
$totalCountQuery = "SELECT COUNT(*) as total FROM users";
if ($whereClauses) {
    $totalCountQuery .= " WHERE " . implode(' AND ', $whereClauses);
}
$countStmt = $conn->prepare($totalCountQuery);
if ($params) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalCount = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalCount / $limit);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .status-active { color: #28a745; font-weight: bold; }
        .status-inactive { color: #ffc107; font-weight: bold; }
        .status-banned { color: #dc3545; font-weight: bold; }
        .table th { background-color: #343a40; color: #fff; }
    
    /* Gradient Backgrounds */
    .card-header {
        background: linear-gradient(135deg, #ff7eb3, #ff758c);
        color: #fff;
        font-weight: bold;
    }

    .btn-success {
        background: linear-gradient(135deg, #1dd1a1, #10ac84);
        border: none;
        color: #fff;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #10ac84, #1dd1a1);
        color: #fff;
    }

    /* Sidebar Colors */
    .nav-link {
        color: #fff !important;
    }

    .nav-link:hover {
        background: linear-gradient(135deg, #ff758c, #ff7eb3);
        color: #fff;
    }

    .bg-dark {
        background: linear-gradient(135deg, #6a11cb, #2575fc);
    }

    /* Status Indicators */
    .status-active {
        background: #00b894;
        color: #2575fc;
        padding: 5px 10px;
        border-radius: 12px;
        font-weight: bold;
    }

    .status-inactive {
        background: #fdcb6e;
        color: #fff;
        padding: 5px 10px;
        border-radius: 12px;
        font-weight: bold;
    }

    .status-banned {
        background: #e74c3c;
        color: #fff;
        padding: 5px 10px;
        border-radius: 12px;
        font-weight: bold;
    }

    /* Table Row Hover */
    .table-striped tbody tr:hover {
        background: #f8f9fa;
        transform: scale(1.01);
        transition: 0.3s;
    }

    /* Pagination Active Link */
    .page-item.active .page-link {
        background: linear-gradient(135deg, #ff758c, #ff7eb3);
        border-color: transparent;
        color: #fff;
    }

    .page-link:hover {
        background: #e0e0e0;
        color: #343a40;
    }
</style>

</head>

<body>
    <div class="container mt-5">
        <!-- Sidebar Navigation -->
        <div class="bg-dark text-white p-3 rounded mb-4">
            <nav class="nav">
                <a class="nav-link text-white" href="admin.php"><i class="fas fa-home"></i> Dashboard</a>
                <a class="nav-link text-white" href="manageuser.php"><i class="fas fa-users"></i> Manage Users</a>
                <a class="nav-link text-white" href="report.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a class="nav-link text-white" href="settings.php"><i class="fas fa-cog"></i> ADD Departments</a>
              <!--  <a class="nav-link text-white" href="profile1.php"><i class="fas fa-user"></i> Profile</a> -->
            <a class="nav-link text-white" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Filter Users</div>
            <div class="card-body">
                <form method="GET" action="report.php" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control" placeholder="Search by name">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">Filter by status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="banned">Banned</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="role" class="form-select">
                            <option value="">Filter by role</option>
                            <option value="user">User</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-search"></i> Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- User Table -->
        <div class="card">
            <div class="card-header bg-dark text-white">User Report</div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['name'] ?></td>
                                    <td><?= $row['email'] ?></td>
                                    <td><?= ucfirst($row['role']) ?></td>
                                    <td class="status-<?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></td>
                                    <td><?= $row['created_at'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</body>
