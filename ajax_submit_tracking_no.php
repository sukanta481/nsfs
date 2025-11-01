<?php
include("include/apps_top.php");
$doc_no=mysqli_real_escape_string($conn,trim($_REQUEST['doc_no']));
$get_shipping_details_sql="select * from  tbl_shipping_details where doc='".$doc_no."'";
$get_shipping_details_rs=mysqli_query($conn,$get_shipping_details_sql);
$get_shipping_details_num=mysqli_num_rows($get_shipping_details_rs);
$get_shipping_details_row=mysqli_fetch_array($get_shipping_details_rs);
?>
<table>
	<tr>
	<th>Doc No</th>
	<th>Pick Up Date</th>
	<th>Company Name</th>
	<th>Car No</th>
	<th>Delivery Agent</th>
	<th>Number</th>
	<th>Status</th>
	
	<?php
if($get_shipping_details_row['status']=='Delay')
{
?>
	<th>Reason</th>
<?php
}
?>	
	</tr>
<tr>	
<?php

if($get_shipping_details_num>0)
{

?>
<td><?= $get_shipping_details_row['doc'];?></td>
<?php
// $get_company_sql="select * from   tbl_client where client_id='".$get_shipping_details_row['client_id']."'";
$get_company_sql="select * from   tbl_shipping_details where doc='".$doc_no."'";
$get_company_rs=mysqli_query($conn,$get_company_sql);
$get_company_row=mysqli_fetch_array($get_company_rs);

?>
<!--<td><?= $get_company_row['client_title'];?></td>-->
<td><?= $get_company_row['pickup_dates'];?></td>
<td><?= $get_company_row['client_name'];?></td>
<?php
$get_car_sql="select * from tbl_car where car_id=(select car_id from  tbl_register where register_id='".$get_shipping_details_row['register_id']."')";
$get_car_rs=mysqli_query($conn,$get_car_sql);
$get_car_row=mysqli_fetch_array($get_car_rs);

?>
<td><?= $get_car_row['car_number'];?></td>
<?php
$get_helper_sql="select * from    tbl_helper where helper_id=(select helper_id from  tbl_register where register_id='".$get_shipping_details_row['register_id']."')";
$get_helper_rs=mysqli_query($conn,$get_helper_sql);
$get_helper_row=mysqli_fetch_array($get_helper_rs);

?>
<td><?= $get_helper_row['helper_name'];?></td>
<td><?= $get_helper_row['helper_number'];?></td>
<td><?= $get_shipping_details_row['status'];?></td>
<?php
if($get_shipping_details_row['status']=='Delay')
{
?>
<td><?= $get_shipping_details_row['reason_of_delay'];?></td>
<?php
}
?>
<?php	

}
else
{
?>
<td colspan="6">No Data Found</td>
<?php	
}
?>
</tr>	
</table>

