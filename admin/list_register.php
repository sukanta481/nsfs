<?php
include('conn.php');
?>
<script type="text/javascript">
function delconfirmregister(shipping_details_id) {
    var c = confirm("Are you sure to delete?");
    if(c==true) {
        // Preserve current filters in URL after delete
        var search = window.location.search.replace(/^\?/, '');
        if (search) {
            search = search.replace(/([&\?])msg=deleted/, '').replace(/([&\?])action=delete_shipping_details/, '').replace(/([&\?])shipping_details_id=\d+/, '');
            location.href = 'action_handler.php?action=delete_shipping_details&shipping_details_id=' + shipping_details_id + '&' + search;
        } else {
            location.href = 'action_handler.php?action=delete_shipping_details&shipping_details_id=' + shipping_details_id;
        }
    }
}
</script>

<div class="x_panel">
    <div class="x_title">
        <h2>List Docs</h2>
        <div>
            <form action="" method="get" id="filterForm" style="float: right;">
                <!-- Date Range -->
                From: <input type="date" name="fromdate" id="fromdate" value="<?= htmlspecialchars($_REQUEST['fromdate'] ?? '') ?>">
                To: <input type="date" name="todate" id="todate" value="<?= htmlspecialchars($_REQUEST['todate'] ?? '') ?>">

                <!-- DRS Type -->
                <select name="doc_type" id="doc_type">
                    <option value="">All DRS Type</option>
                    <option value="DRS" <?= (($_REQUEST['doc_type'] ?? '') == 'DRS') ? 'selected' : '' ?>>DRS</option>
                    <option value="NON-DRS" <?= (($_REQUEST['doc_type'] ?? '') == 'NON-DRS') ? 'selected' : '' ?>>NON-DRS</option>
                </select>

                <!-- Status -->
                <select id="status" name="status">
                    <option value="">All Status</option>
                    <option value="Processing" <?= (($_REQUEST['status'] ?? '') == 'Processing') ? 'selected' : '' ?>>Processing</option>
                    <option value="Delivered" <?= (($_REQUEST['status'] ?? '') == 'Delivered') ? 'selected' : '' ?>>Delivered</option>
                    <option value="Picked Up" <?= (($_REQUEST['status'] ?? '') == 'Picked Up') ? 'selected' : '' ?>>Picked Up</option>
                    <option value="Delayed" <?= (($_REQUEST['status'] ?? '') == 'Delayed') ? 'selected' : '' ?>>Delayed</option>
                    <option value="In Transit" <?= (($_REQUEST['status'] ?? '') == 'In Transit') ? 'selected' : '' ?>>In Transit</option>
                    <option value="Out for Delivery" <?= (($_REQUEST['status'] ?? '') == 'Out for Delivery') ? 'selected' : '' ?>>Out for Delivery</option>
                </select>

                <!-- Doc No/Box -->
                <select id="type" name="type">
                    <option value="">Select Type</option>
                    <option value="doc" <?= (($_REQUEST['type'] ?? '') == 'doc') ? 'selected' : '' ?>>Doc No</option>
                    <option value="box" <?= (($_REQUEST['type'] ?? '') == 'box') ? 'selected' : '' ?>>Box/Unit</option>
                </select>
                <input type="text" id="typedata" name="typedata" placeholder="Enter value" value="<?= htmlspecialchars($_REQUEST['typedata'] ?? '') ?>" style="width:110px;">

                <!-- Consignor/Consignee -->
                <input type="text" name="consignor" placeholder="Consignor Company" value="<?= htmlspecialchars($_REQUEST['consignor'] ?? '') ?>" style="width:140px;">
                <input type="text" name="consignee" placeholder="Consignee Name" value="<?= htmlspecialchars($_REQUEST['consignee'] ?? '') ?>" style="width:140px;">

                <input type="submit" name="submit_filters" value="Search" class="btn btn-success btn-submit">
                <button id="resetBtn" class="btn btn-success">Reset Form</button>
            </form>
        </div>
        <div class="clearfix"></div>
    </div>
    <div id="posts">
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
// --- Filter section ---
$doc_type = $_REQUEST['doc_type'] ?? '';
$status = $_REQUEST['status'] ?? '';
$fromdate = $_REQUEST['fromdate'] ?? '';
$todate = $_REQUEST['todate'] ?? '';
$type = $_REQUEST['type'] ?? '';
$typedata = $_REQUEST['typedata'] ?? '';
$consignor = trim($_REQUEST['consignor'] ?? '');
$consignee = trim($_REQUEST['consignee'] ?? '');

// Support POST preference if needed (matches trip logic, but not used for GET filter by default)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doc_type = $_POST['doc_type'] ?? $doc_type;
    $status   = $_POST['status']   ?? $status;
    $fromdate = $_POST['fromdate'] ?? $fromdate;
    $todate   = $_POST['todate']   ?? $todate;
    $type     = $_POST['type']     ?? $type;
    $typedata = $_POST['typedata'] ?? $typedata;
    $consignor= trim($_POST['consignor'] ?? $consignor);
    $consignee= trim($_POST['consignee'] ?? $consignee);
}

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
                            <a class="btn btn-info btn-xs" href="edit_register.php?shipping_details_id=<?= $row['shipping_details_id']; ?>&<?= session_name().'='.session_id();?>">Edit</a>
                            <a class="btn btn-danger btn-xs" href="javascript:void(0);" onclick="delconfirmregister('<?= $row['shipping_details_id'] ?>');">Delete</a>
                        </td>
                    </tr>
<?php
$rowCount++;
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(function () {
    $('#filterForm').on('submit', function (e) {
        // Default GET: reloads with query params, no AJAX needed
    });
    $("#resetBtn").click(function (e) {
        e.preventDefault();
        $("#filterForm")[0].reset();
        window.location.href = "register.php?type=list_register"; // reset filter and reload page
    });
});
</script>
