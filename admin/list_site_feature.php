<script type="text/javascript">
	function delconfirmsitefeature(feature_id)
	{
		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		location.href='site_feature.php?type=list_site_feature&lp=ac&client_id='+feature_id+'&actnfeature=dellfeature';
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
                  <h2>List  Site Feature </h2>
                 
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                      	<th>Sl</th>                  	
                        <th>Image</th>
                        <th>Title</th> 
                       <th>Action</th>
                      </tr>
                    </thead>


                    <tbody>
                    	
                    	<?php


$sql = "select * from tbl_site_feature order by feature_id asc";
$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);
$rowCount = 1;
?>

<?php while($result = mysqli_fetch_array($exe)){?>
                    	
                    	
                    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	
                    
                    	<td><?php print $rowCount; ?>  </td>
                    	
                    	<td><img src="post_img/<?php echo $result['feature_image'];?>" width="100" height="100"></td>
						<td><?php echo $result['feature_title']; ?>  </td>

<td>					
			
	 <a class="btn btn-info btn-xs" href="site_feature.php?type=edit_site_feature&lp=ac&feature_id=<?php print $result['feature_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	
	<!-- <a class="btn btn-danger btn-xs" onclick="delconfirmsitefeature(<?php print $result['feature_id']; ?>)">Delete</a>	 -->
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
           
      