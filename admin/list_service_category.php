<script type="text/javascript">
	function delconfirmservice_category(service_category_id)
	{
		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		location.href='service_category.php?type=list_service_category&lp=ac&service_category_id='+service_category_id+'&actnservice_category=dellservice_category';
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
                  <h2>List service category </h2>
                 
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                      	<th>Sl</th> 
                      	
                      	 <th>Title</th>                   	
                        
                       <th>Action</th>
                      </tr>
                    </thead>


                    <tbody>
                    	
                    	<?php


$sql = "select * from tbl_service_category order by service_category_id asc";
$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);
$rowCount = 1;
?>

<?php while($result = mysqli_fetch_array($exe)){?>
                    	
                    	
                    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	
                    
                    	<td><?php print $rowCount; ?> </td>
                   
                    	<td><?php echo $result['service_category_title']; ?>  </td>


<td>					
			
	 <a class="btn btn-info btn-xs" href="service_category.php?type=edit_service_category&lp=ac&service_category_id=<?php print $result['service_category_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	
	<a class="btn btn-danger btn-xs" onclick="delconfirmservice_category(<?php print $result['service_category_id']; ?>)">Delete</a>	
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
           
      