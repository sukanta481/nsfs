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

if(isset($_REQUEST['save_helper']) && $_REQUEST['save_helper']=="Save"){
	$ser_alias = alias($_REQUEST['helper_name']);	
	$ser_sql="select * from tbl_helper where alise='".$ser_alias."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{	
	
if(!empty($_FILES['helper_image']['name']))
	{

		$image_name=time().$_FILES['helper_image']['name'];
		$image_type=$_FILES['helper_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['helper_image']['size'];
		$temp_name=$_FILES['helper_image']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);

	}
	else
	{

		$image_name = 'noimage.jpg';

	}

	 $add_helper_sql="insert into tbl_helper set     
	helper_name='".mysqli_real_escape_string($conn,$_REQUEST['helper_name'])."',  
	alise='".$ser_alias."',
	helper_number='".mysqli_real_escape_string($conn,$_REQUEST['helper_number'])."', 
	active_status='".mysqli_real_escape_string($conn,$_REQUEST['active_status'])."', 
			helper_image='".$image_name."'";
			
	$add_helper_exe=mysqli_query($conn,$add_helper_sql) or die(mysqli_error($conn));
if($add_helper_exe){
$helpermsg.= showMessage("Helper has been added successfully",'success');		
}
}
else{
	$helpermsg .= showMessage('There is an item with same name.','error');
}
			 						
}

if(isset($_REQUEST['edit_helper']) && $_REQUEST['edit_helper']=="Update"){
		
$ser_alias = alias($_REQUEST['helper_name']);	
	$ser_sql="select * from tbl_helper where alise='".$ser_alias."' and helper_id!='".$_REQUEST['helper_id']."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{		
	
$edit_helper_sql1="update tbl_helper set
	helper_name='".mysqli_real_escape_string($conn,$_REQUEST['helper_name'])."', 
	alise='".$ser_alias."',
	helper_number='".mysqli_real_escape_string($conn,$_REQUEST['helper_number'])."', 
active_status='".mysqli_real_escape_string($conn,$_REQUEST['active_status'])."' 				
            where helper_id ='".$_REQUEST['helper_id']."'";
			$edit_helper_exe1=mysqli_query($conn,$edit_helper_sql1)  or die(mysqli_error($conn));	
			
			
			if(!empty($_FILES['helper_image']['name']))
			{

			$image_name=time().$_FILES['helper_image']['name'];
			$image_type=$_FILES['helper_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['helper_image']['size'];
			$temp_name=$_FILES['helper_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_helper_sql="update tbl_helper set 
			helper_image ='".$image_name."'
			where helper_id='".$_REQUEST['helper_id']."'";
			mysqli_query($conn,$edit_helper_sql)  or die(mysqli_error($conn));	

			}
			
		

if($edit_helper_exe1){
$helpermsg.= showMessage("Helper has been updated successfully",'success');		
}
}
else{
		$helpermsg .= showMessage('There is an item with same name.','error');
	}
}

$action  = $_REQUEST['actnhelper'];
$helper_id = $_REQUEST['helper_id'];
if($action == 'dellhelper' && !empty($helper_id)){
$DelhelperSql = 'DELETE FROM tbl_helper WHERE helper_id  = "'.$helper_id.'"';
$DelhelperQuery = g_db_query($DelhelperSql);
if($DelhelperQuery){
$helpermsg.= showMessage('The Helper Has Been Deleted','success');
}
}
?>