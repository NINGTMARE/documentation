<?php
include('db_config.php');
session_start();

$activeForm = isset($_SESSION['activeForm']) ? $_SESSION['activeForm'] : 'login';
unset($_SESSION['activeForm']); // Clear after use
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registration Form</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(120deg, #89f7fe, #66a6ff);
            font-family: Arial, sans-serif;
        }

        .container {
            width: 800px;
            max-width: 100%;
            height: 500px;
            backdrop-filter: blur(10px);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            position: relative;
            transition: all 0.6s ease-in-out;
        }

        .form-container {
            display: flex;
            width: 200%;
            backdrop-filter: blur(10px);
            height: 100%;
            transition: transform 0.6s ease-in-out;
        }

        .form {
            width: 50%;
            padding: 40px;
            backdrop-filter: blur(10px);
            text-align: center;
            box-sizing: border-box;
            position: relative;
            z-index: 5;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
        }

        .form h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
            transition: color 0.3s ease;
        }

        .form input, .form select {
            width: 80%;
            background: transparent;
            backdrop-filter: blur(10px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            outline: none;
        }

         .form input:focus, .form select:focus {
            border-color: #007BFF;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }

         .form input.valid {
            border-color:  green;
            box-shadow: 0 0 2px green;
        }
        .form input.invalid {
            border-color: red;
            box-shadow: 0 0 2px red;
        }

        .form button {
            font-style: #fff;
            width: 80%;
            padding: 10px;
            margin-top: 20px;
            background: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .form button:hover {
            background: #0056b3;
            transform: translateY(-3px);
        }

        .switch-btn {
            margin-top: 15px;
            background: none;           
            font-style: #fff;
            border: none;
            color: #007BFF;
            font-size: 14px;
            cursor: pointer;
            outline: none;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .switch-btn:hover {
            font-style: #fff;
            color: #0056b3;
        }

        .forgot-password-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0);
    width: 350px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease-in-out, opacity 0.3s ease;
    opacity: 0;
    visibility: hidden;
    z-index: 20;
}

.forgot-password-container.active {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
    visibility: visible;
}

.forgot-password-form {
    text-align: center;
}

.forgot-password-form input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid;
    border-radius: 5px;
}
 .forgot-password-form input.valid {
            border-color: 2px solid green;
            box-shadow: 0 0 5px green;
        }
.forgot-password-form input.invalid {
            border-color: 2px solid red;
            box-shadow: 0 0 5px red;
        }
.forgot-password-form button {
    width: 100%;
    padding: 10px;
    background: #007BFF;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.forgot-password-form button:hover {
    background: #0056b3;
}
        .image-divider {
            position: absolute;
            top: 50%;
            left: 75%;
            transform: translate(-50%, -50%);
            width: 50%;
            height: 100%;
            background: url('b52b37124fdb79e5b77b166a5b1b0bb4.jpg') no-repeat center/cover;
            border-radius: 2%;
            z-index: 10;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.6s ease-in-out, box-shadow 0.3s ease;
        }

        .container.active .form-container {
            transform: translateX(-50%);
        }

        .container.active .image-divider {
            left: 25%;
        }

        /* When switching to login, move image to the right */
        .container.active-login .form-container {
            transform: translateX(0%);
        }

        .container.active-login .image-divider {
            right: 100%;
        }

        /* Floating shadow effect for the form */
        .container .form:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* Hover effect for the image */
        .image-divider:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        input[type="submit"]:active{
            opacity: 0.6;
        }
        @media (max-width: 600px) {
            .container {
                backdrop-filter: blur(10px);
                width: 90%;
                height: auto;
            }

            .form {
                backdrop-filter: blur(10px);
                padding: 30px;
            }

            .form input, .form select {
                font-size: 14px;
            }

            .form button {
                font-size: 14px;
            }

            .switch-btn {
                font-size: 12px;
            }

            .image-divider {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
<div class="container <?= $activeForm === 'register' ? 'active' : '' ?>" id="formContainer">
    <div class="form-container">
        <!-- Login Form -->
        <div class="form login">
            <h2>Login</h2>
            <?php if (isset($_SESSION['error']) && $activeForm === 'login'): ?>
                <div style="color: red;"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div style="color: green;"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); endif; ?>
            <?php if (isset($_SESSION['password_reset'])): ?>
    <div style="color: blue;"><?= $_SESSION['password_reset'] ?></div>
        <?php unset($_SESSION['password_reset']); endif; ?>
            <form action="process.php" method="POST">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email" required oninput="validateInput(this)">
                <input type="password" name="password" placeholder="Password" required oninput="validateInput(this)">
                <button type="submit">Login</button>
                <button type="button" class="switch-btn" onclick="toggleForm()">Don't have an account? Register</button>
                <button type="button" class="switch-btn" onclick="toggleForgotPassword()">Forgot Password?</button>
            </form>
        </div>

        <!-- Register Form -->
        <div class="form register">
            <h2>Register</h2>
            
            <form action="process.php" method="POST">
                <input type="hidden" name="action" value="register">
                <input type="text" name="name" placeholder="Full Name" required oninput="validateInput(this)">
                <input type="email" name="email" placeholder="Email" required oninput="validateInput(this)">
                <input type="password" name="password" placeholder="Password" required oninput="validateInput(this)">
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="user">User</option>
                </select>
                <button type="submit">Register</button>
                <button type="button" class="switch-btn" onclick="toggleForm()">Already have an account? Login</button>
            </form>
        </div>
    </div>
<!-- forgoten password -->
<div class="forgot-password-container" id="forgotPasswordContainer">
    <div class="forgot-password-form">
        <h2>Forgot Password?</h2>
        <form action="forgot_password.php" method="POST">
            <input type="text" name="name" placeholder="Enter your name" required oninput="validateInput(this)">
            <input type="email" name="email" placeholder="Enter your email" required oninput="validateInput(this)">
            <button type="submit">Find My Account</button>
            <button type="button" class="switch-btn" onclick="toggleForgotPassword()">Cancel</button>
        </form>
    </div>
</div>
    <!-- Image Divider -->
    <div class="image-divider"></div>
</div>

<script>
    const container = document.getElementById('formContainer');

    function toggleForm() {
        container.classList.toggle('active');
        container.classList.toggle('active-login');
            
    }
    function toggleForgotPassword() {
        document.getElementById('forgotPasswordContainer').classList.toggle('active');
    }
    function validateInput(input) {
        if (input.value.trim() === '') {
            input.classList.add('invalid');
            input.classList.remove('valid');
        } else {
            input.classList.add('valid');
            input.classList.remove('invalid');
        }
    }
</script>
</body>
</html>

   