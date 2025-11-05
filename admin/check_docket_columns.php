<?php
require 'conn.php';

// Check table structure
$result = mysqli_query($conn, 'DESCRIBE docket_details');
echo "docket_details table structure:\n";
echo "Field | Type | Null | Key | Default\n";
echo "-----------------------------------------------------------\n";
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . ($row['Default'] ?? 'NULL') . "\n";
}
?>
