<script type="text/javascript">
	function delconfirmhelper(helper_id)
	{
		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		location.href='helper.php?type=list_helper&lp=ac&helper_id='+helper_id+'&actnhelper=dellhelper';
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
                  <h2>List Helper </h2>
                 
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


$sql = "select * from tbl_helper order by helper_name";
$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);
$rowCount = 1;
?>

<?php while($result = mysqli_fetch_array($exe)){?>
                    	
                    	
                    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	
                    
                    	<td><?php print $rowCount; ?>  </td>
                    	
                    	<!-- <td><img src="post_img/<?php echo $result['helper_image'];?>" width="100" height="100"></td> -->
						<td><?php echo $result['helper_name']; ?>  </td>

<td>					
			
	 <a class="btn btn-info btn-xs" href="helper.php?type=edit_helper&lp=ac&helper_id=<?php print $result['helper_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	
	<a class="btn btn-danger btn-xs" onclick="delconfirmhelper(<?php print $result['helper_id']; ?>)">Delete</a>	
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
           
      