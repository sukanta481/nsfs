<?php
/**
 * OpsDesk bootstrap. Every ops_*.php page does:
 *
 *   require 'check_auth.php';
 *   requirePermission('ops_...');
 *   require 'includes/top.php';      // session + $conn (no HTML output)
 *   require 'opsdesk/bootstrap.php';
 *   ...handle POST, then...
 *   require 'opsdesk/layout_start.php';  ...page...  require 'opsdesk/layout_end.php';
 *
 * PHP 7.2 compatible. All queries use prepared statements.
 */

require_once __DIR__ . '/lib/money.php';
require_once __DIR__ . '/lib/payroll_calc.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('OpsDesk: database connection missing.');
}
mysqli_set_charset($conn, 'utf8mb4');

/* ---------------- DB helpers ---------------- */

/** Run a prepared statement. $params are bound as strings unless given $types. Returns mysqli_stmt. */
function ops_exec($sql, array $params = array(), $types = null)
{
    global $conn;
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . mysqli_error($conn));
    }
    if ($params) {
        if ($types === null) {
            $types = str_repeat('s', count($params));
        }
        $refs = array($types);
        foreach ($params as $k => $v) {
            $refs[] = &$params[$k];
        }
        call_user_func_array(array($stmt, 'bind_param'), $refs);
    }
    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_stmt_error($stmt);
        $errno = mysqli_stmt_errno($stmt);
        mysqli_stmt_close($stmt);
        throw new RuntimeException($err, $errno);
    }
    return $stmt;
}

function ops_rows($sql, array $params = array())
{
    $stmt = ops_exec($sql, $params);
    $res = mysqli_stmt_get_result($stmt);
    $rows = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : array();
    mysqli_stmt_close($stmt);
    return $rows;
}

function ops_one($sql, array $params = array())
{
    $rows = ops_rows($sql, $params);
    return $rows ? $rows[0] : null;
}

function ops_value($sql, array $params = array())
{
    $row = ops_one($sql, $params);
    return $row ? reset($row) : null;
}

/** INSERT/UPDATE; returns insert id (or affected rows when no id). */
function ops_write($sql, array $params = array())
{
    global $conn;
    $stmt = ops_exec($sql, $params);
    $id = mysqli_stmt_insert_id($stmt);
    $aff = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $id ?: $aff;
}

function ops_begin() { global $conn; mysqli_begin_transaction($conn); }
function ops_commit() { global $conn; mysqli_commit($conn); }
function ops_rollback() { global $conn; mysqli_rollback($conn); }

/* ---------------- user / security ---------------- */

function ops_user_id()
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function ops_user_name()
{
    if (!empty($_SESSION['full_name'])) return $_SESSION['full_name'];
    if (!empty($_SESSION['username'])) return $_SESSION['username'];
    return 'Unknown';
}

function ops_csrf_token()
{
    if (empty($_SESSION['ops_csrf'])) {
        $_SESSION['ops_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['ops_csrf'];
}

function ops_csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . ops_csrf_token() . '">';
}

function ops_check_csrf()
{
    $sent = isset($_POST['csrf']) ? $_POST['csrf'] : (isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '');
    if (!$sent || !hash_equals(ops_csrf_token(), $sent)) {
        $msg = 'Security token expired. Please reload the page and try again.';
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'fetch') {
            header('Content-Type: application/json; charset=utf-8');
            die(json_encode(array('ok' => false, 'error' => $msg)));
        }
        http_response_code(400);
        die($msg);
    }
}

function ops_require($perm)
{
    if (!hasPermission($perm)) {
        http_response_code(403);
        die('You do not have permission for this action.');
    }
}

/** SQL fragment limiting tbl_staff (alias) to the user's branch, using the CMS's own rule. */
function ops_office_scope($alias = 's')
{
    return function_exists('getOfficeFilter') ? getOfficeFilter($alias, 'office_id') : '';
}

/* ---------------- flash + redirect (POST/redirect/GET) ---------------- */

function ops_flash($msg, $type = 'ok')
{
    $_SESSION['ops_flash'] = array('msg' => $msg, 'type' => $type);
}

function ops_take_flash()
{
    if (empty($_SESSION['ops_flash'])) return null;
    $f = $_SESSION['ops_flash'];
    unset($_SESSION['ops_flash']);
    return $f;
}

function ops_redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/* ---------------- view helpers ---------------- */

if (!function_exists('h')) {
    function h($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

function ops_initials($name)
{
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_substr($p, 0, 1);
    }
    return mb_strtoupper($out);
}

function ops_avatar($id, $name, $size = null)
{
    $colors = array('#2c7fb8', '#27ae60', '#8e44ad', '#d35400', '#16a085', '#c0392b', '#2c3e50', '#7f8c8d');
    $style = 'background:' . $colors[$id % count($colors)];
    if ($size) $style .= ";width:{$size}px;height:{$size}px;font-size:" . round($size / 2.6) . 'px';
    return '<div class="avatar" style="' . $style . '">' . h(ops_initials($name)) . '</div>';
}

function ops_fmt_date($ymd, $fmt = 'j M')
{
    return $ymd ? date($fmt, strtotime($ymd)) : '';
}

/** Valid 'Y-m-d' or fallback */
function ops_date_param($key, $default)
{
    $v = isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
    $d = DateTime::createFromFormat('Y-m-d', $v);
    return ($d && $d->format('Y-m-d') === $v) ? $v : $default;
}

/** Valid 'Y-m' month or fallback; returns 'Y-m-01' */
function ops_month_param($key, $default)
{
    $v = isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
    return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $v) ? $v . '-01' : $default;
}

function ops_offices()
{
    return ops_rows('SELECT office_id, office_name FROM tbl_offices ORDER BY office_name');
}

function ops_staff_types()
{
    return ops_rows('SELECT type_name, activity_frequency FROM tbl_ops_staff_types WHERE active_status = 1 ORDER BY sort_order, type_name');
}

require_once __DIR__ . '/lib/ledgers.php';
require_once __DIR__ . '/lib/payroll.php';
