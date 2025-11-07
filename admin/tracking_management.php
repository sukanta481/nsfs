<?php
require 'check_auth.php';
requirePermission('tracking_management');
require 'conn.php';

// Get filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$status_filter = isset($_GET['status_filter']) ? mysqli_real_escape_string($conn, $_GET['status_filter']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query for both docket_details and tbl_shipping_details
$where = [];
if (!empty($search)) {
    $where[] = "(doc_no LIKE '%$search%' OR company_name LIKE '%$search%' OR client_name LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $where[] = "status = '$status_filter'";
}
if (!empty($date_from)) {
    $where[] = "DATE(created_at) >= '$date_from'";
}
if (!empty($date_to)) {
    $where[] = "DATE(created_at) <= '$date_to'";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Query to get from docket_details table only
$query = "
    SELECT 
        doc_no, 
        COALESCE(company_name, 'N/A') as company_name,
        COALESCE(client_name, 'N/A') as client_name,
        COALESCE(status, 'Pending') as status,
        created_at,
        'docket' as source,
        docket_id as id,
        COALESCE(pickup_location, 'N/A') as from_location,
        COALESCE(delivery_location, 'N/A') as to_location,
        pickup_datetime,
        delivery_datetime
    FROM docket_details 
    $where_clause
    ORDER BY created_at DESC
    LIMIT 100";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}

// Get status counts from docket_details only
$where_for_counts = str_replace('WHERE', '', $where_clause);
$and_clause = !empty($where_for_counts) ? ' AND (' . $where_for_counts . ')' : '';

$counts_query = "
    SELECT 
        (SELECT COUNT(*) FROM docket_details WHERE 1=1 $and_clause) as total,
        
        (SELECT COUNT(*) FROM docket_details WHERE (status = 'Pending' OR status = '' OR status IS NULL) $and_clause) as pending,
        
        (SELECT COUNT(*) FROM docket_details WHERE status = 'In Transit' $and_clause) as in_transit,
        
        (SELECT COUNT(*) FROM docket_details WHERE status = 'Out for Delivery' $and_clause) as out_for_delivery,
        
        (SELECT COUNT(*) FROM docket_details WHERE status = 'Delivered' $and_clause) as delivered
";

$counts_result = mysqli_query($conn, $counts_query);
$counts = mysqli_fetch_assoc($counts_result);

// Get available statuses from config (with fallback if table doesn't exist)
$available_statuses = [];
$status_config_query = "SELECT * FROM tbl_tracking_status_config WHERE is_active = 1 ORDER BY display_order ASC";
$status_config_result = @mysqli_query($conn, $status_config_query);

if ($status_config_result && mysqli_num_rows($status_config_result) > 0) {
    while ($status_row = mysqli_fetch_assoc($status_config_result)) {
        $available_statuses[] = $status_row;
    }
} else {
    // Fallback to default statuses if config table doesn't exist or is empty
    $available_statuses = [
        ['status_name' => 'Pending', 'status_label' => 'Order Pending', 'status_icon' => 'hourglass-start', 'status_color' => '#ffc107'],
        ['status_name' => 'Confirmed', 'status_label' => 'Order Confirmed', 'status_icon' => 'check-circle', 'status_color' => '#17a2b8'],
        ['status_name' => 'Picked Up', 'status_label' => 'Picked Up', 'status_icon' => 'truck-loading', 'status_color' => '#007bff'],
        ['status_name' => 'In Transit', 'status_label' => 'In Transit', 'status_icon' => 'shipping-fast', 'status_color' => '#20c997'],
        ['status_name' => 'Out for Delivery', 'status_label' => 'Out for Delivery', 'status_icon' => 'truck', 'status_color' => '#fd7e14'],
        ['status_name' => 'Delivered', 'status_label' => 'Delivered Successfully', 'status_icon' => 'check-double', 'status_color' => '#28a745'],
        ['status_name' => 'Delayed', 'status_label' => 'Shipment Delayed', 'status_icon' => 'clock', 'status_color' => '#e83e8c'],
        ['status_name' => 'Failed', 'status_label' => 'Delivery Failed', 'status_icon' => 'exclamation-triangle', 'status_color' => '#dc3545'],
        ['status_name' => 'Cancelled', 'status_label' => 'Order Cancelled', 'status_icon' => 'times-circle', 'status_color' => '#6c757d'],
    ];
}

require 'top_header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
body {
    background: #f0f4f8 !important;
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.tracking-page-container {
    padding: 20px;
    max-width: 100%;
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
    margin: 0;
    font-size: 36px;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 15px;
}

.page-header p {
    margin: 15px 0 0 0;
    opacity: 0.95;
    font-size: 17px;
}

/* Stats Cards */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    cursor: pointer;
    border-left: 5px solid #ddd;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: translate(30%, -30%);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stat-card.total { border-left-color: #667eea; }
.stat-card.pending { border-left-color: #ffc107; }
.stat-card.in-transit { border-left-color: #17a2b8; }
.stat-card.out-for-delivery { border-left-color: #007bff; }
.stat-card.delivered { border-left-color: #28a745; }

.stat-number {
    font-size: 42px;
    font-weight: 900;
    margin: 0;
    color: #2c3e50;
}

.stat-label {
    font-size: 15px;
    color: #7f8c8d;
    margin: 8px 0 0 0;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Filter Section */
.filter-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.filter-title {
    font-size: 20px;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    align-items: end;
}

.filter-group label {
    font-size: 14px;
    font-weight: 700;
    color: #34495e;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-input {
    padding: 14px 16px;
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.3s;
    width: 100%;
}

.filter-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
}

.btn-filter {
    padding: 14px 35px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
    justify-content: center;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102,126,234,0.4);
}

.btn-clear {
    padding: 14px 35px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-clear:hover {
    background: #5a6268;
}

/* Tracking Cards */
.tracking-cards-container {
    display: grid;
    gap: 25px;
}

.tracking-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border-left: 6px solid #ddd;
    position: relative;
}

.tracking-card:hover {
    box-shadow: 0 8px 35px rgba(0,0,0,0.15);
    transform: translateX(5px);
}

.card-header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.doc-number {
    font-size: 26px;
    font-weight: 900;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-badge {
    padding: 10px 20px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d1ecf1; color: #0c5460; }
.status-picked-up { background: #cce5ff; color: #004085; }
.status-in-transit { background: #d1ecf1; color: #0c5460; }
.status-out-for-delivery { background: #cfe2ff; color: #084298; }
.status-delivered { background: #d1e7dd; color: #0f5132; }
.status-failed { background: #f8d7da; color: #842029; }
.status-delayed { background: #f8d7da; color: #842029; }

.card-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.detail-box {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-label {
    font-size: 13px;
    color: #7f8c8d;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.detail-value {
    font-size: 17px;
    color: #2c3e50;
    font-weight: 700;
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.btn-action {
    padding: 15px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
}

.btn-update-status {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.btn-update-status:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(40,167,69,0.4);
}

.btn-view-history {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.btn-view-history:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,123,255,0.4);
}

.btn-track-public {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
}

.btn-track-public:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(108,117,125,0.4);
}

.no-records {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.no-records i {
    font-size: 100px;
    color: #e1e8ed;
    margin-bottom: 25px;
}

.no-records h3 {
    color: #7f8c8d;
    font-size: 24px;
    margin: 0 0 10px 0;
}

/* Alert Messages */
.alert {
    padding: 18px 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    font-size: 16px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left: 5px solid #28a745;
}

/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.modal-content-tracking {
    background: white;
    border-radius: 20px;
    padding: 40px;
    max-width: 700px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header-tracking {
    margin-bottom: 30px;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 20px;
}

.modal-header-tracking h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 900;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-doc-info {
    margin-top: 10px;
    color: #7f8c8d;
    font-size: 16px;
}

.modal-doc-info strong {
    color: #667eea;
    font-weight: 800;
}

.form-group-tracking {
    margin-bottom: 25px;
}

.form-group-tracking label {
    display: block;
    font-weight: 800;
    color: #34495e;
    margin-bottom: 12px;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-control-tracking {
    width: 100%;
    padding: 16px 18px;
    border: 2px solid #e1e8ed;
    border-radius: 12px;
    font-size: 16px;
    transition: all 0.3s;
    font-family: inherit;
}

.form-control-tracking:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
}

.modal-buttons {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-modal-submit {
    flex: 1;
    padding: 18px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-modal-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40,167,69,0.4);
}

.btn-modal-cancel {
    flex: 1;
    padding: 18px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-modal-cancel:hover {
    background: #5a6268;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .page-header h1 {
        font-size: 26px;
    }
    
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .card-header-section {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .doc-number {
        font-size: 22px;
    }
    
    .modal-content-tracking {
        padding: 25px;
        width: 95%;
    }
}

/* Loading Animation */
.loading-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <div class="right_col" role="main">
        <div class="tracking-page-container">
          
          <!-- Page Header -->
          <div class="page-header">
            <h1>
              <i class="fas fa-location-arrow"></i>
              Tracking Management System
            </h1>
            <p>📦 Track and manage all shipments in real-time with complete visibility</p>
          </div>

          <!-- Success Message -->
          <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle" style="font-size: 24px;"></i>
            <span><?php echo htmlspecialchars($_GET['success']); ?></span>
          </div>
          <?php endif; ?>

          <!-- Stats Cards -->
          <div class="stats-row">
            <div class="stat-card total" onclick="filterByStatus('')">
              <p class="stat-number"><?php echo $counts['total'] ?? 0; ?></p>
              <p class="stat-label">📦 Total Shipments</p>
            </div>
            <div class="stat-card pending" onclick="filterByStatus('Pending')">
              <p class="stat-number"><?php echo $counts['pending'] ?? 0; ?></p>
              <p class="stat-label">⏳ Pending</p>
            </div>
            <div class="stat-card in-transit" onclick="filterByStatus('In Transit')">
              <p class="stat-number"><?php echo $counts['in_transit'] ?? 0; ?></p>
              <p class="stat-label">🚚 In Transit</p>
            </div>
            <div class="stat-card out-for-delivery" onclick="filterByStatus('Out for Delivery')">
              <p class="stat-number"><?php echo $counts['out_for_delivery'] ?? 0; ?></p>
              <p class="stat-label">📍 Out for Delivery</p>
            </div>
            <div class="stat-card delivered" onclick="filterByStatus('Delivered')">
              <p class="stat-number"><?php echo $counts['delivered'] ?? 0; ?></p>
              <p class="stat-label">✅ Delivered</p>
            </div>
          </div>

          <!-- Filter Section -->
          <div class="filter-section">
            <div class="filter-title">
              <i class="fas fa-filter"></i>
              Search & Filter Shipments
            </div>
            <form method="GET" action="">
              <div class="filter-grid">
                <div class="filter-group">
                  <label>
                    <i class="fas fa-search"></i>
                    Search Document
                  </label>
                  <input type="text" name="search" class="filter-input" 
                         placeholder="Doc No., Company, or Client"
                         value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                  <label>
                    <i class="fas fa-flag"></i>
                    Status
                  </label>
                  <select name="status_filter" class="filter-input">
                    <option value="">All Statuses</option>
                    <?php foreach ($available_statuses as $status): ?>
                    <option value="<?= htmlspecialchars($status['status_name']) ?>" 
                            <?= $status_filter == $status['status_name'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($status['status_label']) ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                
                <div class="filter-group">
                  <label>
                    <i class="fas fa-calendar"></i>
                    From Date
                  </label>
                  <input type="date" name="date_from" class="filter-input" value="<?php echo $date_from; ?>">
                </div>
                
                <div class="filter-group">
                  <label>
                    <i class="fas fa-calendar"></i>
                    To Date
                  </label>
                  <input type="date" name="date_to" class="filter-input" value="<?php echo $date_to; ?>">
                </div>
                
                <div class="filter-group">
                  <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i>
                    Search
                  </button>
                </div>
                
                <div class="filter-group">
                  <a href="tracking_management.php" class="btn-clear">
                    <i class="fas fa-times"></i>
                    Clear
                  </a>
                </div>
              </div>
            </form>
          </div>

          <!-- Tracking Cards -->
          <div class="tracking-cards-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <div class="tracking-card">
                <div class="card-header-section">
                  <div class="doc-number">
                    <i class="fas fa-barcode"></i>
                    <?php echo htmlspecialchars($row['doc_no'] ?? 'N/A'); ?>
                  </div>
                  <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'] ?? 'pending')); ?>">
                    <?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?>
                  </span>
                </div>
                
                <div class="card-details-grid">
                  <div class="detail-box">
                    <div class="detail-label">
                      <i class="fas fa-building"></i>
                      Company
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($row['company_name'] ?? 'N/A'); ?></div>
                  </div>
                  
                  <div class="detail-box">
                    <div class="detail-label">
                      <i class="fas fa-user"></i>
                      Client
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($row['client_name'] ?? 'N/A'); ?></div>
                  </div>
                  
                  <div class="detail-box">
                    <div class="detail-label">
                      <i class="fas fa-map-marker-alt"></i>
                      Current Location
                    </div>
                    <div class="detail-value">
                      <?php 
                      // Try to get latest location from tracking history
                      $latest_location_query = "SELECT location FROM tbl_tracking_history 
                                               WHERE doc_no = '" . mysqli_real_escape_string($conn, $row['doc_no']) . "' 
                                               AND location IS NOT NULL AND location != '' 
                                               ORDER BY created_at DESC LIMIT 1";
                      $latest_location_result = @mysqli_query($conn, $latest_location_query);
                      $latest_location = 'Not Updated';
                      if ($latest_location_result && mysqli_num_rows($latest_location_result) > 0) {
                          $loc_row = mysqli_fetch_assoc($latest_location_result);
                          $latest_location = $loc_row['location'];
                      }
                      echo htmlspecialchars($latest_location);
                      ?>
                    </div>
                  </div>
                  
                  <div class="detail-box">
                    <div class="detail-label">
                      <i class="fas fa-calendar-check"></i>
                      Created Date
                    </div>
                    <div class="detail-value">
                      <?php echo !empty($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : 'N/A'; ?>
                    </div>
                  </div>
                </div>
                
                <div class="action-buttons">
                  <button class="btn-action btn-update-status" 
                          onclick="openTrackingModal('<?= htmlspecialchars($row['doc_no']) ?>', '<?= htmlspecialchars($row['source']) ?>', <?= $row['id'] ?>)">
                    <i class="fas fa-edit"></i>
                    Update Status
                  </button>
                  <a href="tracking_history.php?doc_no=<?= urlencode($row['doc_no']) ?>" class="btn-action btn-view-history">
                    <i class="fas fa-history"></i>
                    View History
                  </a>
                  <a href="../track_shipment.php?doc_no=<?= urlencode($row['doc_no']) ?>" target="_blank" class="btn-action btn-track-public">
                    <i class="fas fa-external-link-alt"></i>
                    Public View
                  </a>
                </div>
              </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="no-records">
                <i class="fas fa-inbox"></i>
                <h3>No shipments found</h3>
                <p>Try adjusting your search filters</p>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
      
      <?php require 'footer.php'; ?>
    </div>
  </div>

<!-- Tracking Update Modal -->
<div id="trackingModal" class="modal-overlay">
  <div class="modal-content-tracking">
    <div class="modal-header-tracking">
      <h2>
        <i class="fas fa-location-arrow"></i>
        Update Tracking Status
      </h2>
      <p class="modal-doc-info">
        Document: <strong id="modalDocNo"></strong>
      </p>
    </div>
    
    <form id="trackingUpdateForm">
      <input type="hidden" name="doc_no" id="formDocNo">
      <input type="hidden" name="source" id="formSource">
      <input type="hidden" name="id" id="formId">
      
      <div class="form-group-tracking">
        <label>
          <i class="fas fa-flag"></i> Select Status <span style="color: #e74c3c;">*</span>
        </label>
        <select name="status" id="formStatus" required class="form-control-tracking">
          <option value="">-- Choose Status --</option>
          <?php foreach ($available_statuses as $status): ?>
          <option value="<?= htmlspecialchars($status['status_name']) ?>">
            <?= htmlspecialchars($status['status_label']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="form-group-tracking">
        <label>
          <i class="fas fa-map-marker-alt"></i> Current Location
        </label>
        <input type="text" name="location" class="form-control-tracking" 
               placeholder="e.g., Mumbai Warehouse, Delhi Hub">
      </div>
      
      <div class="form-group-tracking">
        <label>
          <i class="fas fa-comment-alt"></i> Notes / Remarks
        </label>
        <textarea name="notes" rows="4" class="form-control-tracking" 
                  placeholder="Add any additional notes about this status update..."></textarea>
      </div>
      
      <div class="modal-buttons">
        <button type="submit" class="btn-modal-submit">
          <i class="fas fa-check"></i> Update Status
        </button>
        <button type="button" onclick="closeTrackingModal()" class="btn-modal-cancel">
          <i class="fas fa-times"></i> Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openTrackingModal(docNo, source, id) {
    document.getElementById('modalDocNo').textContent = docNo;
    document.getElementById('formDocNo').value = docNo;
    document.getElementById('formSource').value = source;
    document.getElementById('formId').value = id;
    document.getElementById('trackingModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeTrackingModal() {
    document.getElementById('trackingModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('trackingUpdateForm').reset();
}

function filterByStatus(status) {
    const url = new URL(window.location.href);
    url.searchParams.set('status_filter', status);
    url.searchParams.delete('search');
    url.searchParams.delete('date_from');
    url.searchParams.delete('date_to');
    window.location.href = url.toString();
}

// Close modal when clicking outside
document.getElementById('trackingModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTrackingModal();
    }
});

// Handle form submission with AJAX
document.getElementById('trackingUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('.btn-modal-submit');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;
    
    fetch('api_tracking_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeTrackingModal();
            window.location.href = 'tracking_management.php?success=' + encodeURIComponent(data.message);
        } else {
            alert('Error: ' + data.message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        console.error('Error:', error);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Auto-hide success message
setTimeout(function() {
    const alert = document.querySelector('.alert-success');
    if (alert) {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.5s';
        setTimeout(() => alert.remove(), 500);
    }
}, 5000);

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTrackingModal();
    }
});
</script>

</body>
</html>
