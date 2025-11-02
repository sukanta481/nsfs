<?php
require 'conn.php';

echo "Adding service_type column to tbl_shipping_details...\n\n";

// Add service_type column
echo "1. Adding service_type column...\n";
$result = mysqli_query($conn, "ALTER TABLE tbl_shipping_details ADD COLUMN service_type VARCHAR(50) NULL DEFAULT 'Standard' AFTER doc_type");
if ($result) {
    echo "   ✓ SUCCESS: service_type column added\n";
} else {
    $error = mysqli_error($conn);
    if (strpos($error, 'Duplicate column') !== false) {
        echo "   ✓ Column already exists\n";
    } else {
        echo "   ✗ ERROR: " . $error . "\n";
    }
}

echo "\n✓ Database structure updated successfully!\n";
echo "\nAdded column:\n";
echo "- service_type (VARCHAR(50), NULL, DEFAULT 'Standard')\n";
?>
