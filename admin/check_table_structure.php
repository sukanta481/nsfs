<?php
require 'conn.php';

echo "=== tbl_shipping_details TABLE STRUCTURE ===\n";
$result = mysqli_query($conn, "DESCRIBE tbl_shipping_details");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . " | " . $row['Type'] . " | NULL:" . $row['Null'] . " | KEY:" . $row['Key'] . "\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

echo "\n=== tbl_manifest TABLE STRUCTURE ===\n";
$result2 = mysqli_query($conn, "DESCRIBE tbl_manifest");
if ($result2) {
    while ($row = mysqli_fetch_assoc($result2)) {
        echo $row['Field'] . " | " . $row['Type'] . " | NULL:" . $row['Null'] . " | KEY:" . $row['Key'] . "\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

echo "\n=== tbl_manifest_details TABLE STRUCTURE ===\n";
$result3 = mysqli_query($conn, "DESCRIBE tbl_manifest_details");
if ($result3) {
    while ($row = mysqli_fetch_assoc($result3)) {
        echo $row['Field'] . " | " . $row['Type'] . " | NULL:" . $row['Null'] . " | KEY:" . $row['Key'] . "\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
?>
