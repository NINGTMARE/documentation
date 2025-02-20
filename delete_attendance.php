<?php
// Include database connection
include 'db_config.php';

// Get attendance ID from the query parameter
$attendance_id = $_GET['id'];

// Delete the attendance record
$conn->query("DELETE FROM attendance WHERE id = $attendance_id");

// Redirect to dashboard
header("Location: manager.php");
exit();
?>
