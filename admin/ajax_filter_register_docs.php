<?php
include 'conn.php';

$doc_no    = $_POST['doc_no'] ?? '';
$drs_type  = $_POST['drs_type'] ?? '';
$status    = $_POST['status'] ?? '';
$consignor = $_POST['consignor'] ?? '';
$consignee = $_POST['consignee'] ?? '';
$from_date = $_POST['from_date'] ?? '';
$to_date   = $_POST['to_date'] ?? '';

$where = [];
if ($doc_no)    $where[] = "doc_no LIKE '%" . mysqli_real_escape_string($conn, $doc_no) . "%'";
if ($drs_type)  $where[] = "doc_type='" . mysqli_real_escape_string($conn, $drs_type) . "'";
if ($status)    $where[] = "status='" . mysqli_real_escape_string($conn, $status) . "'";
if ($consignor) $where[] = "company_id IN (SELECT company_id FROM tbl_company WHERE company_title LIKE '%" . mysqli_real_escape_string($conn, $consignor) . "%')";
if ($consignee) $where[] = "client_name LIKE '%" . mysqli_real_escape_string($conn, $consignee) . "%'";
if ($from_date && $to_date) $where[] = "(DATE(pickup_dates) BETWEEN '$from_date' AND '$to_date')";

$whereSql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT * FROM tbl_shipping_details $whereSql ORDER BY shipping_details_id DESC";
$res = mysqli_query($conn, $sql);

$rowCount = 1;
while ($row = mysqli_fetch_assoc($res)) {
    $compRs = mysqli_query($conn, "SELECT company_title FROM tbl_company WHERE company_id='" . $row['company_id'] . "'");
    $comp = mysqli_fetch_assoc($compRs);
    $consignor_name = $comp ? $comp['company_title'] : '-';
    echo "<tr>
        <td>{$rowCount}</td>
        <td>" . htmlspecialchars($row['pickup_dates']) . "</td>
        <td>" . htmlspecialchars($row['doc_no']) . "</td>
        <td>" . htmlspecialchars($row['doc_type']) . "</td>
        <td>" . htmlspecialchars($consignor_name) . "</td>
        <td>" . htmlspecialchars($row['client_name']) . "</td>
        <td>" . htmlspecialchars($row['status']) . "</td>
        <td>
            <a class='btn btn-info btn-xs' href='edit_register.php?shipping_details_id={$row['shipping_details_id']}&" . session_name() . "=" . session_id() . "'>Edit</a>
            <a class='btn btn-danger btn-xs' href='javascript:void(0);' onclick='delconfirmregister({$row['shipping_details_id']});'>Delete</a>
        </td>
    </tr>";
    $rowCount++;
}
if ($rowCount === 1) {
    echo "<tr><td colspan='8'>No records found.</td></tr>";
}
?>
