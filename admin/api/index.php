<?php
/**
 * North Superfast Service (NSFS) API — Unified Production Build.
 * SITUATION: public_html/admin/api/index.php
 * Handles 100% of the Android Jetpack Compose app integrations.
 */

date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep PHP logs clean and out of JSON payloads

// ---------------- CORS and Headers ----------------
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---------------- Database Loader ----------------
$conn = null;
$conn_path = __DIR__ . '/../conn.php';
if (file_exists($conn_path)) {
    require_once $conn_path;
} else {
    // Elegant runtime fallback
    $db_host = getenv('DB_HOST') ?: 'localhost';
    $db_user = getenv('DB_USER') ?: 'root';
    $db_pass = getenv('DB_PASS') ?: '';
    $db_name = getenv('DB_NAME') ?: 'nsfs_db';
    error_reporting(0);
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    error_reporting(E_ALL);
}

if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database offline']);
    exit;
}

// ---------------- Table Auto-Provisioning ----------------
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS tbl_api_tokens (
    token_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    is_legacy_admin TINYINT(1) NOT NULL DEFAULT 0,
    token CHAR(64) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_used_at DATETIME DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    KEY idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ---------------- Shared Helpers ----------------
function json_ok($data = [], $extra = []) {
    $response = array_merge(['success' => true], $extra);
    if (is_array($data)) {
        $is_assoc = false;
        if (!empty($data)) {
            $keys = array_keys($data);
            $is_assoc = (array_keys($keys) !== $keys);
        }
        if ($is_assoc) {
            $response = array_merge($response, $data);
        } else {
            $response['data'] = $data;
        }
    } else {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_err($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function body_json() {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? array_merge($decoded, $_POST) : $_POST;
}

function bearer_token() {
    $hdr = '';
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) $hdr = $_SERVER['HTTP_AUTHORIZATION'];
    elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) $hdr = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    elseif (function_exists('apache_request_headers')) {
        $h = apache_request_headers();
        if (isset($h['Authorization'])) $hdr = $h['Authorization'];
    }
    if (preg_match('/Bearer\s+(\S+)/i', $hdr, $m)) return trim($m[1]);
    return null;
}

function status_list() {
    return [
        'Pending', 'Confirmed', 'Picked Up', 'In Transit', 'In Transit to Branch',
        'Arrived at Branch', 'Received at Destination', 'Out for Delivery',
        'Pending POD', 'Delivered', 'Failed', 'Cancelled', 'Delayed'
    ];
}

function sanitize_docket_row($row) {
    if (!is_array($row)) return $row;
    $string_fields = [
        'doc_no', 'service_type', 'doc_type', 'status', 'created_at', 'updated_at',
        'company_name', 'company_phone', 'company_address', 'pickup_location',
        'client_name', 'client_phone', 'client_email', 'client_address', 'delivery_location',
        'item', 'dimensions', 'proof_of_delivery'
    ];
    foreach ($string_fields as $f) {
        $row[$f] = isset($row[$f]) && $row[$f] !== null ? (string)$row[$f] : '';
    }
    foreach (['docket_id', 'box', 'office_id', 'created_by'] as $f) {
        $row[$f] = isset($row[$f]) ? intval($row[$f]) : 0;
    }
    foreach (['weight', 'rate', 'amount'] as $f) {
        $row[$f] = isset($row[$f]) ? floatval($row[$f]) : 0.0;
    }
    return $row;
}

// ---------------- Auth Logic & Contexts ----------------
function build_context($user_id, $is_legacy) {
    global $conn;
    $uid = intval($user_id);

    if ($is_legacy) {
        $q = mysqli_query($conn, "SELECT id, adminname, admin_email FROM tbl_administrator WHERE id = $uid LIMIT 1");
        $r = $q ? mysqli_fetch_assoc($q) : null;
        if (!$r) return null;
        return [
            'user_id' => intval($r['id']),
            'username' => $r['adminname'],
            'full_name' => $r['adminname'],
            'role_id' => 0,
            'role_name' => 'Super Admin',
            'is_super_admin' => true,
            'office_id' => null,
            'office_name' => null,
            'can_access_all_offices' => true,
            'permissions' => ['*'],
            'allowed_statuses' => status_list(),
        ];
    }

    $q = mysqli_query($conn, "SELECT user_id, username, full_name, role_id, office_id, can_access_all_offices FROM tbl_users WHERE user_id = $uid LIMIT 1");
    $u = $q ? mysqli_fetch_assoc($q) : null;
    if (!$u) return null;

    $role_name = '';
    $rid = intval($u['role_id']);
    $rq = mysqli_query($conn, "SELECT role_name FROM tbl_roles WHERE role_id = $rid LIMIT 1");
    if ($rq && $rr = mysqli_fetch_assoc($rq)) $role_name = $rr['role_name'];
    $is_super = ($role_name === 'Super Admin');

    if ($is_super) {
        $perms = ['*'];
    } else {
        $perms = [];
        $pq = mysqli_query($conn, "SELECT p.permission_key FROM tbl_role_permissions rp JOIN tbl_permissions p ON rp.permission_id = p.permission_id WHERE rp.role_id = $rid");
        if ($pq) while ($pr = mysqli_fetch_assoc($pq)) $perms[] = $pr['permission_key'];
        if (empty($perms)) $perms = ['dashboard_view', 'docket_view', 'docket_create', 'docket_status_update'];
    }

    $office_name = null;
    $office_id = ($u['office_id'] !== null && $u['office_id'] !== '') ? intval($u['office_id']) : null;
    if ($office_id) {
        $oq = mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id = $office_id LIMIT 1");
        if ($oq && $orow = mysqli_fetch_assoc($oq)) $office_name = $orow['office_name'];
    }

    return [
        'user_id' => intval($u['user_id']),
        'username' => $u['username'],
        'full_name' => $u['full_name'] ?: $u['username'],
        'role_id' => $rid,
        'role_name' => $role_name ?: 'User',
        'is_super_admin' => $is_super,
        'office_id' => $office_id,
        'office_name' => $office_name,
        'can_access_all_offices' => !empty($u['can_access_all_offices']),
        'permissions' => $perms,
        'allowed_statuses' => status_list(),
    ];
}

$cached_api_user = null;
function current_user() {
    global $conn, $cached_api_user;
    if ($cached_api_user !== null) return $cached_api_user;
    
    $tok = bearer_token();
    if (!$tok) return null;
    
    $tok_esc = mysqli_real_escape_string($conn, $tok);
    $q = mysqli_query($conn, "SELECT user_id, is_legacy_admin, expires_at FROM tbl_api_tokens WHERE token = '$tok_esc' LIMIT 1");
    if (!$q || !($row = mysqli_fetch_assoc($q))) return null;
    if (strtotime($row['expires_at']) < time()) return null;
    
    mysqli_query($conn, "UPDATE tbl_api_tokens SET last_used_at = NOW() WHERE token = '$tok_esc'");
    $cached_api_user = build_context($row['user_id'], intval($row['is_legacy_admin']) === 1);
    return $cached_api_user;
}

function require_auth() {
    $user = current_user();
    if (!$user) json_err('Unauthorized session, invalid token', 401);
    return $user;
}

function require_perm($key) {
    $user = require_auth();
    if ($user['is_super_admin'] || in_array('*', $user['permissions'])) return;
    if (!in_array($key, $user['permissions'])) json_err("Forbidden, missing permission: $key", 403);
}

function office_filter_sql($alias = 'dd') {
    $user = current_user();
    if (!$user) return ' AND 1=0 ';
    if ($user['is_super_admin'] || $user['can_access_all_offices'] || in_array('office_view_all', $user['permissions'])) return '';
    if (!empty($user['office_id'])) return " AND {$alias}.office_id = " . intval($user['office_id']) . " ";
    return '';
}

function creator_filter_sql($alias = 'dd') {
    $user = current_user();
    if (!$user) return ' AND 1=0 ';
    if ($user['is_super_admin'] || in_array('docket_view_all', $user['permissions'])) return '';
    return " AND {$alias}.created_by = " . intval($user['user_id']) . " ";
}

function password_matches($plain, $stored) {
    if ($stored === null || $stored === '') return false;
    if (substr($stored, 0, 4) === '$2y$' || substr($stored, 0, 4) === '$2a$') return password_verify($plain, $stored);
    if (strlen($stored) === 32 && ctype_xdigit($stored)) return md5($plain) === $stored;
    return hash_equals((string)$stored, (string)$plain);
}

// ---------------- Route Parsing ----------------
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
// Normalize base folder removal
$path = '';
if (preg_match('#/api/(.*)$#', $uri, $m)) {
    $path = trim($m[1], '/');
}
// Strip out manual index.php routing
$path = str_replace('index.php/', '', $path);
$path = trim(str_replace('index.php', '', $path), '/');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ---------------- ENDPOINT CONTROLLER MATRIX ----------------

if ($path === 'ping') {
    json_ok(['pong' => true, 'time' => date('c')]);
}

// PUBLIC DOCKET TRACKING (No Authorized Header Required)
if ($path === 'track') {
    if ($method !== 'GET') json_err('Method not allowed', 405);
    $doc_no = isset($_GET['doc_no']) ? trim($_GET['doc_no']) : '';
    if (empty($doc_no)) json_err('doc_no parameter required', 400);

    $sql = "SELECT docket_id, doc_no, status, current_location, proof_of_delivery, manifest_id, created_at FROM docket_details WHERE doc_no = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) json_err('Query preparation failed', 500);
    
    mysqli_stmt_bind_param($stmt, 's', $doc_no);
    mysqli_stmt_execute($stmt);
    $dres = mysqli_stmt_get_result($stmt);
    $docket = mysqli_fetch_assoc($dres);
    mysqli_stmt_close($stmt);

    if (!$docket) json_err('Docket reference number not registered', 404);

    $id = intval($docket['docket_id']);
    $history_sql = "SELECT new_status, changed_at, notes, location, status_date FROM docket_status_history WHERE docket_id = ? ORDER BY COALESCE(status_date, changed_at) ASC, history_id ASC";
    $hstmt = mysqli_prepare($conn, $history_sql);
    $history_rows = [];
    if ($hstmt) {
        mysqli_stmt_bind_param($hstmt, 'i', $id);
        mysqli_stmt_execute($hstmt);
        $hres = mysqli_stmt_get_result($hstmt);
        while ($hrow = mysqli_fetch_assoc($hres)) {
            $ev_time = $hrow['status_date'] ?: $hrow['changed_at'];
            $history_rows[] = [
                'status' => $hrow['new_status'],
                'notes' => $hrow['notes'] ?: '',
                'location' => $hrow['location'] ?: '',
                'time' => date('c', strtotime($ev_time))
            ];
        }
        mysqli_stmt_close($hstmt);
    }

    $has_manifest = (!empty($docket['manifest_id']) && intval($docket['manifest_id']) > 0);
    $steps = $has_manifest 
        ? ['Pending', 'Confirmed', 'Picked Up', 'In Transit to Branch', 'Arrived at Branch', 'Out for Delivery', 'Delivered']
        : ['Pending', 'Confirmed', 'Picked Up', 'Out for Delivery', 'Delivered'];

    $timeline = [];
    foreach ($steps as $step) {
        $matched = false;
        $timestamp = null;
        $location = '';
        $notes = '';

        if ($docket['status'] === $step) $matched = true;

        foreach ($history_rows as $hr) {
            if ($hr['status'] === $step) {
                $matched = true;
                $timestamp = $hr['time'];
                $location = $hr['location'];
                $notes = $hr['notes'];
                break;
            }
        }

        if ($step === 'Pending' && !$timestamp) {
            $matched = true;
            $timestamp = date('c', strtotime($docket['created_at']));
        }

        $timeline[] = [
            'step_name' => $step,
            'completed' => $matched,
            'timestamp' => $timestamp,
            'location' => $location ?: ($matched ? ($docket['current_location'] ?: 'Unspecified Office') : ''),
            'notes' => $notes
        ];
    }

    json_ok([
        'doc_no' => $docket['doc_no'],
        'current_status' => $docket['status'],
        'current_location' => $docket['current_location'] ?: 'Unspecified Office',
        'pod_available' => !empty($docket['proof_of_delivery']),
        'timeline' => $timeline,
        'history' => $history_rows
    ]);
}

// AUTH - SESSION MANAGER
if ($path === 'auth/login') {
    if ($method !== 'POST') json_err('Method not allowed', 405);
    $in = body_json();
    $username = trim($in['username'] ?? '');
    $password = (string)($in['password'] ?? '');
    if ($username === '' || $password === '') json_err('Username and password are required', 422);

    $u_esc = mysqli_real_escape_string($conn, $username);
    $uq = mysqli_query($conn, "SELECT user_id, password, active_status FROM tbl_users WHERE username = '$u_esc' LIMIT 1");
    $matched_user_id = null; $is_legacy = false;
    
    if ($uq && $urow = mysqli_fetch_assoc($uq)) {
        $active = !isset($urow['active_status']) || intval($urow['active_status']) === 1;
        if ($active && password_matches($password, $urow['password'])) {
            $matched_user_id = intval($urow['user_id']);
        }
    }

    if ($matched_user_id === null) {
        $aq = mysqli_query($conn, "SELECT id, adminpassword FROM tbl_administrator WHERE adminname = '$u_esc' LIMIT 1");
        if ($aq && $arow = mysqli_fetch_assoc($aq)) {
            if (password_matches($password, $arow['adminpassword'])) {
                $matched_user_id = intval($arow['id']);
                $is_legacy = true;
            }
        }
    }

    if ($matched_user_id === null) json_err('Invalid username or password', 401);

    $ctx = build_context($matched_user_id, $is_legacy);
    if (!$ctx) json_err('User context retrieval failure', 500);

    $token = bin2hex(random_bytes(32));
    $tok_esc = mysqli_real_escape_string($conn, $token);
    $leg = $is_legacy ? 1 : 0;
    mysqli_query($conn, "INSERT INTO tbl_api_tokens (user_id, is_legacy_admin, token, expires_at)
                         VALUES ($matched_user_id, $leg, '$tok_esc', DATE_ADD(NOW(), INTERVAL 30 DAY))");

    json_ok(['token' => $token, 'user' => $ctx]);
}

if ($path === 'auth/me') {
    $ctx = require_auth();
    json_ok(['user' => $ctx]);
}

if ($path === 'auth/logout') {
    $tok = bearer_token();
    if ($tok) {
        $tok_esc = mysqli_real_escape_string($conn, $tok);
        mysqli_query($conn, "DELETE FROM tbl_api_tokens WHERE token = '$tok_esc'");
    }
    json_ok([]);
}

// DYNAMIC DROP-DOWN LOOKUPS
if (strpos($path, 'lookups/') === 0) {
    require_auth();
    $sub = substr($path, 8);

    if ($sub === 'offices') {
        $tbl = 'tbl_offices';
        $ck = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_offices'");
        if (!$ck || mysqli_num_rows($ck) == 0) $tbl = 'offices';

        $offices = [];
        $col_res = mysqli_query($conn, "SHOW COLUMNS FROM $tbl");
        $cols = [];
        if ($col_res) {
            while ($c = mysqli_fetch_assoc($col_res)) $cols[] = $c['Field'];
        }
        $oid = in_array('office_id', $cols) ? 'office_id' : 'id';
        $onm = in_array('office_name', $cols) ? 'office_name' : 'name';
        $cp = in_array('contact_person', $cols) ? 'contact_person' : 'contact_name';
        $ph = in_array('contact_number', $cols) ? 'contact_number' : 'phone';
        $ad = in_array('address', $cols) ? 'address' : 'location';

        $res = mysqli_query($conn, "SELECT $oid, $onm, $cp, $ph, $ad FROM $tbl ORDER BY $onm");
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $offices[] = [
                    'office_id' => intval($r[$oid]),
                    'office_name' => (string)$r[$onm],
                    'contact_person' => isset($r[$cp]) ? (string)$r[$cp] : '',
                    'contact_number' => isset($r[$ph]) ? (string)$r[$ph] : '',
                    'address' => isset($r[$ad]) ? (string)$r[$ad] : ''
                ];
            }
        }
        json_ok($offices);
    }

    if ($sub === 'cars') {
        $tbl = 'tbl_car';
        $ck = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_car'");
        if (!$ck || mysqli_num_rows($ck) == 0) $tbl = 'car';

        $cars = [];
        $res = mysqli_query($conn, "SELECT car_id, car_number, car_details FROM $tbl ORDER BY car_number");
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $cars[] = [
                    'car_id' => intval($r['car_id']),
                    'car_number' => (string)$r['car_number'],
                    'car_details' => isset($r['car_details']) ? (string)$r['car_details'] : ''
                ];
            }
        }
        json_ok($cars);
    }

    if ($sub === 'staff') {
        $tbl = 'tbl_staff';
        $ck = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_staff'");
        if (!$ck || mysqli_num_rows($ck) == 0) $tbl = 'staff';

        $staff = [];
        $res = mysqli_query($conn, "SELECT staff_id, staff_name, staff_phone FROM $tbl ORDER BY staff_name");
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $staff[] = [
                    'staff_id' => intval($r['staff_id']),
                    'staff_name' => (string)$r['staff_name'],
                    'staff_phone' => isset($r['staff_phone']) ? (string)$r['staff_phone'] : ''
                ];
            }
        }
        json_ok($staff);
    }

    if ($sub === 'delay-reasons') {
        $tbl = 'tbl_delay_reasons';
        $ck = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_delay_reasons'");
        if (!$ck || mysqli_num_rows($ck) == 0) $tbl = 'delay_reasons';

        $flat = isset($_GET['format']) && $_GET['format'] === 'flat';
        $reasons = [];
        $active_where = "";
        $col_res = mysqli_query($conn, "SHOW COLUMNS FROM $tbl");
        if ($col_res) {
            while ($c = mysqli_fetch_assoc($col_res)) {
                if ($c['Field'] === 'is_active') { $active_where = "WHERE is_active=1"; break; }
            }
        }

        $res = mysqli_query($conn, "SELECT reason_id, reason_category, reason_text FROM $tbl $active_where ORDER BY reason_category, reason_text");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $cat = $row['reason_category'] ?: 'General';
                $item = [
                    'reason_id' => intval($row['reason_id']),
                    'reason_category' => $cat,
                    'reason_text' => (string)$row['reason_text'],
                    'is_active' => 1
                ];
                if ($flat) {
                    $reasons[] = $item;
                } else {
                    if (!isset($reasons[$cat])) $reasons[$cat] = [];
                    $reasons[$cat][] = [
                        'reason_id' => $item['reason_id'],
                        'reason_text' => $item['reason_text']
                    ];
                }
            }
        }
        json_ok($reasons);
    }

    if ($sub === 'statuses') {
        json_ok(status_list());
    }

    json_err('Lookup subtype route not matched', 404);
}

