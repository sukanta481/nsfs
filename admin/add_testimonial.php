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
  <h2>Add New Testimonial </h2>
  
  <div class="clearfix"></div>
</div>
<div class="x_content">

  <form id="add_testimonial_form" action="" method="post" name="add_testimonial_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
    
    
    
     <!-- <div class="item form-group">
      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Testimonial Image</label>
      <div class="col-md-6 col-sm-6 col-xs-12">
			<input type="file" name="testimonial_image" id="testimonial_image" value="" class="form-control col-md-7 col-xs-12"  />
      </div>
    </div> -->
    
    <!-- <div class="item form-group">
      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Testimonial Image Webp</label>
      <div class="col-md-6 col-sm-6 col-xs-12">
			<input type="file" name="testimonial_image_webp" id="testimonial_image_webp" value="" class="form-control col-md-7 col-xs-12"  />
      </div>
    </div> -->

    <div class="item form-group">
      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Testimonial Title:<span class="required">*</span>
      </label>
      <div class="col-md-6 col-sm-6 col-xs-12">
        <input type="text" name="testimonial_title" required="required" id="testimonial_title" class="form-control col-md-7 col-xs-12"  />
      </div>
    </div>
    <div class="item form-group">
      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Testimonial Location:<span class="required">*</span>
      </label>
      <div class="col-md-6 col-sm-6 col-xs-12">
        <input type="text" name="testimonial_position" required="required" id="testimonial_position" class="form-control col-md-7 col-xs-12"  />
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
      <div class="col-md-6 col-sm-6 col-xs-12"><div class="txtarea-wysiwyg">
		<?php
			$oFCKeditor = new FCKeditor('testimonial_desc') ;
			$oFCKeditor->BasePath	= 'fckeditor/';
			//$oFCKeditor->ToolbarSet = 'Basic';
			$oFCKeditor->Width 		= '660px';
			$oFCKeditor->Height 	= '500px';
			$oFCKeditor->Value		= '';
			$oFCKeditor->Create() ;
		?>
		</div></div>
	  </div>
     
    <div class="ln_solid"></div>
    <div class="form-group">
      <div class="col-md-6 col-md-offset-3">
      	<input type="submit" name="save_testimonial" value="Save" onclick="return validate_form('add_testimonial_form');" class="btn btn-success btn-submit">
		<input type="button" name="add_testimonial_cancel" value="Cancel" onclick="listtestimonial();" class="btn btn-success btn-submit">
 
        
      </div>
    </div>
  </form>

</div>
  </div>