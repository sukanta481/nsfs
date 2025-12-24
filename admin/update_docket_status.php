<?php
require 'check_auth.php';
require 'conn.php';
require 'email_config_smtp.php';
require 'email_templates.php';

// Handle both AJAX/JSON API requests and regular form submissions
// Check for AJAX request via XMLHttpRequest header or fetch API
$is_ajax_request = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
    (isset($_POST['ajax']) && $_POST['ajax'] == '1')
);

// For AJAX requests, return plain text or JSON (no HTML page layout)
if ($is_ajax_request) {
    header('Content-Type: text/plain; charset=UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($is_ajax_request) {
        echo 'Error: Invalid request method';
    } else {
        header("Location: delivery_status.php?error=Invalid request method");
    }
    exit;
}

// Get form data
$is_bulk = isset($_POST['is_bulk']) && $_POST['is_bulk'] == '1';
$bulk_docket_ids = isset($_POST['bulk_docket_ids']) ? $_POST['bulk_docket_ids'] : '';

// For bulk update, get array of docket IDs
if ($is_bulk && !empty($bulk_docket_ids)) {
    $docket_ids = array_map('intval', explode(',', $bulk_docket_ids));
    $docket_id = 0; // Not used for bulk
} else {
    $docket_id = intval($_POST['docket_id'] ?? 0);
    $docket_ids = [$docket_id];
}

$new_status = mysqli_real_escape_string($conn, trim($_POST['status'] ?? ''));
$current_status = mysqli_real_escape_string($conn, trim($_POST['current_status'] ?? ''));
$remarks = mysqli_real_escape_string($conn, trim($_POST['remarks'] ?? ''));
$location = isset($_POST['location']) ? mysqli_real_escape_string($conn, trim($_POST['location'])) : '';
$status_date = isset($_POST['status_date']) ? mysqli_real_escape_string($conn, $_POST['status_date']) : NULL;

// Car and driver can be manual input or from database
$car_number = isset($_POST['car_number']) ? mysqli_real_escape_string($conn, trim($_POST['car_number'])) : NULL;
$car_id = isset($_POST['car_id']) && !empty($_POST['car_id']) ? intval($_POST['car_id']) : NULL;
$driver_name = isset($_POST['driver_name']) ? mysqli_real_escape_string($conn, trim($_POST['driver_name'])) : NULL;
$driver_id = isset($_POST['driver_id']) && !empty($_POST['driver_id']) ? intval($_POST['driver_id']) : NULL;
$driver_phone = isset($_POST['driver_phone']) ? mysqli_real_escape_string($conn, trim($_POST['driver_phone'])) : NULL;

$delay_reason = isset($_POST['delay_reason']) ? mysqli_real_escape_string($conn, $_POST['delay_reason']) : NULL;
$doc_no = isset($_POST['doc_no']) ? mysqli_real_escape_string($conn, $_POST['doc_no']) : '';

$updated_by = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
$updated_by_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin';

// Validation
$error = NULL;

if (!$is_bulk && $docket_id <= 0) {
    $error = 'Invalid docket ID';
}

if ($is_bulk && empty($docket_ids)) {
    $error = 'No dockets selected for bulk update';
}

if (empty($new_status)) {
    $error = 'Status is required';
}

// NEW: Check if docket is manifested and "In Transit" - must be received first
if (!$error && !$is_bulk) {
    $manifest_check = "SELECT dd.status, dd.manifest_id, m.manifest_no 
                       FROM docket_details dd
                       LEFT JOIN tbl_manifest m ON dd.manifest_id = m.manifest_id
                       WHERE dd.docket_id = $docket_id";
    $manifest_result = mysqli_query($conn, $manifest_check);
    if ($manifest_result && $manifest_row = mysqli_fetch_assoc($manifest_result)) {
        if ($manifest_row['status'] == 'In Transit' && $manifest_row['manifest_id']) {
            $error = "This docket is in transit (Manifest: " . $manifest_row['manifest_no'] . "). It must be marked as 'Received at Destination' first before updating status.";
        }
    }
}

// Get doc_no if not provided (only for single update)
if (!$is_bulk && empty($doc_no)) {
    $doc_query = "SELECT doc_no, status FROM docket_details WHERE docket_id = $docket_id";
    $doc_result = mysqli_query($conn, $doc_query);
    if ($doc_result && $doc_row = mysqli_fetch_assoc($doc_result)) {
        $doc_no = $doc_row['doc_no'];
        if (empty($current_status)) {
            $current_status = $doc_row['status'];
        }
    } else {
        $error = 'Docket not found';
    }
}

