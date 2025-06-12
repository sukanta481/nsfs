<?php

function random_number($min = null, $max = null) {
    static $seeded;

    if (!$seeded) {
        mt_srand((double)microtime()*1000000);
        $seeded = true;
    }

    if (isset($min) && isset($max)) {
        if ($min >= $max) {
            return $min;
        } else {
            return mt_rand($min, $max);
        }
    } else {
        return mt_rand();
    }
}

function tep_get_file_permissions($mode) {
    // determine type
    if (($mode & 0xC000) == 0xC000) { // unix domain socket
        $type = 's';
    } elseif (($mode & 0x4000) == 0x4000) { // directory
        $type = 'd';
    } elseif (($mode & 0xA000) == 0xA000) { // symbolic link
        $type = 'l';
    } elseif (($mode & 0x8000) == 0x8000) { // regular file
        $type = '-';
    } elseif (($mode & 0x6000) == 0x6000) { //bBlock special file
        $type = 'b';
    } elseif (($mode & 0x2000) == 0x2000) { // character special file
        $type = 'c';
    } elseif (($mode & 0x1000) == 0x1000) { // named pipe
        $type = 'p';
    } else { // unknown
        $type = '?';
    }

    // determine permissions
    $owner['read']    = ($mode & 00400) ? 'r' : '-';
    $owner['write']   = ($mode & 00200) ? 'w' : '-';
    $owner['execute'] = ($mode & 00100) ? 'x' : '-';
    $group['read']    = ($mode & 00040) ? 'r' : '-';
    $group['write']   = ($mode & 00020) ? 'w' : '-';
    $group['execute'] = ($mode & 00010) ? 'x' : '-';
    $world['read']    = ($mode & 00004) ? 'r' : '-';
    $world['write']   = ($mode & 00002) ? 'w' : '-';
    $world['execute'] = ($mode & 00001) ? 'x' : '-';

    // adjust for SUID, SGID and sticky bit
    if ($mode & 0x800 ) $owner['execute'] = ($owner['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x400 ) $group['execute'] = ($group['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x200 ) $world['execute'] = ($world['execute'] == 'x') ? 't' : 'T';

    return $type .
        $owner['read'] . $owner['write'] . $owner['execute'] .
        $group['read'] . $group['write'] . $group['execute'] .
        $world['read'] . $world['write'] . $world['execute'];
}

function get_browser_detect($component) {
    return isset($_SERVER['HTTP_USER_AGENT']) ? stripos($_SERVER['HTTP_USER_AGENT'], $component) !== false : false;
}

function break_string($string, $len, $break_char = '_') {
    $l = 0;
    $output = '';
    for ($i = 0, $n = strlen($string); $i < $n; $i++) {
        $char = substr($string, $i, 1);
        if ($char != ' ') {
            $l++;
        } else {
            $l = 0;
        }
        if ($l > $len) {
            $l = 1;
            $output .= $break_char;
        }
        $output .= $char;
    }

    return $output;
}

function parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
}

function output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5);
    } else {
        if ($translate == false) {
            return parse_input_field_data($string, array('"' => '&quot;'));
        } else {
            return parse_input_field_data($string, $translate);
        }
    }
}

function output_string_protected($string) {
    return output_string($string, false, true);
}

function sanitize_string($string) {
    $string = preg_replace('/ +/', ' ', $string); // replaces multiple spaces with one space
    return preg_replace("/[<>]/", '_', $string);
}

