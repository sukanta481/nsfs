<?php
/**
 * Manifest Save Handler - Fixed Version with Email Support
 * Saves manifest data with proper error handling and email notifications
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conn.php';
require 'email_config_smtp.php';
require 'email_templates.php';

$office_id = intval($_POST['office_id'] ?? 0);
$car_id = intval($_POST['car_id'] ?? 0);
$driver_id = intval($_POST['driver_id'] ?? 0);
$driver_license = trim($_POST['driver_license'] ?? '');
$is_manual = intval($_POST['is_manual'] ?? 0);

// Get manual input for car and driver (combo box functionality)
$manual_car_number = isset($_POST['car_number']) ? mysqli_real_escape_string($conn, trim($_POST['car_number'])) : '';
$manual_driver_name = isset($_POST['driver_name']) ? mysqli_real_escape_string($conn, trim($_POST['driver_name'])) : '';

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
// NEW: Email fields (if provided from form)
$client_emails = $_POST['client_email'] ?? [];
$company_emails = $_POST['company_email'] ?? [];

if (!$office_id) {
    echo "<div class='alert alert-danger' style='font-size:1.2rem;padding:20px;'><i class='fa fa-exclamation-triangle'></i> Invalid office selection.</div>";
    exit;
}

// Validate car and driver (check manual input or IDs)
if (empty($manual_car_number) || empty($manual_driver_name)) {
    echo "<div class='alert alert-danger' style='font-size:1.2rem;padding:20px;'><i class='fa fa-exclamation-triangle'></i> Please enter both Car and Driver!</div>";
    exit;
}

// Auto-add car to database if it's a new manual entry
if (!empty($manual_car_number) && !$car_id) {
    // Check if car already exists
    $check_car = mysqli_query($conn, "SELECT car_id FROM tbl_car WHERE car_number = '$manual_car_number' LIMIT 1");
    if ($check_car && mysqli_num_rows($check_car) > 0) {
        $car_row = mysqli_fetch_assoc($check_car);
        $car_id = $car_row['car_id'];
    } else {
        // Insert new car
        $insert_car = mysqli_query($conn, "INSERT INTO tbl_car (car_number, car_details, active_status) VALUES ('$manual_car_number', 'External Vehicle', 1)");
        if ($insert_car) {
            $car_id = mysqli_insert_id($conn);
        } else {
            echo "<div class='alert alert-danger' style='font-size:1.2rem;padding:20px;'><i class='fa fa-exclamation-triangle'></i> Failed to add new car.</div>";
            exit;
        }
    }
}

// Auto-add driver to staff if it's a new manual entry
if (!empty($manual_driver_name) && !$driver_id) {
    // Check if driver already exists
    $check_driver = mysqli_query($conn, "SELECT staff_id FROM tbl_staff WHERE staff_name = '$manual_driver_name' AND staff_role = 'Driver' LIMIT 1");
    if ($check_driver && mysqli_num_rows($check_driver) > 0) {
        $driver_row = mysqli_fetch_assoc($check_driver);
        $driver_id = $driver_row['staff_id'];
    } else {
        // Insert new driver (external driver with minimal info)
        $insert_driver = mysqli_query($conn, "INSERT INTO tbl_staff (staff_name, staff_role, staff_phone, office_id, branch_office, active_status)
                                              VALUES ('$manual_driver_name', 'Driver', 'N/A', 1, 'External', 1)");
        if ($insert_driver) {
            $driver_id = mysqli_insert_id($conn);
        } else {
            echo "<div class='alert alert-danger' style='font-size:1.2rem;padding:20px;'><i class='fa fa-exclamation-triangle'></i> Failed to add new driver.</div>";
            exit;
        }
    }
}

// Final validation - ensure we have both IDs now
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
    $client_email = trim($client_emails[$i] ?? '');
    $company_email = trim($company_emails[$i] ?? '');

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
        'pay' => $pay,
        'client_email' => $client_email,
        'company_email' => $company_email
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
    // Read ignore list from POST (docket numbers the user chose to "Add Anyway")
    $ignore_raw = $_POST['ignore_manifest'] ?? [];
    $ignore_list = array_map(function($v){ return strtoupper(trim($v)); }, (array)$ignore_raw);

    // Insert manifest details (but check for existing manifest entries unless ignored)
    foreach ($details_to_insert as $d) {
        $doc_clean = mysqli_real_escape_string($conn, $d['doc']);

        // Check if this docket is already present in any other manifest
        $md_q = "SELECT md.manifest_id, m.manifest_no FROM tbl_manifest_details md LEFT JOIN tbl_manifest m ON md.manifest_id=m.manifest_id WHERE md.doc_no='".$doc_clean."' LIMIT 1";
        $md_res = mysqli_query($conn, $md_q);
        if ($md_res && mysqli_num_rows($md_res) > 0) {
            $md_row = mysqli_fetch_assoc($md_res);
            $existing_manifest_no = $md_row['manifest_no'] ?? $md_row['manifest_id'];
            // If the user did not explicitly ignore this docket, abort
            if (!in_array(strtoupper($d['doc']), $ignore_list)) {
                throw new Exception("Docket " . htmlspecialchars($d['doc']) . " is already present in manifest " . htmlspecialchars($existing_manifest_no) . ". Please remove it or choose 'Add Anyway' for this docket.");
            }
            // else: allowed to add duplicate entry
        }

        $detail_insert = "INSERT INTO tbl_manifest_details (
            manifest_id, doc_no, client_name, item, client_address,
            box, weight, rate, amount, eway_bill, pay_to
        ) VALUES (
            ".intval($manifest_id).",
            '".$doc_clean."',
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

    // Get car and driver info for updates
    $car_result = mysqli_query($conn, "SELECT car_number FROM tbl_car WHERE car_id=".intval($car_id));
    $car_info = mysqli_fetch_assoc($car_result);

    $driver_result = mysqli_query($conn, "SELECT staff_name, staff_phone FROM tbl_staff WHERE staff_id=".intval($driver_id)." AND staff_role='Driver'");
    $driver_info = mysqli_fetch_assoc($driver_result);

    $car_number = $car_info['car_number'] ?? 'N/A';
    $driver_name = $driver_info['staff_name'] ?? 'N/A';
    $driver_phone = $driver_info['staff_phone'] ?? 'N/A';

    // Sync manifest_id to docket_details for ALL dockets (manual or auto-fetched)
    foreach ($details_to_insert as $d) {
        $check_result = mysqli_query($conn, "SELECT docket_id, client_email, company_email FROM docket_details WHERE doc_no='".mysqli_real_escape_string($conn, $d['doc'])."'");

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            // Docket exists - UPDATE manifest_id and car/driver info
            // Also update emails if they were provided and are currently empty
            $existing = mysqli_fetch_assoc($check_result);
            $docket_id = $existing['docket_id'];

            $update_query = "UPDATE docket_details SET
                manifest_id = ".intval($manifest_id).",
                car_id = ".intval($car_id).",
                car_number = '".mysqli_real_escape_string($conn, $car_number)."',
                driver_id = ".intval($driver_id).",
                staff_id = ".intval($driver_id).",
                driver_name = '".mysqli_real_escape_string($conn, $driver_name)."',
                driver_phone = '".mysqli_real_escape_string($conn, $driver_phone)."',";

            // Update emails if provided and database value is empty
            if (!empty($d['client_email']) && empty($existing['client_email'])) {
                $update_query .= " client_email = '".mysqli_real_escape_string($conn, $d['client_email'])."',";
            }
            if (!empty($d['company_email']) && empty($existing['company_email'])) {
                $update_query .= " company_email = '".mysqli_real_escape_string($conn, $d['company_email'])."',";
            }

            $update_query .= " updated_at = NOW()
                WHERE docket_id = ".intval($docket_id);

            if (!mysqli_query($conn, $update_query)) {
                throw new Exception('Failed to update docket manifest_id: ' . mysqli_error($conn));
            }

            // Send "Docket Created" email if this is a newly created manifest entry with email
            $refreshed = mysqli_query($conn, "SELECT * FROM docket_details WHERE docket_id = $docket_id");
            $docket_data = mysqli_fetch_assoc($refreshed);

            if ($docket_data && !empty($docket_data['company_email']) && filter_var($docket_data['company_email'], FILTER_VALIDATE_EMAIL)) {
                $email_subject = "📝 Docket Added to Manifest - #" . $docket_data['doc_no'];
                $email_body = getDocketCreatedEmailTemplate($docket_data);
                @sendEmail($docket_data['company_email'], $email_subject, $email_body, $docket_data['company_name']);
                error_log("Docket manifest email sent to company: " . $docket_data['company_email']);
            }

        } else if ($is_manual == 1) {
            // Manual mode: CREATE new docket if not exists
            $docket_insert = "INSERT INTO docket_details (
                doc_no, doc_type, trip_group_id, manifest_id, status, created_at, pickup_datetime,
                company_name, client_name, client_address, client_email, company_email,
                item, box, weight, rate, amount,
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
                '".mysqli_real_escape_string($conn, $d['client_email'])."',
                '".mysqli_real_escape_string($conn, $d['company_email'])."',
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

            // ========================================
            // SEND EMAIL NOTIFICATION TO COMPANY (Consignor/Sender) - DOCKET CREATED
            // ========================================
            $new_docket_id = mysqli_insert_id($conn);

            // Get full docket details for email
            $email_query = "SELECT * FROM docket_details WHERE docket_id = $new_docket_id";
            $email_result = mysqli_query($conn, $email_query);
            $docket_data = mysqli_fetch_assoc($email_result);

            if ($docket_data && !empty($docket_data['company_email']) && filter_var($docket_data['company_email'], FILTER_VALIDATE_EMAIL)) {
                $email_subject = "📝 Docket Created - #" . $docket_data['doc_no'];
                $email_body = getDocketCreatedEmailTemplate($docket_data);
                @sendEmail($docket_data['company_email'], $email_subject, $email_body, $docket_data['company_name']);
                error_log("Docket created email sent to company: " . $docket_data['company_email']);
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
