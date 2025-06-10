<?php
$testimonial_id = $_REQUEST['testimonial_id'];
$sl = 'SELECT * FROM  tbl_testimonial WHERE testimonial_id = "'.$testimonial_id.'"';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listtestimonial()
	{
		location.href = "testimonial.php?type=list_testimonial&lp=cu";
	}
</script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Testimonial </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_testimonial_form" action="" method="post" name="edit_testimonial_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                     <!-- <?php if($rc['testimonial_image']){?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<img src="post_img/<?php print $rc['testimonial_image'];?>" height="100" width="100" />
                      </div>
                    </div>
                    <?php } ?> -->
                     <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Testimonial Image</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<input type="file" name="testimonial_image" id="testimonial_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    <!--<?php if($rc['testimonial_image_webp']){?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<img src="post_img/<?php print $rc['testimonial_image_webp'];?>" height="100" width="100" />
                      </div>
                    </div>
                    <?php } ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Testimonial Image Image Webp</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<input type="file" name="testimonial_image_webp" id="testimonial_image_webp"  class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>-->
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Testimonial Title  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="text" name="testimonial_title" id="testimonial_title" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['testimonial_name']));?>" required="required" />
                      </div>
                    </div>
                                 
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Testimonial Location  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="text" name="testimonial_position" id="testimonial_position" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['testimonial_position']));?>" required="required" />
                      </div>
                    </div>
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Testimonial Rate  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="number" name="testimonial_rate" id="testimonial_rate" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['testimonial_rate']));?>" min="0" max="5" required="required" />
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Testimonial Description</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
	                      	<div class="txtarea-wysiwyg">
								<?php
									$oFCKeditor = new FCKeditor('testimonial_desc') ;
									$oFCKeditor->BasePath	= 'fckeditor/';
									//$oFCKeditor->ToolbarSet = 'Basic';
									$oFCKeditor->Width 		= '660px';
									$oFCKeditor->Height 	= '500px';
									$oFCKeditor->Value		= htmlspecialchars_decode($rc['testimonial_desc']);
									$oFCKeditor->Create() ;
								?>
							</div>
                		</div>
                    </div>     
                   
                       
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                  		<input type="hidden" name="testimonial_id" value="<?php print $rc['testimonial_id']; ?>" />
                      	<input type="submit" name="edit_testimonial" value="Update" onclick="return validate_form('edit_testimonial_form');" class="btn btn-success btn-submit">     	
						<input type="button" name="edit_testimonial_cancel" value="Cancel" onclick="listtestimonial();" class="btn btn-success btn-submit">
                      </div>
                    </div>
                  </form>

                </div>
             </div>