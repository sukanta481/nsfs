<script type="text/javascript">
	function delconfirmregister(register_id)
	{
		var c = confirm("Are you sure to delete");
		if(c==true)
		{
		location.href='register.php?type=list_register&lp=ac&register_id='+register_id+'&actnregister=dellregister';
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
                  <h2>List Tip </h2>
                  <div>
                 	<form action="" method="post" id="forms" style="float: right;">
                 	    	From:<input type="date" name="fromdate" id="fromdate" value="<?php //echo date('Y-m-d');?>">
                 	To:<input type="date" name="todate" id="todate" value="<?php //echo date('Y-m-d');?>">
                 	<input type="hidden" name="action" value="search" />
                 <select id="type" name="type">
                     <option value="">Select Type</option>
                     <option value="doc">Doc Type</option>
                     <option value="box">Trip Type</option>
                 </select>
                 <select id="typedata" name="typedata">
                     <option value="">Select</option>
                     <!--<option value="1">Doc Type</option>
                     <option value="2">Trip Type</option>-->
                 </select>
                 <select id="status" name="status">
                     <option value="Processing">Processing</option>
                     <option value="Delivered">Delivered</option>
                     <option value="Delayed">Delayed</option>
                 </select>
                 
                 	<input type="submit" name="submit_filters" value="Search" class="btn btn-success btn-submit">
                 	<button id="resetBtn" class="btn btn-success">
            Reset Form
        </button>
                 	</form>
                 </div>
                 <div >
                 	<!--<form action="" method="post" style="float: right;">-->
                 
                 	<!--<input type="submit" name="submit_filter" value="Search" class="btn btn-success btn-submit">
                 	</form>-->
                 </div>
                  <div class="clearfix"></div>
                </div>
                <div id="posts">
                <div class="x_content">
                 
                  <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                      	<th>Sl</th>   
                      	                   	
                        <!-- <th>Image</th> -->
                         <th>Date</th> 
                        <th>Tip No.</th> 
                       <th>Action</th>
                      </tr>
                    </thead>


                    <tbody>
                        
                        
                    	
                    	<?php

//if(isset($_REQUEST['action']) && $_REQUEST['action']=="search")
//{
	 //$sql = "select * from  tbl_shipping where shipping_date between '".$_REQUEST['fromdate']."' and '".$_REQUEST['todate']."' order by shipping_id desc";
	// if($_REQUEST['type']!=''){
//	 $sql = "select * from  tbl_shipping join tbl_shipping_details on tbl_shipping.shipping_id =tbl_shipping_details.shipping_id where tbl_shipping.shipping_date between '".$_REQUEST['fromdate']."' and '".$_REQUEST['todate']."' and tbl_shipping_details.doc='".$_REQUEST['typedata']."' and status='".$_REQUEST['status']."' order by tbl_shipping.shipping_id desc";
    //}elseif($_REQUEST['type']!='doc'){
    //   $sql = "select * from  tbl_shipping join tbl_shipping_details on tbl_shipping.shipping_id =tbl_shipping_details.shipping_id where tbl_shipping.shipping_date between '".$_REQUEST['fromdate']."' and '".$_REQUEST['todate']."' and tbl_shipping_details.box='".$_REQUEST['typedata']."' and status='".$_REQUEST['status']."' order by tbl_shipping.shipping_id desc"; 
   // }

//elseif(isset($_REQUEST['typedata'])=="")
//{
	 //$sql = "select * from  tbl_shipping join tbl_shipping_details on tbl_shipping.shipping_id =tbl_shipping_details.shipping_id where tbl_shipping.shipping_date between '".$_REQUEST['fromdate']."' and '".$_REQUEST['todate']."'  and status='".$_REQUEST['status']."' order by tbl_shipping.shipping_id desc";
//}
//elseif(isset($_REQUEST['typedata'])=="" && $_REQUEST['type']=="" && $_REQUEST['fromdate']!='')
//{
	//$sql = "select * from  tbl_shipping where shipping_id in( select distinct shipping_id from tbl_shipping_details where status='Delivered') order by shipping_id desc";
	 //$sql = "select * from  tbl_shipping join tbl_shipping_details on tbl_shipping.shipping_id =tbl_shipping_details.shipping_id where tbl_shipping.shipping_date between '".$_REQUEST['fromdate']."' and '".$_REQUEST['todate']."'   order by tbl_shipping.shipping_id desc";
//}
//}
//else
//{
	 $sql = "select * from  tbl_shipping order by shipping_id desc";
//}
//echo $sql;
//exit;

$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);
$rowCount = 1;
?>

<?php while($result = mysqli_fetch_array($exe))
{

?>
                    	
                    	
                    <tr class="<?php print ($rowCount % 2) == 0 ? 'alt1' : 'alt2';?>">
                    	
                    
                    	<td><?php print $rowCount; ?>  </td>
                    	
                    	<!-- <td><img src="post_img/<?php echo $result['register_image'];?>" width="100" height="100"></td> -->
                    	<td><?php echo $result['shipping_date']; ?></td>
						<td><a href="javascript:void(0);" data-toggle="modal" data-target="#myModal<?= $rowCount;?>"><?php echo $result['trip_no']; ?></a>
						<div id="myModal<?= $rowCount;?>" class="modal fade tripnoModal" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      
      <div class="modal-body">
      	 <button type="button" class="close" data-dismiss="modal">&times;</button>
      	<h4 class="modal-title"><strong>Tip No:</strong> <span><?php echo $result['trip_no']; ?></span></h4>
      	<h6>All Doc No.</h6>
        <ul>
        	<?php
        	$get_shipping_details_sql = "select * from  tbl_shipping_details where shipping_id='".$result['shipping_id']."' order by client_id asc";
			$get_shipping_details_rs = mysqli_query($conn,$get_shipping_details_sql);
			while($get_shipping_details_row = mysqli_fetch_array($get_shipping_details_rs))
			{
        	?>
        	<li><i class="fa-solid fa-check"></i> <?= $get_shipping_details_row['doc'];?></li>
        	<?php
			}
        	?>
        </ul>
      </div>
      
    </div>

  </div>
</div>	
						</td>

<td>					
			
	 <a class="btn btn-info btn-xs" href="trip.php?type=list_trip_company&lp=ac&shipping_id=<?php print $result['shipping_id']; ?>&<?php echo session_name().'='.session_id();?>">List Doc No.</a>	
	  <a class="btn btn-success btn-xs" href="trip.php?type=print_trip&lp=cu&shipping_id=<?php print $result['shipping_id']; ?>" target="_blank">Print</a>

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
                <div id="post"></div>
              </div>
              
              <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script>
      $(function () {

        $('#forms').on('submit', function (e) {

          e.preventDefault();

          $.ajax({
            type: 'post',
            url: 'posts.php',
            data: $('#forms').serialize(),
            success: function (data) {
              //alert('form was submitted');
              $('#posts').hide();
              $('#post').html(data);
              
            }
          });

        });

      });
    </script>
    <script>  
 $(document).ready(function(){  
      $('#type').change(function(){  
           var brand_id = $(this).val();  
           $.ajax({  
                url:"load_data.php",  
                method:"POST",  
                data:{brand_id:brand_id},  
                success:function(data){  
                     $('#typedata').html(data);  
                     //$('#typedata').append('<option>'+data+'</option>');
                }  
           });  
      });  
 });  
 </script> 
 <script>
        $(document).ready(function () {
            $("#resetBtn").click(function (e) {
                //$("#d").trigger("reset"); 
                //$("#d").get(0).reset(); 
                e.preventDefault();
                $("#forms")[0].reset();
                location.reload();
            });
        }); 
    </script>
           
      