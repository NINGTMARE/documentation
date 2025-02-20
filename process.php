<?php
include('db_config.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'banned') {
                $_SESSION['error'] = "Your account has been banned. Please contact the administrator.";
                $_SESSION['activeForm'] = 'login';
                header('Location: index.php');
                exit();
            }

            if ($user['role'] === 'manager' && $user['status'] === 'pending') {
                $_SESSION['error'] = "Your account is pending approval. Please wait for admin confirmation.";
                $_SESSION['activeForm'] = 'login';
                header('Location: index.php');
                exit();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            switch ($user['role']) {
                case 'admin':
                    header('Location: admin.php');
                    break;
                case 'manager':
                    header('Location: manager.php');
                    break;
                case 'user':
                    header('Location: employee/dashboard.php');
                    break;
                default:
                    $_SESSION['error'] = "Invalid role. Contact admin.";
                    header('Location: index.php');
                    break;
            }
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            $_SESSION['activeForm'] = 'login';
            header('Location: index.php');
        }
    } 
    elseif ($action === 'register') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
        $role = trim($_POST['role']);

        // Set status based on role (pending for managers)
        $status = ($role === 'manager') ? 'pending' : 'approved';

        try {
            $sql = "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $email, $password, $role, $status);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Registration successful! Please log in.";
                $_SESSION['activeForm'] = 'login';
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $_SESSION['error'] = "A user with this email already exists.";
                $_SESSION['activeForm'] = 'login';
            } else {
                $_SESSION['error'] = "Error: Could not register user. Please try again.";
                $_SESSION['activeForm'] = 'login';
            }
        }
        header('Location: index.php');
    } else {
        $_SESSION['error'] = "Invalid action.";
        header('Location: index.php');
    }
    exit();
}
?>