function validate_email($email) {
    global $domain_name;
    $valid_address = true;
    $mail_pat = '/^(.+)@(.+)$/';
    $valid_chars = "[^] \(\)<>@,;:\.\\\"\[]";
    $atom = "$valid_chars+";
    $quoted_user = '("[^"]*")';
    $word = "($atom|$quoted_user)";
    $user_pat = "/^$word(\.$word)*$/";
    $ip_domain_pat = '/^\[([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\]$/';
    $domain_pat = "/^$atom(\.$atom)*$/";

    if (preg_match($mail_pat, $email, $components)) {
        $user = $components[1];
        $domain = $components[2];
        // validate user
        if (preg_match($user_pat, $user)) {
            // validate domain
            if (preg_match($ip_domain_pat, $domain, $ip_components)) {
                // this is an IP address
                for ($i = 1; $i <= 4; $i++) {
                    if ($ip_components[$i] > 255) {
                        $valid_address = false;
                        break;
                    }
                }
            } else {
                // Domain is a name, not an IP
                if (preg_match($domain_pat, $domain)) {
                    $domain_components = explode(".", $domain);
                    if (sizeof($domain_components) < 2) {
                        $valid_address = false;
                    } else {
                        $top_level_domain = strtolower($domain_components[sizeof($domain_components) - 1]);
                        // Allow all 2-letter TLDs (ccTLDs)
                        if (!preg_match('/^[a-z][a-z]$/', $top_level_domain)) {
                            $tld_pattern = '';
                            // Get authorized TLDs from text file
                            $tlds = file('admin/includes/functions/tld.txt');
                            foreach ($tlds as $line) {
                                $words = explode('#', $line);
                                $tld = trim($words[0]);
                                if (preg_match('/^[a-z]{3,}$/', $tld)) {
                                    $tld_pattern .= '^' . $tld . '$|';
                                }
                            }
                            $tld_pattern = substr($tld_pattern, 0, -1);
                            if (!preg_match("/$tld_pattern/", $top_level_domain)) {
                                $valid_address = false;
                            }
                        }
                    }
                } else {
                    $valid_address = false;
                }
            }
        } else {
            $valid_address = false;
        }
    } else {
        $valid_address = false;
    }
    if ($valid_address) {
        if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
            $valid_address = false;
        }
        $domain_name = $domain;
    }

    return $valid_address;
}

function validate_password($plain, $encrypted) {
    if (not_null($plain) && not_null($encrypted)) {
        $stack = explode(':', $encrypted);
        if (sizeof($stack) != 2) return false;
        if (md5($stack[1] . $plain) == $stack[0]) {
            return true;
        }
    }
    return false;
}

function encrypt_password($plain) {
    $password = '';
    for ($i = 0; $i < 10; $i++) {
        $password .= random_number();
    }
    $salt = substr(md5($password), 0, 2);
    $password = md5($salt . $plain) . ':' . $salt;
    return $password;
}

function get_ip_address() {
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else {
            $ip = getenv('REMOTE_ADDR');
        }
    }
    return $ip;
}

function string_to_int($string) {
    return (int)$string;
}

function array_to_string($array, $exclude = '', $equals = '=', $separator = '&') {
    if (!is_array($exclude)) $exclude = array();
    $get_string = '';
    if (sizeof($array) > 0) {
        foreach ($array as $key => $value) {
            if ((!in_array($key, $exclude)) && ($key != 'x') && ($key != 'y')) {
                $get_string .= $key . $equals . $value . $separator;
            }
        }
        $remove_chars = strlen($separator);
        $get_string = substr($get_string, 0, -$remove_chars);
    }
    return $get_string;
}

