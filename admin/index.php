<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'top_header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>      
      <!-- page content -->
      <div class="right_col" role="main" style="padding-top:0px !important;">
        
        <!-- Stats Cards Grid -->
        <?php
        function fetch_count($sql) {
          global $conn;
          $res = mysqli_query($conn, $sql);
          if (!$res) {
            die("Query failed: ".mysqli_error($conn)." SQL: $sql");
          }
          $row = mysqli_fetch_assoc($res);
          return $row['c'] ?? 0;
        }
        // 1. Total Docket
        $total_docket = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details");

        // 2. NON-DRS (Registered, status='Pickup', branch_office is NULL/empty)
        $non_drs = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Picked Up' AND (branch_office IS NULL OR branch_office='' OR branch_office=0)");

        // 3. DRS (status='Pickup', branch_office not NULL/empty)
        $drs = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Picked Up' AND branch_office IS NOT NULL AND branch_office<>'' AND branch_office<>0");

        // 4. In Transit
        $intransit = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='In Transit'");

        // 5. Out For Delivery
        $out_for_delivery = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Out for Delivery'");

        // 6. Delivered
        $delivered = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Delivered'");

        // 7. Delayed
        $delayed = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Delayed'");

        // 8. Pending POD (Delivered but proof_of_delivery empty)
        $pending_pod = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Delivered' AND (proof_of_delivery IS NULL OR proof_of_delivery='')");

        // 9. Manifest Count from tbl_manifest
        $manifest_count = fetch_count("SELECT COUNT(*) as c FROM tbl_manifest");
        ?>
        
        <!-- Stats Cards Grid -->
        <div class="stats-grid">
          <!-- Total Dockets Card -->
          <a href="trip.php?type=list_trip" class="stat-card stat-card-dark">
            <div class="stat-icon"><i class="fa fa-th-list"></i></div>
            <div class="stat-content">
              <div class="stat-label">TOTAL DOCKETS</div>
              <div class="stat-value"><?= $total_docket ?></div>
            </div>
          </a>
          
          <!-- Pending Card -->
          <a href="trip.php?type=list_trip&status=Picked%20Up" class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="fa fa-clock-o"></i></div>
            <div class="stat-content">
              <div class="stat-label">PENDING</div>
              <div class="stat-value"><?= $non_drs ?></div>
            </div>
          </a>
          
          <!-- In Transit Card -->
          <a href="trip.php?type=list_trip&status=In%20Transit" class="stat-card stat-card-info">
            <div class="stat-icon"><i class="fa fa-truck"></i></div>
            <div class="stat-content">
              <div class="stat-label">IN TRANSIT</div>
              <div class="stat-value"><?= $intransit ?></div>
            </div>
          </a>
          
          <!-- Delivered Card -->
          <a href="trip.php?type=list_trip&status=Delivered" class="stat-card stat-card-success">
            <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
            <div class="stat-content">
              <div class="stat-label">DELIVERED</div>
              <div class="stat-value"><?= $delivered ?></div>
            </div>
          </a>
          
          <!-- Manifest Card -->
          <a href="manifest.php" class="stat-card stat-card-manifest">
            <div class="stat-icon"><i class="fa fa-file-text"></i></div>
            <div class="stat-content">
              <div class="stat-label">MANIFEST</div>
              <div class="stat-value"><?= $manifest_count ?></div>
            </div>
          </a>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-section">
          <input type="text" class="search-input" placeholder="Search by tracking number, sender, receiver..." id="searchInput">
          <select class="status-filter" id="statusFilter">
            <option value="">All Status</option>
            <option value="Picked Up">Picked Up</option>
            <option value="In Transit">In Transit</option>
            <option value="Out for Delivery">Out for Delivery</option>
            <option value="Delivered">Delivered</option>
            <option value="Delayed">Delayed</option>
          </select>
          <button class="btn-search" onclick="searchDockets()">
            <i class="fa fa-search"></i> Search
          </button>
          <button class="btn-reset" onclick="resetSearch()">
            <i class="fa fa-refresh"></i> Reset
          </button>
        </div>

        <!-- Dockets List Table -->
        <div class="dockets-section">
          <div class="dockets-header">
            <i class="fa fa-list"></i> Dockets List
          </div>
          <div class="table-responsive">
            <table class="dockets-table">
              <thead>
                <tr>
                  <th>Tracking #</th>
                  <th>Sender</th>
                  <th>Receiver</th>
                  <th>Service Type</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="docketsTableBody">
                <?php
                // First check if tables exist
                $tables_exist = true;
                $check_consignor = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_consignor'");
                $check_client = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_client'");
                
                if(mysqli_num_rows($check_consignor) > 0 && mysqli_num_rows($check_client) > 0) {
                  $sql = "SELECT sd.*, c.consignor_name, cl.client_name 
                          FROM tbl_shipping_details sd
                          LEFT JOIN tbl_consignor c ON sd.consignor_id = c.consignor_id
                          LEFT JOIN tbl_client cl ON sd.client_id = cl.client_id
                          ORDER BY sd.shipping_id DESC LIMIT 20";
                } else {
                  // If tables don't exist, just get shipping details
                  $sql = "SELECT * FROM tbl_shipping_details ORDER BY shipping_id DESC LIMIT 20";
                  $tables_exist = false;
                }
                
                $result = mysqli_query($conn, $sql);
                
                if(!$result) {
                  echo '<tr><td colspan="7" style="text-align:center;padding:40px;color:#e74c3c;">
                        <strong>Database Error:</strong> '.htmlspecialchars(mysqli_error($conn)).'</td></tr>';
                } else if(mysqli_num_rows($result) > 0) {
                  while($row = mysqli_fetch_assoc($result)) {
                    $status_class = '';
                    switch($row['status']) {
                      case 'In Transit': $status_class = 'status-transit'; break;
                      case 'Delivered': $status_class = 'status-delivered'; break;
                      case 'Picked Up': $status_class = 'status-pending'; break;
                      case 'Out for Delivery': $status_class = 'status-out'; break;
                      case 'Delayed': $status_class = 'status-delayed'; break;
                      default: $status_class = 'status-default';
                    }
                    $created_date = date('M d, Y g:i A', strtotime($row['created_at'] ?? date('Y-m-d H:i:s')));
                    
                    // Get sender and receiver based on table availability
                    $sender_name = 'N/A';
                    $receiver_name = 'N/A';
                    
                    if($tables_exist) {
                      $sender_name = $row['consignor_name'] ?? 'N/A';
                      $receiver_name = $row['client_name'] ?? 'N/A';
                    } else {
                      // Fallback to direct fields if they exist
                      $sender_name = $row['sender_name'] ?? $row['consignor_name'] ?? 'N/A';
                      $receiver_name = $row['receiver_name'] ?? $row['client_name'] ?? 'N/A';
                    }
                    ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($row['doc_no'] ?? $row['shipping_id']) ?></strong></td>
                      <td><?= htmlspecialchars($sender_name) ?></td>
                      <td><?= htmlspecialchars($receiver_name) ?></td>
                      <td><?= htmlspecialchars($row['service_type'] ?? 'Standard') ?></td>
                      <td><span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($row['status'] ?? 'Pending') ?></span></td>
                      <td><?= $created_date ?></td>
                      <td>
                        <div class="action-buttons">
                          <a href="trip.php?type=view_trip&id=<?= $row['shipping_id'] ?>" class="action-btn btn-view" title="View">
                            <i class="fa fa-eye"></i>
                          </a>
                          <a href="trip.php?type=edit_trip&id=<?= $row['shipping_id'] ?>" class="action-btn btn-edit" title="Edit">
                            <i class="fa fa-edit"></i>
                          </a>
                          <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['shipping_id'] ?>)" class="action-btn btn-delete" title="Delete">
                            <i class="fa fa-trash"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <?php
                  }
                } else {
                  echo '<tr><td colspan="7" style="text-align:center;padding:50px;font-size:1.1rem;color:#7f8c8d;">
                        <i class="fa fa-inbox" style="font-size:3rem;display:block;margin-bottom:15px;opacity:0.5;"></i>
                        <strong>No dockets found</strong></td></tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
        <!-- END Dashboard Content -->
      </div>
    </div>
  </div>
  <?php require 'footer.php';?>
  
  <script>
  function searchDockets() {
    var search = document.getElementById('searchInput').value.toLowerCase();
    var status = document.getElementById('statusFilter').value.toLowerCase();
    var rows = document.querySelectorAll('#docketsTableBody tr');
    
    rows.forEach(function(row) {
      var text = row.textContent.toLowerCase();
      var statusMatch = !status || text.includes(status);
      var searchMatch = !search || text.includes(search);
      
      if(statusMatch && searchMatch) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  }
  
  function resetSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = '';
    var rows = document.querySelectorAll('#docketsTableBody tr');
    rows.forEach(function(row) {
      row.style.display = '';
    });
  }
  
  function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this docket?')) {
      window.location.href = 'trip.php?type=delete_trip&id=' + id;
    }
  }
  </script>
  
  <style>
