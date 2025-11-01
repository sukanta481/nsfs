<script type="text/javascript">
	function delconfirmpackage(package_id)
	{
		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		location.href='package.php?type=list_package&lp=ac&package_id='+package_id+'&actnpackage=dellpackage';
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
                  <h2>List Packages </h2>
                 
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                      	<th>Sl</th> 
                      	 <!-- <th>Image</th>  --> 
                      	 <th>Name</th>                   	
                       	<th>Action</th>
                      </tr>
                    </thead>


                    <tbody>
                    	
                    	<?php


$sql = "select * from tbl_package order by package_id desc";
$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);
$rowCount = 1;
?>

<?php while($result = mysqli_fetch_array($exe)){?>
                    	
                    	
                    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	
                    
                    	<td><?php print $rowCount; ?> </td>
                    	<!-- <td> <img src="images/<?php print $result['package_image'];?>" height="50" width="50" /></td> -->
                    	<td><?php echo $result['package_name']; ?></td>


<td>					
			
	 <a class="btn btn-info btn-xs" href="package.php?type=edit_package&lp=ac&package_id=<?php print $result['package_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	
	<a class="btn btn-danger btn-xs" onclick="delconfirmpackage(<?php print $result['package_id']; ?>)">Delete</a>	
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
           
      