<?php
$driver_id = $_REQUEST['driver_id'];
$sl = 'SELECT * FROM  tbl_driver WHERE driver_id = "'.$driver_id.'"';
$q  =mysqli_query($conn,$sl);
$rc =mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listdriver()
	{
		location.href = "driver.php?type=list_driver&lp=cu";
	}
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Driver </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_driver_form" action="" method="post" name="edit_driver_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	   	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Driver Name <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="driver_name" value="<?php echo stripcslashes(html_entity_decode($rc['driver_name']));?>" required="required" id="driver_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div>
                        <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    
 <img src="post_img/<?php print $rc['driver_image'];?>" height="100" width="100" />
 
                      </div>
                    </div> -->
                    
                    
                       <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    

 <input type="file" name="driver_image" id="driver_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Driver Link <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="driver_link" value="<?php echo stripcslashes(html_entity_decode($rc['driver_link']));?>" required="required" id="driver_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div> -->
                                
                  <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Driver Phone No. <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="driver_number" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo $rc['driver_number'];?>">
                     
                       
 
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
                      	
                  <input type="hidden" name="driver_id" value="<?php print $rc['driver_id']; ?>" />
                      	
                      			
	
                      	
                      	<input type="submit" name="edit_driver" value="Update" onclick="return validate_form('edit_driver_form');" class="btn btn-success btn-submit">
                      	
                      	
                      	
		<input type="button" name="add_driver_cancel" value="Cancel" onclick="listdriver();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>