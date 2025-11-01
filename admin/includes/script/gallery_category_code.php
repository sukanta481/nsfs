<?php
$message		= '';
$type = $_GET['type'] ?? '';
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );

if(isset($_REQUEST['save_gallery_category']) && $_REQUEST['save_gallery_category']=="Save"){

	$ser_alias = alias($_REQUEST['gallery_category_title']);	
	$ser_sql="select * from tbl_gallery_category where alise='".$ser_alias."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1){
		$date = date("Y-m-d");
		
		

	$add_gallery_category_sql="insert into tbl_gallery_category set  
		
		gallery_category_title='".mysqli_real_escape_string($conn,$_REQUEST['gallery_category_title'])."',
		alise='".$ser_alias."' 
		";
	$add_gallery_category_exe=mysqli_query($conn,$add_gallery_category_sql) or die(mysqli_error($conn));
	
	
	
	
	if($add_gallery_category_exe){
	
		
		$gallery_categorymsg.= showMessage("gallery category has been added successfully",'success');		
	}
}else{
	$gallery_categorymsg .= showMessage('There is an item with same name.','error');
}	
			 						
}

if(isset($_REQUEST['edit_gallery_category']) && $_REQUEST['edit_gallery_category']=="Update"){
	
	 $ser_alias = alias($_REQUEST['gallery_category_title']);	
	 $ser_sql="select * from tbl_gallery_category where alise='".$ser_alias."' and gallery_category_id!='".$_REQUEST['gallery_category_id']."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{	
		 $edit_gallery_category_sql1="update tbl_gallery_category set
			
			gallery_category_title='".mysqli_real_escape_string($conn,$_REQUEST['gallery_category_title'])."',
			
			alise='".$ser_alias."'
	 		
	   		where gallery_category_id ='".$_REQUEST['gallery_category_id']."'";
		$edit_gallery_category_exe1=mysqli_query($conn,$edit_gallery_category_sql1)  or die(mysqli_error($conn));	
		
		
		
		if($edit_gallery_category_exe1){
			
			$gallery_categorymsg.= showMessage("gallery category has been updated successfully",'success');		
		}
	}else{
		$gallery_categorymsg .= showMessage('There is an item with same name.','error');
	}
}
if(isset($_REQUEST['actngallery_category']) && isset($_REQUEST['gallery_category_id']))
{
$action  = $_REQUEST['actngallery_category'];
$gallery_category_id = $_REQUEST['gallery_category_id'];
if($action == 'dellgallery_category' && !empty($gallery_category_id)){
$Delgallery_categorySql = 'DELETE FROM tbl_gallery_category WHERE gallery_category_id  = "'.$gallery_category_id.'"';
$Delgallery_categoryQuery = g_db_query($Delgallery_categorySql);

// $Delgallery_categorySql = 'DELETE FROM tbl_gallery_category_data WHERE gallery_category_id  = "'.$gallery_category_id.'"';
// $Delgallery_categoryQuery = g_db_query($Delgallery_categorySql);

if($Delgallery_categoryQuery){
$gallery_categorymsg.= showMessage('The gallery category Has Been Deleted','success');
}
}
}
?>