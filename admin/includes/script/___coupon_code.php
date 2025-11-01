<?php
$message		= '';
$type			= $_GET['type'];
ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1 );
//print phpinfo();
//print ini_get('upload_max_filesize');
/***************************  Create a New Bus Code ******************************************************/
if(isset($_REQUEST['save_coupon']) && $_REQUEST['save_coupon'] == 'Save'){
	
		$st_date = explode("-",$_REQUEST['cupon_st_dt']);
								$dt = $st_date[0];
								$month = $st_date[1];
								$year = $st_date[2];
								$st_date = $year."-".$month."-".$dt;
		$end_date = explode("-",$_REQUEST['cupon_end_dt']);
								$dt1 = $end_date[0];
								$month1 = $end_date[1];
								$year1 = $end_date[2];
								$end_date = $year1."-".$month1."-".$dt1;
				 $check_sql="select * from tbl_coupon where coupon_code='".$_REQUEST['coupon_code']."'";
	$check_run=mysqli_query($conn,$check_sql);
	$get_count = mysqli_num_rows($check_run);
	if($get_count>0){
		$couponmessages .= showMessage('Discount already exits','success');
	}
	else{		
		$save_coupon_sql="insert IGNORE into tbl_coupon set 
		cupon_type='".htmlspecialchars(stripslashes($_REQUEST['coupon_type']),ENT_QUOTES)."',		
		coupon_code='".htmlspecialchars(stripslashes($_REQUEST['coupon_code']),ENT_QUOTES)."',
		cupon_st_dt='".$_REQUEST['cupon_st_dt']."',
		cupon_end_dt='".$_REQUEST['cupon_end_dt']."',
		cupon_discount='".$_REQUEST['cupon_discount']."'";
		
		$save_coupon_rs=mysqli_query($conn,$save_coupon_sql);
		$last_id=mysqli_insert_id($conn);
		
		
		
	}
		
		if($save_coupon_rs){
			$couponmessages .= showMessage('Discount Added Successfully','success');
		}
	}

if(isset($_REQUEST['send_coupon']) && $_REQUEST['send_coupon'] == 'Send'){
			
			$coupon_code=$_REQUEST['coupon_code'];
			$cupon_end_dt=$_REQUEST['cupon_end_dt'];
	
			$to	 		= $_REQUEST['email_addr'];
			
			$subject	=  "Clouds of Vape: Discount Code";
			
			$message_body = "<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml'><head>
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0'>
		<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>
		<meta name='format-detection' content='telephone=no'>
		<!-- disable auto telephone linking in iOS -->
		<title>Clouds of Vape</title>
		<style type='text/css'>
/* RESET STYLES */

html {
	background-color: #f7f7f7;
	margin: 0;
	padding: 0;
}
body {
	height: 100% !important;
	margin: 0;
	padding: 0;
	width: 100% !important;
	font-family: Arial, Helvetica, sans-serif;
}
table {
	border-collapse: collapse;
}
table[id=bodyTable] {
	width: 100%!important;
	margin: auto;
	max-width: 560px!important;
	color: #7A7A7A;
	font-weight: normal;
}
img, a img {
	border: 0;
	outline: none;
	text-decoration: none;
	height: auto;
	line-height: 100%;
}
a { text-decoration: none !important; }
body {
	background-color: #fff;
}
/* /\/\/\/\/\/\/\/\/ TEMPLATE STYLES /\/\/\/\/\/\/\/\/ */
.main_container{ width:560px; }
@font-face {
    font-family: 'NeutraTextBook';
    src: url('fonts/NeutraTextBook.eot');
    src: url('fonts/NeutraTextBook.eot') format('embedded-opentype'),
         url('fonts/NeutraTextBook.woff2') format('woff2'),
         url('fonts/NeutraTextBook.woff') format('woff'),
         url('fonts/NeutraTextBook.ttf') format('truetype'),
         url('fonts/NeutraTextBook.svg#NeutraTextBook') format('svg');
}
@font-face {
    font-family: 'NeutraTextBookItalic';
    src: url('fonts/NeutraTextBookItalic.eot');
    src: url('fonts/NeutraTextBookItalic.eot') format('embedded-opentype'),
         url('fonts/NeutraTextBookItalic.woff2') format('woff2'),
         url('fonts/NeutraTextBookItalic.woff') format('woff'),
         url('fonts/NeutraTextBookItalic.ttf') format('truetype'),
         url('fonts/NeutraTextBookItalic.svg#NeutraTextBookItalic') format('svg');
}

</style>
</head>
<body leftmargin='0' topmargin='0' offset='0' bgcolor='#f7f7f7' marginheight='0' marginwidth='0'>
<center style='background: #fff;'>
    <table class='main_container' style='border-collapse: collapse; width:560px;' border='0' cellpadding='0' cellspacing='0' width='560'>         
         <tbody>
         <tr>
         	<td class='top_sec' style='width: 100%; height:40px; padding:6px 30px; background:#555656;' align='left' valign='top'><a href='http://www.clouds-of-vape.com/' target='_blank'><img src='".SITE_URL."images/logo.png' alt='logo'></a></td>            	
         </tr>
         <tr>
         	<td class='mid_sec' style='width: 100%; height:40px; padding:25px 30px 50px; background:#fff;' align='left' valign='top'>
         		<h6 style='margin:0 0 0 0; padding: 0 0 25px 0; color:#555656; font: normal 16px/30px 'NeutraTextBook';'>Hello <span style='color:#ffb5c2;'>User,</span></h6>
         		<p style='margin:0 0 0 0; padding: 0 0 0 0; color:#555656; font: normal 16px/30px 'NeutraTextBook';'>We are sending you a coupon code. This can be used to redeem price at the time of purchase.</p>
         		<span class='reset_btn' style=' padding:20px 0; width:100%; display:inline-block; '><strong>$coupon_code</strong></span>
         		<p style='margin:0 0 0 0; padding: 0 0 0 0; color:#555656; font: normal 16px/30px 'NeutraTextBook';'>The Validity of this coupon is upto $cupon_end_dt</p>
         		<span class='kica_foot_img' style=' width:100%; padding:50px 0 0 0; display:inline-block;'><img src='http://www.clouds-of-vape.com/admin/images/foot_kica_logo.jpg' alt=''></span>
         	</td>            	
         </tr>
         <tr>
         	<td class='footer_sec' style='width: 100%; height:auto; padding:13px 30px; background:#555656;' align='left' valign='top'>
         		<ul style=' width:100%; display:inline-block; text-align: center; margin:0px; padding:0px; '>
         			<li style=' display: inline-block; padding:0 55px;'><a href='http://www.clouds-of-vape.com/' target='_blank'><img src='http://www.clouds-of-vape.com/admin/images/f1.jpg' alt=''></a></li>
         			<li style=' display: inline-block; padding:0 55px;'><a href='https://www.facebook.com/clouds-of-vape/' target='_blank'><img src='http://www.clouds-of-vape.com/admin/images/f2.jpg' alt=''></a></li>
         			<li style=' display: inline-block; padding:0 55px;'><a href='https://www.instagram.com/clouds-of-vape/' target='_blank'><img src='http://www.clouds-of-vape.com/admin/images/f3.jpg' alt=''></a></li>
         		</ul>
         		
         		
         	</td>            	
         </tr>
         </tbody>
     </table>
</center>      
</body>
</html>";
			
			
			
				$my_message=$message_body;
				
				// $headers = "Content-Type: text/html; charset=iso-8859-1";
				// $headers.= "Content-Transfer-Encoding: 8bit";
				// $headers.= $subject;
				// $body	 = $my_message;
				// $send 	 = mail($to,$subject,$body,$headers);
				
				$reply_email='info@quicklease.in';
				$headers ="From: ".$reply_email."\n";
				$headers.="MIME-Version: 1.0\n";
				$headers.="Content-type: text/html; charset=iso 8859-1";
				$body	 = $my_message;
				
				$send 	 = mail($to,$subject,$body,$headers,"-f$reply_email");
				
				
				if($send){
					
				$couponmessages .= showMessage('Discount Code send Successfully','success');	  
				}else{
				$couponmessages .= showMessage('Problem in sending mail','error');	
					  
					}	
	
}	

	/***************************  edit bus code  ******************************************************/
