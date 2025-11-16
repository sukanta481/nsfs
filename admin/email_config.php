<?php
/**
 * Email Configuration for Docket Notifications
 * Using PHP's built-in mail() function
 */

// Email settings
define('EMAIL_FROM', 'onestepup@northsuperfastservice.com');
define('EMAIL_FROM_NAME', 'North Super Fast Service');
define('EMAIL_REPLY_TO', 'onestepup@northsuperfastservice.com');

// Website URL for tracking links
define('TRACKING_BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/nsfs/track.php?doc_no=');

/**
 * Send email using PHP mail() function
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

    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">" . "\r\n";
    $headers .= "Reply-To: " . EMAIL_REPLY_TO . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    // Add recipient name to "To" header if provided
    if (!empty($recipient_name)) {
        $headers .= "To: " . $recipient_name . " <" . $to . ">" . "\r\n";
    }

    // Send email
    $result = mail($to, $subject, $html_body, $headers);

    if ($result) {
        error_log("Email sent successfully to: $to - Subject: $subject");
    } else {
        error_log("Failed to send email to: $to - Subject: $subject");
    }

    return $result;
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
