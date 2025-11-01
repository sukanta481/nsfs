<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
// echo "You are on forgot_password.php";
// exit;

require 'top_header.php';
// require 'includes/functions/database.php';

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
            $headers .= "From: noreply@northsuperfastservice.com\r\n"; // Add this line


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
<?php
$message = $message ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS (update path if needed) -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body {
            background: #f7f7f7;
        }
        .login_content {
            background: #fff;
            padding: 35px 30px 30px 30px;
            margin: 80px auto 0 auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ccc;
            max-width: 400px;
        }
        .logo-img {
            max-width: 120px;
            margin-bottom: 18px;
        }
        .form-title {
            margin-bottom: 25px;
            font-size: 26px;
            color: #5a5a5a;
            text-align: center;
            font-weight: 500;
        }
        .btn-primary {
            width: 100%;
            border-radius: 4px;
        }
        .footer {
            margin-top: 35px;
            text-align: center;
            color: #888;
        }
        .back-link {
            display: block;
            margin-top: 14px;
            text-align: right;
        }
        .msg-success { color: #389e5a; }
        .msg-error { color: #c0392b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login_content">
            <div class="text-center">
                <img src="images/logo.png" alt="North Super Fast Service" class="logo-img">
            </div>
            <div class="form-title">Forgot Password</div>
            <?php if ($message): ?>
                <div class="msg-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <input type="text" name="identifier" class="form-control" placeholder="Username or Email" required>
                </div>
                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </form>
            <a class="back-link" href="login.php">Back to login</a>
        </div>
        <div class="footer">
            &copy;<?= date('Y'); ?> All Rights Reserved. North Super Fast Service
        </div>
    </div>
</body>
</html>

