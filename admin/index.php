<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'top_header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>      
      <!-- page content -->
      <div class="right_col" role="main" style="padding-top:0px !important;">
        <!-- Dashboard header -->
        <div class="dashboard-header-row" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;">
          <div>
            <h2 class="dashboard-title" style="margin:0;font-weight:800;letter-spacing:-.5px;color:#222;font-size:2.15rem;">Dashboard Overview</h2>
          </div>
        </div>
        <!-- Metric Cards Grid -->
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
        // 1. Total Docket
        $total_docket = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details");

        // 2. NON-DRS (Registered, status='Pickup', branch_office is NULL/empty)
        $non_drs = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Picked Up' AND (branch_office IS NULL OR branch_office='' OR branch_office=0)");

        // 3. DRS (status='Pickup', branch_office not NULL/empty)
        $drs = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Picked Up' AND branch_office IS NOT NULL AND branch_office<>'' AND branch_office<>0");

        // 4. In Transit
        $intransit = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='In Transit'");

        // 5. Out For Delivery
        $out_for_delivery = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Out for Delivery'");

        // 6. Delivered
        $delivered = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Delivered'");

        // 7. Delayed
        $delayed = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Delayed'");

        // 8. Pending POD (Delivered but proof_of_delivery empty)
        $pending_pod = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Delivered' AND (proof_of_delivery IS NULL OR proof_of_delivery='')");
        ?>
        <div class="dashboard-metric-grid">
          <!-- 1. Total Docket -->
          <a href="trip.php?type=list_trip" class="metric-card metric-blue metric-card-bold">
            <div class="metric-icon" style="background:#2057ae;color:#fff;"><i class="fa fa-th-list"></i></div>
            <div>
              <div class="metric-label">Total Docket</div>
              <div class="metric-value"><?= $total_docket ?></div>
              <div class="metric-desc">All dockets in the system</div>
            </div>
          </a>
          <!-- 2. NON-DRS -->
          <a href="trip.php?type=list_trip&doc_type=NON-DRS" class="metric-card metric-orange metric-card-bold">
            <div class="metric-icon" style="background:#e95f18;color:#fff;"><i class="fa fa-list-alt"></i></div>
            <div>
              <div class="metric-label">NON-DRS</div>
              <div class="metric-value"><?= $non_drs ?></div>
              <div class="metric-desc">Pickup, no branch assigned</div>
            </div>
          </a>
          <!-- 3. DRS -->
          <a href="trip.php?type=list_trip&doc_type=DRS" class="metric-card metric-green metric-card-bold">
            <div class="metric-icon" style="background:#19c37d;color:#fff;"><i class="fa fa-truck-moving"></i></div>
            <div>
              <div class="metric-label">DRS</div>
              <div class="metric-value"><?= $drs ?></div>
              <div class="metric-desc">Pickup with branch office</div>
            </div>
          </a>
          <!-- 4. In Transit -->
          <a href="trip.php?type=list_trip&status=In%20Transit" class="metric-card metric-lime metric-card-bold">
            <div class="metric-icon" style="background:#2e86ab;color:#fff;"><i class="fa fa-road"></i></div>
            <div>
              <div class="metric-label">In Transit</div>
              <div class="metric-value"><?= $intransit ?></div>
              <div class="metric-desc">Currently in transit</div>
            </div>
          </a>
          <!-- 5. Out For Delivery -->
          <a href="trip.php?type=list_trip&status=Out%20for%20Delivery" class="metric-card metric-yellow metric-card-bold">
            <div class="metric-icon" style="background:#b6901e;color:#fff;"><i class="fa fa-motorcycle"></i></div>
            <div>
              <div class="metric-label">Out For Delivery</div>
              <div class="metric-value"><?= $out_for_delivery ?></div>
              <div class="metric-desc">Ready for delivery</div>
            </div>
          </a>
          <!-- 6. Delivered -->
          <a href="trip.php?type=list_trip&status=Delivered" class="metric-card metric-purple metric-card-bold">
            <div class="metric-icon" style="background:#6d5dfc;color:#fff;"><i class="fa fa-check-square"></i></div>
            <div>
              <div class="metric-label">Delivered</div>
              <div class="metric-value"><?= $delivered ?></div>
              <div class="metric-desc">Successfully delivered</div>
            </div>
          </a>
          <!-- 7. Delayed -->
          <a href="trip.php?type=list_trip&status=Delayed" class="metric-card metric-red metric-card-bold">
            <div class="metric-icon" style="background:#e04c41;color:#fff;"><i class="fa fa-exclamation-triangle"></i></div>
            <div>
              <div class="metric-label">Delayed</div>
              <div class="metric-value"><?= $delayed ?></div>
              <div class="metric-desc">Currently delayed</div>
            </div>
          </a>
          <!-- 8. Pending POD -->
          <a href="trip.php?type=list_trip&status=PendingPOD" class="metric-card metric-pod metric-card-bold">
            <div class="metric-icon" style="background:#283044;color:#fff;"><i class="fa fa-upload"></i></div>
            <div>
              <div class="metric-label">Pending POD</div>
              <div class="metric-value"><?= $pending_pod ?></div>
              <div class="metric-desc">Delivered, Proof not uploaded</div>
            </div>
          </a>
        </div>
        <!-- Floating Add Register Button (bottom-right corner) -->
        <a href="register.php?type=add_register" class="add-register-fab">
          <i class="fa fa-plus"></i> Add Register
        </a>
        <!-- END Dashboard Cards -->
      </div>
    </div>
  </div>
  <?php require 'footer.php';?>
  <style>
