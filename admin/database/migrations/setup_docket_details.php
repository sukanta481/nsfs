<?php
/**
 * Setup Docket Details Table
 * Run this file once to create the table
 */

require 'conn.php';

echo "<h2>Setting up Docket Details Table...</h2>";

$sql = file_get_contents('create_docket_details_table.sql');

if (!$sql) {
    die("Error: Could not read SQL file!");
}

// Execute multi-query
if (mysqli_multi_query($conn, $sql)) {
    echo "<p style='color: green;'>✓ Table creation queries executed successfully!</p>";
    
    // Clear result sets
    while(mysqli_more_results($conn)) {
        mysqli_next_result($conn);
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    }
    
    // Verify table exists
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'docket_details'");
    if ($check && mysqli_num_rows($check) > 0) {
        echo "<p style='color: green;'><strong>✓ Table 'docket_details' created successfully!</strong></p>";
        
        // Show table structure
        echo "<h3>Table Structure:</h3>";
        $structure = mysqli_query($conn, "DESCRIBE docket_details");
        if ($structure) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            while ($row = mysqli_fetch_assoc($structure)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Test insert
        echo "<h3>Testing Table...</h3>";
        $test_insert = "INSERT INTO docket_details (doc_no) VALUES ('TEST-" . time() . "')";
        if (mysqli_query($conn, $test_insert)) {
            $test_id = mysqli_insert_id($conn);
            echo "<p style='color: green;'>✓ Test insert successful! Docket ID: $test_id</p>";
            
            // Delete test record
            mysqli_query($conn, "DELETE FROM docket_details WHERE docket_id = $test_id");
            echo "<p>✓ Test record cleaned up.</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Test insert failed: " . mysqli_error($conn) . "</p>";
        }
        
        echo "<hr>";
        echo "<h3>✅ Setup Complete!</h3>";
        echo "<p>You can now use the DocketDetailsManager class to manage dockets.</p>";
        echo "<p><a href='add_trip_modern.php'>Go to Add Trip</a></p>";
        
    } else {
        echo "<p style='color: red;'>✗ Error: Table was not created!</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Error executing SQL: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);
?>
