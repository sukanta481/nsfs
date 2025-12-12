<?php
require_once 'conn.php';

echo "<h2>Add docket_view_all Permission</h2>";

// Add the permission
$key = 'docket_view_all';
$name = 'View All Dockets (Unfiltered)';
$module = 'Dockets';
$desc = 'Can view ALL dockets without creator filter - gives full access to all dockets regardless of who created them';

// Check if permission already exists
$check = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key = '$key'");

if (mysqli_num_rows($check) > 0) {
    $row = mysqli_fetch_assoc($check);
    $permission_id = $row['permission_id'];
    echo "<p style='color: orange;'>Permission '$key' already exists (ID: $permission_id)</p>";
} else {
    $sql = "INSERT INTO tbl_permissions (permission_key, permission_name, module_name, permission_description) 
            VALUES ('$key', '$name', '$module', '$desc')";
    
    if (mysqli_query($conn, $sql)) {
        $permission_id = mysqli_insert_id($conn);
        echo "<p style='color: green;'>✓ Successfully added permission '$key' (ID: $permission_id)</p>";
    } else {
        die("<p style='color: red;'>Error adding permission: " . mysqli_error($conn) . "</p>");
    }
}

// Now grant to Admin (role_id = 3) and Staff Manager (role_id = 2)
echo "<hr><h3>Granting Permission to Roles</h3>";

$roles = [
    ['id' => 1, 'name' => 'Super Admin'],
    ['id' => 2, 'name' => 'Staff Manager'],
    ['id' => 3, 'name' => 'Admin']
];

foreach ($roles as $role) {
    // Check if permission already granted
    $checkGrant = mysqli_query($conn, "SELECT * FROM tbl_role_permissions WHERE role_id = {$role['id']} AND permission_id = $permission_id");
    
    if (mysqli_num_rows($checkGrant) > 0) {
        echo "<p>✓ {$role['name']} (Role ID: {$role['id']}) already has this permission</p>";
    } else {
        $grantSql = "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES ({$role['id']}, $permission_id)";
        
        if (mysqli_query($conn, $grantSql)) {
            echo "<p style='color: green;'>✓ Successfully granted to {$role['name']} (Role ID: {$role['id']})</p>";
        } else {
            echo "<p style='color: red;'>✗ Error granting to {$role['name']}: " . mysqli_error($conn) . "</p>";
        }
    }
}

echo "<hr>";
echo "<h3 style='color: green;'>✓ Setup Complete!</h3>";
echo "<p>The docket_view_all permission has been added and granted to Super Admin, Staff Manager, and Admin roles.</p>";
echo "<p><a href='check_admin_permissions.php'>Verify Permissions</a> | <a href='index.php'>Go to Dashboard</a></p>";
?>
