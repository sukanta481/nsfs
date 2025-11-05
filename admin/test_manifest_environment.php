<?php
/**
 * Manifest Environment Test Script
 * Tests PHP, database, and file encoding
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manifest Environment Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .test { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #ccc; }
        .pass { border-left-color: #4caf50; }
        .fail { border-left-color: #f44336; }
        .warning { border-left-color: #ff9800; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 0; }
        .status { font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Manifest System Environment Test</h1>

<?php
$all_passed = true;

// Test 1: PHP Version
echo '<div class="test pass">';
echo '<h2>✓ PHP Version</h2>';
echo '<p>Version: <strong>' . PHP_VERSION . '</strong></p>';
echo '<p>Status: <span class="status">OK</span></p>';
echo '</div>';

// Test 2: Database Connection
echo '<div class="test ';
try {
    require 'conn.php';
    if ($conn && !mysqli_connect_error()) {
        echo 'pass">';
        echo '<h2>✓ Database Connection</h2>';
        echo '<p>Status: <span class="status">Connected</span></p>';
        echo '<p>Database: ' . mysqli_get_host_info($conn) . '</p>';
    } else {
        echo 'fail">';
        echo '<h2>✗ Database Connection</h2>';
        echo '<p>Status: <span class="status">Failed</span></p>';
        echo '<p>Error: ' . mysqli_connect_error() . '</p>';
        $all_passed = false;
    }
} catch (Exception $e) {
    echo 'fail">';
    echo '<h2>✗ Database Connection</h2>';
    echo '<p>Status: <span class="status">Error</span></p>';
    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    $all_passed = false;
}
echo '</div>';

// Test 3: Check Tables Exist
if ($conn) {
    $tables_to_check = ['tbl_manifest', 'tbl_manifest_details', 'docket_details', 'tbl_staff', 'tbl_car', 'tbl_offices'];
    $tables_status = [];
    
    foreach ($tables_to_check as $table) {
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        $tables_status[$table] = mysqli_num_rows($result) > 0;
    }
    
    $all_tables_exist = !in_array(false, $tables_status);
    
    echo '<div class="test ' . ($all_tables_exist ? 'pass' : 'warning') . '">';
    echo '<h2>' . ($all_tables_exist ? '✓' : '⚠') . ' Required Tables</h2>';
    
    foreach ($tables_status as $table => $exists) {
        echo '<p>' . $table . ': <strong>' . ($exists ? '✓ Exists' : '✗ Missing') . '</strong></p>';
    }
    
    if (!$all_tables_exist) {
        echo '<p><a href="setup_manifest_tables.php" style="background:#4caf50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin-top:10px;">Run Setup Script</a></p>';
    }
    echo '</div>';
}

// Test 4: File Encoding Check
echo '<div class="test ';
$test_files = [
    'manifest_save.php',
    'manifest_new_entry.php',
    'manifest_fetch_docket.php'
];

$encoding_issues = [];
foreach ($test_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $first_chars = substr($content, 0, 10);
        
        // Check for BOM
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $encoding_issues[] = "$file has UTF-8 BOM (should be removed)";
        }
        
        // Check for corrupted characters
        if (strpos($content, '&lt;') !== false || strpos($content, '&gt;') !== false || 
            strpos($content, '<�') !== false || strpos($content, '>') !== false) {
            $encoding_issues[] = "$file has HTML entity corruption or encoding issues";
        }
        
        // Check if file starts with <?php
        if (trim(substr($content, 0, 5)) !== '<?php') {
            $encoding_issues[] = "$file doesn't start with <?php tag";
        }
    }
}

if (empty($encoding_issues)) {
    echo 'pass">';
    echo '<h2>✓ File Encoding</h2>';
    echo '<p>Status: <span class="status">All files OK</span></p>';
} else {
    echo 'fail">';
    echo '<h2>✗ File Encoding Issues</h2>';
    echo '<p>Status: <span class="status">Encoding problems detected</span></p>';
    foreach ($encoding_issues as $issue) {
        echo '<p>• ' . htmlspecialchars($issue) . '</p>';
    }
    echo '<p><strong>ACTION REQUIRED:</strong> Files need to be re-saved with proper UTF-8 encoding (no BOM)</p>';
    $all_passed = false;
}
echo '</div>';

// Test 5: Test Simple Manifest Save Query
if ($conn) {
    echo '<div class="test ';
    
    $test_query = "SELECT COUNT(*) as cnt FROM tbl_manifest";
    $result = mysqli_query($conn, $test_query);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo 'pass">';
        echo '<h2>✓ Manifest Table Query</h2>';
        echo '<p>Status: <span class="status">Query successful</span></p>';
        echo '<p>Total manifests in database: <strong>' . $row['cnt'] . '</strong></p>';
    } else {
        echo 'fail">';
        echo '<h2>✗ Manifest Table Query</h2>';
        echo '<p>Status: <span class="status">Query failed</span></p>';
        echo '<p>Error: ' . htmlspecialchars(mysqli_error($conn)) . '</p>';
        $all_passed = false;
    }
    echo '</div>';
}

// Test 6: PHP Settings
echo '<div class="test pass">';
echo '<h2>ℹ PHP Configuration</h2>';
echo '<p>display_errors: <strong>' . ini_get('display_errors') . '</strong></p>';
echo '<p>error_reporting: <strong>' . ini_get('error_reporting') . '</strong></p>';
echo '<p>max_execution_time: <strong>' . ini_get('max_execution_time') . 's</strong></p>';
echo '<p>memory_limit: <strong>' . ini_get('memory_limit') . '</strong></p>';
echo '</div>';

// Final Summary
echo '<div class="test ' . ($all_passed ? 'pass' : 'fail') . '" style="margin-top: 30px;">';
if ($all_passed) {
    echo '<h2>✓ All Tests Passed!</h2>';
    echo '<p>Your manifest system environment is properly configured.</p>';
    echo '<p><a href="manifest.php" style="background:#4caf50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin-top:10px;">Go to Manifest Management</a></p>';
} else {
    echo '<h2>✗ Some Tests Failed</h2>';
    echo '<p>Please fix the issues above before using the manifest system.</p>';
}
echo '</div>';

?>

</body>
</html>
