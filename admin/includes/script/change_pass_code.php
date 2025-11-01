<?php
$message='';
if(isset($_REQUEST['pwdsubmit']) && ($_REQUEST['pwdsubmit']=='Submit'))
{
	
	$old  = $_REQUEST['old_password'];
	$new1 = $_REQUEST['new_password1'];
	$new2 = $_REQUEST['new_password2'];
	$admin_email=$_REQUEST['admin_email'];
	$flag=0;
	
	$val=new validate();
	$val->letters_on();
	$val->nums_on();
	$val->specialchars_off();
	$val->whitespaces_off();
	$val->punctations_off();
	if(!$val->check($old)) {
		$passmessage .= showMessage("Please Enter valid Old Password.",'error');
		$flag=1;
	}
	if(!$val->check($new1)) {
		$passmessage .= showMessage("Please Enter valid Password.",'error');
		$flag=1;
	}
	if(!$val->check($new2)) {
		$passmessage .= showMessage("Please Enter valid Confirmed Password.",'error');
		$flag=1;
	}
	/*$val->length(5,8);
	if(!$val->check($new1)) {
		$message .= showMessage("Password Length may be 5 characters long.",'error');
	}*/
	if($flag==0) {
		if($new1===$new2) {
		}else {
			$passmessage .= showMessage("New Password and Confirmed Password are not valid.",'error');
			
		}
	}

	if($passmessage=='') {
		if(check_session('admin'))
		{
			$s = "select id from ".TABLE_PREFIX."administrator where adminpassword ='".g_db_input(md5($old))."'";
			$q = g_db_query($s);
			$r = g_db_fetch_array($q);
			if(($r['id']!='') && ($r['id'] == $_SESSION['admin']['id']))
			{
				$query="update ".TABLE_PREFIX."administrator set adminpassword ='".g_db_input(md5($new1))."' where id='".$_SESSION['admin']['id']."'";
				$res=g_db_query($query);
				if($res)
				{
					delete_session('admin');
					
					$passmessage .= showMessage("Your Password have been Changed Sucessfully.",'success');
					redirect(href_link('index.php'));
				}else {
					$passmessage .= showMessage("Error in Database.Please Try Again.",'error');
				}
			}
		}else {
			$passmessage .= showMessage("Your Session have been closed.",'warning');
		}
	}
	
	
	if($admin_email!='')
	{
		$query="update ".TABLE_PREFIX."administrator set admin_email ='".$admin_email."' where id='".$_SESSION['admin']['id']."'";
				$res=g_db_query($query);
		if($res)
		{
			$passmessage .= showMessage("Email address updated.",'success');
		}		
	}
}
?>