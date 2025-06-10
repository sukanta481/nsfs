<?php
$order_id = $_REQUEST['order_id'];
$sl = 'SELECT * FROM   tbl_order WHERE order_unique_id = "'.$order_id.'"';
$q  = mysqli_query($conn,$sl);
$rc = mysqli_fetch_array($q);
?>
<script type="text/javascript">
	function listbooking()
	{
		location.href = "order.php?type=list_order&lp=cu";
	}
</script>

<div class="x_panel">
                <div class="x_title">
                  <h2>View Booking </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <form id="edit_booking_form" action="" method="post" name="edit_booking_form" class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
             		<div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Booking ID.  
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo $rc['order_unique_id'];
					?>
                      </div>
                    </div>  
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Booking Date  
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo $rc['order_date'];
					?>
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Booking Amount  
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo "Rs.".$rc['order_total_price'];
					?>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Package Name  
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo $rc['order_pkg_name'];
					?>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Payment Mode  
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo $rc['payment_mode'];
					?>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Payment Status  
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo $rc['payment_status'];
					?>
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >User Name  
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo $rc['order_user_name'];
					?>
                      </div>
                    </div>
                    
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >User Email  
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo $rc['order_user_email'];
					?>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >User Phone  
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo $rc['order_user_phone'];
					?>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >User Address  
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo $rc['order_user_address']."<br>".$rc['order_user_city']."<br>".$rc['order_user_zip']."<br>".$rc['order_user_state']."<br>".$rc['order_user_country'];
					?>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Booking Location
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo $rc['booking_location'];
					?>
                      </div>
                    </div>
                    <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Booking Slot.
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo "(".$rc['booking_start_date']." ".$rc['booking_start_time'].") - (".$rc['booking_end_date']." ".$rc['booking_end_time'].")";
					?>
                      </div>
                    </div>
                     <div class="item form-group">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12" >Booking Vehicle Reg No.
                      </label>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                   <?php 
					echo $rc['booking_vehicle_reg_no'];
					?>
                      </div>
                    </div>
                   
                    <div class="ln_solid"></div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-3">	
                  			<input type="hidden" name="booking_id" value="<?php print $rc['order_id']; ?>" />
                      		<!-- <input type="submit" name="edit_booking" value="Update" onclick="return validate_form('edit_booking_form');" class="btn btn-success btn-submit"> -->
							<input type="button" name="add_booking_cancel" value="Cancel" onclick="listbooking();" class="btn btn-success btn-submit">
                      </div>
                    </div>
                  </form>

                </div>
             </div>