<?php
include('conn.php');

$fromdate = '2025-11-03';
$todate = '2025-11-05';

echo "<h2>Testing Exact Filter from URL</h2>";
echo "<p>From: $fromdate</p>";
echo "<p>To: $todate</p>";
echo "<hr>";

$fromDateTime = mysqli_real_escape_string($conn, $fromdate) . ' 00:00:00';
$toDateTime = mysqli_real_escape_string($conn, $todate) . ' 23:59:59';

$sql = "SELECT dd.docket_id, dd.doc_no, dd.pickup_datetime, dd.company_name, dd.client_name, dd.status
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        WHERE (dd.pickup_datetime >= '$fromDateTime' AND dd.pickup_datetime <= '$toDateTime')
        ORDER BY dd.docket_id DESC";

echo "<h3>SQL:</h3>";
echo "<pre>$sql</pre>";

$result = mysqli_query($conn, $sql);

if(!$result) {
    echo "<p style='color:red;'><strong>ERROR:</strong> " . mysqli_error($conn) . "</p>";
} else {
    $count = mysqli_num_rows($result);
    echo "<p style='color:green;'><strong>SUCCESS! Found $count records</strong></p>";
    
    if($count > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Doc No</th><th>Pickup DateTime</th><th>Company</th><th>Client</th><th>Status</th></tr>";
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$row['docket_id']}</td>";
            echo "<td>{$row['doc_no']}</td>";
            echo "<td>{$row['pickup_datetime']}</td>";
            echo "<td>{$row['company_name']}</td>";
            echo "<td>{$row['client_name']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}
?>
