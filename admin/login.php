<?php 
$message = $message ?? '';
require 'top_header.php'; 
require_once 'includes/script/login_code.php';
?>
<?php
			 if(isset($_REQUEST['forgot'])) {
			 	$length = 8;
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
		     $password = substr( str_shuffle( $chars ), 0, $length );
		    $enc_pass=md5($password);
			 	
			$sql="update tbl_administrator set adminpassword='".$enc_pass."' where id=1";
	//echo $sql;
		$result=mysqli_query($conn,$sql);
		if($result){
			
			$get_admin_email_sql="select * from tbl_administrator where adminname='admin'";
			$get_admin_email_rs=mysqli_query($conn,$get_admin_email_sql);
			$get_admin_email_row=mysqli_fetch_array($get_admin_email_rs);
			$admin_email=$get_admin_email_row['admin_email'];
		
			$to	 		= $admin_email;
			$subject	=  "North Super Fast Service New Admin Password";
			$massege_body 		= "
			<table width='100%' border='0' cellspacing='0' cellpadding='0'>
			<tr><td><table width='100%' border='0' cellspacing='0' cellpadding='0'>
			<tr><td>Hi</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td>You Ask for new password for North Super Fast Service admin panel</td></tr>
			<tr><td>Your New Password</td><td>:</td><td>".$password."</td></tr>
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
			
			
				
				$headers = "Content-Type: text/html; charset=iso-8859-1";
				$headers.= "Content-Transfer-Encoding: 8bit";
				$headers.= $subject;
				 $body	 = $my_message;
				
				$send 	 = mail($to,$subject,$body,$headers);
				
				if($send)
				{
					 $message = "Your Password is send to your email address";
				}		
			
			
			
	 
	}
			 }
?>
<body style="background:#F7F7F7;">

  <div class="">
    <a class="hiddenanchor" id="toregister"></a>
    <a class="hiddenanchor" id="tologin"></a>

    <div id="wrapper">
      <div id="login" class="animate form">
        <section class="login_content">
          <form method="post" class="cmxform" id="form1" name="form1" action="<?php echo $_SERVER['PHP_SELF'];?>">
            <h1>Login Form</h1>
            <?php if($message!='') { ?>
			
			<div class="" style="margin:2px;padding:3px;">
			<span style="margin-left:30px;"><?php echo $message;?></span>
			</div>
	
		 <?php } ?>
            <div>
              <input type="text" class="form-control" placeholder="Username" required="" name="user" />
            </div>
            <div>
              <input type="password" class="form-control" placeholder="Password" required="" name="password" />
            </div>
            <div>
            	<input type="hidden" name="Login" value="Login">
			            	
              <!-- <a class="btn btn-default submit" onclick="document.forms.form1.submit()">Log in</a> -->
              <button class="btn btn-default" type="submit">Log in</button>
              	<div>
    				<a href="forgot_password.php">Forgot Password?</a>
				</div>
            </div>
            <div class="clearfix"></div>
            <div class="separator">

              <div class="clearfix"></div>
              <br />
              <div>
                <h1><img src="images/logo.png"></h1>

                <p>©<?=  date('Y'); ?> All Rights Reserved. North Super Fast Service</p>
              </div>
            </div>
          </form>
          <!-- form -->
        </section>
        <!-- content -->
      </div>
      
    </div>
  </div>

</body>

</html>