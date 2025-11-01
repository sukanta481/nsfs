<?php include("include/header.php"); ?>
<?php
$get_page_sql = "select * from tbl_pagecontent where id='3'";
$get_page_qry = mysqli_query($conn,$get_page_sql);
$get_page_res = mysqli_fetch_assoc($get_page_qry);

?>





<div class="banIn">
	<img src="<?= SITE_URL; ?>admin/post_img/<?= $get_page_res['page_image'];?>" alt="" />
	<div class="banCon">
		<div class="container">
			<div class="banLeft">
<?= $get_page_res['banner_heading'];?>

			</div>
		</div>
	</div>
</div>



<section class="passSec tophead">
	<div class="container">
		<ul class="brdCm">
			<li><a href="<?= SITE_URL; ?>">Home</a></li>
			<li><span><?= $get_page_res['heading'];?></span></li>
		</ul>
		<div class="row">
			<div class="col-md-6">
<?= $get_page_res['content'];?>
			</div>
			<div class="col-md-6">
<?= $get_page_res['add_cont1'];?>
			</div>
		</div>
		<div class="flImg">
			<img src="<?= SITE_URL; ?>admin/post_img/<?= $get_page_res['page_details_image'];?>" alt="flimg" />
		</div>
<?= $get_page_res['add_cont2'];?>
	</div>
</section>


<?php include("sec_site_feature.php"); ?>








<section class="whyRea tophead">
	<div class="container">
		<div class="whyReaTxt">
<?= $get_page_res['add_cont3'];?>
		</div>
		<div class="owl-carousel">
			<?php
				$get_why_choose_sql="select * from  tbl_why_choose order by feature_id  asc";
				$get_why_choose_rs=mysqli_query($conn,$get_why_choose_sql);
				$i=1;
				while($get_why_choose_row=mysqli_fetch_array($get_why_choose_rs))
				{
				?>
			<div class="item">
				<div class="whyReaBx">
					<h6><?= $get_why_choose_row['feature_title'];?></h6>
					<strong><?= sprintf("%02d",$i);?></strong>
					<p><?= $get_why_choose_row['feature_text'];?></p>
				</div>
			</div>
			
			<?php
				$i++;
				}
				?>
			
			
		</div>
	</div>
</section>

<section class="msnVsn tophead">
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				<div class="msnPrt" style="background: url(<?= SITE_URL; ?>admin/post_img/<?= $get_page_res['feature_image'];?>) no-repeat;">
					<div class="msnTxt">
						<?= $get_page_res['feature_text'];?>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="msnPrt" style="background: url(<?= SITE_URL; ?>admin/post_img/<?= $get_page_res['feature_image1'];?>) no-repeat;">
					<div class="msnTxt">
						<?= $get_page_res['feature_text1'];?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>


<section class="tmKey tophead">
	<div class="container">
		<div class="whyReaTxt">
			<?= $get_page_res['add_cont4'];?>
		</div>
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
				<div class="tmKeyBx">
					<img src="<?= SITE_URL;?>admin/post_img/<?= $team_result['team_image']; ?>" alt="tmk1" />
					<div class="tmKeyBxTxt">
						<h6><?= $team_result['team_title']; ?></h6>
						<b><?= $team_result['team_desg']; ?></b>
					</div>
				</div>
			</div>
			<?php $rowCount ++ ; ?>
			<?php
			}
			?>
			
			
		</div>
	</div>
</section>



<?php include("sec_testimonial.php"); ?>



<?php include("include/footer.php"); ?>