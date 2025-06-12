<?php
require 'top_header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and validate input
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $cpass    = trim($_POST['confirm_password'] ?? '');

    if ($username === '' || $email === '' || $password === '' || $cpass === '') {
        $message = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
    } elseif ($password !== $cpass) {
        $message = 'Passwords do not match.';
    } else {
        // Check if username already exists
        $q = "SELECT * FROM tbl_administrator WHERE adminname='" . g_db_input($username) . "'";
        $result = g_db_query($q);
        if (g_db_num_rows($result) > 0) {
            $message = "Username already exists.";
        } else {
            // Hash password and insert user (using md5 for compatibility with your login system)
            $enc_pass = md5($password);
            $insert = "INSERT INTO tbl_administrator 
                        (adminname, adminpassword, admin_email, login_status) 
                       VALUES (
                        '" . g_db_input($username) . "',
                        '" . g_db_input($enc_pass) . "',
                        '" . g_db_input($email) . "',
                        1
                    )";
            if (g_db_query($insert)) {
                $message = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $message = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Signup</title>
    <style>
        body { background:#F7F7F7; font-family: Arial; }
        .signup-box { width:400px; margin:60px auto; background:white; border-radius:8px; box-shadow:0 2px 8px #aaa; padding:30px;}
        .signup-box h2 { margin-bottom:20px; }
        .signup-box input { width:100%; margin-bottom:14px; padding:8px 6px; border:1px solid #ccc; border-radius:4px;}
        .signup-box button { width:100%; padding:10px; background:#2185d0; color:white; border:none; border-radius:4px; font-size:16px;}
        .msg { color: #c00; margin-bottom: 14px; }
        .success { color: #090; }
    </style>
</head>
<body>
    <div class="signup-box">
        <h2>Admin Signup</h2>
        <?php if ($message) {
            $cls = (strpos($message, 'success') !== false || strpos($message, 'Login here') !== false) ? 'success' : 'msg';
            echo "<div class='$cls'>$message</div>";
        } ?>
        <form method="post" autocomplete="off">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <div style="margin-top:15px; font-size:14px;">Already registered? <a href="login.php">Login here</a></div>
    </div>
</body>
</html>
