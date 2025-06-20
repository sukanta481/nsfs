<?php
// File: admin/includes/notifications/send_email.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__ . '/../../../vendor/phpmailer/phpmailer/src/PHPMailer.php');
require_once(__DIR__ . '/../../../vendor/phpmailer/phpmailer/src/SMTP.php');
require_once(__DIR__ . '/../../../vendor/phpmailer/phpmailer/src/Exception.php');

// Load SMTP config from .env
function get_smtp_config() {
    $env = parse_ini_file(__DIR__ . '/../../../.env');
    return [
        'host' => $env['SMTP_HOST'],
        'port' => $env['SMTP_PORT'],
        'username' => $env['SMTP_USER'],
        'password' => $env['SMTP_PASS'],
        'from_name' => $env['SMTP_FROM_NAME'],
        'from_email' => $env['SMTP_USER']
    ];
}


function sendShipmentEmail($to, $subject, $body) {
    $config = get_smtp_config();
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $config['port'];

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Mail error: " . $mail->ErrorInfo . "<br>";
        // Optionally log error: $mail->ErrorInfo
        return false;
    }
}

?>
