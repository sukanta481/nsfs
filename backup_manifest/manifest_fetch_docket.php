<?php
require 'conn.php';
header('Content-Type: application/json');
$docket_no = trim($_GET['docket_no'] ?? '');
<<<<<<< HEAD
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT client_name AS consignee, item, client_address AS address, box, weight, rate, eway_bill FROM tbl_shipping_details WHERE doc_no='".mysqli_real_escape_string($conn, $docket_no)."' LIMIT 1"));
=======
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT 
    client_name,
    item,
    client_address,
    box,
    weight,
    rate,
    eway_bill,
    pay_to
FROM tbl_shipping_details 
WHERE doc_no='".mysqli_real_escape_string($conn, $docket_no)."' 
LIMIT 1"));
>>>>>>> 4202b17 (re-install)
if ($row) {
  echo json_encode($row);
} else {
  echo json_encode(['status'=>'not_found']);
}
