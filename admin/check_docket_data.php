<?php
require 'conn.php';

// Check total records
$result = mysqli_query($conn, 'SELECT COUNT(*) as total FROM docket_details');
$row = mysqli_fetch_assoc($result);
echo "Total dockets in docket_details: " . $row['total'] . "\n\n";

// Get sample records
$result = mysqli_query($conn, 'SELECT docket_id, doc_no, company_name, client_name, status, created_at FROM docket_details ORDER BY docket_id DESC LIMIT 5');
if($result) {
    echo "Sample dockets:\n";
    echo "Docket ID | Doc No | Company | Client | Status | Created\n";
    echo "-----------------------------------------------------------\n";
    while($row = mysqli_fetch_assoc($result)) {
        echo $row['docket_id'] . " | " . $row['doc_no'] . " | " . ($row['company_name'] ?? 'N/A') . " | " . ($row['client_name'] ?? 'N/A') . " | " . $row['status'] . " | " . $row['created_at'] . "\n";
    }
} else {
    echo "Sample query failed: " . mysqli_error($conn) . "\n";
}

// Check the exact query used in list_register.php
echo "\n\nTesting list_register.php query:\n";
$sql = "SELECT dd.*, 
               o.office_name as branch_office_name
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        ORDER BY dd.docket_id DESC";
$result = mysqli_query($conn, $sql);
if($result) {
    echo "Query executed successfully. Rows returned: " . mysqli_num_rows($result) . "\n";
} else {
    echo "Query failed: " . mysqli_error($conn) . "\n";
}
?>
