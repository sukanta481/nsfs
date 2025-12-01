<?php
require 'admin/conn.php';

$doc_no = isset($_GET['doc_no']) ? mysqli_real_escape_string($conn, trim($_GET['doc_no'])) : '';

// Handle AJAX requests
$is_ajax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ||
    (isset($_POST['ajax']) && $_POST['ajax'] == '1')
);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['doc_no'])) {
    $doc_no = mysqli_real_escape_string($conn, trim($_POST['doc_no']));
}

$shipment_data = null;
$tracking_history = [];
$timeline = [];

if ($doc_no) {
    // Fetch from docket_details table only
    $query = "SELECT 
        doc_no, 
        COALESCE(company_name, 'N/A') as company_name,
        COALESCE(client_name, 'N/A') as client_name,
        COALESCE(client_phone, '') as client_contact,
        COALESCE(client_email, '') as client_email,
        COALESCE(status, 'Pending') as status,
        COALESCE(pickup_location, '') as pickup_location,
        COALESCE(delivery_location, '') as delivery_location,
        COALESCE(pickup_datetime, created_at) as created_at,
        delivery_datetime as estimated_delivery,
        delivery_datetime as actual_delivery,
        'docket' as source,
        docket_id as id,
        branch_office,
        car_number,
        driver_name,
        driver_phone
    FROM docket_details 
    WHERE doc_no = '$doc_no' 
    LIMIT 1";
    
    $result = mysqli_query($conn, $query);
    $shipment_data = mysqli_fetch_assoc($result);
    
    // Fetch tracking history (if table exists)
    if ($shipment_data) {
        $history_query = "SELECT 
            th.tracking_id,
            th.doc_no,
            th.status,
            th.notes,
            th.location,
            th.updated_by_name,
            th.created_at,
            tsc.status_label,
            tsc.status_icon,
            tsc.status_color
        FROM tbl_tracking_history th
        LEFT JOIN tbl_tracking_status_config tsc ON th.status = tsc.status_name
        WHERE th.doc_no = '$doc_no'
        ORDER BY th.created_at ASC";
        
        $history_result = @mysqli_query($conn, $history_query);
        
        if ($history_result && mysqli_num_rows($history_result) > 0) {
            while ($row = mysqli_fetch_assoc($history_result)) {
                $tracking_history[] = $row;
            }
        }
        
        // Build timeline based on tracking history and current status
        $timeline = buildTimeline($shipment_data, $tracking_history);
    }
}

function buildTimeline($shipment, $history) {
    $has_branch = isset($shipment['branch_office']) && !empty($shipment['branch_office']);
    
    // Define timeline steps
    if ($has_branch) {
        $steps = [
            'Pending' => ['label' => 'Order Pending', 'icon' => 'hourglass-start'],
            'Picked Up' => ['label' => 'Picked Up', 'icon' => 'box'],
            'Manifest Created' => ['label' => 'Manifest Created', 'icon' => 'clipboard-list'],
            'In Transit to Branch' => ['label' => 'In Transit to Branch', 'icon' => 'truck'],
            'Arrived at Branch' => ['label' => 'Arrived at Branch', 'icon' => 'building'],
            'Out for Delivery' => ['label' => 'Out for Delivery', 'icon' => 'shipping-fast'],
            'Delivered' => ['label' => 'Delivered', 'icon' => 'check-circle']
        ];
    } else {
        $steps = [
            'Pending' => ['label' => 'Order Pending', 'icon' => 'hourglass-start'],
            'Picked Up' => ['label' => 'Picked Up', 'icon' => 'box'],
            'In Transit' => ['label' => 'In Transit', 'icon' => 'truck'],
            'Out for Delivery' => ['label' => 'Out for Delivery', 'icon' => 'shipping-fast'],
            'Delivered' => ['label' => 'Delivered', 'icon' => 'check-circle']
        ];
    }
    
    // Map history to timeline steps
    $timeline_data = [];
    $history_by_status = [];
    
    foreach ($history as $h) {
        $status = $h['status'];
        if (!isset($history_by_status[$status])) {
            $history_by_status[$status] = [];
        }
        $history_by_status[$status][] = $h;
    }
    
    foreach ($steps as $status => $step) {
        $timeline_item = [
            'status' => $status,
            'label' => $step['label'],
            'icon' => $step['icon'],
            'completed' => false,
            'active' => false,
            'time' => null,
            'location' => null,
            'notes' => null
        ];
        
        if (isset($history_by_status[$status])) {
            $latest = end($history_by_status[$status]);
            $timeline_item['completed'] = true;
            $timeline_item['time'] = $latest['created_at'];
            $timeline_item['location'] = $latest['location'];
            $timeline_item['notes'] = $latest['notes'];
        }
        
        // Mark current status as active
        if ($status == $shipment['status']) {
            $timeline_item['active'] = true;
            if (!$timeline_item['completed']) {
                $timeline_item['completed'] = true;
                $timeline_item['time'] = $shipment['created_at'];
            }
        }
        
        $timeline_data[] = $timeline_item;
    }
    
    return $timeline_data;
}

