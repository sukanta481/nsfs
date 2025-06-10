<?php
$gallery_id = $_REQUEST['gallery_id'];
$sl = 'SELECT * FROM  tbl_gallery WHERE gallery_id = "'.$gallery_id.'"';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listgallery()
	{
		location.href = "gallery.php?type=list_gallery&lp=cu";
	}
</script>

			<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Gallery </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_gallery_form" action="" method="post" name="edit_gallery_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Category  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<select name="gallery_category_id" id="gallery_category_id" class="form-control col-md-7 col-xs-12">
 							<option value="">Select Category</option>
 							
 							<?php
 							$get_gallery_category_sql="select * from tbl_gallery_category order by gallery_category_title asc";
							$get_gallery_category_rs=mysqli_query($conn,$get_gallery_category_sql);
							while($get_gallery_category_row=mysqli_fetch_array($get_gallery_category_rs))
							{
							?>
							<option value="<?= $get_gallery_category_row['gallery_category_id'];?>" <?php if($rc['gallery_category_id']==$get_gallery_category_row['gallery_category_id']){ echo "selected"; }?>><?= $get_gallery_category_row['gallery_category_title'];?></option>
							<?php	
							}
 							?>
 						</select>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Gallery Image</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      	<?php if($rc['gallery_image']){ ?>
 						<img src="post_img/<?php print $rc['gallery_image'];?>" height="100" width="50%" />
 						<?php } ?>
    					<input type="file" name="gallery_image" id="gallery_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div>
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >gallery Image Webp</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      	<?php if($rc['gallery_image_webp']){ ?>
 						<img src="post_img/<?php print $rc['gallery_image_webp'];?>" height="100" width="50%" />
 						<?php } ?>
    					<input type="file" name="gallery_image_webp" id="gallery_image_webp" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >gallery Image Mobile</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      	<?php if($rc['gallery_image_mobile']){ ?>
 						<img src="post_img/<?php print $rc['gallery_image_mobile'];?>" height="100" width="50%" />
 						<?php } ?>
    					<input type="file" name="gallery_image_mobile" id="gallery_image_mobile" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Gallery Title <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="text" name="gallery_title" id="gallery_title" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['gallery_title']));?>" required="required" />
                      </div>
                    </div>
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Gallery Link <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="gallery_link" required="required" id="gallery_title" value="<?php echo stripslashes(html_entity_decode($rc['gallery_link']));?>" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Details</label>
                      <div class="col-md-6 col-sm-6 col-xs-12"><div class="txtarea-wysiwyg">
						<?php
							$oFCKeditor = new FCKeditor('gallery_desc') ;
							$oFCKeditor->BasePath	= 'fckeditor/';
							//$oFCKeditor->ToolbarSet = 'Basic';
							$oFCKeditor->Width 		= '660px';
							$oFCKeditor->Height 	= '500px';
							$oFCKeditor->Value		= htmlspecialchars_decode($rc['gallery_desc']);
							$oFCKeditor->Create() ;
						?>
						</div>
                		</div>
                    </div>  -->   
                   
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                  		<input type="hidden" name="gallery_id" value="<?php print $rc['gallery_id']; ?>" />
                      	<input type="submit" name="edit_gallery" value="Update" onclick="return validate_form('edit_gallery_form');" class="btn btn-success btn-submit">
                      	<input type="button" name="add_gallery_cancel" value="Cancel" onclick="listgallery();" class="btn btn-success btn-submit">
                      </div>
                    </div>
                  </form>

                </div>
             </div>