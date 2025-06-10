<?php
include("conn.php");
$get_driver_sql="select * from   tbl_driver where driver_id='".$_REQUEST['q']."'";
$get_driver_rs=mysqli_query($conn,$get_driver_sql);
$get_driver_row=mysqli_fetch_array($get_driver_rs);

?>
 <input type="text" name="driver_number" required="required" id="driver_number" value="<?= $get_driver_row['driver_number'];?>" class="form-control col-md-7 col-xs-12"  />