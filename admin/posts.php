<?php
	include('conn.php');

// Build filter query for tbl_shipping_details (not tbl_shipping)
$where = [];
if (isset($_REQUEST['action']) && $_REQUEST['action'] == "search") {
    if (!empty($_REQUEST['fromdate']) && !empty($_REQUEST['todate'])) {
        $where[] = "(pickup_date BETWEEN '" . mysqli_real_escape_string($conn, $_REQUEST['fromdate']) . "' AND '" . mysqli_real_escape_string($conn, $_REQUEST['todate']) . "')";
    }
    if (!empty($_REQUEST['type']) && !empty($_REQUEST['typedata'])) {
        if ($_REQUEST['type'] == 'doc') {
            $where[] = "doc_no='" . mysqli_real_escape_string($conn, $_REQUEST['typedata']) . "'";
        }
        if ($_REQUEST['type'] == 'box') {
            $where[] = "box='" . mysqli_real_escape_string($conn, $_REQUEST['typedata']) . "'";
        }
    }
    if (!empty($_REQUEST['status'])) {
        $where[] = "status='" . mysqli_real_escape_string($conn, $_REQUEST['status']) . "'";
    }
}
$whereSQL = (count($where) > 0) ? ("WHERE " . implode(" AND ", $where)) : "";

// Query directly from tbl_shipping_details for speed and simplicity
$sql = "SELECT * FROM tbl_shipping_details $whereSQL ORDER BY shipping_details_id DESC";

$exe = mysqli_query($conn, $sql);
$rowCount = 1;
?>
 <div class="x_content">
<table id="user" class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Sl</th>
        <th>Pickup Date</th>
        <th>Doc No</th>
        <th>DRS Type</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
<?php while($result = mysqli_fetch_array($exe)) { ?>
    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
        <td><?php print $rowCount; ?></td>
        <td><?php echo htmlspecialchars($result['pickup_dates'] ?? 'N/A'); ?></td>
        <td><?php echo htmlspecialchars($result['doc_no'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($result['doc_type'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($result['status'] ?? ''); ?></td>
        <td>
            <a class="btn btn-info btn-xs" href="register.php?type=view_doc&lp=ac&shipping_details_id=<?php echo $result['shipping_details_id']; ?>">View</a>
            <a class="btn btn-success btn-xs" href="register.php?type=print_doc&lp=cu&shipping_details_id=<?php echo $result['shipping_details_id']; ?>" target="_blank">Print</a>
            <a class="btn btn-danger btn-xs" href="javascript:void(0);" onclick="delconfirmregister('<?php echo $result['shipping_details_id']; ?>');">Delete</a>
        </td>
    </tr>
<?php $rowCount++; } ?>
    </tbody>
</table>
</div>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.uikit.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function () {
        $('#user').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: "copy", className: "btn-sm" },
                { extend: "csv", className: "btn-sm" },
                { extend: "excel", className: "btn-sm" },
                { extend: "pdf", className: "btn-sm" },
                { extend: "print", className: "btn-sm" }
            ],
            responsive: true
        });
    });
</script>
