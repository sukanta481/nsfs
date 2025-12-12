<?php
require_once 'conn.php';

echo "<h2>Grant docket_view_all Permission to Admin and Staff Manager Roles</h2>";

// Get the docket_view_all permission ID
$permQuery = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key = 'docket_view_all'");
if (!$permQuery || mysqli_num_rows($permQuery) == 0) {
    die("<p style='color: red;'>Error: docket_view_all permission not found! Please run add_docket_action_permissions.php first.</p>");
}

$perm = mysqli_fetch_assoc($permQuery);
$docket_view_all_id = $perm['permission_id'];

echo "<p>Found docket_view_all permission with ID: $docket_view_all_id</p>";

// Roles to grant permission to
$roles = [
    ['id' => 2, 'name' => 'Staff Manager'],
    ['id' => 3, 'name' => 'Admin']
];

echo "<h3>Granting Permission:</h3><ul>";

foreach ($roles as $role) {
    // Check if permission already exists
    $checkQuery = mysqli_query($conn, "SELECT * FROM tbl_role_permissions WHERE role_id = {$role['id']} AND permission_id = $docket_view_all_id");
    
    if (mysqli_num_rows($checkQuery) > 0) {
        echo "<li><strong>{$role['name']}</strong> (Role ID: {$role['id']}): Already has docket_view_all permission ✓</li>";
    } else {
        // Insert the permission
        $insertQuery = "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES ({$role['id']}, $docket_view_all_id)";
        if (mysqli_query($conn, $insertQuery)) {
            echo "<li><strong>{$role['name']}</strong> (Role ID: {$role['id']}): <span style='color: green;'>✓ Successfully granted docket_view_all permission!</span></li>";
        } else {
            echo "<li><strong>{$role['name']}</strong> (Role ID: {$role['id']}): <span style='color: red;'>✗ Error: " . mysqli_error($conn) . "</span></li>";
        }
    }
}

echo "</ul>";

echo "<hr>";
echo "<h3>Verification</h3>";

foreach ($roles as $role) {
    $countQuery = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_role_permissions WHERE role_id = {$role['id']}");
    $count = mysqli_fetch_assoc($countQuery)['cnt'];
    echo "<p><strong>{$role['name']}</strong> now has <strong>$count</strong> total permissions</p>";
}

echo "<hr>";
echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✓ Done! Admin and Staff Manager users can now see ALL dockets!</p>";
echo "<p><a href='check_admin_permissions.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Verify Permissions</a></p>";
echo "<p><a href='index.php' style='padding: 10px 20px; background: #48bb78; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";
?>
