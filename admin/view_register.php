<?php
// Check if this page is being included or accessed directly
$is_standalone = !isset($conn);

if ($is_standalone) {
    // Standalone access - include full headers
    require 'check_auth.php';
    requirePermission('docket_view');
    require 'conn.php';
}

// DocketDetailsManager.php is optional - we don't actually use it
// Just query directly instead
$docket_id = intval($_REQUEST['id'] ?? $_REQUEST['docket_id'] ?? 0);

// Fetch docket details - query directly without DocketDetailsManager
$sql = "SELECT dd.*, o.office_name
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        WHERE dd.docket_id = $docket_id";
$res = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    echo '<div class="alert alert-danger">Docket not found. <a href="register.php?type=list_register&lp=ac">Back to List</a></div>';
    exit;
}

// Fetch status history
$history_sql = "SELECT * FROM docket_status_history WHERE docket_id = $docket_id ORDER BY changed_at DESC";
$history_result = mysqli_query($conn, $history_sql);
$status_history = [];
if($history_result) {
    while($row = mysqli_fetch_assoc($history_result)) {
        $status_history[] = $row;
    }
}

// Get cars for dropdown (for enhanced status update)
$cars_query = "SELECT car_id, car_number, car_details FROM tbl_car WHERE active_status = 1 ORDER BY car_number";
$cars_result = mysqli_query($conn, $cars_query);

// Get drivers for dropdown (for enhanced status update)
$drivers_query = "SELECT staff_id, staff_name, staff_phone FROM tbl_staff WHERE staff_role = 'Driver' AND active_status = 1 ORDER BY staff_name";
$drivers_result = mysqli_query($conn, $drivers_query);

// Get delay reasons (for enhanced status update)
$delay_reasons_query = "SELECT reason_id, reason_text, reason_category FROM tbl_delay_reasons WHERE is_active = 1 ORDER BY reason_category, reason_text";
$delay_reasons_result = mysqli_query($conn, $delay_reasons_query);

// Generate tracking URL for QR code
$tracking_url = "http://" . $_SERVER['HTTP_HOST'] . "/nsfs/track.php?doc_no=" . urlencode($data['doc_no']);

// If standalone, include top_header and open body/layout
if ($is_standalone) {
    require 'top_header.php';
?>
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php'; ?>
      <?php require 'header_banner.php'; ?>
      <div class="right_col" role="main">
<?php
}
?>

