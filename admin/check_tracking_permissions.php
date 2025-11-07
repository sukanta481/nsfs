<?php
// Quick script to check if tracking permissions and tables are in the database
require 'conn.php';

echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h3 { color: #667eea; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; }
th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
th { background: #667eea; color: white; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.btn { padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
.btn:hover { background: #5568d3; }
</style>";

echo "<h2>🔍 Tracking System Database Check</h2>";

// Check 1: Tracking Permissions
echo "<h3>1. Checking Tracking Permissions...</h3>";
$result = mysqli_query($conn, "SELECT * FROM tbl_permissions WHERE module_name='Tracking' ORDER BY permission_id");

if (!$result) {
    echo "<p class='error'>✗ Error checking permissions: " . mysqli_error($conn) . "</p>";
} else {
    $count = mysqli_num_rows($result);
    echo "<p><strong>Tracking permissions found: $count</strong></p>";
    
    if ($count > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Permission Key</th><th>Permission Name</th><th>Module</th><th>Description</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['permission_id'] . "</td>";
            echo "<td>" . $row['permission_key'] . "</td>";
            echo "<td>" . $row['permission_name'] . "</td>";
            echo "<td>" . $row['module_name'] . "</td>";
            echo "<td>" . $row['permission_description'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<p class='success'>✓ Tracking permissions are properly installed!</p>";
    } else {
        echo "<p class='error'>✗ No tracking permissions found in database!</p>";
        echo "<p class='warning'>You need to import the SQL file:</p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin (http://localhost/phpmyadmin)</li>";
        echo "<li>Select your database</li>";
        echo "<li>Click 'Import' tab</li>";
        echo "<li>Choose file: <code>admin/add_tracking_permissions.sql</code></li>";
        echo "<li>Click 'Go' button</li>";
        echo "</ol>";
    }
}

echo "<hr>";

// Check 2: Tracking History Table
echo "<h3>2. Checking Tracking History Table...</h3>";
$result = @mysqli_query($conn, "SHOW TABLES LIKE 'tbl_tracking_history'");

if ($result && mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✓ tbl_tracking_history table exists</p>";
    
    // Check columns
    $cols = mysqli_query($conn, "SHOW COLUMNS FROM tbl_tracking_history");
    if ($cols) {
        echo "<table>";
        echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        while ($col = mysqli_fetch_assoc($cols)) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Count records
    $count_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_tracking_history");
    $count_row = mysqli_fetch_assoc($count_result);
    echo "<p>Records in table: <strong>" . $count_row['cnt'] . "</strong></p>";
} else {
    echo "<p class='error'>✗ tbl_tracking_history table does NOT exist</p>";
    echo "<p class='warning'>Import: <code>admin/create_tracking_system.sql</code></p>";
}

echo "<hr>";

// Check 3: Tracking Status Config Table
echo "<h3>3. Checking Tracking Status Config Table...</h3>";
$result = @mysqli_query($conn, "SHOW TABLES LIKE 'tbl_tracking_status_config'");

if ($result && mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✓ tbl_tracking_status_config table exists</p>";
    
    $count_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_tracking_status_config");
    $count_row = mysqli_fetch_assoc($count_result);
    echo "<p>Status configurations: <strong>" . $count_row['cnt'] . "</strong></p>";
    
    if ($count_row['cnt'] > 0) {
        $statuses = mysqli_query($conn, "SELECT status_name, status_label, status_color, is_active FROM tbl_tracking_status_config ORDER BY display_order");
        echo "<table>";
        echo "<tr><th>Status Name</th><th>Label</th><th>Color</th><th>Active</th></tr>";
        while ($s = mysqli_fetch_assoc($statuses)) {
            echo "<tr>";
            echo "<td>" . $s['status_name'] . "</td>";
            echo "<td>" . $s['status_label'] . "</td>";
            echo "<td><span style='background:" . $s['status_color'] . "; color:white; padding:3px 10px; border-radius:3px;'>" . $s['status_color'] . "</span></td>";
            echo "<td>" . ($s['is_active'] ? '✓ Yes' : '✗ No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p class='error'>✗ tbl_tracking_status_config table does NOT exist</p>";
    echo "<p class='warning'>Import: <code>admin/create_tracking_system.sql</code></p>";
}

echo "<hr>";

// Check 4: Tracking Notifications Table
echo "<h3>4. Checking Tracking Notifications Table...</h3>";
$result = @mysqli_query($conn, "SHOW TABLES LIKE 'tbl_tracking_notifications'");

if ($result && mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✓ tbl_tracking_notifications table exists</p>";
    $count_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_tracking_notifications");
    $count_row = mysqli_fetch_assoc($count_result);
    echo "<p>Notifications logged: <strong>" . $count_row['cnt'] . "</strong></p>";
} else {
    echo "<p class='error'>✗ tbl_tracking_notifications table does NOT exist</p>";
    echo "<p class='warning'>Import: <code>admin/create_tracking_system.sql</code></p>";
}

echo "<hr>";

// Summary
echo "<h3>📊 Summary</h3>";
$has_permissions = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tbl_permissions WHERE module_name='Tracking'")) > 0;
$has_history = mysqli_num_rows(@mysqli_query($conn, "SHOW TABLES LIKE 'tbl_tracking_history'")) > 0;
$has_config = mysqli_num_rows(@mysqli_query($conn, "SHOW TABLES LIKE 'tbl_tracking_status_config'")) > 0;
$has_notifications = mysqli_num_rows(@mysqli_query($conn, "SHOW TABLES LIKE 'tbl_tracking_notifications'")) > 0;

if ($has_permissions && $has_history && $has_config && $has_notifications) {
    echo "<p class='success' style='font-size:18px;'>✓ All tracking system components are installed!</p>";
    echo "<p>You can now use the tracking system:</p>";
    echo "<a href='tracking_management.php' class='btn'>Open Tracking Dashboard</a>";
    echo "<a href='roles.php' class='btn'>Manage Permissions</a>";
} else {
    echo "<p class='error' style='font-size:18px;'>✗ Some components are missing</p>";
    echo "<ul>";
    if (!$has_permissions) echo "<li class='error'>Missing: Tracking Permissions - Import <code>admin/add_tracking_permissions.sql</code></li>";
    if (!$has_history) echo "<li class='error'>Missing: Tracking History Table - Import <code>admin/create_tracking_system.sql</code></li>";
    if (!$has_config) echo "<li class='error'>Missing: Tracking Status Config Table - Import <code>admin/create_tracking_system.sql</code></li>";
    if (!$has_notifications) echo "<li class='error'>Missing: Tracking Notifications Table - Import <code>admin/create_tracking_system.sql</code></li>";
    echo "</ul>";
    echo "<p style='margin-top:20px;'><strong>Quick Fix:</strong> Import the main SQL file which includes everything:</p>";
    echo "<ol>";
    echo "<li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "<li>Select your database</li>";
    echo "<li>Click 'Import' tab</li>";
    echo "<li>Choose file: <code>c:\\xampp\\htdocs\\nsfs\\admin\\create_tracking_system.sql</code></li>";
    echo "<li>Click 'Go' button</li>";
    echo "<li>Refresh this page</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><a href='index.php' class='btn'>Back to Dashboard</a> <a href='roles.php' class='btn'>Manage Roles</a></p>";
?>