// For bulk updates, doc_no will be fetched individually in the loop

// Validate status hierarchy - no reverse updates allowed (only for single updates)
// For bulk updates, validation happens in the loop for each docket
if (!$error && !$is_bulk) {
    $hierarchy_query = "SELECT old.status_order as old_order, new.status_order as new_order,
                               new.requires_date, new.requires_pod, new.requires_car_driver,
                               new.requires_delay_reason, new.is_final
                        FROM tbl_status_hierarchy old
                        JOIN tbl_status_hierarchy new
                        WHERE old.status_name = '$current_status' AND new.status_name = '$new_status'";
    $hierarchy_result = mysqli_query($conn, $hierarchy_query);

    if ($hierarchy_result && mysqli_num_rows($hierarchy_result) > 0) {
        $hierarchy = mysqli_fetch_assoc($hierarchy_result);

        // Check if trying to move backward (reverse update)
        if ($hierarchy['new_order'] < $hierarchy['old_order'] && $new_status != 'Delayed') {
            $error = "Cannot reverse status from '$current_status' to '$new_status'. Status updates must move forward only.";
        }
        // Check if current status is final (but allow POD upload for Delivered status)
        else {
            $final_check = mysqli_query($conn, "SELECT is_final FROM tbl_status_hierarchy WHERE status_name = '$current_status'");
            if ($final_check) {
                $final_row = mysqli_fetch_assoc($final_check);
                // Allow POD upload even if status is final (when current and new status are the same)
                $is_pod_upload_only = ($current_status == 'Delivered' && $new_status == 'Delivered' && isset($_FILES['pod_file']));
                if ($final_row['is_final'] == 1 && !$is_pod_upload_only) {
                    $error = "Cannot update status. '$current_status' is a final status and cannot be changed.";
                }
            }
        }

        // Validate required fields
        if (!$error) {
            if ($hierarchy['requires_date'] && empty($status_date)) {
                $error = "Date is required for '$new_status' status.";
            }
            if ($hierarchy['requires_car_driver'] && (empty($car_number) || empty($driver_name))) {
                $error = "Both vehicle number and driver name are required for '$new_status' status.";
            }
            if ($hierarchy['requires_delay_reason'] && empty($delay_reason)) {
                $error = "Delay reason is required for '$new_status' status.";
            }
        }
    }
}

// Define main office (Barasat) ID
if (!defined('BARASAT_OFFICE_ID')) {
    define('BARASAT_OFFICE_ID', 12);
}

