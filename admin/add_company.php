<script type="text/javascript">
	function listcompany()
	{
		location.href = "company.php?type=list_company&lp=cu";
	}
</script>

<div class="x_panel">
                <div class="x_title">
                  <h2>Add Consignor</h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="add_company_form" action="" method="post" name="add_company_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Title <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" name="company_title" required="required" id="company_title" class="form-control col-md-7 col-xs-12"  />
                     
 
                      </div>
                    </div>
                    
                  <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Address <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="company_address" value="" required="required" id="company_address" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Phone <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="company_phone" value="" required="required" id="company_phone" class="form-control col-md-7 col-xs-12"  />
                     
                       
 
                      </div>
                    </div>
                    
                     <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Image <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
 <input type="file" name="company_image" id="company_image" value="" class="form-control col-md-7 col-xs-12" required="required"/>
                      </div>
                    </div> -->
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company Link <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <input type="text" name="company_link" required="required" id="company_link" class="form-control col-md-7 col-xs-12"  />
                     
 
                      </div>
                    </div> -->
                            <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Company short Description <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <textarea name="company_short_desc"  required="required" class="form-control col-md-7 col-xs-12"  /></textarea>
                     
                       
 
                      </div>
                    </div> -->         
                       
                     
                    
                     
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                      	<input type="submit" name="save_company" value="Save" onclick="return validate_form('add_company_form');" class="btn btn-success btn-submit">
		<input type="button" name="add_event_cancel" value="Cancel" onclick="listcompany();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
              </div>