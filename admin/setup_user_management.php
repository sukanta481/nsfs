<?php
/**
 * User Management System Setup
 * Creates all necessary tables for role-based access control
 */

require 'conn.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>User Management Setup</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .btn { display: inline-block; padding: 12px 30px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn:hover { background: #45a049; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔐 User Management System Setup</h1>";

$all_success = true;

// 1. Create tbl_users table
echo "<h3>1. Creating Users Table...</h3>";
$create_users = "CREATE TABLE IF NOT EXISTS `tbl_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL UNIQUE,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `active_status` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  KEY `role_id` (`role_id`),
  KEY `staff_id` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $create_users)) {
    echo "<div class='success'>✓ Users table created successfully</div>";
} else {
    echo "<div class='error'>✗ Error: " . mysqli_error($conn) . "</div>";
    $all_success = false;
}

// 2. Create tbl_roles table
echo "<h3>2. Creating Roles Table...</h3>";
$create_roles = "CREATE TABLE IF NOT EXISTS `tbl_roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) NOT NULL UNIQUE,
  `role_description` text,
  `is_system_role` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $create_roles)) {
    echo "<div class='success'>✓ Roles table created successfully</div>";
} else {
    echo "<div class='error'>✗ Error: " . mysqli_error($conn) . "</div>";
    $all_success = false;
}

// 3. Create tbl_permissions table
echo "<h3>3. Creating Permissions Table...</h3>";
$create_permissions = "CREATE TABLE IF NOT EXISTS `tbl_permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_key` varchar(100) NOT NULL UNIQUE,
  `permission_name` varchar(255) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `permission_description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $create_permissions)) {
    echo "<div class='success'>✓ Permissions table created successfully</div>";
} else {
    echo "<div class='error'>✗ Error: " . mysqli_error($conn) . "</div>";
    $all_success = false;
}

// 4. Create tbl_role_permissions table (junction table)
echo "<h3>4. Creating Role-Permissions Junction Table...</h3>";
$create_role_permissions = "CREATE TABLE IF NOT EXISTS `tbl_role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`, `permission_id`),
  KEY `role_id` (`role_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $create_role_permissions)) {
    echo "<div class='success'>✓ Role-Permissions table created successfully</div>";
} else {
    echo "<div class='error'>✗ Error: " . mysqli_error($conn) . "</div>";
    $all_success = false;
}

// 5. Insert default permissions
echo "<h3>5. Inserting Default Permissions...</h3>";
$permissions = [
    // Dashboard
    ['dashboard_view', 'View Dashboard', 'Dashboard', 'Access to main dashboard'],
    
    // Dockets/Register
    ['docket_view', 'View Dockets', 'Dockets', 'View docket list'],
    ['docket_create', 'Create Dockets', 'Dockets', 'Create new dockets'],
    ['docket_edit', 'Edit Dockets', 'Dockets', 'Edit existing dockets'],
    ['docket_delete', 'Delete Dockets', 'Dockets', 'Delete dockets'],
    
    // Manifest
    ['manifest_view', 'View Manifests', 'Manifest', 'View manifest list'],
    ['manifest_create', 'Create Manifests', 'Manifest', 'Create new manifests'],
    ['manifest_edit', 'Edit Manifests', 'Manifest', 'Edit existing manifests'],
    ['manifest_delete', 'Delete Manifests', 'Manifest', 'Delete manifests'],
    ['manifest_print', 'Print Manifests', 'Manifest', 'Print manifest documents'],
    
    // Staff Management
    ['staff_view', 'View Staff', 'Staff', 'View staff list'],
    ['staff_create', 'Create Staff', 'Staff', 'Add new staff members'],
    ['staff_edit', 'Edit Staff', 'Staff', 'Edit staff information'],
    ['staff_delete', 'Delete Staff', 'Staff', 'Delete staff members'],
    
    // Clients
    ['client_view', 'View Clients', 'Clients', 'View client list'],
    ['client_create', 'Create Clients', 'Clients', 'Add new clients'],
    ['client_edit', 'Edit Clients', 'Clients', 'Edit client information'],
    ['client_delete', 'Delete Clients', 'Clients', 'Delete clients'],
    
    // Vehicles/Cars
    ['vehicle_view', 'View Vehicles', 'Vehicles', 'View vehicle list'],
    ['vehicle_create', 'Create Vehicles', 'Vehicles', 'Add new vehicles'],
    ['vehicle_edit', 'Edit Vehicles', 'Vehicles', 'Edit vehicle information'],
    ['vehicle_delete', 'Delete Vehicles', 'Vehicles', 'Delete vehicles'],
    
    // Reports
    ['reports_view', 'View Reports', 'Reports', 'Access to all reports'],
    ['reports_export', 'Export Reports', 'Reports', 'Export reports to PDF/Excel'],
    
    // Settings
    ['settings_view', 'View Settings', 'Settings', 'View system settings'],
    ['settings_edit', 'Edit Settings', 'Settings', 'Modify system settings'],
    
    // User Management
    ['user_view', 'View Users', 'User Management', 'View user list'],
    ['user_create', 'Create Users', 'User Management', 'Create new users'],
    ['user_edit', 'Edit Users', 'User Management', 'Edit user information'],
    ['user_delete', 'Delete Users', 'User Management', 'Delete users'],
    ['role_manage', 'Manage Roles', 'User Management', 'Create and manage roles and permissions'],
];

