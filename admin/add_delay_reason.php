<script type="text/javascript">
	function listdelay_reason()
	{
		location.href = "delay_reason.php?type=list_delay_reason&lp=cu";
	}
</script>

<div class="x_panel">
                <div class="x_title">
                  <h2>Add New Delay Reason </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="add_delay_reason_form" action="" method="post" name="add_delay_reason_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Delay Reason Title <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" name="delay_reason_name" required="required" id="delay_reason_name" class="form-control col-md-7 col-xs-12"  />
                     
 
                      </div>
                    </div>
                    
                 
                    
                     <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Image <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="file" name="delay_reason_image" id="delay_reason_image" value="" class="form-control col-md-7 col-xs-12" required="required"/>
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Delay Reason Link <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" name="delay_reason_link" required="required" id="delay_reason_link" class="form-control col-md-7 col-xs-12"  />
                     
 
                      </div>
                    </div> -->
                          <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Delay Reason No. <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="delay_reason_number" required="required" class="form-control col-md-7 col-xs-12" value="">
                     
                       
 
                      </div>
                    </div> -->
                    
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Delay Reason Details <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="delay_reason_details" required="required" class="form-control col-md-7 col-xs-12" value="">
                     
                       
 
                      </div>
                    </div> -->
                    
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
                      	
                      	<input type="submit" name="save_delay_reason" value="Save" onclick="return validate_form('add_delay_reason_form');" class="btn btn-success btn-submit">
		<input type="button" name="add_event_cancel" value="Cancel" onclick="listdelay_reason();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
              </div>