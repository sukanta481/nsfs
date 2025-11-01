<?php
$message		= '';
$type = $_GET['type'] ?? '';
ini_set("service_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );

if(isset($_REQUEST['save_service']) && $_REQUEST['save_service']=="Save"){

	$ser_alias = alias($_REQUEST['service_title']);	
	$ser_sql="select * from tbl_service where alise='".$ser_alias."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1){
		$date = date("Y-m-d");
		if(!empty($_FILES['service_small_image']['name'])){
			$small_image_name=time().$_FILES['service_small_image']['name'];
			$image_type=$_FILES['service_small_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['service_small_image']['size'];
			$temp_name=$_FILES['service_small_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$small_image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
		}
			
		if(!empty($_FILES['service_image']['name'])){
			$image_name=time().$_FILES['service_image']['name'];
			$image_type=$_FILES['service_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['service_image']['size'];
			$temp_name=$_FILES['service_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
		}
		
		

	$add_service_sql="insert into tbl_service set  
		meta_title='".mysqli_real_escape_string($conn,$_REQUEST['meta_title'])."', 
		meta_keyword='".mysqli_real_escape_string($conn,$_REQUEST['meta_keyword'])."', 
		meta_desc='".mysqli_real_escape_string($conn,$_REQUEST['meta_desc'])."',      
		service_title='".mysqli_real_escape_string($conn,$_REQUEST['service_title'])."',
		alise='".$ser_alias."',
		service_link='".mysqli_real_escape_string($conn,$_REQUEST['service_link'])."',  
		service_srt_desc='".mysqli_real_escape_string($conn,$_REQUEST['service_srt_desc'])."', 
	   	service_desc='".mysqli_real_escape_string($conn,$_REQUEST['service_desc'])."',
	   	service_overview='".mysqli_real_escape_string($conn,$_REQUEST['service_overview'])."',
	   	service_small_image='".$small_image_name."',
	   	service_image='".$image_name."'";
	$add_service_exe=mysqli_query($conn,$add_service_sql) or die(mysqli_error($conn));
	if($add_service_exe){
	
		
		$servicemsg.= showMessage("service has been added successfully",'success');		
	}
}else{
	$servicemsg .= showMessage('There is an item with same name.','error');
}	
			 						
}

if(isset($_REQUEST['edit_service']) && $_REQUEST['edit_service']=="Update"){
	
	$ser_alias = alias($_REQUEST['service_title']);	
	$ser_sql="select * from tbl_service where alise='".$ser_alias."' and service_id!='".$_REQUEST['service_id']."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{	
		$edit_service_sql1="update tbl_service set
			meta_title='".mysqli_real_escape_string($conn,$_REQUEST['meta_title'])."', 
			meta_keyword='".mysqli_real_escape_string($conn,$_REQUEST['meta_keyword'])."', 
			meta_desc='".mysqli_real_escape_string($conn,$_REQUEST['meta_desc'])."', 
			service_title='".mysqli_real_escape_string($conn,$_REQUEST['service_title'])."',
			alise='".$ser_alias."',
			service_link='".mysqli_real_escape_string($conn,$_REQUEST['service_link'])."',
	 		service_srt_desc='".mysqli_real_escape_string($conn,$_REQUEST['service_srt_desc'])."',     
	   		service_desc='".mysqli_real_escape_string($conn,$_REQUEST['service_desc'])."',
	   		service_overview='".mysqli_real_escape_string($conn,$_REQUEST['service_overview'])."'
	   		where service_id ='".$_REQUEST['service_id']."'";
		$edit_service_exe1=mysqli_query($conn,$edit_service_sql1)  or die(mysqli_error($conn));	
		
		if(!empty($_FILES['service_small_image']['name'])){
			$image_name=time().$_FILES['service_small_image']['name'];
			$image_type=$_FILES['service_small_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['service_small_image']['size'];
			$temp_name=$_FILES['service_small_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_service_sql="update tbl_service set 
			service_small_image ='".$image_name."'
			where service_id='".$_REQUEST['service_id']."'";
			mysqli_query($conn,$edit_service_sql)  or die(mysqli_error($conn));	
		}
		if(!empty($_FILES['service_image']['name'])){
			$image_name=time().$_FILES['service_image']['name'];
			$image_type=$_FILES['service_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['service_image']['size'];
			$temp_name=$_FILES['service_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_service_sql="update tbl_service set 
			service_image ='".$image_name."'
			where service_id='".$_REQUEST['service_id']."'";
			mysqli_query($conn,$edit_service_sql)  or die(mysqli_error($conn));	
		}
		
		if($edit_service_exe1){
			
			$servicemsg.= showMessage("service has been updated successfully",'success');		
		}
	}else{
		$servicemsg .= showMessage('There is an item with same name.','error');
	}
}
if(isset($_REQUEST['actnservice']) && isset($_REQUEST['service_id']))
{
$action  = $_REQUEST['actnservice'];
$service_id = $_REQUEST['service_id'];
if($action == 'dellservice' && !empty($service_id)){
$DelserviceSql = 'DELETE FROM tbl_service WHERE service_id  = "'.$service_id.'"';
$DelserviceQuery = g_db_query($DelserviceSql);
if($DelserviceQuery){
$servicemsg.= showMessage('The service Has Been Deleted','success');
}
}
}
?>