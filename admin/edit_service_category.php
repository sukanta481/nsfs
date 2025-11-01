<?php
$service_category_id = $_REQUEST['service_category_id'];
$sl = 'SELECT * FROM  tbl_service_category WHERE service_category_id = "'.$service_category_id.'"';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listservice_category()
	{
		location.href = "service_category.php?type=list_service_category&lp=cu";
	}
</script>


<div class="x_panel">
                <div class="x_title">
                  <h2>Edit service category </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_service_category_form" action="" method="post" name="edit_service_category_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
             
                  	
                  	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >service category Title  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="text" name="service_category_title" id="service_category_title" class="form-control col-md-7 col-xs-12" value="<?= $rc['service_category_title'];?>" required="required" />
                      </div>
                    </div>
                      
                      
                   <?php if($rc['service_category_image']){ ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<img src="post_img/<?= $rc['service_category_image'];?>" height="100" width="100" />
                      </div>
                    </div>
                    <?php } ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Service Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 						<input type="file" name="service_category_image" id="service_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div>
                                 
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >service Sort Details 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
					 <textarea name="service_category_srt_desc" id="service_srt_desc" class="form-control col-md-7 col-xs-12"/><?php print $rc['service_category_srt_desc'];?></textarea>
                      </div>
                    </div>
                       
                    
                   
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">	
                  			<input type="hidden" name="service_category_id" value="<?php print $rc['service_category_id']; ?>" />
                      		<input type="submit" name="edit_service_category" value="Update" onclick="return validate_form('edit_service_category_form');" class="btn btn-success btn-submit">
							<input type="button" name="add_service_category_cancel" value="Cancel" onclick="listservice_category();" class="btn btn-success btn-submit">
                      </div>
                    </div>
                  </form>

                </div>
             </div>