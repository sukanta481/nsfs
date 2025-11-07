<?php
/**
 * Diagnostic Script for add_user.php 500 Error
 * Upload this to your live server and access it to see what's causing the issue
 * Access: yourdomain.com/admin/diagnostic_add_user.php
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Diagnostic Report for add_user.php</h1>";
echo "<style>body{font-family:Arial;padding:20px;}h2{color:#333;border-bottom:2px solid #666;padding-bottom:5px;}.success{color:green;}.error{color:red;}.warning{color:orange;}code{background:#f4f4f4;padding:2px 6px;border-radius:3px;}</style>";

// Test 1: PHP Version
echo "<h2>1. PHP Version</h2>";
echo "Current PHP Version: <strong>" . phpversion() . "</strong><br>";
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "<span class='success'>✓ PHP version is compatible (7.4+)</span>";
} else {
    echo "<span class='warning'>⚠ PHP version might be too old</span>";
}

// Test 2: Required Files Exist
echo "<h2>2. Required Files Check</h2>";
$required_files = [
    'check_auth.php',
    'conn.php',
    'top_header.php',
    'left_panel.php',
    'header_banner.php',
    'footer.php'
];

foreach ($required_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<span class='success'>✓ {$file} exists</span><br>";
    } else {
        echo "<span class='error'>✗ {$file} MISSING</span><br>";
    }
}

// Test 3: Check session
echo "<h2>3. Session Check</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_name('pro');
    session_start();
    echo "<span class='success'>✓ Session started successfully</span><br>";
} else {
    echo "<span class='success'>✓ Session already active</span><br>";
}

// Test 4: Database Connection
echo "<h2>4. Database Connection</h2>";
try {
    // Try to include conn.php
    if (file_exists(__DIR__ . '/conn.php')) {
        include_once __DIR__ . '/conn.php';
        
        if (isset($conn) && $conn) {
            echo "<span class='success'>✓ Database connection successful</span><br>";
            echo "Database Host: " . (isset($db_host) ? $db_host : 'N/A') . "<br>";
            echo "Database Name: " . (isset($db_name) ? $db_name : 'N/A') . "<br>";
            
            // Check required tables
            echo "<h3>Required Tables:</h3>";
            $required_tables = ['tbl_users', 'tbl_roles', 'tbl_staff'];
            foreach ($required_tables as $table) {
                $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
                if ($result && mysqli_num_rows($result) > 0) {
                    echo "<span class='success'>✓ $table exists</span><br>";
                } else {
                    echo "<span class='warning'>⚠ $table MISSING</span><br>";
                }
            }
        } else {
            echo "<span class='error'>✗ Database connection failed</span><br>";
            echo "Error: " . (isset($conn) ? mysqli_connect_error() : 'Connection variable not set') . "<br>";
        }
    } else {
        echo "<span class='error'>✗ conn.php not found</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>✗ Error: " . $e->getMessage() . "</span><br>";
}

// Test 5: Check .env file
echo "<h2>5. Environment Configuration</h2>";
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    echo "<span class='success'>✓ .env file exists</span><br>";
    echo "<span class='warning'>Make sure it has correct database credentials</span><br>";
} else {
    echo "<span class='warning'>⚠ .env file not found (using defaults)</span><br>";
    echo "Create .env file with:<br>";
    echo "<code>";
    echo "DB_HOST=localhost<br>";
    echo "DB_USER=your_db_user<br>";
    echo "DB_PASS=your_db_password<br>";
    echo "DB_NAME=your_db_name";
    echo "</code>";
}

// Test 6: File Permissions
echo "<h2>6. File Permissions</h2>";
$permission_check_files = ['add_user.php', 'check_auth.php', 'conn.php'];
foreach ($permission_check_files as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (file_exists($filepath)) {
        $perms = substr(sprintf('%o', fileperms($filepath)), -4);
        echo "$file: <code>$perms</code> ";
        if (is_readable($filepath)) {
            echo "<span class='success'>✓ Readable</span>";
        } else {
            echo "<span class='error'>✗ NOT readable</span>";
        }
        echo "<br>";
    }
}

// Test 7: Check authentication system
echo "<h2>7. Authentication System</h2>";
if (isset($_SESSION['admin_id']) || isset($_SESSION['user_id'])) {
    echo "<span class='success'>✓ User is logged in</span><br>";
    if (isset($_SESSION['user_id'])) {
        echo "User ID: " . $_SESSION['user_id'] . "<br>";
        echo "Role: " . (isset($_SESSION['role_name']) ? $_SESSION['role_name'] : 'N/A') . "<br>";
    }
} else {
    echo "<span class='warning'>⚠ Not logged in (this is OK for testing)</span><br>";
}

// Test 8: PHP Extensions
echo "<h2>8. Required PHP Extensions</h2>";
$required_extensions = ['mysqli', 'session'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='success'>✓ $ext extension loaded</span><br>";
    } else {
        echo "<span class='error'>✗ $ext extension NOT loaded</span><br>";
    }
}

// Test 9: Error logs
echo "<h2>9. Recent Errors</h2>";
$error_log_path = __DIR__ . '/../error_log';
if (file_exists($error_log_path) && is_readable($error_log_path)) {
    echo "<strong>Last 10 lines of error_log:</strong><br>";
    $lines = file($error_log_path);
    $recent_lines = array_slice($lines, -10);
    echo "<pre style='background:#f4f4f4;padding:10px;overflow:auto;max-height:300px;'>";
    echo htmlspecialchars(implode('', $recent_lines));
    echo "</pre>";
} else {
    echo "<span class='warning'>No error_log file found in parent directory</span><br>";
}

// Test 10: Try to actually run add_user.php logic
echo "<h2>10. Test add_user.php Core Logic</h2>";
try {
    // Check if we can query roles table
    if (isset($conn) && $conn) {
        $roles_query = "SELECT role_id, role_name FROM tbl_roles ORDER BY role_name";
        $roles_result = mysqli_query($conn, $roles_query);
        
        if ($roles_result) {
            $role_count = mysqli_num_rows($roles_result);
            echo "<span class='success'>✓ Can query roles table ($role_count roles found)</span><br>";
        } else {
            echo "<span class='error'>✗ Cannot query roles table</span><br>";
            echo "Error: " . mysqli_error($conn) . "<br>";
        }
        
        // Check staff table
        $staff_query = "SELECT staff_id, CONCAT(first_name, ' ', last_name) as staff_name FROM tbl_staff ORDER BY first_name LIMIT 1";
        $staff_result = mysqli_query($conn, $staff_query);
        
        if ($staff_result) {
            echo "<span class='success'>✓ Can query staff table</span><br>";
        } else {
            echo "<span class='error'>✗ Cannot query staff table</span><br>";
            echo "Error: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "<span class='error'>✗ No database connection to test queries</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>✗ Error testing queries: " . $e->getMessage() . "</span><br>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>If you see any <span class='error'>red errors</span> above, those need to be fixed first.</p>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Fix any missing files or database connection issues</li>";
echo "<li>Check that .env file has correct credentials</li>";
echo "<li>Ensure file permissions are correct (usually 644 for PHP files)</li>";
echo "<li>Check the error log for specific PHP errors</li>";
echo "<li>Try accessing <a href='add_user.php'>add_user.php</a> again</li>";
echo "</ol>";

echo "<hr><p><small>Generated: " . date('Y-m-d H:i:s') . "</small></p>";
?>
