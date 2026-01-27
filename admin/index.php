<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'top_header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- Chart.js for charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>      
      <!-- page content -->
      <div class="right_col" role="main" style="padding-top:0px !important;">
        
        <!-- Stats Cards Grid -->
        <?php
        // Get office filter for branch-based access control
        $dashboardOfficeFilter = getOfficeFilter('dd');
        // Get creator filter for users who can only see their own dockets
        $dashboardCreatorFilter = getCreatorFilter('dd');
        // Combine filters
        $combinedFilter = $dashboardOfficeFilter . $dashboardCreatorFilter;
        $officeFilterClause = !empty($combinedFilter) ? "WHERE 1=1 $combinedFilter" : "";
        $officeFilterAnd = !empty($combinedFilter) ? $combinedFilter : "";
        
        function fetch_count($sql) {
          global $conn;
          $res = @mysqli_query($conn, $sql);
          if (!$res) {
            // Return 0 if query fails (table doesn't exist, etc.)
            return 0;
          }
          $row = mysqli_fetch_assoc($res);
          return $row['c'] ?? 0;
        }
        
        // 1. Total Docket (filtered by office)
        $total_docket = fetch_count("SELECT COUNT(*) as c FROM docket_details dd $officeFilterClause");

        // 2. NON-DRS (Registered, status='Picked Up')
        $non_drs = fetch_count("SELECT COUNT(*) as c FROM docket_details dd WHERE status='Picked Up' $officeFilterAnd");

        // 3. In Transit
        $intransit = fetch_count("SELECT COUNT(*) as c FROM docket_details dd WHERE status='In Transit' $officeFilterAnd");

        // 4. Out For Delivery
        $out_for_delivery = fetch_count("SELECT COUNT(*) as c FROM docket_details dd WHERE status='Out for Delivery' $officeFilterAnd");

        // 5. Delivered
        $delivered = fetch_count("SELECT COUNT(*) as c FROM docket_details dd WHERE status='Delivered' $officeFilterAnd");

        // 6. Delayed
        $delayed = fetch_count("SELECT COUNT(*) as c FROM docket_details dd WHERE status='Delayed' $officeFilterAnd");

        // 7. Pending POD (status='Pending POD' OR Delivered but proof_of_delivery empty)
        $pending_pod = fetch_count("SELECT COUNT(*) as c FROM docket_details dd WHERE (status='Pending POD' OR (status='Delivered' AND (proof_of_delivery IS NULL OR proof_of_delivery=''))) $officeFilterAnd");

        // 8. Manifest Count from tbl_manifest (filtered by office)
        $manifestOfficeFilter = getOfficeFilter('m');
        $manifestFilterClause = !empty($manifestOfficeFilter) ? "WHERE 1=1 $manifestOfficeFilter" : "";
        $manifest_count = fetch_count("SELECT COUNT(*) as c FROM tbl_manifest m $manifestFilterClause");

        // ============ ADDITIONAL DATA FOR CHARTS ============
        
        // Monthly docket trend (last 12 months)
        $monthlyDockets = [];
        $monthLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd = date('Y-m-t', strtotime("-$i months"));
            $monthLabels[] = date('M', strtotime("-$i months"));
            $count = fetch_count("SELECT COUNT(*) as c FROM docket_details dd WHERE created_at >= '$monthStart' AND created_at <= '$monthEnd 23:59:59' $officeFilterAnd");
            $monthlyDockets[] = $count;
        }

        // Monthly delivered trend (last 12 months)
        $monthlyDelivered = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd = date('Y-m-t', strtotime("-$i months"));
            $count = fetch_count("SELECT COUNT(*) as c FROM docket_details dd WHERE status='Delivered' AND created_at >= '$monthStart' AND created_at <= '$monthEnd 23:59:59' $officeFilterAnd");
            $monthlyDelivered[] = $count;
        }

        // Service type distribution
        $serviceTypes = [];
        $serviceTypeCounts = [];
        $serviceQuery = "SELECT COALESCE(service_type, 'Standard') as stype, COUNT(*) as cnt FROM docket_details dd " . 
                       ($officeFilterClause ? $officeFilterClause : "WHERE 1=1") . 
                       " GROUP BY COALESCE(service_type, 'Standard') ORDER BY cnt DESC LIMIT 5";
        $serviceResult = @mysqli_query($conn, $serviceQuery);
        if ($serviceResult) {
            while ($row = mysqli_fetch_assoc($serviceResult)) {
                $serviceTypes[] = $row['stype'] ?: 'Standard';
                $serviceTypeCounts[] = (int)$row['cnt'];
            }
        }

        // Top companies by dockets
        $topCompanies = [];
        $topCompanyCounts = [];
        $companyQuery = "SELECT COALESCE(company_name, 'Unknown') as cname, COUNT(*) as cnt FROM docket_details dd " . 
                       ($officeFilterClause ? $officeFilterClause : "WHERE 1=1") . 
                       " GROUP BY company_name ORDER BY cnt DESC LIMIT 8";
        $companyResult = @mysqli_query($conn, $companyQuery);
        if ($companyResult) {
            while ($row = mysqli_fetch_assoc($companyResult)) {
                $topCompanies[] = substr($row['cname'] ?: 'Unknown', 0, 15);
                $topCompanyCounts[] = (int)$row['cnt'];
            }
        }

        // Today's stats
        $today = date('Y-m-d');
        $todayTotal = fetch_count("SELECT COUNT(*) as c FROM docket_details dd WHERE DATE(created_at) = '$today' $officeFilterAnd");
        $todayDelivered = fetch_count("SELECT COUNT(*) as c FROM docket_details dd WHERE DATE(created_at) = '$today' AND status='Delivered' $officeFilterAnd");

        // This week stats
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekTotal = fetch_count("SELECT COUNT(*) as c FROM docket_details dd WHERE created_at >= '$weekStart' $officeFilterAnd");

        // Calculate delivery rate
        $deliveryRate = $total_docket > 0 ? round(($delivered / $total_docket) * 100, 1) : 0;
        
        // Show office info if user is restricted to specific office
        $userOffice = getUserOffice();
        ?>
        
        <?php if ($userOffice && !isSuperAdmin() && empty($_SESSION['can_access_all_offices'])): ?>
        <div class="office-indicator">
            <i class="fa fa-building"></i>
            <span>
                <strong>Viewing data for:</strong> <?php echo htmlspecialchars($userOffice['office_name']); ?>
                <?php if ($userOffice['office_address']): ?>
                <span style="opacity: 0.8;"> - <?php echo htmlspecialchars($userOffice['office_address']); ?></span>
                <?php endif; ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1><i class="fa fa-dashboard"></i> ANALYTICS DASHBOARD</h1>
                <p>Real-time logistics overview</p>
            </div>
            <div class="dashboard-filters">
                <span class="filter-badge"><i class="fa fa-calendar"></i> <?= date('F Y') ?></span>
                <span class="filter-badge"><i class="fa fa-refresh"></i> Live</span>
            </div>
        </div>

        <!-- Top KPI Cards Row -->
        <div class="kpi-row">
          <?php if (hasPermission('dashboard_view_total_dockets')): ?>
          <a href="register.php?type=list_register" class="kpi-card">
            <div class="kpi-label">Total Dockets</div>
            <div class="kpi-value"><?= number_format($total_docket) ?></div>
            <div class="kpi-trend up"><i class="fa fa-arrow-up"></i> All Time</div>
          </a>
          <?php endif; ?>

          <?php if (hasPermission('dashboard_view_delivered')): ?>
          <a href="register.php?type=list_register&status=Delivered" class="kpi-card kpi-success">
            <div class="kpi-label">Delivered</div>
            <div class="kpi-value"><?= number_format($delivered) ?></div>
            <div class="kpi-trend up"><i class="fa fa-check-circle"></i> <?= $deliveryRate ?>% Rate</div>
          </a>
          <?php endif; ?>

          <?php if (hasPermission('dashboard_view_in_transit')): ?>
          <a href="register.php?type=list_register&status=In%20Transit" class="kpi-card kpi-info">
            <div class="kpi-label">In Transit</div>
            <div class="kpi-value"><?= number_format($intransit) ?></div>
            <div class="kpi-trend"><i class="fa fa-truck"></i> Active</div>
          </a>
          <?php endif; ?>

          <div class="kpi-card kpi-today">
            <div class="kpi-label">Today's Dockets</div>
            <div class="kpi-value"><?= number_format($todayTotal) ?></div>
            <div class="kpi-trend"><i class="fa fa-calendar-check-o"></i> <?= $todayDelivered ?> delivered</div>
          </div>

          <div class="kpi-card kpi-week">
            <div class="kpi-label">This Week</div>
            <div class="kpi-value"><?= number_format($weekTotal) ?></div>
            <div class="kpi-trend"><i class="fa fa-bar-chart"></i> Weekly</div>
          </div>

          <?php if (hasPermission('dashboard_view_delayed')): ?>
          <a href="register.php?type=list_register&status=Delayed" class="kpi-card kpi-danger">
            <div class="kpi-label">Delayed</div>
            <div class="kpi-value"><?= number_format($delayed) ?></div>
            <div class="kpi-trend down"><i class="fa fa-exclamation-triangle"></i> Needs Attention</div>
          </a>
          <?php endif; ?>
        </div>

        <!-- Charts Row 1 -->
        <div class="charts-row">
          <!-- Monthly Trend Chart -->
          <div class="chart-card chart-wide">
            <div class="chart-header">
              <h3><i class="fa fa-line-chart"></i> Monthly Docket Trend</h3>
              <span class="chart-badge">Last 12 Months</span>
            </div>
            <div class="chart-body">
              <canvas id="monthlyTrendChart"></canvas>
            </div>
          </div>

          <!-- Status Distribution Donut -->
          <div class="chart-card">
            <div class="chart-header">
              <h3><i class="fa fa-pie-chart"></i> Status Distribution</h3>
            </div>
            <div class="chart-body chart-donut-container">
              <canvas id="statusDonutChart"></canvas>
              <div class="donut-center-text">
                <span class="donut-total"><?= number_format($total_docket) ?></span>
                <span class="donut-label">Total</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Status Cards Grid -->
        <div class="status-cards-grid">
          <?php if (hasPermission('dashboard_view_picked_up')): ?>
          <a href="register.php?type=list_register&status=Picked%20Up" class="status-card status-warning">
            <div class="status-icon"><i class="fa fa-clock-o"></i></div>
            <div class="status-info">
              <span class="status-count"><?= $non_drs ?></span>
              <span class="status-name">Picked Up</span>
            </div>
            <div class="status-bar" style="--percent: <?= $total_docket > 0 ? ($non_drs/$total_docket)*100 : 0 ?>%"></div>
          </a>
          <?php endif; ?>

          <?php if (hasPermission('dashboard_view_out_for_delivery')): ?>
          <a href="register.php?type=list_register&status=Out%20for%20Delivery" class="status-card status-teal">
            <div class="status-icon"><i class="fa fa-truck"></i></div>
            <div class="status-info">
              <span class="status-count"><?= $out_for_delivery ?></span>
              <span class="status-name">Out for Delivery</span>
            </div>
            <div class="status-bar" style="--percent: <?= $total_docket > 0 ? ($out_for_delivery/$total_docket)*100 : 0 ?>%"></div>
          </a>
          <?php endif; ?>

          <?php if (hasPermission('dashboard_view_pending_pod')): ?>
          <a href="register.php?type=list_register&status=Pending%20POD" class="status-card status-orange">
            <div class="status-icon"><i class="fa fa-file-image-o"></i></div>
            <div class="status-info">
              <span class="status-count"><?= $pending_pod ?></span>
              <span class="status-name">Pending POD</span>
            </div>
            <div class="status-bar" style="--percent: <?= $total_docket > 0 ? ($pending_pod/$total_docket)*100 : 0 ?>%"></div>
          </a>
          <?php endif; ?>

          <?php if (hasPermission('dashboard_view_manifest')): ?>
          <a href="manifest.php" class="status-card status-purple">
            <div class="status-icon"><i class="fa fa-file-text"></i></div>
            <div class="status-info">
              <span class="status-count"><?= $manifest_count ?></span>
              <span class="status-name">Manifests</span>
            </div>
            <div class="status-bar" style="--percent: 100%"></div>
          </a>
          <?php endif; ?>
        </div>

        <!-- Charts Row 2 -->
        <div class="charts-row">
          <!-- Top Companies Bar Chart -->
          <div class="chart-card">
            <div class="chart-header">
              <h3><i class="fa fa-building"></i> Top Clients</h3>
              <span class="chart-badge">By Dockets</span>
            </div>
            <div class="chart-body">
              <canvas id="topCompaniesChart"></canvas>
            </div>
          </div>

          <!-- Service Type Chart -->
          <div class="chart-card">
            <div class="chart-header">
              <h3><i class="fa fa-tags"></i> Service Types</h3>
            </div>
            <div class="chart-body">
              <canvas id="serviceTypeChart"></canvas>
            </div>
          </div>

          <!-- Delivery Performance -->
          <div class="chart-card">
            <div class="chart-header">
              <h3><i class="fa fa-trophy"></i> Delivery Performance</h3>
            </div>
            <div class="chart-body performance-stats">
              <div class="perf-stat">
                <div class="perf-circle" style="--percent: <?= $deliveryRate ?>">
                  <span class="perf-value"><?= $deliveryRate ?>%</span>
                </div>
                <span class="perf-label">Delivery Rate</span>
              </div>
              <div class="perf-bars">
                <div class="perf-bar-item">
                  <span class="perf-bar-label">On Time</span>
                  <div class="perf-bar-track">
                    <div class="perf-bar-fill success" style="width: <?= $delivered > 0 ? 85 : 0 ?>%"></div>
                  </div>
                  <span class="perf-bar-value"><?= $delivered > 0 ? '85%' : '0%' ?></span>
                </div>
                <div class="perf-bar-item">
                  <span class="perf-bar-label">In Progress</span>
                  <div class="perf-bar-track">
                    <div class="perf-bar-fill info" style="width: <?= $total_docket > 0 ? (($intransit + $out_for_delivery)/$total_docket)*100 : 0 ?>%"></div>
                  </div>
                  <span class="perf-bar-value"><?= $intransit + $out_for_delivery ?></span>
                </div>
                <div class="perf-bar-item">
                  <span class="perf-bar-label">Delayed</span>
                  <div class="perf-bar-track">
                    <div class="perf-bar-fill danger" style="width: <?= $total_docket > 0 ? ($delayed/$total_docket)*100 : 0 ?>%"></div>
                  </div>
                  <span class="perf-bar-value"><?= $delayed ?></span>
                </div>
              </div>
            </div>
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
            <option value="Pending POD">Pending POD</option>
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
                  <th>Created By</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="docketsTableBody">
                <?php
                // Use docket_details table with LEFT JOIN to tbl_offices (filtered by office and creator)
                // Apply combined filter to show only dockets user has access to
                $whereClause = !empty($combinedFilter) ? "WHERE 1=1 $combinedFilter" : "";
                $sql = "SELECT dd.*, o.office_name, u.full_name as creator_name, u.username as creator_username
                        FROM docket_details dd
                        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
                        LEFT JOIN tbl_users u ON dd.created_by = u.user_id
                        $whereClause
                        ORDER BY dd.docket_id DESC LIMIT 20";
                
                $result = mysqli_query($conn, $sql);
                
                if(!$result) {
                  echo '<tr><td colspan="8" style="text-align:center;padding:40px;color:#e74c3c;">
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
                    
                    // Get sender and receiver from docket_details columns
                    $sender_name = $row['company_name'] ?? 'N/A';
                    $receiver_name = $row['client_name'] ?? 'N/A';
                    ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($row['doc_no'] ?? $row['docket_id']) ?></strong></td>
                      <td><?= htmlspecialchars($sender_name) ?></td>
                      <td><?= htmlspecialchars($receiver_name) ?></td>
                      <td><?= htmlspecialchars($row['service_type'] ?? 'Standard') ?></td>
                      <td><span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($row['status'] ?? 'Pending') ?></span></td>
                      <td><?= htmlspecialchars($row['creator_name'] ?: $row['creator_username'] ?: 'N/A') ?></td>
                      <td><?= $created_date ?></td>
                      <td>
                        <div class="action-menu">
                          <button class="action-menu-trigger" onclick="toggleActionMenu(this)" title="Actions">
                            <i class="fa fa-ellipsis-h"></i>
                          </button>
                          <div class="action-dropdown">
                            <?php if (hasPermission('docket_view_details') || hasPermission('docket_view')): ?>
                            <a href="register.php?type=view_register&id=<?= $row['docket_id'] ?>" class="action-item">
                              <i class="fa fa-eye"></i> View Details
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('docket_download_pdf') || hasPermission('docket_view')): ?>
                            <a href="download_docket.php?docket_id=<?= $row['docket_id'] ?>" class="action-item" target="_blank">
                              <i class="fa fa-download"></i> Download PDF
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('docket_edit')): ?>
                            <a href="edit_register_new.php?docket_id=<?= $row['docket_id'] ?>" class="action-item">
                              <i class="fa fa-edit"></i> Edit
                            </a>
                            <?php endif; ?>
                            
                            <?php if (hasPermission('docket_delete')): ?>
                            <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['docket_id'] ?>)" class="action-item action-item-danger">
                              <i class="fa fa-trash"></i> Delete
                            </a>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <?php
                  }
                } else {
                  echo '<tr><td colspan="8" style="text-align:center;padding:50px;color:var(--text-secondary);">
                        <i class="fa fa-inbox" style="font-size:3rem;display:block;margin-bottom:15px;opacity:0.5;color:var(--text-muted);"></i>
                        <strong style="color:var(--text-primary);">No dockets found</strong><br>
                        <span style="font-size:0.9rem;">Start by creating a new docket</span></td></tr>';
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
      window.location.href = 'register.php?type=delete_register&id=' + id;
    }
  }

  // Action Menu (Meatball Menu) Toggle
  function toggleActionMenu(button) {
    const menu = button.closest('.action-menu');
    const isActive = menu.classList.contains('active');
    
    // Close all other open menus
    document.querySelectorAll('.action-menu.active').forEach(m => {
      m.classList.remove('active');
    });
    
    // Toggle current menu
    if (!isActive) {
      menu.classList.add('active');
    }
  }

  // Close menu when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-menu')) {
      document.querySelectorAll('.action-menu.active').forEach(m => {
        m.classList.remove('active');
      });
    }
  });
  </script>
  
  <style>
