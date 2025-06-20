<?php
include_once(__DIR__ . '/../notifications/send_email.php');
include_once(__DIR__ . '/../notifications/send_sms.php');
include_once(__DIR__ . '/../notifications/send_whatsapp.php');

date_default_timezone_set('Asia/Kolkata');
$message = '';
$registermsg = '';
$type = $_GET['type'] ?? '';

// PHP upload & memory settings
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1);

// --- ADD REGISTER ---
if (isset($_REQUEST['save_register']) && $_REQUEST['save_register'] == "Save") {
    $rented_car = $_REQUEST['rented_car'] ?? '';
    $driver_number = ($rented_car == 1) ? ($_REQUEST['driver_number_rent'] ?? '') : ($_REQUEST['driver_number'] ?? '');

    $add_register_sql = "INSERT INTO tbl_register SET
        rented_car='" . mysqli_real_escape_string($conn, $rented_car) . "',
        car_id='" . mysqli_real_escape_string($conn, $_REQUEST['car_id'] ?? '') . "',
        car_number='" . mysqli_real_escape_string($conn, $_REQUEST['car_number'] ?? '') . "',
        driver_id='" . mysqli_real_escape_string($conn, $_REQUEST['driver_id'] ?? '') . "',
        driver_name='" . mysqli_real_escape_string($conn, $_REQUEST['driver_name'] ?? '') . "',
        driver_number='" . mysqli_real_escape_string($conn, $driver_number) . "',
        helper_id='" . mysqli_real_escape_string($conn, $_REQUEST['helper_id'] ?? '') . "',
        helper_number='" . mysqli_real_escape_string($conn, $_REQUEST['helper_number'] ?? '') . "',
        car_oil_amount='" . mysqli_real_escape_string($conn, $_REQUEST['car_oil_amount'] ?? '') . "',
        car_in_time='" . mysqli_real_escape_string($conn, $_REQUEST['car_in_time'] ?? '') . "',
        car_out_time='" . mysqli_real_escape_string($conn, $_REQUEST['car_out_time'] ?? '') . "',
        register_date='" . date("Y-m-d") . "',
        register_no='" . time() . "'";

    $add_register_exe = mysqli_query($conn, $add_register_sql) or die(mysqli_error($conn));
    $last_id = mysqli_insert_id($conn);

    $add_shipping_sql = "INSERT INTO tbl_shipping SET 
        register_id='" . $last_id . "',
        trip_no='" . time() . "',
        shipping_date='" . date("Y-m-d") . "'";
    $add_shipping_rs = mysqli_query($conn, $add_shipping_sql);
    $last_id1 = mysqli_insert_id($conn);

    // Shipping details (company, client etc.)
    $inserted_shipping_ids = [];
    if (!empty($_REQUEST['company_id']) && is_array($_REQUEST['company_id'])) {
        $company_id_count = count($_REQUEST['company_id']);
        for ($i = 0; $i < $company_id_count; $i++) {
            $company_id = $_REQUEST['company_id'][$i] ?? '';
            $client_id = $_REQUEST['client_id'][$i] ?? '';
            $client_name = $_REQUEST['client_name'][$i] ?? '';
            $client_phone = $_REQUEST['client_phone'][$i] ?? '';
            $client_email = $_REQUEST['client_email'][$i] ?? '';
            $client_address = $_REQUEST['client_address'][$i] ?? '';
            $doc = $_REQUEST['doc'][$i] ?? '';
            $box = $_REQUEST['box'][$i] ?? '';
            $weight = $_REQUEST['weight'][$i] ?? '';
            $unit_price = $_REQUEST['unit_price'][$i] ?? '';
            $have_eoa_bill_no = $_REQUEST['have_eoa_bill_no'][$i] ?? '';
            $eoa_bill_no = $_REQUEST['eoa_bill_no'][$i] ?? '';
            $pay_to = $_REQUEST['pay_to'][$i] ?? '';

            // Generate tracking link (always use deliveryHistory page)
            $tracking_link = "https://northsuperfastservice.com/deliveryHistory.php?doc_no=" . urlencode($doc);

            // Fetch consignor (company) info
            $company_email = '';
            $company_name = '';
            if ($company_id != '') {
                $sql_company = mysqli_query($conn, "SELECT company_title, company_email FROM tbl_company WHERE company_id='" . mysqli_real_escape_string($conn, $company_id) . "'");
                if ($sql_company && $company_row = mysqli_fetch_assoc($sql_company)) {
                    $company_email = $company_row['company_email'];
                    $company_name = $company_row['company_title'];
                }
            }

            if ($company_id != '') {
                $add_shipping_details_sql = "INSERT INTO tbl_shipping_details SET
                    register_id='" . $last_id . "',
                    shipping_id='" . $last_id1 . "',
                    company_id='" . mysqli_real_escape_string($conn, $company_id) . "',
                    company_email='" . mysqli_real_escape_string($conn, $company_email) . "',
                    client_id='" . mysqli_real_escape_string($conn, $client_id) . "',
                    client_name='" . mysqli_real_escape_string($conn, $client_name) . "',
                    client_phone='" . mysqli_real_escape_string($conn, $client_phone) . "',
                    client_email='" . mysqli_real_escape_string($conn, $client_email) . "',
                    client_address='" . mysqli_real_escape_string($conn, $client_address) . "',
                    doc='" . mysqli_real_escape_string($conn, $doc) . "',
                    box='" . mysqli_real_escape_string($conn, $box) . "',
                    weight='" . mysqli_real_escape_string($conn, $weight) . "',
                    unit_price='" . mysqli_real_escape_string($conn, $unit_price) . "',
                    have_eoa_bill_no='" . mysqli_real_escape_string($conn, $have_eoa_bill_no) . "',
                    eoa_bill_no='" . mysqli_real_escape_string($conn, $eoa_bill_no) . "',
                    tracking_link='" . mysqli_real_escape_string($conn, $tracking_link) . "',
                    pay_to='" . mysqli_real_escape_string($conn, $pay_to) . "',
                    status='Processing'";
                mysqli_query($conn, $add_shipping_details_sql);
                $shipping_details_id = mysqli_insert_id($conn);
                $inserted_shipping_ids[] = $shipping_details_id;

                // ================= NOTIFICATION SYSTEM (START) ================
                // --- Email to Consignee (client/receiver) ---
                if (!empty($client_email)) {
                    $subject_client = "Your Shipment Has Been Created: $doc";
                    $body_client = "Dear $client_name,<br>Your shipment (Doc No: <b>$doc</b>) is created and will be delivered soon.<br>Track your shipment: <a href='$tracking_link'>$tracking_link</a><br><br>Thanks,<br>North Super Fast Service";
                    sendShipmentEmail($client_email, $subject_client, $body_client);
                }

                // --- Email to Consignor (company/sender) ---
                if (!empty($company_email)) {
                    $subject_company = "You Have Dispatched a Shipment: $doc";
                    $body_company = "Dear $company_name,<br>Your package (Doc No: <b>$doc</b>) has been booked and is now in transit.<br>Track: <a href='$tracking_link'>$tracking_link</a><br><br>Thank you for choosing North Super Fast Service.";
                    sendShipmentEmail($company_email, $subject_company, $body_company);
                }

                // --- SMS/WhatsApp to Consignee (client) ---
                if (!empty($client_phone)) {
                    $sms_message = "Your shipment $doc is created. Track: $tracking_link";
                    sendShipmentSMS($client_phone, $sms_message);
                    sendShipmentWhatsApp($client_phone, $sms_message);
                }
                // ================= NOTIFICATION SYSTEM (END) ==================
            }
        }
    }

    // --- INSERT STATUS 'Created' FOR EVERY NEW SHIPMENT ---
    $created_time = date('Y-m-d H:i:s');
    foreach ($inserted_shipping_ids as $ship_id) {
        $ins_trip_status = "INSERT INTO tbl_trip_status (ship_id, status, updateddate) VALUES ('$ship_id', 'Created', '$created_time')";
        mysqli_query($conn, $ins_trip_status);
    }

    if ($add_register_exe) {
        $registermsg .= showMessage("Register has been added successfully", 'success');
    }
}

