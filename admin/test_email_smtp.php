<?php
/**
 * Test SMTP Email Configuration
 * Quick test to verify email sending works
 */

// Check if email_config_smtp.php exists
if (!file_exists(__DIR__ . '/email_config_smtp.php')) {
    die("❌ Email configuration file not found! Please run setup_smtp_email.php first.");
}

require 'email_config_smtp.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test SMTP Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            opacity: 0.9;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-weight: bold;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 20px 0;
        }
        .config-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Test SMTP Email Configuration</h1>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $test_email = filter_var($_POST['test_email'] ?? '', FILTER_VALIDATE_EMAIL);
            $test_name = htmlspecialchars($_POST['test_name'] ?? 'Test User');

            if (!$test_email) {
                echo '<div class="alert alert-error">❌ Invalid email address!</div>';
            } else {
                // Create test email content
                $subject = "🎉 SMTP Test Email from North Super Fast Service";
                $message = '
                    <h2 style="color:#667eea;">SMTP Email Test Successful!</h2>
                    <p>Hello <strong>' . $test_name . '</strong>,</p>
                    <p>If you are reading this email, your SMTP configuration is working perfectly!</p>
                    <div style="background:#e7f3ff;padding:15px;border-left:4px solid #2196F3;margin:20px 0;">
                        <h3 style="margin-top:0;">✅ Configuration Details:</h3>
                        <ul>
                            <li><strong>SMTP Host:</strong> ' . SMTP_HOST . '</li>
                            <li><strong>SMTP Port:</strong> ' . SMTP_PORT . '</li>
                            <li><strong>From Email:</strong> ' . EMAIL_FROM . '</li>
                            <li><strong>Test Date:</strong> ' . date('d M Y, h:i A') . '</li>
                        </ul>
                    </div>
                    <p style="color:#28a745;font-weight:bold;">Your email notification system is ready to use!</p>
                    <hr>
                    <p style="font-size:12px;color:#666;">This is an automated test email from North Super Fast Service.</p>
                ';

                // Send test email
                $result = sendEmail($test_email, $subject, getEmailTemplate($message), $test_name);

                if ($result) {
                    echo '<div class="alert alert-success">';
                    echo '✅ <strong>Email sent successfully!</strong><br>';
                    echo 'Check your inbox at: <strong>' . $test_email . '</strong><br>';
                    echo '<small>Don\'t forget to check spam/junk folder if you don\'t see it.</small>';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-error">';
                    echo '❌ <strong>Failed to send email!</strong><br>';
                    echo 'Please check:<br>';
                    echo '<ul>';
                    echo '<li>SMTP password is correct in email_config_smtp.php</li>';
                    echo '<li>PHPMailer is installed correctly</li>';
                    echo '<li>Error logs: <code>c:\xampp\php\logs\php_error_log</code></li>';
                    echo '</ul>';
                    echo '</div>';
                }
            }
        }
        ?>

        <div class="info-box">
            <strong>ℹ️ About This Test:</strong>
            <p>This tool sends a test email to verify your SMTP configuration is working correctly. Enter your email address below to receive a test message.</p>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="test_email">Your Email Address:</label>
                <input type="email" id="test_email" name="test_email" placeholder="your-email@example.com" required>
            </div>

            <div class="form-group">
                <label for="test_name">Your Name:</label>
                <input type="text" id="test_name" name="test_name" placeholder="Your Name" value="Test User">
            </div>

            <button type="submit">📨 Send Test Email</button>
        </form>

        <hr style="margin:30px 0;">

        <h2>📋 Current SMTP Configuration</h2>
        <div class="config-info">
            <table style="width:100%;">
                <tr>
                    <td style="padding:5px;"><strong>SMTP Host:</strong></td>
                    <td style="padding:5px;"><?= SMTP_HOST ?></td>
                </tr>
                <tr>
                    <td style="padding:5px;"><strong>SMTP Port:</strong></td>
                    <td style="padding:5px;"><?= SMTP_PORT ?> (SSL)</td>
                </tr>
                <tr>
                    <td style="padding:5px;"><strong>Username:</strong></td>
                    <td style="padding:5px;"><?= SMTP_USERNAME ?></td>
                </tr>
                <tr>
                    <td style="padding:5px;"><strong>From Email:</strong></td>
                    <td style="padding:5px;"><?= EMAIL_FROM ?></td>
                </tr>
                <tr>
                    <td style="padding:5px;"><strong>From Name:</strong></td>
                    <td style="padding:5px;"><?= EMAIL_FROM_NAME ?></td>
                </tr>
                <tr>
                    <td style="padding:5px;"><strong>Password:</strong></td>
                    <td style="padding:5px;">
                        <?php
                        if (SMTP_PASSWORD === 'YOUR_PASSWORD_HERE') {
                            echo '<span style="color:red;">⚠️ NOT CONFIGURED</span>';
                        } else {
                            echo '<span style="color:green;">✅ Configured (hidden)</span>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <hr style="margin:30px 0;">

        <h2>🛠️ Troubleshooting</h2>
        <div class="info-box">
            <h3 style="margin-top:0;">If email sending fails:</h3>
            <ol>
                <li><strong>Check SMTP Password:</strong> Edit <code>email_config_smtp.php</code> line 19</li>
                <li><strong>Verify PHPMailer:</strong> Ensure <code>admin/PHPMailer/src/</code> folder exists</li>
                <li><strong>Check Error Logs:</strong> <code>c:\xampp\php\logs\php_error_log</code></li>
                <li><strong>Test Credentials:</strong> Try logging into Hostinger webmail</li>
                <li><strong>Firewall:</strong> Ensure port 465 is not blocked</li>
            </ol>
        </div>

        <div style="margin-top:30px;text-align:center;color:#666;font-size:14px;">
            <p>Once the test is successful, you can delete this file for security.</p>
            <p><a href="SMTP_EMAIL_SETUP_GUIDE.md" target="_blank">View Full Setup Guide</a></p>
        </div>
    </div>
</body>
</html>
