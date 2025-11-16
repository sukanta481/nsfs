<?php
// Email config is now loaded by parent files
// require_once 'email_config_smtp.php';

/**
 * ========================================
 * CLIENT (CONSIGNEE/RECEIVER) EMAIL TEMPLATES
 * ========================================
 */

/**
 * Email template for "Out for Delivery" status - sent to CLIENT
 */
function getOutForDeliveryEmailTemplate($docket_data, $car_number, $driver_name) {
    $doc_no = htmlspecialchars($docket_data['doc_no']);
    $client_name = htmlspecialchars($docket_data['client_name']);
    $client_address = htmlspecialchars($docket_data['client_address']);
    $tracking_url = TRACKING_BASE_URL . urlencode($docket_data['doc_no']);

    $content = '
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; border-radius: 50px; font-size: 18px; font-weight: bold;">
            🚚 Out for Delivery
        </div>
    </div>

    <h2 style="color: #2c3e50; margin: 0 0 10px 0;">Hello ' . $client_name . ',</h2>
    <p style="color: #34495e; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
        Great news! Your shipment is <strong>out for delivery</strong> and will reach you soon!
    </p>

    <!-- Docket Details Box -->
    <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 5px;">
        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px;">📦 Shipment Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Docket Number:</td>
                <td style="padding: 8px 0; color: #2c3e50; font-weight: bold;">' . $doc_no . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Delivery Address:</td>
                <td style="padding: 8px 0; color: #2c3e50;">' . $client_address . '</td>
            </tr>
        </table>
    </div>

    <!-- Delivery Vehicle Info -->
    <div style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-left: 4px solid #4caf50; padding: 20px; margin: 20px 0; border-radius: 5px;">
        <h3 style="margin: 0 0 15px 0; color: #2e7d32; font-size: 18px;">🚛 Delivery Vehicle Information</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #558b2f; font-weight: 600;">Vehicle Number:</td>
                <td style="padding: 8px 0; color: #2e7d32; font-weight: bold; font-size: 18px;">' . htmlspecialchars($car_number) . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #558b2f; font-weight: 600;">Driver Name:</td>
                <td style="padding: 8px 0; color: #2e7d32; font-weight: bold;">' . htmlspecialchars($driver_name) . '</td>
            </tr>
        </table>
    </div>

    <p style="color: #34495e; font-size: 15px; line-height: 1.6; margin: 25px 0 20px 0;">
        Please keep the above vehicle details handy. Our delivery partner will arrive at your doorstep shortly.
    </p>

    <!-- Track Shipment Button -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="' . $tracking_url . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
            📍 Track Your Shipment
        </a>
    </div>

    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p style="margin: 0; color: #856404; font-size: 14px;">
            <strong>💡 Tip:</strong> Please ensure someone is available to receive the shipment. Keep your docket number ready for verification.
        </p>
    </div>

    <p style="color: #6c757d; font-size: 14px; margin: 30px 0 0 0;">
        Thank you for choosing North Super Fast Service!<br>
        <strong>Team NSFS</strong>
    </p>';

    return getEmailTemplate($content);
}

/**
 * Email template for "Delivered" status - sent to CLIENT
 */