/* --- Dashboard Style --- */
.right_col { background: #f8fafc !important; min-height: 100vh; }
.dashboard-header-row { margin-bottom: 18px; }
.dashboard-title { font-weight: 800; letter-spacing: -.5px; color: #222; font-size: 2.13rem; margin-bottom: 0;}
.dashboard-subtitle { color: #7f8ba5; font-size: 1.12rem; font-weight: 500; margin-top: 5px; }
.dashboard-metric-grid {
  width: 100%; max-width: 1750px; margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(315px, 1fr));
  gap: 32px;
  margin-top: 0 !important;
  margin-bottom: 0;
}
.metric-card {
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 8px 32px 0 rgba(78, 110, 255, 0.07), 0 1.5px 4px 0 rgba(40, 60, 90, 0.03);
  padding: 35px 30px 30px 30px;
  min-height: 154px;
  display: flex;
  align-items: flex-start;
  gap: 23px;
  transition: box-shadow .12s, transform .14s;
  position: relative;
  text-decoration: none !important;
  color: #141C29 !important;
  border: 1.5px solid transparent;
}
.metric-card-bold {
  font-family: 'Segoe UI', Arial, sans-serif;
}
.metric-card .metric-label {
  font-size: 1.19rem;
  font-weight: 800;
  letter-spacing: 0.01em;
  color: #141c29;
  text-shadow: 0 1px 0 #fff, 0 1px 4px #ddd;
}
.metric-card .metric-value {
  font-size: 2.3rem;
  font-weight: 900;
  margin-top: 4px;
  letter-spacing: 1.5px;
  color: #222;
  text-shadow: 0 2px 4px #e5eaf2;
}
.metric-card .metric-desc {
  font-size: 1.07rem;
  color: #6e7282;
  font-weight: 500;
  margin-top: 3px;
}
.metric-card .metric-icon {
  width: 56px; height: 56px;
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  font-size: 2.3rem;
  margin-right: 0;
  margin-top: 2px;
  box-shadow: 0 2px 12px 0 rgba(38,38,38,0.10);
}
/* -- Floating Add Register Button -- */
.add-register-fab {
  position: fixed;
  bottom: 38px;
  right: 50px;
  z-index: 1001;
  background: #25c16f;
  color: #fff !important;
  font-size: 1.17rem;
  font-weight: 700;
  border-radius: 34px;
  box-shadow: 0 6px 24px 0 rgba(40,200,110,0.13);
  padding: 17px 30px 17px 24px;
  display: flex;
  align-items: center;
  gap: 9px;
  text-decoration: none !important;
  border: none;
  transition: background 0.16s, box-shadow 0.13s;
  cursor: pointer;
}
.add-register-fab:hover, .add-register-fab:focus {
  background: #1e9d57;
  color: #fff !important;
  box-shadow: 0 12px 38px 0 rgba(40,200,110,0.19);
}
.add-register-fab i {
  font-size: 1.19em;
  margin-right: 7px;
}
/* --- Responsive Styles --- */
@media (max-width: 1200px) {
  .dashboard-metric-grid { gap: 16px; }
  .metric-card { min-height: 120px; padding: 24px 15px 20px 15px;}
}
@media (max-width: 900px) {
  .dashboard-metric-grid { 
    grid-template-columns: 1fr;
    gap: 15px;
    max-width: 100vw;
    margin: 0;}
  .metric-card { 
    min-height: 80px;
    padding: 14px 10px 13px 13px;
    border-radius: 16px;
    box-shadow: 0 3px 14px 0 rgba(78, 110, 255, 0.06);
    border: 1.5px solid #f1f3fb !important;
    margin: 0;
  }
  .right_col {
    padding: 8px 0 70px 0 !important;
    min-width: unset !important;
  }
  .dashboard-title { font-size: 1.21rem;}
  .dashboard-header-row { 
   padding-left: 9px; padding-right: 9px;
    margin-bottom: 10px;
  }
  .add-register-fab { right: 17px; bottom: 17px; padding: 13px 22px; font-size: 1.01rem;}
}
@media (max-width: 600px) {
  .add-register-fab {
    left: 7px; right: 7px; bottom: 9px;
    width: auto; min-width: unset;
    border-radius: 19px;
    justify-content: center;
    padding: 13px 0;
    font-size: .99rem;
  } 
  .right_col {
    padding: 2px 0 74px 0 !important;
  }
   .dashboard-metric-grid {
    gap: 8px;
  }
  .metric-card {
    border-radius: 11px;
    padding: 10px 6px 10px 9px;
    min-height: 54px;
    font-size: .99rem;
    box-shadow: 0 2px 8px 0 rgba(78, 110, 255, 0.07);
    border: 1.5px solid #f4f7fd !important;
  }
  .dashboard-header-row, .dashboard-title, .dashboard-subtitle {
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
  }
}
  </style>
</body>
