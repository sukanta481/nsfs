<?php
$message		= '';
$type = $_GET['type'] ?? '';
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );

if(isset($_REQUEST['edit_widget']) && $_REQUEST['edit_widget']=="Update"){
	$edit_widget_sql1="update tbl_widget set
	 usd_rate='".mysqli_real_escape_string($conn,$_REQUEST['usd_rate'])."',
	 widget_charge='".mysqli_real_escape_string($conn,$_REQUEST['widget_charge'])."',
	widget_site_feature='".mysqli_real_escape_string($conn,$_REQUEST['widget_site_feature'])."',
	widget_copyright='".mysqli_real_escape_string($conn,$_REQUEST['widget_copyright'])."',
	 widget_site_info='".mysqli_real_escape_string($conn,$_REQUEST['widget_site_info'])."',
	  widget_site_slogan='".mysqli_real_escape_string($conn,$_REQUEST['widget_site_slogan'])."',
	   widget_header_site_feature='".mysqli_real_escape_string($conn,$_REQUEST['widget_header_site_feature'])."',
	 widget_newsletter='".mysqli_real_escape_string($conn,$_REQUEST['widget_newsletter'])."',
	  widget_sale_weekend='".mysqli_real_escape_string($conn,$_REQUEST['widget_sale_weekend'])."',
	   widget_newsletter_left_sec='".mysqli_real_escape_string($conn,$_REQUEST['widget_newsletter_left_sec'])."',
	   widget_site_bg_active='".mysqli_real_escape_string($conn,$_REQUEST['widget_site_bg_active'])."',
	      widget_newsletter_left_sec_link='".mysqli_real_escape_string($conn,$_REQUEST['widget_newsletter_left_sec_link'])."',
	     widget_delivery_option='".mysqli_real_escape_string($conn,$_REQUEST['widget_delivery_option'])."',
	     widget_footer_banner='".mysqli_real_escape_string($conn,$_REQUEST['widget_footer_banner'])."',
	     widget_best_seller_banner1='".mysqli_real_escape_string($conn,$_REQUEST['widget_best_seller_banner1'])."',
	      reply_email='".mysqli_real_escape_string($conn,$_REQUEST['reply_email'])."',
	        returun_day='".mysqli_real_escape_string($conn,$_REQUEST['returun_day'])."',
	          coin_percent='".mysqli_real_escape_string($conn,$_REQUEST['coin_percent'])."',
	            coin_value='".mysqli_real_escape_string($conn,$_REQUEST['coin_value'])."',
	             wallet_expire_date1='".mysqli_real_escape_string($conn,$_REQUEST['wallet_expire_date1'])."',
	               wallet_expire_date='".mysqli_real_escape_string($conn,$_REQUEST['wallet_expire_date'])."',
	     widget_product_details_banner='".mysqli_real_escape_string($conn,$_REQUEST['widget_product_details_banner'])."',
	     widget_blog_banner='".mysqli_real_escape_string($conn,$_REQUEST['widget_blog_banner'])."',
	    design_and_developed_by='".mysqli_real_escape_string($conn,$_REQUEST['design_and_developed_by'])."',
	    widget_link='".mysqli_real_escape_string($conn,$_REQUEST['widget_link'])."',
	 widget_what_new='".mysqli_real_escape_string($conn,$_REQUEST['widget_what_new'])."'
	where widget_id =1";
	
	$edit_widget_exe1=mysqli_query($conn,$edit_widget_sql1)  or die(mysqli_error($conn));	
	
	
	
	
	if(!empty($_FILES['widget_newsletter_left_sec_image']['name']))
	{
		$image_name=time()."_".$_FILES['widget_newsletter_left_sec_image']['name'];

		$image_type=$_FILES['widget_newsletter_left_sec_image']['type'];

		$type=explode("/","$image_type");

		$image_size=$_FILES['widget_newsletter_left_sec_image']['size'];

		$temp_name=$_FILES['widget_newsletter_left_sec_image']['tmp_name'];

		$dir="post_img/";

		$uploadimage=$dir.$image_name;

		$upload=move_uploaded_file($temp_name,$uploadimage);

		$pageUpdSql1 = "update tbl_widget set 

					widget_newsletter_left_sec_image='".$image_name."' where widget_id=1";

		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}
	
			
	if(!empty($_FILES['widget_sale_weekend_image']['name']))
	{
		$image_name=time()."_".$_FILES['widget_sale_weekend_image']['name'];

		$image_type=$_FILES['widget_sale_weekend_image']['type'];

		$type=explode("/","$image_type");

		$image_size=$_FILES['widget_sale_weekend_image']['size'];

		$temp_name=$_FILES['widget_sale_weekend_image']['tmp_name'];

		$dir="post_img/";

		$uploadimage=$dir.$image_name;

		$upload=move_uploaded_file($temp_name,$uploadimage);

		$pageUpdSql1 = "update tbl_widget set 

					widget_sale_weekend_image='".$image_name."' where widget_id=1";

		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}
	
	
	
	if(!empty($_FILES['widget_site_bg']['name']))
	{
		$image_name1=time()."_".$_FILES['widget_site_bg']['name'];

		$image_type=$_FILES['widget_site_bg']['type'];

		$type=explode("/","$image_type");

		$image_size=$_FILES['widget_site_bg']['size'];

		$temp_name1=$_FILES['widget_site_bg']['tmp_name'];

		$dir="post_img/";

		$uploadimage1=$dir.$image_name1;

		$upload1=move_uploaded_file($temp_name1,$uploadimage1);

		$pageUpdSql11 = "update tbl_widget set 

					widget_site_bg='".$image_name1."' where widget_id=1";

		$pageUpdExe1 = mysqli_query($conn,$pageUpdSql11) or die(mysqli_error($conn));
	}	
			
			
			if(!empty($_REQUEST['site_feature_content'])){
		        
			$feature_id=$_REQUEST['site_feature_content'];
				$count=count($feature_id);
				
				for($i=0;$i<$count;$i++)
				{
				if(!empty($_FILES['site_feature_image']['name'][$i]))
	{
		 $image_name1=time()."_".$_FILES['site_feature_image']['name'][$i];

		$image_type=$_FILES['site_feature_image']['type'][$i];

		$type=explode("/","$image_type");
  
		$image_size=$_FILES['site_feature_image']['size'][$i];

		$temp_name1=$_FILES['site_feature_image']['tmp_name'][$i];

		$dir="post_img/";

		$uploadimage1=$dir.$image_name1;

		$upload1=move_uploaded_file($temp_name1,$uploadimage1);
		

  }			

				 $feature_sql="insert into tbl_site_feature set feature_image='".$image_name1."',
				widget_id='1', feature_content ='".$_REQUEST['site_feature_content'][$i]."'
				";

				$feature_run=mysqli_query($conn,$feature_sql);	
				$last_id=mysqli_insert_id($conn);
				
				
										
		
		}
	}	


		if(!empty($_REQUEST['feature_id'])){
					
		        
			$feature_id=$_REQUEST['feature_id'];
				$count=count($feature_id);
				
				for($i=0;$i<$count;$i++)
				{
				
				 $feature_sql="update tbl_site_feature set widget_id='1', 
				 feature_content ='".$_REQUEST['site_feature_content_edit'][$i]."' 
				 where feature_id='".$_REQUEST['feature_id'][$i]."'";

				$feature_run=mysqli_query($conn,$feature_sql);	
				
				if(!empty($_FILES['site_feature_image_edit']['name'][$i]))
	{
		 $image_name1=time()."_".$_FILES['site_feature_image_edit']['name'][$i];

		$image_type=$_FILES['site_feature_image_edit']['type'][$i];

		$type=explode("/","$image_type");

		$image_size=$_FILES['site_feature_image_edit']['size'][$i];

		$temp_name1=$_FILES['site_feature_image_edit']['tmp_name'][$i];

		$dir="post_img/";

		$uploadimage1=$dir.$image_name1;

		$upload1=move_uploaded_file($temp_name1,$uploadimage1);
		
		 $img_sql="update tbl_site_feature set widget_id='1', 
				 feature_image ='".$image_name1."' 
				 where feature_id='".$_REQUEST['feature_id'][$i]."'";

				mysqli_query($conn,$img_sql);

  }			
				
				
				
										
		
		}
	}	


				

if($edit_widget_exe1){
$widgetmsg.= showMessage("widget has been updated successfully",'success');		
}
}


if(isset($_REQUEST['actftr']) && $_REQUEST['actftr']=='delftr'){
	 $fid=$_REQUEST['fid'];
	$fid_sql="select * from tbl_site_feature where feature_id='".$fid."'";
	$fid_run=mysqli_query($conn,$fid_sql);
	$fid_rows=mysqli_fetch_array($fid_run);
	if($fid_rows['feature_id']!=''){
	 $del_ftr_sql="delete from tbl_site_feature where feature_id='".$fid."'";
	$del_ftr_run=mysqli_query($conn,$del_ftr_sql);
	if($del_ftr_run){
		$widgetmsg.= showMessage("One Site Feature has been deleted successfully",'success');	
	}
	}else{}
	
}
?>