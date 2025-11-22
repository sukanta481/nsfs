<?php
// Connect DB if not already
// include("include/db_connect.php"); // Uncomment if not already included above

// Detect AJAX
$is_ajax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ||
    (isset($_POST['ajax']) && $_POST['ajax'] == '1')
);

if (!$is_ajax) include("include/header.php");

// ... your DB connection and header logic ...

$doc_no = isset($_GET['doc_no']) ? mysqli_real_escape_string($conn, trim($_GET['doc_no'])) : '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['doc_no'])) {
    $doc_no = mysqli_real_escape_string($conn, trim($_POST['doc_no']));
}
$get_shipping_details_row = false;
$branch_office_name = '-';
if ($doc_no) {
    // Query from docket_details table
    $get_shipping_details_sql = "SELECT * FROM docket_details WHERE doc_no='" . $doc_no . "'";
    $get_shipping_details_rs = mysqli_query($conn, $get_shipping_details_sql);
    $get_shipping_details_row = mysqli_fetch_assoc($get_shipping_details_rs);

    // Branch Office name resolve (if any)
    if ($get_shipping_details_row && !empty($get_shipping_details_row['branch_office'])) {
        $branch_office = $get_shipping_details_row['branch_office'];
        $office_sql = "SELECT office_name FROM tbl_offices WHERE office_name='$branch_office' LIMIT 1";
        $office_rs = mysqli_query($conn, $office_sql);
        if ($office_row = mysqli_fetch_assoc($office_rs)) {
            $branch_office_name = $office_row['office_name'];
        } else {
            $branch_office_name = $branch_office;
        }
    }
}

