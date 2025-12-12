<?php
require_once 'conn.php';

echo "<h2>Roles</h2>";
$roles = mysqli_query($conn, "SELECT * FROM tbl_roles ORDER BY role_id");
if (!$roles) {
    die("Error fetching roles: " . mysqli_error($conn));
}
echo "<table border='1'><tr><th>Role ID</th><th>Role Name</th></tr>";
while($r = mysqli_fetch_assoc($roles)) {
    echo "<tr><td>{$r['role_id']}</td><td>{$r['role_name']}</td></tr>";
}
echo "</table>";

echo "<h2>Admin and Sukanta Users</h2>";
$users = mysqli_query($conn, "SELECT u.user_id, u.username, u.role_id, r.role_name FROM tbl_users u LEFT JOIN tbl_roles r ON u.role_id = r.role_id WHERE u.username IN ('admin', 'sukanta') ORDER BY u.user_id");
if (!$users) {
    die("Error fetching users: " . mysqli_error($conn));
}
echo "<table border='1'><tr><th>User ID</th><th>Username</th><th>Role ID</th><th>Role Name</th></tr>";
while($u = mysqli_fetch_assoc($users)) {
    echo "<tr><td>{$u['user_id']}</td><td>{$u['username']}</td><td>{$u['role_id']}</td><td>{$u['role_name']}</td></tr>";
}
echo "</table>";

echo "<h2>Admin User Permissions</h2>";
$adminUser = mysqli_query($conn, "SELECT user_id, role_id FROM tbl_users WHERE username = 'admin' LIMIT 1");
if (!$adminUser) {
    die("Error fetching admin user: " . mysqli_error($conn));
}
$admin = mysqli_fetch_assoc($adminUser);
if ($admin) {
    echo "<p>Admin User ID: {$admin['user_id']}, Role ID: {$admin['role_id']}</p>";
    
    $perms = mysqli_query($conn, "SELECT p.permission_key, p.permission_name 
                                   FROM tbl_role_permissions rp 
                                   JOIN tbl_permissions p ON rp.permission_id = p.permission_id 
                                   WHERE rp.role_id = {$admin['role_id']} 
                                   ORDER BY p.permission_key");
    echo "<table border='1'><tr><th>Permission Key</th><th>Permission Name</th></tr>";
    while($p = mysqli_fetch_assoc($perms)) {
        echo "<tr><td>{$p['permission_key']}</td><td>{$p['permission_name']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>Admin user not found</p>";
}

echo "<h2>Sukanta User Permissions</h2>";
$sukantaUser = mysqli_query($conn, "SELECT user_id, role_id FROM tbl_users WHERE username = 'sukanta' LIMIT 1");
$sukanta = mysqli_fetch_assoc($sukantaUser);
if ($sukanta) {
    echo "<p>Sukanta User ID: {$sukanta['user_id']}, Role ID: {$sukanta['role_id']}</p>";
    
    $perms = mysqli_query($conn, "SELECT p.permission_key, p.permission_name 
                                   FROM tbl_role_permissions rp 
                                   JOIN tbl_permissions p ON rp.permission_id = p.permission_id 
                                   WHERE rp.role_id = {$sukanta['role_id']} 
                                   ORDER BY p.permission_key");
    echo "<table border='1'><tr><th>Permission Key</th><th>Permission Name</th></tr>";
    while($p = mysqli_fetch_assoc($perms)) {
        echo "<tr><td>{$p['permission_key']}</td><td>{$p['permission_name']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>Sukanta user not found</p>";
}

echo "<h2>Docket Count Check</h2>";
$totalDockets = mysqli_query($conn, "SELECT COUNT(*) as total FROM docket_details");
$total = mysqli_fetch_assoc($totalDockets);
echo "<p>Total dockets in database: {$total['total']}</p>";

if ($admin) {
    $adminDockets = mysqli_query($conn, "SELECT COUNT(*) as total FROM docket_details WHERE created_by = {$admin['user_id']}");
    $adminTotal = mysqli_fetch_assoc($adminDockets);
    echo "<p>Dockets created by admin: {$adminTotal['total']}</p>";
}

if ($sukanta) {
    $sukantaDockets = mysqli_query($conn, "SELECT COUNT(*) as total FROM docket_details WHERE created_by = {$sukanta['user_id']}");
    $sukantaTotal = mysqli_fetch_assoc($sukantaDockets);
    echo "<p>Dockets created by sukanta: {$sukantaTotal['total']}</p>";
}
?>
