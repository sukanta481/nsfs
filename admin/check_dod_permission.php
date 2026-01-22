<?php
require 'conn.php';

echo "<h2>Checking DOD Permission</h2>";

// Check if permission exists
$result = mysqli_query($conn, "SELECT * FROM tbl_permissions WHERE permission_key = 'docket_edit_delivery_date'");

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo "<p style='color:green;'>✅ DOD Permission exists!</p>";
    echo "<pre>";
    print_r($row);
    echo "</pre>";
    
    // Check which roles have it
    echo "<h3>Roles with DOD Permission:</h3>";
    $roles_result = mysqli_query($conn, "
        SELECT r.role_name, rp.role_id 
        FROM tbl_role_permissions rp
        JOIN tbl_roles r ON rp.role_id = r.role_id
        WHERE rp.permission_id = " . $row['permission_id']
    );
    
    if ($roles_result && mysqli_num_rows($roles_result) > 0) {
        while ($role = mysqli_fetch_assoc($roles_result)) {
            echo "- " . $role['role_name'] . " (ID: " . $role['role_id'] . ")<br>";
        }
    } else {
        echo "<p style='color:orange;'>No roles have this permission yet.</p>";
    }
} else {
    echo "<p style='color:red;'>❌ DOD Permission does NOT exist. Please run setup_dod_permission.php</p>";
    echo "<p><a href='setup_dod_permission.php'>Click here to run setup</a></p>";
}

// Also check all Dockets module permissions
echo "<h3>All Dockets Module Permissions:</h3>";
$dockets_perms = mysqli_query($conn, "SELECT permission_key, permission_name FROM tbl_permissions WHERE module_name = 'Dockets' ORDER BY permission_key");
if ($dockets_perms) {
    echo "<ul>";
    while ($perm = mysqli_fetch_assoc($dockets_perms)) {
        echo "<li><strong>" . $perm['permission_key'] . "</strong> - " . $perm['permission_name'] . "</li>";
    }
    echo "</ul>";
}
?>
