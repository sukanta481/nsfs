<script type="text/javascript">
	function listCat()
	{
		location.href = "coupon.php?type=list_coupon&lp=adc&";
	}
</script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
<script>
$(function() {

$( "#datepicker" ).datepicker({
changeMonth: true,
changeYear: true,
minDate: 0,
dateFormat:"yy-mm-dd"
});
$( "#datepicker2" ).datepicker({
changeMonth: true,
changeYear: true,
minDate: 0,
dateFormat:"yy-mm-dd"
});
//$( "#datepicker" ).datepicker( "option", "dateFormat", "yy-mm-dd");
//$( "#datepicker2" ).datepicker( "option", "dateFormat", "yy-mm-dd");
});
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Add Discount Code </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="add_coupon_form" action="" method="post" name="add_coupon_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	
                  	
                  	
                                 
                 
                                  
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Discount Code <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="coupon_code" required="required" id="coupon_code" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                     
                     
                      <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Start Date <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="cupon_st_dt" required="required" id="datepicker" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                    
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >End Date <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="cupon_end_dt" required="required" id="datepicker2" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                    
                   <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Discount Type <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="radio" name="coupon_type" value="1" /> Percentage(%)
									<input type="radio" name="coupon_type" value="0" checked="checked" /> Fixed Value
                      </div>
                    </div>
                    
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Discount <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="cupon_discount" required="required" id="cupon_discount" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                     
                    
                     
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                      	<input type="submit" name="save_coupon" value="Save" onclick="return validate_form('add_coupon_form');" class="btn btn-success btn-submit">
		<input type="button" name="save_coupon_cancel" value="Cancel" onclick="listCat();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
              </div>