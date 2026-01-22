<?php
/**
 * Setup DOD (Date of Delivery) Permission
 * 
 * This permission allows users to change the delivery date even after 
 * a shipment has been marked as delivered.
 * 
 * By default, only Super Admin (role_id = 1) will have this permission.
 * Admin can grant this permission to other roles as needed.
 * 
 * Run this script once to add the permission to the system.
 */

require 'conn.php';
require 'check_auth.php';

// Only super admin can run this
if (!isSuperAdmin()) {
    die("<h2 style='color:red;'>Access Denied</h2><p>Only Super Admin can run this setup script.</p>");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup DOD Permission</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        h2 { color: #2c3e50; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>";

echo "<h2>🔐 Setup DOD (Date of Delivery) Permission</h2>";
echo "<p>This permission allows users to modify the delivery date even after a shipment has been marked as 'Delivered'.</p><hr>";

// Define the DOD permission
$permission_key = 'docket_edit_delivery_date';
$permission_name = 'Edit Delivery Date (DOD)';
$module_name = 'Dockets';
$permission_description = 'Allows changing the delivery date/time after a docket has been marked as Delivered. Only grant this to trusted users who need to correct delivery records.';

echo "<h3>Step 1: Adding DOD Permission</h3>";

// Check if permission already exists
$check_query = "SELECT permission_id FROM tbl_permissions WHERE permission_key = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param('s', $permission_key);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $permission_id = $row['permission_id'];
    echo "<div class='warning'>⚠️ Permission '<strong>$permission_key</strong>' already exists (ID: $permission_id)</div>";
} else {
    // Insert new permission
    $insert_query = "INSERT INTO tbl_permissions (permission_key, permission_name, module_name, permission_description) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param('ssss', $permission_key, $permission_name, $module_name, $permission_description);
    
    if ($stmt->execute()) {
        $permission_id = $conn->insert_id;
        echo "<div class='success'>✅ Added permission '<strong>$permission_name</strong>' ($permission_key) - ID: $permission_id</div>";
    } else {
        echo "<div class='error'>❌ Error adding permission: " . $conn->error . "</div>";
        exit;
    }
}

echo "<h3>Step 2: Assigning Permission to Super Admin</h3>";

// Super Admin role ID is typically 1
$super_admin_role_id = 1;

// Check if permission is already assigned to Super Admin
$check_assign = "SELECT * FROM tbl_role_permissions WHERE role_id = ? AND permission_id = ?";
$stmt = $conn->prepare($check_assign);
$stmt->bind_param('ii', $super_admin_role_id, $permission_id);
$stmt->execute();
$assign_result = $stmt->get_result();

if ($assign_result && $assign_result->num_rows > 0) {
    echo "<div class='warning'>⚠️ Permission already assigned to Super Admin (role_id: $super_admin_role_id)</div>";
} else {
    // Assign permission to Super Admin
    $assign_query = "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES (?, ?)";
    $stmt = $conn->prepare($assign_query);
    $stmt->bind_param('ii', $super_admin_role_id, $permission_id);
    
    if ($stmt->execute()) {
        echo "<div class='success'>✅ Permission assigned to Super Admin role</div>";
    } else {
        echo "<div class='error'>❌ Error assigning permission: " . $conn->error . "</div>";
    }
}

echo "<hr><h3>📋 Summary</h3>";
echo "<div class='info'>
    <p><strong>Permission Key:</strong> <code>$permission_key</code></p>
    <p><strong>Permission Name:</strong> $permission_name</p>
    <p><strong>Module:</strong> $module_name</p>
    <p><strong>Description:</strong> $permission_description</p>
</div>";

echo "<h3>📝 How to Use</h3>";
echo "<div class='info'>
    <p><strong>For Developers:</strong></p>
    <ul>
        <li>Check permission with: <code>hasPermission('docket_edit_delivery_date')</code></li>
        <li>This permission is automatically checked when:
            <ul>
                <li>Editing delivery datetime in the docket edit form</li>
                <li>Updating delivery date for already-delivered dockets</li>
            </ul>
        </li>
    </ul>
    <p><strong>For Administrators:</strong></p>
    <ul>
        <li>Go to <a href='roles.php'>Roles Management</a> to assign this permission to other roles</li>
        <li>Only grant this permission to users who need to correct delivery records</li>
    </ul>
</div>";

echo "<p><a href='roles.php' style='display:inline-block; padding:10px 20px; background:#667eea; color:white; text-decoration:none; border-radius:5px; margin-top:20px;'>← Go to Role Management</a></p>";

echo "</body></html>";
?>
