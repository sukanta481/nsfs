<?php
/**
 * Fix Docket - Set proper "Received at Destination" status with history
 */

require 'conn.php';

$doc_no = 'SP 3456050';
$docket_id = 27;

echo "<h2>Fixing Docket: $doc_no</h2>";
echo "<hr>";

if (isset($_GET['fix']) && $_GET['fix'] == 'yes') {
    $received_at = date('Y-m-d H:i:s');
    $user_name = 'Bardhaman Office';
    $notes = 'Parcel received at Bardhaman office via Manifest #BAR25/000009. Received by: Bardhaman';
    
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Update docket status to proper value
        $update_docket = "UPDATE docket_details 
                         SET status = 'Received at Destination',
                             received_at_destination = '$received_at',
                             received_by_user_id = 8,
                             received_by_name = '$user_name',
                             received_notes = 'Manifest received',
                             last_status_update = '$received_at'
                         WHERE docket_id = $docket_id";
        
        if (!mysqli_query($conn, $update_docket)) {
            throw new Exception('Failed to update docket: ' . mysqli_error($conn));
        }
        
        // 2. Delete the old "Received" history entry
        $delete_old = "DELETE FROM docket_status_history WHERE docket_id = $docket_id AND new_status = 'Received'";
        mysqli_query($conn, $delete_old);
        
        // 3. Add proper "In Transit" history entry (from manifest creation)
        $insert_transit = "INSERT INTO docket_status_history 
                          (docket_id, old_status, new_status, changed_by, changed_at, notes, location)
                          VALUES
                          ($docket_id, 'Pending', 'In Transit', 
                           0, '2025-12-12 11:39:17', 
                           'On the way to Bardhaman office via Manifest #BAR25/000009. Vehicle: WB05A7798', 
                           'Barasat')";
        
        if (!mysqli_query($conn, $insert_transit)) {
            throw new Exception('Failed to insert transit history: ' . mysqli_error($conn));
        }
        
        // 4. Add "Received at Destination" history entry
        $insert_received = "INSERT INTO docket_status_history 
                           (docket_id, old_status, new_status, changed_by, changed_at, notes, location)
                           VALUES
                           ($docket_id, 'In Transit', 'Received at Destination', 
                            8, '$received_at', 
                            '$notes', 
                            'Bardhaman')";
        
        if (!mysqli_query($conn, $insert_received)) {
            throw new Exception('Failed to insert received history: ' . mysqli_error($conn));
        }
        
        mysqli_commit($conn);
        
        echo "<div style='background: #c8e6c9; padding: 15px; border-left: 4px solid #4caf50;'>";
        echo "<h3>✅ SUCCESS!</h3>";
        echo "<p>Docket fixed with proper status and history entries:</p>";
        echo "<ul>";
        echo "<li>Status changed to: <strong>Received at Destination</strong></li>";
        echo "<li>Added 'In Transit' history entry (manifest created)</li>";
        echo "<li>Added 'Received at Destination' history entry</li>";
        echo "<li>Set received tracking fields</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<hr>";
        echo "<p><a href='../deliveryHistory_enhanced.php?doc_no=$doc_no' target='_blank' style='padding: 10px 20px; background: #2196f3; color: white; text-decoration: none; border-radius: 5px;'>View Tracking Timeline</a></p>";
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<div style='background: #ffcdd2; padding: 15px; border-left: 4px solid #f44336;'>";
        echo "<h3>❌ ERROR!</h3>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ff9800;'>";
    echo "<h3>⚠️ Current Issues:</h3>";
    echo "<ul>";
    echo "<li>Status is 'Received' instead of 'Received at Destination'</li>";
    echo "<li>Missing 'In Transit' history entry</li>";
    echo "<li>Missing proper 'Received at Destination' history entry</li>";
    echo "<li>Received tracking fields are NULL</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>What Will Be Fixed:</h3>";
    echo "<ol>";
    echo "<li>Change status to '<strong>Received at Destination</strong>'</li>";
    echo "<li>Add 'In Transit' history entry with manifest info</li>";
    echo "<li>Add 'Received at Destination' history entry</li>";
    echo "<li>Set received_at_destination, received_by_name, received_by_user_id</li>";
    echo "</ol>";
    
    echo "<hr>";
    echo "<p><a href='?fix=yes' style='padding: 15px 30px; background: #4caf50; color: white; text-decoration: none; border-radius: 5px; font-size: 16px;'>✓ FIX NOW</a></p>";
}

mysqli_close($conn);
?>
