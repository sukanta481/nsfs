<?php
require 'conn.php';

header('Content-Type: application/json');

$manifest_id = intval($_GET['manifest_id'] ?? 0);

if (!$manifest_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid manifest ID']);
    exit;
}

// Get manifest details
$query = "SELECT detail_id, doc_no, client_name, item, client_address, box, weight, rate, amount, eway_bill, pay_to
          FROM tbl_manifest_details 
          WHERE manifest_id = $manifest_id 
          ORDER BY detail_id ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

$details = [];
while ($row = mysqli_fetch_assoc($result)) {
    $details[] = [
        'doc_no' => $row['doc_no'],
        'client_name' => $row['client_name'],
        'item' => $row['item'],
        'client_address' => $row['client_address'],
        'box' => $row['box'],
        'weight' => $row['weight'],
        'rate' => $row['rate'],
        'amount' => $row['amount'],
        'eway_bill' => $row['eway_bill'],
        'pay_to' => $row['pay_to']
    ];
}

echo json_encode([
    'success' => true,
    'details' => $details,
    'count' => count($details)
]);
?>
