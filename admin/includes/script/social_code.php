<?php

$message		= '';

$type= $_GET['type'];

ini_set("post_max_size", "10M");

ini_set("upload_max_filesize", "128M");

ini_set("max_input_time", "300");

ini_set("max_execution_time", "300");

ini_set("memory_limit", -1 );



///////////////////////////////////////////////////////////////////////

if(isset($_REQUEST['save_social']) && $_REQUEST['save_social'] == 'submit'){

 $edit_social_sql="UPDATE tbl_social set

            social_facebook='".$_REQUEST['social_facebook']."',

			social_twitter='".$_REQUEST['social_twitter']."',

			social_youtube ='".$_REQUEST['social_youtube']."',

			social_linkedin ='".$_REQUEST['social_linkedin']."',

			social_instagram ='".$_REQUEST['social_instagram']."',

			social_pinterest ='".$_REQUEST['social_pinterest']."',

			social_email ='".$_REQUEST['social_email']."'			

            where social_id = 1";		

$edit_social_row=mysqli_query($conn,$edit_social_sql)  or die(mysqli_error($conn));

if($edit_social_row){

$socialmessages.= showMessage("Social has been updated successfully",'success');		

}

else

{

	$socialmessages.= "";

}

}

?>