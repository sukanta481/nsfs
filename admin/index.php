<?php require 'top_header.php'; ?>
<body class="nav-md">

  <div class="container body">

    <div class="main_container">
<?php require 'left_panel.php';?>
 <?php require 'header_banner.php';?>      
      <!-- page content -->
      <div class="right_col" role="main">
      	
 
      	
      	
		<div class="row tile_count">
        <h3>Admin Panel</h3>
        </div>
 			<div class="row">            
 				<div class="col-md-12">              
 					<div class="x_panel">                
 						<!-- <div class="x_title">                  
 							<h2>Admin Panel</h2>                  
 							<div class="clearfix"></div>                
 							</div> -->                
 							<div class="x_content">    
 								
 								
 								<table width="100%" border="0" cellspacing="0" cellpadding="0">					 
 													  
 									<tr>						
 										<td style="text-align:center; font-size:16px; font-family:Georgia, 'Times New Roman', Times, serif; color:#FF6600">						<strong>Welcome to Administrator Area</strong></td>					  
 										</tr>					 
 										 		
 								</table>  
 								
 								
 								     	<div class="row tile_count">
      	<?php
      //	echo $conn;
      	$get_total_trip_sql="select * from  tbl_shipping order by shipping_id desc";
		$get_total_trip_rs=mysqli_query($conn,$get_total_trip_sql);
		$get_total_trip_num=mysqli_num_rows($get_total_trip_rs);
		$total_trip_num=$get_total_trip_num;
      	?>
          <div class="animated flipInY col-md-4 col-sm-4 col-xs-4 tile_stats_count">
            <div class="left"></div>
            <div class="right">
              <a href="trip.php?type=list_trip" target="_blank"><span class="count_top"><i class="fa fa-user"></i> Total Tip</span></a>
              <div class="count"><?= $total_trip_num;?></div>
             
            </div>
          </div>
         <?php
      	$get_total_underprocessing_trip_sql="select * from  tbl_shipping where shipping_id in( select distinct shipping_id from tbl_shipping_details where status='Processing') order by shipping_id desc";
		$get_total_underprocessing_trip_rs=mysqli_query($conn,$get_total_underprocessing_trip_sql);
		$get_total_underprocessing_trip_num=mysqli_num_rows($get_total_underprocessing_trip_rs);
		$total_underprocessing_trip_num=$get_total_underprocessing_trip_num;
      	?>
          <div class="animated flipInY col-md-4 col-sm-4 col-xs-4 tile_stats_count">
            <div class="left"></div>
            <div class="right">
              <a href="trip.php?type=list_trip&status=underprocessing" target="_blank"><span class="count_top"><i class="fa fa-user"></i> Underprocessing</span></a>
              <div class="count green"><?= $total_underprocessing_trip_num;?></div>
              
            </div>
          </div>
          <?php
      	$get_total_delivered_trip_sql="select * from  tbl_shipping where shipping_id in( select distinct shipping_id from tbl_shipping_details where status='Delivered') order by shipping_id desc";
		$get_total_delivered_trip_rs=mysqli_query($conn,$get_total_delivered_trip_sql);
		$get_total_delivered_trip_num=mysqli_num_rows($get_total_delivered_trip_rs);
		$total_delivered_trip_num=$get_total_delivered_trip_num;
      	?>
          <div class="animated flipInY col-md-4 col-sm-4 col-xs-4 tile_stats_count">
            <div class="left"></div>
            <div class="right">
              <a href="trip.php?type=list_trip&status=delivered" target="_blank"><span class="count_top"><i class="fa fa-user"></i> Delivered</span></a>
              <div class="count "><?= $total_delivered_trip_num;?></div>
              
            </div>
          </div>
          
 		
 		
 		
 		
 		
 		
 		
 		
 		
 		            
 										 	</div>            
 										 	</div>		
 										 	</div>	 
 										 	</div>      
 										 	</div>	
 										 	</div>
<?php require 'footer.php';?>      