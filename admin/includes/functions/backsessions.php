<?php
$link=mysql_connect("edgecutting.db.4553468.hostedresource.com","edgecutting","Edge_C00ting");
mysql_select_db("edgecutting",$link);
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
      $value_query = mysql_query("select value from tbl_sessions where sesskey = '" . mysql_real_escape_string($key) . "' and expiry > '" . time() . "'");
      $value = mysql_fetch_array($value_query);

      if (isset($value['value'])) {
        return $value['value'];
      }

      return '';
    }

    function _sess_write($key, $val) {
      global $SESS_LIFE;

      $expiry = time() + $SESS_LIFE;
      $value = $val;

      $check_query = mysql_query("select count(*) as total from tbl_sessions where sesskey = '" . mysql_real_escape_string($key) . "'");
      $check = mysql_fetch_array($check_query);

      if ($check['total'] > 0) {
        return mysql_query("update tbl_sessions set expiry = '" . mysql_real_escape_string($expiry) . "', value = '" . mysql_real_escape_string($value) . "' where sesskey = '" . mysql_real_escape_string($key) . "'");
      } else {
        return mysql_query("insert into tbl_sessions values ('" . mysql_real_escape_string($key) . "', '" . mysql_real_escape_string($expiry) . "', '" . mysql_real_escape_string($value) . "')");
      }
    }

    function _sess_destroy($key) {
      return mysql_query("delete from tbl_sessions where sesskey = '" . mysql_real_escape_string($key) . "'");
    }

    function _sess_gc($maxlifetime) {
      mysql_query("delete from tbl_sessions where expiry < '" . time() . "'");

      return true;
    }

    session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
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
        $sane_session_id = session_get_cookie_params();

        setcookie(function_session_name(), '', time()-42000, $session_data['path'], $session_data['domain']);

        $sane_session_id = false;
      }
    }
	if ($sane_session_id == false) {
		redirect(href_link('index.php','','NONSSL',false));
	}else {
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
        session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
      }

      function_session_start();

      $_SESSION = $session_backup;
      unset($session_backup);
    }
  }

 
?>