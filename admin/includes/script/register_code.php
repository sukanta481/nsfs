<?php
$message = '';
$type = $_GET['type'] ?? ''; // FIXED: No more warning!

ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1);

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if(isset($_REQUEST['save_register']) && $_REQUEST['save_register']=="Save"){
    if($_REQUEST['rented_car'] == 1) {
        $driver_number = $_REQUEST['driver_number_rent'];
    } else {
        $driver_number = $_REQUEST['driver_number'];
    }

    $add_register_sql = "insert into tbl_register set     
        rented_car='" . mysqli_real_escape_string($conn, $_REQUEST['rented_car']) . "', 
        car_id='" . mysqli_real_escape_string($conn, $_REQUEST['car_id']) . "', 
        car_number='" . mysqli_real_escape_string($conn, $_REQUEST['car_number']) . "', 
        driver_id='" . mysqli_real_escape_string($conn, $_REQUEST['driver_id']) . "', 
        driver_name='" . mysqli_real_escape_string($conn, $_REQUEST['driver_name']) . "', 
        driver_number='" . mysqli_real_escape_string($conn, $driver_number) . "',     
        helper_id='" . mysqli_real_escape_string($conn, $_REQUEST['helper_id']) . "', 
        helper_number='" . mysqli_real_escape_string($conn, $_REQUEST['helper_number']) . "', 
        car_oil_amount='" . mysqli_real_escape_string($conn, $_REQUEST['car_oil_amount']) . "', 
        car_in_time='" . mysqli_real_escape_string($conn, $_REQUEST['car_in_time']) . "', 
        car_out_time='" . mysqli_real_escape_string($conn, $_REQUEST['car_out_time']) . "',
        register_date='" . date("Y-m-d") . "', 
        register_no='" . time() . "'";

    $add_register_exe = mysqli_query($conn, $add_register_sql) or die(mysqli_error($conn));
    $last_id = mysqli_insert_id($conn);

    $add_shipping_sql = "insert into  tbl_shipping set 
        register_id='" . $last_id . "', 
        trip_no='" . time() . "',
        shipping_date='" . date("Y-m-d") . "'";
    $add_shipping_rs = mysqli_query($conn, $add_shipping_sql);
    $last_id1 = mysqli_insert_id($conn);

    if(isset($_REQUEST['company_id'])) {
        $company_id_count = count($_REQUEST['company_id']);
        for($i = 0; $i < $company_id_count; $i++) {
            if($_REQUEST['company_id'][$i] != '') {
                $add_shipping_sql = "insert into  tbl_shipping_details set
                    register_id='" . $last_id . "',
                    shipping_id='" . $last_id1 . "',
                    company_id='" . mysqli_real_escape_string($conn, $_REQUEST['company_id'][$i]) . "',
                    client_id='" . mysqli_real_escape_string($conn, $_REQUEST['client_id'][$i]) . "',
                    client_name='" . mysqli_real_escape_string($conn, $_REQUEST['client_name'][$i]) . "',
                    client_phone='" . mysqli_real_escape_string($conn, $_REQUEST['client_phone'][$i]) . "',
                    client_address='" . mysqli_real_escape_string($conn, $_REQUEST['client_address'][$i]) . "',
                    doc='" . mysqli_real_escape_string($conn, $_REQUEST['doc'][$i]) . "',
                    box='" . mysqli_real_escape_string($conn, $_REQUEST['box'][$i]) . "',
                    weight='" . mysqli_real_escape_string($conn, $_REQUEST['weight'][$i]) . "',
                    unit_price='" . mysqli_real_escape_string($conn, $_REQUEST['unit_price'][$i]) . "',
                    have_eoa_bill_no='" . mysqli_real_escape_string($conn, $_REQUEST['have_eoa_bill_no'][$i]) . "',
                    eoa_bill_no='" . mysqli_real_escape_string($conn, $_REQUEST['eoa_bill_no'][$i]) . "',
                    pay_to='" . mysqli_real_escape_string($conn, $_REQUEST['pay_to'][$i]) . "',
                    status='Processing'";
                $add_shipping_rs = mysqli_query($conn, $add_shipping_sql);
            }
        }
    }

    if($add_register_exe) {
        $registermsg .= showMessage("Register has been added successfully", 'success');
    }
}

