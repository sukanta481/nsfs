<?php
require 'conn.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'delete_register':
        $register_id = intval($_GET['register_id'] ?? 0);
        if ($register_id > 0) {
            mysqli_query($conn, "DELETE FROM tbl_register WHERE register_id=$register_id");
            // Optionally, delete associated shipping/shipping_details too
        }
        header("Location: register.php?type=list_register&msg=deleted");
        exit;
   case 'delete_shipping_details':
    $id = intval($_GET['shipping_details_id'] ?? 0);
    // Rebuild query string to preserve filters
    $qs = $_GET;
    unset($qs['action'], $qs['shipping_details_id']);
    $qs['msg'] = 'deleted';
    $redir = 'list_trip.php';
    if (!empty($qs)) $redir .= '?' . http_build_query($qs);
    if ($id > 0) {
        mysqli_query($conn, "DELETE FROM tbl_shipping_details WHERE shipping_details_id=$id");
    }
    header("Location: trip.php?type=list_trip&status=Delivered&msg=deleted");
    exit;


    // Add more cases for other actions (edit, update, etc)
    default:
        header("Location: index.php");
        exit;
}
?>
