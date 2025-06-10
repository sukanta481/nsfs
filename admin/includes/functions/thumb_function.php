<?php

		function createThumbnail($image_name,$image_size) {
			
     $dir="post_img/";
		$dir1="post_img/thumb_img/";
   
     
    if(preg_match('/[.](jpg)$/', $image_name)) {
        $im = imagecreatefromjpeg($dir . $image_name);
    } else if (preg_match('/[.](gif)$/', $image_name)) {
        $im = imagecreatefromgif($dir . $image_name);
    } else if (preg_match('/[.](png)$/', $image_name)) {
        $im = imagecreatefrompng($dir . $image_name);
    }
	$final_width_of_image =$image_size;
	 
	 $ox = imagesx($im);
     $oy = imagesy($im);
     
    $nx = $final_width_of_image;
    //$ny = floor($oy * ($final_width_of_image / $ox));
    $ny =248; 
     
    $nm = imagecreatetruecolor($nx, $ny);
     
    imagecopyresized($nm, $im, 0,0,0,0,$nx,$ny,$ox,$oy);
     
    if(!file_exists($dir1)) {
      if(!mkdir($dir1)) {
           die("There was a problem. Please try again!");
      } 
       }
 
    imagejpeg($nm, $dir1 . $image_name);
    $tn = '<img src="' . $dir1 . $image_name . '" alt="image" />';
    // $tn .= '<br />Congratulations. Your file has been successfully uploaded, and a      thumbnail has been created.';
    // echo $tn;
}
?>
