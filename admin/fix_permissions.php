<?php
/**
 * Fix Live Server Permissions
 * Run this on live server to update permission keys to match left_panel.php
 * URL: https://northsuperfastservice.com/admin/fix_permissions.php?key=nsfs_fix_2024
 * DELETE THIS FILE AFTER RUNNING!
 */

require 'conn.php';

// Security check
session_name('pro');
session_start();

$allowed = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) {
    $allowed = true;
}
if (isset($_GET['key']) && $_GET['key'] === 'nsfs_fix_2024') {
    $allowed = true;
}

if (!$allowed) {
    die('<h2 style="color:red;">Access Denied</h2><p>Login as Super Admin or use ?key=nsfs_fix_2024</p>');
}

echo "<html><head><title>Fix Permissions</title>
<style>
body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; }
.success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
.error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
.info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #667eea; color: white; }
</style></head><body>";

echo "<h1>🔧 Fix Live Server Permissions</h1>";

// Show current permissions
echo "<h3>Current Permissions:</h3>";
$result = mysqli_query($conn, "SELECT * FROM tbl_permissions ORDER BY module_name, permission_name");
echo "<table><tr><th>ID</th><th>Key</th><th>Name</th><th>Module</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>{$row['permission_id']}</td><td>{$row['permission_key']}</td><td>{$row['permission_name']}</td><td>{$row['module_name']}</td></tr>";
}
echo "</table>";

if (!isset($_GET['confirm'])) {
    echo "<div class='info'>";
    echo "<h3>This will replace ALL permissions with the correct keys that match left_panel.php</h3>";
    echo "<p>The sidebar checks for specific permission keys. Your current permissions have different keys.</p>";
    echo "</div>";
    echo "<p><a href='?key=nsfs_fix_2024&confirm=1' style='background:#667eea; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; font-size:18px;'>✅ Fix Permissions Now</a></p>";
} else {
    mysqli_begin_transaction($conn);
    
    try {
        // Clear existing
        mysqli_query($conn, "DELETE FROM tbl_role_permissions");
        mysqli_query($conn, "DELETE FROM tbl_permissions");
        mysqli_query($conn, "ALTER TABLE tbl_permissions AUTO_INCREMENT = 1");
        
        // Insert correct permissions matching left_panel.php checks
        $permissions = [
            // Dashboard
            ['dashboard_view', 'View Dashboard', 'Dashboard'],
            
            // Dockets - matching left_panel.php checks
            ['docket_view', 'View Dockets', 'Dockets'],
            ['docket_create', 'Create Dockets', 'Dockets'],
            ['docket_status_update', 'Update Docket Status', 'Dockets'],
            
            // Manifest
            ['manifest_view', 'View Manifests', 'Manifest'],
            
            // Tracking
            ['tracking_view', 'View Tracking', 'Tracking'],
            ['tracking_management', 'Manage Tracking', 'Tracking'],
            
            // Fleet - matching left_panel.php checks
            ['vehicle_view', 'View Vehicles', 'Fleet'],
            ['staff_view', 'View Staff', 'Fleet'],
            
            // Companies - matching left_panel.php checks
            ['client_view', 'View Clients/Companies', 'Companies'],
            
            // Settings
            ['settings_view', 'View Settings', 'Settings'],
            
            // User Management - matching left_panel.php checks
            ['user_view', 'View Users', 'User Management'],
            ['user_create', 'Create Users', 'User Management'],
            ['user_edit', 'Edit Users', 'User Management'],
            ['user_delete', 'Delete Users', 'User Management'],
            ['role_view', 'View Roles', 'User Management'],
            ['role_manage', 'Manage Roles', 'User Management'],
            
            // Access Control
            ['office_view_all', 'Access All Offices', 'Access Control'],
        ];
        
        foreach ($permissions as $p) {
            $key = mysqli_real_escape_string($conn, $p[0]);
            $name = mysqli_real_escape_string($conn, $p[1]);
            $module = mysqli_real_escape_string($conn, $p[2]);
            
            $sql = "INSERT INTO tbl_permissions (permission_key, permission_name, module_name) VALUES ('$key', '$name', '$module')";
            if (!mysqli_query($conn, $sql)) {
                throw new Exception("Failed to insert: $key - " . mysqli_error($conn));
            }
            echo "<div class='success'>✓ Added: $key</div>";
        }
        
        // Give Super Admin (role_id=1) ALL permissions
        mysqli_query($conn, "INSERT INTO tbl_role_permissions (role_id, permission_id) SELECT 1, permission_id FROM tbl_permissions");
        echo "<div class='success'>✓ Assigned all " . count($permissions) . " permissions to Super Admin</div>";
        
        // Give Delivery Agent (role_id=2) basic permissions
        $basic_perms = ['dashboard_view', 'docket_view', 'docket_status_update'];
        foreach ($basic_perms as $perm) {
            mysqli_query($conn, "INSERT INTO tbl_role_permissions (role_id, permission_id) SELECT 2, permission_id FROM tbl_permissions WHERE permission_key = '$perm'");
        }
        echo "<div class='success'>✓ Assigned basic permissions to Delivery Agent</div>";
        
        mysqli_commit($conn);
        
        echo "<h3 style='color:green;'>✅ PERMISSIONS FIXED!</h3>";
        
        // Show new permissions
        echo "<h3>New Permissions:</h3>";
        $result = mysqli_query($conn, "SELECT * FROM tbl_permissions ORDER BY module_name, permission_name");
        echo "<table><tr><th>ID</th><th>Key</th><th>Name</th><th>Module</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['permission_id']}</td><td>{$row['permission_key']}</td><td>{$row['permission_name']}</td><td>{$row['module_name']}</td></tr>";
        }
        echo "</table>";
        
        // Show role permissions count
        echo "<h3>Role Permission Counts:</h3>";
        $roles_q = mysqli_query($conn, "SELECT r.role_name, COUNT(rp.permission_id) as cnt FROM tbl_roles r LEFT JOIN tbl_role_permissions rp ON r.role_id = rp.role_id GROUP BY r.role_id");
        while ($r = mysqli_fetch_assoc($roles_q)) {
            echo "<p>{$r['role_name']}: {$r['cnt']} permissions</p>";
        }
        
        echo "<hr>";
        echo "<p><strong>IMPORTANT:</strong> You need to <strong>LOG OUT and LOG BACK IN</strong> for changes to take effect!</p>";
        echo "<p><a href='logout.php' style='background:#dc3545; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Logout Now</a></p>";
        echo "<p style='color:red;'><strong>⚠️ DELETE THIS FILE!</strong></p>";
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
}

echo "</body></html>";
?>
