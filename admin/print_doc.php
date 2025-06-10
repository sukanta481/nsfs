<?php
$sdid = $_REQUEST['shipping_details_id'];





$get_shipping_detail_sql="select * from   tbl_shipping_details where shipping_details_id='".$sdid."'";
$get_shipping_detail_rs=mysqli_query($conn, $get_shipping_detail_sql);
$get_shipping_detail_row=mysqli_fetch_array($get_shipping_detail_rs);

$get_register_sql="select * from    tbl_register where register_id='".$get_shipping_detail_row['register_id']."'";
$get_register_rs=mysqli_query($conn, $get_register_sql);
$get_register_row=mysqli_fetch_array($get_register_rs);


$get_shipping_sql="select * from    tbl_shipping where shipping_id='".$get_shipping_detail_row['shipping_id']."'";
$get_shipping_rs=mysqli_query($conn, $get_shipping_sql);
$get_shipping_row=mysqli_fetch_array($get_shipping_rs);
?>
<script>
	function print_invoice()
	{
	document.title = "";	
	window.print();	
	}
</script>
<div class="x_panel">
                <div class="x_title">
                  <h2>Print Invoice </h2>
                  
                  <div class="clearfix"></div>
                </div>
                <div class="x_content">

                  <div class="invoice_container">	
                  	<div class="invoice_containerTopprnt">
                  		<div class="prt_ico prnticon"><a href="#" onclick="print_invoice();"><img src="images/prnt.png" alt=""></a></div>
                  	<div class="scent_logo ">
                  		<img src="<?= SITE_URL; ?>assets/images/logo.png" alt="" height="100px";width="100px";>
                  		<!-- <img src="images/royal.png" alt="" style="float: right;"> -->
                  		
                  	</div>

<div class="two_part_sec_first">	
	<div class="left_section_inv">
		<?php

		$get_page_sql1="select * from  tbl_contact";
		$get_page_rs1=mysqli_query($conn,$get_page_sql1);
		$get_page_row1=mysqli_fetch_array($get_page_rs1);
		
		?>
		<h2>NORTH SUPER FAST SERVICE</h2>
		<div class="cst_address"><?php echo $get_page_row1['contact_address'];?></div>
		<div class="cst_address"> Mob : <?php echo $get_page_row1['contact_phone2'];?></div>
		<div class="cst_address"> Email : <?php echo $get_page_row1['contact_email2'];?></div>		
		
		
	</div>
	<div class="right_section_inv">
		<div class="cst1"><strong>DOC NO : </strong> <?php echo $get_shipping_detail_row['doc'];?></div>
		<div class="cst1"><strong>DATE  : </strong> <?php echo $get_shipping_row['shipping_date'];?></div>
		<div class="cst1"><strong>TRIP NO : </strong> <?php echo $get_shipping_row['trip_no'];?></div>
		
	</div>
