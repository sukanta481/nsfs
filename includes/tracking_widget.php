<!-- 
  =====================================================
  Tracking Widget - Reusable Component
  =====================================================
  
  This widget can be embedded in any page to show quick tracking status
  
  Usage:
  1. Include this file: <?php include 'includes/tracking_widget.php'; ?>
  2. Call the function: displayTrackingWidget($doc_no);
  
  Or use as inline HTML with the provided CSS and JS
  =====================================================
-->

<?php
/**
 * Display tracking widget for a document number
 * 
 * @param string $doc_no Document number to track
 * @param bool $compact Show compact version (default: false)
 */
function displayTrackingWidget($doc_no, $compact = false) {
    global $conn;
    
    if (empty($doc_no)) {
        return;
    }
    
    $doc_no = mysqli_real_escape_string($conn, $doc_no);
    
    // Get shipment data
    $query = "
        SELECT doc_no, status, current_location, created_at, estimated_delivery
        FROM docket_details 
        WHERE doc_no = '$doc_no'
        
        UNION
        
        SELECT doc_no, status, current_location, pickup_dates as created_at, NULL as estimated_delivery
        FROM tbl_shipping_details 
        WHERE doc_no = '$doc_no'
        
        LIMIT 1";
    
    $result = mysqli_query($conn, $query);
    $shipment = mysqli_fetch_assoc($result);
    
    if (!$shipment) {
        return;
    }
    
    // Get latest tracking update
    $tracking_query = "SELECT status, location, created_at 
                       FROM tbl_tracking_history 
                       WHERE doc_no = '$doc_no' 
                       ORDER BY created_at DESC 
                       LIMIT 1";
    $tracking_result = mysqli_query($conn, $tracking_query);
    $latest_tracking = mysqli_fetch_assoc($tracking_result);
    
    $status = $shipment['status'] ?? 'Pending';
    $location = $latest_tracking['location'] ?? $shipment['current_location'] ?? 'In Transit';
    $last_update = $latest_tracking['created_at'] ?? $shipment['created_at'];
    
    // Status colors
    $status_colors = [
        'Pending' => '#ffc107',
        'Picked Up' => '#17a2b8',
        'In Transit' => '#20c997',
        'Out for Delivery' => '#fd7e14',
        'Delivered' => '#28a745',
        'Failed' => '#dc3545',
        'Delayed' => '#e83e8c'
    ];
    
    $status_color = $status_colors[$status] ?? '#6c757d';
    
    if ($compact) {
        // Compact version (single line)
        ?>
        <div class="tracking-widget-compact">
            <span class="tw-status" style="background: <?= $status_color ?>;"><?= htmlspecialchars($status) ?></span>
            <span class="tw-location"><?= htmlspecialchars($location) ?></span>
            <a href="track_shipment.php?doc_no=<?= urlencode($doc_no) ?>" class="tw-link">Track</a>
        </div>
        <?php
    } else {
        // Full version (card)
        ?>
        <div class="tracking-widget-card">
            <div class="tw-header">
                <div class="tw-doc-no">
                    <i class="fas fa-barcode"></i>
                    <?= htmlspecialchars($doc_no) ?>
                </div>
                <span class="tw-status-badge" style="background: <?= $status_color ?>;">
                    <?= htmlspecialchars($status) ?>
                </span>
            </div>
            <div class="tw-body">
                <div class="tw-info-row">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Current Location:</span>
                    <strong><?= htmlspecialchars($location) ?></strong>
                </div>
                <div class="tw-info-row">
                    <i class="fas fa-clock"></i>
                    <span>Last Update:</span>
                    <strong><?= date('d M Y, h:i A', strtotime($last_update)) ?></strong>
                </div>
            </div>
            <div class="tw-footer">
                <a href="track_shipment.php?doc_no=<?= urlencode($doc_no) ?>" class="tw-btn">
                    <i class="fas fa-external-link-alt"></i>
                    View Full Tracking
                </a>
            </div>
        </div>
        <?php
    }
}
?>

<!-- Widget Styles -->
<style>
/* Compact Widget */
.tracking-widget-compact {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #667eea;
}

.tw-status {
    padding: 6px 14px;
    border-radius: 20px;
    color: white;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tw-location {
    flex: 1;
    font-size: 15px;
    color: #2c3e50;
    font-weight: 600;
}

.tw-link {
    padding: 6px 16px;
    background: #667eea;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    transition: all 0.3s;
}

.tw-link:hover {
    background: #5541d7;
    transform: translateY(-2px);
}

/* Card Widget */
.tracking-widget-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    overflow: hidden;
    max-width: 400px;
}

.tw-header {
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.tw-doc-no {
    font-size: 18px;
    font-weight: 900;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tw-status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    color: white;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tw-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.tw-info-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
    color: #495057;
}

.tw-info-row i {
    color: #667eea;
    width: 20px;
}

.tw-info-row strong {
    color: #2c3e50;
    font-weight: 700;
}

.tw-footer {
    padding: 15px 20px;
    background: #f8f9fa;
    border-top: 2px solid #e9ecef;
}

.tw-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 800;
    transition: all 0.3s;
}

.tw-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102,126,234,0.4);
}

/* Responsive */
@media (max-width: 480px) {
    .tracking-widget-compact {
        flex-wrap: wrap;
    }
    
    .tracking-widget-card {
        max-width: 100%;
    }
}
</style>

<!-- Example Usage -->
<?php
/*
// In your PHP file:

// Compact version
displayTrackingWidget('DOC123456', true);

// Full card version
displayTrackingWidget('DOC123456', false);

// Or without function, directly in HTML:
*/
?>

<!-- Standalone HTML Example (without PHP function) -->
<!--
<div class="tracking-widget-card">
    <div class="tw-header">
        <div class="tw-doc-no">
            <i class="fas fa-barcode"></i>
            DOC123456
        </div>
        <span class="tw-status-badge" style="background: #28a745;">
            Delivered
        </span>
    </div>
    <div class="tw-body">
        <div class="tw-info-row">
            <i class="fas fa-map-marker-alt"></i>
            <span>Current Location:</span>
            <strong>Mumbai Warehouse</strong>
        </div>
        <div class="tw-info-row">
            <i class="fas fa-clock"></i>
            <span>Last Update:</span>
            <strong>15 Nov 2024, 03:30 PM</strong>
        </div>
    </div>
    <div class="tw-footer">
        <a href="track_shipment.php?doc_no=DOC123456" class="tw-btn">
            <i class="fas fa-external-link-alt"></i>
            View Full Tracking
        </a>
    </div>
</div>
-->
