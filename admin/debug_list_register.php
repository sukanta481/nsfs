<?php
include('conn.php');

echo "<!DOCTYPE html><html><head><title>Debug List Register</title></head><body>";
echo "<h1>Debug List Register</h1>";

// Get filter parameters
$doc_type = $_GET['doc_type'] ?? '';
$status = $_GET['status'] ?? '';
$fromdate = $_GET['fromdate'] ?? '';
$todate = $_GET['todate'] ?? '';
$type = $_GET['type'] ?? '';
$typedata = $_GET['typedata'] ?? '';
$consignor = trim($_GET['consignor'] ?? '');
$consignee = trim($_GET['consignee'] ?? '');

echo "<h3>Filter Parameters:</h3>";
echo "doc_type: $doc_type<br>";
echo "status: $status<br>";
echo "fromdate: $fromdate<br>";
echo "todate: $todate<br>";
echo "type: $type<br>";
echo "typedata: $typedata<br>";
echo "consignor: $consignor<br>";
echo "consignee: $consignee<br>";

// Build WHERE clause
$where = [];
if (!empty($fromdate) && !empty($todate)) {
    $where[] = "(dd.pickup_datetime BETWEEN '".mysqli_real_escape_string($conn, $fromdate)."' AND '".mysqli_real_escape_string($conn, $todate)."')";
}
if (!empty($doc_type)) {
    $where[] = "dd.doc_type='".mysqli_real_escape_string($conn, $doc_type)."'";
}
if (!empty($status)) {
    $where[] = "dd.status='".mysqli_real_escape_string($conn, $status)."'";
}
if (!empty($type) && !empty($typedata)) {
    if ($type == 'doc') {
        $where[] = "dd.doc_no='".mysqli_real_escape_string($conn, $typedata)."'";
    }
    if ($type == 'box') {
        $where[] = "dd.box='".mysqli_real_escape_string($conn, $typedata)."'";
    }
}
if (!empty($consignor)) {
    $where[] = "dd.company_name LIKE '%" . mysqli_real_escape_string($conn, $consignor) . "%'";
}
if (!empty($consignee)) {
    $where[] = "dd.client_name LIKE '%" . mysqli_real_escape_string($conn, $consignee) . "%'";
}
$whereSQL = (count($where) > 0) ? ("WHERE " . implode(" AND ", $where)) : "";

echo "<h3>WHERE clause:</h3>";
echo "<pre>" . htmlspecialchars($whereSQL) . "</pre>";

// Fetch data from docket_details table
$sql = "SELECT dd.*, 
               o.office_name as branch_office_name
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        $whereSQL
        ORDER BY dd.docket_id DESC";

echo "<h3>Full SQL Query:</h3>";
echo "<pre>" . htmlspecialchars($sql) . "</pre>";

$result = mysqli_query($conn, $sql);

if(!$result) {
    echo "<h3 style='color:red;'>SQL Error:</h3>";
    echo "<pre>" . mysqli_error($conn) . "</pre>";
} else {
    $totalRecords = mysqli_num_rows($result);
    echo "<h3 style='color:green;'>Query Successful! Total Records: $totalRecords</h3>";
    
    if($totalRecords > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr>";
        echo "<th>Docket ID</th>";
        echo "<th>Doc No</th>";
        echo "<th>Company Name</th>";
        echo "<th>Client Name</th>";
        echo "<th>Status</th>";
        echo "<th>Pickup Date</th>";
        echo "</tr>";
        
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['docket_id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['doc_no']) . "</td>";
            echo "<td>" . htmlspecialchars($row['company_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['client_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['status'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['pickup_datetime'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
}

echo "</body></html>";
?>
