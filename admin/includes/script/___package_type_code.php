<?php
$message		= '';
$type= $_GET['type'];
ini_set("testimonial_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );

if(isset($_REQUEST['add_package_type']) && $_REQUEST['add_package_type']=="Save"){
	

$add_testimonial_sql1="insert into tbl_package_type set     
meta_title='".mysqli_real_escape_string($conn,$_REQUEST['meta_title'])."',         meta_keyword='".mysqli_real_escape_string($conn,$_REQUEST['meta_keyword'])."',         
meta_desc='".mysqli_real_escape_string($conn,$_REQUEST['meta_desc'])."', 
package_type_name='".mysqli_real_escape_string($conn,$_REQUEST['package_type_name'])."', 	 
package_type_desc='".mysqli_real_escape_string($conn,$_REQUEST['package_type_desc'])."'";
$add_testimonial_exe1=mysqli_query($conn,$add_testimonial_sql1)  or die(mysqli_error());	
			
			
			
		
		

if($add_testimonial_exe1){
$testimonialmsg.= showMessage("Package type has been added successfully",'success');		
}
}

if(isset($_REQUEST['edit_package_type']) && $_REQUEST['edit_package_type']=="Update"){
	

$edit_testimonial_sql1="update tbl_package_type set     
meta_title='".mysqli_real_escape_string($conn,$_REQUEST['meta_title'])."',         meta_keyword='".mysqli_real_escape_string($conn,$_REQUEST['meta_keyword'])."',         
meta_desc='".mysqli_real_escape_string($conn,$_REQUEST['meta_desc'])."', 
	package_type_name='".mysqli_real_escape_string($conn,$_REQUEST['package_type_name'])."', 	 
	    package_type_desc='".mysqli_real_escape_string($conn,$_REQUEST['package_type_desc'])."'			
            	where package_type_id ='".$_REQUEST['package_type_id']."'";
			$edit_testimonial_exe1=mysqli_query($conn,$edit_testimonial_sql1)  or die(mysqli_error());	
			
			
			
		
		

if($edit_testimonial_exe1){
$testimonialmsg.= showMessage("Package type has been updated successfully",'success');		
}


}

$action  = $_REQUEST['actnpackage_type'];
$package_type_id = $_REQUEST['package_type_id'];
if($action == 'dellpackage_type' && !empty($package_type_id)){
$DeltestimonialSql = 'DELETE FROM tbl_package_type WHERE package_type_id  = "'.$package_type_id.'"';
$DeltestimonialQuery = g_db_query($DeltestimonialSql);
if($DeltestimonialQuery){
$testimonialmsg.= showMessage('The Package type Has Been Deleted','success');
}
}
?>