<?php
include("../includes/conn.php");
$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? '';
if ($type == 'driver') {
    $result = mysqli_query($conn, "SELECT driver_number FROM tbl_driver WHERE driver_id='".mysqli_real_escape_string($conn, $id)."'");
    $row = mysqli_fetch_assoc($result);
    echo $row['driver_number'] ?? '';
} elseif ($type == 'helper') {
    $result = mysqli_query($conn, "SELECT helper_number FROM tbl_helper WHERE helper_id='".mysqli_real_escape_string($conn, $id)."'");
    $row = mysqli_fetch_assoc($result);
    echo $row['helper_number'] ?? '';
}
?>
