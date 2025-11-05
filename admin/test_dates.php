<?php
include('conn.php');

// Test query to see actual dates
$sql = "SELECT docket_id, doc_no, DATE(pickup_datetime) as pdate, pickup_datetime 
        FROM docket_details 
        ORDER BY pickup_datetime DESC 
        LIMIT 10";

$result = mysqli_query($conn, $sql);

echo "<h3>Sample Docket Dates:</h3>";
echo "<table border='1'>";
echo "<tr><th>Docket ID</th><th>Doc No</th><th>Date Only</th><th>Full Datetime</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['docket_id']}</td>";
    echo "<td>{$row['doc_no']}</td>";
    echo "<td>{$row['pdate']}</td>";
    echo "<td>{$row['pickup_datetime']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";

// Test the filter with Nov 3, 2025
$testFrom = '2025-11-03';
$testTo = '2025-11-03';

echo "<h3>Testing filter: $testFrom to $testTo</h3>";

// Current broken query
$sql1 = "SELECT COUNT(*) as cnt FROM docket_details dd 
         WHERE (dd.pickup_datetime BETWEEN '$testFrom' AND '$testTo')";
$r1 = mysqli_query($conn, $sql1);
$row1 = mysqli_fetch_assoc($r1);
echo "<p><strong>Current Query (BROKEN):</strong> $sql1</p>";
echo "<p>Result: {$row1['cnt']} records</p>";

// Fixed query
$sql2 = "SELECT COUNT(*) as cnt FROM docket_details dd 
         WHERE DATE(dd.pickup_datetime) BETWEEN '$testFrom' AND '$testTo'";
$r2 = mysqli_query($conn, $sql2);
$row2 = mysqli_fetch_assoc($r2);
echo "<p><strong>Fixed Query:</strong> $sql2</p>";
echo "<p>Result: {$row2['cnt']} records</p>";

// Even better - with time range
$sql3 = "SELECT COUNT(*) as cnt FROM docket_details dd 
         WHERE dd.pickup_datetime >= '$testFrom 00:00:00' 
         AND dd.pickup_datetime <= '$testTo 23:59:59'";
$r3 = mysqli_query($conn, $sql3);
$row3 = mysqli_fetch_assoc($r3);
echo "<p><strong>Best Query (with time):</strong> $sql3</p>";
echo "<p>Result: {$row3['cnt']} records</p>";
?>
