<?php
/**
 * Quick check - See if status history exists for received docket
 */

require 'conn.php';

$doc_no = 'SP 3456050';

echo "<h2>Checking Status History for: $doc_no</h2>";
echo "<hr>";

// Get docket info
$docket_query = "SELECT docket_id, status, received_at_destination, received_by_name, received_notes 
                 FROM docket_details 
                 WHERE doc_no = '$doc_no'";
$docket_result = mysqli_query($conn, $docket_query);
$docket = mysqli_fetch_assoc($docket_result);

echo "<h3>Current Docket Status:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Value</th></tr>";
echo "<tr><td>Docket ID</td><td>" . $docket['docket_id'] . "</td></tr>";
echo "<tr><td>Status</td><td><strong>" . $docket['status'] . "</strong></td></tr>";
echo "<tr><td>Received At</td><td>" . ($docket['received_at_destination'] ?? 'NULL') . "</td></tr>";
echo "<tr><td>Received By</td><td>" . ($docket['received_by_name'] ?? 'NULL') . "</td></tr>";
echo "<tr><td>Notes</td><td>" . ($docket['received_notes'] ?? 'NULL') . "</td></tr>";
echo "</table>";

echo "<hr>";
echo "<h3>Status History Entries:</h3>";

$history_query = "SELECT * FROM docket_status_history 
                  WHERE docket_id = " . $docket['docket_id'] . " 
                  ORDER BY changed_at ASC";
$history_result = mysqli_query($conn, $history_query);

if (mysqli_num_rows($history_result) == 0) {
    echo "<p style='color: red;'><strong>NO STATUS HISTORY ENTRIES FOUND!</strong></p>";
    echo "<p>This is why it's not showing in the timeline.</p>";
    
    echo "<hr>";
    echo "<h3>Create Missing History Entry?</h3>";
    
    if (isset($_GET['create']) && $_GET['create'] == 'yes') {
        // Create the history entry
        $insert_history = "INSERT INTO docket_status_history 
                          (docket_id, old_status, new_status, changed_by, changed_at, notes, location)
                          VALUES
                          ({$docket['docket_id']}, 'In Transit', 'Received at Destination', 
                           0, '{$docket['received_at_destination']}', 
                           'Parcel received at destination office. Received by: {$docket['received_by_name']}', 
                           'bardhaman')";
        
        if (mysqli_query($conn, $insert_history)) {
            echo "<p style='color: green;'><strong>✅ History entry created!</strong></p>";
            echo "<p><a href='?'>Refresh to see result</a></p>";
        } else {
            echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p><a href='?create=yes' style='padding: 10px 20px; background: #4caf50; color: white; text-decoration: none; border-radius: 5px;'>Create History Entry Now</a></p>";
    }
    
} else {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Old Status</th><th>New Status</th><th>Changed At</th><th>Notes</th><th>Location</th></tr>";
    
    while ($row = mysqli_fetch_assoc($history_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['old_status'] . "</td>";
        echo "<td><strong>" . $row['new_status'] . "</strong></td>";
        echo "<td>" . $row['changed_at'] . "</td>";
        echo "<td>" . htmlspecialchars($row['notes']) . "</td>";
        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($conn);
?>
