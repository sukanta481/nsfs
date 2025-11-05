<?php
require 'conn.php';

echo "=== DRIVER TABLE COLUMNS ===\n";
$driver_cols = mysqli_query($conn, "DESCRIBE tbl_driver");
while($row = mysqli_fetch_assoc($driver_cols)) {
    echo $row['Field'] . "\n";
}

echo "\n=== HELPER TABLE COLUMNS ===\n";
$helper_cols = mysqli_query($conn, "DESCRIBE tbl_helper");
while($row = mysqli_fetch_assoc($helper_cols)) {
    echo $row['Field'] . "\n";
}

echo "\n=== SAMPLE DRIVER DATA ===\n";
$driver = mysqli_query($conn, "SELECT * FROM tbl_driver LIMIT 1");
if ($d = mysqli_fetch_assoc($driver)) {
    print_r($d);
}

echo "\n=== SAMPLE HELPER DATA ===\n";
$helper = mysqli_query($conn, "SELECT * FROM tbl_helper LIMIT 1");
if ($h = mysqli_fetch_assoc($helper)) {
    print_r($h);
}
?>