/* ============================================
   NETFLIX-STYLE DARK ANALYTICS DASHBOARD
   ============================================ */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

:root {
  --bg-primary: #141414;
  --bg-secondary: #1a1a1a;
  --bg-card: #232323;
  --bg-card-hover: #2a2a2a;
  --accent-red: #e50914;
  --accent-red-dark: #b20710;
  --accent-orange: #f5a623;
  --accent-green: #46d369;
  --accent-blue: #2196f3;
  --accent-purple: #9c27b0;
  --accent-teal: #00bcd4;
  --text-primary: #ffffff;
  --text-secondary: #b3b3b3;
  --text-muted: #808080;
  --border-color: #333333;
  --shadow-lg: 0 10px 40px rgba(0,0,0,0.5);
  --shadow-md: 0 4px 20px rgba(0,0,0,0.3);
}

* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.right_col { 
  background: var(--bg-primary) !important; 
  min-height: 100vh; 
  padding: 0 !important;
}

/* Office Indicator */
.office-indicator {
  background: linear-gradient(135deg, var(--accent-red) 0%, var(--accent-red-dark) 100%);
  color: white;
  padding: 12px 25px;
  margin: 90px 25px 0 25px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 0.95rem;
}

/* Dashboard Header */
.dashboard-header {
  padding: 100px 30px 20px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}

