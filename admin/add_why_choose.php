
<script type="text/javascript">
	function listsitefeature()
	{
		location.href = "why_choose.php?type=list_why_choose&lp=cu";
	}
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Edit  Why Choose </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="add_feature_form" action="" method="post" name="add_feature_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" > Feature Title <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      	<input type="text" name="feature_title" value="" required="required" id="feature_title" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                   
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="file" name="feature_image" id="feature_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" > Feature Link <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      	<input type="text" name="feature_link" value="" required="required" id="feature_link" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div> -->
                   
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Feature Count<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     	<input type="number" name="feature_count" value="" required="required" id="feature_count" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div> -->       
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" > Feature Text <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      	
                      	<textarea name="feature_text" id="feature_text" class="form-control col-md-7 col-xs-12"></textarea>
                      </div>
                    </div>
                  	<!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" > Description <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <div class="txtarea-wysiwyg">
						<?php
							$oFCKeditor = new FCKeditor('feature_desc') ;
							$oFCKeditor->BasePath	= 'fckeditor/';
							//$oFCKeditor->ToolbarSet = 'Basic';
							$oFCKeditor->Width 		= '660px';
							$oFCKeditor->Height 	= '500px';
							$oFCKeditor->Value		= htmlspecialchars_decode($rc['feature_desc']);
								$oFCKeditor->Config['EnterMode'] = 'br';
							$oFCKeditor->Create() ;
						?>
						</div>
                      </div>
                    </div> -->
                              
            
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                  	
                      	<input type="submit" name="add_why_choose" value="Save" onclick="return validate_form('add_feature_form');" class="btn btn-success btn-submit">
                      	<input type="button" name="add_why_choose_cancel" value="Cancel" onclick="listsitefeature();" class="btn btn-success btn-submit">
                      </div>
                    </div>
                  </form>

                </div>
             </div>