function getDeliveredEmailTemplate($docket_data) {
    $doc_no = htmlspecialchars($docket_data['doc_no']);
    $client_name = htmlspecialchars($docket_data['client_name']);
    $tracking_url = TRACKING_BASE_URL . urlencode($docket_data['doc_no']);
    $delivery_date = !empty($docket_data['delivery_datetime']) ? date('d M Y, h:i A', strtotime($docket_data['delivery_datetime'])) : date('d M Y, h:i A');

    $content = '
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 60px; margin-bottom: 10px;">🎉</div>
        <div style="display: inline-block; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 15px 30px; border-radius: 50px; font-size: 20px; font-weight: bold;">
            ✓ Successfully Delivered!
        </div>
    </div>

    <h2 style="color: #2c3e50; margin: 0 0 10px 0;">Congratulations ' . $client_name . '!</h2>
    <p style="color: #34495e; font-size: 18px; line-height: 1.6; margin: 0 0 20px 0; font-weight: 600;">
        Your shipment has been <span style="color: #28a745;">successfully delivered</span>! 🎊
    </p>

    <!-- Success Box -->
    <div style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border: 2px solid #28a745; padding: 25px; margin: 25px 0; border-radius: 10px; text-align: center;">
        <div style="font-size: 50px; margin-bottom: 10px;">✅</div>
        <h3 style="margin: 0 0 10px 0; color: #155724; font-size: 20px;">Delivery Confirmed</h3>
        <p style="margin: 0; color: #155724; font-size: 16px;">
            <strong>Docket Number:</strong> ' . $doc_no . '<br>
            <strong>Delivered On:</strong> ' . $delivery_date . '
        </p>
    </div>

    <!-- Shipment Summary -->
    <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 5px;">
        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px;">📦 Shipment Summary</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Docket Number:</td>
                <td style="padding: 8px 0; color: #2c3e50; font-weight: bold;">' . $doc_no . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Recipient Name:</td>
                <td style="padding: 8px 0; color: #2c3e50;">' . $client_name . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Delivery Address:</td>
                <td style="padding: 8px 0; color: #2c3e50;">' . htmlspecialchars($docket_data['client_address']) . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Status:</td>
                <td style="padding: 8px 0; color: #28a745; font-weight: bold; font-size: 16px;">✓ DELIVERED</td>
            </tr>
        </table>
    </div>

    <!-- View Proof of Delivery -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="' . $tracking_url . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
            📄 View Delivery Details
        </a>
    </div>

    <div style="background: #e7f3ff; border: 1px solid #2196F3; padding: 20px; border-radius: 5px; margin: 25px 0; text-align: center;">
        <h4 style="margin: 0 0 10px 0; color: #0d47a1; font-size: 18px;">We Value Your Feedback!</h4>
        <p style="margin: 0; color: #1565c0; font-size: 15px;">
            How was your delivery experience? We would love to hear from you!
        </p>
    </div>

    <p style="color: #34495e; font-size: 15px; line-height: 1.6; margin: 25px 0;">
        Thank you for trusting <strong>North Super Fast Service</strong> with your shipment. We look forward to serving you again!
    </p>

    <p style="color: #6c757d; font-size: 14px; margin: 30px 0 0 0; text-align: center;">
        <strong>Team NSFS</strong><br>
        Making deliveries faster, one shipment at a time! 🚀
    </p>';

    return getEmailTemplate($content);
}

/**
 * Email template for "Delayed" status - sent to CLIENT
 */
