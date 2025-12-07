<?php
session_name('pro');
session_start();
include_once('conn.php');
require_once 'check_auth.php'; // Required for getOfficeFilter() and access control functions

// Check if user is logged in (check both possible session variables)
if(!isset($_SESSION['aid']) && !isset($_SESSION['admin'])) {
    die("Access denied. Please login first.");
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
$where = [];
$params = [];
$types = '';

// Date filter
if (!empty($fromdate) && !empty($todate)) {
    $where[] = "dd.pickup_datetime BETWEEN ? AND ?";
    $params[] = $fromdate . ' 00:00:00';
    $params[] = $todate . ' 23:59:59';
    $types .= 'ss';
} elseif (!empty($fromdate)) {
    $where[] = "dd.pickup_datetime >= ?";
    $params[] = $fromdate . ' 00:00:00';
    $types .= 's';
} elseif (!empty($todate)) {
    $where[] = "dd.pickup_datetime <= ?";
    $params[] = $todate . ' 23:59:59';
    $types .= 's';
}

// Status filter
if (!empty($status)) {
    $where[] = "dd.status = ?";
    $params[] = $status;
    $types .= 's';
}

// Search filter
if (!empty($searchType) && !empty($searchValue)) {
    if ($searchType == 'doc') {
        $where[] = "dd.doc_no LIKE ?";
        $params[] = "%$searchValue%";
        $types .= 's';
    } elseif ($searchType == 'box') {
        $where[] = "dd.box LIKE ?";
        $params[] = "%$searchValue%";
        $types .= 's';
    }
}

// Consignor filter
if (!empty($consignor)) {
    $where[] = "dd.company_name = ?";
    $params[] = $consignor;
    $types .= 's';
}

// Consignee filter
if (!empty($consignee)) {
    $where[] = "dd.client_name = ?";
    $params[] = $consignee;
    $types .= 's';
}

$whereSQL = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Apply office filter for branch-based access control
$officeFilter = getOfficeFilter('dd');
if (!empty($officeFilter)) {
    if (!empty($whereSQL)) {
        $whereSQL .= $officeFilter;
    } else {
        $whereSQL = "WHERE " . ltrim($officeFilter, ' AND');
    }
}

// Build query
$sql = "SELECT 
            dd.docket_id,
            dd.doc_no,
            dd.box,
            DATE_FORMAT(dd.pickup_datetime, '%d-%m-%Y %h:%i %p') as pickup_datetime,
            dd.company_name,
            dd.company_phone,
            dd.company_email,
            dd.company_address,
            dd.pickup_location,
            dd.client_name,
            dd.client_phone,
            dd.client_email,
            dd.client_address,
            dd.delivery_location,
            dd.weight,
            dd.amount,
            dd.status,
            dd.service_type,
            dd.doc_type,
            dd.item,
            dd.dimensions,
            dd.rate,
            dd.unit_price,
            dd.invoice_no,
            dd.eway_bill,
            o.office_name,
            o.office_address,
            o.office_phone,
            dd.car_number,
            dd.car_model,
            dd.driver_name,
            dd.driver_license,
            dd.driver_phone,
            dd.helper_name,
            dd.helper_phone,
            dd.reason_of_delay,
            dd.delivery_notes,
            dd.special_instructions,
            dd.remarks
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        $whereSQL
        ORDER BY dd.pickup_datetime DESC, dd.docket_id DESC";

$stmt = $conn->prepare($sql);

// Check if prepare was successful
if(!$stmt) {
    die("SQL Error: " . $conn->error);
}

// Bind parameters only if there are any
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

if(!$stmt->execute()) {
    die("Execute Error: " . $stmt->error);
}

$result = $stmt->get_result();

if(!$result) {
    die("Result Error: " . $stmt->error);
}

// NOW set headers for Excel file download (after all possible errors)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="dockets_' . date('Y-m-d_His') . '.xls"');
header('Cache-Control: max-age=0');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Docket Export</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #667eea;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Docket No.</th>
                <th>Box</th>
                <th>Pickup Date & Time</th>
                <th>Company Name</th>
                <th>Company Phone</th>
                <th>Company Email</th>
                <th>Company Address</th>
                <th>Pickup Location</th>
                <th>Client Name</th>
                <th>Client Phone</th>
                <th>Client Email</th>
                <th>Client Address</th>
                <th>Delivery Location</th>
                <th>Weight (kg)</th>
                <th>Amount (₹)</th>
                <th>Status</th>
                <th>Service Type</th>
                <th>Doc Type</th>
                <th>Item</th>
                <th>Dimensions</th>
                <th>Rate</th>
                <th>Unit Price</th>
                <th>Invoice No</th>
                <th>Eway Bill</th>
                <th>Office Name</th>
                <th>Office Address</th>
                <th>Office Phone</th>
                <th>Car Number</th>
                <th>Car Model</th>
                <th>Driver Name</th>
                <th>Driver License</th>
                <th>Driver Phone</th>
                <th>Helper Name</th>
                <th>Helper Phone</th>
                <th>Delay Reason</th>
                <th>Delivery Notes</th>
                <th>Special Instructions</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if($result->num_rows > 0) {
                $sr = 1;
                while($row = $result->fetch_assoc()) {
            ?>
            <tr>
                <td><?= $sr++ ?></td>
                <td><?= htmlspecialchars($row['doc_no']) ?></td>
                <td><?= htmlspecialchars($row['box']) ?></td>
                <td><?= htmlspecialchars($row['pickup_datetime']) ?></td>
                <td><?= htmlspecialchars($row['company_name']) ?></td>
                <td><?= htmlspecialchars($row['company_phone']) ?></td>
                <td><?= htmlspecialchars($row['company_email']) ?></td>
                <td><?= htmlspecialchars($row['company_address']) ?></td>
                <td><?= htmlspecialchars($row['pickup_location']) ?></td>
                <td><?= htmlspecialchars($row['client_name']) ?></td>
                <td><?= htmlspecialchars($row['client_phone']) ?></td>
                <td><?= htmlspecialchars($row['client_email']) ?></td>
                <td><?= htmlspecialchars($row['client_address']) ?></td>
                <td><?= htmlspecialchars($row['delivery_location']) ?></td>
                <td><?= htmlspecialchars($row['weight']) ?></td>
                <td><?= htmlspecialchars($row['amount']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['service_type']) ?></td>
                <td><?= htmlspecialchars($row['doc_type']) ?></td>
                <td><?= htmlspecialchars($row['item']) ?></td>
                <td><?= htmlspecialchars($row['dimensions']) ?></td>
                <td><?= htmlspecialchars($row['rate']) ?></td>
                <td><?= htmlspecialchars($row['unit_price']) ?></td>
                <td><?= htmlspecialchars($row['invoice_no']) ?></td>
                <td><?= htmlspecialchars($row['eway_bill']) ?></td>
                <td><?= htmlspecialchars($row['office_name']) ?></td>
                <td><?= htmlspecialchars($row['office_address']) ?></td>
                <td><?= htmlspecialchars($row['office_phone']) ?></td>
                <td><?= htmlspecialchars($row['car_number']) ?></td>
                <td><?= htmlspecialchars($row['car_model']) ?></td>
                <td><?= htmlspecialchars($row['driver_name']) ?></td>
                <td><?= htmlspecialchars($row['driver_license']) ?></td>
                <td><?= htmlspecialchars($row['driver_phone']) ?></td>
                <td><?= htmlspecialchars($row['helper_name']) ?></td>
                <td><?= htmlspecialchars($row['helper_phone']) ?></td>
                <td><?= htmlspecialchars($row['reason_of_delay']) ?></td>
                <td><?= htmlspecialchars($row['delivery_notes']) ?></td>
                <td><?= htmlspecialchars($row['special_instructions']) ?></td>
                <td><?= htmlspecialchars($row['remarks']) ?></td>
            </tr>
            <?php 
                }
            } else {
            ?>
            <tr>
                <td colspan="39" style="text-align: center;">No records found</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
