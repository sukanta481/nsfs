<?php
include_once(__DIR__ . '/../../conn.php');
date_default_timezone_set('Asia/Kolkata');

// PHP upload & memory settings
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1);

if (isset($_POST['save_register'])) {
    // CAR SELECTION
    $rented_car      = $_POST['rented_car'] ?? '';
    $car_id          = $_POST['car_id'] ?? '';
    $driver_id       = $_POST['driver_id'] ?? '';
    $helper_id       = $_POST['helper_id'] ?? '';
    $car_number      = $_POST['car_number'] ?? '';
    $driver_name     = $_POST['driver_name'] ?? '';
    $helper_name     = $_POST['helper_name'] ?? '';
    $driver_number   = $_POST['driver_number'] ?? '';
    $driver_number_rent = $_POST['driver_number_rent'] ?? '';
    $helper_number   = $_POST['helper_number'] ?? '';
    $car_oil_amount  = $_POST['car_oil_amount'] ?? '';
    $car_in_time     = $_POST['car_in_time'] ?? '';
    $car_out_time    = $_POST['car_out_time'] ?? '';

    // DOC INFO
    $doc_no          = $_POST['doc_no'] ?? '';
    $doc_type        = $_POST['doc_type'] ?? '';
    $branch_office   = $_POST['branch_office'] ?? '';
    $box             = $_POST['box'] ?? 0;
    $weight          = $_POST['weight'] ?? 0;
    $pay_to          = $_POST['pay_to'] ?? '0';

    // COMPANY/CONSIGNOR INFO
    $company_id      = $_POST['company_id'] ?? '';
    $company_email   = '';
    if ($company_id) {
        $get_company = mysqli_query($conn, "SELECT company_email FROM tbl_company WHERE company_id='" . mysqli_real_escape_string($conn, $company_id) . "' LIMIT 1");
        if ($row = mysqli_fetch_assoc($get_company)) {
            $company_email = $row['company_email'];
        }
    }

    // CONSIGNEE (CLIENT) INFO
    $client_name     = $_POST['client_name'] ?? '';
    $client_phone    = $_POST['client_phone'] ?? '';
    $client_email    = $_POST['client_email'] ?? '';
    $client_address  = $_POST['client_address'] ?? '';

    // Set status as "Picked Up"
    $status = 'Picked Up';

    // Tracking link
    $tracking_link = "https://northsuperfastservice.com/deliveryHistory.php?doc_no=" . urlencode($doc_no);

    // --------- HANDLING RENTED OR PERSONAL CAR LOGIC ----------
    if ($rented_car == '1') {
    // Rented car: use manual inputs
    $final_car_id = '';
    $final_car_number = $car_number;
    $final_driver_id = '';
    $final_driver_name = $driver_name;
    $final_driver_number = $driver_number_rent;
    $final_helper_id = '';
    $final_helper_name = $helper_name;
    $final_helper_number = $helper_number;
} else {
    // Personal car: use selected IDs, fetch car number and helper name/number from tables
    $final_car_id = $car_id;
    // --- Fetch car number by car_id ---
    if ($car_id) {
        $car_number_sql = mysqli_query($conn, "SELECT car_number FROM tbl_car WHERE car_id='" . mysqli_real_escape_string($conn, $car_id) . "' LIMIT 1");
        if ($row = mysqli_fetch_assoc($car_number_sql)) {
            $final_car_number = $row['car_number'];
        } else {
            $final_car_number = '';
        }
    } else {
        $final_car_number = '';
    }
    $final_driver_id = $driver_id;
    $final_driver_name = '';
    $final_driver_number = $driver_number;
    $final_helper_id = $helper_id;
    // --- Fetch helper name/number by ID ---
    if ($helper_id) {
        $get_helper = mysqli_query($conn, "SELECT helper_name, helper_number FROM tbl_helper WHERE helper_id='" . mysqli_real_escape_string($conn, $helper_id) . "' LIMIT 1");
        if ($row = mysqli_fetch_assoc($get_helper)) {
            $final_helper_name = $row['helper_name'];
            $final_helper_number = $row['helper_number'];
        } else {
            $final_helper_name = '';
            $final_helper_number = $helper_number;
        }
    } else {
        $final_helper_name = '';
        $final_helper_number = $helper_number;
    }
}


    // ---------- SAVE THE DATA ----------
    $add_shipping_details_sql = "INSERT INTO tbl_shipping_details SET
        doc_no          = '" . mysqli_real_escape_string($conn, $doc_no) . "',
        doc_type        = '" . mysqli_real_escape_string($conn, $doc_type) . "',
        branch_office   = '" . mysqli_real_escape_string($conn, $branch_office) . "',
        company_id      = '" . mysqli_real_escape_string($conn, $company_id) . "',
        company_email   = '" . mysqli_real_escape_string($conn, $company_email) . "',
        client_name     = '" . mysqli_real_escape_string($conn, $client_name) . "',
        client_phone    = '" . mysqli_real_escape_string($conn, $client_phone) . "',
        client_email    = '" . mysqli_real_escape_string($conn, $client_email) . "',
        client_address  = '" . mysqli_real_escape_string($conn, $client_address) . "',
        box             = '" . mysqli_real_escape_string($conn, $box) . "',
        weight          = '" . mysqli_real_escape_string($conn, $weight) . "',
        pay_to          = '" . mysqli_real_escape_string($conn, $pay_to) . "',
        status          = '" . mysqli_real_escape_string($conn, $status) . "',
        tracking_link   = '" . mysqli_real_escape_string($conn, $tracking_link) . "',
        car_id          = '" . mysqli_real_escape_string($conn, $final_car_id) . "',
        car_number      = '" . mysqli_real_escape_string($conn, $final_car_number) . "',
        rented_car      = '" . mysqli_real_escape_string($conn, $rented_car) . "',
        driver_id       = '" . mysqli_real_escape_string($conn, $final_driver_id) . "',
        driver_name     = '" . mysqli_real_escape_string($conn, $final_driver_name) . "',
        driver_number   = '" . mysqli_real_escape_string($conn, $final_driver_number) . "',
        helper_id       = '" . mysqli_real_escape_string($conn, $final_helper_id) . "',
        helper_name     = '" . mysqli_real_escape_string($conn, $final_helper_name) . "',
        helper_number   = '" . mysqli_real_escape_string($conn, $final_helper_number) . "',
        car_oil_amount  = '" . mysqli_real_escape_string($conn, $car_oil_amount) . "',
        car_in_time     = '" . mysqli_real_escape_string($conn, $car_in_time) . "',
        car_out_time    = '" . mysqli_real_escape_string($conn, $car_out_time) . "',
        pickup_dates     = NOW()
    ";

    mysqli_query($conn, $add_shipping_details_sql) or die(mysqli_error($conn));
    $ship_id = mysqli_insert_id($conn);

    // --- INSERT FIRST STATUS ENTRY ("Picked Up") ---
    $status_note = "Parcel picked up from $client_name and received at North Super Fast Service Main Office, Kolkata. Docket number generated.";
    $location = "Kolkata";
    $updateddate = date('Y-m-d H:i:s');
    mysqli_query($conn, "INSERT INTO tbl_trip_status (ship_id, status, note, location, updateddate)
        VALUES ('$ship_id', 'Picked Up', '".mysqli_real_escape_string($conn, $status_note)."', '".mysqli_real_escape_string($conn, $location)."', '$updateddate')");

   // Redirect back to add_register.php with success message
    // ✅ This is correct!
    header("Location: ../../register.php?type=add_register&msg=success");
    exit;

}
?>
