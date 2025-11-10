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
        ORDER BY m.created_at DESC";

$result = mysqli_query($conn, $sql);

// Check for query errors
if (!$result) {
    die('Query Error: ' . mysqli_error($conn));
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="manifests_'.date('Y-m-d_His').'.xls"');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manifests Export</title>
</head>
<body>
    <table border="1">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Created Date</th>
                <th>Manifest No</th>
                <th>To Office</th>
                <th>Docket Count</th>
                <th>Net Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sl = 1;
            while ($row = mysqli_fetch_assoc($result)):
            ?>
            <tr>
                <td><?= $sl ?></td>
                <td><?= date('d M Y h:i A', strtotime($row['created_at'])) ?></td>
                <td><?= htmlspecialchars($row['manifest_no'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['office_name'] ?? '-') ?></td>
                <td><?= $row['docket_count'] ?></td>
                <td>₹<?= number_format($row['net_total'] ?? 0, 2) ?></td>
            </tr>
            <?php
            $sl++;
            endwhile;
            ?>
        </tbody>
    </table>
</body>
</html>
