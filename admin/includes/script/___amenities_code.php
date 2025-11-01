<?php
$message		= '';
$type= $_GET['type'];
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );

if(isset($_REQUEST['save_amenities']) && $_REQUEST['save_amenities']=="Save"){
	if(!empty($_FILES['amenities_image']['name'])){
		$image_name=time().$_FILES['amenities_image']['name'];
		$image_type=$_FILES['amenities_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['amenities_image']['size'];
		$temp_name=$_FILES['amenities_image']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
	}else{
		$image_name = 'noimage.jpg';
	}
	
	if(!empty($_FILES['amenities_image2']['name'])){
		$image_name2=time().$_FILES['amenities_image2']['name'];
		$image_type=$_FILES['amenities_image2']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['amenities_image2']['size'];
		$temp_name=$_FILES['amenities_image2']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name2;
		$upload=move_uploaded_file($temp_name,$uploadimage);
	}else{
		$image_name2 = 'noimage.jpg';
	}

	$add_amenities_sql="insert into tbl_amenities set   
		property_type_id='".mysqli_real_escape_string($conn,$_REQUEST['property_type_id'])."',
		featured_amenities='".mysqli_real_escape_string($conn,$_REQUEST['featured_amenities'])."',   
		amenities_text='".mysqli_real_escape_string($conn,$_REQUEST['amenities_text'])."',
		amenities_image='".$image_name."',
		amenities_image2='".$image_name2."'";
	$add_amenities_exe=mysqli_query($conn,$add_amenities_sql) or die(mysqli_error($conn));
	$last_id = mysqli_insert_id($conn);
	if($_REQUEST['sub_type_id']!=''){
			$count = count($_REQUEST['sub_type_id']);
			for($i=0; $i<$count; $i++){
				$property_amenities_sql = "Insert into tbl_amenities_sub_type set
					sub_type_id='".$_REQUEST['sub_type_id'][$i]."',     
					amenities_id='".$last_id."'";
				$property_amenities_qry = mysqli_query($conn,$property_amenities_sql);
			}
		}
	if($add_amenities_exe){
		$amenitiesmsg.= showMessage("amenities has been added successfully",'success');		
	}				 						
}

if(isset($_REQUEST['edit_amenities']) && $_REQUEST['edit_amenities']=="Update"){

	$edit_amenities_sql1="update tbl_amenities set 
		property_type_id='".mysqli_real_escape_string($conn,$_REQUEST['property_type_id'])."', 
		featured_amenities='".mysqli_real_escape_string($conn,$_REQUEST['featured_amenities'])."',  
		amenities_text='".mysqli_real_escape_string($conn,$_REQUEST['amenities_text'])."'			
		where amenities_id ='".$_REQUEST['amenities_id']."'";
	$edit_amenities_exe1=mysqli_query($conn,$edit_amenities_sql1)  or die(mysqli_error($conn));	
	if(!empty($_FILES['amenities_image']['name'])){
		$image_name=time().$_FILES['amenities_image']['name'];
		$image_type=$_FILES['amenities_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['amenities_image']['size'];
		$temp_name=$_FILES['amenities_image']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$edit_amenities_sql="update tbl_amenities set 
		amenities_image ='".$image_name."'
		where amenities_id='".$_REQUEST['amenities_id']."'";
		mysqli_query($conn,$edit_amenities_sql)  or die(mysqli_error($conn));	
	}
	if(!empty($_FILES['amenities_image2']['name'])){
		$image_name=time().$_FILES['amenities_image2']['name'];
		$image_type=$_FILES['amenities_image2']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['amenities_image2']['size'];
		$temp_name=$_FILES['amenities_image2']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$edit_amenities_sql="update tbl_amenities set 
		amenities_image2 ='".$image_name."'
		where amenities_id='".$_REQUEST['amenities_id']."'";
		mysqli_query($conn,$edit_amenities_sql)  or die(mysqli_error($conn));	
	}
		if($_REQUEST['sub_type_id']!=''){
			$del_amenities_sub_type_id_sql = "Delete from tbl_amenities_sub_type where amenities_id='".$_REQUEST['amenities_id']."'";
			$del_amenities_sub_type_id_qry = mysqli_query($conn,$del_amenities_sub_type_id_sql);
			$count = count($_REQUEST['sub_type_id']);
			for($i=0; $i<$count; $i++){
				$property_amenities_sql = "Insert into tbl_amenities_sub_type set
					sub_type_id='".$_REQUEST['sub_type_id'][$i]."',     
					amenities_id='".$_REQUEST['amenities_id']."'";
				$property_amenities_qry = mysqli_query($conn,$property_amenities_sql);
			}
		}
	if($edit_amenities_exe1){
		$amenitiesmsg.= showMessage("amenities has been updated successfully",'success');		
	}

}

$action  = $_REQUEST['actnamenities'];
$amenities_id = $_REQUEST['amenities_id'];
if($action == 'dellamenities' && !empty($amenities_id)){
$DelamenitiesSql = 'DELETE FROM tbl_amenities WHERE amenities_id  = "'.$amenities_id.'"';
$DelamenitiesQuery = g_db_query($DelamenitiesSql);
	if($DelamenitiesQuery){
		$amenitiesmsg.= showMessage('The amenities Has Been Deleted','success');
	}
}

?>