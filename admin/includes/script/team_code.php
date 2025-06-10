<?php
$message		= '';
$type= $_GET['type'];
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );

if(isset($_REQUEST['save_team']) && $_REQUEST['save_team']=="Save"){
	$ser_alias = alias($_REQUEST['team_title']);	
	$ser_sql="select * from tbl_team where alise='".$ser_alias."' and team_type='".$_REQUEST['team_type']."' and  team_desg='".$_REQUEST['team_desg']."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{	
		if(!empty($_FILES['team_image']['name'])){
			$image_name=time().$_FILES['team_image']['name'];
			$image_type=$_FILES['team_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['team_image']['size'];
			$temp_name=$_FILES['team_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
		}else{
			$image_name = 'noimage.jpg';
		}
		if(!empty($_FILES['team_image_webp']['name'])){
			$image_name_webp=time().$_FILES['team_image_webp']['name'];
			$image_type=$_FILES['team_image_webp']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['team_image_webp']['size'];
			$temp_name=$_FILES['team_image_webp']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name_webp;
			$upload=move_uploaded_file($temp_name,$uploadimage);
		}else{
			$image_name_webp = 'noimage.jpg';
		}

		$add_team_sql="insert into tbl_team set       
			team_title='".mysqli_real_escape_string($conn,$_REQUEST['team_title'])."', 
			team_type='".mysqli_real_escape_string($conn,$_REQUEST['team_type'])."', 
			team_srt_desc='".mysqli_real_escape_string($conn,$_REQUEST['team_srt_desc'])."',
			team_desg='".mysqli_real_escape_string($conn,$_REQUEST['team_desg'])."',
			team_work_area='".mysqli_real_escape_string($conn,$_REQUEST['team_work_area'])."',
			alise='".$ser_alias."', 
			team_image='".$image_name."' ,
			team_image_webp='".$image_name."'";
		$add_team_qry=mysqli_query($conn,$add_team_sql) or die(mysqli_error($conn));
		if($add_team_qry){
			$teammsg.= showMessage("Member has been added successfully",'success');		
		}
	}else{
			$teammsg .= showMessage('There is an item with same name.','error');
	}			 						
}

if(isset($_REQUEST['edit_team']) && $_REQUEST['edit_team']=="Update"){
	$ser_alias = alias($_REQUEST['team_title']);	
	$ser_sql="select * from tbl_team where alise='".$ser_alias."' and team_type='".$_REQUEST['team_type']."' and  team_desg='".$_REQUEST['team_desg']."' and team_id!='".$_REQUEST['team_id']."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{	
		$edit_team_sql1="update tbl_team set
			team_title='".mysqli_real_escape_string($conn,$_REQUEST['team_title'])."', 
			team_type='".mysqli_real_escape_string($conn,$_REQUEST['team_type'])."', 
			team_srt_desc='".mysqli_real_escape_string($conn,$_REQUEST['team_srt_desc'])."',
			team_desg='".mysqli_real_escape_string($conn,$_REQUEST['team_desg'])."',
			team_work_area='".mysqli_real_escape_string($conn,$_REQUEST['team_work_area'])."',
			alise='".$ser_alias."'	
            where team_id ='".$_REQUEST['team_id']."'";
		$edit_team_exe1=mysqli_query($conn,$edit_team_sql1)  or die(mysqli_error($conn));	
		if(!empty($_FILES['team_image']['name'])){
			$image_name=time().$_FILES['team_image']['name'];
			$image_type=$_FILES['team_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['team_image']['size'];
			$temp_name=$_FILES['team_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_team_sql="update tbl_team set 
				team_image ='".$image_name."'
				where team_id='".$_REQUEST['team_id']."'";
			mysqli_query($conn,$edit_team_sql)  or die(mysqli_error($conn));	
		}
		if(!empty($_FILES['team_image_webp']['name'])){
			$image_name=time().$_FILES['team_image_webp']['name'];
			$image_type=$_FILES['team_image_webp']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['team_image_webp']['size'];
			$temp_name=$_FILES['team_image_webp']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_team_sql="update tbl_team set 
				team_image_webp ='".$image_name."'
				where team_id='".$_REQUEST['team_id']."'";
			mysqli_query($conn,$edit_team_sql)  or die(mysqli_error($conn));	
		}
		if($edit_team_exe1){
			$teammsg.= showMessage("Member has been updated successfully",'success');		
		}
	}else{
		$teammsg .= showMessage('There is an item with same name.','error');
	}
}
if(isset($_REQUEST['actnteam']) && isset($_REQUEST['team_id']))
{
$action  = $_REQUEST['actnteam'];
$team_id = $_REQUEST['team_id'];
if($action == 'dellteam' && !empty($team_id)){
	$DelteamSql = 'DELETE FROM tbl_team WHERE team_id  = "'.$team_id.'"';
	$DelteamQuery = g_db_query($DelteamSql);
	if($DelteamQuery){
		$teammsg.= showMessage('The Member Has Been Deleted','success');
	}
}
}
?>