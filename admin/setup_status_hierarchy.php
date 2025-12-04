<?php
/**
 * Setup Status Hierarchy Table
 * Run this on live server to populate tbl_status_hierarchy
 * URL: https://northsuperfastservice.com/admin/setup_status_hierarchy.php
 * DELETE THIS FILE AFTER RUNNING!
 */

require 'conn.php';

// Security check
session_name('pro');
session_start();

$allowed = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) {
    $allowed = true;
}
if (isset($_GET['key']) && $_GET['key'] === 'nsfs_setup_2024') {
    $allowed = true;
}

if (!$allowed) {
    die('<h2 style="color:red;">Access Denied</h2><p>Login as Super Admin or use ?key=nsfs_setup_2024</p>');
}

echo "<html><head><title>Setup Status Hierarchy</title>
<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
.success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
.error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
.info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #667eea; color: white; }
</style></head><body>";

echo "<h1>🔄 Setup Status Hierarchy</h1>";

// Check current status
$check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_status_hierarchy");
$current_count = mysqli_fetch_assoc($check)['cnt'];
echo "<div class='info'>Current status count: <strong>$current_count</strong></div>";

if (!isset($_GET['confirm'])) {
    echo "<p>This will populate tbl_status_hierarchy with standard delivery statuses.</p>";
    echo "<p><a href='?key=nsfs_setup_2024&confirm=1' style='background:#667eea; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; font-size:18px;'>✅ Setup Status Hierarchy</a></p>";
} else {
    // Clear and insert statuses
    mysqli_query($conn, "DELETE FROM tbl_status_hierarchy");
    
    $statuses = [
        ['Pending', 1, '#6c757d', 'Initial status when docket is created'],
        ['Confirmed', 2, '#17a2b8', 'Docket confirmed for pickup'],
        ['Picked Up', 3, '#007bff', 'Package picked up from sender'],
        ['In Transit', 4, '#fd7e14', 'Package is on the way'],
        ['Delayed', 5, '#ffc107', 'Delivery delayed due to issues'],
        ['Out for Delivery', 6, '#20c997', 'Package out for final delivery'],
        ['Delivered', 7, '#28a745', 'Successfully delivered'],
        ['Failed', 8, '#dc3545', 'Delivery attempt failed'],
        ['Cancelled', 9, '#6c757d', 'Docket cancelled']
    ];
    
    $success_count = 0;
    foreach ($statuses as $s) {
        $name = mysqli_real_escape_string($conn, $s[0]);
        $order = intval($s[1]);
        $color = mysqli_real_escape_string($conn, $s[2]);
        $desc = mysqli_real_escape_string($conn, $s[3]);
        
        $sql = "INSERT INTO tbl_status_hierarchy (status_name, status_order, status_color, description) 
                VALUES ('$name', $order, '$color', '$desc')";
        
        if (mysqli_query($conn, $sql)) {
            $success_count++;
            echo "<div class='success'>✓ Added: $name</div>";
        } else {
            echo "<div class='error'>✗ Failed: $name - " . mysqli_error($conn) . "</div>";
        }
    }
    
    echo "<h3>Summary</h3>";
    echo "<div class='success'><strong>$success_count / " . count($statuses) . " statuses added successfully!</strong></div>";
    
    // Show current statuses
    echo "<h3>Current Status Hierarchy:</h3>";
    echo "<table><tr><th>ID</th><th>Name</th><th>Order</th><th>Color</th></tr>";
    $result = mysqli_query($conn, "SELECT * FROM tbl_status_hierarchy ORDER BY status_order");
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['status_id']}</td>";
        echo "<td>{$row['status_name']}</td>";
        echo "<td>{$row['status_order']}</td>";
        echo "<td><span style='background:{$row['status_color']}; color:white; padding:2px 8px; border-radius:3px;'>{$row['status_color']}</span></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><a href='add_user.php' style='background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Add User</a></p>";
    echo "<p style='color:red;'><strong>⚠️ DELETE THIS FILE NOW!</strong></p>";
}

echo "</body></html>";
?>
