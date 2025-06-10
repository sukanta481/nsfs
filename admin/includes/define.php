<?php
//session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
define('SESSION_BLOCK_SPIDERS',true);
//define('STORE_SESSIONS','mysql');
//define('SESSION_WRITE_DIRECTORY','mysql');
define('SESSION_CHECK_USER_AGENT',false);
define('SESSION_CHECK_IP_ADDRESS',false);
define('STORE_SESSIONS','mysql');
define('SESSION_WRITE_DIRECTORY','');
define('SESSION_FORCE_COOKIE_USE',false);
define('ENABLE_SSL','False');
define('TEXT_FIELD_REQUIRED', '&nbsp;<span class="fieldRequired">* Required</span>');
define('DATE_FORMAT_SHORT', '%m/%d/%Y');
define('DATE_FORMAT_LONG', '%A %d %B, %Y');
define('DATE_FORMAT', 'm/d/Y');
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');
define('USE_PCONNECT', false);

define('SITE_URL', 'https://northsuperfastservice.com/');
/* DATABASE INFORMATION*/
//if($_SERVER['HTTP_HOST'] == 'localhost'):
/***************FOR LOCAL****************/
define('DB_SERVER','127.0.0.1');
define('DB_SERVER_USERNAME','u286257250_north');
define('DB_SERVER_PASSWORD','North@2024');
define('DB_DATABASE','u286257250_north');


/***************FOR LOCAL****************/
/*else:*/
/***************FOR SERVER****************/
/*define('DB_SERVER','localhost');
define('DB_SERVER_USERNAME','nextscre_kaos');
define('DB_SERVER_PASSWORD','3Gmfxe79(X6D');
define('DB_DATABASE','nextscre_kaosforu');*/
/***************FOR SERVER****************/
/*endif;*/


define('STORE_DB_TRANSACTIONS','false');
define('STORE_PAGE_PARSE_TIME_LOG','');
define('TABLE_PREFIX','tbl_');
/* Error Reporting*/
error_reporting(E_ALL^E_NOTICE);
