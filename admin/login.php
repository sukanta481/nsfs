<?php
session_name('pro');
session_start();
require 'conn.php';

$message = '';

// If already logged in, redirect
if (isset($_SESSION['admin']) && !empty($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

// Include login processing code
require_once 'includes/script/login_code.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 10;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 100;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 5px;
        }

        .toggle-password:hover {
            color: #764ba2;
            background: rgba(102, 126, 234, 0.1);
        }

        .toggle-password i {
            position: static !important;
            transform: none !important;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control.with-toggle {
            padding-right: 45px;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102,126,234,0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }

        .footer-links {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e1e1;
        }

        .footer-links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            display: block;
            margin: 8px 0;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .version-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
        }

        .logo-section {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e1e1;
        }

        .logo-section img {
            max-width: 200px;
            height: auto;
            margin-bottom: 10px;
        }

        .logo-section p {
            color: #666;
            font-size: 12px;
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            body {
                padding: 15px;
            }

            .login-container {
                max-width: 100%;
            }

            .login-header {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            .login-body {
                padding: 30px 20px;
            }

            .btn-login {
                padding: 12px;
                font-size: 15px;
            }
        }

        @media (max-width: 400px) {
            .login-header h1 {
                font-size: 22px;
            }

            .login-body {
                padding: 25px 15px;
            }

            .form-control {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-user-shield"></i> Admin Panel</h1>
            <p>Sign in to access the admin dashboard</p>
            <span class="version-badge">Legacy Login System</span>
        </div>

        <div class="login-body">
            <?php if (!empty($message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label for="user"><i class="fas fa-user"></i> Username</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="user" id="user" class="form-control"
                               placeholder="Enter your username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" class="form-control with-toggle"
                               placeholder="Enter your password" required>
                        <span class="toggle-password" id="togglePassword" title="Show password">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <input type="hidden" name="Login" value="Login">

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="footer-links">
                <a href="forgot_password.php"><i class="fas fa-key"></i> Forgot Password?</a>
                <a href="login_new.php"><i class="fas fa-arrow-right"></i> Use New Login System</a>
            </div>

            <div class="logo-section">
                <img src="images/logo.png" alt="North Super Fast Service">
                <p>© <?php echo date('Y'); ?> All Rights Reserved. North Super Fast Service</p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                // Toggle the type attribute
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle the icon
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');

                // Update title
                this.setAttribute('title', type === 'password' ? 'Show password' : 'Hide password');
            });
        }
    </script>
</body>
</html>