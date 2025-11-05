<?php
include('conn.php');

echo "<h2>Database Table Investigation</h2>";
echo "<hr>";

// Check if docket_details table exists
echo "<h3>1. Checking if 'docket_details' table exists:</h3>";
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'docket_details'");
if($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    echo "<p style='color: green;'>✅ Table 'docket_details' EXISTS</p>";
} else {
    echo "<p style='color: red;'>❌ Table 'docket_details' DOES NOT EXIST</p>";
}

// Show all tables
echo "<h3>2. All tables in database:</h3>";
$allTables = mysqli_query($conn, "SHOW TABLES");
echo "<ul>";
while($table = mysqli_fetch_array($allTables)) {
    echo "<li>" . $table[0] . "</li>";
}
echo "</ul>";

// If docket_details exists, show its structure
if($tableCheck && mysqli_num_rows($tableCheck) > 0) {
    echo "<h3>3. Structure of 'docket_details' table:</h3>";
    $structure = mysqli_query($conn, "DESCRIBE docket_details");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($col = mysqli_fetch_assoc($structure)) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $col['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count records
    echo "<h3>4. Record count in 'docket_details':</h3>";
    $count = mysqli_query($conn, "SELECT COUNT(*) as total FROM docket_details");
    $countRow = mysqli_fetch_assoc($count);
    echo "<p><strong>Total records:</strong> " . $countRow['total'] . "</p>";
    
    // Show sample data
    if($countRow['total'] > 0) {
        echo "<h3>5. Sample data (first 5 records):</h3>";
        $sample = mysqli_query($conn, "SELECT docket_id, doc_no, pickup_datetime, company_name, client_name, status FROM docket_details ORDER BY docket_id DESC LIMIT 5");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Docket ID</th><th>Doc No</th><th>Pickup DateTime</th><th>Company</th><th>Client</th><th>Status</th></tr>";
        while($row = mysqli_fetch_assoc($sample)) {
            echo "<tr>";
            echo "<td>" . $row['docket_id'] . "</td>";
            echo "<td>" . $row['doc_no'] . "</td>";
            echo "<td>" . $row['pickup_datetime'] . "</td>";
            echo "<td>" . $row['company_name'] . "</td>";
            echo "<td>" . $row['client_name'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Check for similar named tables
echo "<h3>6. Looking for similar table names (docket, register, etc.):</h3>";
$similarTables = mysqli_query($conn, "SHOW TABLES");
echo "<ul>";
while($table = mysqli_fetch_array($similarTables)) {
    $tableName = $table[0];
    if(stripos($tableName, 'docket') !== false || stripos($tableName, 'register') !== false) {
        $countQ = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM `$tableName`");
        $countR = mysqli_fetch_assoc($countQ);
        echo "<li><strong>$tableName</strong> - " . $countR['cnt'] . " records</li>";
    }
}
echo "</ul>";
?>
