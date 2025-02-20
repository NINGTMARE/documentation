<?php
include 'db_config.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Add or Update Department
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $user_id = $_POST['manager_id'];

    if (!empty($name) && !empty($user_id)) {
        if (isset($_POST['department_id'])) {
            // Update department
            $department_id = $_POST['department_id'];
            $stmt = $conn->prepare("UPDATE departments SET name = ?, description = ?, manager_id = (SELECT id FROM managers WHERE user_id = ?) WHERE id = ?");
            $stmt->bind_param("ssii", $name, $description, $user_id, $department_id);
            $stmt->execute();
            $stmt->close();
            $message = "Department updated successfully!";
        } else {
            // Check for duplicate department
            $stmt = $conn->prepare("SELECT COUNT(*) FROM departments WHERE name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $message = "A department with this name already exists!";
            } else {
                // Insert new department
                $stmt = $conn->prepare("INSERT INTO departments (name, description, manager_id) VALUES (?, ?, (SELECT id FROM managers WHERE user_id = ?))");
                $stmt->bind_param("ssi", $name, $description, $user_id);
                $stmt->execute();
                $stmt->close();
                $message = "Department added successfully!";
            }
        }
    } else {
        $message = "All fields are required!";
    }
}

// Delete Department
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "Department deleted successfully!";
}

// Fetch Managers (not assigned to any department)
$managers = $conn->query("SELECT id, name FROM users WHERE role = 'manager'")->fetch_all(MYSQLI_ASSOC);

// Fetch Departments
$departments = $conn->query("SELECT d.id, d.name, d.description, u.name AS manager_name, m.user_id AS manager_user_id FROM departments d LEFT JOIN managers m ON d.manager_id = m.id LEFT JOIN users u ON m.user_id = u.id")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
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
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: #fff;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: #e2e6ea;
        }
        .btn {
            border-radius: 30px;
            padding: 10px 20px;
            text-transform: uppercase;
            font-weight: bold;
            transition: all 0.3s ease-in-out;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-warning {
            background-color: #ffc107;
            border: none;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .badge-unassigned {
            background-color: #ffc107;
            color: #000;
            font-size: 0.9rem;
            border-radius: 20px;
            padding: 5px 10px;
        }
        .modal-content {
            border-radius: 10px;
            border: 0;
            background: #f8f9fa;
        }
        .modal-header {
            background: #007bff;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .modal-body input, .modal-body select, .modal-body textarea {
            border-radius: 8px;
            border: 1px solid #ccc;
            box-shadow: none;
            transition: all 0.3s;
        }
        .modal-body input:focus, .modal-body select:focus, .modal-body textarea:focus {
            border-color: #007bff;
        }
        .alert-info {
            background-color: #e9f7fe;
            color: #31708f;
            border-color: #bce8f1;
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            color: #007bff;
        }
        h3 {
            font-size: 1.75rem;
            color: #333;
            margin-bottom: 20px;
        }
        .search-box {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="bg-dark text-white p-3 rounded mb-4">
            <nav class="nav">
                <a class="nav-link text-white" href="admin.php"><i class="fas fa-home"></i> Dashboard</a>
                <a class="nav-link text-white" href="manageuser.php"><i class="fas fa-users"></i> Manage Users</a>
                <a class="nav-link text-white" href="report.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a class="nav-link text-white" href="settings.php"><i class="fas fa-cog"></i> Add Departments</a>
              <!--  <a class="nav-link text-white" href="profile.php"><i class="fas fa-user"></i> Profile</a> -->
            <a class="nav-link text-white" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
    </div>
<div class="container mt-5">
    <h1 class="text-center mb-4">Manage Departments</h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-info"><?= $message; ?></div>
    <?php endif; ?>

    <!-- Add Department -->
    <div class="card p-4 mb-4">
        <h3>Add New Department</h3>
        <form method="POST" id="departmentForm">
            <div class="mb-3">
                <label for="name" class="form-label">Department Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <!-- Assign Manager Dropdown -->
            <div class="mb-3">
                <label for="manager_id" class="form-label">Assign Manager</label>
                <select class="form-select" id="manager_id" name="manager_id" required>
                    <option value="">Select a Manager</option>
                    <?php foreach ($managers as $manager): ?>
                        <option value="<?= $manager['id']; ?>"><?= htmlspecialchars($manager['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="add_department" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Department
            </button>
        </form>
    </div>

    <!-- Existing Departments -->
    <div class="card p-4">
    <h3>Manage Departments</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mt-3" id="departmentsTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Department Name</th>
                <th>Description</th>
                <th>Assigned Manager</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($departments as $department): ?>
                <tr>
                    <td><?= $department['id']; ?></td>
                    <td><?= htmlspecialchars($department['name']); ?></td>
                    <td><?= htmlspecialchars($department['description']); ?></td>
                    <td>
                        <?= $department['manager_name'] ?: '<span class="badge badge-unassigned">Unassigned</span>'; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn" 
                                data-id="<?= $department['id']; ?>" 
                                data-name="<?= htmlspecialchars($department['name']); ?>" 
                                data-description="<?= htmlspecialchars($department['description']); ?>" 
                                data-manager="<?= $department['manager_user_id']; ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="?delete_id=<?= $department['id']; ?>" class="btn btn-sm btn-danger delete-btn" onclick="return confirm('Are you sure you want to delete this department?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="editDepartmentForm">
            <input type="hidden" name="department_id" id="editDepartmentId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editManager" class="form-label">Assign Manager</label>
                        <select class="form-select" id="editManager" name="manager_id" required>
                            <option value="">Select a Manager</option>
                            <?php foreach ($managers as $manager): ?>
                                <option value="<?= $manager['id']; ?>">
                                    <?= htmlspecialchars($manager['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Populate Edit Modal
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const description = button.getAttribute('data-description');
            const manager = button.getAttribute('data-manager');

            document.getElementById('editDepartmentId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;
            document.getElementById('editManager').value = manager;

            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
    });
</script>
</body>
</html>