// CORE METRICS (DASHBOARD STATS)
if ($path === 'dashboard/stats') {
    require_perm('dashboard_view');
    $office_filter = office_filter_sql('dd');
    $creator_filter = creator_filter_sql('dd');

    // Counts (KPIs)
    $total = 0;
    $tres = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE 1=1 $office_filter $creator_filter");
    if ($tres && $r = mysqli_fetch_row($tres)) $total = intval($r[0]);

    $delivered = 0;
    $dres = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE dd.status='Delivered' $office_filter $creator_filter");
    if ($dres && $r = mysqli_fetch_row($dres)) $delivered = intval($r[0]);

    $in_transit = 0;
    $itres = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE dd.status IN ('In Transit', 'In Transit to Branch') $office_filter $creator_filter");
    if ($itres && $r = mysqli_fetch_row($itres)) $in_transit = intval($r[0]);

    $delayed = 0;
    $dyres = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE dd.status='Delayed' $office_filter $creator_filter");
    if ($dyres && $r = mysqli_fetch_row($dyres)) $delayed = intval($r[0]);

    $today_start = date('Y-m-d 00:00:00');
    $today_end = date('Y-m-d 23:59:59');

    $today_total = 0;
    $tdres = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE dd.created_at BETWEEN '$today_start' AND '$today_end' $office_filter $creator_filter");
    if ($tdres && $r = mysqli_fetch_row($tdres)) $today_total = intval($r[0]);

    $today_delivered = 0;
    $tddres = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE dd.status='Delivered' AND dd.created_at BETWEEN '$today_start' AND '$today_end' $office_filter $creator_filter");
    if ($tddres && $r = mysqli_fetch_row($tddres)) $today_delivered = intval($r[0]);

    $monday = date('Y-m-d 00:00:00', strtotime('monday this week'));
    $week_total = 0;
    $wkres = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE dd.created_at >= '$monday' $office_filter $creator_filter");
    if ($wkres && $r = mysqli_fetch_row($wkres)) $week_total = intval($r[0]);

    $delivery_rate = $total > 0 ? round(($delivered / $total) * 100, 1) : 0.0;

    // Status Distribution
    $picked_up = 0;
    $pres = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE dd.status='Picked Up' $office_filter $creator_filter");
    if ($pres && $r = mysqli_fetch_row($pres)) $picked_up = intval($r[0]);

    $out_for_delivery = 0;
    $odres = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE dd.status='Out for Delivery' $office_filter $creator_filter");
    if ($odres && $r = mysqli_fetch_row($odres)) $out_for_delivery = intval($r[0]);

    $pending_pod = 0;
    $pdres = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE (dd.status='Pending POD' OR (dd.status='Delivered' AND (dd.proof_of_delivery IS NULL OR dd.proof_of_delivery=''))) $office_filter $creator_filter");
    if ($pdres && $r = mysqli_fetch_row($pdres)) $pending_pod = intval($r[0]);

    // Manifests counts (Scoped mapping)
    $tbl_manifest = 'tbl_manifest';
    $ck = @mysqli_query($conn, "SHOW TABLES LIKE 'tbl_manifest'");
    if (!$ck || mysqli_num_rows($ck) == 0) $tbl_manifest = 'manifest';

    $m_cols = [];
    $mc_res = @mysqli_query($conn, "SHOW COLUMNS FROM $tbl_manifest");
    if ($mc_res) {
        while ($mc = mysqli_fetch_assoc($mc_res)) $m_cols[] = $mc['Field'];
    }
    $f_office = in_array('from_office_id', $m_cols) ? 'from_office_id' : (in_array('from_branch', $m_cols) ? 'from_branch' : 'from_office');
    $t_office = in_array('to_office_id', $m_cols) ? 'to_office_id' : (in_array('to_branch', $m_cols) ? 'to_branch' : 'to_office');

    $manifest_where = "1=1";
    $user = current_user();
    if (!$user['is_super_admin'] && !$user['can_access_all_offices'] && !in_array('office_view_all', $user['permissions'])) {
        if (!empty($user['office_id'])) {
            $oid = intval($user['office_id']);
            $manifest_where = "(m.$f_office = $oid OR m.$t_office = $oid)";
        }
    }
    $manifest_count = 0;
    $mnres = mysqli_query($conn, "SELECT COUNT(*) FROM $tbl_manifest m WHERE $manifest_where");
    if ($mnres && $r = mysqli_fetch_row($mnres)) $manifest_count = intval($r[0]);

    // Chronological rolling 12 months trend metrics
    $labels = [];
    $dockets_counts = [];
    $delivered_counts = [];
    for ($i = 11; $i >= 0; $i--) {
        $m_time = strtotime("-$i months");
        $start = date('Y-m-01 00:00:00', $m_time);
        $end = date('Y-m-t 23:59:59', $m_time);
        $labels[] = date('M', $m_time);

        $dct = 0;
        $d_rs = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE dd.created_at BETWEEN '$start' AND '$end' $office_filter $creator_filter");
        if ($d_rs && $r = mysqli_fetch_row($d_rs)) $dct = intval($r[0]);
        $dockets_counts[] = $dct;

        $dlv = 0;
        $dl_rs = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE dd.status='Delivered' AND dd.created_at BETWEEN '$start' AND '$end' $office_filter $creator_filter");
        if ($dl_rs && $r = mysqli_fetch_row($dl_rs)) $dlv = intval($r[0]);
        $delivered_counts[] = $dlv;
    }

    // Service types Breakdown
    $s_types = [];
    $stres = mysqli_query($conn, "SELECT COALESCE(NULLIF(dd.service_type, ''), 'Standard') as type, COUNT(*) as count FROM docket_details dd WHERE 1=1 $office_filter $creator_filter GROUP BY type ORDER BY count DESC LIMIT 5");
    if ($stres) {
        while ($r = mysqli_fetch_assoc($stres)) $s_types[] = ['type' => (string)$r['type'], 'count' => intval($r['count'])];
    }

    // Client Leaders listing
    $clients = [];
    $clres = mysqli_query($conn, "SELECT COALESCE(NULLIF(dd.company_name, ''), 'Walk-In Client') as name, COUNT(*) as count FROM docket_details dd WHERE 1=1 $office_filter $creator_filter GROUP BY name ORDER BY count DESC LIMIT 8");
    if ($clres) {
        while ($r = mysqli_fetch_assoc($clres)) $clients[] = ['name' => (string)$r['name'], 'count' => intval($r['count'])];
    }

    json_ok([
        'kpis' => [
            'total' => $total,
            'delivered' => $delivered,
            'in_transit' => $in_transit,
            'delayed' => $delayed,
            'today_total' => $today_total,
            'today_delivered' => $today_delivered,
            'week_total' => $week_total,
            'delivery_rate' => $delivery_rate
        ],
        'status_distribution' => [
            'picked_up' => $picked_up,
            'in_transit' => $in_transit,
            'out_for_delivery' => $out_for_delivery,
            'delivered' => $delivered,
            'delayed' => $delayed,
            'pending_pod' => $pending_pod
        ],
        'manifest_count' => $manifest_count,
        'monthly_trend' => [
            'labels' => $labels,
            'dockets' => $dockets_counts,
            'delivered' => $delivered_counts
        ],
        'service_types' => $s_types,
        'top_clients' => $clients
    ]);
}

