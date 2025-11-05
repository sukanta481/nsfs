<?php
require 'top_header.php';
require 'conn.php';
require_once 'DocketDetailsManager.php';

$docket_id = intval($_REQUEST['id'] ?? $_REQUEST['docket_id'] ?? 0);

// Fetch docket details
$manager = new DocketDetailsManager($conn);
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

// Generate tracking URL for QR code
$tracking_url = "http://" . $_SERVER['HTTP_HOST'] . "/nsfs/track.php?doc_no=" . urlencode($data['doc_no']);
?>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <div class="right_col" role="main">
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

              <!-- Update Status Card -->
              <div class="detail-card">
                <div class="card-header">
                  <div class="header-left">
                    <i class="fa fa-refresh"></i>
                    <h3>Update Status</h3>
                  </div>
                </div>
                
                <div class="card-body">
                  <form id="statusUpdateForm" method="POST" action="update_docket_status.php">
                    <input type="hidden" name="docket_id" value="<?= $docket_id ?>">
                    
                    <div class="form-group">
                      <label>Status</label>
                      <select name="status" class="form-control-modern" required>
                        <option value="Pending" <?= $data['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Picked Up" <?= $data['status'] == 'Picked Up' ? 'selected' : '' ?>>Picked Up</option>
                        <option value="In Transit" <?= $data['status'] == 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                        <option value="Out for Delivery" <?= $data['status'] == 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                        <option value="Delivered" <?= $data['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="Delayed" <?= $data['status'] == 'Delayed' ? 'selected' : '' ?>>Delayed</option>
                        <option value="Cancelled" <?= $data['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                      </select>
                    </div>
                    
                    <button type="submit" class="action-button btn-update-status">
                      <i class="fa fa-check"></i> Update Status
                    </button>
                  </form>
                </div>
              </div>

              <!-- QR Code Card -->
              <div class="detail-card">
                <div class="card-header">
                  <div class="header-left">
                    <i class="fa fa-qrcode"></i>
                    <h3>Tracking QR Code</h3>
                  </div>
                </div>
                
                <div class="card-body qr-body">
                  <div class="qr-code-container">
                    <div id="qrcode"></div>
                  </div>
                  <div class="qr-info">
                    <p><strong>Scan to Track</strong></p>
                    <p class="qr-doc-no"><?= htmlspecialchars($data['doc_no']) ?></p>
                    <button onclick="downloadQR()" class="btn-download-qr">
                      <i class="fa fa-download"></i> Download QR
                    </button>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>
      
      <?php require 'footer.php';?>
    </div>
  </div>
</body>

<!-- Include QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// Generate QR Code
var qrcode = new QRCode(document.getElementById("qrcode"), {
    text: "<?= $tracking_url ?>",
    width: 200,
    height: 200,
    colorDark: "#2c3e50",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
});

// Download QR Code
function downloadQR() {
    const canvas = document.querySelector('#qrcode canvas');
    const url = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.download = 'QR_<?= $data['doc_no'] ?>.png';
    link.href = url;
    link.click();
}

// Confirm Delete
function confirmDelete(docketId) {
    if(confirm('Are you sure you want to delete this docket? This action cannot be undone.')) {
        window.location.href = 'action_handler.php?action=delete_docket&docket_id=' + docketId;
    }
}

// Status Update Form
document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('update_docket_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Status updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        console.error(error);
    });
});
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

/* QR Code */
.qr-body {
    text-align: center;
}

.qr-code-container {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

#qrcode {
    display: inline-block;
}

.qr-info {
    text-align: center;
}

.qr-info p {
    margin: 5px 0;
    color: #2c3e50;
}

.qr-doc-no {
    font-size: 1.2rem;
    font-weight: 700;
    color: #4a90e2;
}

.btn-download-qr {
    margin-top: 15px;
    padding: 10px 20px;
    background: #4a90e2;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-download-qr:hover {
    background: #357abd;
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
</style>
