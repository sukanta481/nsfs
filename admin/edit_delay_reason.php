<?php
$delay_reason_id = $_REQUEST['delay_reason_id'];
$sl = 'SELECT * FROM  tbl_delay_reason WHERE delay_reason_id = "'.$delay_reason_id.'"';
$q  =mysqli_query($conn,$sl);
$rc =mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listdelay_reason()
	{
		location.href = "delay_reason.php?type=list_delay_reason&lp=cu";
	}
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Delay Reason </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_delay_reason_form" action="" method="post" name="edit_delay_reason_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	   	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Delay Reason Title <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="delay_reason_name" value="<?php echo stripcslashes(html_entity_decode($rc['delay_reason_name']));?>" required="required" id="delay_reason_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div>
                        <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    
 <img src="post_img/<?php print $rc['delay_reason_image'];?>" height="100" width="100" />
 
                      </div>
                    </div> -->
                    
                    
                       <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    

 <input type="file" name="delay_reason_image" id="delay_reason_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Delay Reason Link <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="delay_reason_link" value="<?php echo stripcslashes(html_entity_decode($rc['delay_reason_link']));?>" required="required" id="delay_reason_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div> -->
                                
                  <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Delay Reason No. <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="delay_reason_number" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo $rc['delay_reason_number'];?>">
                     
                       
 
                      </div>
                    </div> -->
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Delay Reason Details <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="delay_reason_details" required="required" class="form-control col-md-7 col-xs-12"  value="<?php echo $rc['delay_reason_details'];?>">
                     
                       
 
                      </div>
                    </div> -->
                    
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
                      	
                  <input type="hidden" name="delay_reason_id" value="<?php print $rc['delay_reason_id']; ?>" />
                      	
                      			
	
                      	
                      	<input type="submit" name="edit_delay_reason" value="Update" onclick="return validate_form('edit_delay_reason_form');" class="btn btn-success btn-submit">
                      	
                      	
                      	
		<input type="button" name="add_delay_reason_cancel" value="Cancel" onclick="listdelay_reason();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>