<?php
if(isset($_REQUEST['actnorder']) && isset($_REQUEST['oid']))
{
$action  = $_REQUEST['actnorder'];
$oid = $_REQUEST['oid'];
if($action == 'delorder' && !empty($oid)){
$DellocSql1 = 'DELETE FROM tbl_order WHERE order_unique_id  = "'.$oid.'"';
$DellocQuery1 = g_db_query($DellocSql1);
if($DellocQuery1){
$messages1.= showMessage('The Booking Has Been Deleted','success');
}
}
}

?>