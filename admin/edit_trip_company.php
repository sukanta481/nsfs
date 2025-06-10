<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$shipping_details_id = $_REQUEST['shipping_details_id'];
$get_shipping_sql="select * from   tbl_shipping_details where shipping_details_id='".$shipping_details_id."'";
$get_shipping_rs=mysqli_query($conn, $get_shipping_sql);
$get_shipping_row=mysqli_fetch_array($get_shipping_rs);


?>
<script type="text/javascript">
	function listtrip()
	{
		location.href = "trip.php?type=list_trip_company&lp=ac&shipping_id=<?= $get_shipping_row['shipping_id'];?>";
	}
</script>

<div class="x_panel">
                <div class="x_title">
                  <h2>Edit Doc Status</h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_trip_form" action="" method="post" name="edit_trip_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                  	
                  	   
                 
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignor Company Name: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                    
                      	<?php
                      	$get_company_sql="select * from    tbl_company where company_id='".$get_shipping_row['company_id']."'";
                      	$get_company_rs=mysqli_query($conn,$get_company_sql);
						$get_company_row=mysqli_fetch_array($get_company_rs);
						?>
						<?= $get_company_row['company_title'];?>
                     
                     
                       
 
                      </div>
                    </div>
                        
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Consignee Name: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                    
                      	
						<?= $get_shipping_row['client_name'];?>
                     
                     
                       
 
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Doc: 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <?= $get_shipping_row['doc'];?>
                     
                       
 
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Box (unit): 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                    <?= $get_shipping_row['box'];?>
                     
                       
 
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Whight (kg): 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                    <?= $get_shipping_row['weight'];?>
                       
 
                      </div>
                    </div>
                    <?php
                    if($get_shipping_row['have_eoa_bill_no']==1)
					{
                    ?>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >EOA Bill No. : 
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                      
                     <?= $get_shipping_row['eoa_bill_no'];?>
                     
                      
 
                      </div>
                    </div>
                    <?php
					}
                    ?>
                   
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Status:
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                     
                 <select class="form-control col-md-7 col-xs-12" name="status" onchange="change_status(this.value);">
                 	<option value="" >Select Status</option>
                 	<option value="Processing" <?php if($get_shipping_row['status']=='Processing'){ echo "selected";}?>>Processing</option>
                 	<option value="Delay" <?php if($get_shipping_row['status']=='Delay'){ echo "selected";}?>>Delay</option>
                 	<option value="Delivered" <?php if($get_shipping_row['status']=='Delivered'){ echo "selected";}?>>Delivered</option>
                 </select>
 
                      </div>
                    </div>
                    
                    <div class="item form-group" id="rod_sec" <?php if($get_shipping_row['status']=='Delay'){?>style="display:block;"<?php }else{?>style="display:none;"<?php }?>>
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Reason Of Delay:
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12" >
                     
                 <select class="form-control col-md-7 col-xs-12" name="reason_of_delay" >
                 	<option value="">Select Reason Of Delay</option>
                 	<?php


$delay_sql = "select * from tbl_delay_reason order by delay_reason_name asc";
$delay_rs = mysqli_query($conn,$delay_sql);
while($delay_row = mysqli_fetch_array($delay_rs))
{
?>
                 	<option value="<?= $delay_row['delay_reason_name'];?>" <?php if($get_shipping_row['reason_of_delay']==$delay_row['delay_reason_name']){ echo "selected";}?>><?= $delay_row['delay_reason_name'];?></option>

<?php
}
?>
                 	
                 </select> 
                      </div>
                    </div>
                    
                    <div class="item form-group" id="pod_sec" <?php if($get_shipping_row['status']=='Delivered'){?>style="display:block;"<?php }else{?>style="display:none;"<?php }?>>
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Proof of Delivery:
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <?php
                      if($get_shipping_row['proof_of_delivery']!='')
                      {
                      ?>	
                      <img src="post_img/<?= $get_shipping_row['proof_of_delivery'];?>"	width="200" height="200">
                      <?php
                      }
                      ?>
                     	<input type="file" name="proof_of_delivery" >
                 
                      </div>
                    </div>
                    
                 <script>
                 	function change_status(v)
                 	{
                 		if(v=='Delay')
                 		{
                 			$("#rod_sec").show();
                 			$("#pod_sec").hide();
                 		}
                 		else if(v=='Delivered')
                 		{
                 			$("#rod_sec").hide();
                 			$("#pod_sec").show();
                 		}
                 		else
                 		{
                 			$("#rod_sec").hide();
                 			$("#pod_sec").hide();
                 		}
                 	}
                 </script>    	
                     
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">
                      	<input type="hidden" name="shipping_details_id"   value="<?= $get_shipping_row['shipping_details_id'];?>"> 
                      	<input type="submit" name="edit_trip" value="Update" onclick="return validate_form('edit_trip_form');" class="btn btn-success btn-submit">
		<input type="button" name="add_event_cancel" value="Cancel" onclick="listtrip();" class="btn btn-success btn-submit">
                 
                        
                      </div>
                    </div>
                  </form>

                </div>
             </div>