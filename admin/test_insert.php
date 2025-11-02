<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conn.php';

echo "Testing INSERT query...\n";

$query = "INSERT INTO tbl_shipping_details (
    doc_no, doc_type, branch_office, register_id, shipping_id, 
    company_id, company_email, client_id, client_name, item,
    client_phone, client_email, client_address, box, weight, 
    rate, amount, unit_price, have_eoa_bill_no, eoa_bill_no, pay_to,
    pickup_dates, status, 
    reason_of_delay, proof_of_delivery, tracking_link, car_id, 
    car_number, rented_car, car_oil_amount, driver_id, driver_name, driver_number, 
    helper_id, helper_name, helper_number, car_out_time
) VALUES (
    'TEST001', '', '', 0, 0, 
    1, 'test@test.com', 0, 'Test Client', 'Pickup: Test Location | Delivery: Test Delivery',
    '1234567890', 'test@test.com', 'Delivery Address', 1, 10, 
    0, 0, 0, 0, 0, 0,
    '2024-11-02 10:00:00', 'Picked Up', 
    '', '', '', 1, 
    '', 0, 0, 1, '', '', 
    NULL, '', '', '10:00:00'
)";

$result = mysqli_query($conn, $query);

if($result) {
    echo "SUCCESS! Insert worked.\n";
    // Clean up
    mysqli_query($conn, "DELETE FROM tbl_shipping_details WHERE doc_no='TEST001'");
    echo "Test data cleaned up.\n";
} else {
    echo "ERROR: " . mysqli_error($conn) . "\n";
    echo "\nQuery was:\n" . $query . "\n";
}

// Also show table structure
echo "\n\nTable structure:\n";
$columns = mysqli_query($conn, "DESCRIBE tbl_shipping_details");
while($col = mysqli_fetch_assoc($columns)) {
    echo $col['Field'] . " - Type: " . $col['Type'] . " - Null: " . $col['Null'] . " - Default: " . $col['Default'] . "\n";
}
?>
