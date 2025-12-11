<?php
require_once 'includes/top.php';
require_once 'DocketDetailsManager.php';
require_once 'email_config_smtp.php';
require_once 'email_templates.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add_trip_modern.php');
    exit;
}

// Initialize Docket Manager
$docketManager = new DocketDetailsManager($conn);

// Get trip details
$office_id = isset($_POST['office_id']) ? intval($_POST['office_id']) : 0;
$car_id = isset($_POST['car_id']) ? intval($_POST['car_id']) : 0;
$driver_id = isset($_POST['driver_id']) ? intval($_POST['driver_id']) : 0;
$helper_id = isset($_POST['helper_id']) ? intval($_POST['helper_id']) : 0;
$pickup_datetime = isset($_POST['pickup_datetime']) ? mysqli_real_escape_string($conn, $_POST['pickup_datetime']) : '';
$raw_dockets = isset($_POST['dockets']) ? $_POST['dockets'] : [];

// Get manual input for car, driver and helper (combo box functionality)
$manual_car_number = isset($_POST['car_number']) ? mysqli_real_escape_string($conn, trim($_POST['car_number'])) : '';
$manual_driver_name = isset($_POST['driver_name']) ? mysqli_real_escape_string($conn, trim($_POST['driver_name'])) : '';
$manual_helper_name = isset($_POST['helper_name']) ? mysqli_real_escape_string($conn, trim($_POST['helper_name'])) : '';
$driver_phone = isset($_POST['driver_phone']) ? mysqli_real_escape_string($conn, trim($_POST['driver_phone'])) : '';
$helper_phone = isset($_POST['helper_phone']) ? mysqli_real_escape_string($conn, trim($_POST['helper_phone'])) : '';

// Auto-add car to database if it's a new manual entry
if (!empty($manual_car_number) && !$car_id) {
    // Check if car already exists
    $check_car = mysqli_query($conn, "SELECT car_id FROM tbl_car WHERE car_number = '$manual_car_number' LIMIT 1");
    if ($check_car && mysqli_num_rows($check_car) > 0) {
        $car_row = mysqli_fetch_assoc($check_car);
        $car_id = $car_row['car_id'];
    } else {
        // Insert new car
        $insert_car = mysqli_query($conn, "INSERT INTO tbl_car (car_number, car_details, active_status) VALUES ('$manual_car_number', 'External Vehicle', 1)");
        if ($insert_car) {
            $car_id = mysqli_insert_id($conn);
        }
    }
}

