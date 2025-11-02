<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conn.php';

echo "Testing UPDATED INSERT query...\n\n";

$trip_group_id = 'TRIP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
echo "Generated Trip Group ID: $trip_group_id\n\n";

$query = "INSERT INTO tbl_shipping_details (
    trip_group_id, doc_no, doc_type, branch_office, register_id, shipping_id, 
    company_id, company_email, company_address, client_id, client_name, item,
    client_phone, client_email, client_address, box, weight, dimensions,
    rate, amount, unit_price, have_eoa_bill_no, eoa_bill_no, pay_to,
    pickup_dates, status, 
    reason_of_delay, proof_of_delivery, tracking_link, car_id, 
    car_number, rented_car, car_oil_amount, driver_id, driver_name, driver_number, 
    helper_id, helper_name, helper_number, car_out_time
) VALUES (
    '$trip_group_id', 'TEST001', '', '', 0, 0, 
    1, 'test@test.com', '123 Company Street, Test City', 0, 'Test Client', NULL,
    '1234567890', 'test@test.com', '456 Client Avenue, Test City', 5, 10, '10x20x30',
    NULL, NULL, 0, 0, 0, 0,
    NOW(), 'Picked Up', 
    '', '', '', 1, 
    '', 0, 0, 1, '', '', 
    NULL, '', '', NULL
)";

$result = mysqli_query($conn, $query);

if($result) {
    echo "✓ SUCCESS! Insert worked.\n";
    $insert_id = mysqli_insert_id($conn);
    echo "Inserted shipping_details_id: $insert_id\n";
    
    // Show the inserted data
    $check = mysqli_query($conn, "SELECT * FROM tbl_shipping_details WHERE shipping_details_id = $insert_id");
    $data = mysqli_fetch_assoc($check);
    echo "\nInserted data:\n";
    echo "- Trip Group ID: " . $data['trip_group_id'] . "\n";
    echo "- Doc No: " . $data['doc_no'] . "\n";
    echo "- Company Address: " . $data['company_address'] . "\n";
    echo "- Client Address: " . $data['client_address'] . "\n";
    echo "- Box: " . $data['box'] . "\n";
    echo "- Dimensions: " . $data['dimensions'] . "\n";
    echo "- Pickup Date: " . $data['pickup_dates'] . "\n";
    
    // Clean up
    mysqli_query($conn, "DELETE FROM tbl_shipping_details WHERE shipping_details_id = $insert_id");
    echo "\n✓ Test data cleaned up.\n";
} else {
    echo "✗ ERROR: " . mysqli_error($conn) . "\n";
}
?>
