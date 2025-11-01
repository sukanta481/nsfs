<script type="text/javascript">
	function delconfirmpackage_type(package_type_id)
	{
		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		location.href='package_type.php?type=list_package_type&lp=ac&package_type_id='+package_type_id+'&actnpackage_type=dellpackage_type';
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
                  <h2>List Tour Types </h2>
                 
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                      	<th>Sl</th> 
                      	 
                      	 <th>Name</th>                   	
                       	<th>Action</th>
                      </tr>
                    </thead>


                    <tbody>
                    	
                    	<?php


$sql = "select * from tbl_package_type";
$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);
$rowCount = 1;
?>

<?php while($result = mysqli_fetch_array($exe)){?>
                    	
                    	
                    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	
                    
                    	<td><?php print $rowCount; ?> </td>
                    	
                    	<td><?php echo $result['package_type_name']; ?>  </td>


<td>					
			
	 <a class="btn btn-info btn-xs" href="package_type.php?type=edit_package_type&lp=ac&package_type_id=<?php print $result['package_type_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	
	<a class="btn btn-danger btn-xs" onclick="delconfirmpackage_type(<?php print $result['package_type_id']; ?>)">Delete</a>	
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
           
      