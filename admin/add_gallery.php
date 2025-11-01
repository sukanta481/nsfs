<script type="text/javascript">
	function listgallery()
	{
		location.href = "gallery.php?type=list_gallery&lp=cu";
	}
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Add Gallery </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="add_gallery_form" action="" method="post" name="add_gallery_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
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
							<option value="<?= $get_gallery_category_row['gallery_category_id'];?>" ><?= $get_gallery_category_row['gallery_category_title'];?></option>
							<?php	
							}
 							?>
 						</select>
                      </div>
                    </div>			
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Gallery Image</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<input type="file" name="gallery_image" id="gallery_image" value="" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >gallery Image Webp</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<input type="file" name="gallery_image_webp" id="gallery_image_webp" value="" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div> -->
                    
                     <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >gallery Image Mobile</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 							<input type="file" name="gallery_image_mobile" id="gallery_image_mobile" value="" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div> -->
                    
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Name <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="gallery_title" required="required" id="gallery_title" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                     <!-- <div class="item form-group">

                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Gallery Link <span class="required">*</span>

                      </label>

                      <div class="col-md-6 col-sm-6 col-xs-12">

                        <input type="text" name="gallery_link" required="required" id="gallery_link" class="form-control col-md-7 col-xs-12"  />

                      </div>

                    </div> -->

                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Details</label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      	<div class="txtarea-wysiwyg">
						<?php
							$oFCKeditor = new FCKeditor('gallery_desc') ;
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
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                      	<input type="submit" name="save_gallery" value="Save" onclick="return validate_form('add_gallery_form');" class="btn btn-success btn-submit">
						<input type="button" name="add_gallery_cancel" value="Cancel" onclick="listgallery();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
              </div>