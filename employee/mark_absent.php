<?php
include 'db_config.php';

// Get today's date
$today = date('Y-m-d');

// Find users who haven't checked in today
$sql = "
    INSERT INTO attendance (user_id, date, status)
    SELECT u.id, '$today', 'absent'
    FROM users u
    LEFT JOIN attendance a ON u.id = a.user_id AND a.date = '$today'
    WHERE a.id IS NULL
";

if ($conn->query($sql)) {
    echo "Absent status marked for users who didn't check in.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
