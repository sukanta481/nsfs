<?php
// Session is already started in top.php
require_once 'includes/top.php';

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log all incoming data for debugging
error_log("=== UPDATE STATUS REQUEST ===");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));

// Check if user is logged in (using correct session variable)
if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
    $_SESSION['error_msg'] = 'Please login first!';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_details_id = isset($_POST['shipping_details_id']) ? intval($_POST['shipping_details_id']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    // Debug logging
    error_log("Update Status - ID: $shipping_details_id, Status: $status");
    
    if ($shipping_details_id <= 0) {
        $_SESSION['error_msg'] = 'Invalid docket ID!';
        header('Location: trip.php?type=view_trip&id=' . $shipping_details_id);
        exit;
    }
    
    if (empty($status)) {
        $_SESSION['error_msg'] = 'Status is required!';
        header('Location: trip.php?type=view_trip&id=' . $shipping_details_id);
        exit;
    }
    
    // Update the status in tbl_shipping_details
    $update_query = "UPDATE tbl_shipping_details SET status = ? WHERE shipping_details_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'si', $status, $shipping_details_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $affected_rows = mysqli_stmt_affected_rows($stmt);
            error_log("Update successful - Affected rows: $affected_rows");
            
            // Check if tbl_shipping_history exists
            $check_table = @mysqli_query($conn, "SHOW TABLES LIKE 'tbl_shipping_history'");
            
            if ($check_table && mysqli_num_rows($check_table) > 0) {
                // Insert into history table if it exists
                $history_query = "INSERT INTO tbl_shipping_history (shipping_details_id, status, notes, created_at) 
                                VALUES (?, ?, ?, NOW())";
                $history_stmt = mysqli_prepare($conn, $history_query);
                
                if ($history_stmt) {
                    $notes = "Status updated by " . ($_SESSION['user_name'] ?? 'Admin');
                    mysqli_stmt_bind_param($history_stmt, 'iss', $shipping_details_id, $status, $notes);
                    mysqli_stmt_execute($history_stmt);
                    mysqli_stmt_close($history_stmt);
                }
            }
            
            $_SESSION['success_msg'] = 'Status updated successfully!';
        } else {
            error_log("Update failed: " . mysqli_stmt_error($stmt));
            $_SESSION['error_msg'] = 'Failed to update status: ' . mysqli_stmt_error($stmt);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        error_log("Prepare failed: " . mysqli_error($conn));
        $_SESSION['error_msg'] = 'Database error: ' . mysqli_error($conn);
    }
    
    // Redirect back to view page
    $redirect_url = 'trip.php?type=view_trip&id=' . $shipping_details_id;
    error_log("Redirecting to: $redirect_url");
    header('Location: ' . $redirect_url);
    exit;
} else {
    // Not a POST request - log and redirect with error
    error_log("ERROR: Not a POST request, redirecting to dashboard");
    $_SESSION['error_msg'] = 'Invalid request method!';
    
    // If we have a referrer, try to go back there
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'view_trip') !== false) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: index.php');
    }
    exit;
}
?>
