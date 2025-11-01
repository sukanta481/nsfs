<?php require 'top_header.php'; ?>
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>      
      <!-- page content -->
      <div class="right_col" role="main">
        <div class="">
          <div class="page-title">
            <div class="title_left">
              <h3>Admin Panel</h3>
            </div>
          </div>
          <div class="clearfix"></div>
          
          <?php if(isset($tripmsg) && !empty($tripmsg)): ?>
            <div class="" style="margin:2px;padding:3px;">
              <span style="margin-left:30px;"><?php echo $tripmsg;?></span>
            </div>
          <?php endif;?>

          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <?php 
                // Pending POD handling: set a custom flag
                $pending_pod_mode = false;
                if (
                  isset($_GET['status']) && $_GET['status'] === 'PendingPOD' ||
                  (isset($status) && $status === 'PendingPOD')
                ) {
                  $pending_pod_mode = true;
                  $_GET['status'] = 'Delivered'; // List delivered, but we'll filter PendingPOD inside list_trip_table.php
                }
                if(isset($type) && ($type=='view_trip')) {
                  require 'view_trip.php';
                } elseif(isset($type) && ($type=='list_trip')) {
                  $pending_pod_mode = $pending_pod_mode; // just for clarity
                  require 'list_trip.php';
                } elseif(isset($type) && ($type=='list_trip_company')) {
                  require 'list_trip_compnay.php';
                } elseif(isset($type) && ($type=='edit_trip_company')) {
                  require 'edit_trip_company.php';
                } elseif(isset($type) && ($type=='print_trip')) {
                  require 'print_trip.php';
                } elseif(isset($type) && ($type=='print_doc')) {
                  require 'print_doc.php';
                } else {
                  //Do Nothing
                } 
              ?>
            </div>
          </div>
        </div> 
        <?php require 'footer.php';?>
      </div>
    </div>
  </div>
</body>
