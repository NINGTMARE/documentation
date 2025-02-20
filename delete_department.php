<?php
session_start();

// Ensure the user is logged in as a manager
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header("Location: index.php");
    exit();
}

// Include database configuration
include 'db_config.php';

// Check if the department ID is provided via GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $department_id = intval($_GET['id']);
    $manager_id = $_SESSION['user_id'];

    // Verify that the department belongs to the manager
    $stmt = $conn->prepare("
        SELECT id FROM departments 
        WHERE id = ? AND manager_id = (SELECT id FROM managers WHERE user_id = ?)
    ");
    $stmt->bind_param("ii", $department_id, $manager_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Department belongs to the manager, proceed with deletion
        $delete_stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
        $delete_stmt->bind_param("i", $department_id);

        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Department deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete the department. Please try again.";
        }

        $delete_stmt->close();
    } else {
        $_SESSION['error'] = "Invalid department or you do not have permission to delete this department.";
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "No valid department ID provided.";
}

// Redirect back to the manage departments page
$conn->close();
header("Location: manage_departments.php");
exit();
?>
