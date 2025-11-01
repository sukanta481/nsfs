<?php
include("conn.php");
//echo "Failed to connect to MySQL: " . mysqli_connect_error();
$ctr=$_POST['q'];
//echo $ctr;
//exit;
?>
<div class="ln_solid"></div> 
<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignor Company Name: <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <select name="company_id[]" required="required"  class="form-control col-md-7 col-xs-12" >
                      	<option value="">Select</option>
                      	<?php
                      	$get_company_sql="select * from tbl_company order by company_title asc";
                      	$get_company_rs=mysqli_query($conn,$get_company_sql);
						while($get_company_row=mysqli_fetch_array($get_company_rs))
						{
                      	?>
                      	<option value="<?= $get_company_row['company_id'];?>" ><?= $get_company_row['company_title'];?></option>
                      	<?php
						}
                      	?>
                      </select>
                     
                       
 
                      </div>
                    </div>   
<!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignee Company Name: <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <select name="client_id[]" required="required"  class="form-control col-md-7 col-xs-12" >
                      	<option value="">Select</option>
                      	<?php
                      	$get_helper_sql="select * from  tbl_client order by client_title asc";
                      	$get_helper_rs=mysqli_query($conn,$get_helper_sql);
						while($get_helper_row=mysqli_fetch_array($get_helper_rs))
						{
                      	?>
                      	<option value="<?= $get_helper_row['client_id'];?>"><?= $get_helper_row['client_title'];?></option>
                      	<?php
						}
                      	?>
                      </select>
                     
                       
 
                      </div>
                    </div> -->
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignee  Name: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="client_name[]" id="client_name" required="required"  value="" class="form-control col-md-7 col-xs-12" >
                     
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignee  Phone: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="tel" name="client_phone[]" id="client_phone" required="required"  value="" class="form-control col-md-7 col-xs-12" >
                     
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignee  Address: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="client_address[]" id="client_address" required="required"  value="" class="form-control col-md-7 col-xs-12" >
                     
                       
 
                      </div>
                    </div>
                    
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Doc: <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <input type="number" name="doc[]"  required="required" value="0" class="form-control col-md-7 col-xs-12" min="0">
                     
                       
 
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Box (unit): <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <input type="number" name="box[]"  required="required" value="0" class="form-control col-md-7 col-xs-12" min="0">
                     
                       
 
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Weight (kg): <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <input type="number" name="weight[]"  required="required" value="0" class="form-control col-md-7 col-xs-12" min="0">
                     
                       
 
                      </div>
                    </div>
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Unit Price: <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <input type="text" name="unit_price[]"  required="required" value="" class="form-control col-md-7 col-xs-12" min="0">
                     
                       
 
                      </div>
                    </div> -->
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Have EOA Bill? : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     
                     <select name="have_eoa_bill_no[]"  id="have_eoa_bill_no" class="form-control col-md-7 col-xs-12" onchange="change_have_eoa_bill(this.value,<?= $ctr;?>)">
                      	<option value="1">Yes</option>
                      	<option value="0" selected>No</option>
                      	
                      </select>
                       
 
                      </div>
                    </div>
                    
                    
                    <div class="item form-group" style="display:none;"  id="sec_eoa<?= $ctr;?>" >
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >EOA Bill No. : 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <input type="number" name="eoa_bill_no[]"   value="0" class="form-control col-md-7 col-xs-12" min="0">
                     
                       
 
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Pay To : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <!-- <input type="radio" name="pay_to[]" value="1" >Yes
                       <input type="radio" name="pay_to[]" value="0" checked="checked">No -->
                       
                       <select name="pay_to[]"  id="pay_to" class="form-control col-md-7 col-xs-12" >
                      	<option value="1">Yes</option>
                      	<option value="0" selected>No</option>
                      	
                      </select>
                     
                       
 
                      </div>
                    </div>