$permission_count = 0;
foreach ($permissions as $perm) {
    $check = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key='{$perm[0]}'");
    if (mysqli_num_rows($check) == 0) {
        $sql = "INSERT INTO tbl_permissions (permission_key, permission_name, module_name, permission_description) 
                VALUES ('{$perm[0]}', '{$perm[1]}', '{$perm[2]}', '{$perm[3]}')";
        if (mysqli_query($conn, $sql)) {
            $permission_count++;
        }
    }
}
echo "<div class='success'>✓ Inserted $permission_count default permissions</div>";

// 6. Create Super Admin role
echo "<h3>6. Creating Default Roles...</h3>";
$check_role = mysqli_query($conn, "SELECT role_id FROM tbl_roles WHERE role_name='Super Admin'");
if (mysqli_num_rows($check_role) == 0) {
    $insert_role = "INSERT INTO tbl_roles (role_name, role_description, is_system_role) 
                    VALUES ('Super Admin', 'Full system access with all permissions', 1)";
    if (mysqli_query($conn, $insert_role)) {
        $super_admin_role_id = mysqli_insert_id($conn);
        echo "<div class='success'>✓ Super Admin role created (ID: $super_admin_role_id)</div>";
        
        // Assign all permissions to Super Admin
        $all_perms = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions");
        $assigned = 0;
        while ($perm = mysqli_fetch_assoc($all_perms)) {
            $assign_sql = "INSERT IGNORE INTO tbl_role_permissions (role_id, permission_id) VALUES ($super_admin_role_id, {$perm['permission_id']})";
            if (mysqli_query($conn, $assign_sql)) {
                $assigned++;
            }
        }
        echo "<div class='success'>✓ Assigned $assigned permissions to Super Admin role</div>";
    }
} else {
    $role_data = mysqli_fetch_assoc($check_role);
    echo "<div class='info'>ℹ Super Admin role already exists (ID: {$role_data['role_id']})</div>";
}

// 7. Create default admin user
echo "<h3>7. Creating Default Admin User...</h3>";
$check_user = mysqli_query($conn, "SELECT user_id FROM tbl_users WHERE username='admin'");
if (mysqli_num_rows($check_user) == 0) {
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $role_result = mysqli_query($conn, "SELECT role_id FROM tbl_roles WHERE role_name='Super Admin' LIMIT 1");
    $role = mysqli_fetch_assoc($role_result);
    
    $insert_user = "INSERT INTO tbl_users (username, email, password, full_name, role_id, active_status) 
                    VALUES ('admin', 'admin@nsfs.com', '$default_password', 'System Administrator', {$role['role_id']}, 1)";
    if (mysqli_query($conn, $insert_user)) {
        echo "<div class='success'>✓ Default admin user created successfully</div>";
        echo "<div class='info'><strong>Login Credentials:</strong><br>
              Username: <code>admin</code><br>
              Password: <code>admin123</code><br>
              <em>Please change this password after first login!</em></div>";
    }
} else {
    echo "<div class='info'>ℹ Admin user already exists</div>";
}

echo "<h3>Setup Complete!</h3>";
if ($all_success) {
    echo "<div class='success' style='font-size:18px;'><strong>✓ User Management System setup completed successfully!</strong></div>";
    echo "<p>The following tables have been created:</p>
    <ul>
        <li><code>tbl_users</code> - User accounts</li>
        <li><code>tbl_roles</code> - User roles</li>
        <li><code>tbl_permissions</code> - System permissions</li>
        <li><code>tbl_role_permissions</code> - Role-permission mappings</li>
    </ul>";
    echo "<a href='login.php' class='btn'>Go to Login Page</a>";
    echo " <a href='users.php' class='btn' style='background:#007bff;'>Manage Users</a>";
} else {
    echo "<div class='error'>Some errors occurred during setup. Please check the messages above.</div>";
}

echo "</div></body></html>";
?>
