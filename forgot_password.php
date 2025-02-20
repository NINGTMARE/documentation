<?php
session_start();
include('db_config.php'); // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Check if user exists
    $query = "SELECT id FROM users WHERE name = ? AND email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $name, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a new random password
        $newPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%'), 0, 10);
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update password in the database
        $stmt->close();
        $updateQuery = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();
        $stmt->close();

        // Store new password in session for display
        $_SESSION['password_reset'] = "Your new password is: <strong>$newPassword</strong>";

    } else {
        $_SESSION['error'] = "No account found with this name and email.";
    }

    $conn->close();
    header("Location: index.php");
    exit();
}
?>