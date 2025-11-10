<?php
include('conn.php');

// Get filter parameters
$fromdate = $_REQUEST['fromdate'] ?? '';
$todate = $_REQUEST['todate'] ?? '';
$office_id = $_REQUEST['office_id'] ?? '';
$manifest_no = trim($_REQUEST['manifest_no'] ?? '');

// Build WHERE clause
$where = [];

if (!empty($fromdate) && !empty($todate)) {
    $where[] = "(m.created_at BETWEEN '".mysqli_real_escape_string($conn, $fromdate)." 00:00:00' AND '".mysqli_real_escape_string($conn, $todate)." 23:59:59')";
}

if (!empty($office_id)) {
    $where[] = "m.office_id = '".mysqli_real_escape_string($conn, $office_id)."'";
}

if (!empty($manifest_no)) {
    $where[] = "m.manifest_no LIKE '%".mysqli_real_escape_string($conn, $manifest_no)."%'";
}

$whereSQL = (count($where) > 0) ? ("WHERE " . implode(" AND ", $where)) : "";

// Query manifests with docket count from tbl_manifest_details
$sql = "SELECT m.*,
        o.office_name,
        (SELECT COUNT(*) FROM tbl_manifest_details md WHERE md.manifest_id = m.manifest_id) as docket_count
        FROM tbl_manifest m
        LEFT JOIN tbl_offices o ON m.office_id = o.office_id
        $whereSQL
        ORDER BY m.created_at DESC, m.manifest_id DESC";

$exe = mysqli_query($conn, $sql);
$rowCount = 1;
?>

<div class="x_content">
    <table id="datatable-buttons" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>Sl</th>
                <th>Created Date</th>
                <th>Manifest No</th>
                <th>To Office</th>
                <th>Dockets</th>
                <th>Net Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
<?php
if (!$exe) {
    echo '<tr><td colspan="7" class="text-center text-danger">Error: ' . mysqli_error($conn) . '</td></tr>';
} elseif (mysqli_num_rows($exe) == 0) {
    echo '<tr><td colspan="7" class="text-center">No manifests found</td></tr>';
} else {
    while($row = mysqli_fetch_array($exe)) {
?>
            <tr>
                <td><?= $rowCount ?></td>
                <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($row['created_at']))) ?></td>
                <td>
                    <strong><?= htmlspecialchars($row['manifest_no'] ?? '-') ?></strong>
                </td>
                <td><?= htmlspecialchars($row['office_name'] ?? '-') ?></td>
                <td>
                    <?php if ($row['docket_count'] > 0): ?>
                    <a href="javascript:void(0);"
                       onclick="viewManifestDockets(<?= $row['manifest_id'] ?>)"
                       class="btn btn-info btn-xs"
                       title="Click to view dockets">
                        <i class="fa fa-list"></i> <?= $row['docket_count'] ?> Dockets
                    </a>
                    <?php else: ?>
                    <span class="text-muted">0 Dockets</span>
                    <?php endif; ?>
                </td>
                <td>
                    <strong style="color: #28a745;">₹<?= number_format($row['net_total'] ?? 0, 2) ?></strong>
                </td>
                <td>
                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                        <a class="btn btn-primary btn-xs"
                           href="manifest.php?type=view_manifest&manifest_id=<?= $row['manifest_id'] ?>"
                           title="View Details">
                            <i class="fa fa-eye"></i> View
                        </a>
                        <a class="btn btn-success btn-xs"
                           href="manifest_print.php?manifest_id=<?= $row['manifest_id'] ?>"
                           target="_blank"
                           title="Download/Print Manifest">
                            <i class="fa fa-download"></i> Print
                        </a>
                        <a class="btn btn-danger btn-xs"
                           href="javascript:void(0);"
                           onclick="delconfirmmanifest('<?= $row['manifest_id'] ?>');"
                           title="Delete Manifest">
                            <i class="fa fa-trash"></i> Delete
                        </a>
                    </div>
                </td>
            </tr>
<?php
    $rowCount++;
    }
}
?>
        </tbody>
    </table>
</div>

<!-- Modal for showing dockets in manifest -->
<div class="modal fade" id="docketsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Dockets in Manifest</h4>
            </div>
            <div class="modal-body" id="docketsModalBody">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p>Loading dockets...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewManifestDockets(manifestId) {
    $('#docketsModal').modal('show');
    $('#docketsModalBody').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading dockets...</p></div>');

    $.ajax({
        url: 'manifest_dockets_list.php',
        type: 'GET',
        data: {manifest_id: manifestId},
        success: function(response) {
            $('#docketsModalBody').html(response);
        },
        error: function() {
            $('#docketsModalBody').html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error loading dockets. Please try again.</div>');
        }
    });
}
</script>
