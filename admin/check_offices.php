<?php
require 'conn.php';

echo "=== OFFICES TABLE STRUCTURE ===\n";
$result = mysqli_query($conn, "DESCRIBE tbl_offices");
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== OFFICES DATA ===\n";
$offices = mysqli_query($conn, "SELECT * FROM tbl_offices");
while($office = mysqli_fetch_assoc($offices)) {
    print_r($office);
    echo "\n";
}
?>
