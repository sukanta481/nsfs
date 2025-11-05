<?php
include('conn.php');

echo "<h2>Filter Debug Test</h2>";
echo "<hr>";

// Get filter parameters from URL
$fromdate = $_GET['fromdate'] ?? '';
$todate = $_GET['todate'] ?? '';
$status = $_GET['status'] ?? '';

echo "<h3>Filter Parameters:</h3>";
echo "<ul>";
echo "<li>From Date: " . htmlspecialchars($fromdate) . "</li>";
echo "<li>To Date: " . htmlspecialchars($todate) . "</li>";
echo "<li>Status: " . htmlspecialchars($status) . "</li>";
echo "</ul>";

// Build WHERE clause
$where = [];

if (!empty($fromdate) && !empty($todate)) {
    $fromDateTime = mysqli_real_escape_string($conn, $fromdate) . ' 00:00:00';
    $toDateTime = mysqli_real_escape_string($conn, $todate) . ' 23:59:59';
    $where[] = "(dd.pickup_datetime >= '$fromDateTime' AND dd.pickup_datetime <= '$toDateTime')";
}

if (!empty($status)) {
    $where[] = "dd.status='".mysqli_real_escape_string($conn, $status)."'";
}

$whereSQL = (count($where) > 0) ? ("WHERE " . implode(" AND ", $where)) : "";

// Build SQL
$sql = "SELECT dd.docket_id, dd.doc_no, dd.pickup_datetime, dd.company_name, dd.client_name, dd.status
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        $whereSQL
        ORDER BY dd.docket_id DESC";

echo "<h3>SQL Query:</h3>";
echo "<pre>" . htmlspecialchars($sql) . "</pre>";

// Execute query
$result = mysqli_query($conn, $sql);

if(!$result) {
    echo "<h3 style='color: red;'>SQL ERROR:</h3>";
    echo "<pre style='color: red;'>" . mysqli_error($conn) . "</pre>";
} else {
    $totalRecords = mysqli_num_rows($result);
    echo "<h3 style='color: green;'>Query Success! Total Records: $totalRecords</h3>";
    
    if($totalRecords > 0) {
        echo "<table border='1' cellpadding='5'>";
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
    } else {
        echo "<p style='color: orange;'><strong>No records found with the current filters.</strong></p>";
    }
}
?>
