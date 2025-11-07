<?php
// check_duplicate_docket.php
// Endpoint used by add_trip_modern.php to check if a docket already exists

require_once 'conn.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $doc_no = trim($_GET['doc_no'] ?? '');
    if ($doc_no === '') {
        http_response_code(400);
        echo json_encode(['error' => 'doc_no parameter is required']);
        exit;
    }

    $doc_no_esc = mysqli_real_escape_string($conn, $doc_no);
    $sql = "SELECT doc_no, status, created_at, trip_group_id, company_name, client_name FROM docket_details WHERE doc_no = '$doc_no_esc' LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if (!$res) {
        throw new Exception('DB error: ' . mysqli_error($conn));
    }

    if (mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        echo json_encode([
            'exists' => true,
            'doc_no' => $row['doc_no'],
            'status' => $row['status'] ?? null,
            'created_at' => $row['created_at'] ?? null,
            'trip_group_id' => $row['trip_group_id'] ?? null,
            'company_name' => $row['company_name'] ?? null,
            'client_name' => $row['client_name'] ?? null
        ]);
        exit;
    }

    echo json_encode(['exists' => false]);
    exit;

} catch (Exception $ex) {
    http_response_code(500);
    error_log('check_duplicate_docket.php error: ' . $ex->getMessage());
    echo json_encode(['error' => 'Internal server error']);
    exit;
}
