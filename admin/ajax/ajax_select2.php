<?php
include("../includes/conn.php");
$type = $_GET['type'] ?? '';
$q = $_GET['q'] ?? '';
$data = [];

switch($type) {
    case 'car':
        $sql = "SELECT car_id AS id, car_number AS text FROM tbl_car WHERE car_number LIKE '%$q%' ORDER BY car_number ASC LIMIT 20";
        break;
    case 'driver':
        $sql = "SELECT driver_id AS id, driver_name AS text FROM tbl_driver WHERE driver_name LIKE '%$q%' ORDER BY driver_name ASC LIMIT 20";
        break;
    case 'helper':
        $sql = "SELECT helper_id AS id, helper_name AS text FROM tbl_helper WHERE helper_name LIKE '%$q%' ORDER BY helper_name ASC LIMIT 20";
        break;
    case 'company':
        $sql = "SELECT company_id AS id, company_title AS text FROM tbl_company WHERE company_title LIKE '%$q%' ORDER BY company_title ASC LIMIT 20";
        break;
    default:
        $sql = "";
}
if($sql) {
    $rs = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($rs)) $data[] = $row;
}
echo json_encode(['items'=>$data]);
?>
