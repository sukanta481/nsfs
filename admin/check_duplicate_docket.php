<?php
require 'conn.php';
require 'DocketDetailsManager.php';

header('Content-Type: application/json');

$doc_no = isset($_GET['doc_no']) ? trim($_GET['doc_no']) : '';

if (empty($doc_no)) {
    echo json_encode(['exists' => false]);
    exit;
}

// Initialize Docket Manager
$docketManager = new DocketDetailsManager($conn);

// Check if docket exists in docket_details table
$docket = $docketManager->getDocketByNumber($doc_no);

if ($docket) {
    echo json_encode([
        'exists' => true,
        'doc_no' => $docket['doc_no'],
        'status' => $docket['status'],
        'created_at' => date('d M Y', strtotime($docket['created_at'])),
        'trip_group_id' => $docket['trip_group_id'] ?? 'N/A',
        'company_name' => $docket['company_name'] ?? 'N/A',
        'client_name' => $docket['client_name'] ?? 'N/A'
    ]);
} else {
    echo json_encode(['exists' => false]);
}
?>
