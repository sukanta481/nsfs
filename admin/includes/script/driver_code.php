<?php
$message = '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1);

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if(isset($_REQUEST['save_driver']) && $_REQUEST['save_driver']=="Save"){
    $ser_alias = alias($_REQUEST['driver_name']);   
    $ser_sql="select * from tbl_driver where alise='".$ser_alias."'";
    $ser_res=mysqli_query($conn,$ser_sql);
    $ser_row=mysqli_fetch_array($ser_res);
    $ser_num=mysqli_num_rows($ser_res);
    if($ser_num<1)
    {   
        if(!empty($_FILES['driver_image']['name']))
        {
            $image_name = time().$_FILES['driver_image']['name'];
            $image_type = $_FILES['driver_image']['type'];
            $type = explode("/","$image_type");
            $image_size = $_FILES['driver_image']['size'];
            $temp_name = $_FILES['driver_image']['tmp_name'];
            $dir = "post_img/";
            $uploadimage = $dir.$image_name;
            $upload = move_uploaded_file($temp_name,$uploadimage);
        }
        else
        {
            $image_name = 'noimage.jpg';
        }

        $add_driver_sql="insert into tbl_driver set     
            driver_name='".mysqli_real_escape_string($conn,$_REQUEST['driver_name'])."',  
            alise='".$ser_alias."',
            driver_number='".mysqli_real_escape_string($conn,$_REQUEST['driver_number'])."', 
            active_status='".mysqli_real_escape_string($conn,$_REQUEST['active_status'])."', 
            driver_image='".$image_name."'";
                
        $add_driver_exe=mysqli_query($conn,$add_driver_sql) or die(mysqli_error($conn));
        if($add_driver_exe){
            $drivermsg .= showMessage("Driver has been added successfully",'success');       
        }
    }
    else{
        $drivermsg .= showMessage('There is an item with same name.','error');
    }                                  
}

if(isset($_REQUEST['edit_driver']) && $_REQUEST['edit_driver']=="Update"){
    $ser_alias = alias($_REQUEST['driver_name']);   
    $ser_sql="select * from tbl_driver where alise='".$ser_alias."' and driver_id!='".$_REQUEST['driver_id']."'";
    $ser_res=mysqli_query($conn,$ser_sql);
    $ser_row=mysqli_fetch_array($ser_res);
    $ser_num=mysqli_num_rows($ser_res);
    if($ser_num<1)
    {       
        $edit_driver_sql1="update tbl_driver set
            driver_name='".mysqli_real_escape_string($conn,$_REQUEST['driver_name'])."', 
            alise='".$ser_alias."',
            driver_number='".mysqli_real_escape_string($conn,$_REQUEST['driver_number'])."', 
            active_status='".mysqli_real_escape_string($conn,$_REQUEST['active_status'])."'                
            where driver_id ='".$_REQUEST['driver_id']."'";
        $edit_driver_exe1=mysqli_query($conn,$edit_driver_sql1)  or die(mysqli_error($conn));   
        
        if(!empty($_FILES['driver_image']['name']))
        {
            $image_name = time().$_FILES['driver_image']['name'];
            $image_type = $_FILES['driver_image']['type'];
            $type = explode("/","$image_type");
            $image_size = $_FILES['driver_image']['size'];
            $temp_name = $_FILES['driver_image']['tmp_name'];
            $dir = "post_img/";
            $uploadimage = $dir.$image_name;
            $upload = move_uploaded_file($temp_name,$uploadimage);
            $edit_driver_sql="update tbl_driver set 
                driver_image ='".$image_name."'
                where driver_id='".$_REQUEST['driver_id']."'";
            mysqli_query($conn,$edit_driver_sql)  or die(mysqli_error($conn));    
        }
        if($edit_driver_exe1){
            $drivermsg .= showMessage("Driver has been updated successfully",'success');      
        }
    }
    else{
        $drivermsg .= showMessage('There is an item with same name.','error');
    }
}

// Fix: avoid undefined keys for "actndriver" and "driver_id"
$action    = isset($_REQUEST['actndriver']) ? $_REQUEST['actndriver'] : '';
$driver_id = isset($_REQUEST['driver_id']) ? $_REQUEST['driver_id'] : '';

if($action == 'delldriver' && !empty($driver_id)){
    $DeldriverSql = 'DELETE FROM tbl_driver WHERE driver_id  = "'.$driver_id.'"';
    $DeldriverQuery = g_db_query($DeldriverSql);
    if($DeldriverQuery){
        $drivermsg .= showMessage('The Driver Has Been Deleted','success');
    }
}
?>
