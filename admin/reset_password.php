<?php
require 'top_header.php';
require 'functions/database.php';

$message = '';
$show_form = false;

if (isset($_GET['token'])) {
    $token = g_db_input($_GET['token']);
    $query = "SELECT * FROM tbl_administrator WHERE reset_token='$token' AND reset_expire > NOW() LIMIT 1";
    $result = g_db_query($query);
    $admin = g_db_fetch_array($result);

    if ($admin && $admin['id']) {
        $show_form = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pass = trim($_POST['password'] ?? '');
            $cpass = trim($_POST['confirm_password'] ?? '');

            if (empty($pass) || strlen($pass) < 6) {
                $message = "Password must be at least 6 characters.";
            } elseif ($pass !== $cpass) {
                $message = "Passwords do not match.";
            } else {
                $new_hash = md5($pass);
                $update = "UPDATE tbl_administrator SET adminpassword='$new_hash', reset_token=NULL, reset_expire=NULL WHERE id=" . intval($admin['id']);
                g_db_query($update);
                $message = "Password updated! <a href='login.php'>Login here</a>";
                $show_form = false;
            }
        }
    } else {
        $message = "Invalid or expired token. Please request again.";
    }
} else {
    $message = "Invalid request.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    <?php if ($message) echo "<p style='color:red;'>$message</p>"; ?>
    <?php if ($show_form): ?>
    <form method="post">
        <label>New Password:</label>
        <input type="password" name="password" required><br>
        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required><br>
        <button type="submit">Set Password</button>
    </form>
    <?php endif; ?>
</body>
</html>
