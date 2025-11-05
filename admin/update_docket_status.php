<?php
require 'conn.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$docket_id = intval($_POST['docket_id'] ?? 0);
$new_status = trim($_POST['status'] ?? '');

if($docket_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid docket ID']);
    exit;
}

if(empty($new_status)) {
    echo json_encode(['success' => false, 'message' => 'Status is required']);
    exit;
}

// Get current status
$current_query = mysqli_query($conn, "SELECT status FROM docket_details WHERE docket_id = $docket_id");
$current_data = mysqli_fetch_assoc($current_query);

if(!$current_data) {
    echo json_encode(['success' => false, 'message' => 'Docket not found']);
    exit;
}

$old_status = $current_data['status'];

// Update status
$update_sql = "UPDATE docket_details SET 
               status = '" . mysqli_real_escape_string($conn, $new_status) . "',
               updated_at = NOW()
               WHERE docket_id = $docket_id";

if(mysqli_query($conn, $update_sql)) {
    // Insert into status history
    $history_sql = "INSERT INTO docket_status_history 
                    (docket_id, old_status, new_status, changed_by, changed_at, notes) 
                    VALUES 
                    ($docket_id, 
                     '" . mysqli_real_escape_string($conn, $old_status) . "', 
                     '" . mysqli_real_escape_string($conn, $new_status) . "', 
                     'Admin', 
                     NOW(), 
                     'Status updated from admin panel')";
    
    mysqli_query($conn, $history_sql);
    
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status: ' . mysqli_error($conn)]);
}
?>
