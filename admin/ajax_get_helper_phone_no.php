<?php
include("conn.php");
$get_helper_sql="select * from   tbl_helper where helper_id='".$_REQUEST['q']."'";
$get_helper_rs=mysqli_query($conn,$get_helper_sql);
$get_helper_row=mysqli_fetch_array($get_helper_rs);

?>
<input type="text" name="helper_number" required="required" id="helper_number" value="<?= $get_helper_row['helper_number'];?>" class="form-control col-md-7 col-xs-12"  />