/* Modern Dashboard Styles */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Helvetica Neue', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.right_col { 
  background: linear-gradient(135deg, #e8f0f7 0%, #f5f8fb 100%) !important; 
  min-height: 100vh; 
  padding: 0 !important;
}

/* Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 25px;
  padding: 0 35px;
  margin-bottom: 35px;
}

.stat-card {
  background: #fff;
  border-radius: 20px;
  padding: 30px 25px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  border-left: 5px solid;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  gap: 20px;
  text-decoration: none !important;
  color: inherit !important;
  cursor: pointer;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 100px;
  height: 100px;
  background: linear-gradient(135deg, rgba(255,255,255,0.3), transparent);
  border-radius: 50%;
  transform: translate(30%, -30%);
  transition: all 0.4s;
}

.stat-card:hover {
  transform: translateY(-10px) scale(1.03);
  box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

.stat-card:hover::before {
  transform: translate(20%, -20%) scale(1.5);
}

.stat-card-dark {
  border-left-color: #2c3e50;
  background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
}

.stat-card-dark:hover {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: #fff;
}

.stat-card-dark:hover .stat-label,
.stat-card-dark:hover .stat-value {
  color: #fff;
}

.stat-card-dark:hover .stat-icon {
  background: rgba(255,255,255,0.2);
  color: #fff;
}

.stat-card-warning {
  border-left-color: #f39c12;
  background: linear-gradient(135deg, #fff 0%, #fffbf0 100%);
}

.stat-card-warning:hover {
  background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
  color: #fff;
}

.stat-card-warning:hover .stat-label,
.stat-card-warning:hover .stat-value {
  color: #fff;
}

.stat-card-warning:hover .stat-icon {
  background: rgba(255,255,255,0.2);
  color: #fff;
}

.stat-card-info {
  border-left-color: #3498db;
  background: linear-gradient(135deg, #fff 0%, #f0f8ff 100%);
}

.stat-card-info:hover {
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
  color: #fff;
}

.stat-card-info:hover .stat-label,
.stat-card-info:hover .stat-value {
  color: #fff;
}

.stat-card-info:hover .stat-icon {
  background: rgba(255,255,255,0.2);
  color: #fff;
}

.stat-card-success {
  border-left-color: #27ae60;
  background: linear-gradient(135deg, #fff 0%, #f0fff4 100%);
}

.stat-card-success:hover {
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
  color: #fff;
}

.stat-card-success:hover .stat-label,
.stat-card-success:hover .stat-value {
  color: #fff;
}

.stat-card-success:hover .stat-icon {
  background: rgba(255,255,255,0.2);
  color: #fff;
}

.stat-card-manifest {
  border-left-color: #9b59b6;
  background: linear-gradient(135deg, #fff 0%, #f9f0ff 100%);
}

.stat-card-manifest:hover {
  background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
  color: #fff;
}

.stat-card-manifest:hover .stat-label,
.stat-card-manifest:hover .stat-value {
  color: #fff;
}

.stat-card-manifest:hover .stat-icon {
  background: rgba(255,255,255,0.2);
  color: #fff;
}

.stat-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  background: rgba(0,0,0,0.05);
  color: #2c3e50;
  transition: all 0.4s;
  flex-shrink: 0;
}

.stat-card-dark .stat-icon {
  background: rgba(44,62,80,0.1);
  color: #2c3e50;
}

.stat-card-warning .stat-icon {
  background: rgba(243,156,18,0.1);
  color: #f39c12;
}

.stat-card-info .stat-icon {
  background: rgba(52,152,219,0.1);
  color: #3498db;
}

.stat-card-success .stat-icon {
  background: rgba(39,174,96,0.1);
  color: #27ae60;
}

.stat-card-manifest .stat-icon {
  background: rgba(155,89,182,0.1);
  color: #9b59b6;
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 1rem;
  color: #6c757d;
  font-weight: 700;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 2px;
  transition: color 0.4s;
  font-family: 'Inter', sans-serif;
}

.stat-value {
  font-size: 4.5rem;
  font-weight: 900;
  color: #2c3e50;
  line-height: 1;
  transition: color 0.4s;
  font-family: 'Inter', sans-serif;
  letter-spacing: -2px;
}

/* Search Section */
.search-section {
  background: #fff;
  padding: 25px 35px;
  margin: 0 35px 25px 35px;
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  display: flex;
  gap: 15px;
  align-items: center;
  flex-wrap: wrap;
}

