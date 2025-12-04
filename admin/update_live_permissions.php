<?php
/**
 * Update Live Server Permissions
 * Run this script once on the live server to update permissions
 * URL: https://yoursite.com/admin/update_live_permissions.php
 * 
 * DELETE THIS FILE AFTER RUNNING!
 */

require 'conn.php';

// Security check - only allow if logged in as admin or with secret key
session_name('pro');
session_start();

$allowed = false;

// Allow if logged in as Super Admin
if (isset($_SESSION['user_id']) && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) {
    $allowed = true;
}

// Or allow with secret key in URL
if (isset($_GET['key']) && $_GET['key'] === 'nsfs_update_2024') {
    $allowed = true;
}

if (!$allowed) {
    die('<h2 style="color:red;">Access Denied</h2><p>Login as Super Admin or use ?key=nsfs_update_2024</p>');
}

echo "<html><head><title>Update Permissions</title>
<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
.success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
.error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
.info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #667eea; color: white; }
</style></head><body>";

echo "<h1>🔄 Update Live Server Permissions</h1>";

if (!isset($_GET['confirm'])) {
    // Show current state and ask for confirmation
    $count_perms = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_permissions"))['cnt'];
    $count_roles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_roles"))['cnt'];
    
    echo "<div class='info'>";
    echo "<h3>Current State:</h3>";
    echo "<p>Permissions in database: <strong>$count_perms</strong></p>";
    echo "<p>Roles in database: <strong>$count_roles</strong></p>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>This script will:</h3>";
    echo "<ol>";
    echo "<li>Delete all existing role-permission assignments</li>";
    echo "<li>Delete all existing permissions</li>";
    echo "<li>Insert 16 simplified permissions</li>";
    echo "<li>Assign all 16 permissions to Super Admin (role_id=1)</li>";
    echo "<li>Assign basic permissions to Delivery Agent (role_id=2)</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p><a href='?key=nsfs_update_2024&confirm=1' style='background:#667eea; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; font-size:18px;'>✅ Confirm & Update Permissions</a></p>";
    echo "<p><a href='roles.php' style='color:#666;'>Cancel</a></p>";
    
} else {
    // Run the update
    mysqli_begin_transaction($conn);
    
    try {
        // Step 1: Clear existing
        mysqli_query($conn, "DELETE FROM tbl_role_permissions");
        echo "<div class='success'>✓ Cleared role_permissions</div>";
        
        mysqli_query($conn, "DELETE FROM tbl_permissions");
        echo "<div class='success'>✓ Cleared permissions</div>";
        
        mysqli_query($conn, "ALTER TABLE tbl_permissions AUTO_INCREMENT = 1");
        echo "<div class='success'>✓ Reset auto_increment</div>";
        
        // Step 2: Insert new permissions
        $permissions = [
            // Access Control
            ['office_view_all', 'Access All Offices', 'Access Control'],
            
            // Dockets
            ['docket_create', 'Create New Docket/Trip', 'Dockets'],
            ['docket_status_update', 'Update Docket Status', 'Dockets'],
            ['docket_view_all', 'View All Dockets', 'Dockets'],
            
            // Fleet
            ['staff_manage', 'Staff Management', 'Fleet'],
            ['vehicle_manage', 'Vehicle Management', 'Fleet'],
            
            // Pages
            ['client_manage', 'Client/Company Management', 'Pages'],
            ['dashboard_view', 'Dashboard', 'Pages'],
            ['manifest_manage', 'Manifest Management', 'Pages'],
            ['settings_view', 'Settings & Website', 'Pages'],
            ['tracking_management', 'Tracking Management', 'Pages'],
            
            // User Management
            ['user_create', 'Create Users', 'User Management'],
            ['user_delete', 'Delete Users', 'User Management'],
            ['user_edit', 'Edit Users', 'User Management'],
            ['role_manage', 'Manage Roles', 'User Management'],
            ['user_view', 'View Users', 'User Management'],
        ];
        
        foreach ($permissions as $p) {
            $key = mysqli_real_escape_string($conn, $p[0]);
            $name = mysqli_real_escape_string($conn, $p[1]);
            $module = mysqli_real_escape_string($conn, $p[2]);
            
            $sql = "INSERT INTO tbl_permissions (permission_key, permission_name, module_name) 
                    VALUES ('$key', '$name', '$module')";
            
            if (!mysqli_query($conn, $sql)) {
                throw new Exception("Failed to insert permission: $key - " . mysqli_error($conn));
            }
        }
        echo "<div class='success'>✓ Inserted " . count($permissions) . " new permissions</div>";
        
        // Step 3: Assign all to Super Admin (role_id = 1)
        $result = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions");
        $admin_count = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            mysqli_query($conn, "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES (1, " . intval($row['permission_id']) . ")");
            $admin_count++;
        }
        echo "<div class='success'>✓ Assigned $admin_count permissions to Super Admin</div>";
        
        // Step 4: Assign basic to Delivery Agent (role_id = 2)
        $basic_keys = ['dashboard_view', 'docket_status_update', 'docket_view_all'];
        $agent_count = 0;
        foreach ($basic_keys as $key) {
            $perm = mysqli_fetch_assoc(mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key='$key'"));
            if ($perm) {
                mysqli_query($conn, "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES (2, " . intval($perm['permission_id']) . ")");
                $agent_count++;
            }
        }
        echo "<div class='success'>✓ Assigned $agent_count permissions to Delivery Agent</div>";
        
        mysqli_commit($conn);
        
        echo "<div class='success' style='font-size:18px;'><strong>✅ ALL DONE! Permissions updated successfully.</strong></div>";
        
        // Show summary
        echo "<h3>New Permissions:</h3>";
        echo "<table><tr><th>ID</th><th>Key</th><th>Name</th><th>Module</th></tr>";
        $result = mysqli_query($conn, "SELECT * FROM tbl_permissions ORDER BY module_name, permission_name");
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['permission_id']}</td><td>{$row['permission_key']}</td><td>{$row['permission_name']}</td><td>{$row['module_name']}</td></tr>";
        }
        echo "</table>";
        
        echo "<p><strong style='color:red;'>⚠️ DELETE THIS FILE NOW!</strong></p>";
        echo "<p><a href='roles.php' style='background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Roles</a></p>";
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<p>Transaction rolled back. No changes made.</p>";
    }
}

echo "</body></html>";
?>