// --- EDIT REGISTER ---
// (Repeat similar logic for tracking_link if you allow editing doc no/status!)

if (isset($_REQUEST['edit_register']) && $_REQUEST['edit_register'] == "Update") {
    $rented_car = $_REQUEST['rented_car'] ?? '';
    $driver_number = ($rented_car == 1) ? ($_REQUEST['driver_number_rent'] ?? '') : ($_REQUEST['driver_number'] ?? '');

    $edit_register_sql1 = "UPDATE tbl_register SET
        rented_car='" . mysqli_real_escape_string($conn, $rented_car) . "',
        car_id='" . mysqli_real_escape_string($conn, $_REQUEST['car_id'] ?? '') . "',
        car_number='" . mysqli_real_escape_string($conn, $_REQUEST['car_number'] ?? '') . "',
        driver_id='" . mysqli_real_escape_string($conn, $_REQUEST['driver_id'] ?? '') . "',
        driver_name='" . mysqli_real_escape_string($conn, $_REQUEST['driver_name'] ?? '') . "',
        driver_number='" . mysqli_real_escape_string($conn, $driver_number) . "',
        helper_id='" . mysqli_real_escape_string($conn, $_REQUEST['helper_id'] ?? '') . "',
        helper_number='" . mysqli_real_escape_string($conn, $_REQUEST['helper_number'] ?? '') . "',
        car_oil_amount='" . mysqli_real_escape_string($conn, $_REQUEST['car_oil_amount'] ?? '') . "',
        car_in_time='" . mysqli_real_escape_string($conn, $_REQUEST['car_in_time'] ?? '') . "',
        car_out_time='" . mysqli_real_escape_string($conn, $_REQUEST['car_out_time'] ?? '') . "'
        WHERE register_id ='" . ($_REQUEST['register_id'] ?? '') . "'";
    $edit_register_exe1 = mysqli_query($conn, $edit_register_sql1) or die(mysqli_error($conn));

    // Update existing shipping_details if present
    if (!empty($_REQUEST['shipping_details_id']) && is_array($_REQUEST['shipping_details_id'])) {
        $shipping_details_id_count = count($_REQUEST['shipping_details_id']);
        for ($i = 0; $i < $shipping_details_id_count; $i++) {
            $company_id_edit = $_REQUEST['company_id_edit'][$i] ?? '';
            $client_id_edit = $_REQUEST['client_id_edit'][$i] ?? '';
            $client_name_edit = $_REQUEST['client_name_edit'][$i] ?? '';
            $client_phone_edit = $_REQUEST['client_phone_edit'][$i] ?? '';
            $client_email_edit = $_REQUEST['client_email_edit'][$i] ?? '';
            $client_address_edit = $_REQUEST['client_address_edit'][$i] ?? '';
            $doc_edit = $_REQUEST['doc_edit'][$i] ?? '';
            $box_edit = $_REQUEST['box_edit'][$i] ?? '';
            $weight_edit = $_REQUEST['weight_edit'][$i] ?? '';
            $unit_price = $_REQUEST['unit_price'][$i] ?? '';
            $have_eoa_bill_no_edit = $_REQUEST['have_eoa_bill_no_edit'][$i] ?? '';
            $eoa_bill_no_edit = $_REQUEST['eoa_bill_no_edit'][$i] ?? '';
            $pay_to_edit = $_REQUEST['pay_to_edit'][$i] ?? '';

            // Generate tracking link for edits as well
            $tracking_link = "https://northsuperfastservice.com/deliveryHistory.php?doc_no=" . urlencode($doc_edit);

            // Fetch company_email for edit
            $company_email_edit = '';
            $company_name_edit = '';
            if ($company_id_edit != '') {
                $sql_company = mysqli_query($conn, "SELECT company_title, company_email FROM tbl_company WHERE company_id='" . mysqli_real_escape_string($conn, $company_id_edit) . "'");
                if ($sql_company && $company_row = mysqli_fetch_assoc($sql_company)) {
                    $company_email_edit = $company_row['company_email'];
                    $company_name_edit = $company_row['company_title'];
                }
            }

            if ($company_id_edit != '') {
                $edit_shipping_sql = "UPDATE tbl_shipping_details SET
                    company_id='" . mysqli_real_escape_string($conn, $company_id_edit) . "',
                    company_email='" . mysqli_real_escape_string($conn, $company_email_edit) . "',
                    client_id='" . mysqli_real_escape_string($conn, $client_id_edit) . "',
                    client_name='" . mysqli_real_escape_string($conn, $client_name_edit) . "',
                    client_phone='" . mysqli_real_escape_string($conn, $client_phone_edit) . "',
                    client_email='" . mysqli_real_escape_string($conn, $client_email_edit) . "',
                    client_address='" . mysqli_real_escape_string($conn, $client_address_edit) . "',
                    doc='" . mysqli_real_escape_string($conn, $doc_edit) . "',
                    box='" . mysqli_real_escape_string($conn, $box_edit) . "',
                    weight='" . mysqli_real_escape_string($conn, $weight_edit) . "',
                    unit_price='" . mysqli_real_escape_string($conn, $unit_price) . "',
                    have_eoa_bill_no='" . mysqli_real_escape_string($conn, $have_eoa_bill_no_edit) . "',
                    eoa_bill_no='" . mysqli_real_escape_string($conn, $eoa_bill_no_edit) . "',
                    tracking_link='" . mysqli_real_escape_string($conn, $tracking_link) . "',
                    pay_to='" . mysqli_real_escape_string($conn, $pay_to_edit) . "'
                    WHERE shipping_details_id='" . ($_REQUEST['shipping_details_id'][$i] ?? '') . "'";
                mysqli_query($conn, $edit_shipping_sql);
            }
        }
    }

    // Insert new shipping_details if any
    if (!empty($_REQUEST['company_id']) && is_array($_REQUEST['company_id'])) {
        $company_id_count = count($_REQUEST['company_id']);
        for ($i = 0; $i < $company_id_count; $i++) {
            $company_id = $_REQUEST['company_id'][$i] ?? '';
            $client_id = $_REQUEST['client_id'][$i] ?? '';
            $client_name = $_REQUEST['client_name'][$i] ?? '';
            $client_phone = $_REQUEST['client_phone'][$i] ?? '';
            $client_email = $_REQUEST['client_email'][$i] ?? '';
            $client_address = $_REQUEST['client_address'][$i] ?? '';
            $doc = $_REQUEST['doc'][$i] ?? '';
            $box = $_REQUEST['box'][$i] ?? '';
            $weight = $_REQUEST['weight'][$i] ?? '';
            $unit_price = $_REQUEST['unit_price'][$i] ?? '';
            $have_eoa_bill_no = $_REQUEST['have_eoa_bill_no'][$i] ?? '';
            $eoa_bill_no = $_REQUEST['eoa_bill_no'][$i] ?? '';
            $pay_to = $_REQUEST['pay_to'][$i] ?? '';

            $tracking_link = "https://northsuperfastservice.com/deliveryHistory.php?doc_no=" . urlencode($doc);

            $company_email = '';
            $company_name = '';
            if ($company_id != '') {
                $sql_company = mysqli_query($conn, "SELECT company_title, company_email FROM tbl_company WHERE company_id='" . mysqli_real_escape_string($conn, $company_id) . "'");
                if ($sql_company && $company_row = mysqli_fetch_assoc($sql_company)) {
                    $company_email = $company_row['company_email'];
                    $company_name = $company_row['company_title'];
                }
            }

            if ($company_id != '') {
                $add_shipping_sql = "INSERT INTO tbl_shipping_details SET
                    register_id='" . ($_REQUEST['register_id'] ?? '') . "',
                    shipping_id='" . ($_REQUEST['shipping_id'] ?? '') . "',
                    company_id='" . mysqli_real_escape_string($conn, $company_id) . "',
                    company_email='" . mysqli_real_escape_string($conn, $company_email) . "',
                    client_id='" . mysqli_real_escape_string($conn, $client_id) . "',
                    client_name='" . mysqli_real_escape_string($conn, $client_name) . "',
                    client_phone='" . mysqli_real_escape_string($conn, $client_phone) . "',
                    client_email='" . mysqli_real_escape_string($conn, $client_email) . "',
                    client_address='" . mysqli_real_escape_string($conn, $client_address) . "',
                    doc='" . mysqli_real_escape_string($conn, $doc) . "',
                    box='" . mysqli_real_escape_string($conn, $box) . "',
                    weight='" . mysqli_real_escape_string($conn, $weight) . "',
                    unit_price='" . mysqli_real_escape_string($conn, $unit_price) . "',
                    have_eoa_bill_no='" . mysqli_real_escape_string($conn, $have_eoa_bill_no) . "',
                    eoa_bill_no='" . mysqli_real_escape_string($conn, $eoa_bill_no) . "',
                    tracking_link='" . mysqli_real_escape_string($conn, $tracking_link) . "',
                    pay_to='" . mysqli_real_escape_string($conn, $pay_to) . "',
                    status='Processing'";
                mysqli_query($conn, $add_shipping_sql);
            }
        }
    }

    if ($edit_register_exe1) {
        $registermsg .= showMessage("Register has been updated successfully", 'success');
    }
}

// --- DELETE REGISTER ---
$action = $_REQUEST['actnregister'] ?? '';
$register_id = $_REQUEST['register_id'] ?? '';
if ($action == 'dellregister' && !empty($register_id)) {
    $DelregisterSql = 'DELETE FROM tbl_register WHERE register_id = "' . mysqli_real_escape_string($conn, $register_id) . '"';
    $DelregisterQuery = g_db_query($DelregisterSql);

    $DelregisterSql = 'DELETE FROM tbl_shipping WHERE register_id = "' . mysqli_real_escape_string($conn, $register_id) . '"';
    $DelregisterQuery = g_db_query($DelregisterSql);

    $DelregisterSql = 'DELETE FROM tbl_shipping_details WHERE register_id = "' . mysqli_real_escape_string($conn, $register_id) . '"';
    $DelregisterQuery = g_db_query($DelregisterSql);

    if ($DelregisterQuery) {
        $registermsg .= showMessage('The Register Has Been Deleted', 'success');
    }
}
?>
