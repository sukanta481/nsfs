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

if(isset($_REQUEST['save_car']) && $_REQUEST['save_car']=="Save"){
	$ser_alias = alias($_REQUEST['car_number']);	
	$ser_sql="select * from tbl_car where alise='".$ser_alias."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{	
	
if(!empty($_FILES['car_image']['name']))
	{

		$image_name=time().$_FILES['car_image']['name'];
		$image_type=$_FILES['car_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['car_image']['size'];
		$temp_name=$_FILES['car_image']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);

	}
	else
	{

		$image_name = 'noimage.jpg';

	}

	 $add_car_sql="insert into tbl_car set     
	car_number='".mysqli_real_escape_string($conn,$_REQUEST['car_number'])."', 
	car_details='".mysqli_real_escape_string($conn,$_REQUEST['car_details'])."', 
	alise='".$ser_alias."',
	
	active_status='".mysqli_real_escape_string($conn,$_REQUEST['active_status'])."', 
			car_image='".$image_name."'";
			
	$add_car_exe=mysqli_query($conn,$add_car_sql) or die(mysqli_error($conn));
if($add_car_exe){
$carmsg.= showMessage("Car has been added successfully",'success');		
}
}
else{
	$carmsg .= showMessage('There is an item with same name.','error');
}
			 						
}

if(isset($_REQUEST['edit_car']) && $_REQUEST['edit_car']=="Update"){
		
$ser_alias = alias($_REQUEST['car_number']);	
	$ser_sql="select * from tbl_car where alise='".$ser_alias."' and car_id!='".$_REQUEST['car_id']."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{		
	
$edit_car_sql1="update tbl_car set
	
	alise='".$ser_alias."',
	car_number='".mysqli_real_escape_string($conn,$_REQUEST['car_number'])."', 
	car_details='".mysqli_real_escape_string($conn,$_REQUEST['car_details'])."', 
active_status='".mysqli_real_escape_string($conn,$_REQUEST['active_status'])."' 				
            where car_id ='".$_REQUEST['car_id']."'";
			$edit_car_exe1=mysqli_query($conn,$edit_car_sql1)  or die(mysqli_error($conn));	
			
			
			if(!empty($_FILES['car_image']['name']))
			{

			$image_name=time().$_FILES['car_image']['name'];
			$image_type=$_FILES['car_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['car_image']['size'];
			$temp_name=$_FILES['car_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_car_sql="update tbl_car set 
			car_image ='".$image_name."'
			where car_id='".$_REQUEST['car_id']."'";
			mysqli_query($conn,$edit_car_sql)  or die(mysqli_error($conn));	

			}
			
		

if($edit_car_exe1){
$carmsg.= showMessage("Car has been updated successfully",'success');		
}
}
else{
		$carmsg .= showMessage('There is an item with same name.','error');
	}
}

$action  = $_REQUEST['actncar'];
$car_id = $_REQUEST['car_id'];
if($action == 'dellcar' && !empty($car_id)){
$DelcarSql = 'DELETE FROM tbl_car WHERE car_id  = "'.$car_id.'"';
$DelcarQuery = g_db_query($DelcarSql);
if($DelcarQuery){
$carmsg.= showMessage('The Car Has Been Deleted','success');
}
}
?>