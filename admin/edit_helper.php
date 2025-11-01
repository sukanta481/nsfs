<?php
$helper_id = $_REQUEST['helper_id'];
$sl = 'SELECT * FROM  tbl_helper WHERE helper_id = "'.$helper_id.'"';
$q  =mysqli_query($conn,$sl);
$rc =mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listhelper()
	{
		location.href = "helper.php?type=list_helper&lp=cu";
	}
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Helper </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_helper_form" action="" method="post" name="edit_helper_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	   	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Helper Name <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="helper_name" value="<?php echo stripcslashes(html_entity_decode($rc['helper_name']));?>" required="required" id="helper_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div>
                        <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    
 <img src="post_img/<?php print $rc['helper_image'];?>" height="100" width="100" />
 
                      </div>
                    </div> -->
                    
                    
                       <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    

 <input type="file" name="helper_image" id="helper_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Helper Link <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="helper_link" value="<?php echo stripcslashes(html_entity_decode($rc['helper_link']));?>" required="required" id="helper_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div> -->
                                
                  <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Helper Phone No. <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="helper_number" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo $rc['helper_number'];?>">
                     
                       
 
                      </div>
                    </div>
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Active Status <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="radio" name="active_status" value="1" <?php if($rc['active_status']==1){ echo "checked";}?>>Yes
                       <input type="radio" name="active_status" value="0" <?php if($rc['active_status']==0){ echo "checked";}?>>No
                     
                       
 
                      </div>
                    </div> -->
                              
            
                    
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                  <input type="hidden" name="helper_id" value="<?php print $rc['helper_id']; ?>" />
                      	
                      			
	
                      	
                      	<input type="submit" name="edit_helper" value="Update" onclick="return validate_form('edit_helper_form');" class="btn btn-success btn-submit">
                      	
                      	
                      	
		<input type="button" name="add_helper_cancel" value="Cancel" onclick="listhelper();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>