if(isset($_REQUEST['edit_coupon']) && $_REQUEST['edit_coupon'] == 'Save'){
			
	
		$st_date = explode("-",$_REQUEST['cupon_st_dt']);
								$dt = $st_date[0];
								$month = $st_date[1];
								$year = $st_date[2];
								$st_date = $year."-".$month."-".$dt;
		$end_date = explode("-",$_REQUEST['cupon_end_dt']);
								$dt1 = $end_date[0];
								$month1 = $end_date[1];
								$year1 = $end_date[2];
								$end_date = $year1."-".$month1."-".$dt1;
	 $check_sql="select * from tbl_coupon where coupon_code='".$_REQUEST['coupon_code']."' and id!='".$_REQUEST['id']."'";
	$check_run=mysqli_query($conn,$check_sql);
	$get_count = mysqli_num_rows($check_run);
	if($get_count>0){
		$couponmessages .= showMessage('Discount already exits','success');
	}
	else{		
		$edit_coupon_sql="update IGNORE tbl_coupon set 
		coupon_code='".$_REQUEST['coupon_code']."',
		cupon_type='".$_REQUEST['coupon_type']."',		
		cupon_st_dt='".$_REQUEST['cupon_st_dt']."',
		cupon_end_dt='".$_REQUEST['cupon_end_dt']."',
		cupon_discount='".$_REQUEST['cupon_discount']."'
		
		where id='".$_REQUEST['id']."'";
		$edit_coupon_rs=mysqli_query($conn,$edit_coupon_sql);
		
	
	}
		
		
		if($edit_coupon_rs){
			$couponmessages .= showMessage('Discount Details Edited Successfully','success');
		}
	}
	/***************************  delete code  ******************************************************/
$action  = $_REQUEST['actncoupon'];
$id = $_REQUEST['coupon_id'];
if($action == 'delcoupon'){
	$delcouponsql = 'DELETE FROM tbl_coupon WHERE id = "'.$id.'"';
	$delcouponexe = mysqli_query($conn,$delcouponsql);
	if($delcouponexe){
		$couponmessages .= showMessage('Discount Has Been Deleted Successfully','success');
	}
}
?>