<script type="text/javascript">
	function listservice()
	{
		location.href = "service.php?type=list_service&lp=cu";
	}
</script>

<div class="x_panel">
                <div class="x_title">
                  <h2>Add New Service </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="add_service_form" action="" method="post" name="add_service_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	<!-- <div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Title  <span class="required">*</span></label>
		        <div class="col-md-6 col-sm-6 col-xs-12">
		 			<input type="text" name="meta_title" id="meta_title" class="form-control col-md-7 col-xs-12" value="" required="required" />
		        </div>
        	</div>   --> 
            <!-- <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Keyword  <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="text" name="meta_keyword" id="meta_keyword" class="form-control col-md-7 col-xs-12" value="" required="required" />
                </div>
            </div> -->
            <!-- <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Description</label>
              	<div class="col-md-6 col-sm-6 col-xs-12">
 					<textarea name="meta_desc" class="form-control col-md-7 col-xs-12"></textarea>
              	</div>
            </div> -->
                      
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Service Title<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="service_title" required="required" id="service_title" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                   <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Service Small Image<span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="file" name="service_small_image" id="service_small_image" value="" class="form-control col-md-7 col-xs-12"  required="required"/>
                      </div>
                    </div>
                    
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Service Image<span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="file" name="service_image" id="service_image" value="" class="form-control col-md-7 col-xs-12"  required="required"/>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >service Sort Details</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<textarea name="service_srt_desc" id="service_srt_desc" class="form-control col-md-7 col-xs-12"/></textarea>
                      </div>
                    </div>
                    
                    <div class="item form-group">
			             <label class="control-label col-md-3 col-sm-3 col-xs-12" >service Details </label>
			             <div class="col-md-6 col-sm-6 col-xs-12">
			             	<div class="txtarea-wysiwyg">
						<?php
							$oFCKeditor = new FCKeditor('service_desc') ;
							$oFCKeditor->BasePath	= 'fckeditor/';
							//$oFCKeditor->ToolbarSet = 'Basic';
							$oFCKeditor->Width 		= '660px';
							$oFCKeditor->Height 	= '500px';
							$oFCKeditor->Value		= '';
							$oFCKeditor->Create() ;
						?>
						</div>
					</div>
				  </div>
				  
                    
				  <!-- <div class="item form-group">
			             <label class="control-label col-md-3 col-sm-3 col-xs-12" >service Overview </label>
			             <div class="col-md-6 col-sm-6 col-xs-12">
			             	<div class="txtarea-wysiwyg">
						<?php
							$oFCKeditor = new FCKeditor('service_overview') ;
							$oFCKeditor->BasePath	= 'fckeditor/';
							//$oFCKeditor->ToolbarSet = 'Basic';
							$oFCKeditor->Width 		= '660px';
							$oFCKeditor->Height 	= '500px';
							$oFCKeditor->Value		= '';
							$oFCKeditor->Create() ;
						?>
						</div>
					</div>
				  </div> -->
                   
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Service Link<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="service_link" required="required" id="service_link" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                     
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                      	<input type="submit" name="save_service" value="Save" onclick="return validate_form('add_service_form');" class="btn btn-success btn-submit">
		<input type="button" name="add_service_cancel" value="Cancel" onclick="listservice();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
              </div>