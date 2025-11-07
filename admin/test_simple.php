<?php
/**
 * Simple Test Script - No dependencies
 * Upload ONLY this file and access it
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Quick Test</title></head><body>";
echo "<h1>Quick Server Test</h1>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>File Location:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Directory:</strong> " . __DIR__ . "</p>";

// Test database connection inline
echo "<h2>Database Test</h2>";
$db_host = 'localhost';
$db_user = 'workuidy_nsfs'; // Update this
$db_pass = 'your_password_here'; // Update this
$db_name = 'workuidy_north_super_fast_service'; // Update this

$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if ($conn) {
    echo "<p style='color:green;'>✓ Database connected successfully!</p>";
    
    // Test tables
    $tables = ['tbl_users', 'tbl_roles', 'tbl_staff'];
    echo "<h3>Tables:</h3>";
    foreach ($tables as $table) {
        $result = @mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if ($result && mysqli_num_rows($result) > 0) {
            echo "<p style='color:green;'>✓ $table exists</p>";
        } else {
            echo "<p style='color:red;'>✗ $table MISSING</p>";
        }
    }
    mysqli_close($conn);
} else {
    echo "<p style='color:red;'>✗ Database connection FAILED</p>";
    echo "<p>Error: " . mysqli_connect_error() . "</p>";
    echo "<p><strong>Update the credentials in this file and refresh!</strong></p>";
}

echo "<h2>File Checks</h2>";
$files = ['add_user.php', 'check_auth.php', 'conn.php'];
foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p style='color:green;'>✓ $file exists</p>";
    } else {
        echo "<p style='color:red;'>✗ $file MISSING</p>";
    }
}

echo "<hr><p><a href='add_user.php'>Try add_user.php now</a></p>";
echo "</body></html>";
?>