function create_random_value($length, $type = 'mixed') {
    if (($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) return false;
    $rand_value = '';
    while (strlen($rand_value) < $length) {
        if ($type == 'digits') {
            $char = random_number(0,9);
        } else {
            $char = chr(random_number(0,255));
        }
        if ($type == 'mixed') {
            if (preg_match('/^[a-z0-9]$/i', $char)) $rand_value .= $char;
        } elseif ($type == 'chars') {
            if (preg_match('/^[a-z]$/i', $char)) $rand_value .= $char;
        } elseif ($type == 'digits') {
            if (preg_match('/^[0-9]$/', $char)) $rand_value .= $char;
        }
    }
    return $rand_value;
}

function word_count($string, $needle) {
    $temp_array = explode($needle, $string);
    return sizeof($temp_array);
}

function count_modules($modules = '') {
    $count = 0;
    if (empty($modules)) return $count;
    $modules_array = explode(';', $modules);
    for ($i = 0, $n = sizeof($modules_array); $i < $n; $i++) {
        $class = substr($modules_array[$i], 0, strrpos($modules_array[$i], '.'));
        if (is_object($GLOBALS[$class])) {
            if ($GLOBALS[$class]->enabled) {
                $count++;
            }
        }
    }
    return $count;
}

function function_checkdate($date_to_check, $format_string, &$date_array) {
    $separator_idx = -1;
    $separators = array('-', ' ', '/', '.');
    $month_abbr = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
    $no_of_days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    $format_string = strtolower($format_string);

    if (strlen($date_to_check) != strlen($format_string)) {
        return false;
    }

    $size = sizeof($separators);
    for ($i=0; $i<$size; $i++) {
        $pos_separator = strpos($date_to_check, $separators[$i]);
        if ($pos_separator !== false) {
            $date_separator_idx = $i;
            break;
        }
    }

    for ($i=0; $i<$size; $i++) {
        $pos_separator = strpos($format_string, $separators[$i]);
        if ($pos_separator !== false) {
            $format_separator_idx = $i;
            break;
        }
    }

    if ($date_separator_idx != $format_separator_idx) {
        return false;
    }

    if ($date_separator_idx != -1) {
        $format_string_array = explode($separators[$date_separator_idx], $format_string);
        if (sizeof($format_string_array) != 3) {
            return false;
        }

        $date_to_check_array = explode($separators[$date_separator_idx], $date_to_check);
        if (sizeof($date_to_check_array) != 3) {
            return false;
        }

        $size = sizeof($format_string_array);
        for ($i=0; $i<$size; $i++) {
            if ($format_string_array[$i] == 'mm' || $format_string_array[$i] == 'mmm') $month = $date_to_check_array[$i];
            if ($format_string_array[$i] == 'dd') $day = $date_to_check_array[$i];
            if (($format_string_array[$i] == 'yyyy') || ($format_string_array[$i] == 'aaaa')) $year = $date_to_check_array[$i];
        }
    } else {
        if (strlen($format_string) == 8 || strlen($format_string) == 9) {
            $pos_month = strpos($format_string, 'mmm');
            if ($pos_month !== false) {
                $month = substr($date_to_check, $pos_month, 3);
                $size = sizeof($month_abbr);
                for ($i=0; $i<$size; $i++) {
                    if ($month == $month_abbr[$i]) {
                        $month = $i;
                        break;
                    }
                }
            } else {
                $month = substr($date_to_check, strpos($format_string, 'mm'), 2);
            }
        } else {
            return false;
        }

        $day = substr($date_to_check, strpos($format_string, 'dd'), 2);
        $year = substr($date_to_check, strpos($format_string, 'yyyy'), 4);
    }

    if (strlen($year) != 4) {
        return false;
    }

    if (!settype($year, 'integer') || !settype($month, 'integer') || !settype($day, 'integer')) {
        return false;
    }

    if ($month > 12 || $month < 1) {
        return false;
    }

    if ($day < 1) {
        return false;
    }

    if (is_leap_year($year)) {
        $no_of_days[1] = 29;
    }

    if ($day > $no_of_days[$month - 1]) {
        return false;
    }

    $date_array = array($year, $month, $day);

    return true;
}

function is_leap_year($year) {
    if ($year % 100 == 0) {
        if ($year % 400 == 0) return true;
    } else {
        if (($year % 4) == 0) return true;
    }
    return false;
}

// ----------- DEPRECATED FIX: Modern parameter order: &$objects, $search_str = ''
function parse_search_string(&$objects, $search_str = '') {
    $search_str = trim(strtolower($search_str));
    // Break up $search_str on whitespace; quoted string will be reconstructed later
    $pieces = preg_split('/\s+/', $search_str);
    $objects = array();
    $tmpstring = '';
    $flag = '';

    for ($k = 0; $k < count($pieces); $k++) {
        while (substr($pieces[$k], 0, 1) == '(') {
            $objects[] = '(';
            if (strlen($pieces[$k]) > 1) {
                $pieces[$k] = substr($pieces[$k], 1);
            } else {
                $pieces[$k] = '';
            }
        }

        $post_objects = array();

        while (substr($pieces[$k], -1) == ')') {
            $post_objects[] = ')';
            if (strlen($pieces[$k]) > 1) {
                $pieces[$k] = substr($pieces[$k], 0, -1);
            } else {
                $pieces[$k] = '';
            }
        }

        // Check individual words
        if ((substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"')) {
            $objects[] = trim($pieces[$k]);
            for ($j = 0; $j < count($post_objects); $j++) {
                $objects[] = $post_objects[$j];
            }
        } else {
            $tmpstring = trim(str_replace('"', ' ', $pieces[$k]));
            if (substr($pieces[$k], -1) == '"') {
                $flag = 'off';
                $objects[] = trim($pieces[$k]);
                for ($j = 0; $j < count($post_objects); $j++) {
                    $objects[] = $post_objects[$j];
                }
                unset($tmpstring);
                continue;
            }
            $flag = 'on';
            $k++;
            while (($flag == 'on') && ($k < count($pieces))) {
                while (substr($pieces[$k], -1) == ')') {
                    $post_objects[] = ')';
                    if (strlen($pieces[$k]) > 1) {
                        $pieces[$k] = substr($pieces[$k], 0, -1);
                    } else {
                        $pieces[$k] = '';
                    }
                }
                if (substr($pieces[$k], -1) != '"') {
                    $tmpstring .= ' ' . $pieces[$k];
                    $k++;
                    continue;
                } else {
                    $tmpstring .= ' ' . trim(str_replace('"', ' ', $pieces[$k]));
                    $objects[] = trim($tmpstring);
                    for ($j = 0; $j < count($post_objects); $j++) {
                        $objects[] = $post_objects[$j];
                    }
                    unset($tmpstring);
                    $flag = 'off';
                }
            }
        }
    }

    // add default logical operators if needed
    $temp = array();
    for ($i = 0; $i < (count($objects) - 1); $i++) {
        $temp[] = $objects[$i];
        if (
            ($objects[$i] != 'and') &&
            ($objects[$i] != 'or') &&
            ($objects[$i] != '(') &&
            ($objects[$i + 1] != 'and') &&
            ($objects[$i + 1] != 'or') &&
            ($objects[$i + 1] != ')')
        ) {
            $temp[] = ADVANCED_SEARCH_DEFAULT_OPERATOR;
        }
    }
    $temp[] = $objects[$i];
    $objects = $temp;

    $keyword_count = 0;
    $operator_count = 0;
    $balance = 0;
    for ($i = 0; $i < count($objects); $i++) {
        if ($objects[$i] == '(') $balance--;
        if ($objects[$i] == ')') $balance++;
        if (($objects[$i] == 'and') || ($objects[$i] == 'or')) {
            $operator_count++;
        } elseif (($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')')) {
            $keyword_count++;
        }
    }

    if (($operator_count < $keyword_count) && ($balance == 0)) {
        return true;
    } else {
        return false;
    }
}

  function date_short($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || empty($raw_date) ) return false;

    $year = substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
      return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
      return ereg_replace('2037' . '$', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));
    }
  }

  function date_long($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || ($raw_date == '') ) return false;

    $year = (int)substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    return strftime(DATE_FORMAT_LONG, mktime($hour,$minute,$second,$month,$day,$year));
  }

 

  function function_round($number, $precision) {
    if (strpos($number, '.') && (strlen(substr($number, strpos($number, '.')+1)) > $precision)) {
      $number = substr($number, 0, strpos($number, '.') + 1 + $precision + 1);

      if (substr($number, -1) >= 5) {
        if ($precision > 1) {
          $number = substr($number, 0, -1) + ('0.' . str_repeat(0, $precision-1) . '1');
        } elseif ($precision == 1) {
          $number = substr($number, 0, -1) + 0.1;
        } else {
          $number = substr($number, 0, -1) + 1;
        }
      } else {
        $number = substr($number, 0, -1);
      }
    }

    return $number;
  }

  function get_all_get_params($exclude_array = '') {
    global $_GET;

    if (!is_array($exclude_array)) $exclude_array = array();

    $get_url = '';
    if (is_array($_GET) && (sizeof($_GET) > 0)) {
      reset($_GET);
      while (list($key, $value) = each($_GET)) {
        if ( (strlen($value) > 0) && ($key != function_session_name()) && ($key != 'error') && (!in_array($key, $exclude_array)) && ($key != 'x') && ($key != 'y') ) {
          $get_url .= $key . '=' . rawurlencode(stripslashes($value)) . '&';
        }
      }
    }

    return $get_url;
  }

  function random_select($query) {
    $random_product = '';
    $random_query = tep_db_query($query);
    $num_rows = tep_db_num_rows($random_query);
    if ($num_rows > 0) {
      $random_row = random_number(0, ($num_rows - 1));
      tep_db_data_seek($random_query, $random_row);
      $random_product = tep_db_fetch_array($random_query);
    }

    return $random_product;
  }



  function redirect($url) {
    if ( (strstr($url, "\n") != false) || (strstr($url, "\r") != false) ) { 
      redirect(href_link('index.php', '', 'NONSSL', false));
    }

    
//echo $url;
    header('Location: ' . $url);

    exit;
  }


  function function_exit() {
   close_session();
   exit();
  }

 

  function href_link($page = '', $parameters = '', $connection = 'NONSSL') {
	  $link='';
  if ($page == '') {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine the page link!<br><br>Function used:<br><br>href_link(\'' . $page . '\', \'' . $parameters . '\', \'' . $connection . '\')</b>');
    }

    
if ($parameters == '') {
      $link = $link . $page . '?' . SID;
    } else {
      $link = $link . $page . '?' . $parameters . '&' . SID;
    }

   while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

    return $link;
  }




  /*function create_image($src, $alt = '', $width = '', $height = '', $parameters = '') {
    if ( empty($src) || ($src == DIR_WS_IMAGES)) {
      return false;
    }

// alt is added to the img tag even if it is null to prevent browsers from outputting
// the image filename as default
    $image = '<img src="' . output_string($src) . '" border="0" alt="' . output_string($alt) . '"';

    if (not_null($alt)) {
      $image .= ' title=" ' . output_string($alt) . ' "';
    }
		$calculate_image_size=true;
    if ( ($calculate_image_size == 'true') && (empty($width) || empty($height)) ) {
      if ($image_size = @getimagesize($src)) {
        if (empty($width) && not_null($height)) {
          $ratio = $height / $image_size[1];
          $width = intval($image_size[0] * $ratio);
        } elseif (not_null($width) && empty($height)) {
          $ratio = $width / $image_size[0];
          $height = intval($image_size[1] * $ratio);
        } elseif (empty($width) && empty($height)) {
          $width = $image_size[0];
          $height = $image_size[1];
        }
		
      } elseif (IMAGE_REQUIRED == 'false') {
        return false;
      }
    }

    if (not_null($width) && not_null($height)) {
      $image .= ' width="' . output_string($width) . '" height="' . output_string($height) . '"';
    }

    if (not_null($parameters)) $image .= ' ' . $parameters;

    $image .= '>';

    return $image;
  }*/

