<?php
/**
 * Check if docket came via manifest and determine if current office is branch
 */

require 'conn.php';
require 'check_auth.php';

header('Content-Type: application/json');

$docket_id = isset($_GET['docket_id']) ? intval($_GET['docket_id']) : 0;

if ($docket_id <= 0) {
    echo json_encode(['error' => 'Invalid docket ID']);
    exit;
}

// Get docket info with manifest details
$query = "SELECT dd.docket_id, dd.doc_no, dd.created_by, dd.office_id as docket_office_id,
                 u1.office_id as creator_office_id,
                 md.manifest_id,
                 m.office_id as manifest_destination_office_id,
                 o.office_name as manifest_destination_office
          FROM docket_details dd
          LEFT JOIN tbl_users u1 ON dd.created_by = u1.user_id
          LEFT JOIN tbl_manifest_details md ON dd.doc_no = md.doc_no
          LEFT JOIN tbl_manifest m ON md.manifest_id = m.manifest_id
          LEFT JOIN tbl_offices o ON m.office_id = o.office_id
          WHERE dd.docket_id = $docket_id
          LIMIT 1";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['error' => 'Docket not found']);
    exit;
}

$docket = mysqli_fetch_assoc($result);

// Check current user's office
$current_user_office_id = $_SESSION['office_id'] ?? 0;

// Determine if docket came via manifest
$has_manifest = !empty($docket['manifest_id']);

// Determine if current office is branch office (not the creator office)
$is_branch_office = false;
if ($has_manifest) {
    // If manifest exists, check if current office is the destination office (not creator)
    $creator_office = $docket['creator_office_id'] ?? 0;
    $manifest_destination = $docket['manifest_destination_office_id'] ?? 0;
    
    // Current office is branch if it's the manifest destination and different from creator
    $is_branch_office = ($current_user_office_id == $manifest_destination && $current_user_office_id != $creator_office);
}

$response = [
    'has_manifest' => $has_manifest,
    'is_branch_office' => $is_branch_office,
    'manifest_destination_office' => $docket['manifest_destination_office'] ?? null,
    'current_office_id' => $current_user_office_id,
    'creator_office_id' => $docket['creator_office_id'] ?? null
];

echo json_encode($response);