// PAGINATED DOCKET LIST & CREATE ENDPOINTS
if ($path === 'dockets') {
    require_perm('docket_view');

    if ($method === 'GET') {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $per_page = isset($_GET['per_page']) ? min(100, max(1, intval($_GET['per_page']))) : 50;
        $offset = ($page - 1) * $per_page;

        $filters = ['1=1'];
        $filters[] = "1=1" . office_filter_sql('dd') . creator_filter_sql('dd');
        
        if (!empty($_GET['status'])) $filters[] = "dd.status = '" . mysqli_real_escape_string($conn, $_GET['status']) . "'";

        $where = implode(' AND ', $filters);
        
        $c_res = mysqli_query($conn, "SELECT COUNT(*) FROM docket_details dd WHERE $where");
        $total = ($c_res && $row = mysqli_fetch_row($c_res)) ? intval($row[0]) : 0;

        $rows = [];
        $res = mysqli_query($conn, "SELECT dd.*, o.office_name as branch_office_name FROM docket_details dd LEFT JOIN tbl_offices o ON dd.office_id = o.office_id WHERE $where ORDER BY dd.pickup_datetime DESC, dd.docket_id DESC LIMIT $per_page OFFSET $offset");
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                if ($r['pickup_datetime']) $r['pickup_datetime'] = date('c', strtotime($r['pickup_datetime']));
                if ($r['delivery_datetime']) $r['delivery_datetime'] = date('c', strtotime($r['delivery_datetime']));
                if ($r['created_at']) $r['created_at'] = date('c', strtotime($r['created_at']));
                if ($r['updated_at']) $r['updated_at'] = date('c', strtotime($r['updated_at']));
                $rows[] = sanitize_docket_row($r);
            }
        }
        json_ok($rows, ['total' => $total, 'page' => $page, 'per_page' => $per_page, 'total_pages' => ceil($total / $per_page)]);
    }

    if ($method === 'POST') {
        require_perm('docket_create');
        $in = body_json();

        $doc_no = trim($in['doc_no'] ?? '');
        if (empty($doc_no)) json_err('doc_no is required', 400);

        // Check Duplication
        $ck = mysqli_query($conn, "SELECT docket_id FROM docket_details WHERE doc_no = '" . mysqli_real_escape_string($conn, $doc_no) . "' LIMIT 1");
        if ($ck && mysqli_num_rows($ck) > 0) json_err("Docket Number already exists in records", 400);

        $user = require_auth();
        $fields = [
            'doc_no' => $doc_no,
            'service_type' => trim($in['service_type'] ?? 'Standard'),
            'doc_type' => trim($in['doc_type'] ?? ''),
            'status' => 'Pending',
            'company_name' => trim($in['company_name'] ?? ''),
            'company_phone' => trim($in['company_phone'] ?? ''),
            'company_address' => trim($in['company_address'] ?? ''),
            'pickup_location' => trim($in['pickup_location'] ?? ''),
            'client_name' => trim($in['client_name'] ?? ''),
            'client_phone' => trim($in['client_phone'] ?? ''),
            'client_email' => trim($in['client_email'] ?? ''),
            'client_address' => trim($in['client_address'] ?? ''),
            'delivery_location' => trim($in['delivery_location'] ?? ''),
            'item' => trim($in['item'] ?? ''),
            'dimensions' => trim($in['dimensions'] ?? ''),
            'box' => isset($in['box']) ? intval($in['box']) : 1,
            'weight' => isset($in['weight']) ? floatval($in['weight']) : 0.0,
            'rate' => isset($in['rate']) ? floatval($in['rate']) : 0.0,
            'amount' => isset($in['amount']) ? floatval($in['amount']) : 0.0,
            'office_id' => isset($in['office_id']) ? intval($in['office_id']) : ($user['office_id'] ?: 0),
            'created_by' => $user['user_id'],
            'pickup_datetime' => date('Y-m-d H:i:s')
        ];

        $keys = []; $vals = []; $types = ''; $binds = [];
        foreach ($fields as $k => $v) {
            $keys[] = $k;
            $vals[] = '?';
            $binds[] = $v;
            $types .= is_int($v) ? 'i' : (is_double($v) || is_float($v) ? 'd' : 's');
        }

        $ins_sql = "INSERT INTO docket_details (" . implode(',', $keys) . ") VALUES (" . implode(',', $vals) . ")";
        $stmt = mysqli_prepare($conn, $ins_sql);
        if (!$stmt) json_err('Failed to prepare docket creation query', 500);

        mysqli_stmt_bind_param($stmt, $types, ...$binds);
        if (!mysqli_stmt_execute($stmt)) json_err('Insert execution failed', 500);
        
        $new_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // Record Initial Activity Log
        $notes = "Booked via Android App";
        $hist_sql = "INSERT INTO docket_status_history (docket_id, old_status, new_status, notes, changed_at) VALUES (?, 'None', 'Pending', ?, NOW())";
        $hstmt = mysqli_prepare($conn, $hist_sql);
        if ($hstmt) {
            mysqli_stmt_bind_param($hstmt, 'is', $new_id, $notes);
            mysqli_stmt_execute($hstmt);
            mysqli_stmt_close($hstmt);
        }

        $q = mysqli_query($conn, "SELECT dd.*, o.office_name as branch_office_name FROM docket_details dd LEFT JOIN tbl_offices o ON dd.office_id = o.office_id WHERE dd.docket_id = $new_id LIMIT 1");
        $created = mysqli_fetch_assoc($q);
        json_ok(sanitize_docket_row($created));
    }
}

