<?php
$car_id = $_REQUEST['car_id'];
$sl = 'SELECT * FROM  tbl_car WHERE car_id = "'.$car_id.'"';
$q  =mysqli_query($conn,$sl);
$rc =mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listcar()
	{
		location.href = "car.php?type=list_car&lp=cu";
	}
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Car </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_car_form" action="" method="post" name="edit_car_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	   	<!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car Name <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="car_name" value="<?php echo stripcslashes(html_entity_decode($rc['car_name']));?>" required="required" id="car_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div> -->
                        <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    
 <img src="post_img/<?php print $rc['car_image'];?>" height="100" width="100" />
 
                      </div>
                    </div> -->
                    
                    
                       <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Image 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 
    

 <input type="file" name="car_image" id="car_image" class="form-control col-md-7 col-xs-12" value=""   />
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car Link <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="car_link" value="<?php echo stripcslashes(html_entity_decode($rc['car_link']));?>" required="required" id="car_cat" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div> -->
                                
                  <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car No. <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="car_number" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo $rc['car_number'];?>">
                     
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car Details <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="car_details" required="required" class="form-control col-md-7 col-xs-12"  value="<?php echo $rc['car_details'];?>">
                     
                       
 
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
                      	
                  <input type="hidden" name="car_id" value="<?php print $rc['car_id']; ?>" />
                      	
                      			
	
                      	
                      	<input type="submit" name="edit_car" value="Update" onclick="return validate_form('edit_car_form');" class="btn btn-success btn-submit">
                      	
                      	
                      	
		<input type="button" name="add_car_cancel" value="Cancel" onclick="listcar();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>