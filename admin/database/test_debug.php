<?php
/**
 * Quick Diagnostic Test File
 * This file helps identify common issues on live servers
 * Upload this to your live server and access it via browser
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Sync - Diagnostic Test</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;}</style>";

echo "<h2>1. PHP Version Check</h2>";
$phpVersion = phpversion();
echo "<p class='success'>✓ PHP Version: $phpVersion</p>";
if (version_compare($phpVersion, '7.0.0', '<')) {
    echo "<p class='error'>⚠️ Warning: PHP version is old. Recommended 7.4+</p>";
}

echo "<h2>2. Current Directory</h2>";
echo "<p><strong>__DIR__:</strong> " . __DIR__ . "</p>";
echo "<p><strong>__FILE__:</strong> " . __FILE__ . "</p>";
echo "<p><strong>DOCUMENT_ROOT:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

echo "<h2>3. Required Files Check</h2>";
$requiredFiles = [
    '../conn.php' => __DIR__ . '/../conn.php',
    '../left_panel.php' => __DIR__ . '/../left_panel.php',
    '../header_banner.php' => __DIR__ . '/../header_banner.php',
    '../footer.php' => __DIR__ . '/../footer.php',
    'database_sync.php' => __DIR__ . '/database_sync.php'
];

foreach ($requiredFiles as $name => $path) {
    if (file_exists($path)) {
        echo "<p class='success'>✓ Found: $name ($path)</p>";
    } else {
        echo "<p class='error'>✗ Missing: $name ($path)</p>";
    }
}

echo "<h2>4. Directory Permissions</h2>";
$dirs = [
    __DIR__ => 'Current directory',
    __DIR__ . '/backups' => 'Backups directory'
];

foreach ($dirs as $dir => $label) {
    if (file_exists($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? 'Writable' : 'Not writable';
        echo "<p class='success'>✓ $label: $perms ($writable)</p>";
    } else {
        echo "<p class='warning'>⚠️ $label does not exist: $dir</p>";
        // Try to create backups directory
        if ($label === 'Backups directory') {
            if (@mkdir($dir, 0755, true)) {
                echo "<p class='success'>✓ Created backups directory</p>";
            } else {
                echo "<p class='error'>✗ Could not create backups directory</p>";
            }
        }
    }
}

echo "<h2>5. Session Test</h2>";
try {
    session_name('pro');
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
        echo "<p class='success'>✓ Session started successfully</p>";
    } else {
        echo "<p class='success'>✓ Session already active</p>";
    }
    echo "<p>Session ID: " . session_id() . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Session error: " . $e->getMessage() . "</p>";
}

echo "<h2>6. Database Connection Test</h2>";
$conn_path = __DIR__ . '/../conn.php';
if (file_exists($conn_path)) {
    try {
        require $conn_path;
        if (isset($conn) && $conn instanceof mysqli) {
            echo "<p class='success'>✓ Database connection successful</p>";
            echo "<p>MySQL Version: " . mysqli_get_server_info($conn) . "</p>";
            echo "<p>Host Info: " . mysqli_get_host_info($conn) . "</p>";
        } else {
            echo "<p class='error'>✗ conn.php loaded but \$conn not set or not a mysqli object</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Database connection error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>✗ conn.php not found</p>";
}

echo "<h2>7. Environment File Check</h2>";
$env_paths = [
    __DIR__ . '/../../.env',
    __DIR__ . '/../.env',
    __DIR__ . '/.env'
];

$env_found = false;
foreach ($env_paths as $env_path) {
    if (file_exists($env_path)) {
        echo "<p class='success'>✓ .env file found at: $env_path</p>";
        $env_found = true;
        
        // Try to read it
        $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $keys = [];
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $keys[] = trim($name);
            }
        }
        echo "<p>Keys found: " . implode(', ', $keys) . "</p>";
        break;
    }
}

if (!$env_found) {
    echo "<p class='warning'>⚠️ .env file not found in common locations</p>";
}

echo "<h2>8. PHP Extensions</h2>";
$required_extensions = ['mysqli', 'session', 'fileinfo'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✓ Extension loaded: $ext</p>";
    } else {
        echo "<p class='error'>✗ Extension missing: $ext</p>";
    }
}

echo "<h2>9. PHP Configuration</h2>";
$configs = [
    'display_errors' => ini_get('display_errors'),
    'error_reporting' => error_reporting(),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
];

echo "<pre>";
print_r($configs);
echo "</pre>";

echo "<h2>10. Include Path Test</h2>";
$test_includes = [
    '../left_panel.php',
    '../header_banner.php', 
    '../footer.php'
];

foreach ($test_includes as $include) {
    $full_path = __DIR__ . '/' . $include;
    if (file_exists($full_path)) {
        echo "<p class='success'>✓ Can access: $include</p>";
        // Check if it contains any obvious syntax errors
        $content = file_get_contents($full_path);
        if (substr($content, 0, 5) === '<?php' || strpos($content, '<?php') !== false) {
            echo "<p style='margin-left:20px;'>Contains PHP code (first 100 chars): " . htmlspecialchars(substr($content, 0, 100)) . "...</p>";
        }
    } else {
        echo "<p class='error'>✗ Cannot access: $include</p>";
    }
}

echo "<h2>Summary</h2>";
echo "<p>If you see errors above, that's likely the cause of the HTTP 500 error.</p>";
echo "<p>Common fixes:</p>";
echo "<ul>";
echo "<li>Ensure all required files exist and have correct paths</li>";
echo "<li>Check file permissions (should be 644 for files, 755 for directories)</li>";
echo "<li>Verify database connection in conn.php</li>";
echo "<li>Make sure .env file exists with correct database credentials</li>";
echo "<li>Check PHP error logs on your server</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Next Step:</strong> Check the <code>debug_sync.log</code> file in this directory after accessing database_sync.php</p>";
?>
