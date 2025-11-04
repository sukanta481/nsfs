<?php
require 'conn.php';

echo "Current tbl_driver structure:\n\n";
$result = mysqli_query($conn, 'SHOW COLUMNS FROM tbl_driver');
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'].' ('.$row['Type'].")\n";
}
?>
