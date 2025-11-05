<?php
require 'conn.php';

echo "Checking tbl_offices columns:\n";
$result = mysqli_query($conn, 'DESCRIBE tbl_offices');
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . "\n";
}

echo "\n\nChecking if docket_id 4 exists:\n";
$test = mysqli_query($conn, "SELECT * FROM docket_details WHERE docket_id = 4");
if($test) {
    $data = mysqli_fetch_assoc($test);
    if($data) {
        echo "Docket found: " . $data['doc_no'] . "\n";
        echo "Office ID: " . ($data['office_id'] ?? 'NULL') . "\n";
    } else {
        echo "Docket ID 4 not found\n";
    }
} else {
    echo "Query error: " . mysqli_error($conn) . "\n";
}
?>
