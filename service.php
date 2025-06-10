<?php include("include/header.php"); ?>
<?php
$get_page_sql = "select * from tbl_pagecontent where id='8'";
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

<section class="serSec tophead">
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
<?= $get_page_res['add_cont1'];?>	
			</div>
		</div>
		<div class="serSecIn">
			<div class="row">
				<?php
        	$get_service_category_sql="select * from  tbl_service_category order by service_category_id asc";
			$get_service_category_rs=mysqli_query($conn,$get_service_category_sql);
			$i=1;
			while($get_service_category_row=mysqli_fetch_array($get_service_category_rs))
			{
        	?>
				<div class="col-md-6">
					<div class="media">
						<div class="serImg">
							<img src="<?= SITE_URL;?>admin/post_img/<?= $get_service_category_row['service_category_image'];?>" alt="" />
						</div>
						<div class="media-body">
							<strong><?= sprintf("%02d",$i);?></strong>
							<?php
							if($get_service_category_row['service_category_title'])
							{
							?>
							<h6><?= $get_service_category_row['service_category_title'];?></h6>
							<?php
							}
							?>
							<?php
							if($get_service_category_row['service_category_srt_desc'])
							{
							?>
							<p><?= $get_service_category_row['service_category_srt_desc'];?></p>
							<?php
							}
							?>
						</div>
					</div>
				</div>
			<?php
			$i++;
			}
            ?>
				
				
				
			</div>
		</div>
	</div>
</section>



<section class="availSec tophead">
	<div class="container">
		<div class="row align-items-end">
			<div class="col-md-6">
<?= $get_page_res['feature_text'];?>	
			</div>
			<div class="col-md-6 text-right">
				<a href="<?= $get_page_res['feature_link'];?>" class="cnctBtn"><i><img src="<?= SITE_URL;?>assets/images/req.svg" alt="vwSer"></i>Request a Quote</a>
			</div>
		</div>
		<div class="avlSer">
			<?php
        	$get_service_sql="select * from tbl_service order by service_id asc";
			$get_service_rs=mysqli_query($conn,$get_service_sql);
			$i=1;
			while($get_service_row=mysqli_fetch_array($get_service_rs))
			{
        	?>
			<div class="row" id="service<?= $i;?>">
				<div class="col-md-6">
					<div class="avlSerTxt avlSerLst">
						<?php
						if($get_service_row['service_title'])
						{
						?>
						<h6><?= $get_service_row['service_title'];?></h6>
						<?php
						}
						?>
						<?= $get_service_row['service_desc'];?>
						<?php
						if($get_service_row['service_link'])
						{
						?>
						<a href="<?= $get_service_row['service_link'];?>" class="cnctBtn"><i><img src="<?= SITE_URL;?>assets/images/chkcir.svg" alt="vwSer"></i>Book Service</a>
						<?php
						}
						?>
					</div>
				</div>
				<div class="col-md-6 avlSerImg">
					<img src="<?= SITE_URL;?>admin/post_img/<?= $get_service_row['service_image'];?>" alt="avail1" />
				</div>
			</div>
			<?php
			$i++;
			}
            ?>
			
			
			
		</div>
	</div>
</section>




<?php include("include/footer.php"); ?>
