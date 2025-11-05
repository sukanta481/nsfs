<?php
include("conn.php");
$helper_id = intval($_REQUEST['q'] ?? 0);
$helper_number = '';
if ($helper_id) {
    $sql = "SELECT staff_phone FROM tbl_staff WHERE staff_id='$helper_id' AND staff_role='Helper'";
    $rs = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_array($rs)) {
        $helper_number = $row['staff_phone'];
    }
}
echo $helper_number;
?>
