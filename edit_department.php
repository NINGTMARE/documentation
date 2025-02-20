<?php
session_start();

// Ensure the user is logged in as a manager
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header("Location: index.php");
    exit();
}

// Include database configuration
include 'db_config.php';

// Fetch manager's ID
$manager_id = $_SESSION['user_id'];

// Check if department ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_departments.php");
    exit();
}

$department_id = intval($_GET['id']);

// Fetch department details
$stmt = $conn->prepare("
    SELECT * 
    FROM departments 
    WHERE id = ? 
    AND manager_id = (SELECT id FROM managers WHERE user_id = ?)
");
$stmt->bind_param("ii", $department_id, $manager_id);
$stmt->execute();
$result = $stmt->get_result();
$department = $result->fetch_assoc();

// Redirect if department not found
if (!$department) {
    header("Location: manage_departments.php");
    exit();
}

// Handle form submission for updating the department
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (!empty($name) && !empty($description)) {
        $update_stmt = $conn->prepare("
            UPDATE departments 
            SET name = ?, description = ? 
            WHERE id = ? 
            AND manager_id = (SELECT id FROM managers WHERE user_id = ?)
        ");
        $update_stmt->bind_param("ssii", $name, $description, $department_id, $manager_id);

        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Department updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update the department.";
        }

        header("Location: manage_departments.php");
        exit();
    } else {
        $_SESSION['error'] = "Please fill out all required fields.";
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Department</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h1 class="text-center mb-4">Edit Department</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="edit_department.php?id=<?= htmlspecialchars($department_id); ?>" method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Department Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($department['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($department['description']); ?></textarea>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Update Department</button>
            <a href="manage_departments.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
