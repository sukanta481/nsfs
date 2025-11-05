<?php
include("conn.php");
$driver_id = intval($_REQUEST['q'] ?? 0);
$driver_number = '';
if ($driver_id) {
    $sql = "SELECT staff_phone FROM tbl_staff WHERE staff_id='$driver_id' AND staff_role='Driver'";
    $rs = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_array($rs)) {
        $driver_number = $row['staff_phone'];
    }
}
echo $driver_number;
?>
