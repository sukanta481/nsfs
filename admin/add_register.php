<script type="text/javascript">
	function listregister()
	{
		location.href = "register.php?type=list_register&lp=cu";
	}
</script>
<script>
	function add_new_client()
	{
		ctr=$("#ctr").val();
		ctr=parseInt(ctr)+1;
		//alert(ctr);
		
		
		var Ajax=$.ajax({
		type: "POST",
		url: "ajax_add_new_client.php",
		dataType: 'html',
		data:"q="+ctr,
		success: function(html){
		
		$("#ctr").val(ctr)	
		$("#add_new_sec").append(html);
		
		},error: function(){
		},complete: function(){
		}
		});
	}
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Add New Register </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="add_register_form" action="" method="post" name="add_register_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Rented Car : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="radio" name="rented_car" value="1" <?php if($rc['rented_car']==1){ echo "checked";}?> onclick="change_rented_car(this.value);">Yes
                       <input type="radio" name="rented_car" value="0" <?php if($rc['rented_car']==0){ echo "checked";}?> onclick="change_rented_car(this.value);">No
                     
                       
 
                      </div>
                    </div>
                    
                    <script>
  	function change_rented_car(v)
  	{
  		if(v==1)
  		{
  			$("#personal_car_sec").hide();
  			$("#rented_car_sec").show();
  			$("#driver_number_rent").val("");
  		}
  		else
  		{
  			$("#personal_car_sec").show();
  			$("#rented_car_sec").hide();
  			$("#driver_number").val("");
  		}
  	}
  </script>                 
  
  					<div id="rented_car_sec" style="display:none">
  						<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car No : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="car_number" id="car_number" value="" class="form-control col-md-7 col-xs-12"  />
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Driver Name : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="driver_name" id="driver_name" value="" class="form-control col-md-7 col-xs-12"  />
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Driver Number : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="driver_number_rent" id="driver_number_rent" value="" class="form-control col-md-7 col-xs-12"  />
 
                      </div>
                    </div>
                    
                    
                    
  					</div> 
                  	
                  	<div id="personal_car_sec"  >
                  	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car No : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <select name="car_id" required="required" id="car_id" class="form-control col-md-7 col-xs-12" >
                      	<option value="">Select</option>
                      	<?php
                      	$get_car_sql="select * from  tbl_car order by car_number asc";
                      	$get_car_rs=mysqli_query($conn,$get_car_sql);
						while($get_car_row=mysqli_fetch_array($get_car_rs))
						{
                      	?>
                      	<option value="<?= $get_car_row['car_id'];?>"><?= $get_car_row['car_number'];?></option>
                      	<?php
						}
                      	?>
                      </select>
                     
 
                      </div>
                    </div>
                    
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Driver Name : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <select name="driver_id" required="required" id="driver_id" class="form-control col-md-7 col-xs-12" onchange="get_driver_phone_no(this.value);">
                      	<option value="">Select</option>
                      	<?php
                      	$get_driver_sql="select * from   tbl_driver order by driver_name asc";
                      	$get_driver_rs=mysqli_query($conn,$get_driver_sql);
						while($get_driver_row=mysqli_fetch_array($get_driver_rs))
						{
                      	?>
                      	<option value="<?= $get_driver_row['driver_id'];?>"><?= $get_driver_row['driver_name'];?></option>
                      	<?php
						}
                      	?>
                      </select>
                     
 
                      </div>
                    </div>
    <script>
 	function get_driver_phone_no(v)
 	{
 		var Ajax=$.ajax({
		type: "POST",
		url: "ajax_get_driver_phone_no.php",
		dataType: 'html',
		data:"q="+v,
		success: function(html){
		$("#driver_number_sec").html(html);
		},error: function(){
		},complete: function(){
		}
		});
 	}
 </script>                  
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Driver Number : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12" id="driver_number_sec">
                      <input type="text" name="driver_number" required="required" id="driver_number" class="form-control col-md-7 col-xs-12"  />
                     
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Helper Name : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <select name="helper_id" required="required" id="helper_id" class="form-control col-md-7 col-xs-12" onchange="get_helper_phone_no(this.value)">
                      	<option value="">Select</option>
                      	<?php
                      	$get_helper_sql="select * from   tbl_helper order by helper_name asc";
                      	$get_helper_rs=mysqli_query($conn,$get_helper_sql);
						while($get_helper_row=mysqli_fetch_array($get_helper_rs))
						{
                      	?>
                      	<option value="<?= $get_helper_row['helper_id'];?>"><?= $get_helper_row['helper_name'];?></option>
                      	<?php
						}
                      	?>
                      </select>
                     
 
                      </div>
                    </div>
                    <script>
 	function get_helper_phone_no(v)
 	{
 		var Ajax=$.ajax({
		type: "POST",
		url: "ajax_get_helper_phone_no.php",
		dataType: 'html',
		data:"q="+v,
		success: function(html){
		$("#helper_number_sec").html(html);
		},error: function(){
		},complete: function(){
		}
		});
 	}
 </script>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Helper Number : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12" id="helper_number_sec">
                      <input type="text" name="helper_number" required="required" id="helper_number" class="form-control col-md-7 col-xs-12"  />
                     
 
                      </div>
                    </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car Oil Amount : 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12" >
                     
                      
                      <input type="number" name="car_oil_amount" id="car_oil_amount" value="0"  class="form-control col-md-7 col-xs-12" min="0">
                     
                       
 
                      </div>
                    </div>
                    
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >IN Time: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="time" name="car_in_time" id="car_in_time" class="form-control col-md-7 col-xs-12" value="">
                     
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Out Time: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="time" name="car_out_time" id="car_out_time" =class="form-control col-md-7 col-xs-12" value="">
                     
                       
 
                      </div>
                    </div>
                 <h2>Shipping details </h2>
                      <div class="ln_solid"></div>    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignor Company Name: <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <select name="company_id[]" required="required"  class="form-control col-md-7 col-xs-12" >
                      	<option value="">Select</option>
                      	<?php
                      	$get_company_sql="select * from    tbl_company order by company_title asc";
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
                      	$get_helper_sql="select * from    tbl_client order by client_title asc";
                      	$get_helper_rs=mysqli_query($conn,$get_helper_sql);
						while($get_helper_row=mysqli_fetch_array($get_helper_rs))
						{
                      	?>
                      	<option value="<?= $get_helper_row['client_id'];?>" ><?= $get_helper_row['client_title'];?></option>
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
                     
                      
                     
                     <select name="have_eoa_bill_no[]"  id="have_eoa_bill_no" class="form-control col-md-7 col-xs-12" onchange="change_have_eoa_bill(this.value,1)">
                      	<option value="1">Yes</option>
                      	<option value="0" selected>No</option>
                      	
                      </select>
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group" style="display:none;"  id="sec_eoa1">
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
                     
                      
                      <!-- <input type="radio" name="pay_to[]" id="pay_to1" value="1" >Yes
                       <input type="radio" name="pay_to[]"  value="0" checked="checked">No -->
                     <select name="pay_to[]"  id="pay_to" class="form-control col-md-7 col-xs-12" >
                      	<option value="1">Yes</option>
                      	<option value="0" selected>No</option>
                      	
                      </select>
                       
 
                      </div>
                    </div>
                    <div id="add_new_sec"></div>
                    <input type="hidden" id="ctr" value="1"/>
                    <script>
                    	function change_have_eoa_bill(v,id)
                    	{
                    		if(v==1)
                    		{
                    			$("#sec_eoa"+id).show();
                    		}
                    		else
                    		{
                    			$("#sec_eoa"+id).hide();
                    		}
                    	}
                    </script>
                     	<input type="button" name="add_new" value="Add Name" onclick="return add_new_client();" class="btn btn-success btn-submit">
                    
                          
                    
                    
                    
                     
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	
                      	<input type="submit" name="save_register" value="Save" onclick="return validate_form('add_register_form');" class="btn btn-success btn-submit">
		<input type="button" name="add_event_cancel" value="Cancel" onclick="listregister();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
              </div>