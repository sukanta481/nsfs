<?php
$gallery_category_id = $_REQUEST['gallery_category_id'];
$sl = 'SELECT * FROM  tbl_gallery_category WHERE gallery_category_id = "'.$gallery_category_id.'"';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listgallery_category()
	{
		location.href = "gallery_category.php?type=list_gallery_category&lp=cu";
	}
</script>


<div class="x_panel">
                <div class="x_title">
                  <h2>Edit gallery category </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_gallery_category_form" action="" method="post" name="edit_gallery_category_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
             
                  	
                  	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >gallery category Title  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="text" name="gallery_category_title" id="gallery_category_title" class="form-control col-md-7 col-xs-12" value="<?= $rc['gallery_category_title'];?>" required="required" />
                      </div>
                    </div>
                      
                      
                   
                       
                    
                   
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">	
                  			<input type="hidden" name="gallery_category_id" value="<?php print $rc['gallery_category_id']; ?>" />
                      		<input type="submit" name="edit_gallery_category" value="Update" onclick="return validate_form('edit_gallery_category_form');" class="btn btn-success btn-submit">
							<input type="button" name="add_gallery_category_cancel" value="Cancel" onclick="listgallery_category();" class="btn btn-success btn-submit">
                      </div>
                    </div>
                  </form>

                </div>
             </div>