function getDelayedEmailTemplate($docket_data, $delay_reason) {
    $doc_no = htmlspecialchars($docket_data['doc_no']);
    $client_name = htmlspecialchars($docket_data['client_name']);
    $tracking_url = TRACKING_BASE_URL . urlencode($docket_data['doc_no']);

    $content = '
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="display: inline-block; background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white; padding: 15px 30px; border-radius: 50px; font-size: 18px; font-weight: bold;">
            ⏰ Shipment Delayed
        </div>
    </div>

    <h2 style="color: #2c3e50; margin: 0 0 10px 0;">Dear ' . $client_name . ',</h2>
    <p style="color: #34495e; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
        We regret to inform you that there is a delay in your shipment delivery. We sincerely apologize for any inconvenience this may cause.
    </p>

    <!-- Docket Details -->
    <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 5px;">
        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px;">📦 Shipment Details</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Docket Number:</td>
                <td style="padding: 8px 0; color: #2c3e50; font-weight: bold;">' . $doc_no . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Delivery Address:</td>
                <td style="padding: 8px 0; color: #2c3e50;">' . htmlspecialchars($docket_data['client_address']) . '</td>
            </tr>
        </table>
    </div>

    <!-- Delay Reason -->
    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 5px;">
        <h3 style="margin: 0 0 15px 0; color: #856404; font-size: 18px;">⚠️ Reason for Delay</h3>
        <p style="margin: 0; color: #856404; font-size: 16px; font-weight: 600;">
            ' . htmlspecialchars($delay_reason) . '
        </p>
    </div>

    <p style="color: #34495e; font-size: 15px; line-height: 1.6; margin: 25px 0;">
        We are working diligently to resolve this issue and ensure your shipment reaches you as soon as possible. Our team is monitoring the situation closely.
    </p>

    <!-- Track Shipment -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="' . $tracking_url . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
            📍 Track Your Shipment
        </a>
    </div>

    <div style="background: #e3f2fd; border: 1px solid #2196F3; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p style="margin: 0; color: #0d47a1; font-size: 14px;">
            <strong>📞 Need Assistance?</strong> Please feel free to contact our customer support team for any queries or concerns.
        </p>
    </div>

    <p style="color: #6c757d; font-size: 14px; margin: 30px 0 0 0;">
        We apologize for the inconvenience and appreciate your patience.<br>
        <strong>Team NSFS</strong>
    </p>';

    return getEmailTemplate($content);
}

/**
 * ========================================
 * COMPANY (CONSIGNOR/SENDER) EMAIL TEMPLATES
 * ========================================
 */

/**
 * Email template for "Docket Created" - sent to COMPANY
 */
function getDocketCreatedEmailTemplate($docket_data) {
    $doc_no = htmlspecialchars($docket_data['doc_no']);
    $company_name = htmlspecialchars($docket_data['company_name']);
    $client_name = htmlspecialchars($docket_data['client_name']);
    $client_address = htmlspecialchars($docket_data['client_address']);
    $tracking_url = TRACKING_BASE_URL . urlencode($docket_data['doc_no']);
    $pickup_date = !empty($docket_data['pickup_datetime']) ? date('d M Y, h:i A', strtotime($docket_data['pickup_datetime'])) : date('d M Y, h:i A');

    $content = '
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="display: inline-block; background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); color: white; padding: 15px 30px; border-radius: 50px; font-size: 18px; font-weight: bold;">
            📝 Docket Created
        </div>
    </div>

    <h2 style="color: #2c3e50; margin: 0 0 10px 0;">Hello ' . $company_name . ',</h2>
    <p style="color: #34495e; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
        Your shipment docket has been successfully created and is ready for processing!
    </p>

    <!-- Docket Confirmation Box -->
    <div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: 2px solid #2196F3; padding: 25px; margin: 25px 0; border-radius: 10px; text-align: center;">
        <h3 style="margin: 0 0 10px 0; color: #0d47a1; font-size: 22px;">Docket Number</h3>
        <p style="margin: 0; color: #0d47a1; font-size: 32px; font-weight: bold; letter-spacing: 2px;">
            ' . $doc_no . '
        </p>
        <p style="margin: 10px 0 0 0; color: #1565c0; font-size: 14px;">
            Created on: ' . $pickup_date . '
        </p>
    </div>

    <!-- Shipment Details -->
    <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 5px;">
        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px;">📦 Shipment Information</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600; width: 40%;">Consignor (Sender):</td>
                <td style="padding: 8px 0; color: #2c3e50; font-weight: bold;">' . $company_name . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Consignee (Receiver):</td>
                <td style="padding: 8px 0; color: #2c3e50;">' . $client_name . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Delivery Address:</td>
                <td style="padding: 8px 0; color: #2c3e50;">' . $client_address . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Pickup Location:</td>
                <td style="padding: 8px 0; color: #2c3e50;">' . htmlspecialchars($docket_data['pickup_location'] ?? 'N/A') . '</td>
            </tr>';

    if (!empty($docket_data['weight'])) {
        $content .= '
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Weight:</td>
                <td style="padding: 8px 0; color: #2c3e50;">' . htmlspecialchars($docket_data['weight']) . ' kg</td>
            </tr>';
    }

    $content .= '
        </table>
    </div>

    <!-- Track Shipment -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="' . $tracking_url . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
            📍 Track Shipment
        </a>
    </div>

    <div style="background: #e8f5e9; border: 1px solid #4caf50; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p style="margin: 0; color: #2e7d32; font-size: 14px;">
            <strong>✓ Next Steps:</strong> Your shipment is now in our system and will be processed shortly. You will receive updates on the delivery status via email.
        </p>
    </div>

    <p style="color: #6c757d; font-size: 14px; margin: 30px 0 0 0;">
        Thank you for choosing North Super Fast Service!<br>
        <strong>Team NSFS</strong>
    </p>';

    return getEmailTemplate($content);
}