if ($get_shipping_details_row) {
    $docket_id = $get_shipping_details_row['docket_id'];
    
    // Check if this docket has a manifest (branch transfer)
    $has_manifest = false;
    $manifest_id = null;
    $manifest_created_time = null;
    $manifest_office = '';

    // First check if manifest_dockets table exists
    $table_check = @mysqli_query($conn, "SHOW TABLES LIKE 'manifest_dockets'");

    if ($table_check && mysqli_num_rows($table_check) > 0) {
        // Table exists, proceed with manifest check
        $manifest_check_sql = "SELECT md.*, m.manifest_id, m.manifest_no, m.to_office, m.created_at as manifest_date
                              FROM manifest_dockets md
                              LEFT JOIN manifest m ON md.manifest_id = m.manifest_id
                              WHERE md.docket_id = '$docket_id'
                              LIMIT 1";
        $manifest_check_rs = @mysqli_query($conn, $manifest_check_sql);

        if ($manifest_check_rs && mysqli_num_rows($manifest_check_rs) > 0) {
            $manifest_data = mysqli_fetch_assoc($manifest_check_rs);
            $has_manifest = true;
            $manifest_id = $manifest_data['manifest_id'];
            $manifest_created_time = $manifest_data['manifest_date'];

            // Get office name
            if (!empty($manifest_data['to_office'])) {
                $office_query = "SELECT office_name FROM tbl_offices WHERE office_id='" . $manifest_data['to_office'] . "'";
                $office_rs = mysqli_query($conn, $office_query);
                if ($office_row = mysqli_fetch_assoc($office_rs)) {
                    $manifest_office = $office_row['office_name'];
                }
            }
        }
    }

    // Fetch tracking history from docket_status_history (new table)
    $tracking_history_query = "SELECT new_status as status, notes, location, changed_at as updateddate, delay_reason
                               FROM docket_status_history
                               WHERE docket_id='$docket_id'
                               ORDER BY changed_at ASC";
    $tracking_history_rs = @mysqli_query($conn, $tracking_history_query);
    
    // Group all status+note under each status
    $status_notes = [];
    $delay_info = [];
    
    if ($tracking_history_rs && mysqli_num_rows($tracking_history_rs) > 0) {
        while ($row = mysqli_fetch_assoc($tracking_history_rs)) {
            $status = $row['status'];
            if (!isset($status_notes[$status])) $status_notes[$status] = [];

            $note_data = [
                'note' => $row['notes'],
                'date' => date('d M Y, h:i A', strtotime($row['updateddate'])),
                'delay_reason' => $row['delay_reason']
            ];

            // If delay reason exists, store it
            if (!empty($row['delay_reason'])) {
                $note_data['delay_title'] = $row['delay_reason'];
                $note_data['delay_desc'] = ''; // No description in new table
                $delay_info[$status] = $note_data;
            }

            $status_notes[$status][] = $note_data;
        }
    }

    // Build dynamic timeline based on manifest presence
    if ($has_manifest) {
        // Branch transfer shipment - 6 steps
        $timeline = [
            ['label' => 'Picked Up', 'db_status' => 'Picked Up', 'desc' => $get_shipping_details_row['pickup_location'] ?? '', 'time' => null, 'icon' => 'fa-box-open', 'has_delay' => false],
            ['label' => 'Manifest Created', 'db_status' => 'Manifest Created', 'desc' => $manifest_office, 'time' => null, 'icon' => 'fa-clipboard-list', 'has_delay' => false],
            ['label' => 'In Transit to Branch', 'db_status' => 'In Transit to Branch', 'desc' => '', 'time' => null, 'icon' => 'fa-truck-loading', 'has_delay' => false],
            ['label' => 'Arrived at Branch', 'db_status' => 'Arrived at Branch', 'desc' => $manifest_office, 'time' => null, 'icon' => 'fa-warehouse', 'has_delay' => false],
            ['label' => 'Out for Delivery', 'db_status' => 'Out for Delivery', 'desc' => '', 'time' => null, 'icon' => 'fa-shipping-fast', 'has_delay' => false],
            ['label' => 'Delivered', 'db_status' => 'Delivered', 'desc' => '', 'time' => null, 'icon' => 'fa-check-circle', 'has_delay' => false],
        ];
        
        // Auto-set Manifest Created time from database
        if ($manifest_created_time && empty($status_notes['Manifest Created'])) {
            $timeline[1]['time'] = date('d M Y, h:i A', strtotime($manifest_created_time));
            $timeline[1]['auto_set'] = true;
        }
    } else {
        // Direct delivery - 4 steps
        $timeline = [
            ['label' => 'Picked Up', 'db_status' => 'Picked Up', 'desc' => $get_shipping_details_row['pickup_location'] ?? '', 'time' => null, 'icon' => 'fa-box-open', 'has_delay' => false],
            ['label' => 'In Transit', 'db_status' => 'In Transit', 'desc' => '', 'time' => null, 'icon' => 'fa-truck', 'has_delay' => false],
            ['label' => 'Out for Delivery', 'db_status' => 'Out for Delivery', 'desc' => '', 'time' => null, 'icon' => 'fa-shipping-fast', 'has_delay' => false],
            ['label' => 'Delivered', 'db_status' => 'Delivered', 'desc' => '', 'time' => null, 'icon' => 'fa-check-circle', 'has_delay' => false],
        ];
    }
    
    // Attach time and delay info to timeline from tracking history
    foreach ($timeline as $k => $step) {
        if (isset($status_notes[$step['db_status']])) {
            $most_recent = end($status_notes[$step['db_status']]);
            $timeline[$k]['time'] = $most_recent['date'];
            
            // Check for delay
            if (isset($delay_info[$step['db_status']])) {
                $timeline[$k]['has_delay'] = true;
                $timeline[$k]['delay_data'] = $delay_info[$step['db_status']];
            }
        }
    }
    
    // If pickup time is not in tracking history, use pickup_datetime from docket
    if (empty($timeline[0]['time']) && !empty($get_shipping_details_row['pickup_datetime'])) {
        $timeline[0]['time'] = date('d M Y, h:i A', strtotime($get_shipping_details_row['pickup_datetime']));
    } elseif (empty($timeline[0]['time']) && !empty($get_shipping_details_row['created_at'])) {
        $timeline[0]['time'] = date('d M Y, h:i A', strtotime($get_shipping_details_row['created_at']));
    }

    // --- SHIPMENT DETAILS (read directly from docket_details) ---
    $client_name    = !empty($get_shipping_details_row['client_name'])    ? $get_shipping_details_row['client_name']    : '-';
    $pickup_date    = !empty($get_shipping_details_row['pickup_datetime'])   ? date('d M Y', strtotime($get_shipping_details_row['pickup_datetime'])) : 
                      (!empty($get_shipping_details_row['created_at']) ? date('d M Y', strtotime($get_shipping_details_row['created_at'])) : '-');
    $car_no         = !empty($get_shipping_details_row['car_number'])     ? $get_shipping_details_row['car_number']     : '-';
    $delivery_agent = !empty($get_shipping_details_row['driver_name'])    ? $get_shipping_details_row['driver_name']    : 
                      (!empty($get_shipping_details_row['helper_name']) ? $get_shipping_details_row['helper_name'] : '-');
    $contact_number = !empty($get_shipping_details_row['driver_phone'])  ? $get_shipping_details_row['driver_phone']  : 
                      (!empty($get_shipping_details_row['helper_phone']) ? $get_shipping_details_row['helper_phone'] : '-');

    $last_done_idx = -1;
    foreach ($timeline as $i => $step) { if ($step['time']) $last_done_idx = $i; }
} else {
    $timeline = [];
    $status_notes = [];
    $car_no = $delivery_agent = $contact_number = $client_name = $pickup_date = $branch_office_name = '-';
    $last_done_idx = -1;
}

// Debugging: Uncomment below to see what data you have
// echo '<pre>'; print_r($get_shipping_details_row); echo '</pre>';

