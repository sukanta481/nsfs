<?php
$service_id = $_REQUEST['service_id'];
$sl = 'SELECT * FROM  tbl_service WHERE service_id = "'.$service_id.'"';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listservice()
	{
		location.href = "service.php?type=list_service&lp=cu";
	}
</script>

<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Service </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_service_form" action="" method="post" name="edit_service_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
             <!-- <div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Title  <span class="required">*</span></label>
		        <div class="col-md-6 col-sm-6 col-xs-12">
		 			<input type="text" name="meta_title" id="meta_title" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['meta_title']));?>" required="required" />
		        </div>
        	</div> -->   
            <!-- <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Keyword  <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="text" name="meta_keyword" id="meta_keyword" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['meta_keyword']));?>" required="required" />
                </div>
            </div> -->
            <!-- <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Description</label>
              	<div class="col-md-6 col-sm-6 col-xs-12">
 					<textarea name="meta_desc" class="form-control col-md-7 col-xs-12"><?php echo stripslashes(html_entity_decode($rc['meta_desc']));?></textarea>
              	</div>
            </div> -->
                  	
                  	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Service Title  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="text" name="service_title" id="service_title" class="form-control col-md-7 col-xs-12" value="<?= $rc['service_title'];?>" required="required" />
                      </div>
                    </div>
                     <?php if($rc['service_small_image']){ ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<img src="post_img/<?= $rc['service_small_image'];?>" height="100" width="100" />
                      </div>
                    </div>
                    <?php } ?>
                      <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Service Small Image<span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="file" name="service_small_image" id="service_small_image" value="" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                     <?php if($rc['service_image']){ ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<img src="post_img/<?= $rc['service_image'];?>" height="100" width="100" />
                      </div>
                    </div>
                    <?php } ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Service Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="file" name="service_image" id="service_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div>
                                 
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >service Sort Details 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
					 <textarea name="service_srt_desc" id="service_srt_desc" class="form-control col-md-7 col-xs-12"/><?php print $rc['service_srt_desc'];?></textarea>
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >service Details
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12"><div class="txtarea-wysiwyg">
						<?php
							$oFCKeditor = new FCKeditor('service_desc') ;
							$oFCKeditor->BasePath	= 'fckeditor/';
							//$oFCKeditor->ToolbarSet = 'Basic';
							$oFCKeditor->Width 		= '660px';
							$oFCKeditor->Height 	= '500px';
							$oFCKeditor->Value		= htmlspecialchars_decode($rc['service_desc']);
							$oFCKeditor->Create() ;
						?>
						</div>
				       </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Service Link<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="service_link" required="required" id="service_link" class="form-control col-md-7 col-xs-12" value="<?= $rc['service_link'];?>"  />
                      </div>
                    </div>
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >service Overview
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12"><div class="txtarea-wysiwyg">
						<?php
							$oFCKeditor = new FCKeditor('service_overview') ;
							$oFCKeditor->BasePath	= 'fckeditor/';
							//$oFCKeditor->ToolbarSet = 'Basic';
							$oFCKeditor->Width 		= '660px';
							$oFCKeditor->Height 	= '500px';
							$oFCKeditor->Value		= htmlspecialchars_decode($rc['service_overview']);
							$oFCKeditor->Create() ;
						?>
						</div>
				       </div>
                    </div>     --> 
                   
                       
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">	
                  			<input type="hidden" name="service_id" value="<?php print $rc['service_id']; ?>" />
                      		<input type="submit" name="edit_service" value="Update" onclick="return validate_form('edit_service_form');" class="btn btn-success btn-submit">
							<input type="button" name="add_service_cancel" value="Cancel" onclick="listservice();" class="btn btn-success btn-submit">
                      </div>
                    </div>
                  </form>

                </div>
             </div>