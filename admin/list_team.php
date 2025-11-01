<script type="text/javascript">
	function delconfirmteam(team_id)
	{
		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		location.href='team.php?type=list_team&lp=ac&team_id='+team_id+'&actnteam=dellteam';
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
                  <h2>List Team </h2>
                 
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                      	<th>Sl</th> 
                      	<!-- <th>Image</th> -->
                      	 <!-- <th>Type</th> -->
                      	 <th>Title</th>    
                      	 <th>Post</th>  
                      	 <!-- <th>Work Area</th>  -->            	
                       <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>	
                    	<?php
						$sql = "select * from tbl_team order by team_type,team_id asc";
						$exe = mysqli_query($conn,$sql);
						$count = mysqli_num_rows($exe);
						$rowCount = 1;
						?>

						<?php while($result = mysqli_fetch_array($exe)){?>
                   	 	<tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	<td><?php print $rowCount; ?> </td>
	                    	<!-- <td> <img src="post_img/<?php print $result['team_image'];?>" height="50" width="50" /></td> -->
	                    	<!-- <td><?php if( $result['team_type']==0){ echo "Our Team";}  ?></td> -->
	                    	<td><?php echo $result['team_title']; ?></td>
	                    	<td><?php echo $result['team_desg']; ?></td>
	                    	<!-- <td><?php echo $result['team_work_area']; ?></td> -->
							<td>							
								<a class="btn btn-info btn-xs" href="team.php?type=edit_team&lp=ac&team_id=<?php print $result['team_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	
								<a class="btn btn-danger btn-xs" onclick="delconfirmteam(<?php print $result['team_id']; ?>)">Delete</a>	
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
           
      