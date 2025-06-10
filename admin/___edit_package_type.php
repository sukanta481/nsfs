<?php
$package_type_id = $_REQUEST['package_type_id'];
$sl = 'SELECT * FROM  tbl_package_type WHERE package_type_id = "'.$package_type_id.'"';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listpackage_type()
	{
		location.href = "package_type.php?type=list_package_type&lp=cu";
	}
</script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Package type </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_package_type_form" action="" method="post" name="edit_package_type_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	
                       
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Title  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="text" name="meta_title" id="meta_title" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['meta_title']));?>" required="required" />
                      </div>
                    </div> -->
                     
                       <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Keyword  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="text" name="meta_keyword" id="meta_keyword" class="form-control col-md-7 col-xs-12" value="<?php echo stripslashes(html_entity_decode($rc['meta_keyword']));?>" required="required" />
                      </div>
                    </div> -->
                     
                       <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Meta Description 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <textarea class="form-control col-md-7 col-xs-12" name="meta_desc"><?php echo stripslashes(html_entity_decode($rc['meta_desc']));?></textarea>
                      </div>
                    </div> -->
                    
                       
                       
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Name  <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
  <input type="text" name="package_type_name" id="package_type_name" value="<?php echo stripslashes(html_entity_decode($rc['package_type_name']));?>" required="required" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                    
                  
                    
             <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Date <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="testimonial_date" id="datepicker" value="<?php echo stripslashes(html_entity_decode($rc['testimonial_date']));?>" required="required" id="testimonial_date" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div> -->
                    
                       <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Package Type  Description
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12"><div class="txtarea-wysiwyg">
		<?php
			$oFCKeditor = new FCKeditor('package_type_desc') ;
			$oFCKeditor->BasePath	= 'fckeditor/';
			//$oFCKeditor->ToolbarSet = 'Basic';
			$oFCKeditor->Width 		= '660px';
			$oFCKeditor->Height 	= '500px';
			$oFCKeditor->Value		= htmlspecialchars_decode($rc['package_type_desc']);
			$oFCKeditor->Create() ;
		?>
		</div>
                </div>
                    </div>   
                    
                     
                   
                       
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                  <input type="hidden" name="package_type_id" value="<?php print $rc['package_type_id']; ?>" />
                      	
                      			
	
                      	
                      	<input type="submit" name="edit_package_type" value="Update" onclick="return validate_form('edit_package_type_form');" class="btn btn-success btn-submit">
                      	
                      	
                      	
		<input type="button" name="edit_package_type_cancel" value="Cancel" onclick="listpackage_type();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>