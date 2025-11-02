<?php
require 'conn.php';

echo "Checking tbl_company structure...\n\n";
$result = mysqli_query($conn, 'DESCRIBE tbl_company');
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
