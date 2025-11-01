<?php
$message = '';
$type = $_GET['type'] ?? '';
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );


if(isset($_REQUEST['add_why_choose']) && $_REQUEST['add_why_choose']=="Save"){
	
if(!empty($_FILES['feature_image']['name']))
{

$image_name=time().$_FILES['feature_image']['name'];
$image_type=$_FILES['feature_image']['type'];
$type=explode("/","$image_type");
$image_size=$_FILES['feature_image']['size'];
$temp_name=$_FILES['feature_image']['tmp_name'];
$dir="post_img/";
$uploadimage=$dir.$image_name;
$upload=move_uploaded_file($temp_name,$uploadimage);
}
				
	
$edit_festure_sql1="insert into tbl_why_choose set
	feature_title='".mysqli_real_escape_string($conn,$_REQUEST['feature_title'])."', 
	feature_link='".mysqli_real_escape_string($conn,$_REQUEST['feature_link'])."', 
	feature_count='".mysqli_real_escape_string($conn,$_REQUEST['feature_count'])."', 
	feature_text='".mysqli_real_escape_string($conn,$_REQUEST['feature_text'])."', 
	feature_desc='".mysqli_real_escape_string($conn,$_REQUEST['feature_desc'])."',
	feature_image ='".$image_name."'
	";
$edit_feature_exe1=mysqli_query($conn,$edit_festure_sql1)  or die(mysqli_error($conn));	
			
			
			
		

if($edit_feature_exe1){
$why_choosemsg.= showMessage("Why Choose has been added successfully",'success');		
}
}


if(isset($_REQUEST['edit_why_choose']) && $_REQUEST['edit_why_choose']=="Update"){
$edit_festure_sql1="update tbl_why_choose set
	feature_title='".mysqli_real_escape_string($conn,$_REQUEST['feature_title'])."', 
	feature_link='".mysqli_real_escape_string($conn,$_REQUEST['feature_link'])."', 
	feature_count='".mysqli_real_escape_string($conn,$_REQUEST['feature_count'])."', 
	feature_text='".mysqli_real_escape_string($conn,$_REQUEST['feature_text'])."', 
	feature_desc='".mysqli_real_escape_string($conn,$_REQUEST['feature_desc'])."' 				
    where feature_id ='".$_REQUEST['feature_id']."'";
$edit_feature_exe1=mysqli_query($conn,$edit_festure_sql1)  or die(mysqli_error($conn));	
			
			
			if(!empty($_FILES['feature_image']['name']))
			{

			$image_name=time().$_FILES['feature_image']['name'];
			$image_type=$_FILES['feature_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['feature_image']['size'];
			$temp_name=$_FILES['feature_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_feature_sql="update tbl_why_choose set 
			feature_image ='".$image_name."'
			where feature_id='".$_REQUEST['feature_id']."'";
			mysqli_query($conn,$edit_feature_sql)  or die(mysqli_error($conn));	

			}
			
		

if($edit_feature_exe1){
$why_choosemsg.= showMessage("Why Choose has been updated successfully",'success');		
}
}

$action  = $_REQUEST['actnwhychoose'] ?? '';
$feature_id = $_REQUEST['feature_id'] ?? '';
if($action == 'dellwhychoose' && !empty($feature_id)){
$DelclientSql = 'DELETE FROM  tbl_why_choose WHERE feature_id  = "'.$feature_id.'"';
$DelclientQuery = g_db_query($DelclientSql);
if($DelclientQuery){
$why_choosemsg.= showMessage('The Why Choose Has Been Deleted','success');
}
}
?>