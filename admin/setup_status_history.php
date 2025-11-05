<?php
require 'conn.php';

echo "Creating docket_status_history table...\n\n";

$sql = file_get_contents('create_status_history_table.sql');

// Split by semicolon to execute multiple statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success_count = 0;
$error_count = 0;

foreach($statements as $statement) {
    if(empty($statement)) continue;
    
    if(mysqli_query($conn, $statement)) {
        $success_count++;
        echo "✓ Statement executed successfully\n";
    } else {
        $error_count++;
        echo "✗ Error: " . mysqli_error($conn) . "\n";
    }
}

echo "\n";
echo "========================================\n";
echo "Total statements: " . count($statements) . "\n";
echo "Successful: $success_count\n";
echo "Errors: $error_count\n";
echo "========================================\n";

if($error_count == 0) {
    echo "\n✓ docket_status_history table created successfully!\n";
} else {
    echo "\n⚠ Some errors occurred. Please check the output above.\n";
}
?>