</div>
</div>
<?php
if($get_register_row['rented_car']==1)
{
	
	$driver_name=$get_register_row['driver_name'];
	$driver_number=$get_register_row['driver_number'];
}
else
{

$get_car_sql="select * from  tbl_car where car_id='".$get_register_row['car_id']."'";
$get_car_rs=mysqli_query($conn,$get_car_sql);
$get_car_row=mysqli_fetch_array($get_car_rs);

$get_driver_sql="select * from   tbl_driver where driver_id='".$get_register_row['driver_id']."'";
$get_driver_rs=mysqli_query($conn,$get_driver_sql);
$get_driver_row=mysqli_fetch_array($get_driver_rs);

$get_helper_sql="select * from   tbl_helper where helper_id='".$get_register_row['helper_id']."'";
$get_helper_rs=mysqli_query($conn,$get_helper_sql);
$get_helper_row=mysqli_fetch_array($get_helper_rs);



	$driver_name=$get_driver_row['driver_name'];
	$driver_number=$get_driver_row['driver_number'];

}
?>
<div class="sales_recp_section">
	<h6>CAR NO : <?= $get_car_row['car_number'];?></h6>
	<div class="date_sec_inv">
		<table class="table border-none m-0">
			<tr>
				<td><strong>Driver Name :</strong></td>
				<td><?php echo $driver_name;?></td>
				<td><strong>Phone No :</strong></td>
				<td><?php echo $driver_number;?></td>
			</tr>
		</table>
	</div>
	<?php
	if(($get_helper_row['helper_name'] || $get_helper_row['helper_number']) && $get_register_row['rented_car']==0)
	{
	?>
	<div class="date_sec_inv">
		<table class="table border-none m-0">
			<tr>
				<td><strong>Helper Name :</strong></td>
				<td><?php echo $get_helper_row['helper_name'];?></td>
				<td><strong>Phone No :</strong></td>
				<td><?php echo $get_register_row['helper_number'];?></td>
			</tr>
		</table>
	</div>
	<?php
	}
	?>
	
	<div class="two_part_sec">
		<div class="left_section_inv">
			<h5>CONSIGNOR:</h5>
			<?php
          	$get_company_sql="select * from    tbl_company where company_id='".$get_shipping_detail_row['company_id']."'";
          	$get_company_rs=mysqli_query($conn,$get_company_sql);
			$get_company_row=mysqli_fetch_array($get_company_rs);
			?>
			
			<div class="cst1"><strong>Company Name: </strong> <?= $get_company_row['company_title'];?></div>
			<div class="cst1"><strong>Address : </strong> <?php echo $get_company_row['company_address'];?></div>
			<div class="cst1"><strong>Phone : </strong> <?php echo $get_company_row['company_phone'];?></div>
		
		</div>
		<div class="right_section_inv">
			<h5>CONSIGEE</h5>			
			
			<div class="cst1"><strong>Company Name : </strong> <?= $get_shipping_detail_row['client_name'];?></div>
			<div class="cst1"><strong>Address : </strong> <?= $get_shipping_detail_row['client_address'];?></div>
			<div class="cst1"><strong>Phone : </strong> <?= $get_shipping_detail_row['client_phone'];?></div>
		</div>
</div>
    
<div class="table_section_inv">
    <table style="border-collapse: collapse; width:100%; margin:0;" border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody class="tbody_sec" style="border:1px solid #000; width: 100%;">
	    	<tr class="slno_tr">
	    		<td class="slno_Row" style="width: 100%;">
	    			<table class="slno_Row_table" style="width: 100%;">
	    				<thead style="width: 100%;">
	    					<tr class="hd_sec hdblueHed">
	    						
	    						<td>DOC NO</td>
	    						<td>BOX (UNIT)</td>
	    						<td>EOA BILL (NO)	</td>
	    						<td>WEIGHT (KG)</td>
	    						<!-- <td>UNIT PRICE</td> -->
	    					</tr>
	    					
	    					<tr class="pro_row">
	    						
	    						<td><?= $get_shipping_detail_row['doc'];?></td>
	    						
	    						<td>
	    							<?= $get_shipping_detail_row['box'];?>
	    						</td>
	    						<?php
	    						if($get_shipping_detail_row['have_eoa_bill_no']==1)
								{
	    						?>
	    						<td>
	    							<?= $get_shipping_detail_row['eoa_bill_no'];?>
	    						</td>
	    						<?php
								}
								else
								{
								?>
	    						<td>
	    							N/A
	    						</td>
	    						<?php	
								}
	    						?>
	    						
	    						<td>
	    							
	    							<?= $get_shipping_detail_row['weight'];?>
	    						</td>
	    						
	    						<!-- <td>
	    							<?= $get_shipping_detail_row['unit_price'];?>
	    						</td> -->
	    					</tr>	    					    					
	    					
	    					
	    					
	    					
	    				</thead>
	    			</table>
	    		</td>
	    	</tr>
	    </tbody>
    </table>
 </div>   
 <div class="lower_text">If you have any questions concerning this invoice please call us on the number above</div>   

</div>     
  
</div>
</div>

                </div>
              </div>