if(isset($_REQUEST['edit_register']) && $_REQUEST['edit_register']=="Update"){
    if($_REQUEST['rented_car'] == 1) {
        $driver_number = $_REQUEST['driver_number_rent'];
    } else {
        $driver_number = $_REQUEST['driver_number'];
    }

    $edit_register_sql1 = "update tbl_register set
        rented_car='" . mysqli_real_escape_string($conn, $_REQUEST['rented_car']) . "', 
        car_id='" . mysqli_real_escape_string($conn, $_REQUEST['car_id']) . "', 
        car_number='" . mysqli_real_escape_string($conn, $_REQUEST['car_number']) . "', 
        driver_id='" . mysqli_real_escape_string($conn, $_REQUEST['driver_id']) . "', 
        driver_name='" . mysqli_real_escape_string($conn, $_REQUEST['driver_name']) . "', 
        driver_number='" . mysqli_real_escape_string($conn, $driver_number) . "', 
        helper_id='" . mysqli_real_escape_string($conn, $_REQUEST['helper_id']) . "', 
        helper_number='" . mysqli_real_escape_string($conn, $_REQUEST['helper_number']) . "', 
        car_oil_amount='" . mysqli_real_escape_string($conn, $_REQUEST['car_oil_amount']) . "', 
        car_in_time='" . mysqli_real_escape_string($conn, $_REQUEST['car_in_time']) . "', 
        car_out_time='" . mysqli_real_escape_string($conn, $_REQUEST['car_out_time']) . "'
        where register_id ='" . $_REQUEST['register_id'] . "'";
    $edit_register_exe1 = mysqli_query($conn, $edit_register_sql1)  or die(mysqli_error($conn));	

    if(isset($_REQUEST['shipping_details_id'])) {
        $shipping_details_id_count = count($_REQUEST['shipping_details_id']);
        for($i = 0; $i < $shipping_details_id_count; $i++) {
            if($_REQUEST['company_id_edit'][$i] != '') {
                $edit_shipping_sql = "update  tbl_shipping_details set
                    company_id='" . mysqli_real_escape_string($conn, $_REQUEST['company_id_edit'][$i]) . "',
                    client_id='" . mysqli_real_escape_string($conn, $_REQUEST['client_id_edit'][$i]) . "',
                    client_name='" . mysqli_real_escape_string($conn, $_REQUEST['client_name_edit'][$i]) . "',
                    client_phone='" . mysqli_real_escape_string($conn, $_REQUEST['client_phone_edit'][$i]) . "',
                    client_address='" . mysqli_real_escape_string($conn, $_REQUEST['client_address_edit'][$i]) . "',
                    doc='" . mysqli_real_escape_string($conn, $_REQUEST['doc_edit'][$i]) . "',
                    box='" . mysqli_real_escape_string($conn, $_REQUEST['box_edit'][$i]) . "',
                    weight='" . mysqli_real_escape_string($conn, $_REQUEST['weight_edit'][$i]) . "',
                    unit_price='" . mysqli_real_escape_string($conn, $_REQUEST['unit_price'][$i]) . "',
                    have_eoa_bill_no='" . mysqli_real_escape_string($conn, $_REQUEST['have_eoa_bill_no_edit'][$i]) . "',
                    eoa_bill_no='" . mysqli_real_escape_string($conn, $_REQUEST['eoa_bill_no_edit'][$i]) . "',
                    pay_to='" . mysqli_real_escape_string($conn, $_REQUEST['pay_to_edit'][$i]) . "'
                    where shipping_details_id='" . $_REQUEST['shipping_details_id'][$i] . "'";
                $edit_shipping_rs = mysqli_query($conn, $edit_shipping_sql);
            }
        }
    }

    if(isset($_REQUEST['company_id'])) {
        $company_id_count = count($_REQUEST['company_id']);
        for($i = 0; $i < $company_id_count; $i++) {
            if($_REQUEST['company_id'][$i] != '') {
                $add_shipping_sql = "insert into  tbl_shipping_details set
                    register_id='" . $_REQUEST['register_id'] . "',
                    shipping_id='" . $_REQUEST['shipping_id'] . "',
                    company_id='" . mysqli_real_escape_string($conn, $_REQUEST['company_id'][$i]) . "',
                    client_id='" . mysqli_real_escape_string($conn, $_REQUEST['client_id'][$i]) . "',
                    client_name='" . mysqli_real_escape_string($conn, $_REQUEST['client_name'][$i]) . "',
                    client_phone='" . mysqli_real_escape_string($conn, $_REQUEST['client_phone'][$i]) . "',
                    client_address='" . mysqli_real_escape_string($conn, $_REQUEST['client_address'][$i]) . "',
                    doc='" . mysqli_real_escape_string($conn, $_REQUEST['doc'][$i]) . "',
                    box='" . mysqli_real_escape_string($conn, $_REQUEST['box'][$i]) . "',
                    weight='" . mysqli_real_escape_string($conn, $_REQUEST['weight'][$i]) . "',
                    unit_price='" . mysqli_real_escape_string($conn, $_REQUEST['unit_price'][$i]) . "',
                    have_eoa_bill_no='" . mysqli_real_escape_string($conn, $_REQUEST['have_eoa_bill_no'][$i]) . "',
                    eoa_bill_no='" . mysqli_real_escape_string($conn, $_REQUEST['eoa_bill_no'][$i]) . "',
                    pay_to='" . mysqli_real_escape_string($conn, $_REQUEST['pay_to'][$i]) . "',
                    status='Processing'";
                $add_shipping_rs = mysqli_query($conn, $add_shipping_sql);
            }
        }
    }

    if($edit_register_exe1) {
        $registermsg .= showMessage("Register has been updated successfully", 'success');
    }
}

// SAFE: Use ?? '' to avoid warnings!
$action = $_REQUEST['actnregister'] ?? '';
$register_id = $_REQUEST['register_id'] ?? '';
if($action == 'dellregister' && !empty($register_id)){
    $DelregisterSql = 'DELETE FROM tbl_register WHERE register_id  = "'.$register_id.'"';
    $DelregisterQuery = g_db_query($DelregisterSql);

    $DelregisterSql = 'DELETE FROM  tbl_shipping WHERE register_id  = "'.$register_id.'"';
    $DelregisterQuery = g_db_query($DelregisterSql);

    $DelregisterSql = 'DELETE FROM  tbl_shipping_details  WHERE register_id  = "'.$register_id.'"';
    $DelregisterQuery = g_db_query($DelregisterSql);

    if($DelregisterQuery){
        $registermsg .= showMessage('The Register Has Been Deleted','success');
    }
}
?>
