<?php
require 'conn.php';

echo "=== Phone-related Columns in docket_details ===\n\n";

$res = mysqli_query($conn, "SHOW COLUMNS FROM docket_details WHERE Field LIKE '%phone%'");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== Sample Data (Latest Docket) ===\n";
$res2 = mysqli_query($conn, "SELECT doc_no, company_name, company_phone, client_name, client_phone FROM docket_details ORDER BY docket_id DESC LIMIT 1");
if ($row2 = mysqli_fetch_assoc($res2)) {
    foreach ($row2 as $key => $val) {
        echo "$key: $val\n";
    }
}
?>
