<?php
/**
 * Setup Simplified Permissions System
 * Run this script once to set up the clean permission system
 */

require 'conn.php';
require 'check_auth.php';

// Only super admin can run this
if (!isSuperAdmin()) {
    die("Access denied. Only Super Admin can run this setup.");
}

$messages = [];
$errors = [];

// Step 1: Create tbl_user_status_permissions table if not exists
$create_table = "CREATE TABLE IF NOT EXISTS tbl_user_status_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status_id INT NOT NULL,
    can_update TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_status (user_id, status_id)
)";

if (mysqli_query($conn, $create_table)) {
    $messages[] = "✅ tbl_user_status_permissions table ready";
} else {
    $errors[] = "❌ Error creating table: " . mysqli_error($conn);
}

// Step 2: Check if tbl_users has office columns
$check_office = mysqli_query($conn, "SHOW COLUMNS FROM tbl_users LIKE 'office_id'");
if (mysqli_num_rows($check_office) == 0) {
    $add_office = mysqli_query($conn, "ALTER TABLE tbl_users ADD COLUMN office_id INT NULL AFTER staff_id");
    if ($add_office) {
        $messages[] = "✅ Added office_id column to tbl_users";
    } else {
        $errors[] = "❌ Error adding office_id: " . mysqli_error($conn);
    }
} else {
    $messages[] = "✅ office_id column already exists";
}

$check_access = mysqli_query($conn, "SHOW COLUMNS FROM tbl_users LIKE 'can_access_all_offices'");
if (mysqli_num_rows($check_access) == 0) {
    $add_access = mysqli_query($conn, "ALTER TABLE tbl_users ADD COLUMN can_access_all_offices TINYINT(1) DEFAULT 0 AFTER office_id");
    if ($add_access) {
        $messages[] = "✅ Added can_access_all_offices column to tbl_users";
    } else {
        $errors[] = "❌ Error adding can_access_all_offices: " . mysqli_error($conn);
    }
} else {
    $messages[] = "✅ can_access_all_offices column already exists";
}

// Step 3: Simplify permissions - Clear and re-insert clean permissions
$clear_permissions = isset($_GET['reset_permissions']) && $_GET['reset_permissions'] == '1';

if ($clear_permissions) {
    // Clear existing
    mysqli_query($conn, "DELETE FROM tbl_role_permissions");
    mysqli_query($conn, "DELETE FROM tbl_permissions");
    
    // Insert simplified permissions
    $permissions = [
        ['dashboard_view', 'View Dashboard', 'Dashboard'],
        ['docket_view', 'View Dockets', 'Dockets'],
        ['docket_create', 'Create New Docket/Trip', 'Dockets'],
        ['docket_edit', 'Edit Dockets', 'Dockets'],
        ['docket_delete', 'Delete Dockets', 'Dockets'],
        ['docket_status_update', 'Update Docket Status', 'Dockets'],
        ['docket_print', 'Print Docket/Sticker', 'Dockets'],
        ['manifest_view', 'View Manifests', 'Manifest'],
        ['manifest_create', 'Create Manifests', 'Manifest'],
        ['manifest_edit', 'Edit Manifests', 'Manifest'],
        ['manifest_delete', 'Delete Manifests', 'Manifest'],
        ['manifest_print', 'Print Manifests', 'Manifest'],
        ['tracking_view', 'View Tracking', 'Tracking'],
        ['tracking_management', 'Manage Tracking', 'Tracking'],
        ['vehicle_view', 'View Vehicles', 'Fleet'],
        ['vehicle_create', 'Add Vehicles', 'Fleet'],
        ['vehicle_edit', 'Edit Vehicles', 'Fleet'],
        ['vehicle_delete', 'Delete Vehicles', 'Fleet'],
        ['staff_view', 'View Staff', 'Staff'],
        ['staff_create', 'Add Staff', 'Staff'],
        ['staff_edit', 'Edit Staff', 'Staff'],
        ['staff_delete', 'Delete Staff', 'Staff'],
        ['client_view', 'View Clients/Companies', 'Clients'],
        ['client_create', 'Add Clients', 'Clients'],
        ['client_edit', 'Edit Clients', 'Clients'],
        ['client_delete', 'Delete Clients', 'Clients'],
        ['user_view', 'View Users', 'User Management'],
        ['user_create', 'Create Users', 'User Management'],
        ['user_edit', 'Edit Users', 'User Management'],
        ['user_delete', 'Delete Users', 'User Management'],
        ['role_manage', 'Manage Roles & Permissions', 'User Management'],
        ['settings_view', 'View Settings', 'Settings'],
        ['settings_edit', 'Edit Settings', 'Settings'],
        ['office_view_all', 'View All Offices Data', 'Office Access'],
    ];
    
    foreach ($permissions as $perm) {
        $q = "INSERT INTO tbl_permissions (permission_key, permission_name, module_name) VALUES ('{$perm[0]}', '{$perm[1]}', '{$perm[2]}')";
        mysqli_query($conn, $q);
    }
    $messages[] = "✅ Permissions reset with " . count($permissions) . " clean permissions";
    
    // Give Super Admin all permissions
    mysqli_query($conn, "INSERT INTO tbl_role_permissions (role_id, permission_id) SELECT 1, permission_id FROM tbl_permissions");
    $messages[] = "✅ Super Admin role assigned all permissions";
}

