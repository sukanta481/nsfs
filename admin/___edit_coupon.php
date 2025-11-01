<?php
$id = $_REQUEST['id'];
$edit_sql = "select * from tbl_coupon where id='".$id."'";
$edit_exe = mysqli_query($conn,$edit_sql) or die(mysqli_error());
$edit_row = mysqli_fetch_array($edit_exe);
?>
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
dateFormat:"yy-mm-dd"
});
$( "#datepicker2" ).datepicker({
changeMonth: true,
changeYear: true,
dateFormat:"yy-mm-dd"
});
//$( "#datepicker" ).datepicker( "option", "dateFormat", "yy-mm-dd");
//$( "#datepicker2" ).datepicker( "option", "dateFormat", "yy-mm-dd");
});
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Discount Code </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="add_coupon_form" action="" method="post" name="add_coupon_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	
                  	
                  	
                  	  
                 
                                  
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Discount Code <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="coupon_code" value="<?php echo $edit_row['coupon_code']; ?>" required="required" id="coupon_code" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                     
                     <?php
			$st_date = explode("-",$edit_row['cupon_st_dt']);
								$dt = $st_date[2];
								$month = $st_date[1];
								$year = $st_date[0];
								$st_date = $dt."-".$month."-".$year;
			
			$end_date = explode("-",$edit_row['cupon_end_dt']);
								$dt1 = $end_date[2];
								$month1 = $end_date[1];
								$year1 = $end_date[0];
								$end_date = $dt1."-".$month1."-".$year1;
			?>				
		
                      <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Start Date <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="cupon_st_dt" required="required" value="<?php echo $edit_row['cupon_st_dt']; ?>" id="datepicker" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                    
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >End Date <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="cupon_end_dt" required="required" value="<?php echo $edit_row['cupon_end_dt']; ?>" id="datepicker2" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Discount Type <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="radio" name="coupon_type" value="1" <?php if($edit_row['cupon_type']==1){echo 'checked';}else{} ?>/> Percentage(%)
						<input type="radio" name="coupon_type" value="0" <?php if($edit_row['cupon_type']==0){echo 'checked';}else{} ?> /> Fixed Value
                      </div>
                    </div>
                    
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Discount <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="text" name="cupon_discount" required="required" id="cupon_discount" value="<?php echo $edit_row['cupon_discount']; ?>" class="form-control col-md-7 col-xs-12"  />
                      </div>
                    </div>
                     
                   		                     
                     
                     
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	<input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>" />
			<input type="submit" name="edit_coupon" value="Save" onclick="return validate_form('add_coupon_form');" class="btn btn-success btn-submit">
			<input type="button" name="edit_coupon_cancel" value="Cancel" onclick="listCat();" class="btn btn-success btn-submit">
                      	
                        
                      </div>
                    </div>
                  </form>

                </div>
              </div>