<?php
require 'conn.php';

echo "<h2>tbl_offices Table Structure</h2>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#4CAF50;color:white;}</style>";

// Show columns
$columns = mysqli_query($conn, "SHOW COLUMNS FROM tbl_offices");
echo "<h3>Current Table Columns:</h3>";
echo "<table>";
echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";

while($col = mysqli_fetch_assoc($columns)) {
    echo "<tr>";
    echo "<td><strong>{$col['Field']}</strong></td>";
    echo "<td>{$col['Type']}</td>";
    echo "<td>{$col['Null']}</td>";
    echo "<td>{$col['Key']}</td>";
    echo "<td>{$col['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

// Show sample data
echo "<h3>Sample Office Data:</h3>";
$sample = mysqli_query($conn, "SELECT * FROM tbl_offices LIMIT 3");
echo "<table>";

// Get column names
$fields = [];
$columns2 = mysqli_query($conn, "SHOW COLUMNS FROM tbl_offices");
while($col = mysqli_fetch_assoc($columns2)) {
    $fields[] = $col['Field'];
}

echo "<tr>";
foreach($fields as $field) {
    echo "<th>{$field}</th>";
}
echo "</tr>";

while($row = mysqli_fetch_assoc($sample)) {
    echo "<tr>";
    foreach($fields as $field) {
        echo "<td>" . htmlspecialchars($row[$field] ?? '') . "</td>";
    }
    echo "</tr>";
}
echo "</table>";
?>
