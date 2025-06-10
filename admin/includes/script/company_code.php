<?php
$message		= '';
$type= $_GET['type'];
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );



if(isset($_REQUEST['save_company']) && $_REQUEST['save_company']=="Save"){
	$ser_alias = alias($_REQUEST['company_title']);	
	$ser_sql="select * from tbl_company where alise='".$ser_alias."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{	
	
if(!empty($_FILES['company_image']['name']))
	{

		$image_name=time().$_FILES['company_image']['name'];
		$image_type=$_FILES['company_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['company_image']['size'];
		$temp_name=$_FILES['company_image']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);

	}
	else
	{

		$image_name = 'noimage.jpg';

	}

	 $add_company_sql="insert into tbl_company set     
	company_title='".mysqli_real_escape_string($conn,$_REQUEST['company_title'])."',  
	company_address='".mysqli_real_escape_string($conn,$_REQUEST['company_address'])."',  
	company_phone='".mysqli_real_escape_string($conn,$_REQUEST['company_phone'])."',  
	alise='".$ser_alias."',
	

			company_image='".$image_name."'";
			
	$add_company_exe=mysqli_query($conn,$add_company_sql) or die(mysqli_error($conn));
if($add_company_exe){
$companymsg.= showMessage("Company has been added successfully",'success');		
}
}
else{
	$companymsg .= showMessage('There is an item with same name.','error');
}
			 						
}

if(isset($_REQUEST['edit_company']) && $_REQUEST['edit_company']=="Update"){
		
$ser_alias = alias($_REQUEST['company_title']);	
	$ser_sql="select * from tbl_company where alise='".$ser_alias."' and company_id!='".$_REQUEST['company_id']."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{		
	
$edit_company_sql1="update tbl_company set
	company_title='".mysqli_real_escape_string($conn,$_REQUEST['company_title'])."', 
	company_address='".mysqli_real_escape_string($conn,$_REQUEST['company_address'])."',  
	company_phone='".mysqli_real_escape_string($conn,$_REQUEST['company_phone'])."',  
	alise='".$ser_alias."'
		
            where company_id ='".$_REQUEST['company_id']."'";
			$edit_company_exe1=mysqli_query($conn,$edit_company_sql1)  or die(mysqli_error($conn));	
			
			
			if(!empty($_FILES['company_image']['name']))
			{

			$image_name=time().$_FILES['company_image']['name'];
			$image_type=$_FILES['company_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['company_image']['size'];
			$temp_name=$_FILES['company_image']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_company_sql="update tbl_company set 
			company_image ='".$image_name."'
			where company_id='".$_REQUEST['company_id']."'";
			mysqli_query($conn,$edit_company_sql)  or die(mysqli_error($conn));	

			}
			
		

if($edit_company_exe1){
$companymsg.= showMessage("Company has been updated successfully",'success');		
}
}
else{
		$companymsg .= showMessage('There is an item with same name.','error');
	}
}

$action  = $_REQUEST['actncompany'];
$company_id = $_REQUEST['company_id'];
if($action == 'dellcompany' && !empty($company_id)){
$DelcompanySql = 'DELETE FROM tbl_company WHERE company_id  = "'.$company_id.'"';
$DelcompanyQuery = g_db_query($DelcompanySql);
if($DelcompanyQuery){
$companymsg.= showMessage('The Company Has Been Deleted','success');
}
}
?>