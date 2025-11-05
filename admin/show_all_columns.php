<?php
include('conn.php');

echo "All columns in docket_details table:\n\n";
$r = mysqli_query($conn, 'DESCRIBE docket_details');
while($row = mysqli_fetch_assoc($r)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
