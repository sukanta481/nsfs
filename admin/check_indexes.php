<?php
require 'conn.php';

// Check indexes
$r = mysqli_query($conn, 'SHOW INDEX FROM docket_details');
echo "Indexes on docket_details:\n";
while($row = mysqli_fetch_assoc($r)) { 
    echo $row['Key_name'] . ' -> ' . $row['Column_name'] . "\n"; 
}

echo "\n\nRecommended indexes for faster search:\n";
echo "1. ALTER TABLE docket_details ADD INDEX idx_doc_no (doc_no);\n";
echo "2. ALTER TABLE docket_details ADD INDEX idx_status (status);\n";
echo "3. ALTER TABLE docket_details ADD INDEX idx_pickup_datetime (pickup_datetime);\n";
echo "4. ALTER TABLE docket_details ADD INDEX idx_office_id (office_id);\n";
echo "5. ALTER TABLE docket_details ADD INDEX idx_created_by (created_by);\n";
echo "6. ALTER TABLE docket_details ADD INDEX idx_company_name (company_name(100));\n";
echo "7. ALTER TABLE docket_details ADD INDEX idx_client_name (client_name(100));\n";
