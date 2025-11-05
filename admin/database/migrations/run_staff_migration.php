<?php
// Run Staff Table Migration
require __DIR__ . '/../../conn.php';

echo "Creating Staff Management Table...\n\n";

$sql = "CREATE TABLE IF NOT EXISTS tbl_staff (
    staff_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    staff_unique_id VARCHAR(50) NOT NULL UNIQUE COMMENT 'Format: NSFS + Branch Code + Auto Number (e.g., NSFSBAR001)',
    staff_name VARCHAR(255) NOT NULL,
    staff_email VARCHAR(255) NULL,
    staff_phone VARCHAR(20) NOT NULL,
    staff_role VARCHAR(100) NOT NULL COMMENT 'Manager, Driver, Helper, Accountant, etc.',
    office_id INT(11) NULL COMMENT 'References tbl_offices',
    branch_office VARCHAR(255) NULL COMMENT 'Office/Branch name',
    date_of_joining DATE NULL,
    address TEXT NULL,
    emergency_contact VARCHAR(20) NULL,
    emergency_contact_name VARCHAR(255) NULL,
    salary DECIMAL(10,2) NULL,
    active_status TINYINT(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_staff_unique_id (staff_unique_id),
    INDEX idx_staff_role (staff_role),
    INDEX idx_office_id (office_id),
    INDEX idx_active_status (active_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "✓ Table 'tbl_staff' created successfully!\n\n";
    echo "Staff Management System is ready to use.\n";
    echo "Access it from: Fleet → Staff\n\n";
    echo "Staff ID Format: NSFS + First 3 chars of Branch + 001\n";
    echo "Example: NSFSBAR001 (for Barasat office)\n";
} else {
    echo "✗ Error creating table: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
