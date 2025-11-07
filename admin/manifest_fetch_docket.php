<?php
require 'conn.php';
header('Content-Type: application/json');
$docket_no = trim($_GET['docket_no'] ?? '');

// Base docket data
$docket_q = "SELECT 
    dd.docket_id,
    dd.doc_no,
    dd.client_name,
    dd.item,
    dd.client_address,
    dd.box,
    dd.weight,
    dd.rate,
    dd.eway_bill,
    dd.pay_to,
    dd.manifest_id AS docket_manifest_id
FROM docket_details dd
WHERE dd.doc_no='".mysqli_real_escape_string($conn, $docket_no)."' 
LIMIT 1";
$res = mysqli_query($conn, $docket_q);
$row = $res ? mysqli_fetch_assoc($res) : null;

if ($row) {
  // Prepare response with base fields
  $response = [
    'status' => 'found',
    'client_name' => $row['client_name'],
    'item' => $row['item'],
    'client_address' => $row['client_address'],
    'box' => $row['box'],
    'weight' => $row['weight'],
    'rate' => $row['rate'],
    'eway_bill' => $row['eway_bill'],
    'pay_to' => $row['pay_to']
  ];

  // First try: check tbl_manifest_details for this doc_no
  $md_q = "SELECT md.manifest_id, m.manifest_no, m.created_at, m.office_id
           FROM tbl_manifest_details md
           LEFT JOIN tbl_manifest m ON md.manifest_id = m.manifest_id
           WHERE md.doc_no='".mysqli_real_escape_string($conn, $docket_no)."' LIMIT 1";
  $md_res = mysqli_query($conn, $md_q);
  if ($md_res && mysqli_num_rows($md_res) > 0) {
    $md_row = mysqli_fetch_assoc($md_res);
    $response['in_manifest'] = true;
    $response['manifest_id'] = $md_row['manifest_id'];
    $response['manifest_no'] = $md_row['manifest_no'];
    $response['manifest_created_at'] = $md_row['created_at'];
    $response['manifest_office_id'] = $md_row['office_id'];
  } else {
    // Fallback: if docket_details.manifest_id exists, fetch manifest header
    $d_manifest_id = intval($row['docket_manifest_id']);
    if ($d_manifest_id > 0) {
      $mh_q = "SELECT manifest_id, manifest_no, created_at, office_id FROM tbl_manifest WHERE manifest_id=".intval($d_manifest_id)." LIMIT 1";
      $mh_res = mysqli_query($conn, $mh_q);
      if ($mh_res && mysqli_num_rows($mh_res) > 0) {
        $mh_row = mysqli_fetch_assoc($mh_res);
        $response['in_manifest'] = true;
        $response['manifest_id'] = $mh_row['manifest_id'];
        $response['manifest_no'] = $mh_row['manifest_no'];
        $response['manifest_created_at'] = $mh_row['created_at'];
        $response['manifest_office_id'] = $mh_row['office_id'];
      } else {
        $response['in_manifest'] = false;
      }
    } else {
      $response['in_manifest'] = false;
    }
  }

  echo json_encode($response);
} else {
  echo json_encode(['status'=>'not_found']);
}
