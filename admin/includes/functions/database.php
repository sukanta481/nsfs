<?php
  function g_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link') {
    if (USE_PCONNECT == 'true') {
      $connection = mysqli_pconnect($server, $username, $password);
    } else {
      $connection = mysqli_connect($server, $username, $password);
    }

    if ($connection) mysqli_select_db($connection, $database);

    return $connection;
  }

  function g_db_close($link = 'db_link') {
    $conn=g_db_connect();

    return mysqli_close($conn);
  }

  function g_db_error($query, $errno, $error) { 
    die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[Sorry]</font></small><br><br></b></font>');
  }

  function g_db_query($query, $link = 'db_link') {
    $conn=g_db_connect();

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {
      error_log('QUERY ' . $query . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }

    $result = mysqli_query($conn, $query) or g_db_error($query, mysqli_errno($conn), mysqli_error($conn));

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {
       $result_error = mysqli_error();
       error_log('RESULT ' . $result . ' ' . $result_error . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }

    return $result;
  }

  function g_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
    reset($data);
    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= 'now(), ';
            break;
          case 'null':
            $query .= 'null, ';
            break;
          default:
            $query .= '\'' . g_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'update ' . $table . ' set ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'null':
            $query .= $columns .= ' = null, ';
            break;
          default:
            $query .= $columns . ' = \'' . g_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }

    return g_db_query($query, $link);
  }

  function g_db_fetch_array($db_query) {
    return mysqli_fetch_array($db_query);
  }

  function g_db_num_rows($db_query) {
    return mysqli_num_rows($db_query);
  }

  function g_db_data_seek($db_query, $row_number) {
    return mysqli_data_seek($db_query, $row_number);
  }

  function g_db_insert_id($link = 'db_link') {
    $conn=g_db_connect();

    return mysqli_insert_id($conn);
  }

  function g_db_affected_rows($link = 'db_link') {
    $conn=g_db_connect();

    return mysqli_affected_rows($conn);
  }
  function g_db_fetch_row($db_query) {
    $conn=g_db_connect();

    return mysqli_fetch_row($db_query);
  }

  function g_db_free_result($db_query) {
    return mysqli_free_result($db_query);
  }

  function g_db_fetch_fields($db_query) {
    return mysqli_fetch_field($db_query);
  }

  function g_db_output($string) {
    return htmlspecialchars($string);
  }

  function g_db_input($string, $link = 'db_link') {
    $conn=g_db_connect();

    if (function_exists('mysqli_real_escape_string')) {
    	 $conn=g_db_connect();
      return mysqli_real_escape_string($conn, $string);
    } elseif (function_exists('mysqli_escape_string')) {
      return mysqli_escape_string($string);
    }

    return addslashes($string);
  }

  function g_db_prepare_input($string) {
    if (is_string($string)) {
      return trim(g_sanitize_string(stripslashes($string)));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = g_db_prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }

  function g_sanitize_string($string) {
    $string = ereg_replace(' +', ' ', $string);

    return preg_replace("/[<>]/", '_', $string);
  }
?>
