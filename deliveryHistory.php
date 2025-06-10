<?php
include("include/apps_top.php");
$doc_no = mysqli_real_escape_string($conn, trim($_REQUEST['doc_no']));
$get_shipping_details_sql = "SELECT * FROM tbl_shipping_details WHERE doc='" . $doc_no . "'";
$get_shipping_details_rs = mysqli_query($conn, $get_shipping_details_sql);
$get_shipping_details_num = mysqli_num_rows($get_shipping_details_rs);
$get_shipping_details_row = mysqli_fetch_array($get_shipping_details_rs);
$sid=$get_shipping_details_row['shipping_details_id'];
$sql=mysqli_query($conn,"SELECT * FROM `tbl_trip_status` WHERE `ship_id`='$sid'");
$row = mysqli_num_rows($sql);
$row = mysqli_fetch_array($sql);

?>

<div class="delivery-history mt-1">
    <h2 class="mt-1">Delivery Status</h2>

    <?php if ($get_shipping_details_num > 0): ?>
        <div class="delivery-card">
            <h3>CN No: <?= $get_shipping_details_row['doc']; ?></h3>
            <p><strong>Date:</strong> <?= $get_shipping_details_row['pickup_dates']; ?></p>
            <p><strong>Company:</strong> <?= $get_shipping_details_row['client_name']; ?></p>
            <p><strong>Adress: </strong> <?= $get_shipping_details_row['client_address']; ?></p>


            <?php
            // Fetch car details
            $get_car_sql = "SELECT * FROM tbl_car WHERE car_id=(SELECT car_id FROM tbl_register WHERE register_id='" . $get_shipping_details_row['register_id'] . "')";
            $get_car_rs = mysqli_query($conn, $get_car_sql);
            $get_car_row = mysqli_fetch_array($get_car_rs);
            ?>
            <p><strong>Car No:</strong> <?= $get_car_row['car_number']; ?></p>

            <?php
            // Fetch helper details
            $get_helper_sql = "SELECT * FROM tbl_helper WHERE helper_id=(SELECT helper_id FROM tbl_register WHERE register_id='" . $get_shipping_details_row['register_id'] . "')";
            $get_helper_rs = mysqli_query($conn, $get_helper_sql);
            $get_helper_row = mysqli_fetch_array($get_helper_rs);
            ?>
            <p><strong>Delivery Agent:</strong> <?= $get_helper_row['helper_name']; ?></p>
            <p><strong>Contact Number:</strong> <?= $get_helper_row['helper_number']; ?></p>
            <p><strong>Status:</strong> <?= $get_shipping_details_row['status']; ?></p>
            <p><strong>Date And Time:</strong> <?= $row['updateddate']; ?></p>

            <?php if ($get_shipping_details_row['status'] == 'Delay'): ?>
                <p><strong>Reason for Delay:</strong> <?= $get_shipping_details_row['reason_of_delay']; ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p>No Data Found</p>
    <?php endif; ?>
</div>

<style>
    .mt-1{
        margin-bottom: 1.25rem;
    }
    .delivery-history {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background-color: #f9f9f9;
    }
    .delivery-card {
        border: 1px solid #007bff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #ffffff;
    }
    .delivery-card h3 {
        margin-top: 0;
    }
    .delivery-card p {
        margin: 5px 0;
    }
</style>