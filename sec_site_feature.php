<?php
 $page=basename($_SERVER['PHP_SELF']);
?>
<section class="countSec <?php if($page!='index.php'){?>countIn<?php } ?>">
	<div class="container">
		<div class="row">
			<?php
				$get_site_feature_sql="select * from  tbl_site_feature order by feature_id  asc";
				$get_site_feature_rs=mysqli_query($conn,$get_site_feature_sql);
				while($get_site_feature_row=mysqli_fetch_array($get_site_feature_rs))
				{
				?>
			<div class="col-md-3">
				<div class="countBx">
					<i><img src="<?= SITE_URL;?>admin/post_img/<?= $get_site_feature_row['feature_image'];?>" alt="cnt1" /></i>
					<strong><span class="counter"><?= $get_site_feature_row['feature_count'];?></span><?= $get_site_feature_row['feature_text'];?></strong>
					<h5><?= $get_site_feature_row['feature_title'];?></h5>
				</div>
			</div>
			<?php
				}
				?>
			
		</div>
	</div>
</section>