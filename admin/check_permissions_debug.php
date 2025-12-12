<?php
require 'conn.php';

// Check trip_view permission
$res = mysqli_query($conn, "SELECT * FROM tbl_permissions WHERE permission_key = 'trip_view'");
if (mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
    echo "✅ trip_view permission exists (ID: {$row['permission_id']})\n";
} else {
    echo "❌ trip_view permission NOT FOUND - need to run setup script!\n";
}

// Check what permissions the test user has
echo "\n=== Checking Test User Permissions ===\n";
$test_user = mysqli_query($conn, "SELECT user_id, username, role_id FROM tbl_users WHERE role_id = 7 LIMIT 1");
if (mysqli_num_rows($test_user) > 0) {
    $user = mysqli_fetch_assoc($test_user);
    echo "Test User: {$user['username']} (ID: {$user['user_id']}, Role ID: {$user['role_id']})\n\n";
    
    // Get role permissions
    $perms = mysqli_query($conn, "
        SELECT p.permission_key, p.permission_name 
        FROM tbl_role_permissions rp 
        JOIN tbl_permissions p ON rp.permission_id = p.permission_id 
        WHERE rp.role_id = {$user['role_id']}
    ");
    
    echo "Permissions for this role:\n";
    while ($perm = mysqli_fetch_assoc($perms)) {
        echo "  - {$perm['permission_key']} ({$perm['permission_name']})\n";
    }
}

// Check docket filtering
echo "\n=== Checking Docket Creator Tracking ===\n";
$check = mysqli_query($conn, "SELECT COUNT(*) as total FROM docket_details");
$total = mysqli_fetch_assoc($check)['total'];
echo "Total dockets in database: $total\n";

$with_creator = mysqli_query($conn, "SELECT COUNT(*) as count FROM docket_details WHERE created_by IS NOT NULL");
$creator_count = mysqli_fetch_assoc($with_creator)['count'];
echo "Dockets with creator tracking: $creator_count\n";

if ($creator_count < $total) {
    echo "⚠️  WARNING: Some dockets don't have creator information!\n";
    echo "Old dockets may show to all users.\n";
}

mysqli_close($conn);
?>