<!-- View Docket Content -->
<div class="view-docket-container">
  
  <!-- Back Button -->
  <div class="back-button-section">
    <a href="register.php?type=list_register&lp=ac" class="btn-back">
      <i class="fa fa-arrow-left"></i> Back to List
    </a>
  </div>

  <div class="docket-view-grid">
            <!-- Left Column - Docket Details -->
            <div class="left-column">
              
              <!-- Main Docket Details Card -->
              <div class="detail-card">
                <div class="card-header">
                  <div class="header-left">
                    <i class="fa fa-file-text"></i>
                    <h3>Docket Details</h3>
                  </div>
                  <div class="status-badge-large status-<?= strtolower(str_replace(' ', '-', $data['status'])) ?>">
                    <?= htmlspecialchars($data['status']) ?>
                  </div>
                </div>
                
                <div class="card-body">
                  <!-- Tracking Number & Service Type -->
                  <div class="detail-row-grid">
                    <div class="detail-item">
                      <div class="detail-icon">
                        <i class="fa fa-barcode"></i>
                      </div>
                      <div class="detail-content">
                        <label>Tracking Number:</label>
                        <strong><?= htmlspecialchars($data['doc_no']) ?></strong>
                      </div>
                    </div>
                    
                    <div class="detail-item">
                      <div class="detail-icon">
                        <i class="fa fa-truck"></i>
                      </div>
                      <div class="detail-content">
                        <label>Service Type:</label>
                        <strong><?= htmlspecialchars($data['service_type'] ?? 'Standard') ?></strong>
                      </div>
                    </div>
                  </div>

                  <!-- Sender Information -->
                  <div class="section-divider">
                    <i class="fa fa-user"></i>
                    <span>Sender Information</span>
                  </div>
                  
                  <div class="info-grid">
                    <div class="info-item">
                      <label>Name:</label>
                      <span><?= htmlspecialchars($data['company_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                      <label>Phone:</label>
                      <span><?= htmlspecialchars($data['company_phone'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item full-width">
                      <label>Address:</label>
                      <span><?= htmlspecialchars($data['company_address'] ?? 'N/A') ?></span>
                    </div>
                  </div>

                  <!-- Receiver Information -->
                  <div class="section-divider">
                    <i class="fa fa-user"></i>
                    <span>Receiver Information</span>
                  </div>
                  
                  <div class="info-grid">
                    <div class="info-item">
                      <label>Name:</label>
                      <span><?= htmlspecialchars($data['client_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item">
                      <label>Phone:</label>
                      <span><?= htmlspecialchars($data['client_phone'] ?? 'N/A') ?></span>
                    </div>
                    <div class="info-item full-width">
                      <label>Address:</label>
                      <span><?= htmlspecialchars($data['client_address'] ?? 'N/A') ?></span>
                    </div>
                  </div>

                  <!-- Pickup & Delivery Address -->
                  <div class="detail-row-grid">
                    <div class="detail-item">
                      <div class="detail-icon">
                        <i class="fa fa-map-marker"></i>
                      </div>
                      <div class="detail-content">
                        <label>Pickup Address</label>
                        <strong><?= htmlspecialchars($data['pickup_location'] ?? 'N/A') ?></strong>
                      </div>
                    </div>
                    
                    <div class="detail-item">
                      <div class="detail-icon">
                        <i class="fa fa-map-marker"></i>
                      </div>
                      <div class="detail-content">
                        <label>Delivery Address</label>
                        <strong><?= htmlspecialchars($data['delivery_location'] ?? 'N/A') ?></strong>
                      </div>
                    </div>
                  </div>

                  <!-- Package Details -->
                  <div class="detail-row-grid triple">
                    <div class="detail-item">
                      <div class="detail-icon">
                        <i class="fa fa-balance-scale"></i>
                      </div>
                      <div class="detail-content">
                        <label>Weight:</label>
                        <strong><?= htmlspecialchars($data['weight']) ?> kg</strong>
                      </div>
                    </div>
                    
                    <div class="detail-item">
                      <div class="detail-icon">
                        <i class="fa fa-cube"></i>
                      </div>
                      <div class="detail-content">
                        <label>Dimensions:</label>
                        <strong><?= htmlspecialchars($data['dimensions'] ?? 'N/A') ?> cm</strong>
                      </div>
                    </div>

                    <div class="detail-item">
                      <div class="detail-icon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <div class="detail-content">
                        <label>Created:</label>
                        <strong><?= date('M d, Y g:i A', strtotime($data['created_at'])) ?></strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Status History Card -->
              <div class="detail-card">
                <div class="card-header">
                  <div class="header-left">
                    <i class="fa fa-history"></i>
                    <h3>Status History</h3>
                  </div>
                </div>
                
                <div class="card-body">
                  <div class="timeline">
                    <?php if(!empty($status_history)): ?>
                      <?php foreach($status_history as $history): ?>
                        <div class="timeline-item">
                          <div class="timeline-badge status-<?= strtolower(str_replace(' ', '-', $history['new_status'])) ?>">
                            <?= htmlspecialchars($history['new_status']) ?>
                          </div>
                          <div class="timeline-content">
                            <div class="timeline-text"><?= htmlspecialchars($history['notes'] ?? 'Status updated') ?></div>
                            <div class="timeline-date"><?= date('M d, Y g:i A', strtotime($history['changed_at'])) ?></div>

                            <?php if ($history['new_status'] === 'Delivered' || $history['new_status'] === 'Pending POD'): ?>
                              <div class="timeline-pod" style="margin-top: 10px;">
                                <?php if (!empty($history['pod_file'])): ?>
                                  <a href="../<?= htmlspecialchars($history['pod_file']) ?>" target="_blank" class="btn-view-pod">
                                    <i class="fa fa-file-image-o"></i> View POD
                                  </a>
                                <?php else: ?>
                                  <span style="color: #ff9800; font-size: 13px;">
                                    <i class="fa fa-clock"></i> POD Pending
                                  </span>
                                <?php endif; ?>
                              </div>
                            <?php endif; ?>

                            <?php if ($history['new_status'] === 'Out for Delivery' && (!empty($history['car_number']) || !empty($history['driver_name']))): ?>
                              <div class="timeline-vehicle" style="margin-top: 8px; font-size: 13px; color: #7f8c8d;">
                                <?php if (!empty($history['car_number'])): ?>
                                  <i class="fa fa-car"></i> <?= htmlspecialchars($history['car_number']) ?>
                                <?php endif; ?>
                                <?php if (!empty($history['driver_name'])): ?>
                                  &nbsp;&nbsp;<i class="fa fa-user"></i> <?= htmlspecialchars($history['driver_name']) ?>
                                <?php endif; ?>
                              </div>
                            <?php endif; ?>

                            <?php if ($history['new_status'] === 'Delayed' && !empty($history['delay_reason'])): ?>
                              <div class="timeline-delay" style="margin-top: 8px; font-size: 13px; color: #856404; background: #fff3cd; padding: 5px 10px; border-radius: 5px; display: inline-block;">
                                <i class="fa fa-exclamation-triangle"></i> <?= htmlspecialchars($history['delay_reason']) ?>
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <div class="timeline-item">
                        <div class="timeline-badge status-pending">
                          <?= htmlspecialchars($data['status']) ?>
                        </div>
                        <div class="timeline-content">
                          <div class="timeline-text">Docket created</div>
                          <div class="timeline-date"><?= date('M d, Y g:i A', strtotime($data['created_at'])) ?></div>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

            </div>

            <!-- Right Column - Actions & QR Code -->
            <div class="right-column">
              
              <!-- Actions Card -->
              <div class="detail-card actions-card">
                <div class="card-header">
                  <div class="header-left">
                    <i class="fa fa-cog"></i>
                    <h3>Actions</h3>
                  </div>
                </div>
                
                <div class="card-body">
                  <a href="edit_register_new.php?docket_id=<?= $docket_id ?>" class="action-button btn-edit-full">
                    <i class="fa fa-edit"></i> Edit Docket
                  </a>
                  
                  <button onclick="confirmDelete(<?= $docket_id ?>)" class="action-button btn-delete-full">
                    <i class="fa fa-trash"></i> Delete Docket
                  </button>
                  
                  <a href="download_docket.php?docket_id=<?= $docket_id ?>" class="action-button btn-download-full" target="_blank">
                    <i class="fa fa-download"></i> Download PDF
                  </a>
                </div>
              </div>

              <!-- Update Status Card - Enhanced -->
              <div class="detail-card">
                <div class="card-header">
                  <div class="header-left">
                    <i class="fa fa-refresh"></i>
                    <h3>Update Status</h3>
                  </div>
                </div>

                <div class="card-body">
                  <form id="statusUpdateForm" method="POST" action="update_docket_status.php" enctype="multipart/form-data">
                    <input type="hidden" name="docket_id" value="<?= $docket_id ?>">
                    <input type="hidden" name="current_status" value="<?= htmlspecialchars($data['status']) ?>">
                    <input type="hidden" name="doc_no" value="<?= htmlspecialchars($data['doc_no']) ?>">

                    <!-- Error Message -->
                    <div id="statusErrorMessage" style="display: none; background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; border-left: 4px solid #dc3545; margin-bottom: 15px;">
                      <i class="fa fa-exclamation-circle"></i>
                      <span id="statusErrorText"></span>
                    </div>

                    <div class="form-group">
                      <label>
                        <i class="fa fa-flag"></i> Status <span style="color: #dc3545;">*</span>
                      </label>
                      <select name="status" id="statusSelect" class="form-control-modern" required onchange="handleStatusChange()" data-current-status="<?= htmlspecialchars($data['status'] ?? '') ?>">
                        <option value="">-- Select Status --</option>
                        <option value="Pending" data-order="1">Pending</option>
                        <option value="Confirmed" data-order="2">Confirmed</option>
                        <option value="Picked Up" data-order="3">Picked Up</option>
                        <option value="In Transit" data-order="4">In Transit</option>
                        <option value="Delayed" data-order="4" data-allow-anytime="true">Delayed</option>
                        <option value="Out for Delivery" data-order="5">Out for Delivery</option>
                        <option value="Delivered" data-order="6" data-final="true">Delivered</option>
                        <option value="Failed" data-order="6" data-final="true">Failed Delivery</option>
                        <option value="Cancelled" data-order="6" data-final="true">Cancelled</option>
                      </select>
                      <small style="color: #7f8c8d;">Current: <strong><?= htmlspecialchars($data['status'] ?? 'Not Set') ?></strong></small>
                    </div>

                    <!-- Conditional: Date Field (for Out for Delivery, Delivered, Delayed) -->
                    <div id="dateField" class="conditional-field" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                      <div class="form-group">
                        <label>
                          <i class="fa fa-calendar"></i> <span id="dateLabelText">Date</span> <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="datetime-local" name="status_date" class="form-control-modern" value="<?= date('Y-m-d\TH:i') ?>">
                      </div>
                    </div>

                    <!-- Conditional: Car and Driver Fields (for Out for Delivery) -->
                    <div id="carDriverField" class="conditional-field" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                      <div class="form-group">
                        <label>
                          <i class="fa fa-car"></i> Vehicle Number <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="text" name="car_number" id="carNumberInput" class="form-control-modern" list="carList" placeholder="Type or select vehicle number" autocomplete="off">
                        <datalist id="carList">
                          <?php
                          if ($cars_result) {
                              mysqli_data_seek($cars_result, 0);
                              while ($car = mysqli_fetch_assoc($cars_result)):
                          ?>
                            <option value="<?= htmlspecialchars($car['car_number']) ?>" data-id="<?= $car['car_id'] ?>" data-details="<?= htmlspecialchars($car['car_details']) ?>">
                              <?= htmlspecialchars($car['car_number'] . ' - ' . $car['car_details']) ?>
                            </option>
                          <?php
                              endwhile;
                          }
                          ?>
                        </datalist>
                        <input type="hidden" name="car_id" id="carIdHidden">
                        <small style="color: #7f8c8d;">Type manually for external vehicles or select from dropdown</small>
                      </div>

                      <div class="form-group">
                        <label>
                          <i class="fa fa-user"></i> Driver Name <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="text" name="driver_name" id="driverNameInput" class="form-control-modern" list="driverList" placeholder="Type or select driver name" autocomplete="off">
                        <datalist id="driverList">
                          <?php
                          if ($drivers_result) {
                              mysqli_data_seek($drivers_result, 0);
                              while ($driver = mysqli_fetch_assoc($drivers_result)):
                          ?>
                            <option value="<?= htmlspecialchars($driver['staff_name']) ?>" data-id="<?= $driver['staff_id'] ?>" data-phone="<?= htmlspecialchars($driver['staff_phone']) ?>">
                              <?= htmlspecialchars($driver['staff_name'] . ' - ' . $driver['staff_phone']) ?>
                            </option>
                          <?php
                              endwhile;
                          }
                          ?>
                        </datalist>
                        <input type="hidden" name="driver_id" id="driverIdHidden">
                        <small style="color: #7f8c8d;">Type manually for external drivers or select from dropdown</small>
                      </div>
                    </div>

                    <!-- Conditional: Delay Reason (for Delayed) -->
                    <div id="delayReasonField" class="conditional-field" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                      <div class="form-group">
                        <label>
                          <i class="fa fa-exclamation-triangle"></i> Delay Reason <span style="color: #dc3545;">*</span>
                        </label>
                        <select name="delay_reason" class="form-control-modern">
                          <option value="">-- Select Delay Reason --</option>
                          <?php
                          if ($delay_reasons_result) {
                              $current_category = '';
                              mysqli_data_seek($delay_reasons_result, 0);
                              while ($reason = mysqli_fetch_assoc($delay_reasons_result)):
                                if ($current_category != $reason['reason_category']) {
                                  if ($current_category != '') echo '</optgroup>';
                                  echo '<optgroup label="' . htmlspecialchars($reason['reason_category']) . '">';
                                  $current_category = $reason['reason_category'];
                                }
                          ?>
                            <option value="<?= htmlspecialchars($reason['reason_text']) ?>">
                              <?= htmlspecialchars($reason['reason_text']) ?>
                            </option>
                          <?php
                              endwhile;
                              if ($current_category != '') echo '</optgroup>';
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <!-- Conditional: POD Upload (for Delivered) -->
                    <div id="podField" class="conditional-field" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                      <div class="form-group">
                        <label>
                          <i class="fa fa-file-image-o"></i> Proof of Delivery (POD) <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="file" name="pod_file" accept=".jpg,.jpeg,.png,.pdf" class="form-control-modern">
                        <small style="color: #7f8c8d;">Accepted: JPG, PNG, PDF (Max 5MB)</small>
                      </div>
                    </div>

                    <div class="form-group">
                      <label>
                        <i class="fa fa-map-marker"></i> Current Location (Optional)
                      </label>
                      <input type="text" name="location" class="form-control-modern" placeholder="e.g., Mumbai Warehouse, Delhi Hub">
                    </div>

                    <div class="form-group">
                      <label>
                        <i class="fa fa-comment"></i> Remarks (Optional)
                      </label>
                      <textarea name="remarks" rows="3" class="form-control-modern" placeholder="Add any notes or comments..."></textarea>
                    </div>

                    <button type="submit" name="update_status" class="action-button btn-update-status">
                      <i class="fa fa-check"></i> Update Status
                    </button>
                  </form>
                </div>
              </div>

<style>
.conditional-field {
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #34495e;
  margin-bottom: 8px;
  font-size: 14px;
}

.form-group label i {
  margin-right: 5px;
  color: #667eea;
}

.form-control-modern {
  width: 100%;
  padding: 10px 12px;
  border: 2px solid #e1e8ed;
  border-radius: 6px;
  font-size: 14px;
  transition: all 0.3s;
}

.form-control-modern:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control-modern option {
  padding: 10px;
}

textarea.form-control-modern {
  resize: vertical;
  font-family: inherit;
}
</style>

<script>
// Check for error in URL and show it
window.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const error = urlParams.get('error');
  if (error) {
    showError(decodeURIComponent(error));
    // Clean URL without reloading
    const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]error=[^&]*/, '').replace(/^&/, '?');
    window.history.replaceState({}, document.title, cleanUrl);
  }

  // Disable previous status options
  disablePreviousStatusOptions();
});

function disablePreviousStatusOptions() {
  const statusSelect = document.getElementById('statusSelect');
  const currentStatus = statusSelect.getAttribute('data-current-status');

  if (!currentStatus) return;

  // Find current status option
  let currentOrder = 0;
  const options = statusSelect.querySelectorAll('option');

  options.forEach(option => {
    if (option.value === currentStatus) {
      currentOrder = parseInt(option.getAttribute('data-order') || 0);
    }
  });

  // Disable previous options
  options.forEach(option => {
    // Skip empty option
    if (!option.value) return;

    const optionOrder = parseInt(option.getAttribute('data-order') || 0);
    const allowAnytime = option.getAttribute('data-allow-anytime') === 'true';
    const isFinal = option.getAttribute('data-final') === 'true';

    if (optionOrder < currentOrder && !allowAnytime) {
      option.disabled = true;
      option.style.color = '#ccc';
      // Only add (unavailable) if not already there
      if (!option.textContent.includes('(unavailable)')) {
        option.textContent = option.textContent + ' (unavailable)';
      }
    }
  });
}

function hideAllConditionalFields() {
  document.getElementById('dateField').style.display = 'none';
  document.getElementById('carDriverField').style.display = 'none';
  document.getElementById('delayReasonField').style.display = 'none';
  document.getElementById('podField').style.display = 'none';
  // Hide error message when changing status
  document.getElementById('statusErrorMessage').style.display = 'none';
}

function showError(message) {
  const errorDiv = document.getElementById('statusErrorMessage');
  const errorText = document.getElementById('statusErrorText');
  errorText.textContent = message;
  errorDiv.style.display = 'block';
  errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

  // Auto hide after 5 seconds
  setTimeout(() => {
    errorDiv.style.display = 'none';
  }, 5000);
}

function handleStatusChange() {
  const status = document.getElementById('statusSelect').value;
  hideAllConditionalFields();

  if (status === 'Out for Delivery') {
    document.getElementById('dateLabelText').textContent = 'Out for Delivery Date';
    document.getElementById('dateField').style.display = 'block';
    document.getElementById('carDriverField').style.display = 'block';
  } else if (status === 'Delivered') {
    document.getElementById('dateLabelText').textContent = 'Delivery Date';
    document.getElementById('dateField').style.display = 'block';
    document.getElementById('podField').style.display = 'block';
  } else if (status === 'Delayed') {
    document.getElementById('dateLabelText').textContent = 'Delay Date';
    document.getElementById('dateField').style.display = 'block';
    document.getElementById('delayReasonField').style.display = 'block';
  }
}

// Auto-fill car_id and driver_id when selecting from datalist
document.getElementById('carNumberInput').addEventListener('input', function() {
  const value = this.value;
  const options = document.querySelectorAll('#carList option');
  const hiddenInput = document.getElementById('carIdHidden');

  let matched = false;
  options.forEach(option => {
    if (option.value === value) {
      hiddenInput.value = option.getAttribute('data-id') || '';
      matched = true;
    }
  });

  if (!matched) {
    hiddenInput.value = ''; // Manual input - no ID
  }
});

document.getElementById('driverNameInput').addEventListener('input', function() {
  const value = this.value;
  const options = document.querySelectorAll('#driverList option');
  const hiddenInput = document.getElementById('driverIdHidden');

  let matched = false;
  options.forEach(option => {
    if (option.value === value) {
      hiddenInput.value = option.getAttribute('data-id') || '';
      matched = true;
    }
  });

  if (!matched) {
    hiddenInput.value = ''; // Manual input - no ID
  }
});

// Form validation
document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
  const status = document.getElementById('statusSelect').value;

  if (!status) {
    e.preventDefault();
    showError('Please select a status');
    return false;
  }

  // Validate Out for Delivery requirements
  if (status === 'Out for Delivery') {
    const carNumber = document.querySelector('[name="car_number"]').value;
    const driverName = document.querySelector('[name="driver_name"]').value;
    if (!carNumber || !driverName) {
      e.preventDefault();
      showError('Vehicle number and Driver name are required for Out for Delivery status');
      return false;
    }
  }

  // Validate Delayed requirements
  if (status === 'Delayed') {
    const delayReason = document.querySelector('[name="delay_reason"]').value;
    if (!delayReason) {
      e.preventDefault();
      showError('Delay reason is required for Delayed status');
      return false;
    }
  }

  // Validate Delivered requirements
  if (status === 'Delivered') {
    const podFile = document.querySelector('[name="pod_file"]').files.length;
    if (!podFile) {
      e.preventDefault();
      showError('POD file is required for Delivered status');
      return false;
    }
  }
});
</script>

              <!-- Barcode Card -->
              <div class="detail-card">
                <div class="card-header">
                  <div class="header-left">
                    <i class="fa fa-barcode"></i>
                    <h3>Tracking Barcode</h3>
                  </div>
                </div>
                
                <div class="card-body barcode-body">
                  <div class="barcode-container">
                    <svg id="barcode"></svg>
                  </div>
                  <div class="barcode-info">
                    <p><strong>Scan to Track</strong></p>
                    <p class="barcode-doc-no"><?= htmlspecialchars($data['doc_no']) ?></p>
                    <div class="barcode-buttons">
                      <button onclick="printSticker()" class="btn-print-sticker">
                        <i class="fa fa-print"></i> Print Sticker
                      </button>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>

<!-- Include Barcode Library -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<script>
// Generate Barcode
JsBarcode("#barcode", "<?= $data['doc_no'] ?>", {
    format: "CODE128",
    width: 2,
    height: 60,
    displayValue: true,
    fontSize: 14,
    margin: 10,
    background: "#f8f9fa"
});

// Print Sticker
function printSticker() {
    const stickerData = {
        doc_no: "<?= htmlspecialchars($data['doc_no']) ?>",
        invoice_no: "<?= htmlspecialchars($data['invoice_no'] ?? $data['doc_no']) ?>",
        origin: "<?= htmlspecialchars($data['pickup_location'] ?? '-') ?>",
        destination: "<?= htmlspecialchars($data['delivery_location'] ?? '-') ?>",
        consignee: "<?= htmlspecialchars($data['client_name'] ?? '-') ?>",
        service_type: "<?= htmlspecialchars($data['service_type'] ?? 'SURFACE-NORMAL') ?>",
        box: "<?= htmlspecialchars($data['box'] ?? '1') ?>"
    };
    
    const stickerWindow = window.open('', '_blank', 'width=450,height=400');
    stickerWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Sticker - ${stickerData.doc_no}</title>
            <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
            <style>
                @media print {
                    @page { size: 100mm 70mm; margin: 2mm; }
                    body { margin: 0; padding: 0; }
                    .no-print { display: none !important; }
                }
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; font-size: 11px; padding: 5px; }
                .sticker-container {
                    width: 100mm;
                    border: 1px solid #000;
                    padding: 5px;
                    background: #fff;
                }
                .sticker-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 1px solid #000;
                    padding-bottom: 3px;
                    margin-bottom: 5px;
                }
                .company-name { font-weight: bold; font-size: 10px; }
                .service-type { font-weight: bold; font-size: 11px; }
                .sticker-table { width: 100%; border-collapse: collapse; }
                .sticker-table td { padding: 3px 5px; border-bottom: 1px solid #ccc; font-size: 10px; }
                .sticker-table td:first-child { font-weight: bold; width: 80px; }
                .barcode-area { text-align: center; padding: 8px 0; border-top: 1px solid #000; margin-top: 5px; }
                .barcode-area svg { max-width: 100%; }
                .print-btn { display: block; margin: 15px auto; padding: 10px 30px; background: #4CAF50; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
                .print-btn:hover { background: #45a049; }
            </style>
        </head>
        <body>
            <div class="sticker-container">
                <div class="sticker-header">
                    <span class="company-name">NSFS</span>
                    <span class="service-type">${stickerData.service_type}</span>
                </div>
                <table class="sticker-table">
                    <tr><td>Invoice #:</td><td>${stickerData.invoice_no}</td></tr>
                    <tr><td>Ref #:</td><td>${stickerData.doc_no}</td></tr>
                    <tr><td>Package Ref#:</td><td></td></tr>
                    <tr><td>Package:</td><td>${stickerData.box} of ${stickerData.box}</td></tr>
                    <tr><td>GCN:</td><td>${stickerData.doc_no}</td></tr>
                    <tr><td>Origin:</td><td>${stickerData.origin}</td></tr>
                    <tr><td>Destination:</td><td>${stickerData.destination}</td></tr>
                    <tr><td>Consignee:</td><td>${stickerData.consignee}</td></tr>
                </table>
                <div class="barcode-area">
                    <svg id="sticker-barcode"></svg>
                </div>
            </div>
            <button class="print-btn no-print" onclick="window.print()">Print Sticker</button>
            <script>
                JsBarcode("#sticker-barcode", "${stickerData.doc_no}", {
                    format: "CODE128",
                    width: 2,
                    height: 40,
                    displayValue: true,
                    fontSize: 12,
                    margin: 5
                });
            <\/script>
        </body>
        </html>
    `);
    stickerWindow.document.close();
}

// Confirm Delete
function confirmDelete(docketId) {
    if(confirm('Are you sure you want to delete this docket? This action cannot be undone.')) {
        window.location.href = 'action_handler.php?action=delete_docket&docket_id=' + docketId;
    }
}

// Status Update Form - Remove old AJAX handler
// Form now submits normally and backend handles redirect with error/success messages
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.view-docket-container {
    font-family: 'Inter', sans-serif;
    padding: 20px;
    background: #ecf0f1;
    min-height: calc(100vh - 100px);
}

.back-button-section {
    margin-bottom: 20px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #fff;
    color: #2c3e50;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.btn-back:hover {
    background: #2c3e50;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.docket-view-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 20px;
}

.detail-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #fff;
}

.header-left i {
    font-size: 1.5rem;
}

.header-left h3 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 700;
}

.status-badge-large {
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-picked-up { background: #cce5ff; color: #004085; }
.status-in-transit { background: #d1ecf1; color: #0c5460; }
.status-out-for-delivery { background: #d4edda; color: #155724; }
.status-delivered { background: #d4edda; color: #155724; }
.status-delayed { background: #f8d7da; color: #721c24; }
.status-cancelled { background: #e2e3e5; color: #383d41; }

.card-body {
    padding: 25px;
}

.detail-row-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.detail-row-grid.triple {
    grid-template-columns: repeat(3, 1fr);
}

.detail-item {
    display: flex;
    gap: 15px;
    align-items: flex-start;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #4a90e2;
}

.detail-icon {
    background: #4a90e2;
    color: #fff;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.detail-content {
    flex: 1;
}

.detail-content label {
    display: block;
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-content strong {
    font-size: 1.1rem;
    color: #2c3e50;
    font-weight: 700;
}

.section-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 25px 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
    color: #2c3e50;
    font-weight: 700;
    font-size: 1.1rem;
}

.section-divider i {
    color: #4a90e2;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
}

.info-item span {
    font-size: 1rem;
    color: #2c3e50;
    font-weight: 500;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 0;
}

.timeline-item {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 50px;
    top: 40px;
    bottom: -20px;
    width: 2px;
    background: #e9ecef;
}

.timeline-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-align: center;
    min-width: 100px;
    height: fit-content;
    flex-shrink: 0;
}

.timeline-content {
    flex: 1;
    padding: 10px 0;
}

.timeline-text {
    font-size: 1rem;
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 5px;
}

.timeline-date {
    font-size: 0.85rem;
    color: #6c757d;
}

/* Actions Card */
.actions-card .card-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
}

.action-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 12px;
    text-decoration: none;
}

.btn-edit-full {
    background: #3498db;
    color: #fff;
}

.btn-edit-full:hover {
    background: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52,152,219,0.3);
}

.btn-delete-full {
    background: #e74c3c;
    color: #fff;
}

.btn-delete-full:hover {
    background: #c0392b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231,76,60,0.3);
}

.btn-download-full {
    background: #27ae60;
    color: #fff;
}

.btn-download-full:hover {
    background: #229954;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(39,174,96,0.3);
}

/* Status Update Form */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
}

.form-control-modern {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s;
}

.form-control-modern:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74,144,226,0.1);
}

.btn-update-status {
    background: #27ae60;
    color: #fff;
}

.btn-update-status:hover {
    background: #229954;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(39,174,96,0.3);
}

/* Barcode */
.barcode-body {
    text-align: center;
}

.barcode-container {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

#barcode {
    max-width: 100%;
}

.barcode-info {
    text-align: center;
}

.barcode-info p {
    margin: 5px 0;
    color: #2c3e50;
}

.barcode-doc-no {
    font-size: 1.2rem;
    font-weight: 700;
    color: #4a90e2;
}

.barcode-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 15px;
}

.btn-print-sticker {
    padding: 10px 20px;
    background: #27ae60;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-print-sticker:hover {
    background: #229954;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 1200px) {
    .docket-view-grid {
        grid-template-columns: 1fr;
    }
    
    .detail-row-grid.triple {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .detail-row-grid,
    .detail-row-grid.triple,
    .info-grid {
        grid-template-columns: 1fr;
    }
}

/* POD View Button */
.btn-view-pod {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
}

.btn-view-pod:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    color: white;
    text-decoration: none;
}

.btn-view-pod i {
    font-size: 14px;
}

/* ========================================
   RESPONSIVE DESIGN
   ======================================== */

/* Tablets and smaller (max-width: 1024px) */
@media (max-width: 1024px) {
    .docket-view-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .view-docket-container {
        padding: 15px;
    }

    .card-header {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }

    .status-badge-large {
        align-self: flex-start;
    }

    .detail-row-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .detail-row-grid.triple {
        grid-template-columns: 1fr;
    }
}

/* Mobile devices (max-width: 768px) */
@media (max-width: 768px) {
    .view-docket-container {
        padding: 12px;
    }

    .btn-back {
        padding: 10px 18px;
        font-size: 14px;
    }

    .card-header {
        padding: 15px 18px;
    }

    .header-left h3 {
        font-size: 1.1rem;
    }

    .header-left i {
        font-size: 1.2rem;
    }

    .status-badge-large {
        padding: 6px 16px;
        font-size: 0.8rem;
    }

    .card-body {
        padding: 18px;
    }

    .detail-item {
        padding: 12px;
        gap: 12px;
    }

    .detail-icon {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }

    .detail-content strong {
        font-size: 1rem;
    }

    .info-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .section-divider {
        font-size: 1rem;
        margin: 20px 0 15px 0;
    }

    .action-button {
        padding: 12px;
        font-size: 0.95rem;
        margin-bottom: 10px;
    }

    .timeline-item {
        flex-direction: column;
        gap: 10px;
    }

    .timeline-item:not(:last-child)::after {
        display: none;
    }

    .timeline-badge {
        min-width: auto;
        width: fit-content;
    }

    .qr-section {
        padding: 15px;
    }

    .qr-code-container {
        width: 180px;
        height: 180px;
    }
}

/* Small mobile devices (max-width: 576px) */
@media (max-width: 576px) {
    .view-docket-container {
        padding: 10px;
        min-height: auto;
    }

    .back-button-section {
        margin-bottom: 15px;
    }

    .btn-back {
        padding: 8px 15px;
        font-size: 13px;
        width: 100%;
        justify-content: center;
    }

    .detail-card {
        border-radius: 12px;
        margin-bottom: 15px;
    }

    .card-header {
        padding: 12px 15px;
    }

    .header-left h3 {
        font-size: 1rem;
    }

    .header-left i {
        font-size: 1.1rem;
    }

    .status-badge-large {
        padding: 5px 12px;
        font-size: 0.75rem;
    }

    .card-body {
        padding: 15px;
    }

    .detail-item {
        padding: 10px;
        gap: 10px;
        flex-direction: column;
        text-align: center;
    }

    .detail-icon {
        width: 32px;
        height: 32px;
        font-size: 0.9rem;
        align-self: center;
    }

    .detail-content {
        text-align: center;
    }

    .detail-content label {
        font-size: 0.8rem;
    }

    .detail-content strong {
        font-size: 0.95rem;
    }

    .section-divider {
        font-size: 0.95rem;
        margin: 15px 0 12px 0;
    }

    .info-item label {
        font-size: 0.8rem;
    }

    .info-item span {
        font-size: 0.9rem;
    }

    .action-button {
        padding: 10px;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .timeline-badge {
        font-size: 0.7rem;
        padding: 6px 12px;
    }

    .timeline-text {
        font-size: 0.9rem;
    }

    .timeline-date {
        font-size: 0.8rem;
    }

    .barcode-section {
        padding: 12px;
    }

    .barcode-container {
        padding: 15px;
    }

    .barcode-info p {
        font-size: 0.9rem;
    }

    .tracking-link {
        font-size: 0.8rem;
        word-break: break-all;
    }

    .btn-print-sticker {
        padding: 8px 15px;
        font-size: 0.85rem;
    }
}

/* Extra small devices (max-width: 400px) */
@media (max-width: 400px) {
    .view-docket-container {
        padding: 8px;
    }

    .btn-back {
        font-size: 12px;
        padding: 8px 12px;
    }

    .card-header {
        padding: 10px 12px;
    }

    .header-left h3 {
        font-size: 0.95rem;
    }

    .status-badge-large {
        font-size: 0.7rem;
        padding: 4px 10px;
    }

    .card-body {
        padding: 12px;
    }

    .detail-item {
        padding: 8px;
    }

    .detail-icon {
        width: 28px;
        height: 28px;
        font-size: 0.85rem;
    }

    .detail-content strong {
        font-size: 0.9rem;
    }

    .section-divider {
        font-size: 0.9rem;
    }

    .action-button {
        padding: 8px;
        font-size: 0.85rem;
    }

    .barcode-container {
        padding: 12px;
    }

    .btn-view-pod {
        padding: 8px 12px;
        font-size: 0.85rem;
    }
}
</style>

<?php
// If standalone, close the layout divs and include footer
if ($is_standalone) {
?>
      </div><!-- /right_col -->
      <?php require 'footer.php'; ?>
    </div><!-- /main_container -->
  </div><!-- /container body -->
</body>
</html>
<?php
}
?>
