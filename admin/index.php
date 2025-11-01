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
        <!-- Modern Header with Action Buttons -->
        <div class="modern-header">
          <div class="header-left">
            <i class="fa fa-truck header-icon"></i>
            <h1 class="header-title">Logistics CMS</h1>
          </div>
          <div class="header-actions">
            <a href="register.php?type=add_register" class="header-btn btn-users">
              <i class="fa fa-users"></i> Manage Users
            </a>
            <a href="trip.php?type=add_trip" class="header-btn btn-new-docket">
              <i class="fa fa-plus"></i> New Docket
            </a>
            <a href="#" class="header-btn btn-admin">
              <i class="fa fa-user-circle"></i> System Administrator
            </a>
            <a href="logout.php" class="header-btn btn-logout">
              <i class="fa fa-sign-out"></i> Logout
            </a>
          </div>
        </div>
        
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

        // 9. Manifest Count
        $manifest_count = fetch_count("SELECT COUNT(*) as c FROM tbl_shipping_details WHERE status='Manifest Created'");
        ?>
        
        <!-- Stats Cards Grid -->
        <div class="stats-grid">
          <!-- Total Dockets Card -->
          <div class="stat-card stat-card-dark">
            <div class="stat-label">Total Dockets</div>
            <div class="stat-value"><?= $total_docket ?></div>
          </div>
          
          <!-- Pending Card -->
          <div class="stat-card stat-card-warning">
            <div class="stat-label">Pending</div>
            <div class="stat-value"><?= $non_drs ?></div>
          </div>
          
          <!-- In Transit Card -->
          <div class="stat-card stat-card-info">
            <div class="stat-label">In Transit</div>
            <div class="stat-value"><?= $intransit ?></div>
          </div>
          
          <!-- Delivered Card -->
          <div class="stat-card stat-card-success">
            <div class="stat-label">Delivered</div>
            <div class="stat-value"><?= $delivered ?></div>
          </div>
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
                $sql = "SELECT * FROM tbl_shipping_details ORDER BY shipping_id DESC LIMIT 20";
                $result = mysqli_query($conn, $sql);
                if($result && mysqli_num_rows($result) > 0) {
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
                    $created_date = date('M d, Y g:i A', strtotime($row['created_at'] ?? 'now'));
                    ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($row['tracking_no']) ?></strong></td>
                      <td><?= htmlspecialchars($row['sender_name'] ?? 'N/A') ?></td>
                      <td><?= htmlspecialchars($row['receiver_name'] ?? 'N/A') ?></td>
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
                  echo '<tr><td colspan="7" style="text-align:center;padding:40px;">No dockets found</td></tr>';
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
.right_col { 
  background: linear-gradient(135deg, #e8f0f7 0%, #f5f8fb 100%) !important; 
  min-height: 100vh; 
  padding: 0 !important;
}

/* Modern Header */
.modern-header {
  background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
  padding: 18px 35px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  margin-bottom: 30px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 15px;
}

.header-icon {
  font-size: 2rem;
  color: #fff;
  background: rgba(255,255,255,0.2);
  padding: 12px;
  border-radius: 10px;
}

.header-title {
  color: #fff;
  font-size: 1.8rem;
  font-weight: 700;
  margin: 0;
  letter-spacing: 0.5px;
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.header-btn {
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  text-decoration: none !important;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s;
  border: none;
  cursor: pointer;
}

.btn-users {
  background: #ffc107;
  color: #000;
}

.btn-users:hover {
  background: #ffb300;
  color: #000;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255,193,7,0.4);
}

.btn-new-docket {
  background: #fff;
  color: #2b5876;
}

.btn-new-docket:hover {
  background: #f0f0f0;
  color: #2b5876;
  transform: translateY(-2px);
}

.btn-admin {
  background: rgba(255,255,255,0.15);
  color: #fff;
  border: 1px solid rgba(255,255,255,0.3);
}

.btn-admin:hover {
  background: rgba(255,255,255,0.25);
  color: #fff;
}

.btn-logout {
  background: transparent;
  color: #fff;
  border: 1px solid rgba(255,255,255,0.3);
}

.btn-logout:hover {
  background: rgba(255,255,255,0.1);
  color: #fff;
}

/* Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
  padding: 0 35px;
  margin-bottom: 35px;
}

.stat-card {
  background: #fff;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  border-left: 5px solid;
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.stat-card-dark {
  border-left-color: #2c3e50;
}

.stat-card-warning {
  border-left-color: #f39c12;
}

.stat-card-info {
  border-left-color: #3498db;
}

.stat-card-success {
  border-left-color: #27ae60;
}

.stat-label {
  font-size: 1rem;
  color: #7f8c8d;
  font-weight: 600;
  margin-bottom: 10px;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: 700;
  color: #2c3e50;
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
  padding: 12px 20px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.3s;
}

.search-input:focus {
  border-color: #3498db;
  outline: none;
  box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
}

.status-filter {
  padding: 12px 20px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 1rem;
  background: #fff;
  cursor: pointer;
  transition: all 0.3s;
}

.status-filter:focus {
  border-color: #3498db;
  outline: none;
}

.btn-search {
  padding: 12px 30px;
  background: #3498db;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-search:hover {
  background: #2980b9;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(52,152,219,0.3);
}

.btn-reset {
  padding: 12px 30px;
  background: #95a5a6;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
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
  padding: 20px 30px;
  font-size: 1.3rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 12px;
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
  padding: 15px 20px;
  text-align: left;
  font-weight: 600;
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.dockets-table tbody tr {
  border-bottom: 1px solid #ecf0f1;
  transition: all 0.2s;
}

.dockets-table tbody tr:hover {
  background: #f8f9fa;
}

.dockets-table tbody td {
  padding: 18px 20px;
  font-size: 0.95rem;
  color: #2c3e50;
}

/* Status Badges */
.status-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  display: inline-block;
  text-align: center;
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
  .modern-header { padding: 15px 20px; }
  .stats-grid { padding: 0 20px; gap: 20px; }
  .search-section, .dockets-section { margin: 0 20px 20px 20px; }
}

@media (max-width: 992px) {
  .header-title { font-size: 1.4rem; }
  .header-actions { gap: 8px; }
  .header-btn { padding: 8px 15px; font-size: 0.85rem; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
  .modern-header {
    flex-direction: column;
    gap: 15px;
    padding: 15px;
  }
  
  .header-actions {
    width: 100%;
    justify-content: space-between;
  }
  
  .header-btn {
    padding: 8px 12px;
    font-size: 0.8rem;
  }
  
  .header-btn i {
    display: none;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    padding: 0 15px;
  }
  
  .search-section {
    flex-direction: column;
    padding: 20px;
    margin: 0 15px 15px 15px;
  }
  
  .search-input {
    width: 100%;
    min-width: auto;
  }
  
  .status-filter, .btn-search, .btn-reset {
    width: 100%;
  }
  
  .dockets-section {
    margin: 0 15px 15px 15px;
  }
  
  .dockets-header {
    font-size: 1.1rem;
    padding: 15px 20px;
  }
  
  .dockets-table thead th,
  .dockets-table tbody td {
    padding: 12px 10px;
    font-size: 0.85rem;
  }
}

@media (max-width: 576px) {
  .header-icon { font-size: 1.5rem; padding: 8px; }
  .header-title { font-size: 1.2rem; }
  .stat-value { font-size: 2rem; }
  .dockets-table { font-size: 0.8rem; }
  .action-buttons { flex-direction: column; gap: 5px; }
  .action-btn { width: 32px; height: 32px; }
}
  </style>
</body>
