<?php include("include/header.php"); ?>
<?php
$get_page_sql = "select * from tbl_pagecontent where id='5'";
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


<section class="gallerySec tophead">
	<div class="container">
		<ul class="brdCm">
			<li><a href="<?= SITE_URL; ?>">Home</a></li>
			<li><span><?= $get_page_res['heading'];?></span></li>
		</ul>
		<div class="row align-items-end">
			<div class="col-md-6">
<?= $get_page_res['content'];?>	
				
			</div>
			<div class="col-md-6">
				<ul class="gallTab">
					<li class="actv"><a href="#tab1">All</a></li>
					<?php
					$get_gallery_category_sql="select * from tbl_gallery_category order by gallery_category_id asc";
					$get_gallery_category_rs=mysqli_query($conn,$get_gallery_category_sql);
					$i=2;
					while($get_gallery_category_row=mysqli_fetch_array($get_gallery_category_rs))
					{
					?>
					<li><a href="#tab<?= $i;?>"><?= $get_gallery_category_row['gallery_category_title'];?></a></li>
					<?php
					$i++;
					}
					?>
					
				</ul>
			</div>
		</div>
		<div id="tab1" class="tabDes load_sec" style="display: block;">
			<div class="gallImg ">
				<?php
				$get_gallery_image_sql="select * from  tbl_gallery order by gallery_id asc";
				$get_gallery_image_rs=mysqli_query($conn,$get_gallery_image_sql);
				while($get_gallery_image_row=mysqli_fetch_array($get_gallery_image_rs))
				{
				?>
				<div class="galImgIn item">
					<a href="<?= SITE_URL; ?>admin/post_img/<?= $get_gallery_image_row['gallery_image']?>" data-fancybox="gal"><img src="<?= SITE_URL; ?>admin/post_img/<?= $get_gallery_image_row['gallery_image']?>" alt="<?= $get_gallery_image_row['gallery_title']?>" class="w-100"/></a>
				</div>
				<?php
				}
				?>
				
			</div>
			<!-- <div class="text-center">
				<a href="#" class="cnctBtn"><i><img src="assets/images/load.svg" alt="vwSer"></i>LOad More</a>
			</div> -->
		</div>
		<?php
		$get_gallery_category_sql="select * from tbl_gallery_category order by gallery_category_id asc";
		$get_gallery_category_rs=mysqli_query($conn,$get_gallery_category_sql);
		$i=2;
		while($get_gallery_category_row=mysqli_fetch_array($get_gallery_category_rs))
		{
		?>
		<div id="tab<?= $i;?>" class="tabDes load_sec">
			<div class="gallImg">
				<?php
				$get_gallery_image_sql="select * from  tbl_gallery where gallery_category_id='".$get_gallery_category_row['gallery_category_id']."' order by gallery_id asc";
				$get_gallery_image_rs=mysqli_query($conn,$get_gallery_image_sql);
				while($get_gallery_image_row=mysqli_fetch_array($get_gallery_image_rs))
				{
				?>
				<div class="galImgIn item">
					<a href="<?= SITE_URL; ?>admin/post_img/<?= $get_gallery_image_row['gallery_image']?>" data-fancybox="gal"><img src="<?= SITE_URL; ?>admin/post_img/<?= $get_gallery_image_row['gallery_image']?>" alt="<?= $get_gallery_image_row['gallery_title']?>" class="w-100"/></a>
				</div>
				<?php
				}
				?>
				
			</div>
			<!-- <div class="text-center">
				<a href="#" class="cnctBtn"><i><img src="assets/images/load.svg" alt="vwSer"></i>LOad More</a>
			</div> -->
		</div>
		<?php
		$i++;
		}
		?>
		
		
	</div>
</section>




<?php include("include/footer.php"); ?>