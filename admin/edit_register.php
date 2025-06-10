<?php
$register_id = $_REQUEST['register_id'];
$sl = 'SELECT * FROM  tbl_register WHERE register_id = "'.$register_id.'"';
$q  =mysqli_query($conn,$sl);
$rc =mysqli_fetch_array($q);


$sl1 = 'SELECT * FROM   tbl_shipping WHERE register_id = "'.$register_id.'"';
$q1  =mysqli_query($conn,$sl1);
$rc1 =mysqli_fetch_array($q1);
?>
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
                  <h2>Edit Register </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_register_form" action="" method="post" name="edit_register_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	
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
  
  					<div id="rented_car_sec" <?php if($rc['rented_car']==1){?>style="display:block"<?php }else{?>style="display:none"<?php } ?>>
  						<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car No : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="car_number" id="car_number" value="<?= $rc['car_number'];?>" class="form-control col-md-7 col-xs-12"  />
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Driver Name : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="driver_name" id="driver_name" value="<?= $rc['driver_name'];?>" class="form-control col-md-7 col-xs-12"  />
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Driver Number : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      <input type="text" name="driver_number_rent" id="driver_number_rent" value="<?= $rc['driver_number'];?>" class="form-control col-md-7 col-xs-12"  />
 
                      </div>
                    </div>
                    
                    
                    
  					</div> 
                    
                  	<div id="personal_car_sec"  <?php if($rc['rented_car']==0){?>style="display:block"<?php }else{?>style="display:none"<?php } ?>>
                  	   	<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car No : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <select name="car_id"  id="car_id" class="form-control col-md-7 col-xs-12" >
                      	<option value="">Select</option>
                      	<?php
                      	$get_car_sql="select * from  tbl_car order by car_number asc";
                      	$get_car_rs=mysqli_query($conn,$get_car_sql);
						while($get_car_row=mysqli_fetch_array($get_car_rs))
						{
                      	?>
                      	<option value="<?= $get_car_row['car_id'];?>" <?php if($get_car_row['car_id']==$rc['car_id']){ echo "selected";}?>><?= $get_car_row['car_number'];?></option>
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
                      <select name="driver_id"  id="driver_id" class="form-control col-md-7 col-xs-12" onchange="get_driver_phone_no(this.value);">
                      	<option value="">Select</option>
                      	<?php
                      	$get_driver_sql="select * from   tbl_driver order by driver_name asc";
                      	$get_driver_rs=mysqli_query($conn,$get_driver_sql);
						while($get_driver_row=mysqli_fetch_array($get_driver_rs))
						{
                      	?>
                      	<option value="<?= $get_driver_row['driver_id'];?>" <?php if($get_driver_row['driver_id']==$rc['driver_id']){ echo "selected";}?>><?= $get_driver_row['driver_name'];?></option>
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
                      <input type="text" name="driver_number"  id="driver_number" value="<?= $rc['driver_number'];?>" class="form-control col-md-7 col-xs-12"  />
                     
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Helper Name : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <select name="helper_id"  id="helper_id" class="form-control col-md-7 col-xs-12" onchange="get_helper_phone_no(this.value);">
                      	<option value="">Select</option>
                      	<?php
                      	$get_helper_sql="select * from   tbl_helper order by helper_name asc";
                      	$get_helper_rs=mysqli_query($conn,$get_helper_sql);
						while($get_helper_row=mysqli_fetch_array($get_helper_rs))
						{
                      	?>
                      	<option value="<?= $get_helper_row['helper_id'];?>" <?php if($get_helper_row['helper_id']==$rc['helper_id']){ echo "selected";}?>><?= $get_helper_row['helper_name'];?></option>
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
                      <input type="text" name="helper_number"  id="helper_number" value="<?= $rc['helper_number'];?>" class="form-control col-md-7 col-xs-12"  />
                     
 
                      </div>
                    </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Car Oil Amount : 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="number" name="car_oil_amount" id="car_oil_amount"  value="<?= $rc['car_oil_amount'];?>" class="form-control col-md-7 col-xs-12" min="0">
                     
                       
 
                      </div>
                    </div>
                    
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >IN Time: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="time" name="car_in_time" id="car_in_time"  value="<?= $rc['car_in_time'];?>" class="form-control col-md-7 col-xs-12" >
                     
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Out Time: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="time" name="car_out_time" id="car_out_time"  value="<?= $rc['car_out_time'];?>" class="form-control col-md-7 col-xs-12" >
                     
                       
 
                      </div>
                    </div>
                 
                     <h2>Shipping details </h2>
                      <div class="ln_solid"></div>  
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Tip No :
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      
                     <?= $rc1['trip_no'];?>
 
                      </div>
                    </div>
                    
                    
                        
                    <?php
                    $get_shipping_sql="select * from   tbl_shipping_details where shipping_id='".$rc1['shipping_id']."'";
					$get_shipping_rs=mysqli_query($conn, $get_shipping_sql);
					while($get_shipping_row=mysqli_fetch_array($get_shipping_rs))
					{
					?>
					<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignor Company Name: <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <select name="company_id_edit[]" required="required"  class="form-control col-md-7 col-xs-12" >
                      	<option value="">Select</option>
                      	<?php
                      	$get_company_sql="select * from    tbl_company order by company_title asc";
                      	$get_company_rs=mysqli_query($conn,$get_company_sql);
						while($get_company_row=mysqli_fetch_array($get_company_rs))
						{
                      	?>
                      	<option value="<?= $get_company_row['company_id'];?>" <?php if($get_company_row['company_id']==$get_shipping_row['company_id']){ echo "selected";}?>><?= $get_company_row['company_title'];?></option>
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
                     
                      
                      <select name="client_id_edit[]" required="required"  class="form-control col-md-7 col-xs-12" >
                      	<option value="">Select</option>
                      	<?php
                      	$get_helper_sql="select * from    tbl_client order by client_title asc";
                      	$get_helper_rs=mysqli_query($conn,$get_helper_sql);
						while($get_helper_row=mysqli_fetch_array($get_helper_rs))
						{
                      	?>
                      	<option value="<?= $get_helper_row['client_id'];?>" <?php if($get_helper_row['client_id']==$get_shipping_row['client_id']){ echo "selected";}?>><?= $get_helper_row['client_title'];?></option>
                      	<?php
						}
                      	?>
                      </select> 
                     
                       
 
                      </div>
                    </div>-->
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignee  Name: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="client_name_edit[]" id="client_name_edit" required="required"  value="<?= $get_shipping_row['client_name'];?>" class="form-control col-md-7 col-xs-12" >
                     
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignee  Phone: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="tel" name="client_phone_edit[]" id="client_phone_edit" required="required"  value="<?= $get_shipping_row['client_phone'];?>" class="form-control col-md-7 col-xs-12" >
                     
                       
 
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignee  Address: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <input type="text" name="client_address_edit[]" id="client_address_edit" required="required"  value="<?= $get_shipping_row['client_address'];?>" class="form-control col-md-7 col-xs-12" >
                     
                       
 
                      </div>
                    </div>
                    
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Doc: <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <input type="number" name="doc_edit[]"  required="required" value="<?= $get_shipping_row['doc'];?>" class="form-control col-md-7 col-xs-12" min="0">
                     
                       
 
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Box (unit): <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <input type="number" name="box_edit[]"  required="required" value="<?= $get_shipping_row['box'];?>" class="form-control col-md-7 col-xs-12" min="0">
                     
                       
 
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Weight (kg): <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <input type="number" name="weight_edit[]"  required="required" value="<?= $get_shipping_row['weight'];?>" class="form-control col-md-7 col-xs-12" min="0">
                     
                       
 
                      </div>
                    </div>
                    <!-- <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Unit Price: <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <input type="text" name="unit_price_edit[]"  required="required" value="<?= $get_shipping_row['unit_price'];?>" class="form-control col-md-7 col-xs-12" min="0">
                     
                       
 
                      </div>
                    </div> -->
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Have EOA Bill? : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     
                     <select name="have_eoa_bill_no_edit[]"  id="have_eoa_bill_no" class="form-control col-md-7 col-xs-12" onchange="change_have_eoa_bill_edit(this.value,<?= $get_shipping_row['shipping_details_id'];?>)">
                      	<option value="1" <?php if($get_shipping_row['have_eoa_bill_no']==1){ echo "selected";}?>>Yes</option>
                      	<option value="0" <?php if($get_shipping_row['have_eoa_bill_no']==0){ echo "selected";}?>>No</option>
                      	
                      </select>
                       
 
                      </div>
                    </div>
                    <script>
                    	function change_have_eoa_bill_edit(v,id)
                    	{
                    		if(v==1)
                    		{
                    			$("#sec_eoa_edit"+id).show();
                    		}
                    		else
                    		{
                    			$("#sec_eoa_edit"+id).hide();
                    		}
                    	}
                    </script>
                    <div class="item form-group" <?php if($get_shipping_row['have_eoa_bill_no']==1){?>style="display:block"<?php }else{ ?>style="display:none"<?php }?> id="sec_eoa_edit<?= $get_shipping_row['shipping_details_id'];?>">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >EOA Bill No. :
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <input type="number" name="eoa_bill_no_edit[]"   value="<?= $get_shipping_row['eoa_bill_no'];?>" class="form-control col-md-7 col-xs-12" min="0">
                     
                      <input type="hidden" name="shipping_details_id[]"   value="<?= $get_shipping_row['shipping_details_id'];?>"> 
 
                      </div>
                    </div>
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Pay To : <span class="required">*</span>
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                      <!-- <input type="radio" name="pay_to_edit[]" value="1" <?php if($get_shipping_row['pay_to']==1){ echo "checked";}?>>Yes
                       <input type="radio" name="pay_to_edit[]" value="0" <?php if($get_shipping_row['pay_to']==0){ echo "checked";}?>>No
                      -->
                        <select name="pay_to_edit[]"   class="form-control col-md-7 col-xs-12" >
                      	<option value="1" <?php if($get_shipping_row['pay_to']==1){ echo "selected";}?>>Yes</option>
                      	<option value="0" <?php if($get_shipping_row['pay_to']==0){ echo "selected";}?>>No</option>
                      	
                      	</select>
 
                      </div>
                    </div>
                    <div class="ln_solid"></div> 
                    <?php
                    }
                    ?>
                    <div id="add_new_sec"></div>
                    <input type="hidden" id="ctr" value="0"/>
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
                      	<input type="hidden" name="register_id"   value="<?= $rc['register_id'];?>">
                      	<input type="hidden" name="shipping_id"   value="<?= $rc1['shipping_id'];?>">
                      	<input type="submit" name="edit_register" value="Update" onclick="return validate_form('edit_register_form');" class="btn btn-success btn-submit">
		<input type="button" name="add_event_cancel" value="Cancel" onclick="listregister();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>