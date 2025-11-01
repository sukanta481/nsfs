<?php
include("conn.php");
$driver_id = intval($_REQUEST['q'] ?? 0);
$driver_number = '';
if ($driver_id) {
    $sql = "SELECT driver_number FROM tbl_driver WHERE driver_id='$driver_id'";
    $rs = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_array($rs)) {
        $driver_number = $row['driver_number'];
    }
}
echo $driver_number;
?>
