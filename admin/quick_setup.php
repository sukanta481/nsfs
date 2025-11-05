<?php
/**
 * QUICK SETUP - User Management Tables
 * 
 * This is a simple standalone script that creates the user management tables
 * WITHOUT requiring authentication or other dependencies
 * 
 * Upload this file to your live server's /admin/ folder and run it once:
 * https://northsuperfastservice.com/admin/quick_setup.php
 * 
 * DELETE THIS FILE AFTER RUNNING IT!
 */

// Direct database connection (modify if needed)
$host = 'localhost';
$username = 'u286257250_north'; // Your live server DB username
$password = ''; // Your live server DB password
$database = 'u286257250_north_tbl_permissions'; // Your live server DB name

// Try to connect
$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Quick Setup</title><style>
body{font-family:Arial,sans-serif;background:#f5f5f5;padding:20px;}
.container{max-width:800px;margin:0 auto;background:#fff;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}
h1{color:#2c3e50;border-bottom:3px solid #3498db;padding-bottom:10px;}
.success{background:#d4edda;color:#155724;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;}
.error{background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #dc3545;}
.warning{background:#fff3cd;color:#856404;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #ffc107;}
code{background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;}
.btn{display:inline-block;padding:12px 24px;background:#3498db;color:white;text-decoration:none;border-radius:5px;margin-top:20px;}
.btn-danger{background:#e74c3c;}
</style></head><body><div class='container'>";

echo "<h1>🚀 Quick Setup - User Management</h1>";

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS `tbl_users` (
      `user_id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(50) NOT NULL,
      `email` varchar(100) NOT NULL,
      `password` varchar(255) NOT NULL,
      `full_name` varchar(100) DEFAULT NULL,
      `role_id` int(11) DEFAULT NULL,
      `is_active` tinyint(1) DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`user_id`),
      UNIQUE KEY `username` (`username`),
      UNIQUE KEY `email` (`email`),
      KEY `role_id` (`role_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `tbl_roles` (
      `role_id` int(11) NOT NULL AUTO_INCREMENT,
      `role_name` varchar(50) NOT NULL,
      `role_description` text DEFAULT NULL,
      `is_active` tinyint(1) DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`role_id`),
      UNIQUE KEY `role_name` (`role_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `tbl_permissions` (
      `permission_id` int(11) NOT NULL AUTO_INCREMENT,
      `permission_key` varchar(100) NOT NULL,
      `permission_name` varchar(100) NOT NULL,
      `module_name` varchar(50) DEFAULT NULL,
      `permission_description` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`permission_id`),
      UNIQUE KEY `permission_key` (`permission_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `tbl_role_permissions` (
      `role_permission_id` int(11) NOT NULL AUTO_INCREMENT,
      `role_id` int(11) NOT NULL,
      `permission_id` int(11) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`role_permission_id`),
      UNIQUE KEY `role_permission_unique` (`role_id`,`permission_id`),
      KEY `role_id` (`role_id`),
      KEY `permission_id` (`permission_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

$success_count = 0;
foreach ($tables as $sql) {
    if (mysqli_query($conn, $sql)) {
        $success_count++;
    } else {
        echo "<div class='error'>Error: " . mysqli_error($conn) . "</div>";
    }
}

echo "<div class='success'>✅ Created $success_count tables successfully</div>";

// Insert permissions
$permissions = [
    ['dashboard_view', 'View Dashboard', 'Dashboard'],
    ['docket_view', 'View Dockets', 'Dockets'],
    ['docket_create', 'Create Dockets', 'Dockets'],
    ['docket_edit', 'Edit Dockets', 'Dockets'],
    ['docket_delete', 'Delete Dockets', 'Dockets'],
    ['docket_status_update', 'Update Docket Status', 'Dockets'],
    ['manifest_view', 'View Manifest', 'Manifest'],
    ['manifest_create', 'Create Manifest', 'Manifest'],
    ['manifest_edit', 'Edit Manifest', 'Manifest'],
    ['manifest_delete', 'Delete Manifest', 'Manifest'],
    ['staff_view', 'View Staff', 'Staff'],
    ['staff_create', 'Create Staff', 'Staff'],
    ['staff_edit', 'Edit Staff', 'Staff'],
    ['staff_delete', 'Delete Staff', 'Staff'],
    ['client_view', 'View Clients', 'Clients'],
    ['client_create', 'Create Clients', 'Clients'],
    ['client_edit', 'Edit Clients', 'Clients'],
    ['client_delete', 'Delete Clients', 'Clients'],
    ['vehicle_view', 'View Vehicles', 'Vehicles'],
    ['vehicle_create', 'Create Vehicles', 'Vehicles'],
    ['vehicle_edit', 'Edit Vehicles', 'Vehicles'],
    ['vehicle_delete', 'Delete Vehicles', 'Vehicles'],
    ['report_view', 'View Reports', 'Reports'],
    ['report_export', 'Export Reports', 'Reports'],
    ['settings_view', 'View Settings', 'Settings'],
    ['settings_edit', 'Edit Settings', 'Settings'],
    ['user_view', 'View Users', 'User Management'],
    ['user_create', 'Create Users', 'User Management'],
    ['user_edit', 'Edit Users', 'User Management'],
    ['user_delete', 'Delete Users', 'User Management'],
    ['role_view', 'View Roles', 'Role Management'],
    ['role_create', 'Create Roles', 'Role Management'],
    ['role_edit', 'Edit Roles', 'Role Management'],
    ['role_delete', 'Delete Roles', 'Role Management']
];

$perm_count = 0;
foreach ($permissions as $perm) {
    $check = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key = '{$perm[0]}'");
    if (mysqli_num_rows($check) == 0) {
        $sql = "INSERT INTO tbl_permissions (permission_key, permission_name, module_name, permission_description) 
                VALUES ('{$perm[0]}', '{$perm[1]}', '{$perm[2]}', 'Access to {$perm[1]}')";
        if (mysqli_query($conn, $sql)) {
            $perm_count++;
        }
    }
}

echo "<div class='success'>✅ Inserted $perm_count permissions</div>";

// Create Super Admin role
$check_role = mysqli_query($conn, "SELECT role_id FROM tbl_roles WHERE role_name = 'Super Admin'");
if (mysqli_num_rows($check_role) == 0) {
    mysqli_query($conn, "INSERT INTO tbl_roles (role_name, role_description) VALUES ('Super Admin', 'Full system access')");
    $role_id = mysqli_insert_id($conn);
    
    // Assign all permissions
    $all_perms = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions");
    while ($p = mysqli_fetch_assoc($all_perms)) {
        mysqli_query($conn, "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES ($role_id, {$p['permission_id']})");
    }
    
    echo "<div class='success'>✅ Created Super Admin role with all permissions</div>";
} else {
    echo "<div class='success'>✅ Super Admin role already exists</div>";
}

echo "<div class='warning'><strong>⚠️ IMPORTANT:</strong> Delete this file (<code>quick_setup.php</code>) immediately for security!</div>";

echo "<a href='index.php' class='btn'>Go to Dashboard</a>";
echo "<a href='users.php' class='btn'>Manage Users</a>";
echo "</div></body></html>";

mysqli_close($conn);
?>