if (!$is_ajax) { ?>
<section style="background: #f5f8fd; min-height:80vh; padding:42px 0;">
  <div class="container">
    <div class="row justify-content-center flex-wrap-reverse">
      <!-- Search Bar -->
      <div class="col-lg-11 mb-4">
        <form class="track-searchbar" id="trackingSearchForm" autocomplete="off" style="margin-bottom:36px;">
            <input type="text" name="doc_no" class="form-control" placeholder="Enter Tracking ID (Doc No.)" value="<?= htmlspecialchars($doc_no) ?>" required style="max-width:340px; display:inline-block;">
            <button type="submit" class="btn btn-primary">Track</button>
        </form>
      </div>
      <div class="col-lg-12" id="trackingResultBox">
<?php } ?>

        <?php if (!$get_shipping_details_row && !empty($doc_no)): ?>
        <!-- No Tracking Found Message -->
        <div class="row justify-content-center">
          <div class="col-12 col-md-8 col-lg-6">
            <div class="track-box" style="text-align:center; padding:60px 30px;">
              <div style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fa fa-search" style="font-size:2.5rem; color:#fff; margin: 0;"></i>
              </div>
              <h3 style="color:#232351; margin-bottom:15px; font-weight: 700;">Tracking Not Found</h3>
              <p style="color:#6c757d; font-size:1.05rem; margin-bottom:15px;">
                We couldn't find any shipment with tracking ID:
              </p>
              <div style="background: #f8f9fa; padding: 12px 20px; border-radius: 8px; display: inline-block; margin-bottom: 20px;">
                <strong style="color: #dc3545; font-size: 1.2rem;"><?= htmlspecialchars($doc_no) ?></strong>
              </div>
              <p style="color:#8a91ab; font-size:0.95rem; margin-bottom: 25px;">
                Please check your tracking number and try again, or contact our support team for assistance.
              </p>
              <a href="<?= SITE_URL; ?>" class="btn" style="margin-top:10px; padding:12px 30px; border-radius:10px; background: linear-gradient(135deg, #5551c0 0%, #7b77e8 100%); border:none; color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; box-shadow: 0 4px 12px rgba(85, 81, 192, 0.2); transition: all 0.3s;">
                <i class="fa fa-home"></i> Back to Home
              </a>
            </div>
          </div>
        </div>
        <?php elseif ($get_shipping_details_row): ?>
        <div class="row justify-content-center flex-wrap-reverse">
          <!-- Timeline/Left Panel (now first on mobile) -->
          <div class="col-12 col-md-8 col-lg-7 mb-4">
            <div class="track-box">
              <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <h2 class="track-title mb-0">Arriving By</h2>
                    <div style="margin-top:22px;" class="track-date"><?= $pickup_date ?></div>
                </div>
              </div>
              <div class="track-timeline">
                <?php foreach ($timeline as $i => $step):
                    $done = $step['time'];
                    $is_last_done = ($i == $last_done_idx && $done);
                    $step_icon = $step['icon'] ?? 'fa-circle';
                    $has_delay = $step['has_delay'] ?? false;
                    $delay_data = $step['delay_data'] ?? null;
                ?>
                  <div class="track-step <?= $done ? 'done' : '' ?> <?= $is_last_done ? 'active' : '' ?> <?= $has_delay ? 'delayed' : '' ?>">
                    <div class="track-circle">
                        <?php if ($has_delay): ?>
                            <i class="fa fa-exclamation-triangle delay-icon" style="font-size: 1.3rem;"></i>
                        <?php elseif ($done): ?>
                            <i class="fa <?= $step_icon ?>" style="font-size: 1.3rem;"></i>
                        <?php else: ?>
                            <i class="fa <?= $step_icon ?>" style="font-size: 1.1rem; opacity: 0.4;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="track-info">
                        <div class="track-label">
                            <?= $step['label'] ?>
                            <?php if ($is_last_done && $done): ?>
                                <span class="current-badge">Current</span>
                            <?php endif; ?>
                            <?php if ($has_delay): ?>
                                <span class="delay-badge" data-toggle="modal" data-target="#delayModal<?= $i ?>">
                                    <i class="fa fa-exclamation-circle"></i> Delayed
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($step['desc']) && $done): ?>
                          <div class="track-desc">
                            <i class="fa fa-map-marker-alt"></i>
                            <?= htmlspecialchars($step['desc']) ?>
                          </div>
                        <?php endif; ?>
                        <?php if ($done): ?>
                          <div class="track-time">
                            <i class="fa fa-clock"></i>
                            <?= $step['time'] ?>
                            <?php if (isset($step['auto_set']) && $step['auto_set']): ?>
                                <span style="font-size: 0.75rem; color: #6c757d; margin-left: 5px;">(Auto)</span>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>
                        <?php if ($is_last_done && $done): ?>
                            <div style="margin-top: 12px; display: flex; gap: 10px; flex-wrap: wrap;">
                              <a href="#" class="track-action-btn track-quick-info" data-toggle="modal" data-target="#quickInfoModal">
                                <i class="fa fa-info-circle"></i> Quick Info
                              </a>
                              <a href="#" class="track-action-btn track-see-all" id="seeAllUpdatesBtn">
                                <i class="fa fa-history"></i> Full History
                              </a>
                            </div>
                        <?php endif; ?>
                    </div>
                  </div>
                  
                  <?php if ($has_delay && $delay_data): ?>
                  <!-- Delay Modal for this step -->
                  <div id="delayModal<?= $i ?>" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
                      <div class="modal-content" style="border-radius: 16px; border: none;">
                        <div class="modal-header" style="position:relative; border-bottom:2px solid #fff3cd; padding: 20px 24px; background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%); color: #333;">
                          <h5 class="modal-title" style="color: #333; font-weight: 700; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
                            <i class="fa fa-exclamation-triangle"></i>
                            Shipment Delayed
                          </h5>
                          <button type="button" class="close-modal" data-dismiss="modal" aria-label="Close" style="color: #333;">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body" style="padding: 28px;">
                          <div class="delay-content">
                            <div class="delay-info-box">
                              <h4 style="color: #232351; font-weight: 700; margin-bottom: 10px;">
                                <i class="fa fa-info-circle" style="color: #ffc107;"></i>
                                <?= htmlspecialchars($delay_data['delay_title'] ?? 'Delay Information') ?>
                              </h4>
                              <p style="color: #6c757d; font-size: 1.05rem; margin-bottom: 15px;">
                                <?= htmlspecialchars($delay_data['delay_desc'] ?? $delay_data['note'] ?? 'Shipment is experiencing delays.') ?>
                              </p>
                              <div class="delay-time" style="color: #8a91ab; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fa fa-clock"></i>
                                <span>Reported on: <?= $delay_data['date'] ?></span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php endif; ?>
                <?php endforeach; ?>
                <div class="track-timeline-fill"></div>
              </div>
            </div>
          </div>
          <!-- Shipment Details/Right Panel -->
          <div class="col-12 col-md-7 col-lg-5 mb-4">
            <div class="shipment-card">
              <div class="shipment-header d-flex d-md-none" id="shipmentToggle" style="cursor:pointer;align-items:center;justify-content:space-between;">
                <span class="shipment-title" style="margin:0;"><i class="fa fa-archive"></i> Shipment Details</span>
                <span id="shipmentArrow" style="font-size:1.5rem;transition:transform 0.25s;">&#9654;</span>
              </div>
              <div id="shipmentDetails" class="shipment-details d-md-none" style="display:none;">
                <table class="table table-borderless mb-0">
                  <tr><td>Shipment ID</td><td><?= $get_shipping_details_row['doc_no'] ?? '-' ?></td></tr>
                  <tr><td>Order Date</td><td><?= $pickup_date ?></td></tr>
                  <tr><td>Client Name</td><td><?= htmlspecialchars($client_name) ?></td></tr>
                  <tr><td>Delivery Agent</td><td><?= htmlspecialchars($delivery_agent) ?></td></tr>
                  <tr><td>Contact Number</td><td><?= htmlspecialchars($contact_number) ?></td></tr>
                  <tr><td>Car No.</td><td><?= htmlspecialchars($car_no) ?></td></tr>
                  <!-- POD row start -->
                  <?php
                  $status = $get_shipping_details_row['status'] ?? '';
                  $pod_file = $get_shipping_details_row['proof_of_delivery'] ?? '';
                  // Show POD row for "Delivered" or "Pending POD" status
                  if ($status === 'Delivered' || $status === 'Pending POD') {
                      echo '<tr><td>Proof of Delivery (POD)</td><td>';
                      if (empty($pod_file)) {
                          echo '<span class="btn btn-sm btn-warning" style="pointer-events:none;opacity:0.7;"><i class="fa fa-clock"></i> POD Pending</span>';
                      } else {
                          // POD path is relative to project root (uploads/pod/year/month/doc_no/filename.ext)
                          $pod_path = '/nsfs/' . $pod_file;
                          echo '<a href="' . htmlspecialchars($pod_path) . '" target="_blank" class="btn btn-sm btn-primary" style="margin-bottom:4px;"><i class="fa fa-eye"></i> View POD</a> ';
                          echo '<a href="' . htmlspecialchars($pod_path) . '" download class="btn btn-sm btn-success"><i class="fa fa-download"></i> Download</a>';
                      }
                      echo '</td></tr>';
                  }
                  ?>
                  <!-- POD row end -->
                </table>
              </div>
              <div class="shipment-title d-none d-md-flex" style="margin-bottom:16px;">
                <i class="fa fa-archive"></i> Shipment Details
              </div>
              <div class="shipment-details d-none d-md-block">
                <table class="table table-borderless mb-0">
                  <tr><td>Shipment ID</td><td><?= $get_shipping_details_row['doc_no'] ?? '-' ?></td></tr>
                  <tr><td>Order Date</td><td><?= $pickup_date ?></td></tr>
                  <tr><td>Client Name</td><td><?= htmlspecialchars($client_name) ?></td></tr>
                  <tr><td>Delivery Agent</td><td><?= htmlspecialchars($delivery_agent) ?></td></tr>
                  <tr><td>Contact Number</td><td><?= htmlspecialchars($contact_number) ?></td></tr>
                  <tr><td>Car No.</td><td><?= htmlspecialchars($car_no) ?></td></tr>
                  <!-- POD row start -->
                  <?php
                  $status = $get_shipping_details_row['status'] ?? '';
                  $pod_file = $get_shipping_details_row['proof_of_delivery'] ?? '';
                  // Show POD row for "Delivered" or "Pending POD" status
                  if ($status === 'Delivered' || $status === 'Pending POD') {
                      echo '<tr><td>Proof of Delivery (POD)</td><td>';
                      if (empty($pod_file)) {
                          echo '<span class="btn btn-sm btn-warning" style="pointer-events:none;opacity:0.7;"><i class="fa fa-clock"></i> POD Pending</span>';
                      } else {
                          // POD path is relative to project root (uploads/pod/year/month/doc_no/filename.ext)
                          $pod_path = '/nsfs/' . $pod_file;
                          echo '<a href="' . htmlspecialchars($pod_path) . '" target="_blank" class="btn btn-sm btn-primary" style="margin-bottom:4px;"><i class="fa fa-eye"></i> View POD</a> ';
                          echo '<a href="' . htmlspecialchars($pod_path) . '" download class="btn btn-sm btn-success"><i class="fa fa-download"></i> Download</a>';
                      }
                      echo '</td></tr>';
                  }
                  ?>
                  <!-- POD row end -->
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Info Modal -->
        <div id="quickInfoModal" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 550px;">
            <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">
              <div class="modal-header" style="position:relative; border-bottom:none; padding: 24px 28px 20px; background: linear-gradient(135deg, #5551c0 0%, #7b77e8 100%); color: #fff;">
                <h5 class="modal-title" style="color: #fff; font-weight: 700; font-size: 1.4rem; display: flex; align-items: center; gap: 12px;">
                  <i class="fa fa-shipping-fast"></i>
                  Current Status Details
                </h5>
                <button type="button" class="close-modal" data-dismiss="modal" aria-label="Close" style="color: #fff;">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body" style="padding: 28px;">
                <?php 
                $current_status = $get_shipping_details_row['status'] ?? 'Unknown';
                $current_location = $get_shipping_details_row['current_location'] ?? 'N/A';
                $estimated_delivery = $get_shipping_details_row['estimated_delivery'] ?? null;
                
                // Get current office info if available
                $current_office = '';
                $contact_person = $delivery_agent;
                $contact_phone = $contact_number;
                
                if (!empty($get_shipping_details_row['office_id'])) {
                    $office_query = "SELECT * FROM tbl_offices WHERE office_id='" . $get_shipping_details_row['office_id'] . "'";
                    $office_result = mysqli_query($conn, $office_query);
                    if ($office_data = mysqli_fetch_assoc($office_result)) {
                        $current_office = $office_data['office_name'];
                        $contact_person = $office_data['contact_person'] ?? $delivery_agent;
                        $contact_phone = $office_data['contact_number'] ?? $contact_number;
                    }
                }
                
                // Get last update from tracking history
                $last_update_text = '';
                $last_update_time = '';
                if (!empty($status_notes[$current_status])) {
                    $latest = end($status_notes[$current_status]);
                    $last_update_text = $latest['note'] ?? '';
                    $last_update_time = $latest['date'];
                }
                ?>
                
                <div class="quick-info-content">
                  <div class="status-badge-large">
                    <i class="fa fa-box" style="font-size: 2rem; color: #5551c0; margin-bottom: 10px;"></i>
                    <h4 style="color: #232351; font-weight: 700; margin: 0;"><?= htmlspecialchars($current_status) ?></h4>
                  </div>
                  
                  <div class="info-grid">
                    <div class="info-item">
                      <div class="info-icon"><i class="fa fa-map-marker-alt"></i></div>
                      <div class="info-content">
                        <div class="info-label">Current Location</div>
                        <div class="info-value"><?= htmlspecialchars($current_location) ?></div>
                      </div>
                    </div>
                    
                    <?php if ($current_office): ?>
                    <div class="info-item">
                      <div class="info-icon"><i class="fa fa-building"></i></div>
                      <div class="info-content">
                        <div class="info-label">At Office</div>
                        <div class="info-value"><?= htmlspecialchars($current_office) ?></div>
                      </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="info-item">
                      <div class="info-icon"><i class="fa fa-user"></i></div>
                      <div class="info-content">
                        <div class="info-label">Contact Person</div>
                        <div class="info-value"><?= htmlspecialchars($contact_person) ?></div>
                      </div>
                    </div>
                    
                    <div class="info-item">
                      <div class="info-icon"><i class="fa fa-phone"></i></div>
                      <div class="info-content">
                        <div class="info-label">Contact Number</div>
                        <div class="info-value">
                          <a href="tel:<?= htmlspecialchars($contact_phone) ?>" style="color: #5551c0; text-decoration: none; font-weight: 600;">
                            <?= htmlspecialchars($contact_phone) ?>
                          </a>
                        </div>
                      </div>
                    </div>
                    
                    <?php if ($estimated_delivery): ?>
                    <div class="info-item">
                      <div class="info-icon"><i class="fa fa-calendar-check"></i></div>
                      <div class="info-content">
                        <div class="info-label">Estimated Delivery</div>
                        <div class="info-value"><?= date('d M Y, h:i A', strtotime($estimated_delivery)) ?></div>
                      </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($last_update_text): ?>
                    <div class="info-item full-width">
                      <div class="info-icon"><i class="fa fa-sticky-note"></i></div>
                      <div class="info-content">
                        <div class="info-label">Latest Update</div>
                        <div class="info-value"><?= htmlspecialchars($last_update_text) ?></div>
                        <?php if ($last_update_time): ?>
                          <div class="info-time"><i class="fa fa-clock"></i> <?= $last_update_time ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                    <?php endif; ?>
                  </div>
                  
                  <div style="margin-top: 24px; text-align: center;">
                    <button type="button" class="btn btn-primary" style="padding: 10px 24px; border-radius: 10px; background: linear-gradient(135deg, #5551c0 0%, #7b77e8 100%); border: none; font-weight: 600;" data-dismiss="modal" data-toggle="modal" data-target="#allUpdatesModal">
                      <i class="fa fa-history"></i> View Full History
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal for All Updates -->
        <div id="allUpdatesModal" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
            <div class="modal-content" style="border-radius: 16px; border: none;">
              <div class="modal-header" style="position:relative; border-bottom:2px solid #f0f0f0; padding: 20px 24px; background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%);">
                <h5 class="modal-title" style="color: #232351; font-weight: 700; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;">
                  <i class="fa fa-history" style="color: #5551c0;"></i>
                  Complete Tracking History
                </h5>
                <button type="button" class="close-modal" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body" style="padding: 24px;">
                <div class="modal-timeline">
                  <?php foreach ($status_notes as $status => $notes): ?>
                    <div class="modal-step">
                      <div class="modal-step-label"><b><?= htmlspecialchars($status) ?></b></div>
                      <?php foreach ($notes as $n): ?>
                        <?php if ($n['note']) { ?>
                          <div class="modal-step-desc"><?= htmlspecialchars($n['note']) ?></div>
                        <?php } ?>
                        <div class="modal-step-time"><?= $n['date'] ?></div>
                      <?php endforeach; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
