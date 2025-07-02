<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ... rest of your code
?>

<?php
include_once(__DIR__ . '/includes/notifications/send_email.php');
// (Add SMS/WhatsApp includes here if needed)

$status_message = '';

if (isset($_POST['edit_trip'])) {
    $shipping_details_id = $_POST['shipping_details_id'];
    $shipping_id = $_POST['shipping_id'];
    $new_status = trim($_POST['status']);
    $reason_of_delay = $_POST['reason_of_delay'] ?? '';

    // 1. Fetch details BEFORE update
    $sql_prev = mysqli_query($conn, "SELECT status, doc_no, company_email, client_email, client_name, company_id, proof_of_delivery FROM tbl_shipping_details WHERE shipping_details_id = '$shipping_details_id'");
    $prev_row = mysqli_fetch_assoc($sql_prev);

    $doc = $prev_row['doc_no'];
    $client_email = $prev_row['client_email'];
    $client_name = $prev_row['client_name'];
    $company_email = $prev_row['company_email'];
    $company_id = $prev_row['company_id'];
    $proof_of_delivery = $prev_row['proof_of_delivery'];

    // Company name
    $company_name = '';
    if ($company_id) {
        $csql = mysqli_query($conn, "SELECT company_title FROM tbl_company WHERE company_id='$company_id'");
        if ($crow = mysqli_fetch_assoc($csql)) $company_name = $crow['company_title'];
    }

    // File upload for POD (Proof of Delivery)
    if ($new_status == 'Delivered' && !empty($_FILES['proof_of_delivery']['name'])) {
        $pod_name = time() . $_FILES['proof_of_delivery']['name'];
        $pod_tmp = $_FILES['proof_of_delivery']['tmp_name'];
        move_uploaded_file($pod_tmp, "post_img/$pod_name");
        $proof_of_delivery = $pod_name;
    }

    // SEND NOTIFICATION only for 'Out for Delivery' and 'Delivered'
    if ($new_status === "Out for Delivery" || $new_status === "Delivered") {
        $tracking_link = "https://northsuperfastservice.com/deliveryHistory.php?doc_no=" . urlencode($doc);

        // To Consignee
        if (!empty($client_email)) {
            $subject_client = "Shipment Status Update: $doc";
            $body_client = "Dear $client_name,<br>Your shipment (Doc No: <b>$doc</b>) status is now <b>$new_status</b>.<br>Track here: <a href='$tracking_link'>$tracking_link</a><br><br>Thank you.<br>North Super Fast Service";
            sendShipmentEmail($client_email, $subject_client, $body_client);
        }
        // To Consignor
        if (!empty($company_email)) {
            $subject_company = "Status Update for Your Dispatched Shipment: $doc";
            $body_company = "Dear $company_name,<br>Your consignment (Doc No: <b>$doc</b>) status is now <b>$new_status</b>.<br>Track here: <a href='$tracking_link'>$tracking_link</a><br><br>North Super Fast Service";
            sendShipmentEmail($company_email, $subject_company, $body_company);
        }
        $status_message = "<div style='background:#26c6da;padding:12px;color:#fff;border-radius:5px;margin-bottom:12px;'>Notification sent for <b>$new_status</b> status and status updated successfully!</div>";
    } else {
        $status_message = "<div style='background:#4dd0e1;padding:12px;color:#fff;border-radius:5px;margin-bottom:12px;'>Status updated successfully!</div>";
    }

    // UPDATE status in DB
    $update_sql = "UPDATE tbl_shipping_details SET 
        status='$new_status', 
        reason_of_delay='" . mysqli_real_escape_string($conn, $reason_of_delay) . "', 
        proof_of_delivery='$proof_of_delivery' 
        WHERE shipping_details_id='$shipping_details_id'";
    mysqli_query($conn, $update_sql);

    // Log audit (optional)
    $created_time = date('Y-m-d H:i:s');
    $ins_trip_status = "INSERT INTO tbl_trip_status (ship_id, status, updateddate) VALUES ('$shipping_details_id', '$new_status', '$created_time')";
    mysqli_query($conn, $ins_trip_status);
}

// --- GET SHIPPING DETAILS (for display) ---
$shipping_details_id = $_REQUEST['shipping_details_id'];
$get_shipping_sql = "SELECT * FROM tbl_shipping_details WHERE shipping_details_id='" . $shipping_details_id . "'";
$get_shipping_rs = mysqli_query($conn, $get_shipping_sql);
$get_shipping_row = mysqli_fetch_array($get_shipping_rs);
$current_status = $get_shipping_row['status'];
?>

