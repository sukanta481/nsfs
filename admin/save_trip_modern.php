<?php
require_once 'includes/top.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add_trip_modern.php');
    exit;
}

// Get trip details
$car_id = isset($_POST['car_id']) ? intval($_POST['car_id']) : 0;
$driver_id = isset($_POST['driver_id']) ? intval($_POST['driver_id']) : 0;
$helper_id = isset($_POST['helper_id']) ? intval($_POST['helper_id']) : 0;
$pickup_datetime = isset($_POST['pickup_datetime']) ? mysqli_real_escape_string($conn, $_POST['pickup_datetime']) : '';
$dockets = isset($_POST['dockets']) ? $_POST['dockets'] : [];

// Validation
if ($car_id <= 0 || $driver_id <= 0 || empty($pickup_datetime) || empty($dockets)) {
    $_SESSION['error_msg'] = 'Please fill all required fields and add at least one docket!';
    header('Location: add_trip_modern.php?msg=error');
    exit;
}

// Generate unique trip group ID
$trip_group_id = 'TRIP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Start transaction
mysqli_begin_transaction($conn);

try {
    $success_count = 0;
    
    // Insert each docket with the same trip_group_id
    foreach ($dockets as $docket) {
        // Sanitize inputs
        $doc_no = mysqli_real_escape_string($conn, $docket['doc_no'] ?? '');
        $service_type = mysqli_real_escape_string($conn, $docket['service_type'] ?? 'Standard');
        $company_id = intval($docket['company_id'] ?? 0);
        $company_address = mysqli_real_escape_string($conn, $docket['company_address'] ?? '');
        $client_name = mysqli_real_escape_string($conn, $docket['client_name'] ?? '');
        $client_phone = mysqli_real_escape_string($conn, $docket['client_phone'] ?? '');
        $client_email = mysqli_real_escape_string($conn, $docket['client_email'] ?? '');
        $client_address = mysqli_real_escape_string($conn, $docket['client_address'] ?? '');
        $weight = intval($docket['weight'] ?? 0);
        $box = intval($docket['box'] ?? 0);
        $dimensions = mysqli_real_escape_string($conn, $docket['dimensions'] ?? '');
        
        // Skip if essential fields are missing
        if (empty($doc_no) || $company_id <= 0 || empty($company_address) || empty($client_address) || empty($client_name)) {
            continue;
        }
        
        // Insert into tbl_shipping_details
        $insert_query = "INSERT INTO tbl_shipping_details (
            trip_group_id,
            doc_no,
            doc_type,
            service_type,
            branch_office,
            register_id,
            shipping_id,
            company_id,
            company_email,
            company_address,
            client_id,
            client_name,
            item,
            client_phone,
            client_email,
            client_address,
            box,
            weight,
            dimensions,
            rate,
            amount,
            unit_price,
            have_eoa_bill_no,
            eoa_bill_no,
            pay_to,
            pickup_dates,
            status,
            reason_of_delay,
            proof_of_delivery,
            tracking_link,
            car_id,
            car_number,
            rented_car,
            car_oil_amount,
            driver_id,
            driver_name,
            driver_number,
            helper_id,
            helper_name,
            helper_number,
            car_out_time
        ) VALUES (
            '$trip_group_id',
            '$doc_no',
            '',
            '$service_type',
            '',
            0,
            0,
            $company_id,
            '$client_email',
            '$company_address',
            0,
            '$client_name',
            NULL,
            '$client_phone',
            '$client_email',
            '$client_address',
            $box,
            $weight,
            " . (empty($dimensions) ? "NULL" : "'$dimensions'") . ",
            NULL,
            NULL,
            0,
            0,
            0,
            0,
            '$pickup_datetime',
            'Picked Up',
            '',
            '',
            '',
            $car_id,
            '',
            0,
            0,
            $driver_id,
            '',
            '',
            " . ($helper_id > 0 ? $helper_id : "NULL") . ",
            '',
            '',
            NULL
        )";
        
        if (mysqli_query($conn, $insert_query)) {
            $shipping_details_id = mysqli_insert_id($conn);
            $success_count++;
            
            // Add to history if table exists
            $check_table = @mysqli_query($conn, "SHOW TABLES LIKE 'tbl_shipping_history'");
            if ($check_table && mysqli_num_rows($check_table) > 0) {
                $admin_name = $_SESSION['user_name'] ?? $_SESSION['admin'] ?? 'Admin';
                $history_insert = "INSERT INTO tbl_shipping_history (
                    shipping_details_id, 
                    status, 
                    notes, 
                    created_at
                ) VALUES (
                    $shipping_details_id,
                    'Picked Up',
                    'Trip created by $admin_name',
                    NOW()
                )";
                @mysqli_query($conn, $history_insert);
            }
        }
    }
    
    if ($success_count > 0) {
        // Commit transaction
        mysqli_commit($conn);
        
        $_SESSION['success_msg'] = "Trip created successfully with $success_count docket(s)!";
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
