<?php include("include/header.php"); ?>
<?php
$alise=$_REQUEST['alise'];
$get_page_sql = "select * from tbl_pagecontent where alise='".$alise."'";
$get_page_qry = mysqli_query($conn,$get_page_sql);
$get_page_res = mysqli_fetch_assoc($get_page_qry);

?>


<section class="passSec tophead clspage">
	<div class="container">
		<ul class="brdCm">
			<li><a href="<?= SITE_URL; ?>">Home</a></li>
			<li><span><?= $get_page_res['heading'];?></span></li>
		</ul>
		
<?= $get_page_res['content'];?>
	</div>
</section>





<?php include("include/footer.php"); ?>