// Step 4: Check/Create Delivery Agent role
$check_role = mysqli_query($conn, "SELECT role_id FROM tbl_roles WHERE role_name = 'Delivery Agent'");
if (mysqli_num_rows($check_role) == 0) {
    mysqli_query($conn, "INSERT INTO tbl_roles (role_name, role_description, is_system_role) VALUES ('Delivery Agent', 'Can only view dockets and update delivery status for their assigned office', 0)");
    $messages[] = "✅ Created 'Delivery Agent' sample role";
    
    // Assign basic permissions
    $da_role = mysqli_insert_id($conn);
    $da_perms = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key IN ('dashboard_view', 'docket_view', 'docket_status_update')");
    while ($p = mysqli_fetch_assoc($da_perms)) {
        mysqli_query($conn, "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES ($da_role, {$p['permission_id']})");
    }
    $messages[] = "✅ Assigned basic permissions to Delivery Agent role";
} else {
    $messages[] = "✅ Delivery Agent role already exists";
}

// Get current stats
$perm_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_permissions"))['cnt'];
$role_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_roles"))['cnt'];
$office_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_offices"))['cnt'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Permissions System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f6fa; padding: 30px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; border-radius: 15px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 10px; }
        h2 { color: #34495e; margin-bottom: 15px; font-size: 18px; }
        .subtitle { color: #7f8c8d; margin-bottom: 30px; }
        .message { padding: 12px 15px; border-radius: 8px; margin-bottom: 10px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px; }
        .stat { background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center; }
        .stat-value { font-size: 32px; font-weight: bold; color: #667eea; }
        .stat-label { color: #666; font-size: 14px; }
        .btn { display: inline-block; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-right: 10px; margin-top: 10px; }
        .btn-primary { background: #667eea; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><i class="fas fa-cog"></i> Permission System Setup</h1>
            <p class="subtitle">Set up the simplified user permission system</p>
            
            <?php foreach ($messages as $msg): ?>
                <div class="message success"><?php echo $msg; ?></div>
            <?php endforeach; ?>
            
            <?php foreach ($errors as $err): ?>
                <div class="message error"><?php echo $err; ?></div>
            <?php endforeach; ?>
            
            <div class="stats">
                <div class="stat">
                    <div class="stat-value"><?php echo $perm_count; ?></div>
                    <div class="stat-label">Permissions</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?php echo $role_count; ?></div>
                    <div class="stat-label">Roles</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?php echo $office_count; ?></div>
                    <div class="stat-label">Offices</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2><i class="fas fa-exclamation-triangle"></i> Reset Permissions</h2>
            <div class="warning">
                <strong>Warning:</strong> This will delete all existing permissions and role assignments, then create a clean, simplified set of permissions. Use only if you want to start fresh.
            </div>
            <a href="?reset_permissions=1" class="btn btn-danger" onclick="return confirm('Are you sure? This will reset ALL permissions!');">
                <i class="fas fa-sync"></i> Reset Permissions to Clean State
            </a>
        </div>
        
        <div class="card">
            <h2><i class="fas fa-info-circle"></i> How It Works</h2>
            <ul style="line-height: 2; padding-left: 20px;">
                <li><strong>Office Access:</strong> Each user can be assigned to an office. They'll only see dockets from that office.</li>
                <li><strong>All Offices:</strong> Check "Access All Offices" to let a user see dockets from all branches.</li>
                <li><strong>Status Permissions:</strong> Optionally restrict which statuses a user can update to.</li>
                <li><strong>Role Permissions:</strong> Base permissions still come from the user's role.</li>
            </ul>
            
            <div style="margin-top: 20px;">
                <a href="users.php" class="btn btn-primary"><i class="fas fa-users"></i> Manage Users</a>
                <a href="roles.php" class="btn btn-secondary"><i class="fas fa-user-tag"></i> Manage Roles</a>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-home"></i> Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
