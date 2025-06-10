<?php
$shipping_id=$_REQUEST['shipping_id'];
$sql2 = "select * from   tbl_shipping where shipping_id=".$shipping_id."";
$exe2 = mysqli_query($conn,$sql2);
$result2 = mysqli_fetch_array($exe2)
?>
<div class="x_panel">
                <div class="x_title">
                  <h2>List Doc No. : (Tip No.<?= $result2['trip_no'];?>)</h2>
                 <a href="trip.php?type=list_trip&lp=ac" class="btn btn-success btn-submit" style="float: right;">Back</a>
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                      	<th>Sl</th>   
                      	                   	
                        <th>Consignor Company Name</th> 
                         <th>Consignee Name</th> 
                        <th>Doc No.</th> 
                      <th>Status</th> 
                       <th>Action</th>
                      </tr>
                    </thead>


                    <tbody>
                    	
                    	<?php

$sql = "select * from  tbl_shipping_details where shipping_id='".$shipping_id."' order by client_id asc";
$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);
$rowCount = 1;
?>

<?php while($result = mysqli_fetch_array($exe))
{
	

$sql2 = "select * from   tbl_company where company_id='".$result['company_id']."'";
$exe2 = mysqli_query($conn,$sql2);
$result2 = mysqli_fetch_array($exe2);

?>
                    	
                    	
                    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	
                    
                    	<td><?php print $rowCount; ?>  </td>
                    	
                    	<td><?php echo $result2['company_title']; ?></td>
                    	<td><?php echo $result['client_name']; ?></td>
						<td><?php echo $result['doc']; ?></td>
						<td><?php echo $result['status']; ?></td>

<td>					
			
	 <a class="btn btn-info btn-xs" href="trip.php?type=edit_trip_company&lp=ac&shipping_details_id=<?php print $result['shipping_details_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit Doc Status</a>	
	 <a class="btn btn-success btn-xs" href="trip.php?type=print_doc&lp=cu&shipping_details_id=<?php print $result['shipping_details_id']; ?>" target="_blank">Print</a>

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
           
      