<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script type="text/javascript">
	function delconfirmorder(oid)
	{
		var o = confirm("Are you sure to delete");
		if(o==true)
		{
		location.href='order.php?type=list_order&lp=ac&order_usr_id=<?php echo $_REQUEST['order_usr_id'];?>&oid='+oid+'&actnorder=delorder';
		//return true;
		}
		else
		{
		return false;
		}
	}
	
</script>
       
              <div class="x_panel">
              	<div>
              		<?php
                 if($_REQUEST['order_usr_id']!='')
				{
				?>
				<input type="button" name="back_to_usr" value="Back" onclick="backtousr();" align="right">
				<?php	
				}		
                 ?>
              	</div>
                <div class="x_title">
                  <h2>List Booking </h2>
                 
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
       
                      <tr>
                      	<th>S.No</th>
                      		 <th>Booking ID.</th>    
                          	<th>Booking Date</th>            	
                           <th>Booking Amount</th>
                            
                           <th>Booking Status</th> 
                           
                            <th>User Name</th> 
                             
                         
                         <th>Action</th>
                      </tr>
                    </thead>


                    <tbody>
                    	
                 	
    <?php
	$i=1;


	$sql = "select distinct order_unique_id from tbl_order order by order_id desc";
	
	//echo $sql;
	$exe = mysqli_query($conn,$sql);
	
	while($result = mysqli_fetch_array($exe))
	{
		
		$sql1 = "select * from tbl_order where order_unique_id='".$result['order_unique_id']."'";
	$exe1 = mysqli_query($conn,$sql1);
	
	$result1 = mysqli_fetch_array($exe1);
		?>
		
		
		
		<tr>
			
			<td><?php echo $i; ?>  </td>
			<td><?php echo $result1['order_unique_id']; ?>  </td>
			<td><?php echo date('m/d/Y',strtotime($result1['order_date'])); ?>  </td>
			
				
			
				
				
				<td>Rs. <?php echo $result1['order_total_price']; ?>  </td>
				
				<td><?php echo $result1['payment_status']; ?>  </td>
				
			<td><?php echo $result1['order_user_name']; ?>  </td>
		
<td>					
			
	 <a class="btn btn-info btn-xs" href="order.php?type=edit_order&lp=ac&order_usr_id=<?php echo $_REQUEST['order_usr_id'];?>&order_id=<?php echo $result1['order_unique_id']; ?>&<?php echo session_name().'='.session_id();?>">View</a>	
	
	<a class="btn btn-danger btn-xs" onclick="delconfirmorder(<?php echo $result1['order_unique_id']; ?>)">Delete</a>	
	
	</td>

		
</tr> 
		
<?php 
	$i++;
	} 
?>
                      
                    </tbody>
                  </table>
                </div>
              </div> 