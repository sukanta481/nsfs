<?php
/**
 * SMTP Email Configuration for Docket Notifications
 * Using PHPMailer with SMTP authentication
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer (adjust path if needed)
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// ========================================
// SMTP CONFIGURATION
// ========================================

// SMTP Server Settings (Hostinger)
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465); // SSL port
define('SMTP_USERNAME', 'onestepup@northsuperfastservice.com');
define('SMTP_PASSWORD', 'Tanmoy@0050'); // ⚠️ REPLACE WITH ACTUAL PASSWORD

// Email Settings
define('EMAIL_FROM', 'onestepup@northsuperfastservice.com');
define('EMAIL_FROM_NAME', 'North Super Fast Service');
define('EMAIL_REPLY_TO', 'onestepup@northsuperfastservice.com');

// Website URL for tracking links
define('TRACKING_BASE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/nsfs/track.php?doc_no=');

/**
 * Send email using PHPMailer with SMTP
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $html_body HTML body content
 * @param string $recipient_name Recipient name (optional)
 * @return bool Success status
 */
function sendEmail($to, $subject, $html_body, $recipient_name = '') {
    // Validate email
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email address: $to");
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL encryption
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Disable certificate verification (for local testing)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($to, $recipient_name);
        $mail->addReplyTo(EMAIL_REPLY_TO, EMAIL_FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->AltBody = strip_tags($html_body); // Plain text version

        // Send email
        $mail->send();
        error_log("Email sent successfully via SMTP to: $to - Subject: $subject");
        return true;

    } catch (Exception $e) {
        error_log("Email sending failed to: $to - Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Get email template wrapper (common header/footer)
 *
 * @param string $content Main content HTML
 * @return string Complete HTML email
 */
function getEmailTemplate($content) {
    return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>North Super Fast Service</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 20px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                                North Super Fast Service
                            </h1>
                            <p style="margin: 5px 0 0 0; color: #ffffff; font-size: 14px; opacity: 0.9;">
                                Fast & Reliable Delivery Service
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            ' . $content . '
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px 0; color: #6c757d; font-size: 13px;">
                                © ' . date('Y') . ' North Super Fast Service. All rights reserved.
                            </p>
                            <p style="margin: 0; color: #6c757d; font-size: 12px;">
                                This is an automated message, please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}
?>
