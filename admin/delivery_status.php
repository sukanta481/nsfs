<?php
require 'check_auth.php';
requirePermission('docket_status_update');
require 'conn.php';
require 'email_config_smtp.php';
require 'email_templates.php';

// Check if user has DOD (Date of Delivery) permission to edit delivery date after delivery
$has_dod_permission = hasPermission('docket_edit_delivery_date');

// Handle DOD (Date of Delivery) Update - separate from status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_delivery_date'])) {
    $docket_id = intval($_POST['docket_id']);
    $new_delivery_date = isset($_POST['new_delivery_date']) ? mysqli_real_escape_string($conn, $_POST['new_delivery_date']) : NULL;
    $dod_remarks = mysqli_real_escape_string($conn, trim($_POST['dod_remarks'] ?? ''));
    
    $updated_by = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
    $updated_by_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin';
    
    // Check DOD permission
    if (!$has_dod_permission) {
        $error = "Access denied. You don't have permission to edit delivery date (DOD permission required).";
    }
    // Check if user can access this docket
    elseif (!canAccessDocket($docket_id)) {
        $error = "Access denied. You don't have permission to update this docket.";
    }
    // Validate new delivery date
    elseif (empty($new_delivery_date)) {
        $error = "Please provide a valid delivery date.";
    }
    else {
        // Verify docket is in Delivered status
        $check_status = mysqli_query($conn, "SELECT doc_no, status, delivery_datetime FROM docket_details WHERE docket_id = $docket_id");
        $docket_info = mysqli_fetch_assoc($check_status);
        
        if (!$docket_info) {
            $error = "Docket not found.";
        } elseif ($docket_info['status'] !== 'Delivered') {
            $error = "DOD edit is only allowed for dockets with 'Delivered' status. Current status: " . $docket_info['status'];
        } else {
            $old_delivery_date = $docket_info['delivery_datetime'];
            $doc_no = $docket_info['doc_no'];
            
            mysqli_begin_transaction($conn);
            try {
                // Update the delivery date
                $update_query = "UPDATE docket_details SET 
                                 delivery_datetime = '$new_delivery_date',
                                 actual_delivery = '$new_delivery_date',
                                 last_status_update = NOW()
                                 WHERE docket_id = $docket_id";
                
                if (!mysqli_query($conn, $update_query)) {
                    throw new Exception(mysqli_error($conn));
                }
                
                // Log this change in status history with formatted dates
                $old_date_formatted = $old_delivery_date ? date('d M Y, h:i A', strtotime($old_delivery_date)) : 'Not set';
                $new_date_formatted = date('d M Y, h:i A', strtotime($new_delivery_date));
                $notes = "DOD (Date of Delivery) Updated\nOld Date: " . $old_date_formatted . "\nNew Date: " . $new_date_formatted;
                if (!empty($dod_remarks)) {
                    $notes .= "\nRemarks: $dod_remarks";
                }
                $notes = mysqli_real_escape_string($conn, $notes);
                
                // Include status_date so frontend can use the correct delivery date
                $history_query = "INSERT INTO docket_status_history 
                    (docket_id, old_status, new_status, changed_by, changed_at, notes, status_date, updated_by, updated_by_name)
                    VALUES ($docket_id, 'Delivered', 'Delivered', '$updated_by_name', NOW(), '$notes', '$new_delivery_date', $updated_by, '$updated_by_name')";
                
                if (!mysqli_query($conn, $history_query)) {
                    throw new Exception(mysqli_error($conn));
                }
                
                mysqli_commit($conn);
                header("Location: delivery_status.php?success=Delivery date updated successfully for docket #$doc_no!");
                exit;
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Error updating delivery date: " . $e->getMessage();
            }
        }
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $docket_id = intval($_POST['docket_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    $current_status = mysqli_real_escape_string($conn, $_POST['current_status']);
    $remarks = mysqli_real_escape_string($conn, trim($_POST['remarks']));
    $location = isset($_POST['location']) ? mysqli_real_escape_string($conn, trim($_POST['location'])) : '';
    $status_date = isset($_POST['status_date']) ? mysqli_real_escape_string($conn, $_POST['status_date']) : NULL;
    $car_id = isset($_POST['car_id']) && !empty($_POST['car_id']) ? intval($_POST['car_id']) : NULL;
    $driver_id = isset($_POST['driver_id']) && !empty($_POST['driver_id']) ? intval($_POST['driver_id']) : NULL;
    $delay_reason = isset($_POST['delay_reason']) ? mysqli_real_escape_string($conn, $_POST['delay_reason']) : NULL;

    // Get manual input for car and driver (for combo box functionality)
    $manual_car_number = isset($_POST['car_number']) ? mysqli_real_escape_string($conn, trim($_POST['car_number'])) : '';
    $manual_driver_name = isset($_POST['driver_name']) ? mysqli_real_escape_string($conn, trim($_POST['driver_name'])) : '';
    $manual_driver_phone = isset($_POST['driver_phone']) ? mysqli_real_escape_string($conn, trim($_POST['driver_phone'])) : '';

    $updated_by = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
    $updated_by_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin';

    // Get doc_no for this docket
    $doc_query = "SELECT doc_no FROM docket_details WHERE docket_id = $docket_id";
    $doc_result = mysqli_query($conn, $doc_query);
    $doc_row = mysqli_fetch_assoc($doc_result);
    $doc_no = $doc_row['doc_no'] ?? '';

    // Check if user can access this docket (office-based restriction)
    if (!canAccessDocket($docket_id)) {
        $error = "Access denied. You don't have permission to update this docket.";
    }
    // Check if user has permission to update to this specific status
    elseif (!canUpdateToStatus($new_status)) {
        $error = "You don't have permission to update docket status to '$new_status'.";
    }
    else {
        // Validate status hierarchy - no reverse updates allowed
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
            // Allow Pending POD -> Delivered transition (POD upload)
            $is_pod_upload_transition = ($current_status === 'Pending POD' && $new_status === 'Delivered');
            if ($hierarchy['new_order'] < $hierarchy['old_order'] && $new_status != 'Delayed' && !$is_pod_upload_transition) {
                $error = "Cannot reverse status from '$current_status' to '$new_status'. Status updates must move forward only.";
            }
            // Check if current status is final (but allow POD upload for Pending POD)
            else {
                $final_check = mysqli_query($conn, "SELECT is_final FROM tbl_status_hierarchy WHERE status_name = '$current_status'");
                if ($final_check) {
                    $final_row = mysqli_fetch_assoc($final_check);
                    // Allow POD upload from Pending POD status
                    if ($final_row['is_final'] == 1 && !$is_pod_upload_transition) {
                        $error = "Cannot update status. '$current_status' is a final status and cannot be changed.";
                    }
                }
            }

            // Validate required fields
            if (!isset($error)) {
                if ($hierarchy['requires_date'] && empty($status_date)) {
                    $error = "Date is required for '$new_status' status.";
                }
                if ($hierarchy['requires_car_driver'] && (empty($manual_car_number) || empty($manual_driver_name))) {
                    $error = "Both car and driver are required for '$new_status' status.";
                }
                if ($hierarchy['requires_delay_reason'] && empty($delay_reason)) {
                    $error = "Delay reason is required for '$new_status' status.";
                }
            }
        }
    }

    // Handle POD file upload
    $pod_file = NULL;
    if (!isset($error) && $new_status === 'Delivered' && isset($_FILES['pod_file']) && $_FILES['pod_file']['error'] === UPLOAD_ERR_OK) {
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

    // Start transaction if no errors
    if (!isset($error)) {
        mysqli_begin_transaction($conn);

        try {
            // Get car and driver details - prioritize manual input, fallback to database
            $car_number = $manual_car_number; // Use manual input first
            $driver_name = $manual_driver_name; // Use manual input first

            // If car_id is provided but no manual input, get from database
            if ($car_id && empty($car_number)) {
                $car_query = "SELECT car_number FROM tbl_car WHERE car_id = $car_id";
                $car_result = mysqli_query($conn, $car_query);
                if ($car_row = mysqli_fetch_assoc($car_result)) {
                    $car_number = mysqli_real_escape_string($conn, $car_row['car_number']);
                }
            }

            // If driver_id is provided but no manual input, get from database
            if ($driver_id && empty($driver_name)) {
                $driver_query = "SELECT staff_name FROM tbl_staff WHERE staff_id = $driver_id AND staff_role = 'Driver'";
                $driver_result = mysqli_query($conn, $driver_query);
                if ($driver_row = mysqli_fetch_assoc($driver_result)) {
                    $driver_name = mysqli_real_escape_string($conn, $driver_row['staff_name']);
                }
            }

            // Auto-add car to database if it's a new manual entry
            if (!empty($car_number) && !$car_id) {
                // Check if car already exists
                $check_car = mysqli_query($conn, "SELECT car_id FROM tbl_car WHERE car_number = '$car_number' LIMIT 1");
                if ($check_car && mysqli_num_rows($check_car) > 0) {
                    $car_row = mysqli_fetch_assoc($check_car);
                    $car_id = $car_row['car_id'];
                } else {
                    // Insert new car
                    $insert_car = mysqli_query($conn, "INSERT INTO tbl_car (car_number, car_details, active_status) VALUES ('$car_number', 'External Vehicle', 1)");
                    if ($insert_car) {
                        $car_id = mysqli_insert_id($conn);
                    }
                }
            }

            // Get driver phone - prioritize manual input
            $driver_phone = !empty($manual_driver_phone) ? $manual_driver_phone : '';
            
            // Auto-add driver to staff if it's a new manual entry
            if (!empty($driver_name) && !$driver_id) {
                // Check if driver already exists
                $check_driver = mysqli_query($conn, "SELECT staff_id, staff_phone FROM tbl_staff WHERE staff_name = '$driver_name' AND staff_role = 'Driver' LIMIT 1");
                if ($check_driver && mysqli_num_rows($check_driver) > 0) {
                    $driver_row = mysqli_fetch_assoc($check_driver);
                    $driver_id = $driver_row['staff_id'];
                    // Use existing phone only if manual phone not provided
                    if (empty($driver_phone)) {
                        $driver_phone = $driver_row['staff_phone'] ?? '';
                    }
                } else {
                    // Insert new driver (external driver with manual phone or N/A)
                    $phone_to_save = !empty($driver_phone) ? $driver_phone : 'N/A';
                    $insert_driver = mysqli_query($conn, "INSERT INTO tbl_staff (staff_name, staff_role, staff_phone, office_id, branch_office, active_status)
                                                          VALUES ('$driver_name', 'Driver', '$phone_to_save', 1, 'External', 1)");
                    if ($insert_driver) {
                        $driver_id = mysqli_insert_id($conn);
                    }
                }
            }

            // Update status in docket_details
            // IMPORTANT: If marking as Delivered without POD, set to "Pending POD" instead
            $actual_new_status = $new_status;
            $is_pod_upload_only = false;
            
            if ($new_status === 'Delivered') {
                if ($current_status === 'Pending POD' && $pod_file) {
                    // Uploading POD to already delivered (Pending POD) docket
                    $actual_new_status = 'Delivered';
                    $is_pod_upload_only = true;
                } elseif (empty($pod_file)) {
                    // Marking as Delivered but no POD uploaded - set to Pending POD
                    $actual_new_status = 'Pending POD';
                }
                // If POD is uploaded along with Delivered status, keep as Delivered
            }
            
            $update_query = "UPDATE docket_details SET
                             status = '$actual_new_status',
                             last_status_update = NOW()";

            if (!empty($location)) {
                $update_query .= ", current_location = '$location'";
            }

            // Update specific date fields based on status
            if ($new_status === 'Out for Delivery' && $status_date) {
                $update_query .= ", out_for_delivery_date = '$status_date'";
                if ($car_id) $update_query .= ", car_id = $car_id, car_number = '$car_number'";
                if ($driver_id) $update_query .= ", driver_id = $driver_id, driver_name = '$driver_name'";
            } elseif ($new_status === 'Delivered' || $actual_new_status === 'Pending POD') {
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
            
            // Add meaningful notes based on status transition
            if ($is_pod_upload_only) {
                $history_notes = "POD Uploaded: " . basename($pod_file) . " on " . date('d M Y, h:i A');
                if (!empty($remarks)) $history_notes .= "\n" . $remarks;
            } elseif ($actual_new_status === 'Pending POD') {
                $history_notes = "Parcel successfully delivered. Proof of Delivery upload is pending.";
                if (!empty($remarks)) $history_notes .= "\n" . $remarks;
            }
            
            if ($car_number && $driver_name) {
                $history_notes .= "\nVehicle: $car_number, Driver: $driver_name";
                if (!empty($driver_phone)) {
                    $history_notes .= " (Ph: $driver_phone)";
                }
            }
            if ($delay_reason) {
                $history_notes .= "\nDelay Reason: $delay_reason";
            }

            // Insert into docket_status_history (CORRECTED - uses docket_status_history, not tbl_tracking_history)
            $history_query = "INSERT INTO docket_status_history
                (docket_id, old_status, new_status, changed_by, changed_at, notes,
                 status_date, car_id, car_number, driver_id, driver_name,
                 delay_reason, pod_file, pod_uploaded_at, location,
                 updated_by, updated_by_name)
                VALUES ($docket_id, '$current_status', '$actual_new_status', '$updated_by_name', NOW(), " .
                ($history_notes ? "'" . mysqli_real_escape_string($conn, $history_notes) . "'" : "NULL") . ", " .
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

            // ========================================
            // SEND EMAIL NOTIFICATIONS
            // ========================================

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

            header("Location: delivery_status.php?success=Status updated successfully!");
            exit;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Error updating status: " . $e->getMessage();
        }
    }
}

// Get filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$status_filter = isset($_GET['status_filter']) ? mysqli_real_escape_string($conn, $_GET['status_filter']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$where = [];
if (!empty($search)) {
    $where[] = "(d.doc_no LIKE '%$search%' OR d.company_name LIKE '%$search%' OR d.client_name LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $where[] = "d.status = '$status_filter'";
}
if (!empty($date_from)) {
    $where[] = "DATE(d.pickup_datetime) >= '$date_from'";
}
if (!empty($date_to)) {
    $where[] = "DATE(d.pickup_datetime) <= '$date_to'";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Apply office filter for branch-based access control
$officeFilter = getOfficeFilter('d');
if (!empty($officeFilter)) {
    if (!empty($where_clause)) {
        $where_clause .= $officeFilter;
    } else {
        $where_clause = "WHERE " . ltrim($officeFilter, ' AND');
    }
}

// Get dockets
$query = "SELECT d.*
          FROM docket_details d
          $where_clause
          ORDER BY d.pickup_datetime DESC
          LIMIT 100";

$result = mysqli_query($conn, $query);

// Get status counts (also filtered by office)
$counts_query = "SELECT
                 COUNT(*) as total,
                 SUM(CASE WHEN status = 'Pending' OR status IS NULL THEN 1 ELSE 0 END) as pending,
                 SUM(CASE WHEN status = 'In Transit' THEN 1 ELSE 0 END) as in_transit,
                 SUM(CASE WHEN status = 'Out for Delivery' THEN 1 ELSE 0 END) as out_for_delivery,
                 SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered,
                 SUM(CASE WHEN status = 'Delayed' THEN 1 ELSE 0 END) as `delayed`,
                 SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) as `failed`
                 FROM docket_details d
                 $where_clause";
$counts_result = mysqli_query($conn, $counts_query);

if (!$counts_result) {
    die("Error in counts query: " . mysqli_error($conn));
}

$counts = mysqli_fetch_assoc($counts_result);
if (!$counts) {
    $counts = ['total' => 0, 'pending' => 0, 'in_transit' => 0, 'out_for_delivery' => 0, 'delivered' => 0, 'delayed' => 0, 'failed' => 0];
}

// Get cars for dropdown
$cars_query = "SELECT car_id, car_number, car_details FROM tbl_car WHERE active_status = 1 ORDER BY car_number";
$cars_result = mysqli_query($conn, $cars_query);

// Get drivers for dropdown
$drivers_query = "SELECT staff_id, staff_name, staff_phone FROM tbl_staff WHERE staff_role = 'Driver' AND active_status = 1 ORDER BY staff_name";
$drivers_result = mysqli_query($conn, $drivers_query);

// Get delay reasons
$delay_reasons_query = "SELECT reason_id, reason_text, reason_category FROM tbl_delay_reasons WHERE is_active = 1 ORDER BY reason_category, reason_text";
$delay_reasons_result = mysqli_query($conn, $delay_reasons_query);

// Get allowed statuses for current user
$allowed_statuses = getAllowedStatuses();

// Get status hierarchy for dropdown rendering
$status_hierarchy_query = "SELECT status_name, status_order, is_final FROM tbl_status_hierarchy ORDER BY status_order";
$status_hierarchy_result = mysqli_query($conn, $status_hierarchy_query);
$all_statuses = [];
while ($sh = mysqli_fetch_assoc($status_hierarchy_result)) {
    $all_statuses[$sh['status_name']] = $sh;
}

require 'top_header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
body {
    background: #f0f4f8 !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.status-page-container {
    padding: 20px;
    max-width: 100%;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(102,126,234,0.3);
}

.page-header h1 {
    margin: 0;
    font-size: 32px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 15px;
}

.page-header p {
    margin: 10px 0 0 0;
    opacity: 0.95;
    font-size: 16px;
}

/* Stats Cards */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s;
    cursor: pointer;
    border-left: 4px solid #ddd;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.stat-card.pending { border-left-color: #ffc107; }
.stat-card.in-transit { border-left-color: #17a2b8; }
.stat-card.out-for-delivery { border-left-color: #007bff; }
.stat-card.delivered { border-left-color: #28a745; }
.stat-card.delayed { border-left-color: #ff9800; }
.stat-card.failed { border-left-color: #dc3545; }

.stat-number {
    font-size: 36px;
    font-weight: 800;
    margin: 0;
    color: #2c3e50;
}

.stat-label {
    font-size: 13px;
    color: #7f8c8d;
    margin: 5px 0 0 0;
    font-weight: 600;
}

/* Filter Section */
.filter-section {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.filter-title {
    font-size: 18px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 14px;
    font-weight: 600;
    color: #34495e;
    margin-bottom: 8px;
}

.filter-input {
    padding: 12px 15px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.3s;
}

.filter-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.btn-filter {
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102,126,234,0.4);
}

/* Docket Cards */
.dockets-container {
    display: grid;
    gap: 20px;
}

.docket-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s;
}

.docket-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.docket-number {
    font-size: 24px;
    font-weight: 800;
    color: #2c3e50;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
}

.status-badge.pending { background: #fff3cd; color: #856404; }
.status-badge.confirmed { background: #cfe2ff; color: #084298; }
.status-badge.picked-up { background: #d1ecf1; color: #0c5460; }
.status-badge.in-transit { background: #d1ecf1; color: #0c5460; }
.status-badge.out-for-delivery { background: #cce5ff; color: #004085; }
.status-badge.delivered { background: #d4edda; color: #155724; }
.status-badge.delayed { background: #ffe5b4; color: #856404; }
.status-badge.failed { background: #f8d7da; color: #721c24; }

.update-button {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
}

.update-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(40,167,69,0.4);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
}

.modal-content {
    background: white;
    border-radius: 20px;
    padding: 40px;
    max-width: 700px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 50px rgba(0,0,0,0.3);
}

.conditional-field {
    display: none;
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #667eea;
}

.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
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

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 700;
    color: #34495e;
    margin-bottom: 10px;
    font-size: 15px;
}

.form-group label .required {
    color: #dc3545;
}

@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>

      <div class="right_col" role="main">
        <div class="status-page-container">

          <!-- Page Header -->
          <div class="page-header">
            <h1>
              <i class="fas fa-clipboard-check"></i>
              Update Delivery Status
            </h1>
            <p>Enhanced status tracking with validation, car/driver assignment, and POD upload</p>
          </div>

          <!-- Success/Error Messages -->
          <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($_GET['success']); ?></span>
          </div>
          <?php endif; ?>

          <?php if (isset($error)): ?>
          <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error; ?></span>
          </div>
          <?php endif; ?>

          <!-- Stats Cards -->
          <div class="stats-row">
            <div class="stat-card pending" onclick="filterByStatus('')">
              <p class="stat-number"><?php echo $counts['total'] ?? 0; ?></p>
              <p class="stat-label">Total Dockets</p>
            </div>
            <div class="stat-card pending" onclick="filterByStatus('Pending')">
              <p class="stat-number"><?php echo $counts['pending'] ?? 0; ?></p>
              <p class="stat-label">Pending</p>
            </div>
            <div class="stat-card in-transit" onclick="filterByStatus('In Transit')">
              <p class="stat-number"><?php echo $counts['in_transit'] ?? 0; ?></p>
              <p class="stat-label">In Transit</p>
            </div>
            <div class="stat-card out-for-delivery" onclick="filterByStatus('Out for Delivery')">
              <p class="stat-number"><?php echo $counts['out_for_delivery'] ?? 0; ?></p>
              <p class="stat-label">Out for Delivery</p>
            </div>
            <div class="stat-card delivered" onclick="filterByStatus('Delivered')">
              <p class="stat-number"><?php echo $counts['delivered'] ?? 0; ?></p>
              <p class="stat-label">Delivered</p>
            </div>
            <div class="stat-card delayed" onclick="filterByStatus('Delayed')">
              <p class="stat-number"><?php echo $counts['delayed'] ?? 0; ?></p>
              <p class="stat-label">Delayed</p>
            </div>
            <div class="stat-card failed" onclick="filterByStatus('Failed')">
              <p class="stat-number"><?php echo $counts['failed'] ?? 0; ?></p>
              <p class="stat-label">Failed</p>
            </div>
          </div>

          <!-- Filter Section -->
          <div class="filter-section">
            <div class="filter-title">
              <i class="fas fa-filter"></i>
              Search & Filter
            </div>
            <form method="GET" action="">
              <div class="filter-grid">
                <div class="filter-group">
                  <label><i class="fas fa-search"></i> Search</label>
                  <input type="text" name="search" class="filter-input"
                         placeholder="Docket number or client name"
                         value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="filter-group">
                  <label><i class="fas fa-flag"></i> Status</label>
                  <select name="status_filter" class="filter-input">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Confirmed" <?php echo $status_filter == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="Picked Up" <?php echo $status_filter == 'Picked Up' ? 'selected' : ''; ?>>Picked Up</option>
                    <option value="In Transit" <?php echo $status_filter == 'In Transit' ? 'selected' : ''; ?>>In Transit</option>
                    <option value="Out for Delivery" <?php echo $status_filter == 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                    <option value="Delivered" <?php echo $status_filter == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="Delayed" <?php echo $status_filter == 'Delayed' ? 'selected' : ''; ?>>Delayed</option>
                    <option value="Failed" <?php echo $status_filter == 'Failed' ? 'selected' : ''; ?>>Failed</option>
                  </select>
                </div>

                <div class="filter-group">
                  <label><i class="fas fa-calendar"></i> From Date</label>
                  <input type="date" name="date_from" class="filter-input" value="<?php echo $date_from; ?>">
                </div>

                <div class="filter-group">
                  <label><i class="fas fa-calendar"></i> To Date</label>
                  <input type="date" name="date_to" class="filter-input" value="<?php echo $date_to; ?>">
                </div>

                <div class="filter-group">
                  <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Search</button>
                </div>
              </div>
            </form>
          </div>

          <!-- Dockets List -->
          <div class="dockets-container">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <div class="docket-card">
                <div class="docket-header">
                  <div class="docket-number">
                    <i class="fas fa-file-alt"></i>
                    <?php echo htmlspecialchars($row['doc_no'] ?? 'N/A'); ?>
                  </div>
                  <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $row['status'] ?? 'pending')); ?>">
                    <?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?>
                  </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                  <div>
                    <div style="font-size: 12px; color: #7f8c8d; font-weight: 600;">CONSIGNOR</div>
                    <div style="font-size: 16px; color: #2c3e50; font-weight: 600;"><?php echo htmlspecialchars($row['company_name'] ?? 'N/A'); ?></div>
                  </div>
                  <div>
                    <div style="font-size: 12px; color: #7f8c8d; font-weight: 600;">CONSIGNEE</div>
                    <div style="font-size: 16px; color: #2c3e50; font-weight: 600;"><?php echo htmlspecialchars($row['client_name'] ?? 'N/A'); ?></div>
                  </div>
                  <div>
                    <div style="font-size: 12px; color: #7f8c8d; font-weight: 600;">DESTINATION</div>
                    <div style="font-size: 16px; color: #2c3e50; font-weight: 600;"><?php echo htmlspecialchars($row['to_location'] ?? $row['delivery_location'] ?? 'N/A'); ?></div>
                  </div>
                  <div>
                    <div style="font-size: 12px; color: #7f8c8d; font-weight: 600;">DATE</div>
                    <div style="font-size: 16px; color: #2c3e50; font-weight: 600;">
                      <?php echo !empty($row['pickup_datetime']) ? date('d M Y', strtotime($row['pickup_datetime'])) : 'N/A'; ?>
                    </div>
                  </div>
                  <?php if ($row['status'] === 'Delivered' && !empty($row['delivery_datetime'])): ?>
                  <div>
                    <div style="font-size: 12px; color: #7f8c8d; font-weight: 600;">DELIVERED ON</div>
                    <div style="font-size: 16px; color: #155724; font-weight: 600;">
                      <?php echo date('d M Y, h:i A', strtotime($row['delivery_datetime'])); ?>
                    </div>
                  </div>
                  <?php endif; ?>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                  <button class="update-button" onclick="openStatusModal(<?php echo $row['docket_id']; ?>, '<?php echo htmlspecialchars($row['doc_no'] ?? 'N/A'); ?>', '<?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?>')">
                    <i class="fas fa-edit"></i> Update Status
                  </button>
                  <?php if ($row['status'] === 'Delivered' && $has_dod_permission): ?>
                  <button type="button" class="update-button" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);" 
                          onclick="openDodModal(<?php echo $row['docket_id']; ?>, '<?php echo htmlspecialchars($row['doc_no'] ?? 'N/A'); ?>', '<?php echo !empty($row['delivery_datetime']) ? date('Y-m-d\TH:i', strtotime($row['delivery_datetime'])) : ''; ?>')">
                    <i class="fas fa-calendar-alt"></i> Edit DOD
                  </button>
                  <?php endif; ?>
                </div>
              </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
                <i class="fas fa-inbox" style="font-size: 80px; color: #e1e8ed;"></i>
                <h3 style="color: #7f8c8d;">No dockets found</h3>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>

      <?php require 'footer.php'; ?>
    </div>
  </div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
  <div class="modal-content">
    <h2 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 28px; font-weight: 800;">
      <i class="fas fa-clipboard-check" style="color: #667eea;"></i>
      Update Status
    </h2>
    <p style="color: #7f8c8d; margin: 0 0 30px 0;">
      Docket: <strong id="modalDocketNo" style="color: #667eea;"></strong>
    </p>

    <form method="POST" action="" id="statusForm" enctype="multipart/form-data">
      <input type="hidden" name="docket_id" id="modalDocketId">
      <input type="hidden" name="current_status" id="modalCurrentStatus">

      <div class="form-group">
        <label>
          <i class="fas fa-flag"></i> Select New Status <span class="required">*</span>
        </label>
        <select name="status" id="newStatus" required class="filter-input" style="width: 100%; font-size: 16px;" onchange="handleStatusChange()">
          <option value="">-- Choose Status --</option>
          <?php 
          // Only show statuses the user is allowed to update to
          foreach ($all_statuses as $status_name => $status_info): 
            if (in_array($status_name, $allowed_statuses)):
              $is_final = $status_info['is_final'] ? 'true' : 'false';
              $order = $status_info['status_order'];
              $allow_anytime = ($status_name === 'Delayed') ? 'true' : 'false';
          ?>
          <option value="<?php echo htmlspecialchars($status_name); ?>" 
                  data-order="<?php echo $order; ?>"
                  <?php if ($status_name === 'Delayed'): ?>data-allow-anytime="true"<?php endif; ?>
                  <?php if ($status_info['is_final']): ?>data-final="true"<?php endif; ?>>
            <?php echo htmlspecialchars($status_name); ?>
          </option>
          <?php 
            endif;
          endforeach; 
          ?>
        </select>
        <?php if (count($allowed_statuses) < count($all_statuses)): ?>
        <small style="color: #666; display: block; margin-top: 5px;">
          <i class="fas fa-info-circle"></i> You can only update to statuses assigned to your account.
        </small>
        <?php endif; ?>
      </div>

      <!-- Conditional: Date Field (for Out for Delivery, Delivered, Delayed) -->
      <div id="dateField" class="conditional-field">
        <div class="form-group">
          <label>
            <i class="fas fa-calendar"></i> <span id="dateLabelText">Date</span> <span class="required">*</span>
          </label>
          <input type="datetime-local" name="status_date" class="filter-input" style="width: 100%;" value="<?php echo date('Y-m-d\TH:i'); ?>">
        </div>
      </div>

      <!-- Conditional: Car and Driver Fields (for Out for Delivery) -->
      <div id="carDriverField" class="conditional-field">
        <div class="form-group">
          <label>
            <i class="fas fa-car"></i> Vehicle Number <span class="required">*</span>
          </label>
          <input type="text" name="car_number" id="carNumberInput" class="filter-input" list="carList" placeholder="Type or select vehicle number" autocomplete="off" style="width: 100%;">
          <datalist id="carList">
            <?php
            mysqli_data_seek($cars_result, 0);
            while ($car = mysqli_fetch_assoc($cars_result)):
            ?>
              <option value="<?php echo htmlspecialchars($car['car_number']); ?>" data-id="<?php echo $car['car_id']; ?>" data-details="<?php echo htmlspecialchars($car['car_details']); ?>">
                <?php echo htmlspecialchars($car['car_number'] . ' - ' . $car['car_details']); ?>
              </option>
            <?php endwhile; ?>
          </datalist>
          <input type="hidden" name="car_id" id="carIdHidden">
          <small style="color: #7f8c8d; font-size: 12px;">Type manually for external vehicles or select from dropdown</small>
        </div>

        <div class="form-group">
          <label>
            <i class="fas fa-user-tie"></i> Driver Name <span class="required">*</span>
          </label>
          <input type="text" name="driver_name" id="driverNameInput" class="filter-input" list="driverList" placeholder="Type or select driver name" autocomplete="off" style="width: 100%;">
          <datalist id="driverList">
            <?php
            mysqli_data_seek($drivers_result, 0);
            while ($driver = mysqli_fetch_assoc($drivers_result)):
            ?>
              <option value="<?php echo htmlspecialchars($driver['staff_name']); ?>" data-id="<?php echo $driver['staff_id']; ?>" data-phone="<?php echo htmlspecialchars($driver['staff_phone']); ?>">
                <?php echo htmlspecialchars($driver['staff_name'] . ' - ' . $driver['staff_phone']); ?>
              </option>
            <?php endwhile; ?>
          </datalist>
          <input type="hidden" name="driver_id" id="driverIdHidden">
          <small style="color: #7f8c8d; font-size: 12px;">Type manually for external drivers or select from dropdown</small>
        </div>
        
        <div class="form-group">
          <label>
            <i class="fas fa-phone"></i> Driver Phone <span style="color: #7f8c8d; font-size: 12px;">(Optional - auto-fills from dropdown)</span>
          </label>
          <input type="text" name="driver_phone" id="driverPhoneInput" class="filter-input" placeholder="Enter driver phone number" autocomplete="off" style="width: 100%;">
          <small style="color: #7f8c8d; font-size: 12px;">Auto-fills when selecting from dropdown. Type manually for external drivers.</small>
        </div>
      </div>

      <!-- Conditional: Delay Reason (for Delayed) -->
      <div id="delayReasonField" class="conditional-field">
        <div class="form-group">
          <label>
            <i class="fas fa-exclamation-triangle"></i> Delay Reason <span class="required">*</span>
          </label>
          <select name="delay_reason" class="filter-input" style="width: 100%;">
            <option value="">-- Select Delay Reason --</option>
            <?php
            $current_category = '';
            mysqli_data_seek($delay_reasons_result, 0);
            while ($reason = mysqli_fetch_assoc($delay_reasons_result)):
              if ($current_category != $reason['reason_category']) {
                if ($current_category != '') echo '</optgroup>';
                echo '<optgroup label="' . htmlspecialchars($reason['reason_category']) . '">';
                $current_category = $reason['reason_category'];
              }
            ?>
              <option value="<?php echo htmlspecialchars($reason['reason_text']); ?>">
                <?php echo htmlspecialchars($reason['reason_text']); ?>
              </option>
            <?php
            endwhile;
            if ($current_category != '') echo '</optgroup>';
            ?>
          </select>
        </div>
      </div>

      <!-- Conditional: POD Upload (for Delivered) -->
      <div id="podField" class="conditional-field">
        <div class="form-group">
          <label>
            <i class="fas fa-file-upload"></i> Proof of Delivery (POD) <span class="required">*</span>
          </label>
          <input type="file" name="pod_file" accept=".jpg,.jpeg,.png,.pdf" class="filter-input" style="width: 100%;">
          <small style="color: #7f8c8d; font-size: 12px;">Accepted: JPG, PNG, PDF (Max 5MB)</small>
        </div>
      </div>

      <div class="form-group">
        <label>
          <i class="fas fa-map-marker-alt"></i> Current Location (Optional)
        </label>
        <input type="text" name="location" class="filter-input" style="width: 100%;" placeholder="e.g., Mumbai Warehouse, Delhi Hub">
      </div>

      <div class="form-group">
        <label>
          <i class="fas fa-comment"></i> Remarks (Optional)
        </label>
        <textarea name="remarks" rows="3" class="filter-input" style="width: 100%; resize: vertical;" placeholder="Add any notes or comments..."></textarea>
      </div>

      <div style="display: flex; gap: 15px; margin-top: 30px;">
        <button type="submit" name="update_status" style="flex: 1; padding: 15px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 10px; font-size: 18px; font-weight: 700; cursor: pointer;">
          <i class="fas fa-check"></i> Update Status
        </button>
        <button type="button" onclick="closeStatusModal()" style="flex: 1; padding: 15px; background: #6c757d; color: white; border: none; border-radius: 10px; font-size: 18px; font-weight: 700; cursor: pointer;">
          <i class="fas fa-times"></i> Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<!-- DOD (Date of Delivery) Edit Modal -->
<?php if ($has_dod_permission): ?>
<div id="dodModal" class="modal">
  <div class="modal-content" style="max-width: 500px;">
    <h2 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 24px; font-weight: 800;">
      <i class="fas fa-calendar-alt" style="color: #f39c12;"></i>
      Edit Delivery Date (DOD)
    </h2>
    <p style="color: #7f8c8d; margin: 0 0 20px 0;">
      Docket: <strong id="dodModalDocketNo" style="color: #667eea;"></strong>
    </p>
    
    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px; margin-bottom: 20px;">
      <i class="fas fa-exclamation-triangle" style="color: #856404;"></i>
      <span style="color: #856404; font-size: 13px;">
        <strong>Warning:</strong> You are editing the delivery date for an already delivered docket. This action will be logged.
      </span>
    </div>

    <form method="POST" action="" id="dodForm">
      <input type="hidden" name="docket_id" id="dodModalDocketId">

      <div class="form-group">
        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
          <i class="fas fa-calendar-check"></i> New Delivery Date & Time <span class="required" style="color: #e74c3c;">*</span>
        </label>
        <input type="datetime-local" name="new_delivery_date" id="dodNewDate" required 
               class="filter-input" style="width: 100%; font-size: 16px; padding: 12px;">
      </div>

      <div class="form-group" style="margin-top: 15px;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">
          <i class="fas fa-comment"></i> Reason for Change
        </label>
        <textarea name="dod_remarks" rows="3" class="filter-input" 
                  style="width: 100%; resize: vertical; font-size: 14px; padding: 12px;"
                  placeholder="Please provide a reason for changing the delivery date..."></textarea>
      </div>

      <div style="display: flex; gap: 15px; margin-top: 25px;">
        <button type="submit" name="update_delivery_date" 
                style="flex: 1; padding: 15px; background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer;">
          <i class="fas fa-save"></i> Update Delivery Date
        </button>
        <button type="button" onclick="closeDodModal()" 
                style="flex: 1; padding: 15px; background: #6c757d; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer;">
          <i class="fas fa-times"></i> Cancel
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
// DOD Modal Functions
function openDodModal(docketId, docketNo, currentDeliveryDate) {
    const modal = document.getElementById('dodModal');
    if (!modal) return;
    
    document.getElementById('dodModalDocketId').value = docketId;
    document.getElementById('dodModalDocketNo').textContent = docketNo;
    document.getElementById('dodNewDate').value = currentDeliveryDate || '';
    modal.style.display = 'flex';
}

function closeDodModal() {
    const modal = document.getElementById('dodModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close DOD modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const dodModal = document.getElementById('dodModal');
    if (dodModal) {
        dodModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDodModal();
            }
        });
    }
});

function openStatusModal(docketId, docketNo, currentStatus) {
    document.getElementById('modalDocketId').value = docketId;
    document.getElementById('modalDocketNo').textContent = docketNo;
    document.getElementById('modalCurrentStatus').value = currentStatus;
    document.getElementById('statusModal').style.display = 'flex';

    // Reset form
    document.getElementById('statusForm').reset();
    document.getElementById('modalDocketId').value = docketId;
    document.getElementById('modalCurrentStatus').value = currentStatus;
    hideAllConditionalFields();
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}

function hideAllConditionalFields() {
    document.getElementById('dateField').style.display = 'none';
    document.getElementById('carDriverField').style.display = 'none';
    document.getElementById('delayReasonField').style.display = 'none';
    document.getElementById('podField').style.display = 'none';
}

function handleStatusChange() {
    const status = document.getElementById('newStatus').value;
    hideAllConditionalFields();

    if (status === 'Out for Delivery') {
        document.getElementById('dateLabelText').textContent = 'Out for Delivery Date';
        document.getElementById('dateField').style.display = 'block';
        document.getElementById('carDriverField').style.display = 'block';
    } else if (status === 'Delivered' || status === 'Pending POD') {
        document.getElementById('dateLabelText').textContent = 'Delivery Date';
        document.getElementById('dateField').style.display = 'block';
        document.getElementById('podField').style.display = 'block';
    } else if (status === 'Delayed') {
        document.getElementById('dateLabelText').textContent = 'Delay Date';
        document.getElementById('dateField').style.display = 'block';
        document.getElementById('delayReasonField').style.display = 'block';
    }
}

function filterByStatus(status) {
    const url = new URL(window.location.href);
    url.searchParams.set('status_filter', status);
    window.location.href = url.toString();
}

// Close modal when clicking outside
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStatusModal();
    }
});

// Auto-hide success message
setTimeout(function() {
    const alert = document.querySelector('.alert-success');
    if (alert) {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.5s';
        setTimeout(() => alert.remove(), 500);
    }
}, 5000);

// Auto-fill car_id and driver_id when selecting from datalist
document.getElementById('carNumberInput').addEventListener('input', function() {
  const value = this.value;
  const options = document.querySelectorAll('#carList option');
  const hiddenInput = document.getElementById('carIdHidden');

  let matched = false;
  options.forEach(option => {
    if (option.value === value) {
      hiddenInput.value = option.getAttribute('data-id') || '';
      matched = true;
    }
  });

  if (!matched) {
    hiddenInput.value = ''; // Manual input - no ID
  }
});

document.getElementById('driverNameInput').addEventListener('input', function() {
  const value = this.value;
  const options = document.querySelectorAll('#driverList option');
  const hiddenInput = document.getElementById('driverIdHidden');
  const phoneInput = document.getElementById('driverPhoneInput');

  let matched = false;
  options.forEach(option => {
    if (option.value === value) {
      hiddenInput.value = option.getAttribute('data-id') || '';
      // Auto-fill phone number from dropdown selection
      phoneInput.value = option.getAttribute('data-phone') || '';
      matched = true;
    }
  });

  if (!matched) {
    hiddenInput.value = ''; // Manual input - no ID
    // Don't clear phone - let user enter manually
  }
});

// Check for error in URL and show it
window.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const error = urlParams.get('error');
  if (error) {
    // Show error in alert for now (can be enhanced with custom modal)
    alert(decodeURIComponent(error));
    // Clean URL without reloading
    const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]error=[^&]*/, '').replace(/^&/, '?');
    window.history.replaceState({}, document.title, cleanUrl);
  }

  // Disable previous status options when modal opens
  const statusModal = document.getElementById('statusModal');
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.target.style.display === 'flex') {
        disablePreviousStatusOptions();
      }
    });
  });
  observer.observe(statusModal, { attributes: true, attributeFilter: ['style'] });
});

