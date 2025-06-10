<?php
$message		= '';
$type= $_GET['type'];
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );

if(isset($_REQUEST['edit_contact']) && $_REQUEST['edit_contact']=="Update"){
$edit_contact_sql1="update tbl_contact set
    contact_banner_caption='".mysqli_real_escape_string($conn,$_REQUEST['contact_banner_caption'])."',
    contact_office_timing='".mysqli_real_escape_string($conn,$_REQUEST['contact_office_timing'])."',
	contact_phone='".mysqli_real_escape_string($conn,$_REQUEST['contact_phone'])."',	
	contact_phone_inner='".mysqli_real_escape_string($conn,$_REQUEST['contact_phone_inner'])."',			
	contact_phone2='".mysqli_real_escape_string($conn,$_REQUEST['contact_phone2'])."',		
	contact_phone2_inner='".mysqli_real_escape_string($conn,$_REQUEST['contact_phone2_inner'])."',
	contact_phone3='".mysqli_real_escape_string($conn,$_REQUEST['contact_phone3'])."',		
	contact_phone4='".mysqli_real_escape_string($conn,$_REQUEST['contact_phone4'])."',
	contact_address='".mysqli_real_escape_string($conn,$_REQUEST['contact_address'])."',
	contact_address2='".mysqli_real_escape_string($conn,$_REQUEST['contact_address2'])."',
	contact_address3='".mysqli_real_escape_string($conn,$_REQUEST['contact_address3'])."',
	contact_info='".mysqli_real_escape_string($conn,$_REQUEST['contact_info'])."',
	contact_map='".mysqli_real_escape_string($conn,$_REQUEST['contact_map'])."',		contact_email='".mysqli_real_escape_string($conn,$_REQUEST['contact_email'])."',		contact_email2='".mysqli_real_escape_string($conn,$_REQUEST['contact_email2'])."',
	contact_email3='".mysqli_real_escape_string($conn,$_REQUEST['contact_email3'])."'
		
	        where contact_id =1";
			$edit_contact_exe1=mysqli_query($conn,$edit_contact_sql1)  or die(mysqli_error($conn));	
			
	if(!empty($_FILES['contact_banner']['name']))
        {
    
            $image_name=time().$_FILES['contact_banner']['name'];
            $image_type=$_FILES['contact_banner']['type'];
            $type=explode("/","$image_type");
            $image_size=$_FILES['contact_banner']['size'];
            $temp_name=$_FILES['contact_banner']['tmp_name'];
            $dir="post_img/";
            $uploadimage=$dir.$image_name;
            $upload=move_uploaded_file($temp_name,$uploadimage);
            $pageUpdSql1 = "update tbl_contact set 
                    contact_banner='".$image_name."' where contact_id =1";
        $pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
            
        }	
			
		

if($edit_contact_exe1){
$contactmsg.= showMessage("Contact has been updated successfully",'success');		
}
}

?>