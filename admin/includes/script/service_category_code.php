<?php
$message		= '';
$type = $_GET['type'] ?? '';
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );

if(isset($_REQUEST['save_service_category']) && $_REQUEST['save_service_category']=="Save"){

	$ser_alias = alias($_REQUEST['service_category_title']);	
	$ser_sql="select * from tbl_service_category where alise='".$ser_alias."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1){
		$date = date("Y-m-d");
		
	if(!empty($_FILES['service_category_image']['name'])){
			$image_name=time().$_FILES['service_category_image']['name'];
			$image_type=$_FILES['service_category_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['service_category_image']['size'];
			$temp_name=$_FILES['service_category_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			
		}	

	$add_service_category_sql="insert into tbl_service_category set  
		
		service_category_title='".mysqli_real_escape_string($conn,$_REQUEST['service_category_title'])."',
		service_category_srt_desc='".mysqli_real_escape_string($conn,$_REQUEST['service_category_srt_desc'])."',
		service_category_image='".$image_name."',
		alise='".$ser_alias."' 
		";
	$add_service_category_exe=mysqli_query($conn,$add_service_category_sql) or die(mysqli_error($conn));
	
	
	
	
	if($add_service_category_exe){
	
		
		$service_categorymsg.= showMessage("service category has been added successfully",'success');		
	}
}else{
	$service_categorymsg .= showMessage('There is an item with same name.','error');
}	
			 						
}

if(isset($_REQUEST['edit_service_category']) && $_REQUEST['edit_service_category']=="Update"){
	
	 $ser_alias = alias($_REQUEST['service_category_title']);	
	 $ser_sql="select * from tbl_service_category where alise='".$ser_alias."' and service_category_id!='".$_REQUEST['service_category_id']."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{
		
		if(!empty($_FILES['service_category_image']['name'])){
			$image_name=time().$_FILES['service_category_image']['name'];
			$image_type=$_FILES['service_category_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['service_category_image']['size'];
			$temp_name=$_FILES['service_category_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			 $edit_service_sql="update tbl_service_category set 
			service_category_image ='".$image_name."'
			where service_category_id='".$_REQUEST['service_category_id']."'";
			mysqli_query($conn,$edit_service_sql)  or die(mysqli_error($conn));	
		}
			
		 $edit_service_category_sql1="update tbl_service_category set
			
			service_category_title='".mysqli_real_escape_string($conn,$_REQUEST['service_category_title'])."',
			service_category_srt_desc='".mysqli_real_escape_string($conn,$_REQUEST['service_category_srt_desc'])."',
			alise='".$ser_alias."'
	 		
	   		where service_category_id ='".$_REQUEST['service_category_id']."'";
		$edit_service_category_exe1=mysqli_query($conn,$edit_service_category_sql1)  or die(mysqli_error($conn));	
		
		
		
		if($edit_service_category_exe1){
			
			$service_categorymsg.= showMessage("service category has been updated successfully",'success');		
		}
	}else{
		$service_categorymsg .= showMessage('There is an item with same name.','error');
	}
}
if(isset($_REQUEST['actnservice_category']) && isset($_REQUEST['service_category_id']))
{
$action  = $_REQUEST['actnservice_category'];
$service_category_id = $_REQUEST['service_category_id'];
if($action == 'dellservice_category' && !empty($service_category_id)){
$Delservice_categorySql = 'DELETE FROM tbl_service_category WHERE service_category_id  = "'.$service_category_id.'"';
$Delservice_categoryQuery = g_db_query($Delservice_categorySql);

// $Delservice_categorySql = 'DELETE FROM tbl_service_category_data WHERE service_category_id  = "'.$service_category_id.'"';
// $Delservice_categoryQuery = g_db_query($Delservice_categorySql);

if($Delservice_categoryQuery){
$service_categorymsg.= showMessage('The service category Has Been Deleted','success');
}
}
}
?>