function disablePreviousStatusOptions() {
  const statusSelect = document.getElementById('newStatus');
  const currentStatus = document.getElementById('modalCurrentStatus').value;

  if (!currentStatus) return;

  // Find current status option
  let currentOrder = 0;
  const options = statusSelect.querySelectorAll('option');

  options.forEach(option => {
    if (option.value === currentStatus) {
      currentOrder = parseInt(option.getAttribute('data-order') || 0);
    }
  });

  // Re-enable all first (in case modal reopened)
  options.forEach(option => {
    option.disabled = false;
    option.style.color = '';
    // Remove the (unavailable) text if it exists
    option.textContent = option.textContent.replace(' (unavailable)', '');
  });

  // Disable previous options
  options.forEach(option => {
    // Skip empty option
    if (!option.value) return;

    const optionOrder = parseInt(option.getAttribute('data-order') || 0);
    const allowAnytime = option.getAttribute('data-allow-anytime') === 'true';
    const isFinal = option.getAttribute('data-final') === 'true';

    if (optionOrder < currentOrder && !allowAnytime) {
      option.disabled = true;
      option.style.color = '#ccc';
      // Only add (unavailable) if not already there
      if (!option.textContent.includes('(unavailable)')) {
        option.textContent = option.textContent + ' (unavailable)';
      }
    }
  });
}
</script>

</body>
</html>
