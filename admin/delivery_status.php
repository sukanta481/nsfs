<?php
require 'check_auth.php';
requirePermission('docket_status_update');
require 'conn.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $register_id = intval($_POST['register_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    $remarks = mysqli_real_escape_string($conn, trim($_POST['remarks']));
    $updated_by = $_SESSION['user_id'] ?? $_SESSION['admin_id'];
    
    // Update status
    $update_query = "UPDATE tbl_register SET 
                     status = '$new_status',
                     updated_at = NOW()
                     WHERE register_id = $register_id";
    
    if (mysqli_query($conn, $update_query)) {
        // Try to log the status change (if history table exists)
        $log_query = "INSERT INTO tbl_status_history (register_id, old_status, new_status, remarks, updated_by, updated_at) 
                      VALUES ($register_id, 
                             (SELECT status FROM tbl_register WHERE register_id = $register_id), 
                             '$new_status', 
                             '$remarks', 
                             $updated_by, 
                             NOW())";
        @mysqli_query($conn, $log_query); // Don't fail if history table doesn't exist
        
        header("Location: delivery_status.php?success=Status updated successfully for Docket!");
        exit;
    } else {
        $error = "Error updating status: " . mysqli_error($conn);
    }
}

// Get filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$status_filter = isset($_GET['status_filter']) ? mysqli_real_escape_string($conn, $_GET['status_filter']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$where = [];
if (!empty($search)) {
    $where[] = "(r.docket_no LIKE '%$search%' OR c.company_title LIKE '%$search%' OR r.consignee_name LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $where[] = "r.status = '$status_filter'";
}
if (!empty($date_from)) {
    $where[] = "DATE(r.created_at) >= '$date_from'";
}
if (!empty($date_to)) {
    $where[] = "DATE(r.created_at) <= '$date_to'";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get dockets
$query = "SELECT r.*, c.company_title, c.company_address, o.office_name
          FROM tbl_register r
          LEFT JOIN tbl_company c ON r.company_id = c.company_id
          LEFT JOIN tbl_offices o ON r.office_id = o.office_id
          $where_clause
          ORDER BY r.created_at DESC
          LIMIT 100";

$result = mysqli_query($conn, $query);

// Get status counts
$counts_query = "SELECT 
                 COUNT(*) as total,
                 SUM(CASE WHEN status = 'Pending' OR status IS NULL THEN 1 ELSE 0 END) as pending,
                 SUM(CASE WHEN status = 'In Transit' THEN 1 ELSE 0 END) as in_transit,
                 SUM(CASE WHEN status = 'Out for Delivery' THEN 1 ELSE 0 END) as out_for_delivery,
                 SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered,
                 SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) as failed
                 FROM tbl_register r
                 $where_clause";
$counts_result = mysqli_query($conn, $counts_query);
$counts = mysqli_fetch_assoc($counts_result);

require 'top_header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
body {
    background: #f0f4f8 !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.status-page-container {
    padding: 20px;
    max-width: 100%;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(102,126,234,0.3);
}

.page-header h1 {
    margin: 0;
    font-size: 32px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 15px;
}

.page-header p {
    margin: 10px 0 0 0;
    opacity: 0.95;
    font-size: 16px;
}

/* Stats Cards */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s;
    cursor: pointer;
    border-left: 4px solid #ddd;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.stat-card.pending { border-left-color: #ffc107; }
