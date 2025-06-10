<script type="text/javascript">
	function delconfirmregister(register_id)
	{
		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		location.href='register.php?type=list_register&lp=ac&register_id='+register_id+'&actnregister=dellregister';
		//return true;
		}
		else
		{
		return false;
		}
	}
</script>
       
              <div class="x_panel">
                <div class="x_title">
                  <h2>List Register </h2>
                 
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                      	<th>Sl</th>   
                      	                   	
                        <!-- <th>Image</th> -->
                         <th>Date</th> 
                        <th>Tip No.</th> 
                        <th>Car No.</th> 
                        <th>Driver Name</th> 
                       <th>Action</th>
                      </tr>
                    </thead>


                    <tbody>
                    	
                    	<?php


$sql = "select * from tbl_register order by register_id desc";
$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);
$rowCount = 1;
?>

<?php while($result = mysqli_fetch_array($exe))
{
$sql1 = "select * from  tbl_shipping where register_id='".$result['register_id']."' order by shipping_id desc";
$exe1 = mysqli_query($conn,$sql1);
$result1 = mysqli_fetch_array($exe1);


if($result['rented_car']==1)
{
	$car_number=$result['car_number'];
	$driver_name=$result['driver_name'];
}
else
{
	$get_car_sql="select * from  tbl_car where car_id='".$result['car_id']."'";
$get_car_rs=mysqli_query($conn,$get_car_sql);
$get_car_row=mysqli_fetch_array($get_car_rs);
$car_number=$get_car_row['car_number'];


$get_driver_sql="select * from   tbl_driver where driver_id='".$result['driver_id']."'";
$get_driver_rs=mysqli_query($conn,$get_driver_sql);
$get_driver_row=mysqli_fetch_array($get_driver_rs);
$driver_name=$get_driver_row['driver_name'];
}

?>
                    	
                    	
                    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	
                    
                    	<td><?php print $rowCount; ?>  </td>
                    	
                    	<!-- <td><img src="post_img/<?php echo $result['register_image'];?>" width="100" height="100"></td> -->
                    	<td><?php echo $result1['shipping_date']; ?></td>
						<td><?php echo $result1['trip_no']; ?></td>
						<td><?php echo $car_number; ?></td>
						<td><?php echo $driver_name; ?></td>

<td>					
			
	 <a class="btn btn-info btn-xs" href="register.php?type=edit_register&lp=ac&register_id=<?php print $result['register_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	
	<a class="btn btn-danger btn-xs" onclick="delconfirmregister(<?php print $result['register_id']; ?>)">Delete</a>	
	</td>

</tr>
<?php $rowCount ++ ; ?> 
<?php
}
?>
                      
                    </tbody>
                  </table>
                </div>
              </div>
           
      