<?php
require 'conn.php';
$doc_no = trim($_GET['doc_no'] ?? '');
$res = $conn->query("SELECT client_name as consignee, item, client_address as address, box, weight, eway_bill FROM tbl_shipping_details WHERE doc_no='$doc_no' LIMIT 1");
if($row = $res->fetch_assoc()) {
    echo json_encode([
        'success' => 1,
        'consignee' => $row['consignee'],
        'item' => $row['item'],
        'address' => $row['address'],
        'box' => $row['box'],
        'weight' => $row['weight'],
        'eway' => $row['eway_bill'],
    ]);
} else {
    echo json_encode(['success' => 0]);
}
