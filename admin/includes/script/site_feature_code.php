<?php
$message		= '';
$type= $_GET['type'];
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );


if(isset($_REQUEST['edit_site_feature']) && $_REQUEST['edit_site_feature']=="Update"){
$edit_festure_sql1="update tbl_site_feature set
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
			$edit_feature_sql="update tbl_site_feature set 
			feature_image ='".$image_name."'
			where feature_id='".$_REQUEST['feature_id']."'";
			mysqli_query($conn,$edit_feature_sql)  or die(mysqli_error($conn));	

			}
			
		

if($edit_feature_exe1){
$site_featuremsg.= showMessage("Site feature has been updated successfully",'success');		
}
}

// $action  = $_REQUEST['actnclient'];
// $client_id = $_REQUEST['client_id'];
// if($action == 'dellclient' && !empty($client_id)){
// $DelclientSql = 'DELETE FROM tbl_client WHERE client_id  = "'.$client_id.'"';
// $DelclientQuery = g_db_query($DelclientSql);
// if($DelclientQuery){
// $clientmsg.= showMessage('The client Has Been Deleted','success');
// }
// }
?>