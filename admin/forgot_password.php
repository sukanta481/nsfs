<?php
require 'top_header.php';
require 'functions/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');

    if (empty($identifier)) {
        $message = "Please enter your username or email.";
    } else {
        $conn = g_db_connect();
        $query = "SELECT * FROM tbl_administrator WHERE adminname='" . g_db_input($identifier) . "' OR admin_email='" . g_db_input($identifier) . "' LIMIT 1";
        $result = g_db_query($query);
        $admin = g_db_fetch_array($result);

        if ($admin && !empty($admin['admin_email'])) {
            // Generate reset token and expiration (30 minutes)
            $reset_token = bin2hex(random_bytes(16));
            $reset_expire = date('Y-m-d H:i:s', time() + 30*60);
            // Save to DB (add fields if not present)
            $update = "UPDATE tbl_administrator SET reset_token='$reset_token', reset_expire='$reset_expire' WHERE id=" . intval($admin['id']);
            g_db_query($update);

            // Email
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$reset_token";
            $to = $admin['admin_email'];
            $subject = "Password Reset Request";
            $body = "Hi {$admin['adminname']},<br><br>
                You requested a password reset. Click the link below to set a new password:<br>
                <a href='$reset_link'>$reset_link</a><br><br>
                If you did not request this, ignore this email.<br><br>
                Thanks,<br>North Super Fast Service";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";

            if (mail($to, $subject, $body, $headers)) {
                $message = "A password reset link has been sent to your email address.";
            } else {
                $message = "Failed to send reset email. Please try again.";
            }
        } else {
            $message = "No admin found with this username or email.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <?php if ($message) echo "<p style='color:green;'>$message</p>"; ?>
    <form method="post">
        <label>Username or Email:</label>
        <input type="text" name="identifier" required>
        <button type="submit">Send Reset Link</button>
    </form>
    <a href="login.php">Back to login</a>
</body>
</html>
