<?php
// Connect to DB if not already included (uncomment if needed)
// include("../includes/db.php");

// Validate and sanitize shipping_id
$shipping_id = isset($_REQUEST['shipping_id']) ? trim($_REQUEST['shipping_id']) : '';

if ($shipping_id === '' || !is_numeric($shipping_id)) {
    // Invalid or missing shipping_id, handle error
    die('<div class="alert alert-danger">Error: shipping_id is missing or invalid.</div>');
}

// Fetch shipping trip details
$sql2 = "SELECT * FROM tbl_shipping WHERE shipping_id='" . mysqli_real_escape_string($conn, $shipping_id) . "'";
$exe2 = mysqli_query($conn, $sql2) or die(mysqli_error($conn));
$result2 = mysqli_fetch_array($exe2);

if (!$result2) {
    die('<div class="alert alert-danger">Error: No shipping record found for this ID.</div>');
}
?>
<div class="x_panel">
    <div class="x_title">
        <h2>List Doc No. : (Trip No. <?= htmlspecialchars($result2['trip_no']); ?>)</h2>
        <a href="trip.php?type=list_trip&lp=ac" class="btn btn-success btn-submit" style="float: right;">Back</a>
        <div class="clearfix"></div>
    </div>
    <div class="x_content">
        <table id="datatable-buttons" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Consignor Company Name</th>
                    <th>Consignee Name</th>
                    <th>Doc No.</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
<?php
$sql = "SELECT * FROM tbl_shipping_details WHERE shipping_id='" . mysqli_real_escape_string($conn, $shipping_id) . "' ORDER BY client_id ASC";
$exe = mysqli_query($conn, $sql);
$rowCount = 1;
while ($result = mysqli_fetch_array($exe)) {
    // Get company name
    $sql_company = "SELECT * FROM tbl_company WHERE company_id='" . mysqli_real_escape_string($conn, $result['company_id']) . "'";
    $exe_company = mysqli_query($conn, $sql_company);
    $result_company = mysqli_fetch_array($exe_company);
    ?>
    <tr class="<?php echo ($rowCount % 2) == 0 ? 'alt1' : 'alt2'; ?>">
        <td><?php echo $rowCount; ?></td>
        <td><?php echo htmlspecialchars($result_company['company_title']); ?></td>
        <td><?php echo htmlspecialchars($result['client_name']); ?></td>
        <td><?php echo htmlspecialchars($result['doc']); ?></td>
        <td><?php echo htmlspecialchars($result['status']); ?></td>
        <td>
            <a class="btn btn-info btn-xs" href="trip.php?type=edit_trip_company&lp=ac&shipping_details_id=<?php echo urlencode($result['shipping_details_id']); ?>&<?php echo session_name() . '=' . session_id(); ?>">Edit Doc Status</a>
            <a class="btn btn-success btn-xs" href="trip.php?type=print_doc&lp=cu&shipping_details_id=<?php echo urlencode($result['shipping_details_id']); ?>" target="_blank">Print</a>
        </td>
    </tr>
    <?php $rowCount++; ?>
<?php
}
?>
            </tbody>
        </table>
    </div>
</div>
