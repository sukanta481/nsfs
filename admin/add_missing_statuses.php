<?php
/**
 * Add missing statuses to tbl_status_hierarchy
 * Run this once to add: Pending POD, Received at Destination, Cancelled
 */

require 'conn.php';

$statuses_to_add = [
    ['status_name' => 'Received at Destination', 'status_order' => 4.5, 'is_final' => 0],
    ['status_name' => 'Pending POD', 'status_order' => 5.5, 'is_final' => 1],
    ['status_name' => 'Cancelled', 'status_order' => 7, 'is_final' => 1],
];

echo "<h2>Adding Missing Statuses to tbl_status_hierarchy</h2>";

foreach ($statuses_to_add as $status) {
    $name = mysqli_real_escape_string($conn, $status['status_name']);
    $order = $status['status_order'];
    $is_final = $status['is_final'];
    
    // Check if already exists
    $check = mysqli_query($conn, "SELECT * FROM tbl_status_hierarchy WHERE status_name = '$name'");
    
    if (mysqli_num_rows($check) > 0) {
        echo "<p>✅ Status '<strong>$name</strong>' already exists - skipping</p>";
    } else {
        $insert = "INSERT INTO tbl_status_hierarchy (status_name, status_order, is_final) VALUES ('$name', $order, $is_final)";
        if (mysqli_query($conn, $insert)) {
            echo "<p>✅ Added status '<strong>$name</strong>' (order: $order, final: $is_final)</p>";
        } else {
            echo "<p>❌ Failed to add '$name': " . mysqli_error($conn) . "</p>";
        }
    }
}

echo "<h3>Current Status Hierarchy:</h3>";
echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr><th>Status Name</th><th>Order</th><th>Is Final</th></tr>";

$result = mysqli_query($conn, "SELECT * FROM tbl_status_hierarchy ORDER BY status_order");
while ($row = mysqli_fetch_assoc($result)) {
    $final_label = $row['is_final'] ? 'Yes' : 'No';
    echo "<tr><td>{$row['status_name']}</td><td>{$row['status_order']}</td><td>$final_label</td></tr>";
}
echo "</table>";

echo "<br><p><a href='delivery_status.php'>← Back to Delivery Status</a></p>";
?>
