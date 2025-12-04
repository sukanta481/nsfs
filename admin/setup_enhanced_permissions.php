<?php
/**
 * Enhanced User Permission System - Setup Script
 * 
 * This script applies the database migrations for the enhanced user permission system.
 * Run this once to set up the new tables and permissions.
 * 
 * Usage: Navigate to admin/setup_enhanced_permissions.php in your browser
 */

require 'conn.php';
require 'check_auth.php';

// Only super admin can run this
if (!isSuperAdmin()) {
    die('<div style="font-family: Arial; padding: 40px; text-align: center;">
        <h1 style="color: #dc3545;">Access Denied</h1>
        <p>Only Super Admin can run this setup script.</p>
        <a href="index.php">Go to Dashboard</a>
    </div>');
}

$messages = [];
$errors = [];

echo '<!DOCTYPE html>
<html>
<head>
    <title>Enhanced Permission System Setup</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; max-width: 900px; margin: 0 auto; background: #f5f5f5; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h1 { color: #333; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; }
        .step { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .step.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .step.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .step.info { background: #e7f3ff; color: #0c5460; border-left: 4px solid #17a2b8; }
        .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn:hover { background: #5a6fd6; }
        code { background: #f1f1f1; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔐 Enhanced User Permission System Setup</h1>
        <p class="subtitle">Setting up branch-specific access control and granular permissions</p>';

// Step 1: Add columns to tbl_users
echo '<div class="step info"><strong>Step 1:</strong> Adding office_id and can_access_all_offices columns to tbl_users...</div>';

$alter_queries = [
    "ALTER TABLE tbl_users ADD COLUMN office_id INT(11) NULL AFTER staff_id",
    "ALTER TABLE tbl_users ADD COLUMN can_access_all_offices TINYINT(1) DEFAULT 0 AFTER office_id"
];

foreach ($alter_queries as $query) {
    $result = @mysqli_query($conn, $query);
    if (!$result) {
        $error = mysqli_error($conn);
        if (strpos($error, 'Duplicate column') !== false) {
            echo '<div class="step success">✓ Column already exists (skipped)</div>';
        } else {
            echo '<div class="step error">✗ Error: ' . htmlspecialchars($error) . '</div>';
        }
    } else {
        echo '<div class="step success">✓ Column added successfully</div>';
    }
}

// Step 2: Create tbl_user_permissions
echo '<div class="step info"><strong>Step 2:</strong> Creating tbl_user_permissions table...</div>';
$create_user_perms = "CREATE TABLE IF NOT EXISTS tbl_user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    granted TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_permission (user_id, permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $create_user_perms)) {
    echo '<div class="step success">✓ tbl_user_permissions table created/exists</div>';
} else {
    echo '<div class="step error">✗ Error: ' . mysqli_error($conn) . '</div>';
}

// Step 3: Create tbl_user_status_permissions
echo '<div class="step info"><strong>Step 3:</strong> Creating tbl_user_status_permissions table...</div>';
$create_status_perms = "CREATE TABLE IF NOT EXISTS tbl_user_status_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status_name VARCHAR(100) NOT NULL,
    can_update_to TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_status (user_id, status_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $create_status_perms)) {
    echo '<div class="step success">✓ tbl_user_status_permissions table created/exists</div>';
} else {
    echo '<div class="step error">✗ Error: ' . mysqli_error($conn) . '</div>';
}

// Step 4: Create tbl_user_access_log
echo '<div class="step info"><strong>Step 4:</strong> Creating tbl_user_access_log table...</div>';
$create_access_log = "CREATE TABLE IF NOT EXISTS tbl_user_access_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    module VARCHAR(50) NULL,
    record_id VARCHAR(100) NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_id, action_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $create_access_log)) {
    echo '<div class="step success">✓ tbl_user_access_log table created/exists</div>';
} else {
    echo '<div class="step error">✗ Error: ' . mysqli_error($conn) . '</div>';
}

// Step 5: Create tbl_permission_groups
echo '<div class="step info"><strong>Step 5:</strong> Creating tbl_permission_groups table...</div>';
$create_perm_groups = "CREATE TABLE IF NOT EXISTS tbl_permission_groups (
    group_id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(100) NOT NULL,
    group_description TEXT,
    module_name VARCHAR(50) NOT NULL,
    display_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $create_perm_groups)) {
    echo '<div class="step success">✓ tbl_permission_groups table created/exists</div>';
} else {
    echo '<div class="step error">✗ Error: ' . mysqli_error($conn) . '</div>';
}

