<?php
// Add driving_license column to tbl_staff table
require __DIR__ . '/../../conn.php';

echo "Adding driving_license column to tbl_staff table...\n\n";

// Check if column already exists
$check_sql = "SHOW COLUMNS FROM tbl_staff LIKE 'driving_license'";
$result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE tbl_staff 
            ADD COLUMN driving_license VARCHAR(100) NULL COMMENT 'Required if role is Driver' 
            AFTER staff_role";
    
    if (mysqli_query($conn, $sql)) {
        echo "✓ Column 'driving_license' added successfully!\n";
    } else {
        echo "✗ Error adding column: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "✓ Column 'driving_license' already exists!\n";
}

mysqli_close($conn);
?>