<?php
if (!$is_ajax) { ?>
      </div><!-- /trackingResultBox -->
    </div>
  </div>
</section>
<?php include("include/footer.php"); } ?>
<!-- ... keep your CSS and JS as before ... -->

<!-- (CSS and JS as you posted) -->



<style>
body { font-family: 'Inter', 'Segoe UI', Arial, sans-serif; background:#f5f8fd;}

/* Tracking Box */
.track-box {
    background: #fff;
    border-radius: 20px;
    padding: 32px 36px;
    min-width: 320px;
    box-shadow: 0 4px 20px rgba(85, 81, 192, 0.08);
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.track-title {
    color: #5551c0;
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 0px;
    letter-spacing: -.5px;
}

.track-date {
    font-size: 1.13rem;
    color: #3a4256;
    margin-bottom: 22px;
    margin-top: 8px;
    font-weight: 500;
}

/* Timeline Container */
.track-timeline {
    margin-top: 32px;
    margin-left: 12px;
    position: relative;
    padding-left: 60px;
}

/* Animated Progress Line */
.track-timeline-fill {
    position: absolute;
    left: 23px;
    top: 18px;
    width: 4px;
    background: linear-gradient(180deg, #5551c0 0%, #7b77e8 100%);
    border-radius: 3px;
    z-index: 0;
    transition: height 1.2s cubic-bezier(.42,0,.58,1);
    height: 0;
    box-shadow: 0 0 8px rgba(85, 81, 192, 0.3);
}

/* Background Line */
.track-timeline:before {
    content: '';
    position: absolute;
    left: 23px;
    top: 18px;
    bottom: 10px;
    width: 4px;
    background: #e3e6fc;
    z-index: 0;
    border-radius: 3px;
}

/* Timeline Step */
.track-step {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 45px;
    z-index: 1;
    opacity: 0;
    animation: slideInLeft 0.5s ease-out forwards;
}

.track-step:nth-child(1) { animation-delay: 0.1s; }
.track-step:nth-child(2) { animation-delay: 0.2s; }
.track-step:nth-child(3) { animation-delay: 0.3s; }
.track-step:nth-child(4) { animation-delay: 0.4s; }
.track-step:nth-child(5) { animation-delay: 0.5s; }
.track-step:nth-child(6) { animation-delay: 0.6s; }

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.track-step:last-child { margin-bottom: 0; }

/* Circle Icon */
.track-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 3px solid #d7d7e4;
    color: #999;
    background: #fff;
    font-weight: 600;
    text-align: center;
    line-height: 44px;
    font-size: 1.3rem;
    margin-top: 0px;
    z-index: 2;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 0 0 4px #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    position: relative;
}

/* Pending/Inactive State */
.track-step .track-circle {
    background: #f8f9fa;
    border-color: #d7d7e4;
    color: #aaa;
}

/* Completed State */
.track-step.done .track-circle {
    background: linear-gradient(135deg, #5551c0 0%, #7b77e8 100%);
    color: #fff;
    border-color: #5551c0;
    transform: scale(1.05);
    box-shadow: 0 0 0 4px #fff, 0 4px 12px rgba(85, 81, 192, 0.3);
}

/* Delayed State */
.track-step.delayed .track-circle {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: #fff;
    border-color: #ffc107;
    transform: scale(1.05);
    box-shadow: 0 0 0 4px #fff, 0 4px 12px rgba(255, 193, 7, 0.4);
    animation: delayPulse 2s infinite;
}

@keyframes delayPulse {
    0%, 100% {
        box-shadow: 0 0 0 4px #fff, 0 4px 12px rgba(255, 193, 7, 0.4);
    }
    50% {
        box-shadow: 0 0 0 4px #fff, 0 0 0 8px rgba(255, 193, 7, 0.2), 0 4px 16px rgba(255, 193, 7, 0.5);
    }
}

.delay-icon {
    animation: shake 0.5s infinite;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-3px); }
    75% { transform: translateX(3px); }
}

/* Active/Current State */
.track-step.active .track-circle {
    box-shadow: 0 0 0 4px #fff, 0 0 0 8px #e4e6fb, 0 4px 16px rgba(85, 81, 192, 0.4);
    background: #fff;
    color: #5551c0;
    border-color: #5551c0;
    border-width: 4px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 4px #fff, 0 0 0 8px #e4e6fb, 0 4px 16px rgba(85, 81, 192, 0.4);
    }
    50% {
        box-shadow: 0 0 0 4px #fff, 0 0 0 12px rgba(228, 230, 251, 0.5), 0 4px 20px rgba(85, 81, 192, 0.5);
    }
}