// INDIVIDUAL DOCKET HISTORY CHRONOLOGY
if (preg_match('#^dockets/(\d+)/history$#', $path, $m)) {
    require_perm('docket_view');
    $id = intval($m[1]);

    $hist_sql = "SELECT history_id, old_status, new_status, changed_at, notes, location, status_date FROM docket_status_history WHERE docket_id = ? ORDER BY COALESCE(status_date, changed_at) DESC, history_id DESC";
    $stmt = mysqli_prepare($conn, $hist_sql);
    $history = [];
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($r = mysqli_fetch_assoc($res)) {
            $ev_time = $r['status_date'] ?: $r['changed_at'];
            $history[] = [
                'history_id' => intval($r['history_id']),
                'docket_id' => $id,
                'old_status' => $r['old_status'] ?: '',
                'new_status' => $r['new_status'] ?: '',
                'changed_at' => date('c', strtotime($ev_time)),
                'notes' => $r['notes'] ?: '',
                'location' => $r['location'] ?: ''
            ];
        }
        mysqli_stmt_close($stmt);
    }
    
    // Dynamic static list workflow checks
    echo json_encode(['success' => true, 'history' => $history, 'timeline' => status_list()]);
    exit;
}

// STATUS CHANGES & COMPREHENSIVE POD FILE ATTACHMENTS
if (preg_match('#^dockets/(\d+)/status$#', $path, $m)) {
    require_perm('docket_status_update');
    $id = intval($m[1]);

    $in = body_json();
    $status = trim($in['status'] ?? '');
    $notes = trim($in['notes'] ?? '');
    $location = trim($in['location'] ?? '');
    $date_time = trim($in['status_date'] ?? date('Y-m-d H:i:s'));

    if (empty($status)) json_err('New status parameter missing', 400);

    $user = require_auth();
    if (!empty($user['allowed_statuses']) && !in_array('*', $user['allowed_statuses']) && !in_array($status, $user['allowed_statuses'])) {
        json_err("Forbidden status change to '$status'.", 403);
    }

    $dq = mysqli_query($conn, "SELECT status, doc_no, current_location FROM docket_details WHERE docket_id = $id LIMIT 1");
    $docket = $dq ? mysqli_fetch_assoc($dq) : null;
    if (!$docket) json_err("Docket reference lookup failed", 404);

    $old_status = $docket['status'];

    // Handle Upload Proof of Delivery (POD) Image
    $pod_url = '';
    if (isset($_FILES['pod_image']) && $_FILES['pod_image']['error'] === UPLOAD_ERR_OK) {
        $uploaddir = __DIR__ . '/../../uploads/pod/';
        if (!is_dir($uploaddir)) @mkdir($uploaddir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['pod_image']['name'], PATHINFO_EXTENSION));
        $filename = 'pod_' . $id . '_' . time() . '.' . $ext;
        $uploadfile = $uploaddir . $filename;

        if (move_uploaded_file($_FILES['pod_image']['tmp_name'], $uploadfile)) {
            $pod_url = 'uploads/pod/' . $filename;
        }
    }

    $up_parts = ["status = ?", "updated_at = NOW()"];
    $bind_values = [$status];
    $bind_types = "s";

    if (!empty($location)) {
        $up_parts[] = "current_location = ?";
        $bind_values[] = $location;
        $bind_types .= "s";
    }
    if (!empty($pod_url)) {
        $up_parts[] = "proof_of_delivery = ?";
        $bind_values[] = $pod_url;
        $bind_types .= "s";
    }
    if ($status === 'Delivered') {
        $up_parts[] = "delivery_datetime = ?";
        $bind_values[] = $date_time;
        $bind_types .= "s";
    }

    $bind_values[] = $id;
    $bind_types .= "i";

    $up_sql = "UPDATE docket_details SET " . implode(', ', $up_parts) . " WHERE docket_id = ?";
    $stmt = mysqli_prepare($conn, $up_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $bind_types, ...$bind_values);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Insert Log Chronology History Event
    $h_sql = "INSERT INTO docket_status_history (docket_id, old_status, new_status, notes, location, changed_at, status_date) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
    $hstmt = mysqli_prepare($conn, $h_sql);
    if ($hstmt) {
        mysqli_stmt_bind_param($hstmt, 'isssss', $id, $old_status, $status, $notes, $location, $date_time);
        mysqli_stmt_execute($hstmt);
        mysqli_stmt_close($hstmt);
    }

    json_ok(['docket_id' => $id, 'status' => $status, 'proof_of_delivery' => $pod_url]);
}

