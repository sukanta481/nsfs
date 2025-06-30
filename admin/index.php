<?php require 'top_header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>      
      <!-- page content -->
      <div class="right_col" role="main">
        <div class="row tile_count">
          <h3>Admin Panel</h3>
        </div>
        <?php
function fetch_count($sql) {
  global $conn;
  $res = mysqli_query($conn, $sql);
  if (!$res) {
    die("Query failed: ".mysqli_error($conn)." SQL: $sql");
  }
  $row = mysqli_fetch_assoc($res);
  return $row['c'] ?? 0;
}
$total_docs = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details");
$drs_count = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE doc_type='DRS'");
$non_drs_count = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE doc_type='NON-DRS'");
$delayed_count = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Delayed'");
$intransit_count = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='In Transit'");
$ofd_count = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Out for Delivery'");
$delivered_count = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Delivered'");
?>

        <!-- Dashboard Metric Cards -->
			<!-- PILL STYLE DASHBOARD CARDS (LIKE YOUR SCREENSHOT) -->
			<div class="dashboard-pill-cards" style="margin-top:30px; margin-bottom:30px;">
			<div class="pill-row">
				<!-- Total Docs -->
				<a href="trip.php?type=list_trip" class="pill-card pill-blue">
				<div class="pill-icon"><i class="fa fa-file"></i></div>
				<div>
					<div class="pill-label">Total Docs</div>
					<div class="pill-count"><?= $total_docs ?></div>
				</div>
				</a>
				<!-- DRS -->
				<a href="trip.php?type=list_trip&doc_type=DRS" class="pill-card pill-green">
				<div class="pill-icon"><i class="fa fa-truck"></i></div>
				<div>
					<div class="pill-label">DRS</div>
					<div class="pill-count"><?= $drs_count ?></div>
				</div>
				</a>
				<!-- NON-DRS -->
				<a href="trip.php?type=list_trip&doc_type=NON-DRS" class="pill-card pill-orange">
				<div class="pill-icon"><i class="fa fa-clipboard"></i></div>
				<div>
					<div class="pill-label">NON-DRS</div>
					<div class="pill-count"><?= $non_drs_count ?></div>
				</div>
				</a>
				<!-- Delayed -->
				<a href="trip.php?type=list_trip&status=Delayed" class="pill-card pill-red">
				<div class="pill-icon"><i class="fa fa-clock-o"></i></div>
				<div>
					<div class="pill-label">Delayed</div>
					<div class="pill-count"><?= $delayed_count ?></div>
				</div>
				</a>
				<!-- In Transit -->
				<a href="trip.php?type=list_trip&status=In%20Transit" class="pill-card pill-lime">
				<div class="pill-icon"><i class="fa fa-truck"></i></div>
				<div>
					<div class="pill-label">In Transit</div>
					<div class="pill-count"><?= $intransit_count ?></div>
				</div>
				</a>
				<!-- Out for Delivery -->
				<a href="trip.php?type=list_trip&status=Out%20for%20Delivery" class="pill-card pill-yellow">
				<div class="pill-icon"><i class="fa fa-location-arrow"></i></div>
				<div>
					<div class="pill-label">Out for Delivery</div>
					<div class="pill-count"><?= $ofd_count ?></div>
				</div>
				</a>
				<!-- Delivered -->
				<a href="trip.php?type=list_trip&status=Delivered" class="pill-card pill-purple">
				<div class="pill-icon"><i class="fa fa-check-circle"></i></div>
				<div>
					<div class="pill-label">Delivered</div>
					<div class="pill-count"><?= $delivered_count ?></div>
				</div>
				</a>
			</div>
			</div>
        <!-- END Dashboard Cards -->
      </div>
    </div>
  </div>
  <?php require 'footer.php';?>
  <style>
.dashboard-pill-cards {
  display: flex; flex-direction: column; align-items: center;
}
.pill-row {
  width: 100%; max-width: 1450px;
  display: flex; flex-wrap: wrap; gap: 32px; justify-content: flex-start;
}
.pill-card {
  flex: 1 1 280px;
  min-width: 250px;
  max-width: 340px;
  display: flex;
  align-items: center;
  background: #eee;
  border-radius: 80px;
  box-shadow: 0 4px 20px 0 rgba(45,65,100,.07);
  padding: 12px 38px 12px 26px;
  margin: 10px 0;
  color: #fff !important;
  font-family: 'Segoe UI', Arial, sans-serif;
  text-decoration: none;
  transition: box-shadow .15s, transform .15s;
  position: relative;
  overflow: hidden;
}
.pill-card:hover {
  box-shadow: 0 8px 36px 0 rgba(40,80,160,.15);
  transform: translateY(-3px) scale(1.04);
  text-decoration: none !important;
}
.pill-icon {
  width: 60px; height: 60px; margin-right: 22px;
  border: 2.5px solid rgba(255,255,255,0.5);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 2.1rem;
  background: rgba(255,255,255,0.08);
}
.pill-label {
  font-size: 1.13rem; font-weight: 500; opacity: 0.95;
}
.pill-count {
  font-size: 2.08rem; font-weight: bold; margin-top: 1px; letter-spacing: 1px;
}
.pill-blue {
  background: linear-gradient(120deg, #28a1fa 60%, #2365ea 100%);
}
.pill-green {
  background: linear-gradient(120deg, #28b77b 60%, #20ac91 100%);
}
.pill-orange {
  background: linear-gradient(120deg, #ffb93e 50%, #ef9d31 100%);
  color: black !important;
}
.pill-red {
  background: linear-gradient(120deg, #ff4068 60%, #e82121 100%);
}
.pill-lime {
  background: linear-gradient(120deg, #49e49b 60%, #24bd6b 100%);
  color: #fff !important;
}
.pill-yellow {
  background: linear-gradient(120deg, #ffe95b 60%, #ffb730 100%);
  color: #222 !important;
}
.pill-purple {
  background: linear-gradient(120deg, #6e77ff 60%, #8567ee 100%);
}
@media (max-width: 1200px) {
  .pill-row {gap: 18px;}
  .pill-card {min-width: 220px;}
}
@media (max-width: 900px) {
  .pill-row {flex-direction: column; align-items: center; gap: 12px;}
  .pill-card {width:90%; min-width:180px;}
}
</style>
</body>
