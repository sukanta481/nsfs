<script type="text/javascript">

	function delconfirmgallery(gallery_id)

	{

		var c = confirm("Are you sure to delete");

		if(c==true)

		{

		location.href='gallery.php?type=list_gallery&lp=ac&gallery_id='+gallery_id+'&actngallery=dellgallery';

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

                  <h2>List gallery </h2>

                 

                  <div class="clearfix"></div>

                </div>

                <div class="x_content">

                 

                  <table id="datatable-buttons" class="table table-striped table-bordered">

                    <thead>

                      <tr>

                      	<th>Sl</th> 

                      	 <th>Image</th>  

                      	 <th>Name</th>  
                      	 <th>gallery_category</th>                 	

                       	<th>Action</th>

                      </tr>

                    </thead>





                    <tbody>

                    	

                    	<?php





$sql = "select * from tbl_gallery";

$exe = mysqli_query($conn,$sql);

$count = mysqli_num_rows($exe);

$rowCount = 1;

?>



<?php while($result = mysqli_fetch_array($exe)){?>

                    	

                    	

                    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">

                    	

                    

                    	<td><?php print $rowCount; ?> </td>

                    	<td> <img src="post_img/<?php print $result['gallery_image'];?>" height="50" width="50" /></td>

                    	<td><?php echo $result['gallery_title']; ?>  </td>
                    	<?php
	                    	
								$get_gallery_category_sql="select * from tbl_gallery_category where gallery_category_id='".$result['gallery_category_id']."'";
								$get_gallery_category_rs=mysqli_query($conn,$get_gallery_category_sql);
								$get_gallery_category_row=mysqli_fetch_array($get_gallery_category_rs);
								$type= $get_gallery_category_row['gallery_category_title'];
							
	                    	?>
	                    	<td><?php echo $type;  ?></td>





<td>					

			

	 <a class="btn btn-info btn-xs" href="gallery.php?type=edit_gallery&lp=ac&gallery_id=<?php print $result['gallery_id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	

	<a class="btn btn-danger btn-xs" onclick="delconfirmgallery(<?php print $result['gallery_id']; ?>)">Delete</a>	

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

           

