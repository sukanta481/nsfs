<?php

$get_home_page_sql = "select * from tbl_pagecontent where id='1'";
$get_home_page_qry = mysqli_query($conn,$get_home_page_sql);
$get_home_page_rs = mysqli_fetch_assoc($get_home_page_qry);
?>
<section class="tesSec tophead">
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<div class="tesTxt">
<?= $get_home_page_rs['add_cont3'];?>
				</div>
			</div>
			<div class="col-md-8">
				<div class="testiCaro">
					<div class="owl-carousel">
						<?php
        	$get_testimonial_sql="select * from tbl_testimonial order by testimonial_id asc";
			$get_testimonial_rs=mysqli_query($conn,$get_testimonial_sql);
			while($get_testimonial_row=mysqli_fetch_array($get_testimonial_rs))
			{
        	?>
						<div class="item">
							<div class="tesBx">
								<?php 
								$testimonial_rate= $get_testimonial_row['testimonial_rate'];
								for($i=0;$i<$testimonial_rate;$i++)
								{
								?>
								<i><img src="<?= SITE_URL;?>assets/images/full_star.png" alt="str" /></i>
								<?php
								}
								?>
								<?php 
								$remain= 5-$get_testimonial_row['testimonial_rate'];
								for($i=0;$i<$remain;$i++)
								{
								?>
								<i><img src="<?= SITE_URL;?>assets/images/blank_star.png" alt="str" /></i>
								<?php
								}
								?>
								<?= $get_testimonial_row['testimonial_desc'];?>
								<strong><?= $get_testimonial_row['testimonial_name'];?></strong>
								<span><?= $get_testimonial_row['testimonial_position'];?></span>
							</div>
						</div>
			<?php
			}
            ?>			
					</div>
				</div>
			</div>
		</div>
	</div>
</section>