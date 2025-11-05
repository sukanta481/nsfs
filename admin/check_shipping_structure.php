<?php
require 'conn.php';

echo "=== tbl_shipping_details Structure ===\n\n";

$result = mysqli_query($conn, "DESCRIBE tbl_shipping_details");

if ($result) {
    echo str_pad("Field", 30) . str_pad("Type", 20) . str_pad("Null", 8) . str_pad("Key", 8) . "Default\n";
    echo str_repeat("-", 80) . "\n";
    
    while($row = mysqli_fetch_assoc($result)) {
        echo str_pad($row['Field'], 30) . 
             str_pad($row['Type'], 20) . 
             str_pad($row['Null'], 8) . 
             str_pad($row['Key'], 8) . 
             ($row['Default'] ?? 'NULL') . "\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