.search-input {
  flex: 1;
  min-width: 300px;
  padding: 16px 24px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 1.2rem;
  transition: all 0.3s;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
}

.search-input:focus {
  border-color: #3498db;
  outline: none;
  box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
}

.status-filter {
  padding: 16px 24px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 1.2rem;
  background: #fff;
  cursor: pointer;
  transition: all 0.3s;
  font-family: 'Inter', sans-serif;
  font-weight: 600;
}

.status-filter:focus {
  border-color: #3498db;
  outline: none;
}

.btn-search {
  padding: 16px 36px;
  background: #3498db;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 1.2rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: 'Inter', sans-serif;
  letter-spacing: 0.5px;
}

.btn-search:hover {
  background: #2980b9;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52,152,219,0.3);
}

.btn-reset {
  padding: 16px 36px;
  background: #95a5a6;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 1.2rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: 'Inter', sans-serif;
  letter-spacing: 0.5px;
}

.btn-reset:hover {
  background: #7f8c8d;
  transform: translateY(-2px);
}

/* Dockets Section */
.dockets-section {
  background: #fff;
  margin: 0 35px 35px 35px;
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  overflow: hidden;
}

.dockets-header {
  background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
  color: #fff;
  padding: 24px 32px;
  font-size: 1.5rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  gap: 14px;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  font-family: 'Inter', sans-serif;
}

