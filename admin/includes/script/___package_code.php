<?php
$message		= '';
$type= $_GET['type'];
ini_set("testimonial_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if(isset($_REQUEST['add_package']) && $_REQUEST['add_package']=="Save"){
	
	$ser_alias = alias($_REQUEST['package_name']);	
	$ser_sql="select * from tbl_package where alise='".$ser_alias."'";
	$ser_res=mysqli_query($conn,$ser_sql);
	$ser_row=mysqli_fetch_array($ser_res);
	$ser_num=mysqli_num_rows($ser_res);
	if($ser_num<1)
	{
	
if(!empty($_FILES['package_image']['name']))
	{

		$image_name=time().$_FILES['package_image']['name'];
		$image_type=$_FILES['package_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['package_image']['size'];
		$temp_name=$_FILES['package_image']['tmp_name'];
		$dir="images/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);

	}
	else
	{

		$image_name = 'noimage.jpg';

	}
	
if(!empty($_FILES['package_banner_image']['name']))
	{

		$bnr_image_name=time().$_FILES['package_banner_image']['name'];
		$image_type=$_FILES['package_banner_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['package_banner_image']['size'];
		$temp_name=$_FILES['package_banner_image']['tmp_name'];
		$dir="images/";
		$uploadimage=$dir.$bnr_image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);

	}
	else
	{

		$bnr_image_name = 'noimage.jpg';

	}


	$add_testimonial_sql="insert into tbl_package set       
	package_type_id='".mysqli_real_escape_string($conn,$_REQUEST['package_type_id'])."',
	client_id='".mysqli_real_escape_string($conn,$_REQUEST['client_id'])."',
	
	package_name='".mysqli_real_escape_string($conn,$_REQUEST['package_name'])."',
    package_heading='".mysqli_real_escape_string($conn,$_REQUEST['package_heading'])."',  
    alise='".$ser_alias."',
	package_departure_location='".mysqli_real_escape_string($conn,$_REQUEST['package_departure_location'])."', 	 
	package_location_map='".mysqli_real_escape_string($conn,$_REQUEST['package_location_map'])."', 	 
	package_discount_price='".mysqli_real_escape_string($conn,$_REQUEST['package_discount_price'])."', 	 
	package_addon_price='".mysqli_real_escape_string($conn,$_REQUEST['package_addon_price'])."', 	 
	   	
    package_short_desc='".mysqli_real_escape_string($conn,$_REQUEST['package_short_desc'])."',
 	package_long_desc='".mysqli_real_escape_string($conn,$_REQUEST['package_long_desc'])."',
	
	package_banner_text='".mysqli_real_escape_string($conn,$_REQUEST['package_banner_text'])."',
	
	package_start_date='".mysqli_real_escape_string($conn,$_REQUEST['package_start_date'])."',																
	package_on_arrival='".mysqli_real_escape_string($conn,$_REQUEST['package_on_arrival'])."',																			
	package_on_return='".mysqli_real_escape_string($conn,$_REQUEST['package_on_return'])."',
	package_starting_price='".mysqli_real_escape_string($conn,$_REQUEST['package_starting_price'])."',
	package_rating='".mysqli_real_escape_string($conn,$_REQUEST['package_rating'])."',
	covered='".mysqli_real_escape_string($conn,$_REQUEST['covered'])."',
	popular='".mysqli_real_escape_string($conn,$_REQUEST['popular'])."',
	package_banner_image='".$bnr_image_name."',
	package_image='".$image_name."'";
			
	$add_testimonial_exe=mysqli_query($conn,$add_testimonial_sql) or die(mysqli_error());
	
	$last_id=mysqli_insert_id($conn);
	
	
	
	
if($add_testimonial_exe){
			
if(isset($_REQUEST['amenities_id'])){
	$i=0;
	$sightsee=$_REQUEST['amenities_id'];
   
    				
	
	while($i<sizeof($sightsee)){
							
			
		
		$add_map_sightsee= "insert into  tbl_package_amenities_rel set
							package_id='".$last_id."',
							amenities_id='".$sightsee[$i]."'";
		mysqli_query($conn,$add_map_sightsee);
		$i++;
			}
	
}		
	
$testimonialmsg.= showMessage("Package has been added successfully",'success');		
}

}else{
	$testimonialmsg .= showMessage('There is an item with same name.','error');
}	
			 						
}

if(isset($_REQUEST['edit_package']) && $_REQUEST['edit_package']=="Update"){
	
$ser_alias = alias($_REQUEST['package_name']);	
$ser_sql="select * from tbl_package where alise='".$ser_alias."' and package_id!='".$_REQUEST['package_id']."'";
$ser_res=mysqli_query($conn,$ser_sql);
$ser_row=mysqli_fetch_array($ser_res);
$ser_num=mysqli_num_rows($ser_res);
if($ser_num<1)
{


$edit_testimonial_sql1="update tbl_package set 
	package_type_id='".mysqli_real_escape_string($conn,$_REQUEST['package_type_id'])."',
	client_id='".mysqli_real_escape_string($conn,$_REQUEST['client_id'])."',
	package_name='".mysqli_real_escape_string($conn,$_REQUEST['package_name'])."', 	 
	package_heading='".mysqli_real_escape_string($conn,$_REQUEST['package_heading'])."',	
	 alise='".$ser_alias."',
	package_departure_location='".mysqli_real_escape_string($conn,$_REQUEST['package_departure_location'])."', 	 
	package_location_map='".mysqli_real_escape_string($conn,$_REQUEST['package_location_map'])."', 	 
	package_discount_price='".mysqli_real_escape_string($conn,$_REQUEST['package_discount_price'])."', 	 
	package_addon_price='".mysqli_real_escape_string($conn,$_REQUEST['package_addon_price'])."', 	 
	
    
    package_short_desc='".mysqli_real_escape_string($conn,$_REQUEST['package_short_desc'])."',
   
    package_banner_text='".mysqli_real_escape_string($conn,$_REQUEST['package_banner_text'])."',	
 		 		
 	package_start_date='".mysqli_real_escape_string($conn,$_REQUEST['package_start_date'])."',												
 	package_on_arrival='".mysqli_real_escape_string($conn,$_REQUEST['package_on_arrival'])."',														
 	package_on_return='".mysqli_real_escape_string($conn,$_REQUEST['package_on_return'])."',
    package_starting_price='".mysqli_real_escape_string($conn,$_REQUEST['package_starting_price'])."',
    package_rating='".mysqli_real_escape_string($conn,$_REQUEST['package_rating'])."',
    covered='".mysqli_real_escape_string($conn,$_REQUEST['covered'])."',
    popular='".mysqli_real_escape_string($conn,$_REQUEST['popular'])."',
	package_long_desc='".mysqli_real_escape_string($conn,$_REQUEST['package_long_desc'])."'				
    where package_id ='".$_REQUEST['package_id']."'";
			$edit_testimonial_exe1=mysqli_query($conn,$edit_testimonial_sql1)  or die(mysqli_error());	
		
			
			if(!empty($_FILES['package_image']['name']))
			{

			$image_name=time().$_FILES['package_image']['name'];
			$image_type=$_FILES['package_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['package_image']['size'];
			$temp_name=$_FILES['package_image']['tmp_name'];
			$dir="images/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_testimonial_sql="update tbl_package set 
			package_image ='".$image_name."'
			where package_id='".$_REQUEST['package_id']."'";
			mysqli_query($conn,$edit_testimonial_sql)  or die(mysqli_error());	

			}
			
			if(!empty($_FILES['package_banner_image']['name']))
			{

			$bnr_image_name=time().$_FILES['package_banner_image']['name'];
			$image_type=$_FILES['package_banner_image']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['package_banner_image']['size'];
			$temp_name=$_FILES['package_banner_image']['tmp_name'];
			$dir="images/";
			$uploadimage=$dir.$bnr_image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			$edit_testimonial_sql="update tbl_package set 
			package_banner_image ='".$bnr_image_name."'
			where package_id='".$_REQUEST['package_id']."'";
			mysqli_query($conn,$edit_testimonial_sql)  or die(mysqli_error());	

			}
			
		

if($edit_testimonial_exe1){
	
if(isset($_REQUEST['amenities_id'])){
	$i=0;
	$sightsee=$_REQUEST['amenities_id'];
   // $last_id=mysqli_insert_id();
    				
	 $del_map_sightsee="DELETE from  tbl_package_amenities_rel where 
						package_id='". $_REQUEST['package_id']."'";
	 $del_map_qry = mysqli_query($conn,$del_map_sightsee);		
	while($i<sizeof($sightsee)){
							
			
		
		$add_map_sightsee= "insert into  tbl_package_amenities_rel set
							package_id='". $_REQUEST['package_id']."',
							amenities_id='".$sightsee[$i]."'";
		mysqli_query($conn,$add_map_sightsee);
		$i++;
			}
	
}		
		
	
$testimonialmsg.= showMessage("Package has been updated successfully",'success');		
}
}else{
		$testimonialmsg .= showMessage('There is an item with same name.','error');
	}

}

$action  = $_REQUEST['actnpackage'];
$package_id = $_REQUEST['package_id'];
if($action == 'dellpackage' && !empty($package_id)){
$DeltestimonialSql = 'DELETE FROM tbl_package WHERE package_id  = "'.$package_id.'"';
$DeltestimonialQuery = g_db_query($DeltestimonialSql);
if($DeltestimonialQuery){
$testimonialmsg.= showMessage('The Package Has Been Deleted','success');
}
}
?>