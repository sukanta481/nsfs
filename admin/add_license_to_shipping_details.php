<?php
require 'conn.php';

// Add driver_license column to tbl_shipping_details
$sql = "ALTER TABLE tbl_shipping_details ADD COLUMN driver_license VARCHAR(50) NULL AFTER driver_number";

if (mysqli_query($conn, $sql)) {
    echo "✅ Successfully added 'driver_license' column to tbl_shipping_details table!<br>";
    echo "Column added after 'driver_number' column.";
} else {
    // Check if column already exists
    if (mysqli_errno($conn) == 1060) {
        echo "ℹ️ Column 'driver_license' already exists in tbl_shipping_details table.";
    } else {
        echo "❌ Error adding column: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