// TRIP MANIFEST RECORDS
if ($path === 'manifests') {
    require_perm('manifest_view');

    $tbl_manifest = 'tbl_manifest';
    $ck = @mysqli_query($conn, "SHOW TABLES LIKE 'tbl_manifest'");
    if (!$ck || mysqli_num_rows($ck) == 0) $tbl_manifest = 'manifest';

    $m_cols = [];
    $mc_res = @mysqli_query($conn, "SHOW COLUMNS FROM $tbl_manifest");
    if ($mc_res) {
        while ($mc = mysqli_fetch_assoc($mc_res)) $m_cols[] = $mc['Field'];
    }
    $f_office = in_array('from_office_id', $m_cols) ? 'from_office_id' : (in_array('from_branch', $m_cols) ? 'from_branch' : 'from_office');
    $t_office = in_array('to_office_id', $m_cols) ? 'to_office_id' : (in_array('to_branch', $m_cols) ? 'to_branch' : 'to_office');

    if ($method === 'GET') {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $per_page = isset($_GET['per_page']) ? min(100, max(1, intval($_GET['per_page']))) : 50;
        $offset = ($page - 1) * $per_page;

        $manifest_where = "1=1";
        $user = require_auth();
        if (!$user['is_super_admin'] && !$user['can_access_all_offices'] && !in_array('office_view_all', $user['permissions'])) {
            if (!empty($user['office_id'])) {
                $oid = intval($user['office_id']);
                $manifest_where = "(m.$f_office = $oid OR m.$t_office = $oid)";
            }
        }

        $tot_res = mysqli_query($conn, "SELECT COUNT(*) FROM $tbl_manifest m WHERE $manifest_where");
        $total = ($tot_res && $row = mysqli_fetch_row($tot_res)) ? intval($row[0]) : 0;

        $manifests = [];
        $sql = "SELECT m.*, 
                o1.office_name as from_office_name, 
                o2.office_name as to_office_name,
                c.car_number as car_plate_number,
                s.staff_name as driver_full_name
                FROM $tbl_manifest m 
                LEFT JOIN tbl_offices o1 ON m.$f_office = o1.office_id
                LEFT JOIN tbl_offices o2 ON m.$t_office = o2.office_id
                LEFT JOIN tbl_car c ON m.car_id = c.car_id
                LEFT JOIN tbl_staff s ON m.driver_id = s.staff_id
                WHERE $manifest_where
                ORDER BY m.created_at DESC, m.manifest_id DESC
                LIMIT $per_page OFFSET $offset";

        $res = mysqli_query($conn, $sql);
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $r['manifest_id'] = intval($r['manifest_id']);
                $r['car_id'] = isset($r['car_id']) ? intval($r['car_id']) : 0;
                $r['driver_id'] = isset($r['driver_id']) ? intval($r['driver_id']) : 0;
                $r[$f_office] = isset($r[$f_office]) ? intval($r[$f_office]) : 0;
                $r[$t_office] = isset($r[$t_office]) ? intval($r[$t_office]) : 0;
                
                $r['from_office_id'] = $r[$f_office];
                $r['to_office_id'] = $r[$t_office];
                $r['created_at'] = isset($r['created_at']) ? date('c', strtotime($r['created_at'])) : '';
                $manifests[] = $r;
            }
        }

        json_ok($manifests, ['total' => $total, 'page' => $page, 'per_page' => $per_page, 'total_pages' => ceil($total / $per_page)]);
    }

    if ($method === 'POST') {
        $user = require_auth();
        $in = body_json();

        $from_id = isset($in['from_office_id']) ? intval($in['from_office_id']) : ($user['office_id'] ?: 1);
        $to_id = isset($in['to_office_id']) ? intval($in['to_office_id']) : 0;
        $car_id = isset($in['car_id']) ? intval($in['car_id']) : 0;
        $driver_id = isset($in['driver_id']) ? intval($in['driver_id']) : 0;
        $docket_ids = isset($in['docket_ids']) && is_array($in['docket_ids']) ? $in['docket_ids'] : [];

        if ($to_id <= 0) json_err('Destination branch office is required', 400);
        if (empty($docket_ids)) json_err('At least one docket must be attached to the trip manifest', 400);

        // Standardize column inserts
        $manifest_no = 'MNF-' . date('Ymd') . '-' . rand(1000, 9999);
        $ins_mnf = "INSERT INTO $tbl_manifest (manifest_no, $f_office, $t_office, car_id, driver_id, status, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, 'Dispatched', ?, NOW())";
        $stmt = mysqli_prepare($conn, $ins_mnf);
        if (!$stmt) json_err('Trip manifest insert failed to prepare', 500);

        $uid = $user['user_id'];
        mysqli_stmt_bind_param($stmt, 'siiiii', $manifest_no, $from_id, $to_id, $car_id, $driver_id, $uid);
        if (!mysqli_stmt_execute($stmt)) json_err('Create manifest execute failed', 500);

        $m_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // Attach attached dockets mapping details
        $tbl_m_details = 'tbl_manifest_details';
        $ck2 = @mysqli_query($conn, "SHOW TABLES LIKE 'tbl_manifest_details'");
        if (!$ck2 || mysqli_num_rows($ck2) == 0) $tbl_m_details = 'manifest_details';

        $det_stmt = mysqli_prepare($conn, "INSERT INTO $tbl_m_details (manifest_id, docket_id) VALUES (?, ?)");
        $up_docket = mysqli_prepare($conn, "UPDATE docket_details SET manifest_id = ?, status = 'In Transit to Branch', current_location = ? WHERE docket_id = ?");

        $to_branch_name = 'Branch Depot';
        $bq = mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id = $to_id LIMIT 1");
        if ($bq && $brow = mysqli_fetch_assoc($bq)) $to_branch_name = $brow['office_name'];

        foreach ($docket_ids as $did) {
            $did = intval($did);
            if ($det_stmt) {
                mysqli_stmt_bind_param($det_stmt, 'ii', $m_id, $did);
                mysqli_stmt_execute($det_stmt);
            }
            if ($up_docket) {
                mysqli_stmt_bind_param($up_docket, 'isi', $m_id, $to_branch_name, $did);
                mysqli_stmt_execute($up_docket);
            }

            // Insert Event History chronologies
            $notes = "Attached to Manifest ID: #$m_id ($manifest_no)";
            $h_sql = "INSERT INTO docket_status_history (docket_id, old_status, new_status, notes, location, changed_at) VALUES (?, 'Pending', 'In Transit to Branch', ?, 'In Dispatch', NOW())";
            $hstmt = mysqli_prepare($conn, $h_sql);
            if ($hstmt) {
                mysqli_stmt_bind_param($hstmt, 'is', $did, $notes);
                mysqli_stmt_execute($hstmt);
                mysqli_stmt_close($hstmt);
            }
        }
        if ($det_stmt) mysqli_stmt_close($det_stmt);
        if ($up_docket) mysqli_stmt_close($up_docket);

        json_ok([
            'manifest_id' => $m_id,
            'manifest_no' => $manifest_no,
            'from_office_id' => $from_id,
            'to_office_id' => $to_id,
            'status' => 'Dispatched'
        ]);
    }
}