/* Checkmark Icon */
.checkmark {
    font-size: 1.6rem;
    line-height: 1;
    animation: checkmarkPop 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes checkmarkPop {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

/* Track Info */
.track-step .track-info {
    padding-top: 5px;
    flex: 1;
}

.track-label {
    font-weight: 700;
    color: #6c757d;
    font-size: 1.15rem;
    margin-bottom: 4px;
    transition: color 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.current-badge {
    background: linear-gradient(135deg, #5551c0 0%, #7b77e8 100%);
    color: #fff;
    font-size: 0.7rem;
    padding: 3px 10px;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    animation: badgePulse 2s infinite;
}

@keyframes badgePulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(85, 81, 192, 0.4);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(85, 81, 192, 0);
    }
}

.delay-badge {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: #333;
    font-size: 0.7rem;
    padding: 3px 10px;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.3s;
    animation: delayBadgePulse 2s infinite;
}

.delay-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
}

@keyframes delayBadgePulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(255, 193, 7, 0);
    }
}

.track-step.done .track-label,
.track-step.active .track-label {
    color: #232351;
}

.track-step.active .track-label {
    color: #5551c0;
    font-size: 1.2rem;
}

.track-desc {
    font-size: .98rem;
    color: #8a91ab;
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.track-desc i {
    font-size: 0.9rem;
    color: #5551c0;
}

.track-time {
    font-size: .95rem;
    color: #7c7d8a;
    margin-bottom: 1px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.track-time i {
    font-size: 0.85rem;
    color: #5551c0;
}

.track-action-btn {
    color: #5551c0;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.3s;
    background: #f0efff;
    border: 2px solid transparent;
}

.track-action-btn:hover {
    background: #5551c0;
    color: #fff;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(85, 81, 192, 0.3);
}

.track-quick-info {
    background: linear-gradient(135deg, #5551c0 0%, #7b77e8 100%);
    color: #fff;
}

.track-quick-info:hover {
    background: linear-gradient(135deg, #3d3a9b 0%, #5551c0 100%);
    color: #fff;
}

.track-see-all {
    background: #f0efff;
    color: #5551c0;
}

.track-see-all:hover {
    background: #5551c0;
    color: #fff;
}
/* Shipment Card */
.shipment-card {
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 4px 20px rgba(85, 81, 192, 0.08);
    padding: 24px 20px 26px 20px;
    animation: fadeInUp 0.6s ease-out 0.3s backwards;
}

/* Hide collapse arrow & toggle on big screens, show on small */
.shipment-header { display: flex; }

@media (min-width: 768px) {
  .shipment-header, #shipmentArrow, #shipmentDetails { display: none !important; }
  .shipment-title.d-md-flex { display: flex !important; }
  .shipment-details.d-md-block { display: block !important; }
}

@media (max-width: 767.98px) {
  .shipment-header { display: flex !important; }
  .shipment-title.d-md-flex, .shipment-details.d-md-block { display: none !important; }
  .shipment-details.d-md-none { display: none; }
}

.shipment-title {
    font-weight: 700;
    color: #232351;
    font-size: 1.2rem;
    margin-bottom: 20px;
    letter-spacing: -.3px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.shipment-title i {
    color: #5551c0;
    font-size: 1.3rem;
}

.shipment-card table {
    width: 100%;
}

.shipment-card td {
    padding: 12px 0;
    border-bottom: 1px solid #f2f4f7;
    color: #303055;
    font-size: 1rem;
    vertical-align: middle;
    transition: all 0.3s;
}

.shipment-card tr:hover td {
    background: #f8f9ff;
}

.shipment-card tr:last-child td {
    border-bottom: none;
}

.shipment-card td:first-child {
    font-weight: 600;
    color: #6f6bb1;
    width: 45%;
    padding-left: 8px;
}

.shipment-card td:last-child {
    text-align: right;
    color: #232351;
    font-weight: 500;
    padding-right: 8px;
}

/* POD Buttons */
.btn-sm {
    font-size: 0.875rem;
    padding: 6px 14px;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-primary {
    background: #5551c0;
    border-color: #5551c0;
}

.btn-primary:hover {
    background: #3d3a9b;
    border-color: #3d3a9b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(85, 81, 192, 0.3);
}

.btn-success {
    background: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background: #218838;
    border-color: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-warning {
    background: #ffc107;
    border-color: #ffc107;
    color: #856404;
}

/* Modal Styles */
.modal-timeline {
    margin: 0 4px;
}

.modal-step {
    display: flex;
    flex-direction: column;
    margin-bottom: 24px;
    border-left: 3px solid #e3e6fc;
    padding-left: 18px;
    position: relative;
    animation: fadeInLeft 0.4s ease-out;
}

@keyframes fadeInLeft {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.modal-step:before {
    content: "";
    position: absolute;
    left: -8px;
    top: 6px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #5551c0;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #5551c0;
}

.modal-step-label {
    font-weight: 700;
    color: #232351;
    font-size: 1.05rem;
    margin-bottom: 4px;
}

.modal-step-desc {
    font-size: .98rem;
    color: #8a91ab;
    margin-bottom: 4px;
}

.modal-step-time {
    font-size: .95rem;
    color: #7c7d8a;
    display: flex;
    align-items: center;
    gap: 6px;
}

.modal-step-time:before {
    content: "\f017";
    font-family: "Font Awesome 6 Free";
    font-weight: 400;
    color: #5551c0;
    font-size: 0.85rem;
}
/* Responsive Adjustments */
@media (max-width: 900px) {
    .track-box, .shipment-card { min-width: unset; width: 100%; }
    .track-title { font-size: 1.6rem;}
    .track-date { font-size: 1.05rem;}
    .row.justify-content-center.flex-wrap-reverse {
        flex-direction: column-reverse !important;
    }
    .shipment-card { margin-bottom: 24px; }
    .track-timeline { padding-left: 55px; }
    .track-circle { width: 46px; height: 46px; line-height: 40px; }
}

/* Search Bar */
.track-searchbar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 0;
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.track-searchbar input[type="text"] {
    border-radius: 10px;
    border: 2px solid #e3e6fc;
    font-size: 1.05rem;
    padding: 12px 18px;
    background: #fff;
    margin-right: 5px;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(85, 81, 192, 0.05);
}

.track-searchbar input[type="text"]:focus {
    outline: none;
    border-color: #5551c0;
    box-shadow: 0 0 0 4px rgba(85, 81, 192, 0.1);
}

.track-searchbar .btn {
    background: linear-gradient(135deg, #5551c0 0%, #7b77e8 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 12px 28px;
    font-size: 1.05rem;
    font-weight: 600;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(85, 81, 192, 0.2);
}

.track-searchbar .btn:hover {
    background: linear-gradient(135deg, #3d3a9b 0%, #5551c0 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(85, 81, 192, 0.3);
}

/* Modal Close Button */
.close-modal {
    position: absolute;
    right: 18px;
    top: 15px;
    background: transparent;
    border: none;
    font-size: 2rem;
    color: #646493;
    cursor: pointer;
    line-height: 1;
    opacity: .7;
    z-index: 2;
    transition: all 0.3s;
}

.close-modal:hover {
    color: #232351;
    opacity: 1;
    transform: rotate(90deg);
}

/* Error/Not Found State */
.track-box .fa-search {
    animation: searchPulse 2s infinite;
}

@keyframes searchPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* Loading Animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #5551c0;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}

/* Quick Info Modal Styles */
.quick-info-content {
    animation: fadeIn 0.5s ease-out;
}

.status-badge-large {
    text-align: center;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%);
    border-radius: 12px;
    margin-bottom: 24px;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 14px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s;
}

.info-item:hover {
    background: #f0efff;
    transform: translateX(5px);
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #5551c0 0%, #7b77e8 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.info-content {
    flex: 1;
}

.info-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 1.05rem;
    color: #232351;
    font-weight: 600;
}

.info-time {
    font-size: 0.85rem;
    color: #8a91ab;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
}

/* Delay Modal Styles */
.delay-content {
    animation: fadeIn 0.5s ease-out;
}

.delay-info-box {
    padding: 20px;
    background: #fff8e1;
    border-left: 4px solid #ffc107;
    border-radius: 10px;
}

.delay-time {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ffe082;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate the blue fill line to the last completed step
    function animateFillLine() {
        var timeline = document.querySelector('.track-timeline');
        var fillLine = document.querySelector('.track-timeline-fill');
        if (!timeline || !fillLine) return;
        var steps = timeline.querySelectorAll('.track-step.done .track-circle');
        if (steps.length > 0) {
            var first = steps[0].getBoundingClientRect().top + (steps[0].offsetHeight/2);
            var last = steps[steps.length-1].getBoundingClientRect().top + (steps[0].offsetHeight/2);
            var parentTop = timeline.getBoundingClientRect().top;
            fillLine.style.height = (last - parentTop - 18) + 'px';
        }
    }
    setTimeout(animateFillLine, 250);
    window.addEventListener('resize', animateFillLine);

    // AJAX tracking search
    var searchForm = document.getElementById('trackingSearchForm');
    if (searchForm) {
        searchForm.onsubmit = function(e){
            e.preventDefault();
            var doc_no = this.doc_no.value.trim();
            if(!doc_no) return false;
            var formData = new FormData();
            formData.append('ajax', '1');
            formData.append('doc_no', doc_no);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'deliveryHistory.php', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onreadystatechange = function(){
                if(xhr.readyState === 4 && xhr.status === 200){
                    document.getElementById('trackingResultBox').innerHTML = xhr.responseText;
                    if (history.pushState) {
                        var url = 'deliveryHistory.php?doc_no=' + encodeURIComponent(doc_no);
                        window.history.pushState({doc_no:doc_no}, '', url);
                    }
                    setTimeout(animateFillLine, 250);
                }
            };
            xhr.send(formData);
        };
    }

    // Modal popup for "See all updates"
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'seeAllUpdatesBtn') {
            e.preventDefault();
            $('#allUpdatesModal').modal('show');
        }
    });
    
    // Handle modal switching from Quick Info to Full History
    $('#quickInfoModal').on('hidden.bs.modal', function (e) {
        // Check if the button to open full history was clicked
        if ($(e.target).data('openFullHistory')) {
            $('#allUpdatesModal').modal('show');
            $(e.target).removeData('openFullHistory');
        }
    });
    
    // Handle the button click in Quick Info modal
    $(document).on('click', '#quickInfoModal [data-toggle="modal"][data-target="#allUpdatesModal"]', function(e) {
        e.preventDefault();
        $('#quickInfoModal').data('openFullHistory', true);
        $('#quickInfoModal').modal('hide');
        setTimeout(function() {
            $('#allUpdatesModal').modal('show');
        }, 300);
    });

    // Shipment Details Collapse/Expand for small screens
    var shipmentToggle = document.getElementById('shipmentToggle');
    var shipmentDetails = document.getElementById('shipmentDetails');
    var shipmentArrow = document.getElementById('shipmentArrow');
    // By default, collapsed
    if (shipmentToggle && shipmentDetails && shipmentArrow) {
      shipmentToggle.addEventListener('click', function() {
        var isOpen = shipmentDetails.style.display === 'block';
        shipmentDetails.style.display = isOpen ? 'none' : 'block';
        shipmentArrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(90deg)';
      });
    }
});
</script>
