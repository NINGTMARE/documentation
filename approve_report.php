<?php
session_start();

// Ensure the user is logged in as a manager
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header("Location: index.php");
    exit();
}

// Include database configuration
include 'db_config.php';

// Check if the report ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manager.php?message=Invalid report ID.");
    exit();
}

$report_id = intval($_GET['id']);

// Fetch the report details to validate the action
$report = $conn->query("SELECT * FROM reports WHERE id = $report_id")->fetch_assoc();

if (!$report) {
    // Report not found
    $conn->close();
    header("Location: manager.php?message=Report not found.");
    exit();
}

if ($report['status'] !== 'pending') {
    // Report is already processed
    $conn->close();
    header("Location: manager.php?message=Report already processed.");
    exit();
}

// Update the report's status to "approved"
$update = $conn->query("UPDATE reports SET status = 'approved', updated_at = NOW() WHERE id = $report_id");

$conn->close();

// Redirect back to the dashboard with a success message
if ($update) {
    header("Location: manager.php?message=Report approved successfully.");
} else {
    header("Location: manager.php?message=Failed to approve the report.");
}
exit();
?>