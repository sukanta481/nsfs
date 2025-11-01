<?php
require 'conn.php';
// manifest_save.php - improved manifest creation and details saving
// This script will:
// 1) Ensure manifest tables exist
// 2) Generate manifest number in format: FIRST3(office_name) + YY + '/' + 6-digit-seq (global per year)
// 3) Insert a row into tbl_manifest and corresponding rows into tbl_manifest_details
// 4) Compute gross total, total pay_to and net total (gross - pay_to)

$office_id = intval($_POST['office_id'] ?? 0);
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
    echo "<div class='alert alert-danger'>Invalid office selection.</div>";
    exit;
}

// Create tables if not exists
$create_manifest_sql = "CREATE TABLE IF NOT EXISTS `tbl_manifest` (
  `manifest_id` int(11) NOT NULL AUTO_INCREMENT,
  `manifest_no` varchar(60) NOT NULL,
  `office_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `total_gross` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_pay_to` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`manifest_id`),
  UNIQUE KEY `manifest_no` (`manifest_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
mysqli_query($conn, $create_manifest_sql);

$create_details_sql = "CREATE TABLE IF NOT EXISTS `tbl_manifest_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manifest_id` int(11) NOT NULL,
  `doc_no` varchar(255) NOT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `item` varchar(255) DEFAULT NULL,
  `client_address` text,
  `box` int(11) DEFAULT 0,
  `weight` decimal(10,2) DEFAULT 0.00,
  `rate` decimal(12,2) DEFAULT 0.00,
  `amount` decimal(12,2) DEFAULT 0.00,
  `eway_bill` varchar(255) DEFAULT NULL,
  `pay_to` decimal(12,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `manifest_id_idx` (`manifest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
mysqli_query($conn, $create_details_sql);

// Get office name and build prefix
$office_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id=".intval($office_id)." LIMIT 1"));
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
    if ($doc === '') continue; // skip empty row
    $client = trim($client_names[$i] ?? '');
    $item = trim($items[$i] ?? '');
    $addr = trim($client_addresses[$i] ?? '');
    $box = intval($boxes[$i] ?? 0);
    $weight = floatval($weights[$i] ?? 0);
    $rate = floatval($rates[$i] ?? 0);
    $amount = ($amounts[$i] !== '') ? floatval($amounts[$i]) : ($rate * max(1, $box));
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

$net_total = $gross_total - $total_pay_to;

// Insert manifest and details inside transaction
mysqli_begin_transaction($conn);
try {
    $created_at = date('Y-m-d H:i:s');
    $stmt = mysqli_prepare($conn, "INSERT INTO tbl_manifest (manifest_no, office_id, created_at, total_gross, total_pay_to, net_total) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sisddd', $manifest_no, $office_id, $created_at, $gross_total, $total_pay_to, $net_total);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to insert manifest: '.mysqli_stmt_error($stmt));
    }
    $manifest_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    $stmt2 = mysqli_prepare($conn, "INSERT INTO tbl_manifest_details (manifest_id, doc_no, client_name, item, client_address, box, weight, rate, amount, eway_bill, pay_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($details_to_insert as $d) {
        mysqli_stmt_bind_param($stmt2, 'issssidddsd', $manifest_id,
            $d['doc'], $d['client'], $d['item'], $d['addr'], $d['box'], $d['weight'], $d['rate'], $d['amount'], $d['eway'], $d['pay']);
        if (!mysqli_stmt_execute($stmt2)) {
            // fallback to manual escaped insert
            $ins = "INSERT INTO tbl_manifest_details (manifest_id, doc_no, client_name, item, client_address, box, weight, rate, amount, eway_bill, pay_to) VALUES ('".
                intval($manifest_id)."','".mysqli_real_escape_string($conn, $d['doc'])."','".mysqli_real_escape_string($conn, $d['client'])."','".mysqli_real_escape_string($conn, $d['item'])."','".mysqli_real_escape_string($conn, $d['addr'])."','".intval($d['box'])."','".floatval($d['weight'])."','".floatval($d['rate'])."','".floatval($d['amount'])."','".mysqli_real_escape_string($conn, $d['eway'])."','".floatval($d['pay'])."')";
            if (!mysqli_query($conn, $ins)) {
                throw new Exception('Failed to insert manifest detail: '.mysqli_error($conn));
            }
        }
    }
    mysqli_stmt_close($stmt2);

    mysqli_commit($conn);

    echo "<div class='alert alert-success'>success: Manifest <strong>".htmlspecialchars($manifest_no)."</strong> saved successfully (ID: ".intval($manifest_id).").<br><a href='manifest_print.php?manifest_id=".intval($manifest_id)."' target='_blank' class='btn btn-primary' style='margin-top:8px;padding:6px 12px;'><i class='fa fa-print'></i> Print Manifest</a></div>";
} catch (Exception $ex) {
    mysqli_rollback($conn);
    echo "<div class='alert alert-danger'>Error saving manifest: " . htmlspecialchars($ex->getMessage()) . "</div>";
}
?>
