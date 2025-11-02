<?php
require 'conn.php';

header('Content-Type: application/json');

$office_id = intval($_GET['office_id'] ?? 0);
$date_filter = $_GET['date'] ?? '';

if (!$office_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid office ID']);
    exit;
}

// Build query with optional date filter
$query = "SELECT manifest_id, manifest_no, created_at, net_total 
          FROM tbl_manifest 
          WHERE office_id = $office_id";

if (!empty($date_filter)) {
    // Filter by specific date
    $date_filter = mysqli_real_escape_string($conn, $date_filter);
    $query .= " AND DATE(created_at) = '$date_filter'";
}

$query .= " ORDER BY manifest_id DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

$manifests = [];
while ($row = mysqli_fetch_assoc($result)) {
    $manifests[] = [
        'manifest_id' => $row['manifest_id'],
        'manifest_no' => $row['manifest_no'],
        'date' => date('d M Y', strtotime($row['created_at'])),
        'net_total' => number_format($row['net_total'], 2)
    ];
}

echo json_encode([
    'success' => true,
    'manifests' => $manifests,
    'count' => count($manifests)
]);
?>
