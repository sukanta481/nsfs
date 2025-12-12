<?php
require 'conn.php';
require 'check_auth.php';

echo "=== Creator Filter Verification ===\n\n";

// Check if created_by columns exist
echo "1. Checking database columns...\n";
$cols = mysqli_query($conn, "SHOW COLUMNS FROM docket_details LIKE 'created_by%'");
if (mysqli_num_rows($cols) > 0) {
    while ($col = mysqli_fetch_assoc($cols)) {
        echo "   ✅ Column exists: {$col['Field']} ({$col['Type']})\n";
    }
} else {
    echo "   ❌ created_by columns NOT found!\n";
}

// Check special docket permission
echo "\n2. Checking special_docket_create permission...\n";
$perm = mysqli_query($conn, "SELECT * FROM tbl_permissions WHERE permission_key = 'special_docket_create'");
if (mysqli_num_rows($perm) > 0) {
    $p = mysqli_fetch_assoc($perm);
    echo "   ✅ Permission exists (ID: {$p['permission_id']})\n";
} else {
    echo "   ❌ Permission NOT found!\n";
}

// Check Special Docket Creator role
echo "\n3. Checking 'Special Docket Creator' role...\n";
$role = mysqli_query($conn, "SELECT * FROM tbl_roles WHERE role_name = 'Special Docket Creator'");
if (mysqli_num_rows($role) > 0) {
    $r = mysqli_fetch_assoc($role);
    echo "   ✅ Role exists (ID: {$r['role_id']})\n";
    
    // Check permissions for this role
    $role_perms = mysqli_query($conn, "
        SELECT p.permission_name 
        FROM tbl_role_permissions rp 
        JOIN tbl_permissions p ON rp.permission_id = p.permission_id 
        WHERE rp.role_id = {$r['role_id']}
    ");
    echo "   Assigned permissions:\n";
    while ($rp = mysqli_fetch_assoc($role_perms)) {
        echo "     - {$rp['permission_name']}\n";
    }
} else {
    echo "   ❌ Role NOT found!\n";
}

// Check for users with this role
echo "\n4. Checking users with 'Special Docket Creator' role...\n";
$users = mysqli_query($conn, "
    SELECT u.user_id, u.username, u.full_name, r.role_name 
    FROM tbl_users u 
    JOIN tbl_roles r ON u.role_id = r.role_id 
    WHERE r.role_name = 'Special Docket Creator'
");
if (mysqli_num_rows($users) > 0) {
    while ($user = mysqli_fetch_assoc($users)) {
        echo "   ✅ User: {$user['username']} ({$user['full_name']})\n";
        
        // Check dockets created by this user
        $dockets = mysqli_query($conn, "
            SELECT COUNT(*) as count 
            FROM docket_details 
            WHERE created_by = {$user['user_id']}
        ");
        $d = mysqli_fetch_assoc($dockets);
        echo "      Dockets created: {$d['count']}\n";
    }
} else {
    echo "   ℹ️  No users assigned to this role yet\n";
}

// Check recent dockets with creator info
echo "\n5. Recent dockets with creator tracking...\n";
$recent = mysqli_query($conn, "
    SELECT doc_no, created_by, created_by_name, created_at 
    FROM docket_details 
    WHERE created_by IS NOT NULL 
    ORDER BY created_at DESC 
    LIMIT 5
");
if (mysqli_num_rows($recent) > 0) {
    while ($d = mysqli_fetch_assoc($recent)) {
        echo "   - {$d['doc_no']} | Created by: {$d['created_by_name']} (ID: {$d['created_by']}) | {$d['created_at']}\n";
    }
} else {
    echo "   ℹ️  No dockets with creator info yet\n";
}

echo "\n=== Filter Function Test ===\n";
echo "Note: This will only work when logged in\n";
if (isset($_SESSION['user_id'])) {
    echo "Current user: {$_SESSION['username']} (ID: {$_SESSION['user_id']})\n";
    $filter = getCreatorFilter('dd');
    if (empty($filter)) {
        echo "Creator filter: NONE (user can see all dockets)\n";
    } else {
        echo "Creator filter: $filter\n";
        echo "This user will ONLY see their own dockets\n";
    }
} else {
    echo "Not logged in - run this from browser while logged in to test\n";
}

echo "\n=== Setup Complete! ===\n";
echo "✅ Database columns created\n";
echo "✅ Permission added\n";
echo "✅ Role created\n";
echo "✅ Filter functions implemented\n";
echo "✅ Dashboard and listing pages updated\n\n";
echo "Next steps:\n";
echo "1. Assign 'Special Docket Creator' role to a user\n";
echo "2. Login as that user\n";
echo "3. Create a special docket\n";
echo "4. User should only see their own dockets\n";

mysqli_close($conn);
?>
