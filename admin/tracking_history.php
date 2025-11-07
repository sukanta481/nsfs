<?php
require 'check_auth.php';
require 'conn.php';

$doc_no = isset($_GET['doc_no']) ? mysqli_real_escape_string($conn, trim($_GET['doc_no'])) : '';

if (empty($doc_no)) {
    header('Location: tracking_management.php');
    exit;
}

// Get shipment data from docket_details only
$shipment_query = "
    SELECT doc_no, company_name, client_name, status, pickup_location, delivery_location, created_at, 'docket' as source
    FROM docket_details 
    WHERE doc_no = '$doc_no'
    LIMIT 1";

$shipment_result = mysqli_query($conn, $shipment_query);
$shipment = mysqli_fetch_assoc($shipment_result);

if (!$shipment) {
    die('Shipment not found');
}

// Get tracking history
$history_query = "SELECT 
    th.*,
    tsc.status_label,
    tsc.status_icon,
    tsc.status_color
FROM tbl_tracking_history th
LEFT JOIN tbl_tracking_status_config tsc ON th.status = tsc.status_name
WHERE th.doc_no = '$doc_no'
ORDER BY th.created_at DESC";

$history_result = mysqli_query($conn, $history_query);

require 'top_header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
body {
    background: #f0f4f8 !important;
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.history-page-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 35px;
    border-radius: 20px;
    margin-bottom: 30px;
    box-shadow: 0 8px 30px rgba(102,126,234,0.4);
}

.page-header h1 {
    margin: 0 0 15px 0;
    font-size: 32px;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 15px;
}

.shipment-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    opacity: 0.95;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 13px;
    opacity: 0.9;
    margin-bottom: 5px;
}

.info-value {
    font-size: 17px;
    font-weight: 700;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 25px;
    background: rgba(255,255,255,0.2);
    color: white;
    border: 2px solid white;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 700;
    margin-top: 20px;
    transition: all 0.3s;
}

.back-button:hover {
    background: white;
    color: #667eea;
}

.history-container {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.history-title {
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
    top: 0;
    bottom: 0;
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
    background: #667eea;
    border: 4px solid white;
    box-shadow: 0 0 0 4px #e1e8ed;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
    z-index: 2;
}

.timeline-item:first-child .timeline-icon {
    background: #28a745;
    box-shadow: 0 0 0 4px rgba(40,167,69,0.2);
}

.timeline-content {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 15px;
    border-left: 4px solid #667eea;
    transition: all 0.3s;
}

.timeline-item:first-child .timeline-content {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border-left-color: #28a745;
}

.timeline-content:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.timeline-status {
    font-size: 22px;
    font-weight: 900;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.timeline-date {
    font-size: 15px;
    color: #6c757d;
    font-weight: 600;
}

.timeline-location {
    font-size: 16px;
    color: #495057;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.timeline-notes {
    font-size: 15px;
    color: #6c757d;
    line-height: 1.6;
    margin-top: 10px;
    padding-top: 15px;
    border-top: 2px solid rgba(0,0,0,0.05);
}

.timeline-user {
    font-size: 14px;
    color: #7f8c8d;
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.no-history {
    text-align: center;
    padding: 60px 20px;
    color: #7f8c8d;
}

.no-history i {
    font-size: 80px;
    color: #e1e8ed;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .page-header h1 {
        font-size: 24px;
    }
    
    .history-container {
        padding: 25px;
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

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <div class="right_col" role="main">
        <div class="history-page-container">
          
          <!-- Page Header -->
          <div class="page-header">
            <h1>
              <i class="fas fa-history"></i>
              Tracking History
            </h1>
            
            <div class="shipment-info">
              <div class="info-item">
                <div class="info-label">Document Number</div>
                <div class="info-value"><?= htmlspecialchars($shipment['doc_no']) ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Company</div>
                <div class="info-value"><?= htmlspecialchars($shipment['company_name']) ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Client</div>
                <div class="info-value"><?= htmlspecialchars($shipment['client_name']) ?></div>
              </div>
              <div class="info-item">
                <div class="info-label">Current Status</div>
                <div class="info-value"><?= htmlspecialchars($shipment['status']) ?></div>
              </div>
            </div>
            
            <a href="tracking_management.php" class="back-button">
              <i class="fas fa-arrow-left"></i>
              Back to Tracking Management
            </a>
          </div>

          <!-- History Timeline -->
          <div class="history-container">
            <div class="history-title">
              <i class="fas fa-clock"></i>
              Complete Status History
            </div>
            
            <?php if (mysqli_num_rows($history_result) > 0): ?>
              <div class="timeline">
                <?php while ($history = mysqli_fetch_assoc($history_result)): ?>
                  <div class="timeline-item">
                    <div class="timeline-icon">
                      <i class="fas fa-<?= $history['status_icon'] ?? 'flag' ?>"></i>
                    </div>
                    <div class="timeline-content">
                      <div class="timeline-header">
                        <div class="timeline-status">
                          <?= htmlspecialchars($history['status_label'] ?? $history['status']) ?>
                        </div>
                        <div class="timeline-date">
                          <i class="fas fa-clock"></i>
                          <?= date('d M Y, h:i A', strtotime($history['created_at'])) ?>
                        </div>
                      </div>
                      
                      <?php if (!empty($history['location'])): ?>
                        <div class="timeline-location">
                          <i class="fas fa-map-marker-alt"></i>
                          <strong>Location:</strong> <?= htmlspecialchars($history['location']) ?>
                        </div>
                      <?php endif; ?>
                      
                      <?php if (!empty($history['notes'])): ?>
                        <div class="timeline-notes">
                          <i class="fas fa-comment-alt"></i>
                          <?= nl2br(htmlspecialchars($history['notes'])) ?>
                        </div>
                      <?php endif; ?>
                      
                      <?php if (!empty($history['updated_by_name'])): ?>
                        <div class="timeline-user">
                          <i class="fas fa-user"></i>
                          Updated by: <strong><?= htmlspecialchars($history['updated_by_name']) ?></strong>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endwhile; ?>
              </div>
            <?php else: ?>
              <div class="no-history">
                <i class="fas fa-inbox"></i>
                <h3>No tracking history found</h3>
                <p>No status updates have been recorded for this shipment yet.</p>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
      
      <?php require 'footer.php'; ?>
    </div>
  </div>
</body>
</html>
