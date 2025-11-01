<?php
$shipping_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($shipping_id <= 0) {
    echo "<div style='margin: 20px; padding: 20px; background: #f8d7da; color: #721c24; border-radius: 12px; font-size: 1.3rem; font-weight: 600; font-family: Inter, sans-serif;'>
        <i class='fa fa-exclamation-circle'></i> Invalid docket ID! Please select a valid docket.
    </div>";
    exit;
}

$query = "SELECT sd.*, 
          sender.company_title as sender_name, sender.company_phone as sender_phone, sender.company_address as sender_address,
          sd.client_name as receiver_name, sd.client_phone as receiver_phone, sd.client_email as receiver_email, sd.client_address as receiver_address
          FROM tbl_shipping_details sd
          LEFT JOIN tbl_company sender ON sd.company_id = sender.company_id
          WHERE sd.shipping_details_id = $shipping_id";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo "<div style='margin: 20px; padding: 20px; background: #f8d7da; color: #721c24; border-radius: 12px; font-size: 1.3rem; font-weight: 600; font-family: Inter, sans-serif;'>
        <i class='fa fa-exclamation-circle'></i> Database error: " . mysqli_error($conn) . "
    </div>";
    exit;
}

$docket = mysqli_fetch_assoc($result);

if (!$docket) {
    echo "<div style='margin: 20px; padding: 20px; background: #f8d7da; color: #721c24; border-radius: 12px; font-size: 1.3rem; font-weight: 600; font-family: Inter, sans-serif;'>
        <i class='fa fa-exclamation-circle'></i> Docket not found! (ID: $shipping_id)
    </div>";
    exit;
}

// Fetch status history
$history_result = null;
$check_table = @mysqli_query($conn, "SHOW TABLES LIKE 'tbl_shipping_history'");
if ($check_table && mysqli_num_rows($check_table) > 0) {
    $history_query = "SELECT * FROM tbl_shipping_history WHERE shipping_details_id = $shipping_id ORDER BY created_at DESC";
    $history_result = @mysqli_query($conn, $history_query);
}

// Status badge color mapping
$status_colors = [
    'Picked Up' => ['bg' => '#ffc107', 'text' => '#000'],
    'In Transit' => ['bg' => '#17a2b8', 'text' => '#fff'],
    'Out for Delivery' => ['bg' => '#007bff', 'text' => '#fff'],
    'Delivered' => ['bg' => '#28a745', 'text' => '#fff'],
    'Delayed' => ['bg' => '#dc3545', 'text' => '#fff'],
];

