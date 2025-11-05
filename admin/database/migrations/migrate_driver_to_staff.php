<?php
/**
 * Migration: Update docket_details to use tbl_staff for drivers and helpers instead of tbl_driver and tbl_helper
 * 
 * This script:
 * 1. Adds a 'staff_id' column to docket_details table (for drivers)
 * 2. Adds a 'helper_staff_id' column to docket_details table (for helpers)
 * 3. Updates foreign key relationships
 * 
 * Run this ONCE after updating code to use tbl_staff for drivers and helpers
 */

require_once __DIR__ . '/../../conn.php';

echo "<h2>🔄 Migration: Driver & Helper to Staff</h2>";
echo "<hr>";

// Check if staff_id column already exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM docket_details LIKE 'staff_id'");

if (mysqli_num_rows($check_column) > 0) {
    echo "<p style='color: orange;'>⚠️ Column 'staff_id' already exists in docket_details table.</p>";
} else {
    // Add staff_id column after driver_id
    $add_column = "ALTER TABLE docket_details 
                   ADD COLUMN staff_id INT(11) NULL COMMENT 'References tbl_staff.staff_id for drivers' 
                   AFTER driver_id";
    
    if (mysqli_query($conn, $add_column)) {
        echo "<p style='color: green;'>✓ Column 'staff_id' added successfully to docket_details table!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding staff_id column: " . mysqli_error($conn) . "</p>";
        exit;
    }
}

// Check if helper_staff_id column already exists
$check_helper_column = mysqli_query($conn, "SHOW COLUMNS FROM docket_details LIKE 'helper_staff_id'");

if (mysqli_num_rows($check_helper_column) > 0) {
    echo "<p style='color: orange;'>⚠️ Column 'helper_staff_id' already exists in docket_details table.</p>";
} else {
    // Add helper_staff_id column after helper_id
    $add_helper_column = "ALTER TABLE docket_details 
                          ADD COLUMN helper_staff_id INT(11) NULL COMMENT 'References tbl_staff.staff_id for helpers' 
                          AFTER helper_id";
    
    if (mysqli_query($conn, $add_helper_column)) {
        echo "<p style='color: green;'>✓ Column 'helper_staff_id' added successfully to docket_details table!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding helper_staff_id column: " . mysqli_error($conn) . "</p>";
        exit;
    }
}

echo "<hr>";
echo "<h3>Migration Summary:</h3>";
echo "<ul>";
echo "<li>✓ docket_details table now has 'staff_id' column (for drivers)</li>";
echo "<li>✓ docket_details table now has 'helper_staff_id' column (for helpers)</li>";
echo "<li>✓ Future dockets will use tbl_staff for driver and helper information</li>";
echo "<li>✓ Driver dropdown in add_trip_modern.php now fetches from tbl_staff WHERE staff_role = 'Driver'</li>";
echo "<li>✓ Helper dropdown in add_trip_modern.php now fetches from tbl_staff WHERE staff_role = 'Helper'</li>";
echo "<li>✓ DocketDetailsManager auto-syncs driver and helper details from tbl_staff</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Important Notes:</h3>";
echo "<ul>";
echo "<li><strong>driver_id</strong> and <strong>helper_id</strong> columns still exist for backward compatibility</li>";
echo "<li><strong>staff_id</strong> will be used for new driver assignments going forward</li>";
echo "<li><strong>helper_staff_id</strong> will be used for new helper assignments going forward</li>";
echo "<li>Both old and new columns can coexist - driver_id references tbl_driver, staff_id references tbl_staff</li>";
echo "<li>The system now uses <strong>tbl_staff</strong> as the primary source for driver and helper information</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>✅ Migration Complete!</h3>";
echo "<p><a href='../add_trip_modern.php' style='padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Go to Add Trip</a></p>";

mysqli_close($conn);
?>
