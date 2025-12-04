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

// Test 5: Check edit_user.php exists and size
echo "<h3>Test 5: File Check</h3>";
$edit_user_path = __DIR__ . '/edit_user.php';
$add_user_path = __DIR__ . '/add_user.php';

if (file_exists($edit_user_path)) {
    $size = filesize($edit_user_path);
    $modified = date('Y-m-d H:i:s', filemtime($edit_user_path));
    echo "<p style='color:green;'>✓ edit_user.php exists</p>";
    echo "<p>File size: " . number_format($size) . " bytes</p>";
    echo "<p>Last modified: $modified</p>";
    
    // Check first few lines
    $lines = file($edit_user_path, FILE_IGNORE_NEW_LINES);
    echo "<p>First 10 lines:</p><pre>";
    for ($i = 0; $i < min(10, count($lines)); $i++) {
        echo htmlspecialchars($lines[$i]) . "\n";
    }
    echo "</pre>";
} else {
    echo "<p style='color:red;'>✗ edit_user.php NOT FOUND</p>";
}

echo "<hr>";
if (file_exists($add_user_path)) {
    $size = filesize($add_user_path);
    $modified = date('Y-m-d H:i:s', filemtime($add_user_path));
    echo "<p style='color:green;'>✓ add_user.php exists</p>";
    echo "<p>File size: " . number_format($size) . " bytes</p>";
    echo "<p>Last modified: $modified</p>";
} else {
    echo "<p style='color:red;'>✗ add_user.php NOT FOUND</p>";
}

// Test 6: Check check_auth.php version
echo "<h3>Test 6: Check check_auth.php File</h3>";
$check_auth_path = __DIR__ . '/check_auth.php';
if (file_exists($check_auth_path)) {
    $size = filesize($check_auth_path);
    $modified = date('Y-m-d H:i:s', filemtime($check_auth_path));
    echo "<p style='color:green;'>✓ check_auth.php exists</p>";
    echo "<p>File size: " . number_format($size) . " bytes (should be ~17,620 bytes)</p>";
    echo "<p>Last modified: $modified</p>";
    
    if ($size < 10000) {
        echo "<p style='color:red;'>⚠ WARNING: File is too small! This is an old version!</p>";
        echo "<p style='color:red;'>Expected: 17,620 bytes | Actual: $size bytes</p>";
    } elseif ($size >= 17000 && $size <= 18000) {
        echo "<p style='color:green;'>✓ File size looks correct (updated version)</p>";
    }
    
    // Check if new functions exist
    $content = file_get_contents($check_auth_path);
    $has_office_filter = strpos($content, 'function getOfficeFilter') !== false;
    $has_status_check = strpos($content, 'function canUpdateToStatus') !== false;
    
    echo "<p>Has getOfficeFilter function: " . ($has_office_filter ? '<span style="color:green;">✓ YES</span>' : '<span style="color:red;">✗ NO (OLD VERSION!)</span>') . "</p>";
    echo "<p>Has canUpdateToStatus function: " . ($has_status_check ? '<span style="color:green;">✓ YES</span>' : '<span style="color:red;">✗ NO (OLD VERSION!)</span>') . "</p>";
    
    if (!$has_office_filter || !$has_status_check) {
        echo "<p style='color:red; background:#fff3cd; padding:15px; border-left:4px solid #ffc107;'>";
        echo "<strong>⚠ ACTION REQUIRED:</strong><br>";
        echo "Your check_auth.php is OUTDATED! You need to upload the new version from GitHub.<br>";
        echo "Expected functions: getOfficeFilter(), canUpdateToStatus(), getAllowedStatuses()";
        echo "</p>";
    }
} else {
    echo "<p style='color:red;'>✗ check_auth.php NOT FOUND</p>";
}

echo "<hr><p><strong>Next Step:</strong> If all tests pass, try accessing: <a href='edit_user.php?id=2'>edit_user.php?id=2</a></p>";
echo "<p style='color:red;'><strong>DELETE THIS FILE after debugging!</strong></p>";
echo "</body></html>";
?>
