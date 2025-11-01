<?php
include("conn.php");
$helper_id = intval($_REQUEST['q'] ?? 0);
$helper_number = '';
if ($helper_id) {
    $sql = "SELECT helper_number FROM tbl_helper WHERE helper_id='$helper_id'";
    $rs = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_array($rs)) {
        $helper_number = $row['helper_number'];
    }
}
echo $helper_number;
?>