// Auto-add driver to staff if it's a new manual entry
if (!empty($manual_driver_name) && !$driver_id) {
    // Check if driver already exists
    $check_driver = mysqli_query($conn, "SELECT staff_id FROM tbl_staff WHERE staff_name = '$manual_driver_name' AND staff_role = 'Driver' LIMIT 1");
    if ($check_driver && mysqli_num_rows($check_driver) > 0) {
        $driver_row = mysqli_fetch_assoc($check_driver);
        $driver_id = $driver_row['staff_id'];
    } else {
        // Insert new driver (external driver with phone if provided)
        $phone_value = !empty($driver_phone) ? "'$driver_phone'" : "'N/A'";
        $insert_driver = mysqli_query($conn, "INSERT INTO tbl_staff (staff_name, staff_role, staff_phone, office_id, branch_office, active_status)
                                              VALUES ('$manual_driver_name', 'Driver', $phone_value, 1, 'External', 1)");
        if ($insert_driver) {
            $driver_id = mysqli_insert_id($conn);
        }
    }
}

// Auto-add helper to staff if it's a new manual entry
if (!empty($manual_helper_name) && !$helper_id) {
    // Check if helper already exists
    $check_helper = mysqli_query($conn, "SELECT staff_id FROM tbl_staff WHERE staff_name = '$manual_helper_name' AND staff_role = 'Helper' LIMIT 1");
    if ($check_helper && mysqli_num_rows($check_helper) > 0) {
        $helper_row = mysqli_fetch_assoc($check_helper);
        $helper_id = $helper_row['staff_id'];
    } else {
        // Insert new helper (external helper with phone if provided)
        $helper_phone_value = !empty($helper_phone) ? "'$helper_phone'" : "'N/A'";
        $insert_helper = mysqli_query($conn, "INSERT INTO tbl_staff (staff_name, staff_role, staff_phone, office_id, branch_office, active_status)
                                              VALUES ('$manual_helper_name', 'Helper', $helper_phone_value, 1, 'External', 1)");
        if ($insert_helper) {
            $helper_id = mysqli_insert_id($conn);
        }
    }
}

// Log incoming request size and docket keys for debugging when many dockets are posted
error_log('save_trip_modern.php called - POST count: ' . count($_POST) . ', raw_dockets keys: ' . implode(',', array_keys((array)$raw_dockets)));

// Normalize dockets: remove empty entries and ensure array structure
$dockets = [];
if (is_array($raw_dockets)) {
    foreach ($raw_dockets as $key => $entry) {
        if (!is_array($entry)) continue;
        $doc_no = trim($entry['doc_no'] ?? '');
        if ($doc_no === '') continue; // skip empty docket entries
        // ensure well-formed sub-array (keep only relevant fields to avoid massive POST injection)
        $dockets[] = [
            'doc_no' => strtoupper($doc_no),
            'service_type' => $entry['service_type'] ?? 'Standard',
            'company_id' => intval($entry['company_id'] ?? 0),
            'company_address' => $entry['company_address'] ?? null,
            'client_name' => $entry['client_name'] ?? null,
            'client_phone' => $entry['client_phone'] ?? null,
            'client_email' => $entry['client_email'] ?? null,
            'client_address' => $entry['client_address'] ?? null,
            'weight' => $entry['weight'] ?? 0,
            'box' => $entry['box'] ?? 0,
            'dimensions' => $entry['dimensions'] ?? null,
            'eway_bill' => $entry['eway_bill'] ?? null,
            'invoice_no' => !empty($entry['invoice_no']) ? $entry['invoice_no'] : 'N/A',
            'invoice_amount' => !empty($entry['invoice_amount']) ? floatval($entry['invoice_amount']) : 0,
        ];
    }
}

error_log('save_trip_modern.php normalized docket count: ' . count($dockets));

// Get office details for auto-sync
$office_name = 'N/A';
if ($office_id > 0) {
    $office_result = mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id = $office_id");
    if ($office_result && $office_row = mysqli_fetch_assoc($office_result)) {
        $office_name = $office_row['office_name'];
    }
}

// Validation - Allow manual driver/helper names if IDs are not set
$has_driver = ($driver_id > 0 || !empty($manual_driver_name));
$has_valid_trip_details = ($office_id > 0 && $car_id > 0 && $has_driver && !empty($pickup_datetime) && !empty($dockets));

if (!$has_valid_trip_details) {
    $_SESSION['error_msg'] = 'Please fill all required fields (Office, Car, Driver, Pickup Date/Time) and add at least one docket!';
    header('Location: add_trip_modern.php?msg=error');
    exit;
}

// Generate unique trip group ID
$trip_group_id = 'TRIP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Start transaction
mysqli_begin_transaction($conn);

try {
    $success_count = 0;
    $duplicate_dockets = [];
    
    // First, check for duplicates in docket_details
    foreach ($dockets as $docket) {
        $doc_no = trim($docket['doc_no'] ?? '');
        if (empty($doc_no)) continue;
        
        // Check if docket already exists in docket_details
        if ($docketManager->docketExists($doc_no)) {
            $duplicate_dockets[] = $doc_no;
        }
    }
    
    // If duplicates found, rollback and show error
    if (!empty($duplicate_dockets)) {
        mysqli_rollback($conn);
        $duplicate_list = implode(', ', $duplicate_dockets);
        $_SESSION['error_msg'] = 'Duplicate docket(s) found: ' . $duplicate_list . '. These dockets already exist in the system!';
        header('Location: add_trip_modern.php?msg=duplicate&dockets=' . urlencode($duplicate_list));
        exit;
    }
    
    // Insert each docket into docket_details with auto-sync
    foreach ($dockets as $docket) {
        $doc_no = trim($docket['doc_no'] ?? '');
        
        // Skip if docket number is empty
        if (empty($doc_no)) {
            continue;
        }
        
        // Prepare docket data for auto-sync
        $docketData = [
            'doc_no' => $doc_no,
            'trip_group_id' => $trip_group_id,
            'service_type' => $docket['service_type'] ?? 'Standard',
            'doc_type' => 'DRS',
            'status' => 'Picked Up',
            
            // Office data (will auto-sync from tbl_offices)
            'office_id' => $office_id,
            'branch_office' => $office_name,
            
            // Company/Sender data (will auto-sync if company_id provided)
            'company_id' => intval($docket['company_id'] ?? 0) ?: null,
            'company_address' => $docket['company_address'] ?? null,
            'pickup_location' => $docket['company_address'] ?? null,
            
            // Client/Receiver data
            'client_name' => $docket['client_name'] ?? 'N/A',
            'client_phone' => $docket['client_phone'] ?? 'N/A',
            'client_email' => $docket['client_email'] ?? 'N/A',
            'client_address' => $docket['client_address'] ?? null,
            'delivery_location' => $docket['client_address'] ?? null,
            
            // Car data (will auto-sync from tbl_car)
            'car_id' => $car_id,
            
            // Driver data (will auto-sync from tbl_staff WHERE staff_role = 'Driver')
            'driver_id' => $driver_id,
            'driver_name' => $manual_driver_name ?: null,
            'driver_phone' => $driver_phone ?: null,
            
            // Helper data (will auto-sync from tbl_staff WHERE staff_role = 'Helper' if provided)
            'helper_id' => ($helper_id > 0) ? $helper_id : null,
            'helper_name' => $manual_helper_name ?: null,
            'helper_phone' => $helper_phone ?: null,
            
            // Package information
            'weight' => floatval($docket['weight'] ?? 0),
            'box' => intval($docket['box'] ?? 0),
            'dimensions' => $docket['dimensions'] ?? 'N/A',
            'eway_bill' => $docket['eway_bill'] ?? null,
            'invoice_no' => !empty($docket['invoice_no']) ? $docket['invoice_no'] : 'N/A',
            'invoice_amount' => !empty($docket['invoice_amount']) ? floatval($docket['invoice_amount']) : 0,
            
            // Creator tracking
            'created_by' => $_SESSION['user_id'] ?? null,
            'created_by_name' => $_SESSION['username'] ?? 'Unknown',
            
            // Dates
            'pickup_datetime' => $pickup_datetime,
        ];
        
        // Debug log
        error_log("Attempting to save docket $doc_no with driver_id=$driver_id, manual_driver=$manual_driver_name, helper_id=$helper_id, manual_helper=$manual_helper_name");
        
        // Save docket with auto-sync to docket_details table
        $result = $docketManager->saveDocket($docketData);
        
        if ($result['success']) {
            $success_count++;
            error_log("Successfully saved docket $doc_no");
        } else {
            // Log detailed error
            $error_msg = "Failed to save docket $doc_no: " . ($result['error'] ?? 'Unknown error');
            error_log($error_msg);
            // Throw exception to rollback transaction
            throw new Exception($error_msg);
        }
    }
    
    if ($success_count > 0) {
        // Commit transaction
        mysqli_commit($conn);

        // ========================================
        // SEND EMAIL NOTIFICATIONS TO COMPANY (Consignor/Sender) - DOCKET CREATED
        // ========================================

        // Send email for each successfully created docket
        foreach ($dockets as $docket) {
            $doc_no = trim($docket['doc_no'] ?? '');
            if (empty($doc_no)) continue;

            // Get full docket details from database
            $email_query = "SELECT * FROM docket_details WHERE doc_no = '".mysqli_real_escape_string($conn, $doc_no)."' LIMIT 1";
            $email_result = mysqli_query($conn, $email_query);
            $docket_data = mysqli_fetch_assoc($email_result);

            if ($docket_data && !empty($docket_data['company_email']) && filter_var($docket_data['company_email'], FILTER_VALIDATE_EMAIL)) {
                $email_subject = "📝 Docket Created - #" . $docket_data['doc_no'];
                $email_body = getDocketCreatedEmailTemplate($docket_data);
                @sendEmail($docket_data['company_email'], $email_subject, $email_body, $docket_data['company_name']);
                error_log("Docket created email sent to company: " . $docket_data['company_email'] . " for docket: " . $doc_no);
            }
        }

        $_SESSION['success_msg'] = "Trip created successfully with $success_count docket(s) in docket_details table! All details auto-synced from car, staff (drivers & helpers), and company tables.";
        header('Location: add_trip_modern.php?msg=success');
        exit;
    } else {
        // Rollback if no dockets were saved
        mysqli_rollback($conn);
        $_SESSION['error_msg'] = 'No valid dockets to save. Please check your input!';
        header('Location: add_trip_modern.php?msg=error');
        exit;
    }
    
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    error_log("Trip creation error: " . $e->getMessage());
    
    $_SESSION['error_msg'] = 'Error creating trip: ' . $e->getMessage();
    header('Location: add_trip_modern.php?msg=error');
    exit;
}
?>
