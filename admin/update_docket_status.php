<?php
require 'check_auth.php';
require 'conn.php';

// Handle both JSON API requests and regular form submissions
$is_json_request = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

if ($is_json_request) {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($is_json_request) {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    } else {
        header("Location: delivery_status.php?error=Invalid request method");
    }
    exit;
}

// Get form data
$docket_id = intval($_POST['docket_id'] ?? 0);
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

$delay_reason = isset($_POST['delay_reason']) ? mysqli_real_escape_string($conn, $_POST['delay_reason']) : NULL;
$doc_no = isset($_POST['doc_no']) ? mysqli_real_escape_string($conn, $_POST['doc_no']) : '';

$updated_by = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
$updated_by_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin';

// Validation
$error = NULL;

if ($docket_id <= 0) {
    $error = 'Invalid docket ID';
}

if (empty($new_status)) {
    $error = 'Status is required';
}

// Get doc_no if not provided
if (empty($doc_no)) {
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

// Validate status hierarchy - no reverse updates allowed
if (!$error) {
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
        // Check if current status is final
        else {
            $final_check = mysqli_query($conn, "SELECT is_final FROM tbl_status_hierarchy WHERE status_name = '$current_status'");
            if ($final_check) {
                $final_row = mysqli_fetch_assoc($final_check);
                if ($final_row['is_final'] == 1) {
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

// Handle POD file upload
$pod_file = NULL;
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
        } else {
            $error = "Failed to upload POD file. Please try again.";
        }
    }
}

// Return error if validation failed
if ($error) {
    if ($is_json_request) {
        echo json_encode(['success' => false, 'message' => $error]);
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
        if ($car_id) $update_query .= ", car_id = $car_id, car_number = '$car_number'";
        if ($driver_id) $update_query .= ", driver_id = $driver_id, driver_name = '$driver_name'";
    } elseif ($new_status === 'Delivered') {
        $delivery_date = $status_date ?? date('Y-m-d H:i:s');
        $update_query .= ", actual_delivery = '$delivery_date', delivery_datetime = '$delivery_date'";
        if ($pod_file) $update_query .= ", proof_of_delivery = '$pod_file'";
    } elseif ($new_status === 'Delayed' && $status_date) {
        $update_query .= ", delay_date = '$status_date'";
        if ($delay_reason) $update_query .= ", current_delay_reason = '$delay_reason', reason_of_delay = '$delay_reason'";
    }

    $update_query .= " WHERE docket_id = $docket_id";

    if (!mysqli_query($conn, $update_query)) {
        throw new Exception(mysqli_error($conn));
    }

    // Build notes for status history
    $history_notes = $remarks;
    if ($car_number && $driver_name) {
        $history_notes .= "\nVehicle: $car_number, Driver: $driver_name";
    }
    if ($delay_reason) {
        $history_notes .= "\nDelay Reason: $delay_reason";
    }

    // Insert into docket_status_history
    $history_query = "INSERT INTO docket_status_history
        (docket_id, old_status, new_status, changed_by, changed_at, notes,
         status_date, car_id, car_number, driver_id, driver_name,
         delay_reason, pod_file, pod_uploaded_at, location,
         updated_by, updated_by_name)
        VALUES ($docket_id, '$current_status', '$new_status', '$updated_by_name', NOW(), " .
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

    mysqli_commit($conn);

    // Return success
    if ($is_json_request) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'delivery_status.php') . "?success=Status updated successfully!");
    }

} catch (Exception $e) {
    mysqli_rollback($conn);
    if ($is_json_request) {
        echo json_encode(['success' => false, 'message' => 'Error updating status: ' . $e->getMessage()]);
    } else {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'delivery_status.php') . "?error=" . urlencode('Error updating status: ' . $e->getMessage()));
    }
}
?>
