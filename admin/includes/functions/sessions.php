<?php

if ( (bool)ini_get('register_globals') == false ) {
    @ini_set('session.bug_compat_42', 1);
    @ini_set('session.bug_compat_warn', 0);
}

if (STORE_SESSIONS == 'mysql') {
    if (!$SESS_LIFE = get_cfg_var('session.gc_maxlifetime')) {
        $SESS_LIFE = 1440;
    }

    function _sess_open($save_path, $session_name) {
        return true;
    }

    function _sess_close() {
        return true;
    }

    function _sess_read($key) {
        $value_query = g_db_query("select value from ".TABLE_PREFIX."sessions where sesskey = '" . g_db_input($key) . "' and expiry > '" . time() . "'");
        $value = g_db_fetch_array($value_query);

        if (isset($value['value'])) {
            return $value['value'];
        }

        return '';
    }

    function _sess_write($key, $val) {
        global $SESS_LIFE;

        $expiry = time() + $SESS_LIFE;
        $value = $val;

        $check_query = g_db_query("select count(*) as total from ".TABLE_PREFIX."sessions where sesskey = '" . g_db_input($key) . "'");
        $check = g_db_fetch_array($check_query);

        if ($check['total'] > 0) {
            return g_db_query("update ".TABLE_PREFIX."sessions set expiry = '" . g_db_input($expiry) . "', value = '" . g_db_input($value) . "' where sesskey = '" . g_db_input($key) . "'");
        } else {
            return g_db_query("insert into ".TABLE_PREFIX."sessions values ('" . g_db_input($key) . "', '" . g_db_input($expiry) . "', '" . g_db_input($value) . "')");
        }
    }

    function _sess_destroy($key) {
        return g_db_query("delete from ".TABLE_PREFIX."sessions where sesskey = '" . g_db_input($key) . "'");
    }

    function _sess_gc($maxlifetime) {
        g_db_query("delete from ".TABLE_PREFIX."sessions where expiry < '" . time() . "'");
        return true;
    }

    // Only set save handler if session is not active
    if (session_status() == PHP_SESSION_NONE) {
        session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
    }
}


function function_session_start() {
    $sane_session_id = true;

    if (isset($_GET[function_session_name()])) {
        if (preg_match('/^[a-zA-Z0-9]+$/', $_GET[function_session_name()]) == false) {
            unset($_GET[function_session_name()]);
            $sane_session_id = false;
        }
    } elseif (isset($_POST[function_session_name()])) {
        if (preg_match('/^[a-zA-Z0-9]+$/', $_POST[function_session_name()]) == false) {
            unset($_POST[function_session_name()]);
            $sane_session_id = false;
        }
    } elseif (isset($_COOKIE[function_session_name()])) {
        if (preg_match('/^[a-zA-Z0-9]+$/', $_COOKIE[function_session_name()]) == false) {
            $session_data = session_get_cookie_params();
            setcookie(function_session_name(), '', time()-42000, $session_data['path'], $session_data['domain']);
            $sane_session_id = false;
        }
    }
    if ($sane_session_id == false) {
        redirect(href_link('index.php','','NONSSL',false));
    } else {
        session_start();
    }
}


function function_session_register($variable) {
    if (isset($GLOBALS[$variable])) {
        $_SESSION[$variable] =& $GLOBALS[$variable];
    } else {
        $_SESSION[$variable] = null;
    }
    return false;
}

function check_session($variable) {
    return isset($_SESSION) && array_key_exists($variable, $_SESSION);
}

function delete_session($variable) {
    unset($_SESSION[$variable]);
}

function function_session_id($sessid = '') {
    if ($sessid != '') {
        return session_id($sessid);
    } else {
        return session_id();
    }
}

function function_session_name($name = '') {
    if ($name != '') {
        return session_name($name);
    } else {
        return session_name();
    }
}

function close_session() {
    if (PHP_VERSION >= '4.0.4') {
        return session_write_close();
    } elseif (function_exists('session_close')) {
        return session_close();
    }
}

function destroy_session() {
    return session_destroy();
}

function function_session_save_path($path = '') {
    if (!empty($path)) {
        return session_save_path($path);
    } else {
        return session_save_path();
    }
}

function session_recreate() {
    if (PHP_VERSION >= 4.1) {
        $session_backup = $_SESSION;

        unset($_COOKIE[function_session_name()]);

        destroy_session();

        if (STORE_SESSIONS == 'mysql') {
            if (session_status() == PHP_SESSION_NONE) {
                session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
            }
        }

        function_session_start();

        $_SESSION = $session_backup;
        unset($session_backup);
    }
}

?>
