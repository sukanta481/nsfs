<?php
$message = '';
$type = $_GET['type'] ?? '';  // FIXED LINE!
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);



if(isset($_REQUEST['edit_trip']) && $_REQUEST['edit_trip']=="Update"){


			

		 $edit_shipping_sql="update  tbl_shipping_details set
		status='".mysqli_real_escape_string($conn,$_REQUEST['status'])."',
		reason_of_delay='".mysqli_real_escape_string($conn,$_REQUEST['reason_of_delay'])."'
		where shipping_details_id='".$_REQUEST['shipping_details_id']."'
		
		";
		$edit_shipping_rs=mysqli_query($conn,$edit_shipping_sql);
		$sql=mysqli_query($conn,"SELECT * FROM `tbl_trip_status` WHERE ship_id='".$_REQUEST['shipping_details_id']."'
		
		");
		$fetch=mysqli_fetch_row($sql);
		date_default_timezone_set("Asia/Kolkata");
		$d=(date('Y-m-d H:i:s'));
		if($fetch>0){
		    //date_default_timezone_set("Asia/Kolkata");
		   
		 $edit_date=mysqli_query($conn,"UPDATE `tbl_trip_status` SET `ship_id`='".$_REQUEST['shipping_details_id']."',`status`='".mysqli_real_escape_string($conn,$_REQUEST['status'])."', `updateddate`='".$d."' WHERE `ship_id`='".$_REQUEST['shipping_details_id']."'");
		}else{
		   // date_default_timezone_set("Asia/Kolkata");
		   $edit_date=mysqli_query($conn,"INSERT INTO `tbl_trip_status`(`ship_id`, `status`, `updateddate`) VALUES ('".$_REQUEST['shipping_details_id']."','".mysqli_real_escape_string($conn,$_REQUEST['status'])."','$d')"); 
		}
		
		if(!empty($_FILES['proof_of_delivery']['name'])){
			$image_name=time().$_FILES['proof_of_delivery']['name'];
			$image_type=$_FILES['proof_of_delivery']['type'];
			$type=explode("/","$image_type");
			$image_size=$_FILES['proof_of_delivery']['size'];
			$temp_name=$_FILES['proof_of_delivery']['tmp_name'];
			$dir="post_img/";
			$uploadimage=$dir.$image_name;
			$upload=move_uploaded_file($temp_name,$uploadimage);
			
			 $edit_pod_sql="update  tbl_shipping_details set
			proof_of_delivery='".$image_name."'
			where shipping_details_id='".$_REQUEST['shipping_details_id']."'
			
			";
			$edit_pod_rs=mysqli_query($conn,$edit_pod_sql);
		}
		
	

if($edit_shipping_rs){
$tripmsg.= showMessage("Status has been updated successfully",'success');		
}

}

?>