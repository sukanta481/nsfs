<?php
/**
* Image Uploader Calss
*
* @author      : Amit Sarkar <amit.sarkar7@gmail.com>
* @Description : This class will upload image to the selected
*                folder. If the folder not exists, it will create
*				 this under the image folder
*/
class Uploader {
	
	/*
	*|-----------------------------------------------------
	*|	This is the uploaded file value (e.g. name, type, tmp_name)
	*|-----------------------------------------------------
	*/
	public $file = array();
	
	/*
	*|-----------------------------------------------------
	*|	This is the supported file type
	*|-----------------------------------------------------
	*/
	private $file_type = array('image/gif','image/jpeg','image/pjpeg','image/png');
	
	/*
	*|-----------------------------------------------------
	*|	This is the uploaded file status
	*|-----------------------------------------------------
	*/
	public $status;
	
	/*
	*|-----------------------------------------------------
	*|	This is the preassign file uploaded folder
	*|-----------------------------------------------------
	*/
	private $main_path = 'images/';
	
	/*
	*|-----------------------------------------------------
	*|	User define uploaded file diredtory
	*|-----------------------------------------------------
	*/
	public $folder;
	
	/*
	*|-----------------------------------------------------
	*|	@Param  : Message to the user during
	*|		      file upload precess
	*|	@Return : string
	*|-----------------------------------------------------
	*/
	public $msgString;
	
	/*
	*|-----------------------------------------------------
	*|	@Param  : New name of the uploaded file
	*|			  after reach to the destination folder
	*|	@Return : string
	*|-----------------------------------------------------
	*/
	public $newname;
	
	public function upload(){
		if(isset($this->file) && $this->file['name'] != ''){
			$filetype		= $this->file['type'];
			$filename		= basename($this->file['name']);
			$fileext		= substr($filename,-4);
			if(!empty($this->folder)){
				if(substr($this->folder,-1) == '/'){
					$upload_path = $this->folder;
				}else{
					$upload_path = $this->folder.'/';
				}
				$img_path	= $this->main_path.$upload_path;
			}else{
				$img_path	= $this->main_path;
			}
			if(in_array($filetype,$this->file_type)){
				if(!file_exists($img_path)) {
					mkdir($img_path,0777);
				}
				$source			= $this->file['tmp_name'];
				$destination	= $img_path.$filename;
				if(move_uploaded_file($source,$destination)){	
					chmod($destination,0777);
					$this->newname = time()."_".str_replace(' ','_',$filename);
					rename($destination,$img_path.$this->newname);
					$this->msgString['status'] = true;
					return $this->msgString;
					return $this->newname;
				}else {
					$this->msgString['status'] = false;
					$this->msgString['msg'] = showMessage($filename.' can not be uploaded.','error');
					return $this->msgString;
				}
			}else {
				$this->msgString['status'] = false;
				$this->msgString['msg'] = showMessage('This Filetype Cannot be support File.','error');
				return $this->msgString;
			}
		}else{
			$this->msgString['status'] = false;
			$this->msgString['msg'] = showMessage('Upload Product Main Image','error');
			return $this->msgString;
		}
		
	}
	
	
	public function cropUpload($thumb_width,$thumb_height,$minfo_width,$minfo_height){
		if(isset($this->file) && $this->file['name'] != ''){
			$filetype		= $this->file['type'];
			$filename		= basename($this->file['name']);
			$fileext		= substr($filename,-4);
			if(!empty($this->folder)){
				if(substr($this->folder,-1) == '/'){
					$upload_path = $this->folder;
				}else{
					$upload_path = $this->folder.'/';
				}
				$img_path	= $this->main_path.$upload_path;
			}else{
				$img_path	= $this->main_path;
			}
			if(in_array($filetype,$this->file_type)){
				if(!file_exists($img_path)) {
					mkdir($img_path,0777);
				}
				$source			= $this->file['tmp_name'];
				$destination	= $img_path.$filename;
				if(move_uploaded_file($source,$destination)){	
					chmod($destination,0777);
					$this->newname = time()."_".str_replace(' ','_',$filename);
					rename($destination,$img_path.$this->newname);
					list($src_width, $src_height) = getimagesize($img_path.$this->newname);
					$error			= '';
					if(!isset($thumb_width))  $thumb_width 	= 140;
					if(!isset($thumb_height)) $thumb_height = 140;
					if(!isset($minfo_width))  $minfo_width 	= 350;
					if(!isset($minfo_height)) $minfo_height = 300;
					$thumb_folder 	= $img_path.'thumb/';
					$minfo_folder 	= $img_path.'moreinfo_img/';
					$thumb_img = imagecreatetruecolor($thumb_width,$thumb_height);
					$minfo_img = imagecreatetruecolor($minfo_width,$minfo_height);
					if($filetype == 'image/jpeg'){
						$src = imagecreatefromjpeg($img_path.$this->newname);
						imagecopyresampled($thumb_img,$src,0,0,0,0,$thumb_width,$thumb_height,$src_width,$src_height);
						imagecopyresampled($minfo_img,$src,0,0,0,0,$minfo_width,$minfo_height,$src_width,$src_height);
						imagejpeg($thumb_img,$thumb_folder.$this->newname,100);
						imagejpeg($minfo_img,$minfo_folder.$this->newname,100);
					}
					if($filetype == 'image/png'){
						$src = imagecreatefrompng($img_path.$this->newname);
						imagecopyresampled($thumb_img,$src,0,0,0,0,$thumb_width,$thumb_height,$src_width,$src_height);
						imagecopyresampled($minfo_img,$src,0,0,0,0,$minfo_width,$minfo_height,$src_width,$src_height);
						imagejpeg($thumb_img,$thumb_folder.$this->newname,100);
						imagejpeg($minfo_img,$minfo_folder.$this->newname,100);
					}
					if($filetype == 'image/gif'){
						$src = imagecreatefromgif($img_path.$this->newname);
						imagecopyresampled($thumb_img,$src,0,0,0,0,$thumb_width,$thumb_height,$src_width,$src_height);
						imagecopyresampled($minfo_img,$src,0,0,0,0,$minfo_width,$minfo_height,$src_width,$src_height);
						imagejpeg($thumb_img,$thumb_folder.$this->newname,100);
						imagejpeg($minfo_img,$minfo_folder.$this->newname,100);
					}
					chmod($thumb_folder.$this->newname, 0777);
					chmod($minfo_folder.$this->newname, 0777);
					$this->msgString['status'] = true;
					return $this->msgString;
					return $this->newname;
				}else {
					$this->msgString['status'] = false;
					$this->msgString['msg'] = showMessage($filename.' can not be uploaded.','error');
					return $this->msgString;
				}
			}else {
				$this->msgString['status'] = false;
				$this->msgString['msg'] = showMessage('This Filetype Cannot be support File.','error');
				return $this->msgString;
			}
		}else{
			$this->msgString['status'] = false;
			$this->msgString['msg'] = showMessage('Upload Product Main Image','error');
			return $this->msgString;
		}
	}
	
	public function remove($file, $folder){
		if(substr($folder,-1) == '/'){
			$f_path = $folder;
		}else{
			$f_path = $folder.'/';
		}
		$filePath = $this->main_path.$f_path;
		$fileLoc  = $filePath.$file;
		if(file_exists($fileLoc)){
			unlink($fileLoc);
		}
	}
	
}
