<?php
// Quick script to add invoice_amount column to docket_details table
require 'conn.php';

echo "Adding invoice_amount column to docket_details table...\n";

$sql = "ALTER TABLE `docket_details` 
        ADD COLUMN `invoice_amount` DECIMAL(10,2) DEFAULT 0.00 AFTER `invoice_no`";

if (mysqli_query($conn, $sql)) {
    echo "✅ SUCCESS: invoice_amount column added successfully!\n";
    echo "You can now create trips with invoice amount field.\n";
} else {
    $error = mysqli_error($conn);
    if (strpos($error, "Duplicate column name") !== false) {
        echo "ℹ️  INFO: Column 'invoice_amount' already exists. No action needed.\n";
    } else {
        echo "❌ ERROR: " . $error . "\n";
    }
}

mysqli_close($conn);
?>
