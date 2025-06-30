<?php
include('conn.php');
$doc_type = $_REQUEST['doc_type'] ?? '';
$status = $_REQUEST['status'] ?? '';
$fromdate = $_REQUEST['fromdate'] ?? '';
$todate = $_REQUEST['todate'] ?? '';
$type = $_REQUEST['type'] ?? '';
$typedata = $_REQUEST['typedata'] ?? '';
$consignor = trim($_REQUEST['consignor'] ?? '');
$consignee = trim($_REQUEST['consignee'] ?? '');

$where = [];
if (!empty($fromdate) && !empty($todate)) {
    $where[] = "(pickup_dates BETWEEN '".mysqli_real_escape_string($conn, $fromdate)."' AND '".mysqli_real_escape_string($conn, $todate)."')";
}
if (!empty($doc_type)) {
    $where[] = "sd.doc_type='".mysqli_real_escape_string($conn, $doc_type)."'";
}
if (!empty($status)) {
    $where[] = "sd.status='".mysqli_real_escape_string($conn, $status)."'";
}
if (!empty($type) && !empty($typedata)) {
    if ($type == 'doc') {
        $where[] = "sd.doc_no='".mysqli_real_escape_string($conn, $typedata)."'";
    }
    if ($type == 'box') {
        $where[] = "sd.box='".mysqli_real_escape_string($conn, $typedata)."'";
    }
}
if (!empty($consignor)) {
    $where[] = "c.company_title LIKE '%" . mysqli_real_escape_string($conn, $consignor) . "%'";
}
if (!empty($consignee)) {
    $where[] = "sd.client_name LIKE '%" . mysqli_real_escape_string($conn, $consignee) . "%'";
}
$whereSQL = (count($where) > 0) ? ("WHERE " . implode(" AND ", $where)) : "";

// Use JOIN for consignor company name
$sql = "SELECT sd.*, c.company_title
        FROM tbl_shipping_details sd
        LEFT JOIN tbl_company c ON sd.company_id = c.company_id
        $whereSQL
        ORDER BY sd.shipping_details_id DESC";

$exe = mysqli_query($conn, $sql);
$rowCount = 1;
?>
<div class="x_content">
    <table id="datatable-buttons" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Sl</th>
                <th>Pickup Date</th>
                <th>Doc No</th>
                <th>DRS Type</th>
                <th>Consignor Company</th>
                <th>Consignee Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
<?php
while($row = mysqli_fetch_array($exe)) {
?>
            <tr>
                <td><?= $rowCount ?></td>
                <td><?= htmlspecialchars($row['pickup_dates'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['doc_no'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['doc_type'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['company_title'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['client_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['status'] ?? '-') ?></td>
                <td>
                    <a class="btn btn-info btn-xs" href="trip.php?type=edit_trip_company&lp=ac&shipping_details_id=<?= urlencode($row['shipping_details_id']); ?>&<?= session_name() . '=' . session_id(); ?>">Edit Doc Status</a>
                    <a class="btn btn-success btn-xs" href="register.php?type=print_doc&lp=cu&shipping_details_id=<?= $row['shipping_details_id']; ?>" target="_blank">Print</a>
                    <a class="btn btn-danger btn-xs" href="javascript:void(0);" onclick="delconfirmshipping('<?= $row['shipping_details_id'] ?>');">Delete</a>
                </td>
            </tr>
<?php
$rowCount++;
}
?>
        </tbody>
    </table>
</div>
