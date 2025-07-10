<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once(__DIR__ . '/includes/notifications/send_email.php');
// (Add SMS/WhatsApp includes here if needed)

$status_message = '';

if (isset($_POST['edit_trip'])) {
    $shipping_details_id = $_POST['shipping_details_id'];
    $shipping_id = $_POST['shipping_id'];
    $new_status = trim($_POST['status']);
    $reason_of_delay = $_POST['reason_of_delay'] ?? '';
    $manual_note = trim($_POST['manual_note'] ?? '');

    // 1. Fetch details BEFORE update
    $sql_prev = mysqli_query($conn, "SELECT status, doc_no, company_email, client_email, client_name, company_id, proof_of_delivery, branch_office FROM tbl_shipping_details WHERE shipping_details_id = '$shipping_details_id'");
    $prev_row = mysqli_fetch_assoc($sql_prev);

    $doc = $prev_row['doc_no'];
    $client_email = $prev_row['client_email'];
    $client_name = $prev_row['client_name'];
    $company_email = $prev_row['company_email'];
    $company_id = $prev_row['company_id'];
    $proof_of_delivery = $prev_row['proof_of_delivery'];
    $branch_office = $prev_row['branch_office'];

    // Fetch branch office name
    $branch_office_name = '';
    if (!empty($branch_office)) {
        $sql_branch = mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id='" . mysqli_real_escape_string($conn, $branch_office) . "'");
        if ($row_branch = mysqli_fetch_assoc($sql_branch)) {
            $branch_office_name = $row_branch['office_name'];
        }
    }

    // Company name
    $company_name = '';
    if ($company_id) {
        $csql = mysqli_query($conn, "SELECT company_title FROM tbl_company WHERE company_id='$company_id'");
        if ($crow = mysqli_fetch_assoc($csql)) $company_name = $crow['company_title'];
    }

    // File upload for POD (Proof of Delivery)
    $upload_success = true; // Default for cases where file is not uploaded
    // File upload for POD (Proof of Delivery)
$upload_success = true; // Default for cases where file is not uploaded
if ($new_status == 'Delivered' && !empty($_FILES['proof_of_delivery']['name'])) {
    $original_name = $_FILES['proof_of_delivery']['name'];
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    if (!in_array($ext, $allowed_exts)) {
        die('File type not allowed');
    }
    $pod_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', pathinfo($original_name, PATHINFO_FILENAME)) . '.' . $ext;

    // Save to /admin/post_img/pod/
    $save_dir = __DIR__ . '/post_img/pod/';

    // Create folder if not exists
    if (!file_exists($save_dir)) {
        mkdir($save_dir, 0775, true); // Try to create the folder
    }

    $pod_tmp = $_FILES['proof_of_delivery']['tmp_name'];
    $upload_success = move_uploaded_file($pod_tmp, $save_dir . $pod_name);
    $proof_of_delivery = $pod_name;

    // Error handling
    if ($_FILES['proof_of_delivery']['error'] != UPLOAD_ERR_OK) {
        echo "<div style='color:red;'>File upload error code: " . $_FILES['proof_of_delivery']['error'] . "</div>";
    } else if (!$upload_success) {
        echo "<div style='color:red;'>move_uploaded_file failed! Check your folder path and permissions.</div>";
    }
    } else {
        $proof_of_delivery = $prev_row['proof_of_delivery'];
    }






    // Note logic: you can map a default note per status
    $auto_notes = [
        'Created' => "Shipment created in system.",
        'Picked Up' => "Parcel picked up from consignor and received at North Super Fast Service Main Office, Kolkata. Docket number generated.",
        'Manifest Created' => "Parcel grouped with other shipments for $branch_office_name. Scheduled for dispatch.",
        'In Transit' => "Parcel is ready to transit to $client_name.",
        'In Transit to Branch' => "Parcel is on the way to $branch_office_name.",
        'Arrived at Branch' => "Parcel has arrived at $branch_office_name.",
        'Out for Delivery' => "Parcel is out for delivery to $client_name.",
        'Delivered' => $proof_of_delivery
            ? "Parcel delivered to consignee. [Click here to view Proof of Delivery (POD)]"
            : "Parcel delivered to consignee. POD upload is pending.",
        'Delay' => "Parcel delivery delayed.",
    ];

    $auto_note = $auto_notes[$new_status] ?? '';
    // If a manual note is added, append it
    // Only use manual note if present, else auto note
        if ($manual_note) {
            $final_note = $manual_note;
        } else {
            $final_note = $auto_note;
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

    // Log audit (history)
    $created_time = date('Y-m-d H:i:s');
    $ins_trip_status = "INSERT INTO tbl_trip_status 
        (doc_no, ship_id, status, note, updateddate) 
        VALUES (
            '" . mysqli_real_escape_string($conn, $doc) . "',
            '$shipping_details_id', 
            '" . mysqli_real_escape_string($conn, $new_status) . "',
            '" . mysqli_real_escape_string($conn, $final_note) . "',
            '$created_time')";
    mysqli_query($conn, $ins_trip_status);

    // Redirect after successful POST to avoid duplicate insert on refresh
    // Build the correct redirect URL using current GET params


}

// --- GET SHIPPING DETAILS (for display) ---
$shipping_details_id = $_REQUEST['shipping_details_id'];
$get_shipping_sql = "SELECT * FROM tbl_shipping_details WHERE shipping_details_id='" . $shipping_details_id . "'";
$get_shipping_rs = mysqli_query($conn, $get_shipping_sql);
$get_shipping_row = mysqli_fetch_array($get_shipping_rs);
$current_status = $get_shipping_row['status'];
$branch_office = $get_shipping_row['branch_office'];


// Set status list dynamically based on branch_office value
if (empty($branch_office)) {
    // Main branch - direct delivery
    $status_list = [
        'Created',
        'Picked Up',
        'In Transit',
        'Out for Delivery',
        'Delivered',
        'Delay'
    ];
} else {
    // Branch transfer
    $status_list = [
        'Created',
        'Picked Up',
        'Manifest Created',
        'In Transit to Branch',
        'Arrived at Branch',
        'Out for Delivery',
        'Delivered',
        'Delay'
    ];
}
?>
<?php
if (isset($_GET['success'])) {
    echo "<div class='alert alert-success'>Status updated successfully!</div>";
}
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
                    <select class="form-control col-md-7 col-xs-12" name="status" id="status_select" onchange="showAutoNote(this.value);change_status(this.value);">
                        <option value="<?= htmlspecialchars($current_status) ?>" selected>
                            <?= $current_status ? $current_status : "Select Status" ?>
                        </option>
                        <?php
                        foreach ($status_list as $s) {
                            if ($s != $current_status) {
                                echo '<option value="' . $s . '">' . $s . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Auto Note area (shows when status is selected) -->
            <div class="item form-group" id="auto_note_sec" style="display:none;">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Auto Note:</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div id="auto_note" style="background:#f4f7fa;padding:7px 13px;border-radius:4px;color:#234;font-size:1.03em;"></div>
                </div>
            </div>

            <!-- Add extra manual note -->
            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Add Note:</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <textarea name="manual_note" class="form-control" placeholder="Write any additional note (optional)"></textarea>
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
                        <img src="post_img/pod/<?= htmlspecialchars($get_shipping_row['proof_of_delivery']); ?>" width="200" height="200">
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

                function showAutoNote(status) {
                    var branchOffice = <?= json_encode($branch_office) ?>;
                    var clientName = <?= json_encode($get_shipping_row['client_name']) ?>;
                    var proofOfDelivery = <?= json_encode($get_shipping_row['proof_of_delivery']) ?>;
                    var notes = {
                        'Created': "Shipment created in system.",
                        'Picked Up': "Parcel picked up from consignor and received at North Super Fast Service Main Office, Kolkata. Docket number generated.",
                        'Manifest Created': branchOffice ? ("Parcel grouped with other shipments for " + branchOffice + ". Scheduled for dispatch.") : "",
                        'In Transit': "Parcel is ready to transit to " + clientName + ".",
                        'In Transit to Branch': branchOffice ? ("Parcel is on the way to " + branchOffice + ".") : "",
                        'Arrived at Branch': branchOffice ? ("Parcel has arrived at " + branchOffice + ".") : "",
                        'Out for Delivery': "Parcel is out for delivery to " + clientName + ".",
                        'Delivered': proofOfDelivery ? "Parcel delivered to consignee. [Click here to view Proof of Delivery (POD)]" : "Parcel delivered to consignee. POD upload is pending.",
                        'Delay': "Parcel delivery delayed."
                    };
                    if (notes[status]) {
                        $("#auto_note").text(notes[status]);
                        $("#auto_note_sec").show();
                    } else {
                        $("#auto_note").text('');
                        $("#auto_note_sec").hide();
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

