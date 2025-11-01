<?php
function sendShipmentSMS($phone, $message) {
    // Integrate your SMS API here.
    // For now, just simulate (for development):
    // file_put_contents('sms_log.txt', "$phone: $message\n", FILE_APPEND);
    return true;
}
?>
