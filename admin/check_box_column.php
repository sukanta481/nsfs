<?php
include('conn.php');

echo "Checking docket_details columns with 'box' in name:\n";
$r = mysqli_query($conn, 'DESCRIBE docket_details');
while($row = mysqli_fetch_assoc($r)) {
    if(stripos($row['Field'], 'box') !== false) {
        echo "Found: " . $row['Field'] . " (Type: " . $row['Type'] . ")\n";
    }
}
?>
