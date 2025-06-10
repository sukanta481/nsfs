<?php
if(isset($_POST['Login']) && ($_POST['Login']=='Login')){
	$user = trim($_REQUEST['user']);
	$pw = trim($_REQUEST['password']);
	$error=false;
	$message='';
	if(empty($user) || $user == 'Username'){
		$message='Invalid Username';
		$error=true;
	}
	
	if(empty($pw) || $pw == 'Password'){
		$message='Invalid Password';
		$error=true;
	}
	
	if(!$error){
		$username	= mysqli_real_escape_string($conn,stripslashes($user));
		$password	= mysqli_real_escape_string($conn,stripslashes($pw));
		$query="select * from ".TABLE_PREFIX."administrator where adminname='".g_db_input($username)."' and adminpassword='".g_db_input(md5($password))."'";
	
		$result_resource=g_db_query($query);
		$result=g_db_fetch_array($result_resource);
		if($result['id']!=''){
			//function_session_register('admin');
			$_SESSION['admin'] = array('id' => $result['id'],'adminname' => $result['adminname']);
			
			//$admin = array('id' => $result_resource->id,'username' => $result_resource->user_name);
			if(check_session('redirect_origin')) {
				$page = $redirect_origin['page'];
				if($page='') $page='login.php';
				$get_string = '';
				
				if (function_exists('http_build_query')) {
					if(is_array($redirect_origin['get'])){
						$get_string = http_build_query($redirect_origin['get']);
					}else{
						$get_string = $redirect_origin['get'];
					}
				}
				delete_session('redirect_origin');
				if(isset($page) && ($page!='')){
					redirect(href_link($page));
				}else{
					redirect(href_link('index.php'));
				}
			}else{
				redirect(href_link('index.php'));
			}
		}else{
			$message="Username or Password is Invalid on the Domain.";
		}
	}
}


