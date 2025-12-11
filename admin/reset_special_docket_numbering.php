<?php
// Reset special docket numbering to start from SP 3456050
require 'conn.php';

echo "=== Resetting Special Docket Numbering ===\n\n";

// Insert a dummy docket at SP 3456049 so next will be SP 3456050
$dummy_doc_no = 'SP 3456049';
$created_at = date('Y-m-d H:i:s');

// Check if it already exists
$check = mysqli_query($conn, "SELECT doc_no FROM docket_details WHERE doc_no = '$dummy_doc_no'");
if (mysqli_num_rows($check) > 0) {
    echo "✅ Dummy docket SP 3456049 already exists.\n";
    echo "Next special docket will be: SP 3456050\n";
} else {
    // Insert dummy record
    $sql = "INSERT INTO docket_details (
        doc_no, doc_type, status, created_at, pickup_datetime,
        company_name, client_name, service_type,
        client_address, item, box, weight, rate, amount
    ) VALUES (
        '$dummy_doc_no', 'SPECIAL', 'Pending', '$created_at', '$created_at',
        'SYSTEM', 'SYSTEM - Number Reset Placeholder', 'Special Docket',
        'N/A', 'Number sequence reset placeholder', 0, 0, 0, 0
    )";
    
    if (mysqli_query($conn, $sql)) {
        echo "✅ SUCCESS! Inserted dummy docket at SP 3456049\n";
        echo "Next special docket will now be: SP 3456050\n\n";
        echo "Note: You can delete the dummy docket (SP 3456049) from database if needed.\n";
        echo "It's marked as 'SYSTEM - Number Reset Placeholder' for easy identification.\n";
    } else {
        echo "❌ ERROR: " . mysqli_error($conn) . "\n";
    }
}

mysqli_close($conn);
?>
