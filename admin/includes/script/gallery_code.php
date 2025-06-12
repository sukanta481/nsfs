<?php

$message		= '';

$type = $_GET['type'] ?? '';

ini_set("gallery_max_size", "10M");

ini_set("upload_max_filesize", "128M");

ini_set("max_input_time", "300");

ini_set("max_execution_time", "300");

ini_set("memory_limit", -1 );



if(isset($_REQUEST['save_gallery']) && $_REQUEST['save_gallery']=="Save"){
	$ser_alias = alias($_REQUEST['gallery_title']);	
	$ser_sql="select * from tbl_gallery where alise='".$ser_alias."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{	
		if(!empty($_FILES['gallery_image']['name'])){
			$image_name=time().$_FILES['gallery_image']['name'];
			$image_type=$_FILES['gallery_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['gallery_image']['size'];
			$temp_name=$_FILES['gallery_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
		}else{
			$image_name = 'noimage.jpg';
		}
		if(!empty($_FILES['gallery_image_webp']['name'])){
			$image_name_webp=time().$_FILES['gallery_image_webp']['name'];
			$image_type=$_FILES['gallery_image_webp']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['gallery_image_webp']['size'];
			$temp_name=$_FILES['gallery_image_webp']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name_webp;
			$upload=move_uploaded_file($temp_name,$uploadimage);
		}else{
			$image_name_webp = 'noimage.jpg';
		}
		
		if(!empty($_FILES['gallery_image_mobile']['name'])){
			$mimage_name=time().$_FILES['gallery_image_mobile']['name'];
			$image_type=$_FILES['gallery_image_mobile']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['gallery_image_mobile']['size'];
			$temp_name=$_FILES['gallery_image_mobile']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$mimage_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
		}else{
			$mimage_name = 'noimage.jpg';
		}
		
		$add_gallery_sql="insert into tbl_gallery set       
			gallery_title='".mysqli_real_escape_string($conn,$_REQUEST['gallery_title'])."',
			gallery_category_id='".mysqli_real_escape_string($conn,$_REQUEST['gallery_category_id'])."',
			gallery_link='".mysqli_real_escape_string($conn,$_REQUEST['gallery_link'])."',
			alise='".$ser_alias."',  
			gallery_desc='".mysqli_real_escape_string($conn,$_REQUEST['gallery_desc'])."',   
			gallery_image_mobile='".$mimage_name."',  	
			gallery_image='".$image_name."',
			gallery_image_webp='".$image_name_webp."'";
		$add_gallery_exe=mysqli_query($conn,$add_gallery_sql) or die(mysqli_error($conn));
		if($add_gallery_exe){
			$gallerymsg.= showMessage("gallery has been added successfully",'success');		
		}
	}else{
		$gallerymsg .= showMessage('There is an item with same name.','error');
	}	
}


if(isset($_REQUEST['edit_gallery']) && $_REQUEST['edit_gallery']=="Update"){
	$ser_alias = alias($_REQUEST['gallery_title']);	
	$ser_sql="select * from tbl_gallery where alise='".$ser_alias."' and gallery_id!='".$_REQUEST['gallery_id']."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1){	
		$edit_gallery_sql1="update tbl_gallery set
			gallery_title='".mysqli_real_escape_string($conn,$_REQUEST['gallery_title'])."', 
			gallery_category_id='".mysqli_real_escape_string($conn,$_REQUEST['gallery_category_id'])."',
			gallery_link='".mysqli_real_escape_string($conn,$_REQUEST['gallery_link'])."',
			alise='".$ser_alias."',		 
	   		gallery_desc='".mysqli_real_escape_string($conn,$_REQUEST['gallery_desc'])."'
	   		where gallery_id ='".$_REQUEST['gallery_id']."'";
		$edit_gallery_exe1=mysqli_query($conn,$edit_gallery_sql1)  or die(mysqli_error($conn));	

			if(!empty($_FILES['gallery_image']['name'])){
				$image_name=time().$_FILES['gallery_image']['name'];
				$image_type=$_FILES['gallery_image']['type'];
				$type=explode("/","$image_type");
				$image_size=$_FILES['gallery_image']['size'];
				$temp_name=$_FILES['gallery_image']['tmp_name'];
				$dir="post_img/";
				$uploadimage=$dir.$image_name;
				$upload=move_uploaded_file($temp_name,$uploadimage);
				echo $edit_gallery_sql="update tbl_gallery set 
				gallery_image ='".$image_name."'
				where gallery_id='".$_REQUEST['gallery_id']."'";
				mysqli_query($conn,$edit_gallery_sql)  or die(mysqli_error($conn));	
			}
			if(!empty($_FILES['gallery_image_webp']['name'])){
				$image_name2=time().$_FILES['gallery_image_webp']['name'];
				$image_type=$_FILES['gallery_image_webp']['type'];
				$type=explode("/","$image_type");
				$image_size=$_FILES['gallery_image_webp']['size'];
				$temp_name=$_FILES['gallery_image_webp']['tmp_name'];
				$dir="post_img/";
				$uploadimage=$dir.$image_name2;
				$upload=move_uploaded_file($temp_name,$uploadimage);
				$edit_gallery_sql="update tbl_gallery set 
				gallery_image_webp ='".$image_name2."'
				where gallery_id='".$_REQUEST['gallery_id']."'";
				mysqli_query($conn,$edit_gallery_sql)  or die(mysqli_error($conn));	
			}


			if(!empty($_FILES['gallery_image_mobile']['name'])){
				$image_name=time().$_FILES['gallery_image_mobile']['name'];
				$image_type=$_FILES['gallery_image_mobile']['type'];
				$type=explode("/","$image_type");
				$image_size=$_FILES['gallery_image_mobile']['size'];
				$temp_name=$_FILES['gallery_image_mobile']['tmp_name'];
				$dir="post_img/";
				$uploadimage=$dir.$image_name;
				$upload=move_uploaded_file($temp_name,$uploadimage);
				$edit_gallery_sql="update tbl_gallery set 
				gallery_image_mobile ='".$image_name."'
				where gallery_id='".$_REQUEST['gallery_id']."'";
				mysqli_query($conn,$edit_gallery_sql)  or die(mysqli_error($conn));	
			}

		if($edit_gallery_exe1){
			$gallerymsg.= showMessage("gallery has been updated successfully",'success');		
		}

}else{
	$gallerymsg .= showMessage('There is an item with same name.','error');
}

}


if(isset($_REQUEST['actngallery']) && isset($_REQUEST['gallery_id']))
{
$action  = $_REQUEST['actngallery'];

$gallery_id = $_REQUEST['gallery_id'];

if($action == 'dellgallery' && !empty($gallery_id)){

$DelgallerySql = 'DELETE FROM tbl_gallery WHERE gallery_id  = "'.$gallery_id.'"';

$DelgalleryQuery = g_db_query($DelgallerySql);

if($DelgalleryQuery){

$gallerymsg.= showMessage('The gallery Has Been deleted','success');

}

}
}

?>