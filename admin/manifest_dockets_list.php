<?php
include_once('conn.php');
require_once 'check_auth.php'; // Required for getOfficeFilter() and access control functions

$manifest_id = intval($_GET['manifest_id'] ?? 0);

if ($manifest_id <= 0) {
    echo '<div class="alert alert-danger">Invalid manifest ID</div>';
    exit;
}

// Get manifest info with office filter for branch-based access control
$officeFilter = getOfficeFilter('m');
$manifest_query = "SELECT m.*, o.office_name
                   FROM tbl_manifest m
                   LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                   WHERE m.manifest_id = $manifest_id" . $officeFilter;
$manifest_result = mysqli_query($conn, $manifest_query);
$manifest = mysqli_fetch_assoc($manifest_result);

if (!$manifest) {
    echo '<div class="alert alert-danger">Manifest not found</div>';
    exit;
}

// Get dockets in this manifest from tbl_manifest_details
$dockets_query = "SELECT md.*
                  FROM tbl_manifest_details md
                  WHERE md.manifest_id = $manifest_id
                  ORDER BY md.id DESC";
$dockets_result = mysqli_query($conn, $dockets_query);
?>

<div style="margin-bottom: 15px; padding: 12px; background: #f8f9fa; border-left: 4px solid #007bff;">
    <h5 style="margin: 0 0 5px 0;"><strong>Manifest: <?= htmlspecialchars($manifest['manifest_no']) ?></strong></h5>
    <p style="margin: 0; color: #666;">
        To Office: <?= htmlspecialchars($manifest['office_name'] ?? '-') ?> |
        Created: <?= date('d M Y, h:i A', strtotime($manifest['created_at'])) ?> |
        Total: ₹<?= number_format($manifest['net_total'] ?? 0, 2) ?>
    </p>
</div>

<?php if (mysqli_num_rows($dockets_result) == 0): ?>
<div class="alert alert-info">
    <i class="fa fa-info-circle"></i> No dockets found in this manifest.
</div>
<?php else: ?>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th style="width: 50px;">Sl</th>
            <th>Docket No</th>
            <th>Consignee Name</th>
            <th>Address</th>
            <th>Item</th>
            <th>Box</th>
            <th>Weight</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sl = 1;
        while ($docket = mysqli_fetch_assoc($dockets_result)):
        ?>
        <tr>
            <td><?= $sl ?></td>
            <td>
                <strong><?= htmlspecialchars($docket['doc_no'] ?? '-') ?></strong>
            </td>
            <td><?= htmlspecialchars($docket['client_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($docket['client_address'] ?? '-') ?></td>
            <td><?= htmlspecialchars($docket['item'] ?? '-') ?></td>
            <td><?= $docket['box'] ?? 0 ?></td>
            <td><?= number_format($docket['weight'] ?? 0, 2) ?> kg</td>
            <td><strong>₹<?= number_format($docket['amount'] ?? 0, 2) ?></strong></td>
        </tr>
        <?php
        $sl++;
        endwhile;
        ?>
    </tbody>
</table>
<?php endif; ?>
