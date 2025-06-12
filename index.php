<?php include("include/header.php"); ?>
<?php
$get_page_sql="select * from  tbl_pagecontent where id='1'";
$get_page_rs=mysqli_query($conn,$get_page_sql);
$get_page_row=mysqli_fetch_array($get_page_rs);

?>
<div class="bannersec">
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				<div class="banLeft">
<?= $get_page_row['banner_heading'];?>
<a href="<?= $get_page_row['banner_link'];?>" class="cnctBtn"><i><img src="<?= SITE_URL;?>assets/images/vwSer.svg" alt="vwSer" /></i>View Our Services</a>
					<div class="trkBx">
						<h6>Track Your Shipment Here</h6>
						<form id="trakform" action="deliveryHistory.php" method="get">
							<input type="text" name="doc_no" placeholder="Enter your Tracking ID(Doc No.)" required="required"/>
							<input type="submit" value="Track" />
						</form>


						

					</div>
				</div>
			</div>
			<div class="col-md-6 banRight">
				<img src="<?= SITE_URL;?>admin/post_img/<?= $get_page_row['page_image'];?>" alt="hmBn" />
			</div> 
		</div>
	</div>	
	<div class="banSosCon">
		<div class="cntner">
			<div class="banSosBx">
				<ul class="banSos">
					<li><a href="<?= $social_res['social_linkedin'];?>" target="_blank"><i class="fa-brands fa-linkedin-in"></i></a></li>
					<li><a href="<?= $social_res['social_twitter'];?>" target="_blank"><i class="fa-brands fa-x-twitter"></i></a></li>
					<li><a href="<?= $social_res['social_instagram'];?>" target="_blank"><i class="fa-brands fa-instagram"></i></a></li>
				</ul>
			</div>
		</div>
	</div>
</div>










<?php include("sec_site_feature.php"); ?>




<section class="abtSec tophead">
	<div class="container">
		<div class="row">
			<div class="col-md-5">
				<div class="abtImg">
					<img src="<?= SITE_URL;?>admin/post_img/<?= $get_page_row['page_details_image'];?>" alt="abtimg" />
				</div>
			</div>
			<div class="col-md-7">
				<div class="abtTxt">
<?= $get_page_row['content'];?>
					<a href="<?= $get_page_row['page_link'];?>" class="cnctBtn"><i><img src="<?= SITE_URL;?>assets/images/rd.svg" alt="vwSer"></i>Read more</a>
				</div>
			</div>
		</div>
	</div>
</section>





<section class="whtSec tophead">
	<div class="container">
		<div class="whtTp">
<?= $get_page_row['add_cont1'];?>
			<a href="<?= $get_page_row['banner_link'];?>" class="cnctBtn"><i><img src="<?= SITE_URL;?>assets/images/vwSer.svg" alt="vwSer"></i>View Our Services</a>
		</div>
		<div class="owl-carousel">
			<?php
        	$get_service_sql="select * from tbl_service order by service_id asc";
			$get_service_rs=mysqli_query($conn,$get_service_sql);
			$i=1;
			while($get_service_row=mysqli_fetch_array($get_service_rs))
			{
        	?>
			<div class="item">
				<div class="whtBx">
					<img src="<?= SITE_URL;?>admin/post_img/<?= $get_service_row['service_small_image'];?>" alt="wt1" />
					<h4><?= $get_service_row['service_title'];?></h4>
					<a class="whtHov" href="<?= SITE_URL; ?>service.php#service<?= $i;?>"></a>
				</div>
			</div>
			<?php
			$i++;
			}
            ?>
			
		</div>
	</div>
</section>





<section class="tmSec tophead">
	<div class="container">
		<div class="row">
			<div class="col-md-7">
				<div class="tmRgt">
					<div class="owl-carousel">
						<?php
						$team_sql = "select * from tbl_team order by team_id asc";
						$team_exe = mysqli_query($conn,$team_sql);
						$rowCount = 1;
						?>

						<?php while($team_result = mysqli_fetch_array($team_exe))
						{
						?>
						<div class="item">
							<div class="tmBx">
								<i><img src="<?= SITE_URL;?>admin/post_img/<?= $team_result['team_image']; ?>" alt="tm1" /></i>
								<strong><?= $team_result['team_title']; ?></strong>
								<span><?= $team_result['team_desg']; ?></span>
								<p><?= $team_result['team_srt_desc']; ?></p>
							</div>
						</div>
						<?php $rowCount ++ ; ?>
						<?php
						}
						?>
					
						
					</div>
				</div>
			</div>
			<div class="col-md-5">
				<div class="tmLft">
<?= $get_page_row['add_cont2'];?>
				</div>
			</div>
		</div>
	</div>
</section>





<section class="galSec tophead">
	<div class="container">
		<div class="row">
			<div class="col-md-5">
				<div class="galLft">
<?= $get_page_row['feature_text'];?>
				</div>
			</div>
			<div class="col-md-7">
				<img class="w-100" src="<?= SITE_URL;?>admin/post_img/<?= $get_page_row['feature_image'];?>" alt="gallery" />
			</div>
		</div>
	</div>
</section>

<div id="myModal" class="modal fade traketable" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-centered">

    <!-- Modal content-->
    <div class="modal-content">
      
      <div class="modal-body">
      	 <h4 class="modal-title">Track Your Shipment</h4>
      	 <button type="button" class="close" data-dismiss="modal">&times;</button>
       		<div class="table-responsive">
       			<div id="showres"></div>
       		</div>
      </div>
     
    </div>

  </div>
</div>


<?php include("sec_testimonial.php"); ?>
<?php include("include/footer.php"); ?>
