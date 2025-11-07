<?php
/**
 * API for Tracking Updates
 * Handles AJAX requests for updating shipment tracking status
 */

header('Content-Type: application/json');

require 'check_auth.php';
require 'conn.php';

// Check if user has permission
if (!checkPermission('tracking_management') && !checkPermission('docket_status_update')) {
    echo json_encode([
        'success' => false,
        'message' => 'You do not have permission to update tracking status'
    ]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get POST data
$doc_no = isset($_POST['doc_no']) ? mysqli_real_escape_string($conn, trim($_POST['doc_no'])) : '';
$status = isset($_POST['status']) ? mysqli_real_escape_string($conn, trim($_POST['status'])) : '';
$notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, trim($_POST['notes'])) : '';
$location = isset($_POST['location']) ? mysqli_real_escape_string($conn, trim($_POST['location'])) : '';
$source = isset($_POST['source']) ? mysqli_real_escape_string($conn, trim($_POST['source'])) : '';
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Validate required fields
if (empty($doc_no)) {
    echo json_encode([
        'success' => false,
        'message' => 'Document number is required'
    ]);
    exit;
}

if (empty($status)) {
    echo json_encode([
        'success' => false,
        'message' => 'Status is required'
    ]);
    exit;
}

// Get user information
$updated_by = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
$updated_by_name = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'Admin';

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Get docket_id from docket_details table
    $docket_id = null;
    
    if ($source === 'docket' && $id > 0) {
        $docket_id = $id;
    } else {
        // Try to find in docket_details table
        $query = "SELECT docket_id FROM docket_details WHERE doc_no = '$doc_no' LIMIT 1";
        $result = mysqli_query($conn, $query);
        if ($row = mysqli_fetch_assoc($result)) {
            $docket_id = $row['docket_id'];
        }
    }
    
    if (!$docket_id) {
        throw new Exception('Shipment not found with document number: ' . $doc_no);
    }
    
    // Insert into tracking history (if table exists)
    $tracking_query = "INSERT INTO tbl_tracking_history 
        (doc_no, docket_id, shipping_details_id, status, notes, location, updated_by, updated_by_name, created_at) 
        VALUES ('$doc_no', $docket_id, NULL, '$status', '$notes', '$location', $updated_by, '$updated_by_name', NOW())";
    
    @mysqli_query($conn, $tracking_query); // Don't fail if tracking table doesn't exist
    
    // Update docket_details table
    $update_query = "UPDATE docket_details SET 
        status = '$status'";
    
    // Only update delivery_datetime if delivered
    if ($status === 'Delivered') {
        $update_query .= ", delivery_datetime = NOW()";
    }
    
    $update_query .= " WHERE docket_id = $docket_id";
    
    if (!mysqli_query($conn, $update_query)) {
        throw new Exception('Failed to update docket_details: ' . mysqli_error($conn));
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Tracking status updated successfully',
        'data' => [
            'doc_no' => $doc_no,
            'status' => $status,
            'updated_by' => $updated_by_name,
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
    // Optional: Send notification (email/SMS)
    // sendTrackingNotification($doc_no, $status, $notes);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close connection
mysqli_close($conn);

/**
 * Helper function to check permissions
 */
function checkPermission($permission) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        return true; // Admin has all permissions
    }
    
    // Check user permissions
    if (isset($_SESSION['permissions']) && is_array($_SESSION['permissions'])) {
        return in_array($permission, $_SESSION['permissions']);
    }
    
    return false;
}

/**
 * Optional: Send tracking notification
 */
function sendTrackingNotification($doc_no, $status, $notes) {
    // Implement email/SMS notification logic here
    // This is a placeholder for future implementation
    
    // Example:
    // - Get customer email/phone from database
    // - Format notification message
    // - Send via email/SMS API
    // - Log notification in tbl_tracking_notifications
}
?>
