<?php
/**
 * Fix Special Docket Creator Role Permissions
 * Removes broad permissions and keeps only limited access
 */

require 'conn.php';
require 'check_auth.php';

if (!isSuperAdmin()) {
    die("<h2 style='color:red;'>Access Denied</h2><p>Only Super Admin can run this script.</p>");
}

echo "<h2>Fixing 'Special Docket Creator' Role Permissions</h2>";
echo "<p>This will remove broad access permissions and keep only limited permissions...</p><hr>";

// Find Special Docket Creator role
$role_query = mysqli_query($conn, "SELECT role_id FROM tbl_roles WHERE role_name = 'Special Docket Creator'");
if (mysqli_num_rows($role_query) == 0) {
    die("<div style='color: red;'>❌ 'Special Docket Creator' role not found!</div>");
}

$role = mysqli_fetch_assoc($role_query);
$role_id = $role['role_id'];

echo "<div style='color: green;'>✅ Found 'Special Docket Creator' role (ID: $role_id)</div>";

// Define permissions that should be REMOVED (broad access)
$permissions_to_remove = [
    'docket_view',          // This allows seeing ALL dockets
    'docket_edit',          // This allows editing ALL dockets
    'docket_delete',        // This allows deleting dockets
    'docket_status_update', // This allows updating status
    'trip_view',            // Don't need to see all trips
];

// Define permissions that should be KEPT/ADDED (limited access)
$permissions_to_keep = [
    'dashboard_view',
    'special_docket_create',
    'docket_view_details',  // Can view their own dockets
    'docket_download_pdf',  // Can download PDFs of their dockets
];

echo "<h3>Step 1: Removing Broad Permissions</h3>";
$removed_count = 0;

foreach ($permissions_to_remove as $perm_key) {
    $perm_query = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key = '$perm_key'");
    if (mysqli_num_rows($perm_query) > 0) {
        $perm = mysqli_fetch_assoc($perm_query);
        $perm_id = $perm['permission_id'];
        
        $delete_query = "DELETE FROM tbl_role_permissions WHERE role_id = $role_id AND permission_id = $perm_id";
        if (mysqli_query($conn, $delete_query)) {
            if (mysqli_affected_rows($conn) > 0) {
                echo "<div style='color: orange; padding: 5px;'>🗑️ Removed '<strong>$perm_key</strong>' permission</div>";
                $removed_count++;
            } else {
                echo "<div style='color: gray; padding: 5px;'>⚪ Permission '<strong>$perm_key</strong>' was not assigned</div>";
            }
        }
    }
}

echo "<p><strong>Removed $removed_count broad permissions.</strong></p>";

echo "<hr><h3>Step 2: Adding/Verifying Limited Permissions</h3>";
$added_count = 0;

foreach ($permissions_to_keep as $perm_key) {
    $perm_query = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key = '$perm_key'");
    if (mysqli_num_rows($perm_query) > 0) {
        $perm = mysqli_fetch_assoc($perm_query);
        $perm_id = $perm['permission_id'];
        
        // Check if already assigned
        $check = mysqli_query($conn, "SELECT * FROM tbl_role_permissions WHERE role_id = $role_id AND permission_id = $perm_id");
        if (mysqli_num_rows($check) == 0) {
            $insert_query = "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES ($role_id, $perm_id)";
            if (mysqli_query($conn, $insert_query)) {
                echo "<div style='color: green; padding: 5px;'>✅ Added '<strong>$perm_key</strong>' permission</div>";
                $added_count++;
            }
        } else {
            echo "<div style='color: blue; padding: 5px;'>✔️ Permission '<strong>$perm_key</strong>' already assigned</div>";
        }
    } else {
        echo "<div style='color: red; padding: 5px;'>❌ Permission '<strong>$perm_key</strong>' not found in database!</div>";
    }
}

echo "<p><strong>Added $added_count new permissions.</strong></p>";

echo "<hr><h3>Step 3: Current Permissions for 'Special Docket Creator'</h3>";
$current_perms = mysqli_query($conn, "
    SELECT p.permission_key, p.permission_name, p.module_name
    FROM tbl_role_permissions rp
    JOIN tbl_permissions p ON rp.permission_id = p.permission_id
    WHERE rp.role_id = $role_id
    ORDER BY p.module_name, p.permission_name
");

echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
echo "<thead style='background: #667eea; color: white;'>";
echo "<tr><th>Module</th><th>Permission Key</th><th>Permission Name</th></tr>";
echo "</thead><tbody>";

while ($perm = mysqli_fetch_assoc($current_perms)) {
    echo "<tr>";
    echo "<td>{$perm['module_name']}</td>";
    echo "<td><code>{$perm['permission_key']}</code></td>";
    echo "<td>{$perm['permission_name']}</td>";
    echo "</tr>";
}

echo "</tbody></table>";

echo "<hr><h2 style='color: green;'>✅ Role Permissions Fixed!</h2>";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
echo "<h3>What Changed:</h3>";
echo "<ul>";
echo "<li>✅ Removed broad permissions (docket_view, docket_edit, etc.)</li>";
echo "<li>✅ Kept limited permissions (special_docket_create, docket_view_details, etc.)</li>";
echo "<li>✅ Users with this role will now see ONLY their own dockets</li>";
echo "<li>✅ 'All Trips' menu will be hidden for these users</li>";
echo "</ul>";
echo "<h3>Expected Behavior:</h3>";
echo "<ul>";
echo "<li>✔️ Can view dashboard</li>";
echo "<li>✔️ Can create special dockets</li>";
echo "<li>✔️ Can view their own dockets (filtered automatically)</li>";
echo "<li>✔️ Can download PDFs of their dockets</li>";
echo "<li>❌ Cannot see all dockets</li>";
echo "<li>❌ Cannot edit dockets</li>";
echo "<li>❌ Cannot delete dockets</li>";
echo "<li>❌ Cannot see 'All Trips'</li>";
echo "</ul>";
echo "</div>";

echo "<div style='margin-top: 20px; text-align: center;'>";
echo "<a href='roles.php' style='display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>";
echo "Go to Roles Management";
echo "</a>";
echo "</div>";

mysqli_close($conn);
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3 {
    color: #333;
}
code {
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}
</style>
