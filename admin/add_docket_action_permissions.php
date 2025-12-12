<?php
/**
 * Add Docket Action Permissions
 * Adds granular permissions for docket actions: view, download PDF, edit, delete
 * Run this script once to add these permissions to the system
 */

require 'conn.php';
require 'check_auth.php';

// Only super admin can run this
if (!isSuperAdmin()) {
    die("<h2 style='color:red;'>Access Denied</h2><p>Only Super Admin can run this setup script.</p>");
}

echo "<h2>Adding Docket Action Permissions</h2>";
echo "<p>This script will add granular permissions for docket actions...</p><hr>";

// Define new permissions
$new_permissions = [
    ['docket_view_details', 'View Docket Details', 'Dockets', 'Can view individual docket details'],
    ['docket_download_pdf', 'Download Docket PDF', 'Dockets', 'Can download docket as PDF'],
    ['docket_edit', 'Edit Dockets', 'Dockets', 'Can edit docket information'],
    ['docket_delete', 'Delete Dockets', 'Dockets', 'Can delete dockets'],
    ['trip_view', 'View All Trips', 'Trips', 'Can view all trips list'],
];

echo "<h3>Step 1: Adding New Permissions</h3>";
$added_count = 0;
$existing_count = 0;
$permission_ids = [];

foreach ($new_permissions as $perm) {
    $key = mysqli_real_escape_string($conn, $perm[0]);
    $name = mysqli_real_escape_string($conn, $perm[1]);
    $module = mysqli_real_escape_string($conn, $perm[2]);
    $desc = mysqli_real_escape_string($conn, $perm[3]);
    
    // Check if permission already exists
    $check = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key = '$key'");
    
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $permission_ids[$key] = $row['permission_id'];
        echo "<div style='color: #ff9800; padding: 5px;'>⚠️ Permission '<strong>$key</strong>' already exists (ID: {$row['permission_id']})</div>";
        $existing_count++;
    } else {
        $sql = "INSERT INTO tbl_permissions (permission_key, permission_name, module_name, permission_description) 
                VALUES ('$key', '$name', '$module', '$desc')";
        
        if (mysqli_query($conn, $sql)) {
            $perm_id = mysqli_insert_id($conn);
            $permission_ids[$key] = $perm_id;
            echo "<div style='color: green; padding: 5px;'>✅ Added '<strong>$name</strong>' ($key) - ID: $perm_id</div>";
            $added_count++;
        } else {
            echo "<div style='color: red; padding: 5px;'>❌ Error adding '$key': " . mysqli_error($conn) . "</div>";
        }
    }
}

echo "<p><strong>Summary:</strong> $added_count new permissions added, $existing_count already existed.</p>";

// Step 2: Assign all docket action permissions to Super Admin
echo "<hr><h3>Step 2: Assigning Permissions to Super Admin Role</h3>";
$super_admin_role_id = 1;
$assigned_count = 0;

foreach ($permission_ids as $key => $perm_id) {
    // Check if already assigned
    $check_assign = mysqli_query($conn, "SELECT * FROM tbl_role_permissions WHERE role_id = $super_admin_role_id AND permission_id = $perm_id");
    
    if (mysqli_num_rows($check_assign) == 0) {
        $sql = "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES ($super_admin_role_id, $perm_id)";
        if (mysqli_query($conn, $sql)) {
            echo "<div style='color: green; padding: 5px;'>✅ Assigned '$key' to Super Admin</div>";
            $assigned_count++;
        }
    } else {
        echo "<div style='color: #ff9800; padding: 5px;'>⚠️ Super Admin already has '$key' permission</div>";
    }
}

echo "<p><strong>Summary:</strong> $assigned_count permissions assigned to Super Admin.</p>";

// Step 3: Show current permission structure
echo "<hr><h3>Step 3: Current Docket Permissions</h3>";
$docket_perms = mysqli_query($conn, "SELECT * FROM tbl_permissions WHERE module_name = 'Dockets' ORDER BY permission_id");

echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
echo "<thead style='background: #667eea; color: white;'>";
echo "<tr><th>ID</th><th>Permission Key</th><th>Permission Name</th><th>Description</th></tr>";
echo "</thead><tbody>";

while ($perm = mysqli_fetch_assoc($docket_perms)) {
    echo "<tr>";
    echo "<td>{$perm['permission_id']}</td>";
    echo "<td><code>{$perm['permission_key']}</code></td>";
    echo "<td>{$perm['permission_name']}</td>";
    echo "<td>" . ($perm['permission_description'] ?? '-') . "</td>";
    echo "</tr>";
}

echo "</tbody></table>";

// Step 4: Implementation guide
echo "<hr><h3>Step 4: Implementation Guide</h3>";
echo "<div style='background: #f0f0f0; padding: 15px; border-left: 4px solid #667eea;'>";
echo "<h4>How to Use These Permissions:</h4>";
echo "<ol>";
echo "<li><strong>View Docket Details</strong> (<code>docket_view_details</code>)<br>
      Add to <code>view_register.php</code>: <code>requirePermission('docket_view_details');</code></li>";
echo "<li><strong>Download Docket PDF</strong> (<code>docket_download_pdf</code>)<br>
      Add to <code>download_docket.php</code>: <code>requirePermission('docket_download_pdf');</code></li>";
echo "<li><strong>Edit Dockets</strong> (<code>docket_edit</code>)<br>
      Add to <code>edit_register_new.php</code>: <code>requirePermission('docket_edit');</code></li>";
echo "<li><strong>Delete Dockets</strong> (<code>docket_delete</code>)<br>
      Add to <code>action_handler.php</code> (delete action): <code>requirePermission('docket_delete');</code></li>";
echo "</ol>";

echo "<h4>Hide Action Buttons Based on Permissions:</h4>";
echo "<p>In <code>list_register_new.php</code>, wrap action buttons with permission checks:</p>";
echo "<pre style='background: #2d2d2d; color: #f8f8f2; padding: 10px; overflow-x: auto;'>&lt;?php if (hasPermission('docket_view_details')): ?&gt;
    &lt;a href=\"view_register.php?docket_id=...\" class=\"action-btn view\"&gt;
        &lt;i class=\"fas fa-eye\"&gt;&lt;/i&gt; View
    &lt;/a&gt;
&lt;?php endif; ?&gt;

&lt;?php if (hasPermission('docket_download_pdf')): ?&gt;
    &lt;a href=\"download_docket.php?docket_id=...\" class=\"action-btn download\"&gt;
        &lt;i class=\"fas fa-download\"&gt;&lt;/i&gt; PDF
    &lt;/a&gt;
&lt;?php endif; ?&gt;

&lt;?php if (hasPermission('docket_edit')): ?&gt;
    &lt;a href=\"edit_register_new.php?docket_id=...\" class=\"action-btn edit\"&gt;
        &lt;i class=\"fas fa-edit\"&gt;&lt;/i&gt; Edit
    &lt;/a&gt;
&lt;?php endif; ?&gt;

&lt;?php if (hasPermission('docket_delete')): ?&gt;
    &lt;button onclick=\"deleteDocket(...)\" class=\"action-btn delete\"&gt;
        &lt;i class=\"fas fa-trash\"&gt;&lt;/i&gt; Delete
    &lt;/button&gt;
&lt;?php endif; ?&gt;</pre>";
echo "</div>";

// Summary
echo "<hr><h2 style='color: green;'>✅ Setup Complete!</h2>";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
echo "<h3>What's Next?</h3>";
echo "<ul>";
echo "<li>✅ 4 new docket action permissions have been added</li>";
echo "<li>✅ Super Admin has been granted all permissions</li>";
echo "<li>⏭️ Update docket-related PHP files to use these permissions</li>";
echo "<li>⏭️ Assign permissions to roles in <a href='roles.php' style='color: #007bff;'><strong>Roles Management</strong></a></li>";
echo "<li>⏭️ Test with users having limited permissions</li>";
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