.table-responsive {
  overflow-x: auto;
}

.dockets-table {
  width: 100%;
  border-collapse: collapse;
}

.dockets-table thead {
  background: #34495e;
  color: #fff;
}

.dockets-table thead th {
  padding: 20px 24px;
  text-align: left;
  font-weight: 800;
  font-size: 1.1rem;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: #fff;
  font-family: 'Inter', sans-serif;
}

.dockets-table tbody tr {
  border-bottom: 1px solid #ecf0f1;
  transition: all 0.2s;
}

.dockets-table tbody tr:hover {
  background: #f8f9fa;
}

.dockets-table tbody td {
  padding: 22px 24px;
  font-size: 1.2rem;
  color: #2c3e50;
  font-weight: 600;
  font-family: 'Inter', sans-serif;
}

.dockets-table tbody td strong {
  font-weight: 800;
  color: #1a1a1a;
  letter-spacing: 0.3px;
}

/* Status Badges */
.status-badge {
  padding: 10px 20px;
  border-radius: 22px;
  font-size: 1.05rem;
  font-weight: 700;
  display: inline-block;
  text-align: center;
  font-family: 'Inter', sans-serif;
  letter-spacing: 0.5px;
}

.status-transit {
  background: #d4edff;
  color: #0066cc;
}

.status-delivered {
  background: #d4f4dd;
  color: #0d7d2d;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-out {
  background: #e7f3ff;
  color: #004085;
}

.status-delayed {
  background: #f8d7da;
  color: #721c24;
}

.status-default {
  background: #e9ecef;
  color: #495057;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  transition: all 0.2s;
  border: none;
  cursor: pointer;
}

.btn-view {
  background: #3498db;
  color: #fff;
}

.btn-view:hover {
  background: #2980b9;
  color: #fff;
  transform: scale(1.1);
}

.btn-edit {
  background: #f39c12;
  color: #fff;
}

.btn-edit:hover {
  background: #e67e22;
  color: #fff;
  transform: scale(1.1);
}

.btn-delete {
  background: #e74c3c;
  color: #fff;
}

.btn-delete:hover {
  background: #c0392b;
  color: #fff;
  transform: scale(1.1);
}

/* Responsive Styles */
@media (max-width: 1200px) {
  .stats-grid { 
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }
  .stat-card { padding: 28px 22px; }
  .stat-icon { width: 70px; height: 70px; font-size: 2.2rem; }
  .stat-label { font-size: 0.8rem; }
  .stat-value { font-size: 3.2rem; }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
    padding: 0 15px;
    gap: 15px;
  }
  
  .stat-card {
    padding: 22px 20px;
  }
  
  .stat-icon {
    width: 65px;
    height: 65px;
    font-size: 2rem;
  }
  
  .stat-label {
    font-size: 0.75rem;
  }
  
  .stat-value {
    font-size: 2.8rem;
  }
  
  .search-section {
    flex-direction: column;
    padding: 20px;
    margin: 0 15px 15px 15px;
  }
  
  .search-input {
    width: 100%;
    min-width: auto;
    font-size: 1rem;
  }
  
  .status-filter, .btn-search, .btn-reset {
    width: 100%;
    font-size: 1rem;
  }
  
  .dockets-section {
    margin: 0 15px 15px 15px;
  }
  
  .dockets-header {
    font-size: 1.1rem;
    padding: 18px 20px;
  }
  
  .dockets-table thead th,
  .dockets-table tbody td {
    padding: 14px 12px;
    font-size: 0.95rem;
  }
}

@media (max-width: 576px) {
  .stat-icon { width: 60px; height: 60px; font-size: 1.8rem; }
  .stat-value { font-size: 2.4rem; }
  .stat-label { font-size: 0.7rem; }
  .dockets-table { font-size: 0.9rem; }
  .dockets-table thead th,
  .dockets-table tbody td {
    font-size: 0.9rem;
    padding: 12px 10px;
  }
  .status-badge { font-size: 0.85rem; padding: 6px 14px; }
  .action-buttons { flex-direction: column; gap: 5px; }
  .action-btn { width: 32px; height: 32px; }
}
  </style>
</body>
