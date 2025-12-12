<?php
require 'conn.php';

echo "=== All Columns in docket_details ===\n\n";

$res = mysqli_query($conn, "SHOW COLUMNS FROM docket_details");
$count = 0;
while($row = mysqli_fetch_assoc($res)) {
    $count++;
    echo "$count. " . $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== Checking Latest Docket Data ===\n";
$res2 = mysqli_query($conn, "SELECT * FROM docket_details ORDER BY docket_id DESC LIMIT 1");
if ($row2 = mysqli_fetch_assoc($res2)) {
    echo "Doc No: " . $row2['doc_no'] . "\n";
    echo "Company Name: " . $row2['company_name'] . "\n";
    echo "Company Phone: " . $row2['company_phone'] . "\n";
    echo "Company Email: " . $row2['company_email'] . "\n";
    echo "Company Address: " . $row2['company_address'] . "\n";
    echo "Client Name: " . $row2['client_name'] . "\n";
    echo "Client Phone: " . $row2['client_phone'] . "\n";
    echo "Client Email: " . $row2['client_email'] . "\n";
    echo "Client Address: " . $row2['client_address'] . "\n";
    echo "Invoice No: " . $row2['invoice_no'] . "\n";
    echo "Invoice Amount: " . $row2['invoice_amount'] . "\n";
    echo "Eway Bill: " . $row2['eway_bill'] . "\n";
    echo "Item: " . $row2['item'] . "\n";
    echo "Box: " . $row2['box'] . "\n";
    echo "Weight: " . $row2['weight'] . "\n";
    echo "Dimensions: " . $row2['dimensions'] . "\n";
    echo "Service Type: " . $row2['service_type'] . "\n";
}
?>
