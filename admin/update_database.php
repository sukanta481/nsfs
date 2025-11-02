<?php
require 'conn.php';

echo "Updating tbl_shipping_details structure...\n\n";

// 1. Add company_address column
echo "1. Adding company_address column...\n";
$result1 = mysqli_query($conn, "ALTER TABLE tbl_shipping_details ADD COLUMN company_address TEXT NULL AFTER company_email");
if ($result1) {
    echo "   ✓ SUCCESS: company_address column added\n";
} else {
    $error = mysqli_error($conn);
    if (strpos($error, 'Duplicate column') !== false) {
        echo "   ✓ Column already exists\n";
    } else {
        echo "   ✗ ERROR: " . $error . "\n";
    }
}

// 2. Add dimensions column
echo "\n2. Adding dimensions column...\n";
$result2 = mysqli_query($conn, "ALTER TABLE tbl_shipping_details ADD COLUMN dimensions VARCHAR(100) NULL AFTER weight");
if ($result2) {
    echo "   ✓ SUCCESS: dimensions column added\n";
} else {
    $error = mysqli_error($conn);
    if (strpos($error, 'Duplicate column') !== false) {
        echo "   ✓ Column already exists\n";
    } else {
        echo "   ✗ ERROR: " . $error . "\n";
    }
}

echo "\n✓ Database structure updated successfully!\n";
echo "\nUpdated columns:\n";
echo "- company_address (TEXT, NULL)\n";
echo "- dimensions (VARCHAR(100), NULL)\n";
?>
