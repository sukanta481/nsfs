<?php
require_once 'conn.php';

echo "<h2>Grant Admin User Full Access</h2>";

// Find admin user
$adminQuery = mysqli_query($conn, "SELECT user_id, username, role_id FROM tbl_users WHERE username = 'admin' LIMIT 1");
if (!$adminQuery) {
    die("<p style='color: red;'>Error fetching admin user: " . mysqli_error($conn) . "</p>");
}
$admin = mysqli_fetch_assoc($adminQuery);

if (!$admin) {
    die("<p style='color: red;'>Admin user not found!</p>");
}

echo "<p>Admin User Found: ID {$admin['user_id']}, Role ID {$admin['role_id']}</p>";

// Get all permissions that sukanta (super admin) has
$sukantaQuery = mysqli_query($conn, "SELECT user_id, role_id FROM tbl_users WHERE username = 'sukanta' LIMIT 1");
if (!$sukantaQuery) {
    die("<p style='color: red;'>Error fetching sukanta user: " . mysqli_error($conn) . "</p>");
}
$sukanta = mysqli_fetch_assoc($sukantaQuery);

if (!$sukanta) {
    die("<p style='color: red;'>Sukanta user not found!</p>");
}

echo "<p>Sukanta User Found: ID {$sukanta['user_id']}, Role ID {$sukanta['role_id']}</p>";

// Get all permissions from sukanta's role
$superAdminPerms = mysqli_query($conn, "SELECT permission_id FROM tbl_role_permissions WHERE role_id = {$sukanta['role_id']}");
$permissionIds = [];
while ($perm = mysqli_fetch_assoc($superAdminPerms)) {
    $permissionIds[] = $perm['permission_id'];
}

echo "<p>Super Admin has " . count($permissionIds) . " permissions</p>";

// Get current admin permissions
$adminPerms = mysqli_query($conn, "SELECT permission_id FROM tbl_role_permissions WHERE role_id = {$admin['role_id']}");
$currentAdminPerms = [];
while ($perm = mysqli_fetch_assoc($adminPerms)) {
    $currentAdminPerms[] = $perm['permission_id'];
}

echo "<p>Admin currently has " . count($currentAdminPerms) . " permissions</p>";

// Find missing permissions
$missingPerms = array_diff($permissionIds, $currentAdminPerms);

if (empty($missingPerms)) {
    echo "<p style='color: green;'>Admin already has all super admin permissions!</p>";
} else {
    echo "<p>Adding " . count($missingPerms) . " missing permissions to admin role...</p>";
    
    echo "<h3>Missing Permissions Being Added:</h3><ul>";
    
    foreach ($missingPerms as $permId) {
        // Get permission name for display
        $permNameQuery = mysqli_query($conn, "SELECT permission_key, permission_name FROM tbl_permissions WHERE id = $permId");
        $permName = mysqli_fetch_assoc($permNameQuery);
        
        echo "<li>{$permName['permission_key']} - {$permName['permission_name']}</li>";
        
        // Insert permission for admin role
        $insertQuery = "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES ({$admin['role_id']}, $permId)";
        if (mysqli_query($conn, $insertQuery)) {
            echo " ✓ Added<br>";
        } else {
            echo " ✗ Error: " . mysqli_error($conn) . "<br>";
        }
    }
    
    echo "</ul>";
    
    echo "<p style='color: green; font-weight: bold;'>✓ Admin role has been granted all super admin permissions!</p>";
    echo "<p>The admin user will now have full access to all dockets and features.</p>";
}

echo "<hr>";
echo "<p><a href='check_admin_permissions.php'>← Check Permissions Again</a></p>";
echo "<p><a href='index.php'>← Back to Dashboard</a></p>";
?>
