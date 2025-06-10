<?php
$team_id = $_REQUEST['team_id'];
$sl = 'SELECT * FROM  tbl_team WHERE team_id = "'.$team_id.'"';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listteam()
	{
		location.href = "team.php?type=list_team&lp=cu";
	}
</script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Team </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_team_form" action="" method="post" name="edit_post_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	<!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Member Type  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<select name="team_type" id="team_type" class="form-control col-md-7 col-xs-12">
 							<option value="0" <?php if($rc['team_type']=='0'){ echo "selected"; }?>>Our Team</option>
 						</select>
                      </div>
                    </div> -->
                     <?php if($rc['team_image']){?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<img src="post_img/<?php print $rc['team_image'];?>" height="100" width="100" />
                      </div>
                    </div>
                    <?php } ?>
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Member Image</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<input type="file" name="team_image" id="team_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div>
                    <!-- <?php if($rc['team_image_webp']){?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<img src="post_img/<?php print $rc['team_image_webp'];?>" height="100" width="100" />
                      </div>
                    </div>
                    <?php } ?> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Member Image Webp</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<input type="file" name="team_image_webp" id="team_image_webp"  class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div> -->
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Member Title  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="text" name="team_title" id="team_title" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['team_title']));?>" required="required" />
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Member Designation  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="text" name="team_desg" id="team_desg" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['team_desg']));?>" required="required" />
                      </div>
                    </div>
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Member Work Area  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="text" name="team_work_area" id="team_work_area" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['team_work_area']));?>" required="required" />
                      </div>
                    </div> -->
                                 
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Sort Details 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
					 <textarea name="team_srt_desc" id="team_srt_desc" class="form-control col-md-7 col-xs-12"/><?php print $rc['team_srt_desc'];?></textarea>
                      </div>
                    </div>
                     
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Details</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
	                      	<div class="txtarea-wysiwyg">
								<?php
									$oFCKeditor = new FCKeditor('post_desc') ;
									$oFCKeditor->BasePath	= 'fckeditor/';
									//$oFCKeditor->ToolbarSet = 'Basic';
									$oFCKeditor->Width 		= '660px';
									$oFCKeditor->Height 	= '500px';
									$oFCKeditor->Value		= htmlspecialchars_decode($rc['post_desc']);
									$oFCKeditor->Create() ;
								?>
							</div>
                		</div>
                    </div> -->     
                   
                       
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                  		<input type="hidden" name="team_id" value="<?php print $rc['team_id']; ?>" />
                      	<input type="submit" name="edit_team" value="Update" onclick="return validate_form('edit_team_form');" class="btn btn-success btn-submit">     	
						<input type="button" name="edit_team_cancel" value="Cancel" onclick="listteam();" class="btn btn-success btn-submit">
                      </div>
                    </div>
                  </form>

                </div>
             </div>