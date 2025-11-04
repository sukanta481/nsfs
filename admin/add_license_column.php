<?php
require 'conn.php';

// Add driver_license column to tbl_driver
$query1 = "ALTER TABLE tbl_driver ADD COLUMN driver_license VARCHAR(50) NULL AFTER driver_number";
if (mysqli_query($conn, $query1)) {
    echo "✓ Column 'driver_license' added to tbl_driver successfully\n";
} else {
    if (strpos(mysqli_error($conn), 'Duplicate column') !== false) {
        echo "✓ Column 'driver_license' already exists in tbl_driver\n";
    } else {
        echo "✗ Error adding column to tbl_driver: " . mysqli_error($conn) . "\n";
    }
}

echo "\nDatabase update completed!";
?>