function session_expire_check()
		{
			//isset($_SESSION) && array_key_exists($variable, $_SESSION)
			// session not exist
			$key=array_search(session_id(),$_SESSION['user']);
			if(!$key)
			{
				// create
				$u='n';
			}
			else
			{
				$user=$key;
				//  if not valid current session
				if($_SESSION['user']==0 && time() >= $_SESSION['user']["expires"])
				{
					// destroy
					session_kill();
				}
				else
				{
					// add 45 minutes more expiration
					$_SESSION['user']["expires"] = (time()+(45*60));
				}
			}
		}


 function session_create($user_id)
		{
			$_SESSION['user']=$user_id;
			$_SESSION['user']['sessid']=session_id();
			$_SESSION['user']['expires']=time()+(45*60);
			//$_SESSION["expires"] = time()+(45*60);

			
		}
	// destroy session
	 function session_kill()
		{
			// if session is active
			if (isset($_SESSION["user"]) && $_SESSION["user"]!= 0)
			{
				// clean session
				$_SESSION = array();
				// create new
				session_create();
			}
			return true;
		}


		



		 function GetFileExtension($s)
	{
		return substr($s, strripos($s, '.') + 1);
	}
	// get file name withour extension
function GetFileNoExt($s)
	{
		return substr($s,0,(strlen($s)-(strlen(GetFileExtension($s))+1)));
	}
	// get file name with extension
	function GetFileNameWithExt($s)
	{
		$current = strtolower(str_replace('\\','/',$s));
		return  substr($current,strrpos($current,'/')+1);
	}
		
	// check authentication
	function checkauth()
	{
		// if logout request
		if(!(empty($_GET['a'])) && $_GET['a']=='logout'){session_kill();}
		// check expiration
		session_expire_check();
		// get current page name
		$curFile = GetFileNoExt(GetFileNameWithExt($_SERVER["SCRIPT_NAME"]));
		// if not valid session and current page is not login page
		// redirect to login page
		if($_SESSION["user"]==0 && $curFile != 'login')
		{
			
		}
	}

	function image_submit($image, $alt = '', $parameters = '') {
    global $language;

    $image_submit = '<input type="image" src="' . output_string('/images/buttons/' . $image) . '" border="0" alt="' . output_string($alt) . '"';

    if (not_null($alt)) $image_submit .= ' title=" ' . output_string($alt) . ' "';

    if (not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= '>';

    return $image_submit;
  }

  function black_line() {
    return image('images/pixel_black.gif', '', '100%', '1');
  }

  function image_button($image, $alt = '', $params = '') {
    global $language;

    return image('images/buttons/' . $image, $alt, '', '', $params);
  }

  function draw_form($name, $action, $parameters = '', $method = 'post', $params = '') {
    $form = '<form name="' . output_string($name) . '" action="';
    if (not_null($parameters)) {
      $form .= href_link($action, $parameters);
    } else {
      $form .= href_link($action);
    }
    $form .= '" method="' . output_string($method) . '"';
    if (not_null($params)) {
      $form .= ' ' . $params;
    }
    $form .= '>';

    return $form;
  }


  function draw_input_field($name, $value = '', $parameters = '', $required = false, $type = 'text', $reinsert_value = true) {
    

    $field = '<input type="' . output_string($type) . '" name="' . output_string($name) . '"';

    if ( ($reinsert_value == true) && ( (isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])) ) ) {
      if (isset($_GET[$name]) && is_string($_GET[$name])) {
        $value = stripslashes($_GET[$name]);
      } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
        $value = stripslashes($_POST[$name]);
      }
    }

    if (not_null($value)) {
      $field .= ' value="' . output_string($value) . '"';
    }

    if (not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }


  function draw_password_field($name, $value = '', $required = false) {
    $field = draw_input_field($name, $value, 'maxlength="40"', $required, 'password', false);

    return $field;
  }

  function draw_file_field($name, $required = false) {
    $field = draw_input_field($name, '', '', $required, 'file');

    return $field;
  }

