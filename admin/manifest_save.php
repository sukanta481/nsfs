<?php
require 'conn.php';
// manifest_save.php - improved manifest creation and details saving
// This script will:
// 1) Ensure manifest tables exist
// 2) Generate manifest number in format: FIRST3(office_name) + YY + '/' + 6-digit-seq (global per year)
// 3) Insert a row into tbl_manifest and corresponding rows into tbl_manifest_details
// 4) Compute gross total, total pay_to and net total (gross - pay_to)
// 5) Save to tbl_shipping_details if manual mode

$office_id = intval($_POST['office_id'] ?? 0);
$car_id = intval($_POST['car_id'] ?? 0);
$driver_id = intval($_POST['driver_id'] ?? 0);
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

// Create tables if not exists
$create_manifest_sql = "CREATE TABLE IF NOT EXISTS `tbl_manifest` (
  `manifest_id` int(11) NOT NULL AUTO_INCREMENT,
  `manifest_no` varchar(60) NOT NULL,
  `office_id` int(11) NOT NULL,
  `car_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `total_gross` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_pay_to` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`manifest_id`),
  UNIQUE KEY `manifest_no` (`manifest_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
mysqli_query($conn, $create_manifest_sql);

// Add car_id and driver_id columns if they don't exist (for existing tables)
$check_cols = mysqli_query($conn, "SHOW COLUMNS FROM tbl_manifest LIKE 'car_id'");
if ($check_cols && mysqli_num_rows($check_cols) == 0) {
    mysqli_query($conn, "ALTER TABLE tbl_manifest ADD COLUMN car_id INT(11) DEFAULT NULL AFTER office_id");
}
$check_cols2 = mysqli_query($conn, "SHOW COLUMNS FROM tbl_manifest LIKE 'driver_id'");
if ($check_cols2 && mysqli_num_rows($check_cols2) == 0) {
    mysqli_query($conn, "ALTER TABLE tbl_manifest ADD COLUMN driver_id INT(11) DEFAULT NULL AFTER car_id");
}

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
    $stmt = mysqli_prepare($conn, "INSERT INTO tbl_manifest (manifest_no, office_id, car_id, driver_id, created_at, total_gross, total_pay_to, net_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'siiisddd', $manifest_no, $office_id, $car_id, $driver_id, $created_at, $gross_total, $total_pay_to, $net_total);
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

    // If manual mode, also save to tbl_shipping_details for future auto-fetch
    if ($is_manual == 1) {
        // Get office name for branch_office field
        $office_name_for_branch = $office_name;
        
        // Check if tbl_shipping_details exists and create/update if needed
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_shipping_details'");
        if (mysqli_num_rows($check_table) == 0) {
            // Table doesn't exist, skip saving to shipping details
            echo "<!-- Note: tbl_shipping_details table not found, skipping manual entry save -->";
        } else {
            // Check which columns exist
            $columns = [];
            $col_check = mysqli_query($conn, "SHOW COLUMNS FROM tbl_shipping_details");
            while($col = mysqli_fetch_assoc($col_check)) {
                $columns[] = $col['Field'];
            }
            
            // Build insert based on available columns
            foreach ($details_to_insert as $d) {
                $fields = ['doc_no', 'client_name', 'item', 'client_address', 'box', 'weight', 'rate', 'eway_bill', 'pay_to', 'branch_office'];
                $values = [
                    mysqli_real_escape_string($conn, $d['doc']),
                    mysqli_real_escape_string($conn, $d['client']),
                    mysqli_real_escape_string($conn, $d['item']),
                    mysqli_real_escape_string($conn, $d['addr']),
                    intval($d['box']),
                    floatval($d['weight']),
                    floatval($d['rate']),
                    mysqli_real_escape_string($conn, $d['eway']),
                    floatval($d['pay']),
                    mysqli_real_escape_string($conn, $office_name_for_branch)
                ];
                
                // Only include delivery_status if column exists
                if (in_array('delivery_status', $columns)) {
                    $fields[] = 'delivery_status';
                    $values[] = mysqli_real_escape_string($conn, 'pending');
                }
                
                $ins_ship = "INSERT INTO tbl_shipping_details (".implode(', ', $fields).") VALUES ('".implode("','", $values)."')";
                    
                $result = mysqli_query($conn, $ins_ship);
                if (!$result) {
                    // Log error but don't fail the whole transaction
                    echo "<!-- Shipping details insert warning: " . mysqli_error($conn) . " -->";
                }
            }
        }
    }

    mysqli_commit($conn);

    echo "<div class='alert alert-success' style='font-size:1.2rem;padding:20px;'><i class='fa fa-check-circle'></i> <strong>Success!</strong> Manifest <strong style='color:#6a1b9a;'>".htmlspecialchars($manifest_no)."</strong> saved successfully (ID: ".intval($manifest_id).")".($is_manual ? " <span style='color:#7b1fa2;'><i class='fa fa-pencil'></i> [Manual Entry - Also saved to Shipping Details]</span>" : "")."<br><button type='button' class='btn btn-primary' onclick=\"window.open('manifest_print.php?manifest_id=".intval($manifest_id)."', '_blank')\" style='margin-top:12px;padding:10px 20px;font-size:1.1rem;'><i class='fa fa-print'></i> Print Manifest</button></div>";
} catch (Exception $ex) {
    mysqli_rollback($conn);
    echo "<div class='alert alert-danger'>Error saving manifest: " . htmlspecialchars($ex->getMessage()) . "</div>";
}
?>