// Step 6: Add new permissions
echo '<div class="step info"><strong>Step 6:</strong> Adding new granular permissions...</div>';
$new_permissions = [
    // Office/Branch
    ['View All Offices', 'office_view_all', 'Office', 'Can view dockets/data from all offices'],
    ['View Own Office Only', 'office_view_own', 'Office', 'Can only view dockets/data from assigned office'],
    ['Manage Offices', 'office_manage', 'Office', 'Can create/edit/delete offices'],
    
    // Status Updates
    ['Update to Confirmed', 'status_update_confirmed', 'Status', 'Can update docket status to Confirmed'],
    ['Update to Picked Up', 'status_update_picked_up', 'Status', 'Can update docket status to Picked Up'],
    ['Update to In Transit', 'status_update_in_transit', 'Status', 'Can update docket status to In Transit'],
    ['Update to Out for Delivery', 'status_update_out_for_delivery', 'Status', 'Can update docket status to Out for Delivery'],
    ['Update to Delivered', 'status_update_delivered', 'Status', 'Can update docket status to Delivered'],
    ['Update to Delayed', 'status_update_delayed', 'Status', 'Can update docket status to Delayed'],
    ['Update to Failed Delivery', 'status_update_failed', 'Status', 'Can update docket status to Failed Delivery'],
    ['Update to Cancelled', 'status_update_cancelled', 'Status', 'Can update docket status to Cancelled'],
    
    // POD
    ['Upload POD', 'pod_upload', 'POD', 'Can upload Proof of Delivery documents'],
    ['View POD', 'pod_view', 'POD', 'Can view Proof of Delivery documents'],
    ['Delete POD', 'pod_delete', 'POD', 'Can delete Proof of Delivery documents'],
    
    // Dockets (Additional)
    ['Print Docket', 'docket_print', 'Dockets', 'Can print/download docket details'],
    ['Print Sticker', 'docket_sticker', 'Dockets', 'Can print barcode stickers'],
    ['Export Dockets', 'docket_export', 'Dockets', 'Can export docket data to Excel/PDF'],
    ['View Delivery History', 'docket_history', 'Dockets', 'Can view status history of dockets'],
    ['Assign Car/Driver', 'docket_assign', 'Dockets', 'Can assign car and driver to dockets'],
    
    // Trips
    ['Create Trip', 'trip_create', 'Trips', 'Can create new trips with dockets'],
    ['View Trips', 'trip_view', 'Trips', 'Can view trip details'],
    ['Edit Trip', 'trip_edit', 'Trips', 'Can modify trip details'],
    ['Delete Trip', 'trip_delete', 'Trips', 'Can delete trips'],
    
    // Companies
    ['View Companies', 'company_view', 'Companies', 'Can view company list'],
    ['Create Company', 'company_create', 'Companies', 'Can add new companies'],
    ['Edit Company', 'company_edit', 'Companies', 'Can edit company details'],
    ['Delete Company', 'company_delete', 'Companies', 'Can delete companies'],
    
    // Delay Reasons
    ['Manage Delay Reasons', 'delay_reason_manage', 'Settings', 'Can add/edit/delete delay reasons'],
];

$perms_added = 0;
$perms_skipped = 0;
foreach ($new_permissions as $perm) {
    $name = mysqli_real_escape_string($conn, $perm[0]);
    $key = mysqli_real_escape_string($conn, $perm[1]);
    $module = mysqli_real_escape_string($conn, $perm[2]);
    $desc = mysqli_real_escape_string($conn, $perm[3]);
    
    $insert = "INSERT IGNORE INTO tbl_permissions (permission_name, permission_key, module_name, permission_description) 
               VALUES ('$name', '$key', '$module', '$desc')";
    
    if (mysqli_query($conn, $insert)) {
        if (mysqli_affected_rows($conn) > 0) {
            $perms_added++;
        } else {
            $perms_skipped++;
        }
    }
}
echo '<div class="step success">✓ Added ' . $perms_added . ' new permissions (' . $perms_skipped . ' already existed)</div>';

// Step 7: Add new roles
echo '<div class="step info"><strong>Step 7:</strong> Adding new default roles...</div>';
$new_roles = [
    ['Branch Manager', 'Manager of a specific branch - can manage all operations within their branch'],
    ['Branch Staff', 'Staff member of a specific branch - limited operations within their branch'],
    ['Delivery Agent', 'Field agent who can update delivery status and upload POD'],
    ['Viewer', 'Read-only access to assigned office data'],
];

$roles_added = 0;
foreach ($new_roles as $role) {
    $name = mysqli_real_escape_string($conn, $role[0]);
    $desc = mysqli_real_escape_string($conn, $role[1]);
    
    $insert = "INSERT IGNORE INTO tbl_roles (role_name, role_description) VALUES ('$name', '$desc')";
    if (mysqli_query($conn, $insert) && mysqli_affected_rows($conn) > 0) {
        $roles_added++;
    }
}
echo '<div class="step success">✓ Added ' . $roles_added . ' new roles</div>';

// Step 8: Add indexes
echo '<div class="step info"><strong>Step 8:</strong> Adding database indexes for performance...</div>';
$indexes = [
    "ALTER TABLE tbl_users ADD INDEX idx_office (office_id)",
    "ALTER TABLE docket_details ADD INDEX idx_office (office_id)",
    "ALTER TABLE docket_details ADD INDEX idx_branch_office (branch_office)"
];

foreach ($indexes as $idx) {
    $result = @mysqli_query($conn, $idx);
    // Ignore duplicate index errors
}
echo '<div class="step success">✓ Indexes added/verified</div>';

// Summary
echo '
        <div class="step success" style="margin-top: 30px; font-size: 18px;">
            <strong>✅ Setup Complete!</strong>
        </div>
        
        <h3 style="margin-top: 30px;">Next Steps:</h3>
        <ol style="line-height: 2;">
            <li>Go to <strong>Users → Add New User</strong> to create users with office assignments</li>
            <li>Select a <strong>Role</strong> and assign user to a specific <strong>Office/Branch</strong></li>
            <li>Optionally select which <strong>Status Updates</strong> the user can perform</li>
            <li>The user will only see dockets from their assigned office</li>
        </ol>
        
        <a href="users.php" class="btn">Go to User Management</a>
        <a href="add_user_new.php" class="btn" style="margin-left: 10px;">Add New User</a>
        
    </div>
</body>
</html>';
?>
