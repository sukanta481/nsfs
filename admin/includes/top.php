<?php
include 'define.php';
/* write Current Executing File */

$PHP_SELF = (isset($_SERVER['PHP_SELF'])? $_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);

/** -------------------------------*
/* Just including Database  class */
/* Create mysqli object*
* --------------------------------**/
require 'functions/database.php';
$conn=g_db_connect() or die('Unable to connect to database server!');
//require 'classes/class.MysqliDatabase.php';
//$db=MysqliDatabase::singleton() or die(" Internal Error");
//var_dump($db);
/* Just including genaral Functions */

require 'functions/function.php';

/* Just including sessions Functions */

include 'functions/sessions.php';

/* Php Validation Class*/

require 'classes/validate.class.php';

/* Pagination Class*/

require 'classes/queryList.php';

/* Mail class*/

require 'classes/class.phpmailer.php';

/* Session Name and Session Save Path  */

include 'functions/thumb_function.php';

function_session_name('pro');
function_session_save_path();
/*
if (function_exists('session_set_cookie_params')) {
    session_set_cookie_params(0, '/admin/');
  } elseif (function_exists('ini_set')) {
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.cookie_path', '/admin/');
  }*/
function_session_start();
//session_start();
$request_type='NONSSL';
$session_started = false;
  
   if ( ($session_started == true) && function_exists('ini_get') && (ini_get('register_globals') == false) ) {
    extract($_SESSION, EXTR_OVERWRITE+EXTR_REFS);
  }
$current_page = basename($PHP_SELF);
if(!check_session('admin'))
 {
	 $redirect=false;
	 $current_page= basename($PHP_SELF);
	if($current_page!='login.php')
		{
		if(!check_session('redirect_origin')) {
			function_session_register('redirect_origin');
			$redirect_origin=array('page'=>$current_page,'get'=>$_GET);
		}
		$redirect=true;
		}
		if($redirect==true)
	 {
			redirect(href_link('login.php'));
    
		}
		unset($redirect);
 }

//print_r($_SESSION);
//echo session_name();echo session_id();

/*************** Page Title ********************/

if($_GET['type'] == 'home') 	  define('P_TITLE','Home Contant');
if($_GET['type'] == 'company') 	  define('P_TITLE','Company Contant');
if($_GET['type'] == 'whyus')   define('P_TITLE','Why Us Contant');


if($_GET['type'] == 'contactus')  define('P_TITLE','Contact Us Contant');
if($_GET['type'] == 'aboutus') 	  define('P_TITLE','About Us Contant');
if($_GET['type'] == 'faq') 	      define('P_TITLE','FAQ Contant');
if($_GET['type'] == 'mailing') 	      define('P_TITLE','Mialing Contant');
if($_GET['type'] == 'serviceareas')  define('P_TITLE','Service Areas Contant');
if($_GET['type'] == 'privacypolicy')	  define('P_TITLE','Privacy Policy Content');

/*Function Show Massege with Different Style (Start)*/

function showMessage($string = '', $type = ''){
	$message_array = array(
							'success' => '<div class="success" style="margin:2px;padding:3px;"><span style="margin-left:30px;">'.$string.'</span></div>',
							'warning' => '<div class="warning" style="margin:2px;padding:3px;"><span style="margin-left:30px;">'.$string.'</span></div>',
							'error'   => '<div class="error" style="margin:2px;padding:3px;"><span style="margin-left:30px;">'.$string.'</span></div>'
						  );
	return $message_array[$type];
}

function alias($challenge){
	$alias = str_replace(' ', '-', $challenge);
	$alias = str_replace('@', '-', $alias);
	$alias = str_replace('®', '-', $alias);
	$alias = str_replace('&', '', $alias);
	$alias = str_replace('(', '', $alias);
	$alias = str_replace(')', '', $alias);
	$alias = str_replace(',', '', $alias);
	$alias = str_replace("'", '', $alias);
	$alias = str_replace('"', '', $alias);
	$alias = str_replace('?', '', $alias);
	$alias = str_replace(':', '-', $alias);
	$alias = str_replace('.', '-', $alias);
	$alias = preg_replace('/[^a-z0-9\s]/i', '-', $alias);
	$alias=strtolower($alias);
	return $alias;
}
function convert_smart_quotes($string) 
{ 
    $search = array(chr(145), 
                    chr(146), 
                    chr(147), 
                    chr(148), 
                    chr(151)); 

    $replace = array("'", 
                     "'", 
                     '"', 
                     '"', 
                     '-'); 

    return str_replace($search, $replace, $string); 
}

/*Function Show Massege with Different Style (End)*/

/*All Script Included Here*/

require_once 'script/login_code.php';
require_once 'script/post_code.php';
require_once 'script/change_pass_code.php';
require_once 'script/client_code.php';
require_once 'script/company_code.php';
require_once 'script/driver_code.php';
require_once 'script/helper_code.php';
require_once 'script/car_code.php';
require_once 'script/register_code.php';
require_once 'script/trip_code.php';
require_once 'script/cmspage_code.php';
require_once 'script/site_feature_code.php';
require_once 'script/testimonial_code.php';
require_once 'script/team_code.php';
require_once 'script/service_code.php';
require_once 'script/widget_code.php';
require_once 'script/social_code.php';
require_once 'script/contact_code.php';
require_once 'script/why_choose_code.php';
require_once 'script/gallery_code.php';
require_once 'script/gallery_category_code.php';
require_once 'script/service_category_code.php';
require_once 'script/delay_reason_code.php';
 $type = $_GET['type'];?>