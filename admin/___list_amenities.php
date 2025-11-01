 <script type="text/javascript">
	function delconfirmamenities(amenities_id){
		var ca = confirm("Are you sure to delete");
		if(ca==true){
		location.href='amenities.php?type=list_amenities&lp=ac&amenities_id='+amenities_id+'&actnamenities=dellamenities';
		//return true;
		}else{
			return false;
		}
	}
</script>
 <div class="x_panel">
	<div class="x_title">
	  <h2>List amenities </h2>
	 
	  <div class="clearfix"></div>
	</div>
	<div class="x_content">
	 
	  <table id="datatable-buttons" class="table table-striped table-bordered">
		<thead>
		  <tr>
			<th>Sl</th> 
			<th>amenities Image</th>
			<th>amenities Text</th>
			
			<th>Action</th>
		  </tr>
		</thead>
		<tbody>
			<?php
				 $sql = "select * from tbl_amenities order by amenities_id desc";
				$exe = mysqli_query($conn,$sql);
				$count = mysqli_num_rows($exe);
				$rowCount = 1;
			?>

			<?php while($result = mysqli_fetch_array($exe)){
			
			?>
			
			
		<tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
			
		
			<td><?php print $rowCount; ?> </td>
			<td> <img src="post_img/<?= $result['amenities_image'];?>" height="50" width="50" /></td>
			<td><?= $result['amenities_text'];?></td>
			
			<td>					
				<a class="btn btn-info btn-xs" href="amenities.php?type=edit_amenities&lp=ac&amenities_id=<?php print $result['amenities_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>
				<a class="btn btn-danger btn-xs" onclick="delconfirmamenities(<?php print $result['amenities_id']; ?>)">Delete</a>				
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

