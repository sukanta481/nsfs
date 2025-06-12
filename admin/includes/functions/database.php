<?php
// --- Configuration Section ---
if (!defined('DB_SERVER')) define('DB_SERVER', 'localhost');
if (!defined('DB_USER'))   define('DB_USER', 'root');
if (!defined('DB_PASS'))   define('DB_PASS', '');
if (!defined('DB_NAME'))   define('DB_NAME', 'nsfs');

// Optional: define('TABLE_PREFIX', 'tbl_');

// --- Global DB Connection ---
$GLOBALS['db_connection'] = null;

// --- Functions ---

function g_db_connect() {
    if (!isset($GLOBALS['db_connection']) || !$GLOBALS['db_connection']) {
        $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        if (!$conn) {
            die("DB Connection failed: " . mysqli_connect_error());
        }
        $GLOBALS['db_connection'] = $conn;
    }
    return $GLOBALS['db_connection'];
}

function g_db_close() {
    if (isset($GLOBALS['db_connection']) && $GLOBALS['db_connection']) {
        mysqli_close($GLOBALS['db_connection']);
        $GLOBALS['db_connection'] = null;
    }
}

function g_db_error($query, $errno, $error) { 
    die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[Sorry]</font></small><br><br></b></font>');
}

function g_db_query($query) {
    $conn = g_db_connect();
    $result = mysqli_query($conn, $query) or g_db_error($query, mysqli_errno($conn), mysqli_error($conn));
    return $result;
}

function g_db_perform($table, $data, $action = 'insert', $parameters = '') {
    $conn = g_db_connect();
    $query = '';
    if ($action == 'insert') {
        $columns = implode(", ", array_keys($data));
        $values = array();
        foreach ($data as $value) {
            if ($value === 'now()') {
                $values[] = 'NOW()';
            } elseif ($value === 'null') {
                $values[] = 'NULL';
            } else {
                $values[] = "'" . g_db_input($value) . "'";
            }
        }
        $values_str = implode(", ", $values);
        $query = "INSERT INTO $table ($columns) VALUES ($values_str)";
    } elseif ($action == 'update') {
        $updates = array();
        foreach ($data as $column => $value) {
            if ($value === 'now()') {
                $updates[] = "$column = NOW()";
            } elseif ($value === 'null') {
                $updates[] = "$column = NULL";
            } else {
                $updates[] = "$column = '" . g_db_input($value) . "'";
            }
        }
        $updates_str = implode(", ", $updates);
        $query = "UPDATE $table SET $updates_str WHERE $parameters";
    }
    return g_db_query($query);
}

function g_db_fetch_array($db_query) {
    return mysqli_fetch_array($db_query, MYSQLI_ASSOC);
}

function g_db_num_rows($db_query) {
    return mysqli_num_rows($db_query);
}

function g_db_data_seek($db_query, $row_number) {
    return mysqli_data_seek($db_query, $row_number);
}

function g_db_insert_id() {
    $conn = g_db_connect();
    return mysqli_insert_id($conn);
}

function g_db_affected_rows() {
    $conn = g_db_connect();
    return mysqli_affected_rows($conn);
}

function g_db_fetch_row($db_query) {
    return mysqli_fetch_row($db_query);
}

function g_db_free_result($db_query) {
    return mysqli_free_result($db_query);
}

function g_db_fetch_fields($db_query) {
    return mysqli_fetch_field($db_query);
}

function g_db_output($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function g_db_input($string) {
    $conn = g_db_connect();
    return mysqli_real_escape_string($conn, $string);
}

function g_db_prepare_input($string) {
    if (is_string($string)) {
        return trim(g_sanitize_string(stripslashes($string)));
    } elseif (is_array($string)) {
        foreach ($string as $key => $value) {
            $string[$key] = g_db_prepare_input($value);
        }
        return $string;
    } else {
        return $string;
    }
}

function g_sanitize_string($string) {
    $string = preg_replace('/\s+/', ' ', $string); // replaces multiple spaces with single
    return preg_replace("/[<>]/", '_', $string);
}
?>
