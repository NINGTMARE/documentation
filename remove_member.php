<?php
session_start();

// Ensure the user is logged in as a manager
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header("Location: index.php");
    exit();
}

// Include database configuration
include 'db_config.php';

// Validate the member ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_team.php?message=Invalid member ID.");
    exit();
}

$member_id = intval($_GET['id']);

// Fetch the member and validate they belong to the manager's department
$manager_id = $_SESSION['user_id'];
$query = $conn->query("
    SELECT dm.id
    FROM department_members dm
    JOIN departments d ON dm.department_id = d.id
    WHERE dm.user_id = $member_id
    AND d.manager_id = (SELECT id FROM managers WHERE user_id = $manager_id)
");

if ($query->num_rows === 0) {
    $conn->close();
    header("Location: manage_team.php?message=Unauthorized action or member not found.");
    exit();
}

// Remove the member from the department
$delete = $conn->query("DELETE FROM department_members WHERE user_id = $member_id");

if ($delete) {
    $conn->close();
    header("Location: manage_team.php?message=Team member removed successfully.");
    exit();
} else {
    $conn->close();
    header("Location: manage_team.php?message=Failed to remove team member.");
    exit();
}
?>
