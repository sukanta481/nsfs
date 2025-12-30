<?php
/**
 * API Endpoint: Barcode Lookup
 * Fetch docket details by barcode/doc_no for label printing
 */

// Error handling - catch all errors and return as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    header('Content-Type: application/json');
    
    // Check if session exists
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    require 'conn.php';
    
    // Check authentication
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Session expired. Please login again.',
            'error_code' => 'AUTH_REQUIRED'
        ]);
        exit;
    }
    
    // Check database connection
    if (!$conn || $conn->connect_error) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed',
            'error_code' => 'DB_ERROR'
        ]);
        exit;
    }

$barcode = isset($_GET['barcode']) ? trim($_GET['barcode']) : '';

if (empty($barcode)) {
    echo json_encode([
        'success' => false,
        'message' => 'Barcode is required'
    ]);
    exit;
}

// Sanitize barcode
$barcode = mysqli_real_escape_string($conn, $barcode);

// Auto-format SP dockets: convert "sp1234567" or "SP1234567" to "SP 1234567"
if (preg_match('/^(sp)(\d+)$/i', $barcode, $matches)) {
    $barcode = 'SP ' . $matches[2];
} elseif (preg_match('/^sp\s+(\d+)$/i', $barcode, $matches)) {
    $barcode = 'SP ' . $matches[1];
}

// Query docket details
$sql = "SELECT dd.*, 
               o.office_name,
               u.full_name as creator_name
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        LEFT JOIN tbl_users u ON dd.created_by = u.user_id
        WHERE dd.doc_no = '$barcode'
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    // Try searching by docket_id if numeric
    if (is_numeric($barcode)) {
        $sql = "SELECT dd.*, 
                       o.office_name,
                       u.full_name as creator_name
                FROM docket_details dd
                LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
                LEFT JOIN tbl_users u ON dd.created_by = u.user_id
                WHERE dd.docket_id = '$barcode'
                LIMIT 1";
        $result = mysqli_query($conn, $sql);
    }
}

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Docket not found: ' . htmlspecialchars($barcode)
    ]);
    exit;
}

$docket = mysqli_fetch_assoc($result);

// Log the lookup (optional - for audit)
$user_id = $_SESSION['user_id'] ?? 0;
$log_sql = "INSERT INTO tbl_barcode_scans (doc_no, docket_id, scanned_by, scanned_at, action_type) 
            VALUES ('{$docket['doc_no']}', {$docket['docket_id']}, $user_id, NOW(), 'lookup')
            ON DUPLICATE KEY UPDATE scanned_at = NOW()";
@mysqli_query($conn, $log_sql); // Silent fail if table doesn't exist

// Return docket data
echo json_encode([
    'success' => true,
    'docket' => [
        'docket_id' => $docket['docket_id'],
        'doc_no' => $docket['doc_no'],
        'status' => $docket['status'],
        'company_name' => $docket['company_name'],
        'client_name' => $docket['client_name'],
        'client_phone' => $docket['client_phone'],
        'client_address' => $docket['client_address'],
        'pickup_location' => $docket['pickup_location'] ?? $docket['branch_office'] ?? '',
        'delivery_location' => $docket['delivery_location'] ?? $docket['client_address'] ?? '',
        'service_type' => $docket['service_type'] ?? 'SURFACE-NORMAL',
        'invoice_no' => $docket['invoice_no'] ?? $docket['doc_no'],
        'box' => $docket['box'] ?? '1',
        'weight' => $docket['weight'] ?? '0',
        'office_name' => $docket['office_name'],
        'creator_name' => $docket['creator_name'],
        'created_at' => $docket['created_at'],
        'pickup_datetime' => $docket['pickup_datetime']
    ]
]);

} catch (Exception $e) {
    // Catch any PHP errors and return as JSON
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'error_code' => 'SERVER_ERROR'
    ]);
}
