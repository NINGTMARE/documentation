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

// Fetch the team member's details
$member = $conn->query("SELECT * FROM users WHERE id = $member_id")->fetch_assoc();

if (!$member) {
    // Member not found
    $conn->close();
    header("Location: manage_team.php?message=Team member not found.");
    exit();
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $role = $conn->real_escape_string(trim($_POST['role']));
    $email = $conn->real_escape_string(trim($_POST['email']));

    // Validate input
    if (empty($name) || empty($role) || empty($email)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Update the team member's details
        $update = $conn->query("UPDATE users SET name = '$name', role = '$role', email = '$email' WHERE id = $member_id");

        if ($update) {
            $conn->close();
            header("Location: manage_team.php?message=Member updated successfully.");
            exit();
        } else {
            $error_message = "Failed to update team member. Please try again.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Team Member</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fc;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #004e92;
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #00bcd4;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0097a7;
        }

        .btn-danger {
            background-color: #f44336;
            border: none;
        }

        .btn-danger:hover {
            background-color: #d32f2f;
        }

        .form-control:focus {
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            border-color: #80bdff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Team Member</h1>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($member['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <input type="text" id="role" name="role" class="form-control" value="<?= htmlspecialchars($member['role']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($member['email']); ?>" required>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="manage_team.php" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
