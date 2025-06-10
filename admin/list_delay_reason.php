<script type="text/javascript">
	function delconfirmdelay_reason(delay_reason_id)
	{
		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		location.href='delay_reason.php?type=list_delay_reason&lp=ac&delay_reason_id='+delay_reason_id+'&actndelay_reason=delldelay_reason';
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
                  <h2>List Delay Reason </h2>
                 
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                      	<th>Sl</th>   
                      	                   	
                        <!-- <th>Image</th> -->
                        <th>Name</th> 
                       <th>Action</th>
                      </tr>
                    </thead>


                    <tbody>
                    	
                    	<?php


$sql = "select * from tbl_delay_reason order by delay_reason_name asc";
$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);
$rowCount = 1;
?>

<?php while($result = mysqli_fetch_array($exe)){?>
                    	
                    	
                    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	
                    
                    	<td><?php print $rowCount; ?>  </td>
                    	
                    	<!-- <td><img src="post_img/<?php echo $result['delay_reason_image'];?>" width="100" height="100"></td> -->
						<td><?php echo $result['delay_reason_name']; ?>  </td>

<td>					
			
	 <a class="btn btn-info btn-xs" href="delay_reason.php?type=edit_delay_reason&lp=ac&delay_reason_id=<?php print $result['delay_reason_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	
	<a class="btn btn-danger btn-xs" onclick="delconfirmdelay_reason(<?php print $result['delay_reason_id']; ?>)">Delete</a>	
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
           
      