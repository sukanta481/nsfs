<script type="text/javascript">
function delconfirm5(coupon_id){
	var cb = confirm('Are you sure to delete this item');
	if(cb==true){
	location.href='coupon.php?type=list_coupon&lp=ac&coupon_id='+coupon_id+'&actncoupon=delcoupon';
	}else{
	return false;
	}
	
}
</script>
       
              <div class="x_panel">
                <div class="x_title">
                  <h2>List Discount </h2>
                 
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                      	<th>Id</th>   
                      	<th>Discount Code</th>
                       <th>Action</th>
                      </tr>
                    </thead>


                    <tbody>
                    	
                    	<?php


$sql = "select * from tbl_coupon";
$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);?>

<?php 
$i=1;
while($result = mysqli_fetch_array($exe)){	?>
                    	
                    	
                    <tr>
                    	
                    
                    	<td><?php echo $result['id']; ?></td>
                    	<td><?php echo $result['coupon_code']; ?>  </td>
                    	

<td>					
			
	 <a class="btn btn-info btn-xs" href="coupon.php?type=edit_coupon&lp=ac&id=<?php print $result['id']; ?>&<?php echo session_name().'='.session_id();?>">Edit</a>	
	<a class="btn btn-danger btn-xs" onclick="delconfirm5(<?php print $result['id']; ?>)">Delete</a>
	<!-- <a class="btn btn-info btn-xs" onclick="document.getElementById('light<?= $i;?>').style.display='block';document.getElementById('fade').style.display='block'">Send Discount</a>	 -->
	</td>

</tr>
<!-- <div id="light<?= $i;?>" class="white_content" style="display: none;">

<form method="post" action="">	

<p>

	

	<textarea row="50" col="50" name="email_addr" placeholder="Email Addresses (separate by comma)"></textarea>

</p>

<p>

	<input type="hidden" name="coupon_code" value="<?php echo $result['coupon_code']; ?>">

	<input type="hidden" name="cupon_end_dt" value="<?php echo $result['cupon_end_dt']; ?>">

	<input type="submit" name="send_coupon" value="Send">

</p>

<a href = "javascript:void(0)" onclick = "document.getElementById('light<?= $i;?>').style.display='none';document.getElementById('fade').style.display='none'" class="close"><img src="images/cross.png" alt="" /></a>

</form>

</div>  -->
<?php
$i++;
}
?>
                      
                    </tbody>
                  </table>
                </div>
              </div>

	              