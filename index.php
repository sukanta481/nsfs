<?php
date_default_timezone_set('Asia/Kolkata');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("include/header.php"); ?>
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
					<?php if (!empty($social_res['social_facebook'])): ?>
					<li><a href="<?= $social_res['social_facebook'];?>" target="_blank"><i class="fa-brands fa-facebook-f"></i></a></li>
					<?php endif; ?>

					<?php if (!empty($social_res['social_twitter'])): ?>
					<li><a href="<?= $social_res['social_twitter'];?>" target="_blank"><i class="fa-brands fa-x-twitter"></i></a></li>
					<?php endif; ?>

					<?php if (!empty($social_res['social_linkedin'])): ?>
					<li><a href="<?= $social_res['social_linkedin'];?>" target="_blank"><i class="fa-brands fa-linkedin-in"></i></a></li>
					<?php endif; ?>

					<?php if (!empty($social_res['social_instagram'])): ?>
					<li><a href="<?= $social_res['social_instagram'];?>" target="_blank"><i class="fa-brands fa-instagram"></i></a></li>
					<?php endif; ?>

					<?php if (!empty($social_res['social_youtube'])): ?>
					<li><a href="<?= $social_res['social_youtube'];?>" target="_blank"><i class="fa-brands fa-youtube"></i></a></li>
					<?php endif; ?>

					<?php if (!empty($social_res['social_whatsapp'])): ?>
					<li><a href="<?= $social_res['social_whatsapp'];?>" target="_blank"><i class="fa-brands fa-whatsapp"></i></a></li>
					<?php endif; ?>

					<?php if (!empty($social_res['social_pinterest'])): ?>
					<li><a href="<?= $social_res['social_pinterest'];?>" target="_blank"><i class="fa-brands fa-pinterest-p"></i></a></li>
					<?php endif; ?>

					<?php if (!empty($social_res['social_tiktok'])): ?>
					<li><a href="<?= $social_res['social_tiktok'];?>" target="_blank"><i class="fa-brands fa-tiktok"></i></a></li>
					<?php endif; ?>

					<?php if (!empty($social_res['social_telegram'])): ?>
					<li><a href="<?= $social_res['social_telegram'];?>" target="_blank"><i class="fa-brands fa-telegram"></i></a></li>
					<?php endif; ?>

					<?php if (!empty($social_res['social_snapchat'])): ?>
					<li><a href="<?= $social_res['social_snapchat'];?>" target="_blank"><i class="fa-brands fa-snapchat-ghost"></i></a></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
</div>

<!-- Floating Tracking Card -->
<div class="tracking-card-wrapper">
	<div class="tracking-card">
		<div class="tracking-card-inner">
			<div class="tracking-icon">
				<i class="fa-solid fa-magnifying-glass"></i>
			</div>
			<div class="tracking-content">
				<h3>Track Your Shipment</h3>
				<p>Enter your tracking ID to get real-time updates</p>
			</div>
			<form class="tracking-form" action="<?= SITE_URL;?>deliveryHistory_enhanced.php" method="get">
				<div class="tracking-input-group">
					<input type="text" name="doc_no" placeholder="Enter Tracking ID / Docket No." required />
					<button type="submit">
						<i class="fa-solid fa-arrow-right"></i>
						<span>Track</span>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Mobile Sticky Tracking Bar -->
<div class="mobile-tracking-bar">
	<form action="<?= SITE_URL;?>deliveryHistory_enhanced.php" method="get">
		<div class="mobile-tracking-inner">
			<input type="text" name="doc_no" placeholder="Track your shipment..." required />
			<button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
		</div>
	</form>
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
								<i><img src="<?= SITE_URL;?>admin/post_img/<?= $team_result['team_image']; ?>" alt="<?= htmlspecialchars($team_result['team_title']); ?>" /></i>
								<strong><?= $team_result['team_title']; ?></strong>
								<span><?= $team_result['team_desg']; ?></span>
								<div class="tm-social">
									<a href="#" title="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
									<a href="mailto:#" title="Email"><i class="fa-solid fa-envelope"></i></a>
								</div>
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
				<div class="homeGalleryGrid">
					<?php
					// Fetch latest gallery images from tbl_gallery
					$home_gallery_sql = "SELECT * FROM tbl_gallery WHERE status = 1 ORDER BY gallery_id DESC LIMIT 6";
					$home_gallery_rs = mysqli_query($conn, $home_gallery_sql);
					$img_count = 0;
					while($home_gallery_row = mysqli_fetch_array($home_gallery_rs)) {
						$img_count++;
						$size_class = ($img_count == 1 || $img_count == 6) ? 'galBig' : 'galSmall';
					?>
					<div class="galItem <?= $size_class; ?>">
						<a href="<?= SITE_URL; ?>admin/post_img/<?= $home_gallery_row['gallery_image']; ?>" data-fancybox="homeGal">
							<img src="<?= SITE_URL; ?>admin/post_img/<?= $home_gallery_row['gallery_image']; ?>" alt="<?= htmlspecialchars($home_gallery_row['gallery_title'] ?? 'Gallery'); ?>" />
						</a>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</section>

<style>
.homeGalleryGrid {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	grid-template-rows: repeat(2, 180px);
	gap: 10px;
}
.homeGalleryGrid .galItem {
	overflow: hidden;
	border-radius: 10px;
}
.homeGalleryGrid .galItem img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	transition: transform 0.3s ease;
}
.homeGalleryGrid .galItem:hover img {
	transform: scale(1.05);
}
.homeGalleryGrid .galItem.galBig {
	grid-row: span 2;
}
@media (max-width: 768px) {
	.homeGalleryGrid {
		grid-template-columns: repeat(2, 1fr);
		grid-template-rows: repeat(3, 150px);
	}
	.homeGalleryGrid .galItem.galBig {
		grid-row: span 1;
	}
}
</style>

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
