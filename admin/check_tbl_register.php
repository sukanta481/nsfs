<?php
include('conn.php');

echo "<h2>Checking tbl_register table</h2>";
echo "<hr>";

// Check structure
echo "<h3>Structure of tbl_register:</h3>";
$structure = mysqli_query($conn, "DESCRIBE tbl_register");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th></tr>";
while($col = mysqli_fetch_assoc($structure)) {
    echo "<tr><td><strong>" . $col['Field'] . "</strong></td><td>" . $col['Type'] . "</td></tr>";
}
echo "</table>";

// Sample data
echo "<h3>Sample data from tbl_register (last 5 records):</h3>";
$sample = mysqli_query($conn, "SELECT id, doc_no, pickup_dates, consignor_company, consignee_name, status FROM tbl_register ORDER BY id DESC LIMIT 5");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Doc No</th><th>Pickup Dates</th><th>Consignor</th><th>Consignee</th><th>Status</th></tr>";
while($row = mysqli_fetch_assoc($sample)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['doc_no'] . "</td>";
    echo "<td>" . $row['pickup_dates'] . "</td>";
    echo "<td>" . $row['consignor_company'] . "</td>";
    echo "<td>" . $row['consignee_name'] . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
