<?php
/**
 * Quick Debug for edit_user.php
 * Shows exact PHP error
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>PHP Version: " . phpversion() . "</h2>";

echo "<h3>Checking edit_user.php...</h3>";

// Check file exists
$file = __DIR__ . '/edit_user.php';
if (!file_exists($file)) {
    die("<p style='color:red'>edit_user.php NOT FOUND!</p>");
}

$size = filesize($file);
$modified = date('Y-m-d H:i:s', filemtime($file));
echo "<p>File size: $size bytes</p>";
echo "<p>Last modified: $modified</p>";

// Check for match() syntax (PHP 8 only)
$content = file_get_contents($file);
if (preg_match('/\bmatch\s*\(\s*\$/', $content)) {
    echo "<p style='color:red; font-size:20px;'>❌ FOUND PHP 8 match() SYNTAX - THIS IS THE PROBLEM!</p>";
    
    // Find the line
    $lines = explode("\n", $content);
    foreach ($lines as $num => $line) {
        if (preg_match('/\bmatch\s*\(\s*\$/', $line)) {
            echo "<p>Line " . ($num+1) . ": <code>" . htmlspecialchars(trim($line)) . "</code></p>";
        }
    }
} else {
    echo "<p style='color:green;'>✓ No match() syntax found</p>";
}

// Check for PHP 7 compatible code
if (strpos($content, '$status_icons = [') !== false) {
    echo "<p style='color:green;'>✓ PHP 7 compatible \$status_icons array found</p>";
} else {
    echo "<p style='color:orange;'>⚠ PHP 7 compatible code NOT found</p>";
}

// Try to parse the file
echo "<h3>Attempting to parse...</h3>";
$output = null;
$retval = null;

// Use PHP's lint check
$cmd = "php -l " . escapeshellarg($file) . " 2>&1";
$output = @shell_exec($cmd);
if ($output) {
    if (strpos($output, 'No syntax errors') !== false) {
        echo "<p style='color:green;'>✓ No syntax errors</p>";
    } else {
        echo "<p style='color:red;'>Syntax check output:</p><pre>" . htmlspecialchars($output) . "</pre>";
    }
} else {
    echo "<p style='color:orange;'>Could not run syntax check (shell_exec disabled)</p>";
}

// Check tbl_status_hierarchy
echo "<h3>Checking tbl_status_hierarchy...</h3>";
require_once 'conn.php';

$result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_status_hierarchy");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $count = $row['cnt'];
    echo "<p>Status count: <strong>$count</strong></p>";
    
    if ($count == 0) {
        echo "<p style='color:red; font-size:18px;'>❌ tbl_status_hierarchy is EMPTY! Run setup_status_hierarchy.php</p>";
        echo "<p><a href='setup_status_hierarchy.php?key=nsfs_setup_2024' style='background:#667eea;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Setup Status Hierarchy Now</a></p>";
    } else {
        echo "<p style='color:green;'>✓ Statuses exist</p>";
        
        // Show them
        $q = mysqli_query($conn, "SELECT * FROM tbl_status_hierarchy ORDER BY status_order");
        echo "<ul>";
        while ($r = mysqli_fetch_assoc($q)) {
            echo "<li>{$r['status_name']} (ID: {$r['status_id']})</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color:red;'>Failed to query: " . mysqli_error($conn) . "</p>";
}

echo "<hr>";
echo "<p><a href='edit_user.php?id=2' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Try edit_user.php?id=2</a></p>";
echo "<p style='color:red;'><strong>DELETE THIS FILE after debugging!</strong></p>";
?>