function draw_selection_field($name, $type, $value = '', $checked = false, $compare = '') {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $selection = '<input type="' . output_string($type) . '" name="' . output_string($name) . '"';

    if (not_null($value)) $selection .= ' value="' . output_string($value) . '"';

    if ( ($checked == true) || (isset($_GET[$name]) && is_string($_GET[$name]) && (($_GET[$name] == 'on') || (stripslashes($_GET[$name]) == $value))) || (isset($_POST[$name]) && is_string($_POST[$name]) && (($_POST[$name] == 'on') || (stripslashes($_POST[$name]) == $value))) || (not_null($compare) && ($value == $compare)) ) {
      $selection .= ' CHECKED';
    }

    $selection .= '>';

    return $selection;
  }

   function draw_checkbox_field($name, $value = '', $checked = false, $compare = '') {
    return draw_selection_field($name, 'checkbox', $value, $checked, $compare);
  }

  function draw_radio_field($name, $value = '', $checked = false, $compare = '') {
    return draw_selection_field($name, 'radio', $value, $checked, $compare);
  }

  function draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
   

    $field = '<textarea name="' . output_string($name) . '" wrap="' . output_string($wrap) . '" cols="' . output_string($width) . '" rows="' . output_string($height) . '"';

    if (not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ( ($reinsert_value == true) && ( (isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($HTTP_POST_VARS[$name])) ) ) {
      if (isset($_GET[$name]) && is_string($_GET[$name])) {
        $field .= Output_string_protected(stripslashes($_GET[$name]));
      } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
        $field .= output_string_protected(stripslashes($_POST[$name]));
      }
    } elseif (not_null($text)) {
      $field .= output_string_protected($text);
    }

    $field .= '</textarea>';

    return $field;
  }


  function draw_hidden_field($name, $value = '', $parameters = '') {
   

    $field = '<input type="hidden" name="' . output_string($name) . '"';

    if (not_null($value)) {
      $field .= ' value="' . output_string($value) . '"';
    } elseif ( (isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])) ) {
      if ( (isset($_GET[$name]) && is_string($HTTP_GET_VARS[$name])) ) {
        $field .= ' value="' . output_string(stripslashes($_GET[$name])) . '"';
      } elseif ( (isset($_POST[$name]) && is_string($_POST[$name])) ) {
        $field .= ' value="' . output_string(stripslashes($_POST[$name])) . '"';
      }
    }

    if (not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    return $field;
  }
