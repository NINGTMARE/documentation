<?php
// Include database connection
include 'db_config.php';

// Get attendance ID from the query parameter
$attendance_id = $_GET['id'];

// Fetch the attendance record
$attendance = $conn->query("
    SELECT a.id, a.date, a.status, u.name AS team_member_name 
    FROM attendance a
    JOIN users u ON a.user_id = u.id
    WHERE a.id = $attendance_id
")->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = $_POST['status'];
    $conn->query("UPDATE attendance SET status = '$new_status' WHERE id = $attendance_id");
    header("Location: manager.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Attendance</title>
</head>
<body>
    <form method="POST">
        <h3>Edit Attendance for <?= $attendance['team_member_name']; ?></h3>
        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="Present" <?= $attendance['status'] == 'Present' ? 'selected' : ''; ?>>Present</option>
            <option value="Absent" <?= $attendance['status'] == 'Absent' ? 'selected' : ''; ?>>Absent</option>
            <option value="On Leave" <?= $attendance['status'] == 'On Leave' ? 'selected' : ''; ?>>On Leave</option>
        </select>
        <button type="submit">Update</button>
    </form>
</body>
</html>
