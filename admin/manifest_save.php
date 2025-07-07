<?php
require 'conn.php';

$office_id = intval($_POST['office_id'] ?? 0);
$dockets = $_POST['docket_no'] ?? [];
$rates = $_POST['rate'] ?? [];
$amounts = $_POST['amount'] ?? [];

if (!$office_id || !$dockets) {
    echo '<div class="alert alert-danger">Missing office or dockets.</div>';
    exit;
}

$success = 0;
foreach ($dockets as $i => $docket_no) {
    $docket_no = trim($docket_no);
    $rate = floatval($rates[$i] ?? 0);
    $amount = floatval($amounts[$i] ?? 0);
    if (!$docket_no || !$rate) continue;

    // Update rate & office/branch for these dockets
    $q = mysqli_query($conn, "UPDATE tbl_shipping_details SET rate='$rate', amount='$amount', branch_office='$office_id', doc_type='DRS' WHERE doc_no='".mysqli_real_escape_string($conn, $docket_no)."'");
    if ($q) $success++;
}
if ($success > 0) {
    echo '<div class="alert alert-success">Manifest saved successfully ('.$success.' entries updated).</div>';
} else {
    echo '<div class="alert alert-warning">No entries saved. Please check your data.</div>';
}
?>
