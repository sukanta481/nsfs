<?php 
include('include/apps_top.php'); 
if(isset($_REQUEST['action']) && $_REQUEST['action']=='con_submit')
{
		$name           = trim($_REQUEST['name']);
		$email           = trim($_REQUEST['email']);	
		$city          = trim($_REQUEST['city']);
		$talk_about          = trim($_REQUEST['talk_about']);
		$con           = trim($_REQUEST['phone']);		
		$msg = trim($_REQUEST['msg']);
		
		
	if (empty($name) || (!preg_match("/^[a-zA-Z ]*$/",$name))) 
	{
    echo  "Charecter and space only allowed for First Name";
	}
	elseif (empty($email) || (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i",$email))) 
	{
		 echo  "Not a Valid Email ID";
	}
	elseif (empty($con) || (!preg_match("/^[0-9 ]*$/",$con))) {
	 echo  "Number and space only allowed for Phone No";
	}	
	else
	{	
		
		
		$widget_sql="select * from tbl_widget where widget_id='1'";
		$widget_run=mysqli_query($conn,$widget_sql);
		$widget_rows=mysqli_fetch_array($widget_run);
		
			
			 $to	 		= $widget_rows['reply_email'];
			$subject	=  "North Super Contact Us Feedback";
			$massege_body 		= "
			<table width='100%' border='0' cellspacing='0' cellpadding='0'>
			<tr><td><table width='100%' border='0' cellspacing='0' cellpadding='0'>
			<tr><td>Hi</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td>Following Person filled the Feedback form at North Super</td></tr>
			<tr><td>Name</td><td>:</td><td>".$name."</td></tr>
			<tr><td>Email</td><td>:</td><td>".$email."</td></tr>
			<tr><td>Phone</td><td>:</td><td>".$con."</td></tr>
			<tr><td>City</td><td>:</td><td>".$city."</td></tr>
			<tr><td>Talk About</td><td>:</td><td>".$talk_about."</td></tr>
			<tr><td>Message</td><td>:</td><td>".$msg."</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td>Thanks</td></tr>
			<tr><td>&nbsp;</td></tr>";
			
			$massege_body .= "</table></td></tr></table>";
			
			$my_message 		= "
			<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
			<html xmlns=\"http://www.w3.org/1999/xhtml\">
			<head>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />
			<title>Message For Administrator</title>
			</head>
			<body>
			<table width='100%' border='0' cellspacing='0' cellpadding='0'>
			<tr><td>".$massege_body."</td></tr>
			</table></body></html>";
				$reply_email='onestepup@northsuperfastservice.com';
				$headers ="From: ".$reply_email."\r\n";
				$headers.="MIME-Version: 1.0\r\n";
				$headers.="Content-type: text/html; charset=iso 8859-1";
				 $body	 = $my_message; 
				$send 	 = mail($to,$subject,$body,$headers,"-f$reply_email");
				
				
				
				if($send){
					  echo "Thank you for submitting your Feedback";
				}else{
					  echo "An Error Occured During Sending Your Request";
					}
	}
}
?>