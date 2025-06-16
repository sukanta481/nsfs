<?php
$message = '';
$tripmsg = ''; // Always define before using!
$type = $_GET['type'] ?? '';
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1);

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if(isset($_REQUEST['edit_trip']) && $_REQUEST['edit_trip']=="Update"){

    // 1. Update status and reason in tbl_shipping_details
    $edit_shipping_sql = "UPDATE tbl_shipping_details SET
        status='" . mysqli_real_escape_string($conn, $_REQUEST['status']) . "',
        reason_of_delay='" . mysqli_real_escape_string($conn, $_REQUEST['reason_of_delay']) . "'
        WHERE shipping_details_id='" . $_REQUEST['shipping_details_id'] . "'
    ";
    $edit_shipping_rs = mysqli_query($conn, $edit_shipping_sql);

    // 2. Always insert a new row in tbl_trip_status for history
    date_default_timezone_set("Asia/Kolkata");
    $d = date('Y-m-d H:i:s');
    $insert_status = mysqli_query($conn, "INSERT INTO `tbl_trip_status` (`ship_id`, `status`, `updateddate`) VALUES (
        '" . mysqli_real_escape_string($conn, $_REQUEST['shipping_details_id']) . "',
        '" . mysqli_real_escape_string($conn, $_REQUEST['status']) . "',
        '" . $d . "'
    )");

    // 3. Upload Proof of Delivery if present
    if (!empty($_FILES['proof_of_delivery']['name'])) {
        $image_name = time() . $_FILES['proof_of_delivery']['name'];
        $image_type = $_FILES['proof_of_delivery']['type'];
        $type = explode("/", "$image_type");
        $image_size = $_FILES['proof_of_delivery']['size'];
        $temp_name = $_FILES['proof_of_delivery']['tmp_name'];
        $dir = "post_img/";
        $uploadimage = $dir . $image_name;
        $upload = move_uploaded_file($temp_name, $uploadimage);

        $edit_pod_sql = "UPDATE tbl_shipping_details SET
            proof_of_delivery='" . $image_name . "'
            WHERE shipping_details_id='" . $_REQUEST['shipping_details_id'] . "'
        ";
        $edit_pod_rs = mysqli_query($conn, $edit_pod_sql);
    }

    if ($edit_shipping_rs) {
        $tripmsg .= showMessage("Status has been updated successfully", 'success');
    }

}
?>
