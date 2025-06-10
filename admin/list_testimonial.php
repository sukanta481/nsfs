<script type="text/javascript">
	function delconfirmtestimonial(testimonial_id)
	{
		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		location.href='testimonial.php?type=list_testimonial&lp=ac&testimonial_id='+testimonial_id+'&actntestimonial=delltestimonial';
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
                  <h2>List Testimonial </h2>
                 
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                      	<th>Sl</th> 
                      	<!-- <th>Image</th> -->
                      	<th>Title</th>                   	
                       <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>	
                    	<?php
						$sql = "select * from tbl_testimonial order by testimonial_id desc";
						$exe = mysqli_query($conn,$sql);
						$count = mysqli_num_rows($exe);
						$rowCount = 1;
						?>

						<?php while($result = mysqli_fetch_array($exe)){?>
                   	 	<tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	<td><?php print $rowCount; ?> </td>
	                    	<!-- <td> <img src="post_img/<?php print $result['testimonial_image'];?>" height="50" width="50" /></td> -->
	                    	<td><?php echo $result['testimonial_name']; ?>  </td>
							<td>							
								<a class="btn btn-info btn-xs" href="testimonial.php?type=edit_testimonial&lp=ac&testimonial_id=<?php print $result['testimonial_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	
								<a class="btn btn-danger btn-xs" onclick="delconfirmtestimonial(<?php print $result['testimonial_id']; ?>)">Delete</a>	
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
           
      