// ARRIVAL & RECEIPT OF BULK MANIFESTS
if (preg_match('#^manifests/(\d+)/receive$#', $path, $m)) {
    require_perm('manifest_receive');
    $m_id = intval($m[1]);

    $tbl_manifest = 'tbl_manifest';
    $ck = @mysqli_query($conn, "SHOW TABLES LIKE 'tbl_manifest'");
    if (!$ck || mysqli_num_rows($ck) == 0) $tbl_manifest = 'manifest';

    $tbl_m_details = 'tbl_manifest_details';
    $ck2 = @mysqli_query($conn, "SHOW TABLES LIKE 'tbl_manifest_details'");
    if (!$ck2 || mysqli_num_rows($ck2) == 0) $tbl_m_details = 'manifest_details';

    // Verify manifest
    $mq = mysqli_query($conn, "SELECT status, to_office_id FROM $tbl_manifest WHERE manifest_id = $m_id LIMIT 1");
    $manifest = $mq ? mysqli_fetch_assoc($mq) : null;
    if (!$manifest) json_err('Manifest reference lookup failed', 404);

    mysqli_query($conn, "UPDATE $tbl_manifest SET status = 'Received', updated_at = NOW() WHERE manifest_id = $m_id");

    $branch_name = 'Arrived Depot';
    $to_id = intval($manifest['to_office_id']);
    $bq = mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id = $to_id LIMIT 1");
    if ($bq && $brow = mysqli_fetch_assoc($bq)) $branch_name = $brow['office_name'];

    // Unpack and update status of all dockets in manifest to 'Arrived at Branch'
    $dq = mysqli_query($conn, "SELECT docket_id FROM $tbl_m_details WHERE manifest_id = $m_id");
    if ($dq) {
        $up_docket = mysqli_prepare($conn, "UPDATE docket_details SET status = 'Arrived at Branch', current_location = ? WHERE docket_id = ?");
        while ($r = mysqli_fetch_assoc($dq)) {
            $did = intval($r['docket_id']);
            if ($up_docket) {
                mysqli_stmt_bind_param($up_docket, 'si', $branch_name, $did);
                mysqli_stmt_execute($up_docket);
            }

            // Timeline logs
            $notes = "Unpacked from Manifest ID: #$m_id";
            $h_sql = "INSERT INTO docket_status_history (docket_id, old_status, new_status, notes, location, changed_at) VALUES (?, 'In Transit to Branch', 'Arrived at Branch', ?, ?, NOW())";
            $hstmt = mysqli_prepare($conn, $h_sql);
            if ($hstmt) {
                mysqli_stmt_bind_param($hstmt, 'iss', $did, $notes, $branch_name);
                mysqli_stmt_execute($hstmt);
                mysqli_stmt_close($hstmt);
            }
        }
        if ($up_docket) mysqli_stmt_close($up_docket);
    }

    json_ok(['manifest_id' => $m_id, 'status' => 'Received', 'unpacked_location' => $branch_name]);
}

// WILDCARD - PATH UNMATCHED FALLBACK
json_err("API path subsegment '{$path}' not registered on this system", 404);