<!-- HTML starts here -->
<div class="x_panel">
    <div class="x_title">
        <h2>Edit Doc Status</h2>
        <div class="clearfix"></div>
    </div>
    <div class="x_content">
        <?php if(isset($status_message)) echo $status_message; ?>
        <form id="edit_trip_form" action="" method="post" name="edit_trip_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">

            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignor Company Name:</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <?php
                    $get_company_sql = "SELECT * FROM tbl_company WHERE company_id='" . $get_shipping_row['company_id'] . "'";
                    $get_company_rs = mysqli_query($conn, $get_company_sql);
                    $get_company_row = mysqli_fetch_array($get_company_rs);
                    echo $get_company_row['company_title'];
                    ?>
                </div>
            </div>

            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignee Name:</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <?= $get_shipping_row['client_name']; ?>
                </div>
            </div>

            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Doc:</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <?= $get_shipping_row['doc_no']; ?>
                </div>
            </div>

            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Box (unit):</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <?= $get_shipping_row['box']; ?>
                </div>
            </div>

            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Weight (kg):</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <?= $get_shipping_row['weight']; ?>
                </div>
            </div>

            <?php if ($get_shipping_row['have_eoa_bill_no'] == 1) { ?>
                <div class="item form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">EOA Bill No. :</label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <?= $get_shipping_row['eoa_bill_no']; ?>
                    </div>
                </div>
            <?php } ?>

            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Status:</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <select class="form-control col-md-7 col-xs-12" name="status" onchange="change_status(this.value);">
                        <option value="<?= htmlspecialchars($current_status) ?>" selected>
                            <?= $current_status ? $current_status : "Select Status" ?>
                        </option>
                        <?php
                        $status_list = [
                            'Created',
                            'Picked Up',
                            'In Transit',
                            'Delay',
                            'Out for Delivery',
                            'Delivered'
                        ];
                        foreach ($status_list as $s) {
                            if ($s != $current_status) {
                                echo '<option value="' . $s . '">' . $s . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="item form-group" id="rod_sec" <?php if ($get_shipping_row['status'] == 'Delay') { ?>style="display:block;"<?php } else { ?>style="display:none;"<?php } ?>>
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Reason Of Delay:</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <select class="form-control col-md-7 col-xs-12" name="reason_of_delay">
                        <option value="">Select Reason Of Delay</option>
                        <?php
                        $delay_sql = "SELECT * FROM tbl_delay_reason ORDER BY delay_reason_name ASC";
                        $delay_rs = mysqli_query($conn, $delay_sql);
                        while ($delay_row = mysqli_fetch_array($delay_rs)) {
                            ?>
                            <option value="<?= $delay_row['delay_reason_name']; ?>" <?php if ($get_shipping_row['reason_of_delay'] == $delay_row['delay_reason_name']) {
                                                                                        echo "selected";
                                                                                    } ?>><?= $delay_row['delay_reason_name']; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="item form-group" id="pod_sec" <?php if ($get_shipping_row['status'] == 'Delivered') { ?>style="display:block;"<?php } else { ?>style="display:none;"<?php } ?>>
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Proof of Delivery:</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <?php
                    if ($get_shipping_row['proof_of_delivery'] != '') {
                        ?>
                        <img src="post_img/<?= $get_shipping_row['proof_of_delivery']; ?>" width="200" height="200">
                    <?php
                    }
                    ?>
                    <input type="file" name="proof_of_delivery">
                </div>
            </div>

            <script>
                function change_status(v) {
                    if (v == 'Delay') {
                        $("#rod_sec").show();
                        $("#pod_sec").hide();
                    } else if (v == 'Delivered') {
                        $("#rod_sec").hide();
                        $("#pod_sec").show();
                    } else {
                        $("#rod_sec").hide();
                        $("#pod_sec").hide();
                    }
                }
            </script>

            <div class="ln_solid"></div>
            <div class="form-group">
                <div class="col-md-6 col-md-offset-3">
                    <input type="hidden" name="shipping_details_id" value="<?= $get_shipping_row['shipping_details_id']; ?>">
                    <input type="hidden" name="shipping_id" value="<?= $get_shipping_row['shipping_id']; ?>">
                    <input type="submit" name="edit_trip" value="Update" onclick="return validate_form('edit_trip_form');" class="btn btn-success btn-submit">
                    <input type="button" name="add_event_cancel" value="Cancel" onclick="listtrip();" class="btn btn-success btn-submit">
                </div>
            </div>
        </form>
    </div>
</div>
