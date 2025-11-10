<?php
// Include CSRF helper if not already included
if (!function_exists('csrf_verify_request')) {
	require_once __DIR__ . '/../csrf_helper.php';
}

$message='';
if(isset($_REQUEST['pwdsubmit']) && ($_REQUEST['pwdsubmit']=='Submit'))
{
	// CSRF Protection
	if (!csrf_verify_request('change_password')) {
		csrf_error_exit();
	}
	
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
			// Handle both old and new session formats for admin ID
			if (is_array($_SESSION['admin'])) {
				$admin_id = $_SESSION['admin']['id'];
			} else {
				$admin_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? '';
			}

			// Get current password hash
			$s = "select id, adminpassword from ".TABLE_PREFIX."administrator where id='".$admin_id."'";
			$q = g_db_query($s);
			$r = g_db_fetch_array($q);

			if(($r['id']!='') && ($r['id'] == $admin_id))
			{
				$stored_password = $r['adminpassword'];
				$password_verified = false;

				// Check if stored password is bcrypt or MD5
				if (substr($stored_password, 0, 4) === '$2y$' || substr($stored_password, 0, 4) === '$2a$') {
					// Bcrypt - use password_verify
					if (password_verify($old, $stored_password)) {
						$password_verified = true;
					}
				} else if (strlen($stored_password) == 32 && ctype_xdigit($stored_password)) {
					// MD5 - verify with MD5
					if (md5($old) === $stored_password) {
						$password_verified = true;
					}
				}

				if ($password_verified) {
					// Hash new password with bcrypt
					$new_password_hash = password_hash($new1, PASSWORD_DEFAULT);

					$query="update ".TABLE_PREFIX."administrator set adminpassword ='".g_db_input($new_password_hash)."' where id='".$admin_id."'";
					$res=g_db_query($query);

					// Also update in tbl_users if exists
					$check_user = mysqli_query($conn, "SELECT user_id FROM tbl_users WHERE username=(SELECT adminname FROM ".TABLE_PREFIX."administrator WHERE id='".$admin_id."' LIMIT 1) LIMIT 1");
					if ($check_user && mysqli_num_rows($check_user) > 0) {
						$user_row = mysqli_fetch_assoc($check_user);
						mysqli_query($conn, "UPDATE tbl_users SET password='".mysqli_real_escape_string($conn, $new_password_hash)."' WHERE user_id=".$user_row['user_id']);
					}

					if($res)
					{
						delete_session('admin');

						$passmessage .= showMessage("Your Password have been Changed Successfully.",'success');
						redirect(href_link('index.php'));
					}else {
						$passmessage .= showMessage("Error in Database.Please Try Again.",'error');
					}
				} else {
					$passmessage .= showMessage("Old Password is incorrect.",'error');
				}
			}
		}else {
			$passmessage .= showMessage("Your Session have been closed.",'warning');
		}
	}
	

	if($admin_email!='')
	{
		// Handle both old and new session formats for admin ID
		if (is_array($_SESSION['admin'])) {
			$admin_id = $_SESSION['admin']['id'];
		} else {
			$admin_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? '';
		}

		// Properly escape email to prevent SQL injection
		$escaped_email = mysqli_real_escape_string($conn, $admin_email);
		$query="update ".TABLE_PREFIX."administrator set admin_email ='".$escaped_email."' where id='".$admin_id."'";
				$res=g_db_query($query);
		if($res)
		{
			$passmessage .= showMessage("Email address updated.",'success');
		}
	}
}
?>