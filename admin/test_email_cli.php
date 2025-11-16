<?php
/**
 * CLI Email Test Script
 * Tests SMTP email configuration from command line
 */

require __DIR__ . '/email_config_smtp.php';

echo "===========================================\n";
echo "   SMTP EMAIL CONFIGURATION TEST\n";
echo "===========================================\n\n";

// Display configuration
echo "Configuration:\n";
echo "- SMTP Host: " . SMTP_HOST . "\n";
echo "- SMTP Port: " . SMTP_PORT . "\n";
echo "- Username: " . SMTP_USERNAME . "\n";
echo "- From Email: " . EMAIL_FROM . "\n";
echo "- From Name: " . EMAIL_FROM_NAME . "\n\n";

// Test email address (you can change this)
$test_email = "onestepup@northsuperfastservice.com"; // Testing by sending to same address

echo "Sending test email to: $test_email\n\n";

// Create test email content
$subject = "SMTP Test Email from North Super Fast Service";
$message = '
    <h2 style="color:#667eea;">SMTP Email Test Successful!</h2>
    <p>Hello,</p>
    <p>If you are reading this email, your SMTP configuration is working perfectly!</p>
    <div style="background:#e7f3ff;padding:15px;border-left:4px solid #2196F3;margin:20px 0;">
        <h3 style="margin-top:0;">Configuration Details:</h3>
        <ul>
            <li><strong>SMTP Host:</strong> ' . SMTP_HOST . '</li>
            <li><strong>SMTP Port:</strong> ' . SMTP_PORT . '</li>
            <li><strong>From Email:</strong> ' . EMAIL_FROM . '</li>
            <li><strong>Test Date:</strong> ' . date('d M Y, h:i A') . '</li>
        </ul>
    </div>
    <p style="color:#28a745;font-weight:bold;">Your email notification system is ready to use!</p>
';

// Send test email
echo "Attempting to send email...\n";
$result = sendEmail($test_email, $subject, getEmailTemplate($message), 'Test User');

if ($result) {
    echo "\n✅ SUCCESS! Email sent successfully!\n";
    echo "Check your inbox at: $test_email\n";
    echo "Don't forget to check spam/junk folder if you don't see it.\n";
    echo "\n===========================================\n";
    echo "   EMAIL SYSTEM IS WORKING!\n";
    echo "===========================================\n";
} else {
    echo "\n❌ FAILED! Email could not be sent.\n";
    echo "Please check:\n";
    echo "1. SMTP password is correct in email_config_smtp.php\n";
    echo "2. PHPMailer is installed correctly\n";
    echo "3. Error logs in: c:\\xampp\\php\\logs\\php_error_log\n";
    echo "\n===========================================\n";
    echo "   EMAIL SYSTEM FAILED!\n";
    echo "===========================================\n";
}
?>
