<?php
session_start();

// Ensure the user is logged in as a manager
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header("Location: index.php");
    exit();
}

// Include database configuration
include 'db_config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $department_id = $_POST['department_id'] ?? null;

    // Validate inputs
    if (!$user_id || !$department_id) {
        $_SESSION['error_message'] = "Please select both a user and a department.";
        header("Location: manage_team.php");
        exit();
    }

    // Check if the user already belongs to a department
    $result = $conn->query("
        SELECT * FROM department_members WHERE user_id = $user_id
    ");

    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "This user is already assigned to a department.";
        header("Location: manage_team.php");
        exit();
    }

    // Assign user to department
    $stmt = $conn->prepare("INSERT INTO department_members (user_id, department_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $department_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User successfully added to the department.";
    } else {
        $_SESSION['error_message'] = "Failed to add user to the department. Please try again.";
    }

    $stmt->close();
}

// Close DB connection
$conn->close();

// Redirect back to the manage team page
header("Location: manage_team.php");
exit();
?>