.stat-card.in-transit { border-left-color: #17a2b8; }
.stat-card.out-for-delivery { border-left-color: #007bff; }
.stat-card.delivered { border-left-color: #28a745; }
.stat-card.failed { border-left-color: #dc3545; }

.stat-number {
    font-size: 36px;
    font-weight: 800;
    margin: 0;
    color: #2c3e50;
}

.stat-label {
    font-size: 14px;
    color: #7f8c8d;
    margin: 5px 0 0 0;
    font-weight: 600;
}

/* Filter Section */
.filter-section {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.filter-title {
    font-size: 18px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 14px;
    font-weight: 600;
    color: #34495e;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.filter-input {
    padding: 12px 15px;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.3s;
}

.filter-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.btn-filter {
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102,126,234,0.4);
}

.btn-clear {
    padding: 12px 30px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.btn-clear:hover {
    background: #5a6268;
}

/* Docket Cards */
.dockets-container {
    display: grid;
    gap: 20px;
}

.docket-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s;
    border-left: 5px solid #ddd;
}

.docket-card:hover {
    box-shadow: 0 6px 25px rgba(0,0,0,0.15);
    transform: translateX(5px);
}

.docket-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.docket-number {
    font-size: 24px;
    font-weight: 800;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.pending { background: #fff3cd; color: #856404; }
.status-badge.in-transit { background: #d1ecf1; color: #0c5460; }
.status-badge.out-for-delivery { background: #cce5ff; color: #004085; }
.status-badge.delivered { background: #d4edda; color: #155724; }
.status-badge.failed { background: #f8d7da; color: #721c24; }

.docket-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-label {
    font-size: 12px;
    color: #7f8c8d;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 5px;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 16px;
    color: #2c3e50;
    font-weight: 600;
}

.detail-icon {
    color: #667eea;
    margin-right: 8px;
    font-size: 16px;
}

.update-button {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.update-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(40,167,69,0.4);
}

.no-records {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.no-records i {
    font-size: 80px;
    color: #e1e8ed;
    margin-bottom: 20px;
}

.no-records h3 {
    color: #7f8c8d;
    margin: 0;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .page-header h1 {
        font-size: 24px;
    }
    
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .docket-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .docket-number {
        font-size: 20px;
    }
}
</style>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <div class="right_col" role="main">
        <div class="status-page-container">
          
          <!-- Page Header -->
          <div class="page-header">
            <h1>
              <i class="fas fa-clipboard-check"></i>
              Update Delivery Status
            </h1>
            <p>✨ Easily update the status of dockets with just one click</p>
          </div>

          <!-- Success/Error Messages -->
          <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle" style="font-size: 20px;"></i>
            <span><?php echo htmlspecialchars($_GET['success']); ?></span>
          </div>
          <?php endif; ?>
          
          <?php if (isset($error)): ?>
          <div class="alert alert-error">
            <i class="fas fa-exclamation-circle" style="font-size: 20px;"></i>
            <span><?php echo $error; ?></span>
          </div>
          <?php endif; ?>

          <!-- Stats Cards -->
          <div class="stats-row">
            <div class="stat-card pending" onclick="filterByStatus('')">
              <p class="stat-number"><?php echo $counts['total'] ?? 0; ?></p>
              <p class="stat-label">📦 Total Dockets</p>
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
            <div class="stat-card failed" onclick="filterByStatus('Failed')">
              <p class="stat-number"><?php echo $counts['failed'] ?? 0; ?></p>
              <p class="stat-label">❌ Failed</p>
            </div>
          </div>

          <!-- Filter Section -->
          <div class="filter-section">
            <div class="filter-title">
              <i class="fas fa-filter"></i>
              Search & Filter
            </div>
            <form method="GET" action="">
              <div class="filter-grid">
                <div class="filter-group">
                  <label>
                    <i class="fas fa-search"></i>
                    Search Docket / Client
                  </label>
                  <input type="text" name="search" class="filter-input" 
                         placeholder="Enter docket number or client name"
                         value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                  <label>
                    <i class="fas fa-flag"></i>
                    Status
                  </label>
                  <select name="status_filter" class="filter-input">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                    <option value="In Transit" <?php echo $status_filter == 'In Transit' ? 'selected' : ''; ?>>🚚 In Transit</option>
                    <option value="Out for Delivery" <?php echo $status_filter == 'Out for Delivery' ? 'selected' : ''; ?>>📍 Out for Delivery</option>
                    <option value="Delivered" <?php echo $status_filter == 'Delivered' ? 'selected' : ''; ?>>✅ Delivered</option>
                    <option value="Failed" <?php echo $status_filter == 'Failed' ? 'selected' : ''; ?>>❌ Failed Delivery</option>
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
                  <a href="delivery_status.php" class="btn-clear" style="text-decoration: none; text-align: center; display: block;">
                    <i class="fas fa-times"></i>
                    Clear
                  </a>
                </div>
              </div>
            </form>
          </div>

          <!-- Dockets List -->
          <div class="dockets-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <div class="docket-card">
                <div class="docket-header">
                  <div class="docket-number">
                    <i class="fas fa-file-alt"></i>
                    <?php echo htmlspecialchars($row['docket_no']); ?>
                  </div>
                  <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $row['status'] ?? 'pending')); ?>">
                    <?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?>
                  </span>
                </div>
                
                <div class="docket-details">
                  <div class="detail-item">
                    <div class="detail-label">
                      <i class="fas fa-building detail-icon"></i>
                      Client Name
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($row['company_title'] ?? 'N/A'); ?></div>
                  </div>
                  
                  <div class="detail-item">
                    <div class="detail-label">
                      <i class="fas fa-user detail-icon"></i>
                      Consignee
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($row['consignee_name'] ?? 'N/A'); ?></div>
                  </div>
                  
                  <div class="detail-item">
                    <div class="detail-label">
                      <i class="fas fa-map-marker-alt detail-icon"></i>
                      Destination
                    </div>
                    <div class="detail-value"><?php echo htmlspecialchars($row['to_location'] ?? 'N/A'); ?></div>
                  </div>
                  
                  <div class="detail-item">
                    <div class="detail-label">
                      <i class="fas fa-calendar detail-icon"></i>
                      Date
                    </div>
                    <div class="detail-value">
                      <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                    </div>
                  </div>
                </div>
                
                <button class="update-button" onclick="openStatusModal(<?php echo $row['register_id']; ?>, '<?php echo htmlspecialchars($row['docket_no']); ?>', '<?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?>')">
                  <i class="fas fa-edit"></i>
                  Update Status
                </button>
              </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="no-records">
                <i class="fas fa-inbox"></i>
                <h3>No dockets found</h3>
                <p>Try adjusting your search criteria</p>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
      
      <?php require 'footer.php'; ?>
    </div>
  </div>

<!-- Status Update Modal -->
<div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: white; border-radius: 20px; padding: 40px; max-width: 600px; width: 90%; box-shadow: 0 10px 50px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">
    <h2 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 28px; font-weight: 800;">
      <i class="fas fa-clipboard-check" style="color: #667eea;"></i>
      Update Status
    </h2>
    <p style="color: #7f8c8d; margin: 0 0 30px 0; font-size: 16px;">
      Docket: <strong id="modalDocketNo" style="color: #667eea;"></strong>
    </p>
    
    <form method="POST" action="" id="statusForm">
      <input type="hidden" name="register_id" id="modalRegisterId">
      
      <div style="margin-bottom: 25px;">
        <label style="display: block; font-weight: 700; color: #34495e; margin-bottom: 12px; font-size: 16px;">
          <i class="fas fa-flag"></i> Select New Status
        </label>
        <select name="status" required class="filter-input" style="width: 100%; font-size: 18px; padding: 15px;">
          <option value="">-- Choose Status --</option>
          <option value="Pending">⏳ Pending</option>
          <option value="In Transit">🚚 In Transit</option>
          <option value="Out for Delivery">📍 Out for Delivery</option>
          <option value="Delivered">✅ Delivered</option>
          <option value="Failed">❌ Failed Delivery</option>
        </select>
      </div>
      
      <div style="margin-bottom: 30px;">
        <label style="display: block; font-weight: 700; color: #34495e; margin-bottom: 12px; font-size: 16px;">
          <i class="fas fa-comment"></i> Remarks (Optional)
        </label>
        <textarea name="remarks" rows="3" class="filter-input" style="width: 100%; resize: vertical;" placeholder="Add any notes or comments..."></textarea>
      </div>
      
      <div style="display: flex; gap: 15px;">
        <button type="submit" name="update_status" style="flex: 1; padding: 15px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 10px; font-size: 18px; font-weight: 700; cursor: pointer;">
          <i class="fas fa-check"></i> Update Status
        </button>
        <button type="button" onclick="closeStatusModal()" style="flex: 1; padding: 15px; background: #6c757d; color: white; border: none; border-radius: 10px; font-size: 18px; font-weight: 700; cursor: pointer;">
          <i class="fas fa-times"></i> Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openStatusModal(registerId, docketNo, currentStatus) {
    document.getElementById('modalRegisterId').value = registerId;
    document.getElementById('modalDocketNo').textContent = docketNo;
    document.getElementById('statusModal').style.display = 'flex';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}

function filterByStatus(status) {
    const url = new URL(window.location.href);
    url.searchParams.set('status_filter', status);
    window.location.href = url.toString();
}

// Close modal when clicking outside
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStatusModal();
    }
});

// Auto-hide success message after 5 seconds
setTimeout(function() {
    const alert = document.querySelector('.alert-success');
    if (alert) {
        alert.style.opacity = '0';
        alert.style.transition = 'opacity 0.5s';
        setTimeout(() => alert.remove(), 500);
    }
}, 5000);
</script>

</body>
</html>
