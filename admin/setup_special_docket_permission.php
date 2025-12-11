<?php
require 'conn.php';

echo "=== Adding Special Docket Permission & Creator Tracking ===\n\n";

// Step 1: Add special_docket_create permission
echo "Step 1: Adding special_docket_create permission...\n";
$check_perm = mysqli_query($conn, "SELECT * FROM tbl_permissions WHERE permission_key = 'special_docket_create'");
if (mysqli_num_rows($check_perm) == 0) {
    $sql = "INSERT INTO tbl_permissions (permission_key, permission_name, module_name) 
            VALUES ('special_docket_create', 'Create Special Dockets', 'Dockets')";
    if (mysqli_query($conn, $sql)) {
        $perm_id = mysqli_insert_id($conn);
        echo "✅ Added 'special_docket_create' permission (ID: $perm_id)\n";
        
        // Assign to Super Admin (role_id = 1)
        mysqli_query($conn, "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES (1, $perm_id)");
        echo "✅ Assigned to Super Admin role\n";
    } else {
        echo "❌ Error: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "ℹ️  Permission 'special_docket_create' already exists\n";
}

// Step 2: Check if created_by column exists in docket_details
echo "\nStep 2: Adding created_by column to track docket creators...\n";
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM docket_details LIKE 'created_by'");
if (mysqli_num_rows($check_col) == 0) {
    $sql = "ALTER TABLE docket_details 
            ADD COLUMN created_by INT(11) NULL AFTER created_at,
            ADD KEY idx_created_by (created_by)";
    if (mysqli_query($conn, $sql)) {
        echo "✅ Added 'created_by' column to docket_details table\n";
    } else {
        echo "❌ Error: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "ℹ️  Column 'created_by' already exists\n";
}

// Step 3: Check if created_by_name column exists (for fallback display)
echo "\nStep 3: Adding created_by_name column for display...\n";
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM docket_details LIKE 'created_by_name'");
if (mysqli_num_rows($check_col) == 0) {
    $sql = "ALTER TABLE docket_details 
            ADD COLUMN created_by_name VARCHAR(255) NULL AFTER created_by";
    if (mysqli_query($conn, $sql)) {
        echo "✅ Added 'created_by_name' column to docket_details table\n";
    } else {
        echo "❌ Error: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "ℹ️  Column 'created_by_name' already exists\n";
}

// Step 4: Create a sample "Special Docket Creator" role
echo "\nStep 4: Creating 'Special Docket Creator' role...\n";
$check_role = mysqli_query($conn, "SELECT role_id FROM tbl_roles WHERE role_name = 'Special Docket Creator'");
if (mysqli_num_rows($check_role) == 0) {
    $sql = "INSERT INTO tbl_roles (role_name, role_description, is_system_role) 
            VALUES ('Special Docket Creator', 'Can create special dockets and view only their own dockets', 0)";
    if (mysqli_query($conn, $sql)) {
        $role_id = mysqli_insert_id($conn);
        echo "✅ Created 'Special Docket Creator' role (ID: $role_id)\n";
        
        // Assign permissions: dashboard_view, special_docket_create, docket_view (limited)
        $perms_to_assign = ['dashboard_view', 'special_docket_create', 'docket_view'];
        foreach ($perms_to_assign as $perm_key) {
            $perm = mysqli_fetch_assoc(mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key = '$perm_key'"));
            if ($perm) {
                mysqli_query($conn, "INSERT INTO tbl_role_permissions (role_id, permission_id) VALUES ($role_id, {$perm['permission_id']})");
                echo "  ✅ Assigned '$perm_key' permission\n";
            }
        }
    } else {
        echo "❌ Error: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "ℹ️  Role 'Special Docket Creator' already exists\n";
}

echo "\n=== Summary ===\n";
echo "✅ Special docket permission added\n";
echo "✅ Creator tracking columns added (created_by, created_by_name)\n";
echo "✅ Sample role created for special docket creators\n";
echo "\nNext Steps:\n";
echo "1. Update add_special_docket.php to save created_by user ID\n";
echo "2. Update check_auth.php to filter dockets by creator for limited users\n";
echo "3. Assign 'Special Docket Creator' role to users who need it\n";

mysqli_close($conn);
?>
