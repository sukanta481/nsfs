<?php
if(isset($_REQUEST['page_content']) && $_REQUEST['page_content']=='Submit'){
	$al=strtolower($_REQUEST['heading']);
	$ser_alias = alias($al);
	$pageUpdSql = "update tbl_pagecontent set
					meta_title='".mysqli_real_escape_string($conn,$_REQUEST['meta_title'])."', 
					meta_keyword='".mysqli_real_escape_string($conn,$_REQUEST['meta_keyword'])."', 
					meta_desc='".mysqli_real_escape_string($conn,$_REQUEST['meta_desc'])."', 
					heading='".mysqli_real_escape_string($conn,$_REQUEST['heading'])."',										
					short_desc='".mysqli_real_escape_string($conn,$_REQUEST['short_desc'])."',
					feature_text='".mysqli_real_escape_string($conn,$_REQUEST['feature_text'])."',
					feature_link='".mysqli_real_escape_string($conn,$_REQUEST['feature_link'])."',
					feature_text1='".mysqli_real_escape_string($conn,$_REQUEST['feature_text1'])."',
					feature_link1='".mysqli_real_escape_string($conn,$_REQUEST['feature_link1'])."',
					feature_text2='".mysqli_real_escape_string($conn,$_REQUEST['feature_text2'])."',
					feature_link2='".mysqli_real_escape_string($conn,$_REQUEST['feature_link2'])."',  
					content='".mysqli_real_escape_string($conn,$_REQUEST['page_desc'])."',
					page_link='".mysqli_real_escape_string($conn,$_REQUEST['page_link'])."',
					add_cont1='".mysqli_real_escape_string($conn,$_REQUEST['add_cont1'])."', 
					alise='".$ser_alias."', 
					add_cont2='".mysqli_real_escape_string($conn,$_REQUEST['add_cont2'])."',  
					add_cont3='".mysqli_real_escape_string($conn,$_REQUEST['add_cont3'])."', 
					add_cont4='".mysqli_real_escape_string($conn,$_REQUEST['add_cont4'])."', 
					add_cont5='".mysqli_real_escape_string($conn,$_REQUEST['add_cont5'])."', 
					banner_link='".mysqli_real_escape_string($conn,$_REQUEST['banner_link'])."',
					banner_heading='".mysqli_real_escape_string($conn,$_REQUEST['banner_heading'])."' 
					 where id='".$_REQUEST['pgid']."'";
					 
					 $pageUpdExe = mysqli_query($conn,$pageUpdSql) or die(mysqli_error($conn));
					 
					
	if(!empty($_FILES['feature_image']['name'])){
		$image_name=time()."_".$_FILES['feature_image']['name'];
		$image_type=$_FILES['feature_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['feature_image']['size'];
		$temp_name=$_FILES['feature_image']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			feature_image='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}else{}
	if(!empty($_FILES['feature_image_webp']['name'])){
		$image_name=time()."_".$_FILES['feature_image_webp']['name'];
		$image_type=$_FILES['feature_image_webp']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['feature_image_webp']['size'];
		$temp_name=$_FILES['feature_image_webp']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			feature_image_webp='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}else{}
	
	if(!empty($_FILES['feature_image1']['name'])){
		$image_name=time()."_".$_FILES['feature_image1']['name'];
		$image_type=$_FILES['feature_image1']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['feature_image1']['size'];
		$temp_name=$_FILES['feature_image1']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			feature_image1='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}else{}
	if(!empty($_FILES['feature_image_webp1']['name'])){
		$image_name=time()."_".$_FILES['feature_image_webp1']['name'];
		$image_type=$_FILES['feature_image_webp1']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['feature_image_webp1']['size'];
		$temp_name=$_FILES['feature_image_webp1']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			feature_image_webp1='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}else{}
	
	if(!empty($_FILES['feature_image2']['name'])){
		$image_name=time()."_".$_FILES['feature_image2']['name'];
		$image_type=$_FILES['feature_image2']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['feature_image2']['size'];
		$temp_name=$_FILES['feature_image2']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			feature_image2='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}else{}
	if(!empty($_FILES['feature_image_webp2']['name'])){
		$image_name=time()."_".$_FILES['feature_image_webp2']['name'];
		$image_type=$_FILES['feature_image_webp2']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['feature_image_webp2']['size'];
		$temp_name=$_FILES['feature_image_webp2']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			feature_image_webp2='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}else{}
	
	if(!empty($_FILES['page_image']['name'])){
		$image_name=time()."_".$_FILES['page_image']['name'];
		$image_type=$_FILES['page_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['page_image']['size'];
		$temp_name=$_FILES['page_image']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			page_image='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}else{}
	
	if(!empty($_FILES['page_image_mobile']['name'])){
		$image_name=time()."_".$_FILES['page_image_mobile']['name'];
		$image_type=$_FILES['page_image_mobile']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['page_image_mobile']['size'];
		$temp_name=$_FILES['page_image_mobile']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
		page_image_mobile='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}else{}
	
	if(!empty($_FILES['page_webp']['name'])){
		$image_name=time()."_".$_FILES['page_webp']['name'];
		$image_type=$_FILES['page_webp']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['page_webp']['size'];
		$temp_name=$_FILES['page_webp']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			page_webp='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}else{}
	
	
	if(!empty($_FILES['page_video']['name'])){
		$image_name=time()."_".$_FILES['page_video']['name'];
		$image_type=$_FILES['page_video']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['page_video']['size'];
		$temp_name=$_FILES['page_video']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			page_video='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}else{}
	if(!empty($_FILES['page_video_webm']['name'])){
		$image_name=time()."_".$_FILES['page_video_webm']['name'];
		$image_type=$_FILES['page_video_webm']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['page_video_webm']['size'];
		$temp_name=$_FILES['page_video_webm']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			page_video_webm='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}else{}
	
		if(!empty($_FILES['page_details_image']['name'])){
		$image_name=time()."_".$_FILES['page_details_image']['name'];
		$image_type=$_FILES['page_details_image']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['page_details_image']['size'];
		$temp_name=$_FILES['page_details_image']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			page_details_image='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}
		
	if(!empty($_FILES['page_details_image_mobile']['name'])){
		$image_name=time()."_".$_FILES['page_details_image_mobile']['name'];
		$image_type=$_FILES['page_details_image_mobile']['type'];
		$type=explode("/","$image_type");
		$image_size=$_FILES['page_details_image_mobile']['size'];
		$temp_name=$_FILES['page_details_image_mobile']['tmp_name'];
		$dir="post_img/";
		$uploadimage=$dir.$image_name;
		$upload=move_uploaded_file($temp_name,$uploadimage);
		$pageUpdSql1 = "update tbl_pagecontent set 
			page_details_image_mobile='".$image_name."' where id='".$_REQUEST['pgid']."'";
		$pageUpdExe = mysqli_query($conn,$pageUpdSql1) or die(mysqli_error($conn));
	}
		
		
		$reccount1=$_REQUEST['reccount1'];
		for($i=1; $i<=$reccount1; $i++)
		{
			
		$pageeditSql="update tbl_cms_details set
					page_id='".$_REQUEST['pgid']."', 
					page_heading='".mysqli_real_escape_string($conn,$_REQUEST['additionallebel'.$i])."', 
					page_details='".mysqli_real_escape_string($conn,$_REQUEST['additionalcontent'.$i])."'
					where cms_id='".mysqli_real_escape_string($conn,$_REQUEST['cms_id'.$i])."'
					";
					$pageeditRs=mysqli_query($conn,$pageeditSql);
			
		}	
		
		$fieldcount=$_REQUEST['hiddencount'];
		
		
		for($i=1; $i<$fieldcount; $i++){
						
						
					if($_REQUEST['addlebel'.$i]!='')
					{
					$pageaddSql="insert into tbl_cms_details set
					page_id='".$_REQUEST['pgid']."', 
					page_heading='".mysqli_real_escape_string($conn,$_REQUEST['addlebel'.$i])."', 
					page_details='".mysqli_real_escape_string($conn,$_REQUEST['addcontent'.$i])."'
					";
					mysqli_query($conn,$pageaddSql);
					}
		}
		
		if($pageUpdExe){
		$cmsmessage = showMessage("Page Content Updated Successfully.",'success');
		}else{
		$cmsmessage = showMessage("Server Problem! Try after some time",'error');
		}

}
?>