// If AJAX request, return only the content
if (!$is_ajax) {
    include("include/header.php");
}
?>

<?php if (!$is_ajax): ?>
<style>
body { 
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif; 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.tracking-page-wrapper {
    background: #f5f8fd;
    min-height: 100vh;
    padding: 40px 0;
}

.tracking-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Search Section */
.search-section {
    background: white;
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    text-align: center;
}

.search-title {
    font-size: 32px;
    font-weight: 900;
    color: #2c3e50;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.search-subtitle {
    font-size: 17px;
    color: #7f8c8d;
    margin-bottom: 30px;
}

.search-form {
    display: flex;
    gap: 15px;
    max-width: 600px;
    margin: 0 auto;
    flex-wrap: wrap;
    justify-content: center;
}

.search-input {
    flex: 1;
    min-width: 250px;
    padding: 18px 24px;
    border: 3px solid #e1e8ed;
    border-radius: 50px;
    font-size: 18px;
    transition: all 0.3s;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
}

.search-button {
    padding: 18px 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 18px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(102,126,234,0.4);
}

/* Result Section */
.result-section {
    background: white;
    border-radius: 20px;
    padding: 45px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.not-found {
    text-align: center;
    padding: 60px 20px;
}

.not-found i {
    font-size: 100px;
    color: #e1e8ed;
    margin-bottom: 25px;
}

.not-found h2 {
    font-size: 28px;
    color: #2c3e50;
    margin-bottom: 10px;
}

.not-found p {
    font-size: 17px;
    color: #7f8c8d;
}

/* Shipment Header */
.shipment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
    flex-wrap: wrap;
    gap: 20px;
    padding-bottom: 30px;
    border-bottom: 3px solid #f0f0f0;
}

.shipment-doc-number {
    font-size: 36px;
    font-weight: 900;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 15px;
}

.shipment-status-badge {
    padding: 15px 30px;
    border-radius: 50px;
    font-size: 18px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d1ecf1; color: #0c5460; }
.status-picked-up { background: #cce5ff; color: #004085; }
.status-in-transit { background: #d1ecf1; color: #0c5460; }
.status-in-transit-to-branch { background: #cfe2ff; color: #052c65; }
.status-arrived-at-branch { background: #cff4fc; color: #055160; }
.status-out-for-delivery { background: #cfe2ff; color: #084298; }
.status-delivered { background: #d1e7dd; color: #0f5132; }
.status-failed { background: #f8d7da; color: #842029; }

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 40px;
    margin-top: 35px;
}

/* Timeline Section */
.timeline-section h2 {
    font-size: 26px;
    font-weight: 900;
    color: #2c3e50;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.timeline {
    position: relative;
    padding-left: 50px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 18px;
    top: 15px;
    bottom: 15px;
    width: 4px;
    background: #e1e8ed;
    border-radius: 4px;
}

.timeline-item {
    position: relative;
    padding-bottom: 40px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-icon {
    position: absolute;
    left: -42px;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #fff;
    border: 4px solid #e1e8ed;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #95a5a6;
    z-index: 2;
    transition: all 0.3s;
}

.timeline-item.completed .timeline-icon {
    border-color: #667eea;
    background: #667eea;
    color: white;
    box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
}

.timeline-item.active .timeline-icon {
    border-color: #28a745;
    background: #28a745;
    color: white;
    box-shadow: 0 0 0 8px rgba(40,167,69,0.1);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(40,167,69,0.4);
    }
    50% {
        box-shadow: 0 0 0 15px rgba(40,167,69,0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40,167,69,0);
    }
}

.timeline-content {
    background: #f8f9fa;
    padding: 20px 25px;
    border-radius: 15px;
    transition: all 0.3s;
}

.timeline-item.completed .timeline-content {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.timeline-item.active .timeline-content {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border: 2px solid #28a745;
}

.timeline-label {
    font-size: 20px;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 8px;
}

.timeline-time {
    font-size: 15px;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 5px;
}

.timeline-location {
    font-size: 15px;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 8px;
}

.timeline-notes {
    font-size: 14px;
    color: #6c757d;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(0,0,0,0.1);
}

/* Info Cards */
.info-cards {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 25px;
    border-left: 5px solid #667eea;
}

.info-card-title {
    font-size: 18px;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-item {
    margin-bottom: 15px;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-label {
    font-size: 13px;
    color: #6c757d;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.info-value {
    font-size: 17px;
    color: #2c3e50;
    font-weight: 700;
}

/* History Button */
.view-history-btn {
    margin-top: 25px;
    padding: 15px;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s;
}

.view-history-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,123,255,0.4);
}

/* History Modal */
.history-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.history-modal-content {
    background: white;
    border-radius: 20px;
    padding: 40px;
    max-width: 700px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.history-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 3px solid #f0f0f0;
}

.history-modal-header h2 {
    font-size: 28px;
    font-weight: 900;
    color: #2c3e50;
    margin: 0;
}

.history-close-btn {
    background: none;
    border: none;
    font-size: 32px;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.3s;
}

.history-close-btn:hover {
    color: #2c3e50;
    transform: rotate(90deg);
}

.history-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.history-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid #667eea;
}

.history-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.history-status {
    font-size: 18px;
    font-weight: 800;
    color: #2c3e50;
}

.history-time {
    font-size: 14px;
    color: #6c757d;
}

.history-details {
    font-size: 15px;
    color: #495057;
}

/* Responsive */
@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .search-section {
        padding: 25px;
    }
    
    .search-title {
        font-size: 24px;
    }
    
    .search-form {
        flex-direction: column;
    }
    
    .search-input {
        min-width: 100%;
    }
    
    .result-section {
        padding: 25px;
    }
    
    .shipment-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .shipment-doc-number {
        font-size: 26px;
    }
    
    .timeline {
        padding-left: 40px;
    }
    
    .timeline-icon {
        width: 35px;
        height: 35px;
        left: -38px;
        font-size: 16px;
    }
}
</style>

<section class="tracking-page-wrapper">
  <div class="tracking-container">
    
    <!-- Search Section -->
    <div class="search-section">
      <h1 class="search-title">
        <i class="fas fa-search-location"></i>
        Track Your Shipment
      </h1>
      <p class="search-subtitle">Enter your document number to track your package in real-time</p>
      
      <form class="search-form" id="trackingSearchForm" method="GET" action="">
        <input type="text" name="doc_no" class="search-input" 
               placeholder="Enter Document Number" 
               value="<?= htmlspecialchars($doc_no) ?>" 
               required>
        <button type="submit" class="search-button">
          <i class="fas fa-search"></i>
          Track Now
        </button>
      </form>
    </div>

    <!-- Result Section -->
    <div class="result-section" id="trackingResult">
<?php endif; ?>

      <?php if ($doc_no && !$shipment_data): ?>
        <div class="not-found">
          <i class="fas fa-box-open"></i>
          <h2>Shipment Not Found</h2>
          <p>We couldn't find any shipment with document number: <strong><?= htmlspecialchars($doc_no) ?></strong></p>
          <p>Please check the number and try again.</p>
        </div>
      
      <?php elseif ($shipment_data): ?>
        <!-- Shipment Found -->
        <div class="shipment-header">
          <div class="shipment-doc-number">
            <i class="fas fa-barcode"></i>
            <?= htmlspecialchars($shipment_data['doc_no']) ?>
          </div>
          <span class="shipment-status-badge status-<?= strtolower(str_replace(' ', '-', $shipment_data['status'])) ?>">
            <?= htmlspecialchars($shipment_data['status']) ?>
          </span>
        </div>

        <div class="content-grid">
          <!-- Timeline -->
          <div class="timeline-section">
            <h2>
              <i class="fas fa-route"></i>
              Shipment Journey
            </h2>
            
            <div class="timeline">
              <?php foreach ($timeline as $item): ?>
                <div class="timeline-item <?= $item['completed'] ? 'completed' : '' ?> <?= $item['active'] ? 'active' : '' ?>">
                  <div class="timeline-icon">
                    <i class="fas fa-<?= $item['icon'] ?>"></i>
                  </div>
                  <div class="timeline-content">
                    <div class="timeline-label"><?= htmlspecialchars($item['label']) ?></div>
                    <?php if ($item['time']): ?>
                      <div class="timeline-time">
                        <i class="fas fa-clock"></i>
                        <?= date('d M Y, h:i A', strtotime($item['time'])) ?>
                      </div>
                    <?php endif; ?>
                    <?php if ($item['location']): ?>
                      <div class="timeline-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= htmlspecialchars($item['location']) ?>
                      </div>
                    <?php endif; ?>
                    <?php if ($item['notes']): ?>
                      <div class="timeline-notes">
                        <?= htmlspecialchars($item['notes']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Info Cards -->
          <div class="info-cards">
            <div class="info-card">
              <div class="info-card-title">
                <i class="fas fa-info-circle"></i>
                Shipment Details
              </div>
              <div class="info-item">
                <div class="info-label">Company</div>
                <div class="info-value"><?= htmlspecialchars($shipment_data['company_name'] ?? 'N/A') ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Client</div>
                <div class="info-value"><?= htmlspecialchars($shipment_data['client_name'] ?? 'N/A') ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Current Location</div>
                <div class="info-value"><?= htmlspecialchars($shipment_data['current_location'] ?? 'In Transit') ?></div>
              </div>
              <?php if (!empty($shipment_data['estimated_delivery'])): ?>
              <div class="info-item">
                <div class="info-label">Est. Delivery</div>
                <div class="info-value"><?= date('d M Y', strtotime($shipment_data['estimated_delivery'])) ?></div>
              </div>
              <?php endif; ?>
            </div>

            <div class="info-card">
              <div class="info-card-title">
                <i class="fas fa-map-marked-alt"></i>
                Route Information
              </div>
              <div class="info-item">
                <div class="info-label">From</div>
                <div class="info-value"><?= htmlspecialchars($shipment_data['pickup_location'] ?? 'N/A') ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">To</div>
                <div class="info-value"><?= htmlspecialchars($shipment_data['delivery_location'] ?? $shipment_data['client_name']) ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Shipment Date</div>
                <div class="info-value"><?= date('d M Y', strtotime($shipment_data['created_at'])) ?></div>
              </div>
            </div>

            <?php if (!empty($tracking_history)): ?>
            <button class="view-history-btn" onclick="openHistoryModal()">
              <i class="fas fa-history"></i>
              View Full History (<?= count($tracking_history) ?> Updates)
            </button>
            <?php endif; ?>
          </div>
        </div>

      <?php elseif (!$doc_no): ?>
        <div class="not-found">
          <i class="fas fa-search"></i>
          <h2>Start Tracking</h2>
          <p>Enter your document number above to track your shipment</p>
        </div>
      <?php endif; ?>

<?php if (!$is_ajax): ?>
    </div><!-- /result-section -->
  </div><!-- /tracking-container -->
</section>

<!-- History Modal -->
<div id="historyModal" class="history-modal">
  <div class="history-modal-content">
    <div class="history-modal-header">
      <h2><i class="fas fa-history"></i> Complete Tracking History</h2>
      <button class="history-close-btn" onclick="closeHistoryModal()">&times;</button>
    </div>
    <div class="history-list">
      <?php foreach (array_reverse($tracking_history) as $history): ?>
        <div class="history-item">
          <div class="history-item-header">
            <span class="history-status"><?= htmlspecialchars($history['status_label'] ?? $history['status']) ?></span>
            <span class="history-time"><?= date('d M Y, h:i A', strtotime($history['created_at'])) ?></span>
          </div>
          <?php if ($history['location']): ?>
            <div class="history-details">
              <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($history['location']) ?>
            </div>
          <?php endif; ?>
          <?php if ($history['notes']): ?>
            <div class="history-details" style="margin-top: 8px;">
              <i class="fas fa-comment"></i> <?= htmlspecialchars($history['notes']) ?>
            </div>
          <?php endif; ?>
          <?php if ($history['updated_by_name']): ?>
            <div class="history-details" style="margin-top: 5px; font-size: 13px; color: #6c757d;">
              Updated by: <?= htmlspecialchars($history['updated_by_name']) ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
function openHistoryModal() {
    document.getElementById('historyModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeHistoryModal() {
    document.getElementById('historyModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('historyModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeHistoryModal();
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeHistoryModal();
    }
});

// AJAX form submission (optional)
document.getElementById('trackingSearchForm')?.addEventListener('submit', function(e) {
    // For now, use normal form submission
    // You can implement AJAX here if needed
});
</script>

<?php 
include("include/footer.php");
?>
<?php endif; ?>
