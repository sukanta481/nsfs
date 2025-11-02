<?php
require 'conn.php';

echo "=== CHECKING ALL COLUMNS ===\n\n";

// Get actual table structure
echo "1. Getting actual table structure from database...\n";
$result = mysqli_query($conn, "DESCRIBE tbl_shipping_details");
$actual_columns = [];
while($row = mysqli_fetch_assoc($result)) {
    $actual_columns[] = $row['Field'];
}
sort($actual_columns);

echo "Total columns in database: " . count($actual_columns) . "\n\n";

// Columns we're trying to insert
$insert_columns = [
    'trip_group_id',
    'doc_no',
    'doc_type',
    'service_type',
    'branch_office',
    'register_id',
    'shipping_id',
    'company_id',
    'company_email',
    'company_address',
    'client_id',
    'client_name',
    'item',
    'client_phone',
    'client_email',
    'client_address',
    'box',
    'weight',
    'dimensions',
    'rate',
    'amount',
    'unit_price',
    'have_eoa_bill_no',
    'eoa_bill_no',
    'pay_to',
    'pickup_dates',
    'status',
    'reason_of_delay',
    'proof_of_delivery',
    'tracking_link',
    'car_id',
    'car_number',
    'rented_car',
    'car_oil_amount',
    'driver_id',
    'driver_name',
    'driver_number',
    'helper_id',
    'helper_name',
    'helper_number',
    'car_out_time'
];
sort($insert_columns);

echo "2. Columns we're trying to insert: " . count($insert_columns) . "\n\n";

// Check for missing columns (in INSERT but not in DB)
echo "=== COLUMNS IN INSERT BUT NOT IN DATABASE ===\n";
$missing_in_db = array_diff($insert_columns, $actual_columns);
if (empty($missing_in_db)) {
    echo "✓ All INSERT columns exist in database!\n\n";
} else {
    echo "✗ MISSING COLUMNS:\n";
    foreach ($missing_in_db as $col) {
        echo "  - $col\n";
    }
    echo "\n";
}

// Check for extra columns (in DB but not in INSERT)
echo "=== COLUMNS IN DATABASE BUT NOT IN INSERT ===\n";
$not_in_insert = array_diff($actual_columns, $insert_columns);
if (empty($not_in_insert)) {
    echo "All database columns are being used.\n\n";
} else {
    echo "These columns exist but are not being inserted:\n";
    foreach ($not_in_insert as $col) {
        echo "  - $col\n";
    }
    echo "\n";
}

// Show full database structure
echo "=== COMPLETE DATABASE STRUCTURE ===\n";
$result = mysqli_query($conn, "DESCRIBE tbl_shipping_details");
echo str_pad("Column Name", 30) . str_pad("Type", 20) . str_pad("Null", 10) . "Default\n";
echo str_repeat("-", 80) . "\n";
while($row = mysqli_fetch_assoc($result)) {
    echo str_pad($row['Field'], 30) . 
         str_pad($row['Type'], 20) . 
         str_pad($row['Null'], 10) . 
         ($row['Default'] ?? 'NULL') . "\n";
}
?>
