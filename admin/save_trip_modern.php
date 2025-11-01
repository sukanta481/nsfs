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
$pickup_time = isset($_POST['pickup_time']) ? mysqli_real_escape_string($conn, $_POST['pickup_time']) : '';
$dockets = isset($_POST['dockets']) ? $_POST['dockets'] : [];

// Validation
if ($car_id <= 0 || $driver_id <= 0 || empty($pickup_time) || empty($dockets)) {
    $_SESSION['error_msg'] = 'Please fill all required fields and add at least one docket!';
    header('Location: add_trip_modern.php?msg=error');
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    $success_count = 0;
    $trip_date = date('Y-m-d');
    
    // Insert each docket
    foreach ($dockets as $docket) {
        // Sanitize inputs
        $doc_no = mysqli_real_escape_string($conn, $docket['doc_no'] ?? '');
        $service_type = mysqli_real_escape_string($conn, $docket['service_type'] ?? 'Standard');
        $company_id = intval($docket['company_id'] ?? 0);
        $pickup_location = mysqli_real_escape_string($conn, $docket['pickup_location'] ?? '');
        $delivery_location = mysqli_real_escape_string($conn, $docket['delivery_location'] ?? '');
        $client_name = mysqli_real_escape_string($conn, $docket['client_name'] ?? '');
        $client_phone = mysqli_real_escape_string($conn, $docket['client_phone'] ?? '');
        $client_email = mysqli_real_escape_string($conn, $docket['client_email'] ?? '');
        $weight = floatval($docket['weight'] ?? 0);
        $boxes = intval($docket['boxes'] ?? 0);
        $dimensions = mysqli_real_escape_string($conn, $docket['dimensions'] ?? '');
        
        // Skip if essential fields are missing
        if (empty($doc_no) || $company_id <= 0 || empty($pickup_location) || empty($delivery_location) || empty($client_name)) {
            continue;
        }
        
        // Insert into tbl_shipping_details
        $insert_query = "INSERT INTO tbl_shipping_details (
            doc_no,
            service_type,
            company_id,
            car_id,
            driver_id,
            helper_id,
            pickup_location,
            pickup_time,
            delivery_location,
            client_name,
            client_phone,
            client_email,
            client_address,
            weight,
            box,
            dimensions,
            status,
            trip_date,
            created_at
        ) VALUES (
            '$doc_no',
            '$service_type',
            $company_id,
            $car_id,
            $driver_id,
            " . ($helper_id > 0 ? $helper_id : "NULL") . ",
            '$pickup_location',
            '$pickup_time',
            '$delivery_location',
            '$client_name',
            '$client_phone',
            '$client_email',
            '$delivery_location',
            $weight,
            $boxes,
            '$dimensions',
            'Picked Up',
            '$trip_date',
            NOW()
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
        header('Location: register.php?type=list_register&msg=success');
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
