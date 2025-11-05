<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name('pro');
    session_start();
}

// Check if user is logged in
if(!isset($_SESSION['aid']) && !isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}

require 'conn.php';

// Check database connection
if(!$conn || mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if DocketDetailsManager exists, if not, continue without it
if(file_exists('DocketDetailsManager.php')) {
    require_once 'DocketDetailsManager.php';
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php?type=list_register&lp=ac');
    exit;
}

$docket_id = intval($_POST['docket_id'] ?? 0);

if($docket_id <= 0) {
    header('Location: register.php?type=list_register&lp=ac&error=' . urlencode('Invalid docket ID'));
    exit;
}

// Sanitize and collect form data
$doc_no = mysqli_real_escape_string($conn, trim($_POST['doc_no'] ?? ''));
$service_type = mysqli_real_escape_string($conn, trim($_POST['service_type'] ?? 'Standard'));
$doc_type = mysqli_real_escape_string($conn, trim($_POST['doc_type'] ?? 'DRS'));
$status = mysqli_real_escape_string($conn, trim($_POST['status'] ?? 'Pending'));
$office_id = intval($_POST['office_id'] ?? 0);
$pickup_datetime = $_POST['pickup_datetime'] ? mysqli_real_escape_string($conn, $_POST['pickup_datetime']) : null;
$delivery_datetime = $_POST['delivery_datetime'] ? mysqli_real_escape_string($conn, $_POST['delivery_datetime']) : null;

// Company/Sender
$company_id = intval($_POST['company_id'] ?? 0);
$company_phone = mysqli_real_escape_string($conn, trim($_POST['company_phone'] ?? 'N/A'));
$company_email = mysqli_real_escape_string($conn, trim($_POST['company_email'] ?? 'N/A'));
$company_address = mysqli_real_escape_string($conn, trim($_POST['company_address'] ?? ''));
$pickup_location = mysqli_real_escape_string($conn, trim($_POST['pickup_location'] ?? ''));

// Client/Receiver
$client_name = mysqli_real_escape_string($conn, trim($_POST['client_name'] ?? ''));
$client_phone = mysqli_real_escape_string($conn, trim($_POST['client_phone'] ?? 'N/A'));
$client_email = mysqli_real_escape_string($conn, trim($_POST['client_email'] ?? 'N/A'));
$client_address = mysqli_real_escape_string($conn, trim($_POST['client_address'] ?? ''));
$delivery_location = mysqli_real_escape_string($conn, trim($_POST['delivery_location'] ?? ''));

// Vehicle & Staff
$rented_car = intval($_POST['rented_car'] ?? 0);
$car_id = intval($_POST['car_id'] ?? 0);
$driver_id = intval($_POST['driver_id'] ?? 0);
$driver_phone = mysqli_real_escape_string($conn, trim($_POST['driver_phone'] ?? 'N/A'));
$helper_id = intval($_POST['helper_id'] ?? 0);
$helper_phone = mysqli_real_escape_string($conn, trim($_POST['helper_phone'] ?? 'N/A'));

// Package
$item = mysqli_real_escape_string($conn, trim($_POST['item'] ?? 'N/A'));
$box = intval($_POST['box'] ?? 0);
$weight = floatval($_POST['weight'] ?? 0);
$dimensions = mysqli_real_escape_string($conn, trim($_POST['dimensions'] ?? 'N/A'));
$invoice_no = mysqli_real_escape_string($conn, trim($_POST['invoice_no'] ?? 'N/A'));
$eway_bill = mysqli_real_escape_string($conn, trim($_POST['eway_bill'] ?? 'N/A'));
$rate = floatval($_POST['rate'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);

// Additional
$special_instructions = mysqli_real_escape_string($conn, trim($_POST['special_instructions'] ?? ''));
$remarks = mysqli_real_escape_string($conn, trim($_POST['remarks'] ?? ''));

// Validation
if(empty($doc_no)) {
    header('Location: edit_register_new.php?docket_id=' . $docket_id . '&error=' . urlencode('Docket number is required'));
    exit;
}

if($company_id <= 0) {
    header('Location: edit_register_new.php?docket_id=' . $docket_id . '&error=' . urlencode('Please select a company'));
    exit;
}

if(empty($client_name)) {
    header('Location: edit_register_new.php?docket_id=' . $docket_id . '&error=' . urlencode('Client name is required'));
    exit;
}

// Get company name
$company_query = mysqli_query($conn, "SELECT company_title FROM tbl_company WHERE company_id = $company_id");
$company_data = mysqli_fetch_assoc($company_query);
$company_name = $company_data ? mysqli_real_escape_string($conn, $company_data['company_title']) : 'N/A';

// Auto-sync car details if car_id is provided
$car_number = 'N/A';
$car_model = 'N/A';
if($car_id > 0) {
    $car_query = mysqli_query($conn, "SELECT car_number, car_model FROM tbl_car WHERE car_id = $car_id");
    if($car_data = mysqli_fetch_assoc($car_query)) {
        $car_number = mysqli_real_escape_string($conn, $car_data['car_number']);
        $car_model = mysqli_real_escape_string($conn, $car_data['car_model']);
    }
}

// Auto-sync driver details if driver_id is provided
$driver_name = 'N/A';
$driver_license = 'N/A';
if($driver_id > 0) {
    $driver_query = mysqli_query($conn, "SELECT driver_name, driver_license FROM tbl_driver WHERE driver_id = $driver_id");
    if($driver_data = mysqli_fetch_assoc($driver_query)) {
        $driver_name = mysqli_real_escape_string($conn, $driver_data['driver_name']);
        $driver_license = mysqli_real_escape_string($conn, $driver_data['driver_license']);
    }
}

// Auto-sync helper details if helper_id is provided
$helper_name = 'N/A';
if($helper_id > 0) {
    $helper_query = mysqli_query($conn, "SELECT helper_name FROM tbl_helper WHERE helper_id = $helper_id");
    if($helper_data = mysqli_fetch_assoc($helper_query)) {
        $helper_name = mysqli_real_escape_string($conn, $helper_data['helper_name']);
    }
}

// Build UPDATE query
$update_sql = "UPDATE docket_details SET 
    service_type = '$service_type',
    doc_type = '$doc_type',
    status = '$status',
    office_id = " . ($office_id > 0 ? $office_id : "NULL") . ",
    pickup_datetime = " . ($pickup_datetime ? "'$pickup_datetime'" : "NULL") . ",
    delivery_datetime = " . ($delivery_datetime ? "'$delivery_datetime'" : "NULL") . ",
    
    company_id = $company_id,
    company_name = '$company_name',
    company_phone = '$company_phone',
    company_email = '$company_email',
    company_address = '$company_address',
    pickup_location = '$pickup_location',
    
    client_name = '$client_name',
    client_phone = '$client_phone',
    client_email = '$client_email',
    client_address = '$client_address',
    delivery_location = '$delivery_location',
    
    rented_car = $rented_car,
    car_id = " . ($car_id > 0 ? $car_id : "NULL") . ",
    car_number = '$car_number',
    car_model = '$car_model',
    
    driver_id = " . ($driver_id > 0 ? $driver_id : "NULL") . ",
    driver_name = '$driver_name',
    driver_phone = '$driver_phone',
    driver_license = '$driver_license',
    
    helper_id = " . ($helper_id > 0 ? $helper_id : "NULL") . ",
    helper_name = '$helper_name',
    helper_phone = '$helper_phone',
    
    item = '$item',
    box = $box,
    weight = $weight,
    dimensions = '$dimensions',
    invoice_no = '$invoice_no',
    eway_bill = '$eway_bill',
    rate = $rate,
    amount = $amount,
    
    special_instructions = '$special_instructions',
    remarks = '$remarks',
    updated_at = NOW()
    
    WHERE docket_id = $docket_id";

// Execute update
if(mysqli_query($conn, $update_sql)) {
    // Success - redirect to list with success message
    header('Location: register.php?type=list_register&lp=ac&success=1');
    exit;
} else {
    // Error - log and redirect back to edit with error message
    $error = mysqli_error($conn);
    error_log("Docket Update Error: " . $error);
    error_log("SQL: " . $update_sql);
    
    header('Location: edit_register_new.php?docket_id=' . $docket_id . '&error=' . urlencode('Update failed: ' . $error));
    exit;
}
?>