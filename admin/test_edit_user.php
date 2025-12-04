<?php
/**
 * Diagnostic Script for edit_user.php
 * Visit: https://northsuperfastservice.com/admin/test_edit_user.php
 * DELETE THIS FILE AFTER DEBUGGING
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<html><head><title>Edit User Diagnostic</title></head><body>";
echo "<h1>Edit User Diagnostic</h1>";

// Test 1: Load conn.php
echo "<h3>Test 1: Database Connection</h3>";
try {
    require 'conn.php';
    if (isset($conn) && $conn instanceof mysqli) {
        echo "<p style='color:green;'>✓ Database connected successfully</p>";
    } else {
        echo "<p style='color:red;'>✗ \$conn is not a valid mysqli object</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error loading conn.php: " . $e->getMessage() . "</p>";
}

// Test 2: Check tables
echo "<h3>Test 2: Required Tables</h3>";
$required_tables = ['tbl_users', 'tbl_roles', 'tbl_offices', 'tbl_status_hierarchy', 'tbl_user_status_permissions'];
foreach ($required_tables as $table) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if ($check && mysqli_num_rows($check) > 0) {
        echo "<p style='color:green;'>✓ Table $table exists</p>";
    } else {
        echo "<p style='color:red;'>✗ Table $table is MISSING</p>";
    }
}

// Test 3: Load check_auth.php
echo "<h3>Test 3: Authentication</h3>";
try {
    session_name('pro');
    session_start();
    require 'check_auth.php';
    echo "<p style='color:green;'>✓ check_auth.php loaded</p>";
    echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";
    echo "<p>Role ID: " . ($_SESSION['role_id'] ?? 'NOT SET') . "</p>";
    echo "<p>Role Name: " . ($_SESSION['role_name'] ?? 'NOT SET') . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Error loading check_auth.php: " . $e->getMessage() . "</p>";
}

// Test 4: Test user query
echo "<h3>Test 4: User Query</h3>";
$test_user_id = 2;
$query = "SELECT u.*, r.role_name FROM tbl_users u LEFT JOIN tbl_roles r ON u.role_id = r.role_id WHERE u.user_id = $test_user_id";
echo "<pre>$query</pre>";
$result = mysqli_query($conn, $query);
if ($result) {
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        echo "<p style='color:green;'>✓ User found</p>";
        echo "<pre>" . print_r($user, true) . "</pre>";
    } else {
        echo "<p style='color:orange;'>⚠ No user with ID $test_user_id</p>";
    }
} else {
    echo "<p style='color:red;'>✗ Query failed: " . mysqli_error($conn) . "</p>";
}

// Test 5: Check edit_user.php syntax
echo "<h3>Test 5: File Syntax Check</h3>";
$edit_user_path = __DIR__ . '/edit_user.php';
if (file_exists($edit_user_path)) {
    echo "<p style='color:green;'>✓ edit_user.php exists</p>";
    
    // Try to include without executing
    $output = shell_exec("php -l $edit_user_path 2>&1");
    if ($output) {
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<p style='color:green;'>✓ No syntax errors detected</p>";
        } else {
            echo "<p style='color:red;'>✗ Syntax errors found:</p>";
            echo "<pre>$output</pre>";
        }
    } else {
        echo "<p style='color:orange;'>⚠ Could not run syntax check (shell_exec disabled)</p>";
    }
} else {
    echo "<p style='color:red;'>✗ edit_user.php NOT FOUND</p>";
}

echo "<hr><p><strong>Next Step:</strong> If all tests pass, try accessing: <a href='edit_user.php?id=2'>edit_user.php?id=2</a></p>";
echo "<p style='color:red;'><strong>DELETE THIS FILE after debugging!</strong></p>";
echo "</body></html>";
?>
