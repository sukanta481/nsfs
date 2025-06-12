<?php
// === DEBUG OUTPUT: Show session data on every admin page for debugging ===
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Set custom session name (do this BEFORE session_start)
session_name('pro');

// 2. Start session FIRST, before ANY output!
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- DEBUG: Remove in production ---
// echo "<pre style='color:red;'>SESSION DEBUG [top.php]\n";
// echo "Session Name: " . session_name() . "\n";
// echo "Session ID: " . session_id() . "\n";
// print_r($_SESSION);
// echo "</pre>";

include 'define.php';

$PHP_SELF = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

// Database
require 'functions/database.php';
$conn = g_db_connect() or die('Unable to connect to database server!');

// Helpers
require 'functions/function.php';
include 'functions/sessions.php';
require 'classes/validate.class.php';
require 'classes/queryList.php';
require 'classes/class.phpmailer.php';
include 'functions/thumb_function.php';

// --- PAGE PROTECTION: Force login if not authenticated ---
// Add all public pages that do NOT require login
$public_pages = [
    'login.php',
    'signup.php',
    'forget_password.php',
    'reset_password.php'
];
$current_page = basename($PHP_SELF);

if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
    if (!in_array($current_page, $public_pages)) {
        // Store redirect origin if not set
        if (!isset($_SESSION['redirect_origin'])) {
            $_SESSION['redirect_origin'] = [
                'page' => $current_page,
                'get' => $_GET
            ];
        }
        header("Location: login.php");
        exit;
    }
}

// --- PAGE TITLE LOGIC ---
if (isset($_GET['type'])) {
    switch ($_GET['type']) {
        case 'home':
            define('P_TITLE', 'Home Content');
            break;
        case 'company':
            define('P_TITLE', 'Company Content');
            break;
        case 'whyus':
            define('P_TITLE', 'Why Us Content');
            break;
        case 'contactus':
            define('P_TITLE', 'Contact Us Content');
            break;
        case 'aboutus':
            define('P_TITLE', 'About Us Content');
            break;
        case 'faq':
            define('P_TITLE', 'FAQ Content');
            break;
        case 'mailing':
            define('P_TITLE', 'Mailing Content');
            break;
        case 'serviceareas':
            define('P_TITLE', 'Service Areas Content');
            break;
        case 'privacypolicy':
            define('P_TITLE', 'Privacy Policy Content');
            break;
    }
}

// --- Utility Functions (still available) ---
function showMessage($string = '', $type = '') {
    $message_array = [
        'success' => '<div class="success" style="margin:2px;padding:3px;"><span style="margin-left:30px;">' . $string . '</span></div>',
        'warning' => '<div class="warning" style="margin:2px;padding:3px;"><span style="margin-left:30px;">' . $string . '</span></div>',
        'error'   => '<div class="error" style="margin:2px;padding:3px;"><span style="margin-left:30px;">' . $string . '</span></div>'
    ];
    return $message_array[$type] ?? '';
}

function alias($challenge) {
    $alias = str_replace([' ', '@', '®', '&', '(', ')', ',', "'", '"', '?', ':', '.'], '-', $challenge);
    $alias = preg_replace('/[^a-z0-9\s-]/i', '-', $alias);
    return strtolower($alias);
}
function convert_smart_quotes($string) {
    $search = [chr(145), chr(146), chr(147), chr(148), chr(151)];
    $replace = ["'", "'", '"', '"', '-'];
    return str_replace($search, $replace, $string);
}

// --- All Script Includes ---
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

// No $type assignment needed here
?>
