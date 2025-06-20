<?php
// Adjust the path if your send_email.php is in a different directory!
require_once(__DIR__ . '/admin/includes/notifications/send_email.php');

$sent = sendShipmentEmail('onestepup@northsuperfastservice.com', 'Test Email', 'This is a test from North Super Fast Service');

if ($sent) {
    echo 'Email sent!';
} else {
    echo 'Failed to send email.';
}
?>
