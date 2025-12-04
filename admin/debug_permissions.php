<?php
/**
 * Debug User Permissions
 * Check what permissions the current user has
 */

require 'check_auth.php';
require 'conn.php';

echo "<h2>User Session Debug</h2>";
echo "<pre>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
echo "Role ID: " . ($_SESSION['role_id'] ?? 'NOT SET') . "\n";
echo "Role Name: " . ($_SESSION['role_name'] ?? 'NOT SET') . "\n";
echo "Office ID: " . ($_SESSION['office_id'] ?? 'NOT SET') . "\n";
echo "Office Name: " . ($_SESSION['office_name'] ?? 'NOT SET') . "\n";
echo "Can Access All Offices: " . ($_SESSION['can_access_all_offices'] ?? 'NOT SET') . "\n";
echo "</pre>";

echo "<h2>User's Role Permissions</h2>";
$role_id = intval($_SESSION['role_id'] ?? 0);
$perms_query = "SELECT p.permission_key, p.permission_name, p.module_name 
                FROM tbl_role_permissions rp 
                JOIN tbl_permissions p ON rp.permission_id = p.permission_id 
                WHERE rp.role_id = $role_id 
                ORDER BY p.module_name, p.permission_name";
$perms_result = mysqli_query($conn, $perms_query);

echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr><th>Key</th><th>Name</th><th>Module</th><th>hasPermission()</th></tr>";
if ($perms_result && mysqli_num_rows($perms_result) > 0) {
    while ($perm = mysqli_fetch_assoc($perms_result)) {
        $has = hasPermission($perm['permission_key']) ? '✅ YES' : '❌ NO';
        echo "<tr>";
        echo "<td>" . $perm['permission_key'] . "</td>";
        echo "<td>" . $perm['permission_name'] . "</td>";
        echo "<td>" . $perm['module_name'] . "</td>";
        echo "<td>" . $has . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No permissions found for role ID: $role_id</td></tr>";
}
echo "</table>";

echo "<h2>Permission Tests</h2>";
$test_perms = ['dashboard_view', 'docket_view', 'docket_status_update', 'manifest_view', 'settings_view'];
echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr><th>Permission Key</th><th>Result</th></tr>";
foreach ($test_perms as $pk) {
    $result = hasPermission($pk) ? '✅ YES' : '❌ NO';
    echo "<tr><td>$pk</td><td>$result</td></tr>";
}
echo "</table>";

echo "<h2>Is Super Admin?</h2>";
echo isSuperAdmin() ? "✅ YES" : "❌ NO";

echo "<h2>Office Filter</h2>";
echo "<pre>" . getOfficeFilter('dd') . "</pre>";

echo "<p><a href='index.php'>← Back to Dashboard</a></p>";
?>
