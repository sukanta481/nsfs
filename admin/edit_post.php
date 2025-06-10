<?php
$post_id = $_REQUEST['post_id'];
$sl = 'SELECT * FROM  tbl_post WHERE post_id = "'.$post_id.'"';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listpost()
	{
		location.href = "post.php?type=list_post&lp=cu";
	}
</script>

<div class="x_panel">
                <div class="x_title">
                  <h2>Edit post </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_post_form" action="" method="post" name="edit_post_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
             <div class="item form-group">
          		<label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Title  <span class="required">*</span></label>
		        <div class="col-md-6 col-sm-6 col-xs-12">
		 			<input type="text" name="meta_title" id="meta_title" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['meta_title']));?>" required="required" />
		        </div>
        	</div>   
            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Keyword  <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
 					<input type="text" name="meta_keyword" id="meta_keyword" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['meta_keyword']));?>" required="required" />
                </div>
            </div>
            <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Description</label>
              	<div class="col-md-6 col-sm-6 col-xs-12">
 					<textarea name="meta_desc" class="form-control col-md-7 col-xs-12"><?php echo stripslashes(html_entity_decode($rc['meta_desc']));?></textarea>
              	</div>
            </div>
                  	
                    
                    
                    <!-- <?php if($rc['author_image']){ ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<img src="post_img/<?= $rc['author_image'];?>" height="100" width="100" />
                      </div>
                    </div>
                    <?php } ?> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Author Image<span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="file" name="author_image" id="author_image" value="" class="form-control col-md-7 col-xs-12"/>
                      </div>
                    </div>	 -->
                  	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Post Title  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="text" name="post_title" id="post_title" class="form-control col-md-7 col-xs-12" value="<?= $rc['post_title'];?>" required="required" />
                      </div>
                    </div>
                      <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Post Date<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="date" name="post_date" required="required" id="post_date" class="form-control col-md-7 col-xs-12"  value="<?= $rc['post_date'];?>"/>
                      </div>
                    </div> -->
                     <?php if($rc['post_image']){ ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<img src="post_img/<?= $rc['post_image'];?>" height="100" width="100" />
                      </div>
                    </div>
                    <?php } ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Post Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="file" name="post_image" id="post_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div>
                                 
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Post Sort Details 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
					 <textarea name="post_srt_desc" id="post_srt_desc" class="form-control col-md-7 col-xs-12"/><?php print $rc['post_srt_desc'];?></textarea>
                      </div>
                    </div>
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Author Title<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="author_title" required="required" id="author_title" class="form-control col-md-7 col-xs-12"  value="<?= $rc['author_title'];?>"/>
                      </div>
                    </div> -->
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Post Details
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12"><div class="txtarea-wysiwyg">
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
                    </div>
                    <!-- <?php if($rc['review_image']){ ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<img src="post_img/<?= $rc['review_image'];?>" height="100" width="100" />
                      </div>
                    </div>
                    <?php } ?> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Review Image<span class="required">*</span></label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="file" name="review_image" id="review_image" value="" class="form-control col-md-7 col-xs-12">
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Review Name<span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="review_name" required="required" id="review_name" class="form-control col-md-7 col-xs-12"  value="<?= $rc['review_name'];?>"/>
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Review Cintent</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<textarea name="review_content" id="review_content" class="form-control col-md-7 col-xs-12"/><?php print $rc['review_content'];?></textarea>
                      </div>
                    </div> -->
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Post Overview
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12"><div class="txtarea-wysiwyg">
						<?php
							$oFCKeditor = new FCKeditor('post_overview') ;
							$oFCKeditor->BasePath	= 'fckeditor/';
							//$oFCKeditor->ToolbarSet = 'Basic';
							$oFCKeditor->Width 		= '660px';
							$oFCKeditor->Height 	= '500px';
							$oFCKeditor->Value		= htmlspecialchars_decode($rc['post_overview']);
							$oFCKeditor->Create() ;
						?>
						</div>
				       </div>
                    </div>     --> 
                   
                       
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">	
                  			<input type="hidden" name="post_id" value="<?php print $rc['post_id']; ?>" />
                      		<input type="submit" name="edit_post" value="Update" onclick="return validate_form('edit_post_form');" class="btn btn-success btn-submit">
							<input type="button" name="add_post_cancel" value="Cancel" onclick="listpost();" class="btn btn-success btn-submit">
                      </div>
                    </div>
                  </form>

                </div>
             </div>