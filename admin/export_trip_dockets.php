<?php
session_name('pro');
session_start();

// Check if user is logged in
if(!isset($_SESSION['aid']) && !isset($_SESSION['admin'])) {
    http_response_code(401);
    die("Unauthorized access");
}

include('conn.php');

if(!isset($conn) || !$conn) {
    die("Database connection failed!");
}

// Get trip ID
$trip_id = trim($_GET['trip_id'] ?? '');

if(empty($trip_id)) {
    die("Invalid trip ID");
}

// Get filter parameters
$fromdate = $_GET['fromdate'] ?? '';
$todate = $_GET['todate'] ?? '';
$status = $_GET['status'] ?? '';
$searchType = $_GET['searchType'] ?? '';
$searchValue = $_GET['searchValue'] ?? '';
$consignor = trim($_GET['consignor'] ?? '');
$consignee = trim($_GET['consignee'] ?? '');

// Build WHERE clause
$where = ["dd.trip_group_id = '" . mysqli_real_escape_string($conn, $trip_id) . "'"];

if (!empty($fromdate) && !empty($todate)) {
    $fromDateTime = mysqli_real_escape_string($conn, $fromdate) . ' 00:00:00';
    $toDateTime = mysqli_real_escape_string($conn, $todate) . ' 23:59:59';
    $where[] = "(dd.pickup_datetime >= '$fromDateTime' AND dd.pickup_datetime <= '$toDateTime')";
} elseif (!empty($fromdate)) {
    $fromDateTime = mysqli_real_escape_string($conn, $fromdate) . ' 00:00:00';
    $where[] = "dd.pickup_datetime >= '$fromDateTime'";
} elseif (!empty($todate)) {
    $toDateTime = mysqli_real_escape_string($conn, $todate) . ' 23:59:59';
    $where[] = "dd.pickup_datetime <= '$toDateTime'";
}

if (!empty($status)) {
    $where[] = "dd.status='".mysqli_real_escape_string($conn, $status)."'";
}

if (!empty($searchType) && !empty($searchValue)) {
    if ($searchType == 'doc') {
        $where[] = "dd.doc_no LIKE '%" . mysqli_real_escape_string($conn, $searchValue) . "%'";
    } elseif ($searchType == 'box') {
        $where[] = "dd.box LIKE '%" . mysqli_real_escape_string($conn, $searchValue) . "%'";
    }
}

if (!empty($consignor)) {
    $where[] = "dd.company_name LIKE '%" . mysqli_real_escape_string($conn, $consignor) . "%'";
}

if (!empty($consignee)) {
    $where[] = "dd.client_name LIKE '%" . mysqli_real_escape_string($conn, $consignee) . "%'";
}

$whereSQL = "WHERE " . implode(" AND ", $where);

// Fetch dockets
$sql = "SELECT 
            dd.doc_no,
            dd.pickup_datetime,
            dd.company_name,
            dd.client_name,
            dd.client_address,
            dd.client_phone,
            dd.status,
            dd.car_number,
            dd.driver_name,
            dd.driver_phone,
            dd.box,
            dd.weight,
            dd.pieces,
            dd.remarks,
            o.office_name as branch_office
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        $whereSQL
        ORDER BY dd.docket_id ASC";

$result = mysqli_query($conn, $sql);

if(!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Set headers for Excel download
$filename = "Trip_" . $trip_id . "_Dockets_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Output Excel content
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
echo '<!--[if gte mso 9]>';
echo '<xml>';
echo '<x:ExcelWorkbook>';
echo '<x:ExcelWorksheets>';
echo '<x:ExcelWorksheet>';
echo '<x:Name>Trip Dockets</x:Name>';
echo '<x:WorksheetOptions>';
echo '<x:Print><x:ValidPrinterInfo/></x:Print>';
echo '</x:WorksheetOptions>';
echo '</x:ExcelWorksheet>';
echo '</x:ExcelWorksheets>';
echo '</x:ExcelWorkbook>';
echo '</xml>';
echo '<![endif]-->';
echo '</head>';
echo '<body>';

echo '<table border="1">';
echo '<thead>';
echo '<tr style="background-color: #34495e; color: #ffffff; font-weight: bold;">';
echo '<th>Sl</th>';
echo '<th>Pickup Date</th>';
echo '<th>Doc No</th>';
echo '<th>Consignor Company</th>';
echo '<th>Consignee Name</th>';
echo '<th>Client Address</th>';
echo '<th>Client Phone</th>';
echo '<th>Status</th>';
echo '<th>Car Number</th>';
echo '<th>Driver Name</th>';
echo '<th>Driver Phone</th>';
echo '<th>Box</th>';
echo '<th>Weight</th>';
echo '<th>Pieces</th>';
echo '<th>Branch Office</th>';
echo '<th>Remarks</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$sl = 1;
while($row = mysqli_fetch_assoc($result)) {
    $pickup_date = 'N/A';
    if(!empty($row['pickup_datetime'])) {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $row['pickup_datetime']);
        if(!$date) $date = DateTime::createFromFormat('Y-m-d', $row['pickup_datetime']);
        if($date) $pickup_date = $date->format('d-m-Y');
    }
    
    echo '<tr>';
    echo '<td>' . $sl . '</td>';
    echo '<td>' . htmlspecialchars($pickup_date) . '</td>';
    echo '<td>' . htmlspecialchars($row['doc_no'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['company_name'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['client_name'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['client_address'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['client_phone'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['status'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['car_number'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['driver_name'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['driver_phone'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['box'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['weight'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['pieces'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['branch_office'] ?? '-') . '</td>';
    echo '<td>' . htmlspecialchars($row['remarks'] ?? '-') . '</td>';
    echo '</tr>';
    
    $sl++;
}

echo '</tbody>';
echo '</table>';
echo '</body>';
echo '</html>';

mysqli_close($conn);
exit;
?>
