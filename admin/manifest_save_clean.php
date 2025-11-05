<?php
/**
 * Manifest Save Handler - Clean Version
 * Saves manifest data with proper error handling
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conn.php';

$office_id = intval($_POST['office_id'] ?? 0);
$car_id = intval($_POST['car_id'] ?? 0);
$driver_id = intval($_POST['driver_id'] ?? 0);
$driver_license = trim($_POST['driver_license'] ?? '');
$is_manual = intval($_POST['is_manual'] ?? 0);
$doc_nos = $_POST['doc_no'] ?? [];
$client_names = $_POST['client_name'] ?? [];
$items = $_POST['item'] ?? [];
$client_addresses = $_POST['client_address'] ?? [];
$boxes = $_POST['box'] ?? [];
$weights = $_POST['weight'] ?? [];
$rates = $_POST['rate'] ?? [];
$amounts = $_POST['amount'] ?? [];
$eway_bills = $_POST['eway_bill'] ?? [];
$pay_tos = $_POST['pay_to'] ?? [];

if (!$office_id) {
    echo "<div class='alert alert-danger' style='font-size:1.2rem;padding:20px;'><i class='fa fa-exclamation-triangle'></i> Invalid office selection.</div>";
    exit;
}

if (!$car_id || !$driver_id) {
    echo "<div class='alert alert-danger' style='font-size:1.2rem;padding:20px;'><i class='fa fa-exclamation-triangle'></i> Please select both Car and Driver!</div>";
    exit;
}

// Get office name and build prefix
$office_result = mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id=".intval($office_id)." LIMIT 1");
$office_row = mysqli_fetch_assoc($office_result);
$office_name = $office_row['office_name'] ?? 'OFF';
$clean = preg_replace('/[^A-Za-z]/', '', $office_name);
$prefix = strtoupper(substr($clean, 0, 3));
if (strlen($prefix) < 3) $prefix = str_pad($prefix, 3, 'X');
$yy = date('y');

// Determine next global sequence for this year
$like_pattern = mysqli_real_escape_string($conn, "%$yy/%");
$q = "SELECT manifest_no FROM tbl_manifest WHERE manifest_no LIKE '".$like_pattern."' ORDER BY manifest_id DESC LIMIT 1";
$res = mysqli_query($conn, $q);
$next_seq = 1;
if ($res && mysqli_num_rows($res) > 0) {
    $last = mysqli_fetch_assoc($res);
    $last_no = $last['manifest_no'];
    $parts = explode('/', $last_no);
    $num = intval(end($parts));
    $next_seq = $num + 1;
}
$seq_padded = str_pad($next_seq, 6, '0', STR_PAD_LEFT);
$manifest_no = $prefix . $yy . '/' . $seq_padded;

// Prepare details and totals
$gross_total = 0.00;
$total_pay_to = 0.00;
$details_to_insert = [];
$rows = max(count($doc_nos), count($rates));

for ($i = 0; $i < $rows; $i++) {
    $doc = trim($doc_nos[$i] ?? '');
    if ($doc === '') continue;
    
    $client = trim($client_names[$i] ?? '');
    $item = trim($items[$i] ?? '');
    $addr = trim($client_addresses[$i] ?? '');
    $box = intval($boxes[$i] ?? 0);
    $weight = floatval($weights[$i] ?? 0);
    $rate = floatval($rates[$i] ?? 0);
    $amount = ($amounts[$i] !== '' && $amounts[$i] !== null) ? floatval($amounts[$i]) : ($rate * max(1, $box));
    $eway = trim($eway_bills[$i] ?? '');
    $pay = floatval($pay_tos[$i] ?? 0);

    $gross_total += $amount;
    $total_pay_to += $pay;

    $details_to_insert[] = [
        'doc' => $doc,
        'client' => $client,
        'item' => $item,
        'addr' => $addr,
        'box' => $box,
        'weight' => $weight,
        'rate' => $rate,
        'amount' => $amount,
        'eway' => $eway,
        'pay' => $pay
    ];
}

if (count($details_to_insert) == 0) {
    echo "<div class='alert alert-warning' style='font-size:1.2rem;padding:20px;'><i class='fa fa-exclamation-triangle'></i> No docket entries to save. Please enter at least one docket number.</div>";
    exit;
}

$net_total = $gross_total - $total_pay_to;

// Insert manifest and details inside transaction
mysqli_begin_transaction($conn);
try {
    $created_at = date('Y-m-d H:i:s');
    
    // Insert manifest
    $manifest_insert = "INSERT INTO tbl_manifest (manifest_no, office_id, car_id, driver_id, created_at, total_gross, total_pay_to, net_total) VALUES (
        '".mysqli_real_escape_string($conn, $manifest_no)."',
        ".intval($office_id).",
        ".intval($car_id).",
        ".intval($driver_id).",
        '".mysqli_real_escape_string($conn, $created_at)."',
        ".floatval($gross_total).",
        ".floatval($total_pay_to).",
        ".floatval($net_total)."
    )";
    
    if (!mysqli_query($conn, $manifest_insert)) {
        throw new Exception('Failed to insert manifest: '.mysqli_error($conn));
    }
    
    $manifest_id = mysqli_insert_id($conn);

    // Insert manifest details
    foreach ($details_to_insert as $d) {
        $detail_insert = "INSERT INTO tbl_manifest_details (
            manifest_id, doc_no, client_name, item, client_address, 
            box, weight, rate, amount, eway_bill, pay_to
        ) VALUES (
            ".intval($manifest_id).",
            '".mysqli_real_escape_string($conn, $d['doc'])."',
            '".mysqli_real_escape_string($conn, $d['client'])."',
            '".mysqli_real_escape_string($conn, $d['item'])."',
            '".mysqli_real_escape_string($conn, $d['addr'])."',
            ".intval($d['box']).",
            ".floatval($d['weight']).",
            ".floatval($d['rate']).",
            ".floatval($d['amount']).",
            '".mysqli_real_escape_string($conn, $d['eway'])."',
            ".floatval($d['pay'])."
        )";
        
        if (!mysqli_query($conn, $detail_insert)) {
            throw new Exception('Failed to insert manifest detail: '.mysqli_error($conn));
        }
    }

    // If manual mode, also save to docket_details table
    if ($is_manual == 1) {
        $car_result = mysqli_query($conn, "SELECT car_number FROM tbl_car WHERE car_id=".intval($car_id));
        $car_info = mysqli_fetch_assoc($car_result);
        
        $driver_result = mysqli_query($conn, "SELECT staff_name, staff_phone FROM tbl_staff WHERE staff_id=".intval($driver_id)." AND staff_role='Driver'");
        $driver_info = mysqli_fetch_assoc($driver_result);
        
        $car_number = $car_info['car_number'] ?? 'N/A';
        $driver_name = $driver_info['staff_name'] ?? 'N/A';
        $driver_phone = $driver_info['staff_phone'] ?? 'N/A';
        
        foreach ($details_to_insert as $d) {
            $check_result = mysqli_query($conn, "SELECT docket_id FROM docket_details WHERE doc_no='".mysqli_real_escape_string($conn, $d['doc'])."'");
            
            if ($check_result && mysqli_num_rows($check_result) > 0) {
                $update_query = "UPDATE docket_details SET 
                    manifest_id = ".intval($manifest_id).",
                    updated_at = NOW()
                    WHERE doc_no='".mysqli_real_escape_string($conn, $d['doc'])."'";
                mysqli_query($conn, $update_query);
            } else {
                $docket_insert = "INSERT INTO docket_details (
                    doc_no, doc_type, trip_group_id, manifest_id, status, created_at, pickup_datetime,
                    company_name, client_name, client_address, item, box, weight, rate, amount,
                    unit_price, pay_to, eway_bill, office_id, branch_office, car_id, car_number,
                    driver_id, staff_id, driver_name, driver_phone, driver_license, service_type
                ) VALUES (
                    '".mysqli_real_escape_string($conn, $d['doc'])."',
                    'NON-DRS', NULL, ".intval($manifest_id).", 'In Transit',
                    '".mysqli_real_escape_string($conn, $created_at)."',
                    '".mysqli_real_escape_string($conn, $created_at)."',
                    'Manual Entry',
                    '".mysqli_real_escape_string($conn, $d['client'])."',
                    '".mysqli_real_escape_string($conn, $d['addr'])."',
                    '".mysqli_real_escape_string($conn, $d['item'])."',
                    ".intval($d['box']).", ".floatval($d['weight']).", ".floatval($d['rate']).",
                    ".floatval($d['amount']).", ".floatval($d['rate']).", ".floatval($d['pay']).",
                    '".mysqli_real_escape_string($conn, $d['eway'])."',
                    ".intval($office_id).",
                    '".mysqli_real_escape_string($conn, $office_name)."',
                    ".intval($car_id).",
                    '".mysqli_real_escape_string($conn, $car_number)."',
                    ".intval($driver_id).", ".intval($driver_id).",
                    '".mysqli_real_escape_string($conn, $driver_name)."',
                    '".mysqli_real_escape_string($conn, $driver_phone)."',
                    '".mysqli_real_escape_string($conn, $driver_license)."',
                    'Manual Manifest Entry'
                )";
                
                if (!mysqli_query($conn, $docket_insert)) {
                    throw new Exception('Failed to insert docket details: ' . mysqli_error($conn));
                }
            }
        }
    }

    mysqli_commit($conn);

    $success_msg = "<div class='alert alert-success' style='font-size:1.2rem;padding:20px;'>";
    $success_msg .= "<i class='fa fa-check-circle'></i> <strong>Success!</strong> ";
    $success_msg .= "Manifest <strong style='color:#6a1b9a;'>".htmlspecialchars($manifest_no)."</strong> ";
    $success_msg .= "saved successfully (ID: ".intval($manifest_id).")";
    
    if ($is_manual) {
        $success_msg .= " <span style='color:#7b1fa2;'><i class='fa fa-pencil'></i> [Manual Entry - ".count($details_to_insert)." docket(s) saved to Docket Details]</span>";
    }
    
    $success_msg .= "<br><button type='button' class='btn btn-primary' onclick=\"window.open('manifest_print.php?manifest_id=".intval($manifest_id)."', '_blank')\" style='margin-top:12px;padding:10px 20px;font-size:1.1rem;'>";
    $success_msg .= "<i class='fa fa-print'></i> Print Manifest</button>";
    $success_msg .= "</div>";
    
    echo $success_msg;
    
} catch (Exception $ex) {
    mysqli_rollback($conn);
    echo "<div class='alert alert-danger' style='font-size:1.2rem;padding:20px;'><i class='fa fa-exclamation-triangle'></i> Error saving manifest: " . htmlspecialchars($ex->getMessage()) . "</div>";
}
?>
