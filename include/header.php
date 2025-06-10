<?php include("apps_top.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 $page=basename($_SERVER['PHP_SELF']);

  ?>
    <?php
	$widget_sql = "select * from tbl_widget where widget_id='1'";
	$widget_qry = mysqli_query($conn,$widget_sql);
	$widget_res = mysqli_fetch_array($widget_qry);

	$contact_sql = "select * from tbl_contact where contact_id='1'";
	$contact_qry = mysqli_query($conn,$contact_sql);
	$contact_res = mysqli_fetch_array($contact_qry);
	
	$social_sql = "select * from tbl_social where social_id='1'";
	$social_qry = mysqli_query($conn,$social_sql);
	$social_res = mysqli_fetch_array($social_qry);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>North Super Fast Service</title>
    <link href="<?= SITE_URL; ?>assets/css/bootstrap.css" rel="stylesheet">
    <link href="<?= SITE_URL; ?>assets/css/fonts.css" rel="stylesheet">
	<link href="<?= SITE_URL; ?>assets/css/doc.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
	<link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.1.1/css/all.css">
	<link href="<?= SITE_URL; ?>assets/css/owl.carousel.css" rel="stylesheet">
	<link href="<?= SITE_URL; ?>assets/css/animate.css" rel="stylesheet">
		<!-- Light box -->
    <link href="<?= SITE_URL; ?>assets/css/examples.css" rel="stylesheet">
    <link href="<?= SITE_URL; ?>assets/css/jquery.css" rel="stylesheet">
    <script src='https://www.google.com/recaptcha/api.js' async defer></script>
</head>
<body>


<header class="header_sec">
	<div class="container">
		<nav class="navbar navbar-expand-lg nav_top">
			<a class="navbar-brand" href="<?= SITE_URL; ?>"><img src="<?= SITE_URL; ?>assets/images/logo.png" alt="logo" /></a>
			  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			    <span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			  </button>
			  <div class="collapse navbar-collapse justify-content-center" id="navbarSupportedContent">
		        <ul class="navbar-nav menu_sec">
		             <li <?php if($page=='index.php'){?>class="actv"<?php } ?>><a href="<?= SITE_URL; ?>">Home</a></li>
		             <li <?php if($page=='about.php'){?>class="actv"<?php } ?>><a href="<?= SITE_URL; ?>about.php">About</a></li>
		             <li <?php if($page=='service.php'){?>class="actv"<?php } ?>><a href="<?= SITE_URL; ?>service.php">Service</a></li>
		             <li <?php if($page=='gallery.php'){?>class="actv"<?php } ?>><a href="<?= SITE_URL; ?>gallery.php">Gallery</a></li>
		             <li <?php if($page=='contact.php'){?>class="actv"<?php } ?>><a href="<?= SITE_URL; ?>contact-us.php">Contact us</a></li>
		        </ul>
			  </div>
			  <a href="<?= SITE_URL; ?>contact-us.php" class="cnctBtn"><i class="fa-light fa-phone-volume"></i>Contact us</a>
		</nav>
	</div>
</header>