/**
 * Email template for "Delivered" status - sent to COMPANY
 */
function getCompanyDeliveredEmailTemplate($docket_data) {
    $doc_no = htmlspecialchars($docket_data['doc_no']);
    $company_name = htmlspecialchars($docket_data['company_name']);
    $client_name = htmlspecialchars($docket_data['client_name']);
    $tracking_url = TRACKING_BASE_URL . urlencode($docket_data['doc_no']);
    $delivery_date = !empty($docket_data['delivery_datetime']) ? date('d M Y, h:i A', strtotime($docket_data['delivery_datetime'])) : date('d M Y, h:i A');

    $content = '
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 50px; margin-bottom: 10px;">✅</div>
        <div style="display: inline-block; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 15px 30px; border-radius: 50px; font-size: 20px; font-weight: bold;">
            Delivery Completed
        </div>
    </div>

    <h2 style="color: #2c3e50; margin: 0 0 10px 0;">Hello ' . $company_name . ',</h2>
    <p style="color: #34495e; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
        We are pleased to inform you that your shipment has been <strong style="color: #28a745;">successfully delivered</strong> to the recipient!
    </p>

    <!-- Delivery Confirmation -->
    <div style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border: 2px solid #28a745; padding: 25px; margin: 25px 0; border-radius: 10px; text-align: center;">
        <div style="font-size: 40px; margin-bottom: 10px;">📦✓</div>
        <h3 style="margin: 0 0 10px 0; color: #155724; font-size: 20px;">Delivery Confirmed</h3>
        <p style="margin: 0; color: #155724; font-size: 16px;">
            <strong>Docket:</strong> ' . $doc_no . '<br>
            <strong>Delivered On:</strong> ' . $delivery_date . '
        </p>
    </div>

    <!-- Shipment Summary -->
    <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 5px;">
        <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 18px;">📋 Delivery Summary</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600; width: 35%;">Docket Number:</td>
                <td style="padding: 8px 0; color: #2c3e50; font-weight: bold;">' . $doc_no . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Recipient:</td>
                <td style="padding: 8px 0; color: #2c3e50;">' . $client_name . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Delivery Address:</td>
                <td style="padding: 8px 0; color: #2c3e50;">' . htmlspecialchars($docket_data['client_address']) . '</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6c757d; font-weight: 600;">Status:</td>
                <td style="padding: 8px 0; color: #28a745; font-weight: bold; font-size: 16px;">✓ DELIVERED</td>
            </tr>
        </table>
    </div>

    <!-- View Delivery Details -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="' . $tracking_url . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
            📄 View Proof of Delivery
        </a>
    </div>

    <div style="background: #e3f2fd; border: 1px solid #2196F3; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;">
        <p style="margin: 0; color: #0d47a1; font-size: 15px;">
            <strong>Thank you for your business!</strong><br>
            We look forward to serving you again for your future shipments.
        </p>
    </div>

    <p style="color: #6c757d; font-size: 14px; margin: 30px 0 0 0;">
        Best regards,<br>
        <strong>Team NSFS</strong>
    </p>';

    return getEmailTemplate($content);
}
?>
