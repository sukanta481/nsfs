<footer class="ftrSec">
	<div class="ftTp">
		<div class="container">
			<div class="row">
				<div class="col-md-2">
					<div class="logoPrt">
						<a href="#" class="ftrLogo"><img src="<?= SITE_URL; ?>assets/images/ftLogo.png" alt="ftLogo" /></a>
						<p><?= $widget_res['widget_site_info'];?></p>
						<ul class="banSos ftSos">
							<li><a href="<?= $social_res['social_linkedin'];?>" target="_blank"><i class="fa-brands fa-linkedin-in"></i></a></li>
							<li><a href="<?= $social_res['social_twitter'];?>" target="_blank"><i class="fa-brands fa-x-twitter"></i></a></li>
							<li><a href="<?= $social_res['social_instagram'];?>" target="_blank"><i class="fa-brands fa-instagram"></i></a></li>
						</ul>
					</div>
				</div>
				<div class="col-md-2 mnuFoot">
					<h6>What we offer</h6>
					<ul>
						<?php
        	$get_service_sql="select * from tbl_service order by service_id asc";
			$get_service_rs=mysqli_query($conn,$get_service_sql);
			$i=1;
			while($get_service_row=mysqli_fetch_array($get_service_rs))
			{
        	?>
						<li><a href="<?= SITE_URL; ?>service.php#service<?= $i;?>"><?= $get_service_row['service_title'];?></a></li>
			<?php
			$i++;
			}
            ?>
					</ul>
				</div>
				<div class="col-md-2 mnuFoot">
					<h6>quick linkS</h6>
					<ul>
						<li><a href="<?= SITE_URL; ?>">Home</a></li>
						<li><a href="<?= SITE_URL; ?>about.php">About</a></li>
						<li><a href="<?= SITE_URL; ?>gallery.php">Gallery</a></li>
						<li><a href="<?= SITE_URL; ?>service.php">Service</a></li>
						<li><a href="<?= SITE_URL; ?>contact-us.php">Contact us</a></li>
						
						
					</ul>
				</div>
				<div class="col-md-2 mnuFoot">
					<h6>Industries we serve</h6>
					<ul id="navigation-menu">
						<?php
        	$get_service_sql="select * from tbl_service order by service_id asc";
			$get_service_rs=mysqli_query($conn,$get_service_sql);
			$i=1;
			while($get_service_row=mysqli_fetch_array($get_service_rs))
			{
        	?>
						<li><a href="<?= SITE_URL; ?>service.php#service<?= $i;?>"><?= $get_service_row['service_title'];?></a></li>
			<?php
			$i++;
			}
            ?>			
					
					</ul>
				</div>
				<div class="col-md-2">
					<h6>Get in Touch</h6>
					<ul class="locLst">
						<li><i class="fa-regular fa-location-dot"></i><a href="#"><?= $contact_res['contact_address'];?></a></li>
						<li><i class="fa-regular fa-envelope"></i><a href="mailto:<?= $contact_res['contact_email'];?>"><?= $contact_res['contact_email'];?></a></li>
						<li><i class="fa-regular fa-phone"></i><a href="tel:<?= $contact_res['contact_phone'];?>"><?= $contact_res['contact_phone'];?></a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="ftDn">
		<div class="container">
			<div class="row">
				<div class="col-md-6">
					<p><?= $widget_res['widget_copyright'];?> | <?= $widget_res['design_and_developed_by'];?></p>
				</div>
				<div class="col-md-6 text-right">
					<ul>
						<li><a href="<?= SITE_URL; ?>page.php?alise=terms-and-condition">Terms & Condition</a></li>
						<li><a href="<?= SITE_URL; ?>page.php?alise=privacy-policy">Privacy Policy</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</footer>




<!-- Bootstrap core JavaScript -->
<script src="<?= SITE_URL; ?>assets/js/jquerymin.js"></script>
<script src="<?= SITE_URL; ?>assets/js/bootstrap.js"></script>
<script src="<?= SITE_URL; ?>assets/js/wow.js"></script>
<script src="<?= SITE_URL; ?>assets/js/scrollspy.js"></script>
<script src="<?= SITE_URL; ?>assets/js/wowscripts.js"></script>
<script src="<?= SITE_URL; ?>assets/js/owl.carousel.js"></script>
<script src="<?= SITE_URL; ?>assets/js/carouselscript.js"></script>
<script src="<?= SITE_URL; ?>assets/js/jquery.countup.min.js"></script>
<script src="<?= SITE_URL; ?>assets/js/jquery.waypoints.min.js"></script>
<script>
$('.counter').countUp();
</script>
<!-- Light box -->
<script src="<?= SITE_URL; ?>assets/js/examples.js"></script>
<script src="<?= SITE_URL; ?>assets/js/jquery.js"></script>
<script src="<?= SITE_URL; ?>assets/js/jquery.simpleLoadMore.js"></script>
<script>
    jQuery('.load_sec').simpleLoadMore({
      item: '.item',
      count: 8,
      itemsToLoad: 8,
      counterInBtn: true,
      btnText: 'Load More',
    });
  </script>
  <script>
  	if ( window.location.hash ) scroll(0,0);
// void some browsers issue
setTimeout( function() { scroll(0,0); }, 1);

  // from same page
$(function() {
  $('#navigation-menu a').click(function() {
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {

      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
      if (target.length) {
        $('html,body').animate({
          scrollTop: target.offset().top-160 + 'px'
        }, 2000);
        return false;
      }
    }
  });
  
  // from other page
if(window.location.hash) {

    // smooth scroll to the anchor id
    $('html, body').animate({
        scrollTop: $(window.location.hash).offset().top-180 + 'px'
    }, 2000, 'swing');
}
})
  </script>
  	
</body>
</html>

