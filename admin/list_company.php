<script type="text/javascript">
	function delconfirmcompany(company_id)
	{
		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		location.href='company.php?type=list_company&lp=ac&company_id='+company_id+'&actncompany=dellcompany';
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
                  <h2>List Consignor Company </h2>
                 
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 <div style="overflow:auto">
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


$sql = "select * from tbl_company";
$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);
$rowCount = 1;
?>

<?php while($result = mysqli_fetch_array($exe)){?>
                    	
                    	
                    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	
                    
                    	<td><?php print $rowCount; ?>  </td>
                    	
                    	<!-- <td><img src="post_img/<?php echo $result['company_image'];?>" width="100" height="100"></td> -->
						<td><?php echo $result['company_title']; ?>  </td>

<td>					
			
	 <a class="btn btn-info btn-xs" href="company.php?type=edit_company&lp=ac&company_id=<?php print $result['company_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	
	<a class="btn btn-danger btn-xs" onclick="delconfirmcompany(<?php print $result['company_id']; ?>)">Delete</a>	
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
              </div>
           
      