<script type="text/javascript">
	function listcar()
	{
		location.href = "car.php?type=list_car&lp=cu";
	}
</script>

<div class="x_panel">
                <div class="x_title">
                  <h2>Add New Car </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="add_car_form" action="" method="post" name="add_car_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	<!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car Name <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" name="car_name" required="required" id="car_name" class="form-control col-md-7 col-xs-12"  />
                     
 
                      </div>
                    </div> -->
                    
                 
                    
                     <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Image <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="file" name="car_image" id="car_image" value="" class="form-control col-md-7 col-xs-12" required="required"/>
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car Link <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" name="car_link" required="required" id="car_link" class="form-control col-md-7 col-xs-12"  />
                     
 
                      </div>
                    </div> -->
                          <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car No. <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="car_number" required="required" class="form-control col-md-7 col-xs-12" value="">
                     
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car Details <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="car_details" required="required" class="form-control col-md-7 col-xs-12" value="">
                     
                       
 
                      </div>
                    </div>
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Active Status <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="radio" name="active_status" value="1" checked="checked">Yes
                       <input type="radio" name="active_status" value="0" >No
                     
                       
 
                      </div>
                    </div> -->
                    
                     
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                      	<input type="submit" name="save_car" value="Save" onclick="return validate_form('add_car_form');" class="btn btn-success btn-submit">
		<input type="button" name="add_event_cancel" value="Cancel" onclick="listcar();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
              </div>