$current_status = $docket['status'] ?? 'Unknown';
$status_style = $status_colors[$current_status] ?? ['bg' => '#6c757d', 'text' => '#fff'];
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
.alert {
    padding: 15px 20px;
    margin: 20px auto;
    max-width: 1600px;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert i {
    font-size: 1.2rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.docket-view-container {
    font-family: 'Inter', sans-serif;
    padding: 20px;
    max-width: 1600px;
    margin: 0 auto;
}

.docket-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.docket-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.docket-card-header {
    background: linear-gradient(135deg, #4a6b88 0%, #3b5770 100%);
    color: #fff;
    padding: 12px 20px;
    font-size: 1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.docket-card-header i {
    font-size: 1.1rem;
}

.status-badge-header {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 700;
    margin-left: auto;
}

.docket-card-body {
    padding: 20px;
}

.info-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.info-group {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.info-label {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.info-label i {
    font-size: 0.9rem;
}

.info-value {
    font-size: 1rem;
    color: #212529;
    font-weight: 600;
}

.section-header {
    font-size: 0.9rem;
    color: #212529;
    font-weight: 700;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-header i {
    font-size: 1rem;
}

.section-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.info-row-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 0;
}

/* Actions Card */
.actions-card .docket-card-header {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.action-btn {
    padding: 12px 20px;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.action-btn i {
    font-size: 1rem;
}

.btn-edit {
    background: #3498db;
    color: #fff;
}

.btn-edit:hover {
    background: #2980b9;
    text-decoration: none;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52,152,219,0.3);
}

.btn-delete {
    background: #e74c3c;
    color: #fff;
}

.btn-delete:hover {
    background: #c0392b;
    text-decoration: none;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231,76,60,0.3);
}

/* Update Status Card */
.update-card {
    margin-top: 20px;
}

.update-card .docket-card-header {
    background: linear-gradient(135deg, #16a085 0%, #138d75 100%);
}

.status-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-group label {
    font-size: 0.9rem;
    color: #212529;
    font-weight: 700;
    margin-bottom: 8px;
    display: block;
}

.form-group select {
    padding: 10px 15px;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 600;
    color: #212529;
    background: #fff;
    width: 100%;
    cursor: pointer;
    transition: all 0.3s;
}

.form-group select:focus {
    border-color: #16a085;
    outline: none;
    box-shadow: 0 0 0 3px rgba(22,160,133,0.1);
}

.btn-update {
    background: #27ae60;
    color: #fff;
    padding: 12px;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-update:hover {
    background: #229954;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(39,174,96,0.3);
}

/* Status History */
.history-card {
    grid-column: 1 / -1;
}

.history-card .docket-card-header {
    background: linear-gradient(135deg, #4a6b88 0%, #3b5770 100%);
}

.timeline-simple {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.timeline-item {
    padding: 12px 15px;
    background: #f8f9fa;
    border-left: 4px solid #17a2b8;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.timeline-badge {
    background: #17a2b8;
    color: #fff;
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 700;
    min-width: 100px;
    text-align: center;
}

.timeline-badge.pending {
    background: #ffc107;
    color: #000;
}

.timeline-text {
    color: #495057;
    font-size: 0.9rem;
    font-weight: 600;
}

.timeline-time {
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 600;
    margin-left: auto;
}

.no-history {
    text-align: center;
    padding: 40px;
    color: #adb5bd;
    font-size: 1rem;
}

.no-history i {
    font-size: 2.5rem;
    margin-bottom: 10px;
    display: block;
}

@media (max-width: 1200px) {
    .docket-grid {
        grid-template-columns: 1fr;
    }
    
    .info-row {
        grid-template-columns: 1fr;
    }
    
    .info-row-3 {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .docket-view-container {
        padding: 10px;
    }
    
    .docket-card-body {
        padding: 15px;
    }
}
</style>

<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($_SESSION['success_msg']) ?>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_msg'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($_SESSION['error_msg']) ?>
    </div>
    <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

<div class="docket-view-container">
    <div class="docket-grid">
        <!-- Main Details Card -->
        <div class="docket-card">
            <div class="docket-card-header">
                <i class="fas fa-file-alt"></i> Docket Details
                <span class="status-badge-header" style="background: <?= $status_style['bg'] ?>; color: <?= $status_style['text'] ?>;">
                    <?= htmlspecialchars($current_status) ?>
                </span>
            </div>
            <div class="docket-card-body">
                <!-- Tracking & Service -->
                <div class="info-row">
                    <div class="info-group">
                        <div class="info-label"><i class="fas fa-barcode"></i> Tracking Number:</div>
                        <div class="info-value"><?= htmlspecialchars($docket['doc_no'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label"><i class="fas fa-truck"></i> Service Type:</div>
                        <div class="info-value"><?= htmlspecialchars($docket['service_type'] ?? 'Standard') ?></div>
                    </div>
                </div>
                
                <!-- Sender & Receiver -->
                <div class="info-row">
                    <div>
                        <div class="section-header"><i class="fas fa-user"></i> Sender Information</div>
                        <div class="section-content">
                            <div class="info-group">
                                <div class="info-label">Name:</div>
                                <div class="info-value"><?= htmlspecialchars($docket['sender_name'] ?? 'N/A') ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Phone:</div>
                                <div class="info-value"><?= htmlspecialchars($docket['sender_phone'] ?? 'N/A') ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Address:</div>
                                <div class="info-value"><?= htmlspecialchars($docket['sender_address'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="section-header"><i class="fas fa-user-check"></i> Receiver Information</div>
                        <div class="section-content">
                            <div class="info-group">
                                <div class="info-label">Name:</div>
                                <div class="info-value"><?= htmlspecialchars($docket['receiver_name'] ?? 'N/A') ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Phone:</div>
                                <div class="info-value"><?= htmlspecialchars($docket['receiver_phone'] ?? 'N/A') ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Email:</div>
                                <div class="info-value"><?= htmlspecialchars($docket['receiver_email'] ?? 'N/A') ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Address:</div>
                                <div class="info-value"><?= htmlspecialchars($docket['receiver_address'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pickup & Delivery -->
                <div class="info-row">
                    <div class="info-group">
                        <div class="info-label"><i class="fas fa-map-marker-alt"></i> Pickup Address</div>
                        <div class="info-value"><?= htmlspecialchars($docket['pickup_location'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label"><i class="fas fa-map-marker-alt"></i> Delivery Address</div>
                        <div class="info-value"><?= htmlspecialchars($docket['delivery_location'] ?? 'N/A') ?></div>
                    </div>
                </div>
                
                <!-- Weight, Dimensions, Created -->
                <div class="info-row-3">
                    <div class="info-group">
                        <div class="info-label"><i class="fas fa-weight-hanging"></i> Weight:</div>
                        <div class="info-value"><?= htmlspecialchars($docket['weight'] ?? 'N/A') ?> kg</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label"><i class="fas fa-ruler-combined"></i> Dimensions:</div>
                        <div class="info-value"><?= htmlspecialchars($docket['dimensions'] ?? 'N/A') ?> cm</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label"><i class="fas fa-calendar-plus"></i> Created:</div>
                        <div class="info-value"><?= isset($docket['created_at']) ? date('M d, Y g:i A', strtotime($docket['created_at'])) : 'N/A' ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div>
            <div class="docket-card actions-card">
                <div class="docket-card-header">
                    <i class="fas fa-cog"></i> Actions
                </div>
                <div class="docket-card-body">
                    <div class="action-buttons">
                        <a href="trip.php?type=edit_trip&id=<?= $shipping_id ?>" class="action-btn btn-edit">
                            <i class="fas fa-edit"></i> Edit Docket
                        </a>
                        <a href="#" onclick="confirmDelete(<?= $shipping_id ?>); return false;" class="action-btn btn-delete">
                            <i class="fas fa-trash-alt"></i> Delete Docket
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="docket-card update-card">
                <div class="docket-card-header">
                    <i class="fas fa-sync-alt"></i> Update Status
                </div>
                <div class="docket-card-body">
                    <form class="status-form" method="POST" action="update_status.php">
                        <input type="hidden" name="shipping_details_id" value="<?= $shipping_id ?>">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" required>
                                <option value="Picked Up" <?= $current_status == 'Picked Up' ? 'selected' : '' ?>>Picked Up</option>
                                <option value="In Transit" <?= $current_status == 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                                <option value="Out for Delivery" <?= $current_status == 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                <option value="Delivered" <?= $current_status == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="Delayed" <?= $current_status == 'Delayed' ? 'selected' : '' ?>>Delayed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-update">
                            <i class="fas fa-check-circle"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status History -->
    <div class="docket-card history-card">
        <div class="docket-card-header">
            <i class="fas fa-history"></i> Status History
        </div>
        <div class="docket-card-body">
            <?php if ($history_result && mysqli_num_rows($history_result) > 0): ?>
                <div class="timeline-simple">
                    <?php while ($history = mysqli_fetch_assoc($history_result)): ?>
                        <div class="timeline-item">
                            <span class="timeline-badge <?= strtolower(str_replace(' ', '-', $history['status'])) ?>">
                                <?= htmlspecialchars($history['status']) ?>
                            </span>
                            <?php if (!empty($history['notes'])): ?>
                                <span class="timeline-text"><?= htmlspecialchars($history['notes']) ?></span>
                            <?php endif; ?>
                            <span class="timeline-time"><?= date('M d, Y g:i A', strtotime($history['created_at'])) ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-history">
                    <i class="fas fa-info-circle"></i>
                    <p>No status history available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this docket? This action cannot be undone.')) {
        window.location.href = 'trip.php?action=delete_shipping_details&shipping_details_id=' + id;
    }
}
</script>
