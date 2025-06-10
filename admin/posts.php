	<?php
	include('conn.php');

if(isset($_REQUEST['action']) && $_REQUEST['action']=="search")
{
    //if()
   // echo $_REQUEST['type'];
    if($_REQUEST['type']=='doc' && $_REQUEST['typedata']!="" && $_REQUEST['fromdate']!="" ){
	 $sql = "select * from  tbl_shipping join tbl_shipping_details on tbl_shipping.shipping_id =tbl_shipping_details.shipping_id where tbl_shipping.shipping_date between '".$_REQUEST['fromdate']."' and '".$_REQUEST['todate']."' and tbl_shipping_details.doc='".$_REQUEST['typedata']."' and status='".$_REQUEST['status']."' order by tbl_shipping.shipping_id desc";
    }elseif($_REQUEST['type']!='doc' && $_REQUEST['typedata']!='' && $_REQUEST['fromdate']!=''){
       $sql = "select * from  tbl_shipping join tbl_shipping_details on tbl_shipping.shipping_id =tbl_shipping_details.shipping_id where tbl_shipping.shipping_date between '".$_REQUEST['fromdate']."' and '".$_REQUEST['todate']."' and tbl_shipping.trip_no='".$_REQUEST['typedata']."' and status='".$_REQUEST['status']."' order by tbl_shipping.shipping_id desc"; 
    }elseif($_REQUEST['type']=="" && $_REQUEST['typedata']==""){
      $sql= "select * from  tbl_shipping join tbl_shipping_details on tbl_shipping.shipping_id =tbl_shipping_details.shipping_id where tbl_shipping.shipping_date between '".$_REQUEST['fromdate']."' and '".$_REQUEST['todate']."' and status='".$_REQUEST['status']."' order by tbl_shipping.shipping_id desc";
    }elseif($_REQUEST['fromdate']=='' && $_REQUEST['type']!="box"){
        $sql = "select * from  tbl_shipping join tbl_shipping_details on tbl_shipping.shipping_id =tbl_shipping_details.shipping_id where  tbl_shipping_details.doc='".$_REQUEST['typedata']."' and status='".$_REQUEST['status']."' order by tbl_shipping.shipping_id desc";
    }elseif($_REQUEST['fromdate']=='' && $_REQUEST['type']!="doc"){
        $sql = "select * from  tbl_shipping join tbl_shipping_details on tbl_shipping.shipping_id =tbl_shipping_details.shipping_id where  tbl_shipping.trip_no='".$_REQUEST['typedata']."' and status='".$_REQUEST['status']."' order by tbl_shipping.shipping_id desc";
    }else{
       $sql = "select * from  tbl_shipping order by shipping_id desc"; 
    }
}
//echo $sql;
//elseif(isset($_REQUEST['status']) && $_REQUEST['status']=="underprocessing")
//{
//	 $sql = "select * from  tbl_shipping where shipping_id in( select distinct shipping_id from tbl_shipping_details where status='Processing') order by shipping_id desc";
//}
//elseif(isset($_REQUEST['status']) && $_REQUEST['status']=="delivered")
//{
//	$sql = "select * from  tbl_shipping where shipping_id in( select distinct shipping_id from tbl_shipping_details where status='Delivered') order by shipping_id desc";
//}
//else
//{
//	 $sql = "select * from  tbl_shipping order by shipping_id desc";
//}

$exe = mysqli_query($conn,$sql);
$count = mysqli_num_rows($exe);
$rowCount = 1;
?>
 <div class="x_content">
<table id="user" class="table table-striped table-bordered">
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
                  
                  
                  <script src="https://cdn.datatables.net/1.10.19/js/dataTables.uikit.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#user').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                  extend: "copy",
                  className: "btn-sm"
                }, {
                  extend: "csv",
                  className: "btn-sm"
                }, {
                  extend: "excel",
                  className: "btn-sm"
                }, {
                  extend: "pdf",
                  className: "btn-sm"
                }, {
                  extend: "print",
                  className: "btn-sm"
                }],
                responsive: true
             
        
            });
        });
    </script>
         