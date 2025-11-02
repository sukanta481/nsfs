<?php
require 'conn.php';

header('Content-Type: application/json');

$doc_no = isset($_GET['doc_no']) ? mysqli_real_escape_string($conn, trim($_GET['doc_no'])) : '';

if (empty($doc_no)) {
    echo json_encode(['exists' => false]);
    exit;
}

// Check if docket number already exists
$query = "SELECT shipping_details_id, doc_no, status, created_at 
          FROM tbl_shipping_details 
          WHERE doc_no = '$doc_no' 
          LIMIT 1";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $docket = mysqli_fetch_assoc($result);
    echo json_encode([
        'exists' => true,
        'doc_no' => $docket['doc_no'],
        'status' => $docket['status'],
        'created_at' => date('d M Y', strtotime($docket['created_at']))
    ]);
} else {
    echo json_encode(['exists' => false]);
}
?>
