<?php
require 'conn.php';

echo "<h2>tbl_shipping_details Table Structure</h2>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#4CAF50;color:white;}</style>";

// Check if table exists
$check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_shipping_details'");
if (mysqli_num_rows($check) == 0) {
    echo "<p style='color:red;'><strong>⚠️ Table 'tbl_shipping_details' does NOT exist!</strong></p>";
    echo "<p>The manifest system was trying to save manual entries to this table, but it doesn't exist.</p>";
    exit;
}

echo "<p style='color:green;'><strong>✓ Table 'tbl_shipping_details' exists</strong></p>";

// Show columns
$columns = mysqli_query($conn, "SHOW COLUMNS FROM tbl_shipping_details");
echo "<h3>Table Columns:</h3>";
echo "<table>";
echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

$found_columns = [];
while($col = mysqli_fetch_assoc($columns)) {
    echo "<tr>";
    echo "<td><strong>{$col['Field']}</strong></td>";
    echo "<td>{$col['Type']}</td>";
    echo "<td>{$col['Null']}</td>";
    echo "<td>{$col['Key']}</td>";
    echo "<td>{$col['Default']}</td>";
    echo "<td>{$col['Extra']}</td>";
    echo "</tr>";
    $found_columns[] = $col['Field'];
}
echo "</table>";

// Check which columns are missing
$required = ['doc_no', 'client_name', 'item', 'client_address', 'box', 'weight', 'rate', 'eway_bill', 'pay_to', 'branch_office', 'delivery_status'];
$missing = array_diff($required, $found_columns);

if (count($missing) > 0) {
    echo "<h3 style='color:red;'>Missing Required Columns:</h3>";
    echo "<ul>";
    foreach ($missing as $col) {
        echo "<li><strong>{$col}</strong></li>";
    }
    echo "</ul>";
} else {
    echo "<h3 style='color:green;'>✓ All required columns present!</h3>";
}

// Show row count
$count_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_shipping_details");
$count = mysqli_fetch_assoc($count_result);
echo "<p><strong>Total Records:</strong> {$count['cnt']}</p>";

// Show sample data
if ($count['cnt'] > 0) {
    echo "<h3>Sample Data (first 5 rows):</h3>";
    $sample = mysqli_query($conn, "SELECT * FROM tbl_shipping_details LIMIT 5");
    echo "<div style='overflow-x:auto;'><table>";
    
    // Headers
    echo "<tr>";
    foreach ($found_columns as $col) {
        echo "<th>{$col}</th>";
    }
    echo "</tr>";
    
    // Data
    while($row = mysqli_fetch_assoc($sample)) {
        echo "<tr>";
        foreach ($found_columns as $col) {
            echo "<td>" . htmlspecialchars($row[$col] ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table></div>";
}
?>