.dashboard-title h1 {
  color: var(--text-primary);
  font-size: 1.8rem;
  font-weight: 800;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
  letter-spacing: 1px;
}

.dashboard-title h1 i {
  color: var(--accent-red);
}

.dashboard-title p {
  color: var(--text-secondary);
  margin: 5px 0 0 0;
  font-size: 0.9rem;
}

.dashboard-filters {
  display: flex;
  gap: 10px;
}

.filter-badge {
  background: var(--bg-card);
  color: var(--text-secondary);
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid var(--border-color);
}

.filter-badge i {
  color: var(--accent-red);
}

/* KPI Cards Row */
.kpi-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 20px;
  padding: 0 30px 25px 30px;
}

.kpi-card {
  background: var(--bg-card);
  border-radius: 12px;
  padding: 24px;
  border-top: 4px solid var(--accent-red);
  transition: all 0.3s ease;
  text-decoration: none !important;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.kpi-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(229,9,20,0.1) 0%, transparent 50%);
  opacity: 0;
  transition: opacity 0.3s;
}

.kpi-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-lg);
  background: var(--bg-card-hover);
}

.kpi-card:hover::before {
  opacity: 1;
}

.kpi-card.kpi-success { border-top-color: var(--accent-green); }
.kpi-card.kpi-info { border-top-color: var(--accent-blue); }
.kpi-card.kpi-danger { border-top-color: #ff4444; }
.kpi-card.kpi-today { border-top-color: var(--accent-orange); }
.kpi-card.kpi-week { border-top-color: var(--accent-purple); }

.kpi-label {
  color: var(--text-secondary);
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  margin-bottom: 10px;
}

.kpi-value {
  color: var(--text-primary);
  font-size: 2.5rem;
  font-weight: 800;
  line-height: 1;
  margin-bottom: 10px;
}

.kpi-trend {
  color: var(--text-muted);
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 6px;
}

.kpi-trend.up { color: var(--accent-green); }
.kpi-trend.down { color: #ff4444; }
.kpi-trend i { font-size: 0.75rem; }

/* Charts Row */
.charts-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 20px;
  padding: 0 30px 25px 30px;
}

.chart-card {
  background: var(--bg-card);
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid var(--border-color);
}

.chart-card.chart-wide {
  grid-column: span 2;
}

@media (max-width: 900px) {
  .chart-card.chart-wide {
    grid-column: span 1;
  }
}

.chart-header {
  padding: 18px 24px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chart-header h3 {
  color: var(--text-primary);
  font-size: 1rem;
  font-weight: 700;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.chart-header h3 i {
  color: var(--accent-red);
  font-size: 1.1rem;
}

.chart-badge {
  background: var(--bg-secondary);
  color: var(--text-secondary);
  padding: 5px 12px;
  border-radius: 15px;
  font-size: 0.75rem;
  font-weight: 600;
}

.chart-body {
  padding: 20px;
  height: 280px;
  position: relative;
}

/* Donut Chart Center Text */
.chart-donut-container {
  position: relative;
}

.donut-center-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  pointer-events: none;
}

.donut-total {
  display: block;
  color: var(--text-primary);
  font-size: 2rem;
  font-weight: 800;
}

.donut-label {
  display: block;
  color: var(--text-secondary);
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Status Cards Grid */
.status-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 15px;
  padding: 0 30px 25px 30px;
}

.status-card {
  background: var(--bg-card);
  border-radius: 10px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  text-decoration: none !important;
  transition: all 0.3s;
  position: relative;
  overflow: hidden;
  border-left: 4px solid transparent;
}

.status-card:hover {
  transform: translateX(5px);
  background: var(--bg-card-hover);
}

.status-card.status-warning { border-left-color: var(--accent-orange); }
.status-card.status-teal { border-left-color: var(--accent-teal); }
.status-card.status-orange { border-left-color: #ff9800; }
.status-card.status-purple { border-left-color: var(--accent-purple); }

.status-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  background: rgba(255,255,255,0.05);
  color: var(--text-secondary);
}

.status-card.status-warning .status-icon { color: var(--accent-orange); background: rgba(245,166,35,0.15); }
.status-card.status-teal .status-icon { color: var(--accent-teal); background: rgba(0,188,212,0.15); }
.status-card.status-orange .status-icon { color: #ff9800; background: rgba(255,152,0,0.15); }
.status-card.status-purple .status-icon { color: var(--accent-purple); background: rgba(156,39,176,0.15); }

.status-info {
  flex: 1;
}

.status-count {
  display: block;
  color: var(--text-primary);
  font-size: 1.8rem;
  font-weight: 800;
  line-height: 1;
}

.status-name {
  display: block;
  color: var(--text-secondary);
  font-size: 0.85rem;
  margin-top: 4px;
}

.status-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  width: var(--percent);
  background: linear-gradient(90deg, var(--accent-red), var(--accent-orange));
  transition: width 1s ease;
}

/* Performance Stats */
.performance-stats {
  display: flex;
  align-items: center;
  gap: 30px;
  height: 100%;
}

.perf-stat {
  text-align: center;
}

.perf-circle {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: conic-gradient(var(--accent-green) calc(var(--percent) * 1%), var(--bg-secondary) 0%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.perf-circle::before {
  content: '';
  position: absolute;
  width: 90px;
  height: 90px;
  border-radius: 50%;
  background: var(--bg-card);
}

.perf-value {
  position: relative;
  z-index: 1;
  color: var(--text-primary);
  font-size: 1.5rem;
  font-weight: 800;
}

.perf-label {
  display: block;
  margin-top: 10px;
  color: var(--text-secondary);
  font-size: 0.85rem;
}

.perf-bars {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.perf-bar-item {
  display: flex;
  align-items: center;
  gap: 15px;
}

.perf-bar-label {
  color: var(--text-secondary);
  font-size: 0.85rem;
  width: 80px;
}

.perf-bar-track {
  flex: 1;
  height: 8px;
  background: var(--bg-secondary);
  border-radius: 4px;
  overflow: hidden;
}

.perf-bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 1s ease;
}

.perf-bar-fill.success { background: var(--accent-green); }
.perf-bar-fill.info { background: var(--accent-blue); }
.perf-bar-fill.danger { background: #ff4444; }

.perf-bar-value {
  color: var(--text-primary);
  font-size: 0.85rem;
  font-weight: 600;
  width: 50px;
  text-align: right;
}

/* Search Section */
.search-section {
  background: var(--bg-card);
  padding: 20px 25px;
  margin: 0 30px 20px 30px;
  border-radius: 12px;
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
  border: 1px solid var(--border-color);
}

.search-input {
  flex: 1;
  min-width: 250px;
  padding: 14px 20px;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.95rem;
  color: var(--text-primary);
  transition: all 0.3s;
}

.search-input::placeholder {
  color: var(--text-muted);
}

.search-input:focus {
  border-color: var(--accent-red);
  outline: none;
  box-shadow: 0 0 0 3px rgba(229,9,20,0.15);
}

.status-filter {
  padding: 14px 20px;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.95rem;
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.3s;
}

.status-filter:focus {
  border-color: var(--accent-red);
  outline: none;
}

.status-filter option {
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.btn-search {
  padding: 14px 28px;
  background: var(--accent-red);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-search:hover {
  background: var(--accent-red-dark);
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(229,9,20,0.3);
}

.btn-reset {
  padding: 14px 28px;
  background: var(--bg-secondary);
  color: var(--text-secondary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-reset:hover {
  background: var(--bg-card-hover);
  color: var(--text-primary);
}

/* Dockets Section */
.dockets-section {
  background: var(--bg-card);
  margin: 0 30px 30px 30px;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid var(--border-color);
}

.dockets-header {
  background: linear-gradient(135deg, #2d2d2d 0%, #252525 100%);
  color: white;
  padding: 20px 25px;
  font-size: 1.1rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
  border-bottom: 3px solid var(--accent-teal);
}

.dockets-header i {
  color: var(--accent-teal);
}

.table-responsive {
  overflow-x: auto;
}

.dockets-table {
  width: 100%;
  border-collapse: collapse;
}

.dockets-table thead {
  background: var(--bg-secondary);
}

.dockets-table thead th {
  padding: 16px 20px;
  text-align: left;
  font-weight: 700;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--text-secondary);
  border-bottom: 1px solid var(--border-color);
}

.dockets-table tbody tr {
  border-bottom: 1px solid var(--border-color);
  transition: all 0.2s;
}

.dockets-table tbody tr:hover {
  background: var(--bg-card-hover);
}

.dockets-table tbody td {
  padding: 16px 20px;
  font-size: 0.9rem;
  color: var(--text-primary);
}

.dockets-table tbody td strong {
  font-weight: 700;
  color: var(--accent-orange);
}

/* Status Badges */
.status-badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  display: inline-block;
  text-align: center;
}

.status-transit {
  background: rgba(33,150,243,0.2);
  color: #64b5f6;
}

.status-delivered {
  background: rgba(70,211,105,0.2);
  color: var(--accent-green);
}

.status-pending {
  background: rgba(245,166,35,0.2);
  color: var(--accent-orange);
}

.status-out {
  background: rgba(0,188,212,0.2);
  color: var(--accent-teal);
}

.status-delayed {
  background: rgba(255,68,68,0.2);
  color: #ff6b6b;
}

.status-default {
  background: rgba(128,128,128,0.2);
  color: var(--text-secondary);
}

/* Action Menu (Meatball Menu) */
.action-menu {
  position: relative;
  display: inline-block;
}

.action-menu-trigger {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s;
  font-size: 1rem;
}

.action-menu-trigger:hover {
  background: var(--bg-card-hover);
  color: var(--text-primary);
  border-color: var(--text-muted);
}

.action-menu.active .action-menu-trigger {
  background: var(--bg-card-hover);
  color: var(--accent-teal);
  border-color: var(--accent-teal);
}

.action-dropdown {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 4px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  min-width: 160px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.4);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-8px);
  transition: all 0.2s ease;
  z-index: 100;
  overflow: hidden;
}

.action-menu.active .action-dropdown {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.action-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 0.875rem;
  transition: all 0.15s;
  border: none;
  background: none;
  width: 100%;
  cursor: pointer;
  text-align: left;
}

.action-item:hover {
  background: var(--bg-card-hover);
  color: var(--text-primary);
}

.action-item i {
  width: 16px;
  text-align: center;
  opacity: 0.7;
}

.action-item:hover i {
  opacity: 1;
}

.action-item-danger {
  color: #ff6b6b;
}

.action-item-danger:hover {
  background: rgba(255,68,68,0.1);
  color: #ff4444;
}

/* Responsive Styles */
@media (max-width: 1200px) {
  .kpi-row {
    grid-template-columns: repeat(3, 1fr);
  }
  .kpi-value {
    font-size: 2rem;
  }
}

@media (max-width: 992px) {
  .performance-stats {
    flex-direction: column;
    gap: 20px;
  }
  .perf-circle {
    width: 100px;
    height: 100px;
  }
  .perf-circle::before {
    width: 75px;
    height: 75px;
  }
  .perf-value {
    font-size: 1.2rem;
  }
}

@media (max-width: 768px) {
  .dashboard-header {
    padding: 90px 20px 15px 20px;
  }
  
  .dashboard-title h1 {
    font-size: 1.4rem;
  }

  .kpi-row {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    padding: 0 20px 20px 20px;
  }

  .kpi-card {
    padding: 18px;
  }

  .kpi-value {
    font-size: 1.8rem;
  }

  .charts-row {
    grid-template-columns: 1fr;
    padding: 0 20px 20px 20px;
  }

  .chart-body {
    height: 220px;
  }

  .status-cards-grid {
    grid-template-columns: repeat(2, 1fr);
    padding: 0 20px 20px 20px;
  }

  .search-section {
    flex-direction: column;
    padding: 15px;
    margin: 0 20px 15px 20px;
  }

  .search-input, .status-filter, .btn-search, .btn-reset {
    width: 100%;
  }

  .dockets-section {
    margin: 0 20px 20px 20px;
  }

  .dockets-table thead th,
  .dockets-table tbody td {
    padding: 12px 10px;
    font-size: 0.8rem;
  }
}

@media (max-width: 576px) {
  .dashboard-header {
    padding: 85px 15px 10px 15px;
  }

  .kpi-row {
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    padding: 0 15px 15px 15px;
  }

  .kpi-card {
    padding: 15px;
  }

  .kpi-label {
    font-size: 0.7rem;
  }

  .kpi-value {
    font-size: 1.5rem;
  }

  .charts-row {
    padding: 0 15px 15px 15px;
  }

  .status-cards-grid {
    grid-template-columns: 1fr;
    padding: 0 15px 15px 15px;
  }

  .status-card {
    padding: 15px;
  }

  .status-count {
    font-size: 1.5rem;
  }

  .search-section {
    margin: 0 15px 10px 15px;
  }

  .dockets-section {
    margin: 0 15px 15px 15px;
  }

  .dockets-header {
    font-size: 0.95rem;
    padding: 15px 18px;
  }

  .action-menu-trigger {
    width: 32px;
    height: 32px;
    font-size: 0.9rem;
  }

  .action-dropdown {
    min-width: 140px;
  }
}
  </style>
  
  <!-- Chart.js Initialization -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Chart.js global defaults for dark theme
    Chart.defaults.color = '#b3b3b3';
    Chart.defaults.borderColor = '#333333';
    
    // Monthly Trend Line Chart
    const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
    if (monthlyTrendCtx) {
      new Chart(monthlyTrendCtx, {
        type: 'line',
        data: {
          labels: <?= json_encode($monthLabels) ?>,
          datasets: [{
            label: 'Total Dockets',
            data: <?= json_encode($monthlyDockets) ?>,
            borderColor: '#e50914',
            backgroundColor: 'rgba(229,9,20,0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#e50914',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
          }, {
            label: 'Delivered',
            data: <?= json_encode($monthlyDelivered) ?>,
            borderColor: '#46d369',
            backgroundColor: 'rgba(70,211,105,0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#46d369',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
              labels: {
                usePointStyle: true,
                padding: 20,
                font: { weight: '600' }
              }
            }
          },
          scales: {
            x: {
              grid: { display: false }
            },
            y: {
              beginAtZero: true,
              grid: { color: 'rgba(255,255,255,0.05)' }
            }
          },
          interaction: {
            intersect: false,
            mode: 'index'
          }
        }
      });
    }

    // Status Distribution Donut Chart
    const statusDonutCtx = document.getElementById('statusDonutChart');
    if (statusDonutCtx) {
      new Chart(statusDonutCtx, {
        type: 'doughnut',
        data: {
          labels: ['Delivered', 'In Transit', 'Picked Up', 'Out for Delivery', 'Delayed', 'Pending POD'],
          datasets: [{
            data: [<?= $delivered ?>, <?= $intransit ?>, <?= $non_drs ?>, <?= $out_for_delivery ?>, <?= $delayed ?>, <?= $pending_pod ?>],
            backgroundColor: [
              '#46d369',
              '#2196f3',
              '#f5a623',
              '#00bcd4',
              '#ff4444',
              '#ff9800'
            ],
            borderWidth: 0,
            hoverOffset: 10
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '65%',
          plugins: {
            legend: {
              position: 'right',
              labels: {
                usePointStyle: true,
                padding: 15,
                font: { size: 11 }
              }
            }
          }
        }
      });
    }

    // Top Companies Bar Chart
    const topCompaniesCtx = document.getElementById('topCompaniesChart');
    if (topCompaniesCtx) {
      new Chart(topCompaniesCtx, {
        type: 'bar',
        data: {
          labels: <?= json_encode($topCompanies) ?>,
          datasets: [{
            label: 'Dockets',
            data: <?= json_encode($topCompanyCounts) ?>,
            backgroundColor: [
              '#e50914', '#ff4444', '#ff6b6b', '#ff8a8a',
              '#f5a623', '#ffb84d', '#ffc970', '#ffd699'
            ],
            borderRadius: 6,
            borderSkipped: false
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          plugins: {
            legend: { display: false }
          },
          scales: {
            x: {
              beginAtZero: true,
              grid: { color: 'rgba(255,255,255,0.05)' }
            },
            y: {
              grid: { display: false }
            }
          }
        }
      });
    }

    // Service Type Donut Chart
    const serviceTypeCtx = document.getElementById('serviceTypeChart');
    if (serviceTypeCtx) {
      new Chart(serviceTypeCtx, {
        type: 'doughnut',
        data: {
          labels: <?= json_encode($serviceTypes) ?>,
          datasets: [{
            data: <?= json_encode($serviceTypeCounts) ?>,
            backgroundColor: [
              '#e50914',
              '#f5a623',
              '#46d369',
              '#2196f3',
              '#9c27b0'
            ],
            borderWidth: 0,
            hoverOffset: 8
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '60%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                usePointStyle: true,
                padding: 15,
                font: { size: 11 }
              }
            }
          }
        }
      });
    }
  });
  </script>
</body>
