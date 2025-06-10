<?php
$feature_id = $_REQUEST['feature_id'];
$sl = 'SELECT * FROM  tbl_why_choose WHERE feature_id = "'.$feature_id.'"';
$q  =mysqli_query($conn,$sl);
$rc =mysqli_fetch_array($q);
?>
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

                  <form id="edit_feature_form" action="" method="post" name="edit_feature_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" > Feature Title <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      	<input type="text" name="feature_title" value="<?php echo stripcslashes(html_entity_decode($rc['feature_title']));?>" required="required" id="feature_title" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<img src="post_img/<?php print $rc['feature_image'];?>" height="100" width="100" />
                      </div>
                    </div> -->
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
                      	<input type="text" name="feature_link" value="<?php echo stripcslashes(html_entity_decode($rc['feature_link']));?>" required="required" id="feature_link" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div> -->
                   
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Feature Count<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     	<input type="number" name="feature_count" value="<?php echo stripcslashes(html_entity_decode($rc['feature_count']));?>" required="required" id="feature_count" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>     -->   
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" > Feature Text <span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      
                      	<textarea name="feature_text" id="feature_text" class="form-control col-md-7 col-xs-12"><?php echo stripcslashes(html_entity_decode($rc['feature_text']));?></textarea>
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
                  		<input type="hidden" name="feature_id" value="<?php print $rc['feature_id']; ?>" />
                      	<input type="submit" name="edit_why_choose" value="Update" onclick="return validate_form('edit_feature_form');" class="btn btn-success btn-submit">
                      	<input type="button" name="edit_why_choose_cancel" value="Cancel" onclick="listsitefeature();" class="btn btn-success btn-submit">
                      </div>
                    </div>
                  </form>

                </div>
             </div>