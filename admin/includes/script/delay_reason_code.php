<?php
$message		= '';
$type= $_GET['type'];
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if(isset($_REQUEST['save_delay_reason']) && $_REQUEST['save_delay_reason']=="Save"){
	$ser_alias = alias($_REQUEST['delay_reason_name']);	
	$ser_sql="select * from tbl_delay_reason where alise='".$ser_alias."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{	
	
if(!empty($_FILES['delay_reason_image']['name']))
	{

		$image_name=time().$_FILES['delay_reason_image']['name'];
		$image_type=$_FILES['delay_reason_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['delay_reason_image']['size'];
		$temp_name=$_FILES['delay_reason_image']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);

	}
	else
	{

		$image_name = 'noimage.jpg';

	}

	 $add_delay_reason_sql="insert into tbl_delay_reason set     
	delay_reason_name='".mysqli_real_escape_string($conn,$_REQUEST['delay_reason_name'])."', 
	delay_reason_details='".mysqli_real_escape_string($conn,$_REQUEST['delay_reason_details'])."', 
	alise='".$ser_alias."',
	
	active_status='".mysqli_real_escape_string($conn,$_REQUEST['active_status'])."', 
			delay_reason_image='".$image_name."'";
			
	$add_delay_reason_exe=mysqli_query($conn,$add_delay_reason_sql) or die(mysqli_error($conn));
if($add_delay_reason_exe){
$delay_reasonmsg.= showMessage("Delay Reason has been added successfully",'success');		
}
}
else{
	$delay_reasonmsg .= showMessage('There is an item with same name.','error');
}
			 						
}

if(isset($_REQUEST['edit_delay_reason']) && $_REQUEST['edit_delay_reason']=="Update"){
		
$ser_alias = alias($_REQUEST['delay_reason_name']);	
	 $ser_sql="select * from tbl_delay_reason where alise='".$ser_alias."' and delay_reason_id!='".$_REQUEST['delay_reason_id']."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{		
	
$edit_delay_reason_sql1="update tbl_delay_reason set
	
	alise='".$ser_alias."',
	delay_reason_name='".mysqli_real_escape_string($conn,$_REQUEST['delay_reason_name'])."', 
	delay_reason_details='".mysqli_real_escape_string($conn,$_REQUEST['delay_reason_details'])."', 
active_status='".mysqli_real_escape_string($conn,$_REQUEST['active_status'])."' 				
            where delay_reason_id ='".$_REQUEST['delay_reason_id']."'";
			$edit_delay_reason_exe1=mysqli_query($conn,$edit_delay_reason_sql1)  or die(mysqli_error($conn));	
			
			
			if(!empty($_FILES['delay_reason_image']['name']))
			{

			$image_name=time().$_FILES['delay_reason_image']['name'];
			$image_type=$_FILES['delay_reason_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['delay_reason_image']['size'];
			$temp_name=$_FILES['delay_reason_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_delay_reason_sql="update tbl_delay_reason set 
			delay_reason_image ='".$image_name."'
			where delay_reason_id='".$_REQUEST['delay_reason_id']."'";
			mysqli_query($conn,$edit_delay_reason_sql)  or die(mysqli_error($conn));	

			}
			
		

if($edit_delay_reason_exe1){
$delay_reasonmsg.= showMessage("Delay Reason has been updated successfully",'success');		
}
}
else{
		$delay_reasonmsg .= showMessage('There is an item with same name.','error');
	}
}

$action  = $_REQUEST['actndelay_reason'];
$delay_reason_id = $_REQUEST['delay_reason_id'];
if($action == 'delldelay_reason' && !empty($delay_reason_id)){
$Deldelay_reasonSql = 'DELETE FROM tbl_delay_reason WHERE delay_reason_id  = "'.$delay_reason_id.'"';
$Deldelay_reasonQuery = g_db_query($Deldelay_reasonSql);
if($Deldelay_reasonQuery){
$delay_reasonmsg.= showMessage('The Delay Reason Has Been Deleted','success');
}
}
?>