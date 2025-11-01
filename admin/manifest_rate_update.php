<?php
require 'conn.php';
$doc_no = trim($_POST['doc_no'] ?? '');
$rate = floatval($_POST['rate'] ?? 0);
if ($doc_no && $rate) {
    mysqli_query($conn, "UPDATE tbl_shipping_details SET rate='$rate' WHERE doc_no='".mysqli_real_escape_string($conn, $doc_no)."'");
    echo "OK";
} else {
    echo "Invalid";
}