function hide_session_id() {
    $string = '';

    if (defined('SID') && not_null(SID)) {
      $string = draw_hidden_field(function_session_name(), function_session_id());
    }

    return $string;
  }

  function draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
   

    $field = '<select name="' . output_string($name) . '"';

    if (not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && ( (isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])) ) ) {
      if (isset($_GET[$name]) && is_string($_GET[$name])) {
        $default = stripslashes($_GET[$name]);
      } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
        $default = stripslashes($_POST[$name]);
      }
    }

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' SELECTED';
      }

      $field .= '>' . output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }


  function image($src, $alt = '', $width = '', $height = '', $parameters = '') {
    $image = '<img src="' . output_string($src) . '" border="0" alt="' . output_string($alt) . '"';

    if (not_null($alt)) {
      $image .= ' title=" ' . output_string($alt) . ' "';
    }

    if (not_null($width) && not_null($height)) {
      $image .= ' width="' . output_string($width) . '" height="' . output_string($height) . '"';
    }

    if (not_null($parameters)) $image .= ' ' . $parameters;

    $image .= '>';

    return $image;
  }

  function not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if ( (is_string($value) || is_int($value)) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }

  function sets_time_limit($limit) {
    if (!get_cfg_var('safe_mode')) {
      set_time_limit($limit);
    }
  }

   function get_system_information() {
    global $db;
    $db_query = $db->query_one("select now() as datetime");
    //$db = tep_db_fetch_array($db_query);

    list($system, $host, $kernel) = preg_split('/[\s,]+/', @exec('uname -a'), 5);

    return array('date' => datetime_short(date('Y-m-d H:i:s')),
                 'system' => $system,
                 'kernel' => $kernel,
                 'host' => $host,
                 'ip' => gethostbyname($host),
                 'uptime' => @exec('uptime'),
                 'http_server' => $_SERVER['SERVER_SOFTWARE'],
                 'php' => PHP_VERSION,
                 'zend' => (function_exists('zend_version') ? zend_version() : ''),
                 'db_server' => DB_SERVER,
                 'db_ip' => gethostbyname(DB_SERVER),
                 //'db_version' => 'MySQL ' . (function_exists('mysql_get_server_info') ? mysql_get_server_info() : ''),
                 'db_date' => datetime_short($db->datetime));
  }

  function datetime_short($raw_datetime) {
    if ( ($raw_datetime == '0000-00-00 00:00:00') || ($raw_datetime == '') ) return false;

    $year = (int)substr($raw_datetime, 0, 4);
    $month = (int)substr($raw_datetime, 5, 2);
    $day = (int)substr($raw_datetime, 8, 2);
    $hour = (int)substr($raw_datetime, 11, 2);
    $minute = (int)substr($raw_datetime, 14, 2);
    $second = (int)substr($raw_datetime, 17, 2);

    return strftime(DATE_TIME_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
  }

  function remove_file($source) {
    

    if (isset($remove_error)) $remove_error = false;

    if (is_dir($source)) {
      $dir = dir($source);
      while ($file = $dir->read()) {
        if ( ($file != '.') && ($file != '..') ) {
          if (is_writeable($source . '/' . $file)) {
            remove_file($source . '/' . $file);
          } else {
            $message='Error';
            $remove_error = true;
          }
        }
      }
      $dir->close();

      if (is_writeable($source)) {
        rmdir($source);
      } else {
        $message='Error';
        $remove_error = true;
      }
    } else {
      if (is_writeable($source)) {
        unlink($source);
      } else {
        $message='Eror';
        $remove_error = true;
      }
    }
  }


function embed_video($src = '', $title = '', $width = '', $height = ''){
	$embed_video_code = '';
	if(!empty($src)):
		$embed_video_code = '<iframe src="'.$src.'"';
		if($title != ''):
			$embed_video_code .= ' title="'.$title.'"';
		else:
			if(!empty($width) && !empty($height)):
				$embed_video_code .= ' width="'.$width.'" height="'.$height.'"';
			else:
				$embed_video_code .= ' width="240" height="205"';
			endif;
		endif;
		$embed_video_code .= ' frameborder="0" allowFullScreen></iframe>';
		//$embed_video_code .= '</iframe>';
		return $embed_video_code;
	else:
		return false;
	endif;
}

?>