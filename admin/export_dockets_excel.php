<?php
session_start();
include('conn.php');

// Check if user is logged in
if(!isset($_SESSION['aid'])) {
    header("Location: index.php");
    exit();
}

// Set headers for Excel file download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="docket_list_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

// Build WHERE clause based on filters
$where = "WHERE 1=1";
$params = [];

if(isset($_GET['fromdate']) && !empty($_GET['fromdate'])) {
    $where .= " AND dd.pickup_datetime >= ?";
    $params[] = $_GET['fromdate'] . ' 00:00:00';
}

if(isset($_GET['todate']) && !empty($_GET['todate'])) {
    $where .= " AND dd.pickup_datetime <= ?";
    $params[] = $_GET['todate'] . ' 23:59:59';
}

if(isset($_GET['status']) && $_GET['status'] != '') {
    $where .= " AND dd.status = ?";
    $params[] = $_GET['status'];
}

if(isset($_GET['type']) && $_GET['type'] != '' && isset($_GET['typedata']) && $_GET['typedata'] != '') {
    $type = $_GET['type'];
    $typedata = $_GET['typedata'];
    
    if($type == 'doc') {
        $where .= " AND dd.doc_no LIKE ?";
        $params[] = "%$typedata%";
    } elseif($type == 'box') {
        $where .= " AND dd.box_no LIKE ?";
        $params[] = "%$typedata%";
    }
}

if(isset($_GET['consignor']) && !empty($_GET['consignor'])) {
    $where .= " AND dd.company_name LIKE ?";
    $params[] = "%{$_GET['consignor']}%";
}

if(isset($_GET['consignee']) && !empty($_GET['consignee'])) {
    $where .= " AND dd.client_name LIKE ?";
    $params[] = "%{$_GET['consignee']}%";
}

// Fetch dockets with filters
$sql = "SELECT 
            dd.docket_id,
            dd.doc_no,
            dd.box_no,
            DATE_FORMAT(dd.pickup_datetime, '%d-%m-%Y %h:%i %p') as pickup_datetime,
            dd.company_name,
            dd.client_name,
            dd.client_address,
            dd.client_city,
            dd.client_state,
            dd.client_pincode,
            dd.client_phone,
            dd.client_email,
            dd.receiver_name,
            dd.receiver_address,
            dd.receiver_city,
            dd.receiver_state,
            dd.receiver_pincode,
            dd.receiver_phone,
            dd.weight,
            dd.amount,
            dd.status,
            dd.service_type,
            dd.payment_mode,
            dd.description,
            o.office_name,
            o.office_address,
            o.office_phone,
            dd.car_number,
            dd.driver_name,
            dd.driver_license,
            dd.driver_phone,
            dd.helper_name,
            dd.helper_phone
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.from_office_id = o.office_id
        $where
        ORDER BY dd.pickup_datetime DESC";

$stmt = $conn->prepare($sql);

if(!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Docket List Export</title>
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
            background-color: #4CAF50;
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
                <th>Box No.</th>
                <th>Pickup Date & Time</th>
                <th>Consignor Company</th>
                <th>Consignee Name</th>
                <th>Consignee Address</th>
                <th>Consignee City</th>
                <th>Consignee State</th>
                <th>Consignee Pincode</th>
                <th>Consignee Phone</th>
                <th>Consignee Email</th>
                <th>Receiver Name</th>
                <th>Receiver Address</th>
                <th>Receiver City</th>
                <th>Receiver State</th>
                <th>Receiver Pincode</th>
                <th>Receiver Phone</th>
                <th>Weight (kg)</th>
                <th>Amount (₹)</th>
                <th>Status</th>
                <th>Service Type</th>
                <th>Payment Mode</th>
                <th>Description</th>
                <th>Office Name</th>
                <th>Office Address</th>
                <th>Office Phone</th>
                <th>Car Number</th>
                <th>Driver Name</th>
                <th>Driver License</th>
                <th>Driver Phone</th>
                <th>Helper Name</th>
                <th>Helper Phone</th>
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
                <td><?= htmlspecialchars($row['box_no']) ?></td>
                <td><?= htmlspecialchars($row['pickup_datetime']) ?></td>
                <td><?= htmlspecialchars($row['company_name']) ?></td>
                <td><?= htmlspecialchars($row['client_name']) ?></td>
                <td><?= htmlspecialchars($row['client_address']) ?></td>
                <td><?= htmlspecialchars($row['client_city']) ?></td>
                <td><?= htmlspecialchars($row['client_state']) ?></td>
                <td><?= htmlspecialchars($row['client_pincode']) ?></td>
                <td><?= htmlspecialchars($row['client_phone']) ?></td>
                <td><?= htmlspecialchars($row['client_email']) ?></td>
                <td><?= htmlspecialchars($row['receiver_name']) ?></td>
                <td><?= htmlspecialchars($row['receiver_address']) ?></td>
                <td><?= htmlspecialchars($row['receiver_city']) ?></td>
                <td><?= htmlspecialchars($row['receiver_state']) ?></td>
                <td><?= htmlspecialchars($row['receiver_pincode']) ?></td>
                <td><?= htmlspecialchars($row['receiver_phone']) ?></td>
                <td><?= htmlspecialchars($row['weight']) ?></td>
                <td><?= htmlspecialchars($row['amount']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['service_type']) ?></td>
                <td><?= htmlspecialchars($row['payment_mode']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['office_name']) ?></td>
                <td><?= htmlspecialchars($row['office_address']) ?></td>
                <td><?= htmlspecialchars($row['office_phone']) ?></td>
                <td><?= htmlspecialchars($row['car_number']) ?></td>
                <td><?= htmlspecialchars($row['driver_name']) ?></td>
                <td><?= htmlspecialchars($row['driver_license']) ?></td>
                <td><?= htmlspecialchars($row['driver_phone']) ?></td>
                <td><?= htmlspecialchars($row['helper_name']) ?></td>
                <td><?= htmlspecialchars($row['helper_phone']) ?></td>
            </tr>
            <?php 
                }
            } else {
            ?>
            <tr>
                <td colspan="33" style="text-align: center;">No records found</td>
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
