<?php
$message		= '';
$type= $_GET['type'];
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );

if(isset($_REQUEST['save_client']) && $_REQUEST['save_client']=="Save"){
	$ser_alias = alias($_REQUEST['client_title']);	
	$ser_sql="select * from tbl_client where alise='".$ser_alias."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{	
	
if(!empty($_FILES['client_image']['name']))
	{

		$image_name=time().$_FILES['client_image']['name'];
		$image_type=$_FILES['client_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['client_image']['size'];
		$temp_name=$_FILES['client_image']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);

	}
	else
	{

		$image_name = 'noimage.jpg';

	}

	 $add_client_sql="insert into tbl_client set     
	client_title='".mysqli_real_escape_string($conn,$_REQUEST['client_title'])."',
	client_address='".mysqli_real_escape_string($conn,$_REQUEST['client_address'])."',  
	client_phone='".mysqli_real_escape_string($conn,$_REQUEST['client_phone'])."',    
	alise='".$ser_alias."',
	
			client_image='".$image_name."'";
			
	$add_client_exe=mysqli_query($conn,$add_client_sql) or die(mysqli_error($conn));
if($add_client_exe){
$clientmsg.= showMessage("Company has been added successfully",'success');		
}
}
else{
	$clientmsg .= showMessage('There is an item with same name.','error');
}
			 						
}

if(isset($_REQUEST['edit_client']) && $_REQUEST['edit_client']=="Update"){
		
$ser_alias = alias($_REQUEST['client_title']);	
	$ser_sql="select * from tbl_client where alise='".$ser_alias."' and client_id!='".$_REQUEST['client_id']."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{		
	
$edit_client_sql1="update tbl_client set
	client_title='".mysqli_real_escape_string($conn,$_REQUEST['client_title'])."', 
	client_address='".mysqli_real_escape_string($conn,$_REQUEST['client_address'])."',  
	client_phone='".mysqli_real_escape_string($conn,$_REQUEST['client_phone'])."',    
	alise='".$ser_alias."'
				
            where client_id ='".$_REQUEST['client_id']."'";
			$edit_client_exe1=mysqli_query($conn,$edit_client_sql1)  or die(mysqli_error($conn));	
			
			
			if(!empty($_FILES['client_image']['name']))
			{

			$image_name=time().$_FILES['client_image']['name'];
			$image_type=$_FILES['client_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['client_image']['size'];
			$temp_name=$_FILES['client_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_client_sql="update tbl_client set 
			client_image ='".$image_name."'
			where client_id='".$_REQUEST['client_id']."'";
			mysqli_query($conn,$edit_client_sql)  or die(mysqli_error($conn));	

			}
			
		

if($edit_client_exe1){
$clientmsg.= showMessage("Company has been updated successfully",'success');		
}
}
else{
		$clientmsg .= showMessage('There is an item with same name.','error');
	}
}

$action  = $_REQUEST['actnclient'];
$client_id = $_REQUEST['client_id'];
if($action == 'dellclient' && !empty($client_id)){
$DelclientSql = 'DELETE FROM tbl_client WHERE client_id  = "'.$client_id.'"';
$DelclientQuery = g_db_query($DelclientSql);
if($DelclientQuery){
$clientmsg.= showMessage('The Company Has Been Deleted','success');
}
}
?>