// Check if user is from main office
$user_office_id = $_SESSION['office_id'] ?? null;
$isMainOffice = ($user_office_id == BARASAT_OFFICE_ID) ||
                (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Super Admin') ||
                empty($user_office_id);

// Validate driver phone for branch offices (for Out for Delivery status)
if (!$error && $new_status === 'Out for Delivery' && !$isMainOffice && empty($driver_phone)) {
    $error = "Driver phone number is required for branch offices.";
}

// For bulk updates, validate required fields based on new status only
if (!$error && $is_bulk) {
    // Check required fields for the new status
    if ($new_status === 'Out for Delivery' && (empty($car_number) || empty($driver_name))) {
        $error = "Both vehicle number and driver name are required for 'Out for Delivery' status.";
    }
    if ($new_status === 'Delayed' && empty($delay_reason)) {
        $error = "Delay reason is required for 'Delayed' status.";
    }
}

// Handle POD file upload (now optional)
$pod_file = NULL;
$pod_uploaded = false;

// If status is "Delivered", check if POD file is provided
if (!$error && $new_status === 'Delivered' && isset($_FILES['pod_file']) && $_FILES['pod_file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/pod/' . date('Y') . '/' . date('m') . '/' . $doc_no . '/';

    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_ext = pathinfo($_FILES['pod_file']['name'], PATHINFO_EXTENSION);
    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];

    if (!in_array(strtolower($file_ext), $allowed_ext)) {
        $error = "Invalid file type. Only JPG, PNG, and PDF files are allowed for POD.";
    } else {
        $pod_filename = 'POD_' . $doc_no . '_' . time() . '.' . $file_ext;
        $pod_path = $upload_dir . $pod_filename;

        if (move_uploaded_file($_FILES['pod_file']['tmp_name'], $pod_path)) {
            $pod_file = 'uploads/pod/' . date('Y') . '/' . date('m') . '/' . $doc_no . '/' . $pod_filename;
            $pod_uploaded = true;
        } else {
            $error = "Failed to upload POD file. Please try again.";
        }
    }
}

// Auto-adjust status if "Delivered" selected but no POD uploaded
if (!$error && $new_status === 'Delivered' && !$pod_uploaded) {
    // Change status to "Pending POD" if no POD file was provided
    $new_status = 'Pending POD';
}

// Return error if validation failed
if ($error) {
    if ($is_ajax_request) {
        echo 'Error: ' . $error;
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'delivery_status.php') . "?error=" . urlencode($error));
    }
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // car_number and driver_name already obtained from form (can be manual or from dropdown)
    // car_id and driver_id will be NULL if manually entered, or set if selected from dropdown

    $updated_count = 0;

    // Loop through all docket IDs (single or bulk)
    foreach ($docket_ids as $current_docket_id) {
        // Get current status for this docket (needed for bulk update)
        if ($is_bulk) {
            $status_check = mysqli_query($conn, "SELECT status FROM docket_details WHERE docket_id = $current_docket_id");
            $status_row = mysqli_fetch_assoc($status_check);
            $current_status = $status_row['status'] ?? '';
        }

        // Update status in docket_details
        $update_query = "UPDATE docket_details SET
                         status = '$new_status',
                         last_status_update = NOW()";

        if (!empty($location)) {
            $update_query .= ", current_location = '$location'";
        }

        // Update specific date fields based on status
        if ($new_status === 'Out for Delivery' && $status_date) {
            $update_query .= ", out_for_delivery_date = '$status_date'";
            // Save car number and driver name (manual entry or from database)
            if ($car_number) {
                $update_query .= ", car_number = '$car_number'";
                if ($car_id) $update_query .= ", car_id = $car_id";
            }
            if ($driver_name) {
                $update_query .= ", driver_name = '$driver_name'";
                if ($driver_id) $update_query .= ", driver_id = $driver_id";
                if ($driver_phone) $update_query .= ", driver_phone = '$driver_phone'";
            }
        } elseif ($new_status === 'Delivered') {
            // Use provided status_date, or current time if not provided
            $delivery_date = $status_date ?: date('Y-m-d H:i:s');
            $update_query .= ", actual_delivery = '$delivery_date', delivery_datetime = '$delivery_date'";
            if ($pod_file) $update_query .= ", proof_of_delivery = '$pod_file'";
        } elseif ($new_status === 'Pending POD') {
            // Delivered without POD - mark as pending POD
            $delivery_date = $status_date ?: date('Y-m-d H:i:s');
            $update_query .= ", actual_delivery = '$delivery_date', delivery_datetime = '$delivery_date'";
            $update_query .= ", proof_of_delivery = NULL"; // No POD yet
        } elseif ($new_status === 'Delayed' && $status_date) {
            $update_query .= ", delay_date = '$status_date'";
            if ($delay_reason) $update_query .= ", current_delay_reason = '$delay_reason', reason_of_delay = '$delay_reason'";
        }

        $update_query .= " WHERE docket_id = $current_docket_id";

        if (!mysqli_query($conn, $update_query)) {
            throw new Exception(mysqli_error($conn));
        }

        // Build notes for status history
        $history_notes = $remarks;
        if ($car_number && $driver_name) {
            $driver_info = $driver_name;
            if ($driver_phone) $driver_info .= " ($driver_phone)";
            $history_notes .= "\nVehicle: $car_number, Driver: $driver_info";
        }
        if ($delay_reason) {
            $history_notes .= "\nDelay Reason: $delay_reason";
        }

        // Insert into docket_status_history
        // Use status_date for changed_at if provided, otherwise use current time
        $changed_at_value = $status_date ? "'$status_date'" : "NOW()";
        $history_query = "INSERT INTO docket_status_history
            (docket_id, old_status, new_status, changed_by, changed_at, notes,
             status_date, car_id, car_number, driver_id, driver_name,
             delay_reason, pod_file, pod_uploaded_at, location,
             updated_by, updated_by_name)
            VALUES ($current_docket_id, '$current_status', '$new_status', '$updated_by_name', $changed_at_value, " .
            ($history_notes ? "'$history_notes'" : "NULL") . ", " .
            ($status_date ? "'$status_date'" : "NULL") . ", " .
            ($car_id ?: "NULL") . ", " .
            ($car_number ? "'$car_number'" : "NULL") . ", " .
            ($driver_id ?: "NULL") . ", " .
            ($driver_name ? "'$driver_name'" : "NULL") . ", " .
            ($delay_reason ? "'$delay_reason'" : "NULL") . ", " .
            ($pod_file ? "'$pod_file'" : "NULL") . ", " .
            ($pod_file ? "NOW()" : "NULL") . ", " .
            ($location ? "'$location'" : "NULL") . ", " .
            "$updated_by, '$updated_by_name')";

        if (!mysqli_query($conn, $history_query)) {
            throw new Exception(mysqli_error($conn));
        }

        $updated_count++;
    }

    mysqli_commit($conn);

    // ========================================
    // SEND EMAIL NOTIFICATIONS
    // ========================================

    // Only send emails for single updates (not bulk) and for specific statuses
    if (!$is_bulk && $updated_count > 0) {
        // Get full docket details for email
        $email_query = "SELECT * FROM docket_details WHERE docket_id = $docket_id";
        $email_result = mysqli_query($conn, $email_query);
        $docket_data = mysqli_fetch_assoc($email_result);

        if ($docket_data) {
            $client_email = $docket_data['client_email'] ?? '';
            $company_email = $docket_data['company_email'] ?? '';

            // Send email to CLIENT (Consignee/Receiver) for specific statuses
            if (!empty($client_email) && filter_var($client_email, FILTER_VALIDATE_EMAIL)) {

                // Email for "Out for Delivery" status
                if ($new_status === 'Out for Delivery' && !empty($car_number) && !empty($driver_name)) {
                    $email_subject = "🚚 Your Shipment is Out for Delivery - Docket #" . $docket_data['doc_no'];
                    $email_body = getOutForDeliveryEmailTemplate($docket_data, $car_number, $driver_name);
                    sendEmail($client_email, $email_subject, $email_body, $docket_data['client_name']);
                    error_log("Out for Delivery email sent to client: $client_email");
                }

                // Email for "Delivered" status
                elseif ($new_status === 'Delivered') {
                    $email_subject = "✅ Your Shipment Has Been Delivered - Docket #" . $docket_data['doc_no'];
                    $email_body = getDeliveredEmailTemplate($docket_data);
                    sendEmail($client_email, $email_subject, $email_body, $docket_data['client_name']);
                    error_log("Delivered email sent to client: $client_email");
                }

                // Email for "Delayed" status
                elseif ($new_status === 'Delayed' && !empty($delay_reason)) {
                    $email_subject = "⏰ Shipment Delayed - Docket #" . $docket_data['doc_no'];
                    $email_body = getDelayedEmailTemplate($docket_data, $delay_reason);
                    sendEmail($client_email, $email_subject, $email_body, $docket_data['client_name']);
                    error_log("Delayed email sent to client: $client_email");
                }
            }

            // Send email to COMPANY (Consignor/Sender) for "Delivered" status
            if ($new_status === 'Delivered' && !empty($company_email) && filter_var($company_email, FILTER_VALIDATE_EMAIL)) {
                $email_subject = "✅ Delivery Completed - Docket #" . $docket_data['doc_no'];
                $email_body = getCompanyDeliveredEmailTemplate($docket_data);
                sendEmail($company_email, $email_subject, $email_body, $docket_data['company_name']);
                error_log("Delivered email sent to company: $company_email");
            }
        }
    }

    // Return success
    $success_message = $is_bulk ? "Successfully updated $updated_count docket(s)" : "Status updated successfully!";

    if ($is_ajax_request) {
        echo 'Success: ' . $success_message;
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'delivery_status.php') . "?success=" . urlencode($success_message));
    }

} catch (Exception $e) {
    mysqli_rollback($conn);
    if ($is_ajax_request) {
        echo 'Error: Error updating status - ' . $e->getMessage();
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'delivery_status.php') . "?error=" . urlencode('Error updating status: ' . $e->getMessage()));
    }
}
?>
