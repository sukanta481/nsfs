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

    case 'delete_docket':
        // Check permission for delete
        requirePermission('docket_delete');
        
        $docket_id = intval($_GET['docket_id'] ?? 0);
        
        if ($docket_id > 0) {
            // Get docket number before deleting
            $check_query = mysqli_query($conn, "SELECT doc_no FROM docket_details WHERE docket_id=$docket_id");
            $docket_data = mysqli_fetch_assoc($check_query);
            
            if($docket_data) {
                $doc_no = $docket_data['doc_no'];
                
                // Delete the docket
                if(mysqli_query($conn, "DELETE FROM docket_details WHERE docket_id=$docket_id")) {
                    // Success - redirect with deleted message
                    header("Location: register.php?type=list_register&lp=ac&deleted=1&doc_no=" . urlencode($doc_no));
                    exit;
                } else {
                    // Error - redirect with error message
                    header("Location: register.php?type=list_register&lp=ac&error=" . urlencode('Failed to delete docket'));
                    exit;
                }
            } else {
                // Docket not found
                header("Location: register.php?type=list_register&lp=ac&error=" . urlencode('Docket not found'));
                exit;
            }
        } else {
            // Invalid ID
            header("Location: register.php?type=list_register&lp=ac&error=" . urlencode('Invalid docket ID'));
            exit;
        }
        break;


    case 'delete_manifest':
        $manifest_id = intval($_GET['manifest_id'] ?? 0);

        if ($manifest_id > 0) {
            // Get manifest number before deleting
            $check_query = mysqli_query($conn, "SELECT manifest_no FROM tbl_manifest WHERE manifest_id=$manifest_id");
            $manifest_data = mysqli_fetch_assoc($check_query);

            if($manifest_data) {
                $manifest_no = $manifest_data['manifest_no'];

                // First delete all docket details associated with this manifest
                mysqli_query($conn, "DELETE FROM tbl_manifest_details WHERE manifest_id=$manifest_id");

                // Then delete the manifest itself
                if(mysqli_query($conn, "DELETE FROM tbl_manifest WHERE manifest_id=$manifest_id")) {
                    // Success - redirect with deleted message
                    header("Location: manifest.php?type=list_manifest&msg=deleted&manifest_no=" . urlencode($manifest_no));
                    exit;
                } else {
                    // Error - redirect with error message
                    header("Location: manifest.php?type=list_manifest&error=" . urlencode('Failed to delete manifest'));
                    exit;
                }
            } else {
                // Manifest not found
                header("Location: manifest.php?type=list_manifest&error=" . urlencode('Manifest not found'));
                exit;
            }
        } else {
            // Invalid ID
            header("Location: manifest.php?type=list_manifest&error=" . urlencode('Invalid manifest ID'));
            exit;
        }
        break;

    // Add more cases for other actions (edit, update, etc)
    default:
        header("Location: index.php");
        exit;
}
?>
