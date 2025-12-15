<?php
/**
 * Quick Fix: Add docket_view_all permission to a role
 */

require 'conn.php';

echo "<h2>Fix: Add docket_view_all Permission</h2>";

$role_id = isset($_GET['role_id']) ? intval($_GET['role_id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'add' && $role_id > 0) {
    // Get permission ID
    $perm_query = "SELECT permission_id FROM tbl_permissions WHERE permission_key = 'docket_view_all'";
    $perm_result = mysqli_query($conn, $perm_query);
    
    if ($perm_result && mysqli_num_rows($perm_result) > 0) {
        $perm = mysqli_fetch_assoc($perm_result);
        $permission_id = $perm['permission_id'];
        
        // Check if already exists
        $check = mysqli_query($conn, "SELECT * FROM tbl_role_permissions WHERE role_id = $role_id AND permission_id = $permission_id");
        if (mysqli_num_rows($check) > 0) {
            echo "<div style='background: #fff3cd; padding: 15px;'>Permission already exists for this role.</div>";
        } else {
            // Add permission
            $insert = mysqli_query($conn, "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES ($role_id, $permission_id)");
            if ($insert) {
                echo "<div style='background: #c8e6c9; padding: 15px; border-left: 4px solid #4caf50;'>";
                echo "<h4>✅ SUCCESS!</h4>";
                echo "<p>Added <code>docket_view_all</code> permission to role ID $role_id</p>";
                echo "<p><strong>The user needs to LOGOUT and LOGIN again for changes to take effect!</strong></p>";
                echo "</div>";
            } else {
                echo "<div style='background: #ffcdd2; padding: 15px;'>Error: " . mysqli_error($conn) . "</div>";
            }
        }
    } else {
        // Create the permission first
        echo "<div style='background: #fff3cd; padding: 15px;'>";
        echo "<p>Permission 'docket_view_all' doesn't exist. Creating it...</p>";
        
        $create_perm = mysqli_query($conn, "INSERT INTO tbl_permissions (permission_key, permission_name, description) 
                                            VALUES ('docket_view_all', 'View All Office Dockets', 'Can view all dockets in their office, not just their own')");
        if ($create_perm) {
            $permission_id = mysqli_insert_id($conn);
            $insert = mysqli_query($conn, "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES ($role_id, $permission_id)");
            if ($insert) {
                echo "<h4>✅ SUCCESS!</h4>";
                echo "<p>Created permission and added to role ID $role_id</p>";
            }
        }
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<a href='?' style='padding: 10px 20px; background: #2196f3; color: white; text-decoration: none;'>Back to List</a>";
    
} else {
    // Show all roles
    echo "<h3>Select a role to add 'docket_view_all' permission:</h3>";
    
    $roles_query = "SELECT r.*, 
                    (SELECT COUNT(*) FROM tbl_role_permissions rp 
                     INNER JOIN tbl_permissions p ON rp.permission_id = p.permission_id 
                     WHERE rp.role_id = r.role_id AND p.permission_key = 'docket_view_all') as has_permission
                    FROM tbl_roles r ORDER BY r.role_name";
    $roles_result = mysqli_query($conn, $roles_query);
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>Role ID</th><th>Role Name</th><th>Has docket_view_all?</th><th>Action</th></tr>";
    
    while ($role = mysqli_fetch_assoc($roles_result)) {
        $has = $role['has_permission'] > 0;
        $style = $has ? 'background: #c8e6c9;' : '';
        echo "<tr style='$style'>";
        echo "<td>" . $role['role_id'] . "</td>";
        echo "<td>" . htmlspecialchars($role['role_name']) . "</td>";
        echo "<td>" . ($has ? '✅ YES' : '❌ NO') . "</td>";
        echo "<td>";
        if (!$has) {
            echo "<a href='?role_id=" . $role['role_id'] . "&action=add' 
                    style='padding: 5px 15px; background: #4caf50; color: white; text-decoration: none; border-radius: 3px;'>
                    Add Permission</a>";
        } else {
            echo "-";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3;'>";
    echo "<h4>ℹ️ What does 'docket_view_all' do?</h4>";
    echo "<ul>";
    echo "<li><strong>WITH permission:</strong> User sees ALL dockets for their office (like an office manager)</li>";
    echo "<li><strong>WITHOUT permission:</strong> User sees ONLY dockets they personally created</li>";
    echo "</ul>";
    echo "<p>For Bardhaman office to see manifested dockets, their role (ID: 6) needs this permission.</p>";
    echo "</div>";
}

mysqli_close($conn);
?>
