<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

// Database connection
include '../db_config.php';

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch the department the user belongs to
$dept_query = $conn->prepare("
    SELECT d.id AS department_id, d.name AS department_name
    FROM department_members dm
    JOIN departments d ON dm.department_id = d.id
    WHERE dm.user_id = ?
");
$dept_query->bind_param("i", $user_id);
$dept_query->execute();
$dept_result = $dept_query->get_result();

if ($dept_result->num_rows === 0) {
    die("<div class='alert alert-danger'>You are not associated with any department. Contact the administrator.</div>");
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_title = trim($_POST['report_title']);
    $report_description = trim($_POST['report_description']);
    $employee_id = $user_id; // Ensure employee_id is set

    if (empty($report_title) || empty($report_description)) {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO work_reports (employee_id, department_id, report_title, report_description)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $employee_id, $department_id, $report_title, $report_description);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Report submitted successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error submitting report. Please try again.</div>";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Work Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Submit Your Work Report</h2>
        <?= $message; ?>
        <form method="POST" class="shadow p-4 rounded bg-white">
            <div class="mb-3">
                <label for="report_title" class="form-label">Report Title</label>
                <input type="text" name="report_title" id="report_title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="report_description" class="form-label">Report Description</label>
                <textarea name="report_description" id="report_description" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Report</button>
        </form>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>
