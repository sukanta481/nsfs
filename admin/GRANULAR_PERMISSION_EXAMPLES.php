<?php
/**
 * GRANULAR PERMISSION USAGE EXAMPLES
 * 
 * This file demonstrates how to use granular permissions to control
 * specific UI elements and features in your admin pages.
 */

// Always include check_auth.php at the top of protected pages
require 'check_auth.php';
require 'conn.php';

// Example 1: Basic page-level permission
requirePermission('dashboard_view'); // This will show 403 if user doesn't have permission

require 'top_header.php';
?>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <div class="right_col" role="main">
        
        <h2>Dashboard</h2>
        
        <!-- Example 2: Show/Hide Dashboard Cards Based on Permissions -->
        <div class="row">
          
          <?php if (hasPermission('dashboard_total_trips_card')): ?>
          <div class="col-md-3">
            <div class="dashboard-card">
              <h3>Total Trips</h3>
              <p class="stat-number">1,234</p>
            </div>
          </div>
          <?php endif; ?>
          
          <?php if (hasPermission('dashboard_active_trips_card')): ?>
          <div class="col-md-3">
            <div class="dashboard-card">
              <h3>Active Trips</h3>
              <p class="stat-number">56</p>
            </div>
          </div>
          <?php endif; ?>
          
          <?php if (hasPermission('dashboard_completed_trips_card')): ?>
          <div class="col-md-3">
            <div class="dashboard-card">
              <h3>Completed Trips</h3>
              <p class="stat-number">1,178</p>
            </div>
          </div>
          <?php endif; ?>
          
          <?php if (hasPermission('dashboard_revenue_card')): ?>
          <div class="col-md-3">
            <div class="dashboard-card">
              <h3>Revenue</h3>
              <p class="stat-number">₹5,67,890</p>
            </div>
          </div>
          <?php endif; ?>
          
        </div>
        
        <!-- Example 3: Show Charts Only if Permission Granted -->
        <?php if (hasPermission('dashboard_charts_view')): ?>
        <div class="row">
          <div class="col-md-12">
            <div class="chart-container">
              <h3>Revenue Trends</h3>
              <!-- Chart code here -->
            </div>
          </div>
        </div>
        <?php endif; ?>
        
        <!-- Example 4: Show Recent Activities Section -->
        <?php if (hasPermission('dashboard_recent_activities')): ?>
        <div class="row">
          <div class="col-md-12">
            <h3>Recent Activities</h3>
            <table class="table">
              <!-- Activities list -->
            </table>
          </div>
        </div>
        <?php endif; ?>
        
        <!-- Example 5: Show Export Button Only if Permission Granted -->
        <?php if (hasPermission('dashboard_export_data')): ?>
        <div class="export-section">
          <button class="btn btn-success" onclick="exportDashboard()">
            <i class="fa fa-download"></i> Export Dashboard Data
          </button>
        </div>
        <?php endif; ?>
        
        
        <!-- EXAMPLE FOR DOCKET PAGE -->
        <hr>
        <h2>Docket Management Examples</h2>
        
        <!-- Example 6: Status Update Dropdown -->
        <?php if (hasPermission('docket_status_update')): ?>
        <div class="status-update">
          <label>Update Status:</label>
          <select name="status" class="form-control">
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
          </select>
          <button class="btn btn-primary">Update</button>
        </div>
        <?php else: ?>
        <div class="status-display">
          <label>Status:</label>
          <span class="badge badge-info">In Progress</span>
          <small class="text-muted">(You don't have permission to change status)</small>
        </div>
        <?php endif; ?>
        
        <!-- Example 7: Conditional Action Buttons -->
        <div class="action-buttons">
          
          <?php if (hasPermission('docket_assign_driver')): ?>
          <button class="btn btn-info" onclick="assignDriver()">
            <i class="fa fa-user"></i> Assign Driver
          </button>
          <?php endif; ?>
          
          <?php if (hasPermission('docket_assign_vehicle')): ?>
          <button class="btn btn-info" onclick="assignVehicle()">
            <i class="fa fa-truck"></i> Assign Vehicle
          </button>
          <?php endif; ?>
          
          <?php if (hasPermission('docket_print')): ?>
          <button class="btn btn-primary" onclick="printDocket()">
            <i class="fa fa-print"></i> Print
          </button>
          <?php endif; ?>
          
          <?php if (hasPermission('docket_export')): ?>
          <button class="btn btn-success" onclick="exportDocket()">
            <i class="fa fa-download"></i> Export
          </button>
          <?php endif; ?>
          
          <?php if (hasPermission('docket_view_history')): ?>
          <button class="btn btn-secondary" onclick="viewHistory()">
            <i class="fa fa-history"></i> View History
          </button>
          <?php endif; ?>
          
        </div>
        
        
        <!-- EXAMPLE FOR TABLE WITH BULK ACTIONS -->
        <hr>
        <h2>Docket List with Bulk Actions</h2>
        
        <table class="table table-striped">
          <thead>
            <tr>
              <?php if (hasPermission('docket_bulk_actions')): ?>
              <th><input type="checkbox" id="selectAll"></th>
              <?php endif; ?>
              <th>Docket #</th>
              <th>Client</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <?php if (hasPermission('docket_bulk_actions')): ?>
              <td><input type="checkbox" name="docket_ids[]" value="1"></td>
              <?php endif; ?>
              <td>#D001</td>
              <td>ABC Company</td>
              <td><span class="badge badge-warning">Pending</span></td>
              <td>
                <?php if (hasPermission('docket_edit')): ?>
                <a href="edit_docket.php?id=1" class="btn btn-sm btn-primary">Edit</a>
                <?php endif; ?>
                
                <?php if (hasPermission('docket_delete')): ?>
                <a href="delete_docket.php?id=1" class="btn btn-sm btn-danger">Delete</a>
                <?php endif; ?>
              </td>
            </tr>
          </tbody>
        </table>
        
        <?php if (hasPermission('docket_bulk_actions')): ?>
        <div class="bulk-actions">
          <label>Bulk Actions:</label>
          <select name="bulk_action" class="form-control" style="width: 200px; display: inline-block;">
            <option value="">Select Action</option>
            <?php if (hasPermission('docket_status_update')): ?>
            <option value="change_status">Change Status</option>
            <?php endif; ?>
            <?php if (hasPermission('docket_assign_driver')): ?>
            <option value="assign_driver">Assign Driver</option>
            <?php endif; ?>
            <?php if (hasPermission('docket_delete')): ?>
            <option value="delete">Delete Selected</option>
            <?php endif; ?>
          </select>
          <button class="btn btn-primary">Apply</button>
        </div>
        <?php endif; ?>
        
        
        <!-- EXAMPLE: Check Multiple Permissions -->
        <hr>
        <h2>Manifest Page Example</h2>
        
        <?php 
        // Check if user can finalize OR reopen manifests
        if (hasPermission('manifest_finalize') || hasPermission('manifest_reopen')): 
        ?>
        <div class="manifest-controls">
          <?php if (hasPermission('manifest_finalize')): ?>
          <button class="btn btn-success">Finalize Manifest</button>
          <?php endif; ?>
          
          <?php if (hasPermission('manifest_reopen')): ?>
          <button class="btn btn-warning">Reopen Manifest</button>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        
        
        <!-- EXAMPLE: Using hasPermission in JavaScript -->
        <hr>
        <h2>JavaScript Integration Example</h2>
        
        <script>
        // Pass permission status to JavaScript
        const userPermissions = {
            canUpdateStatus: <?php echo hasPermission('docket_status_update') ? 'true' : 'false'; ?>,
            canAssignDriver: <?php echo hasPermission('docket_assign_driver') ? 'true' : 'false'; ?>,
            canPrint: <?php echo hasPermission('docket_print') ? 'true' : 'false'; ?>,
            canExport: <?php echo hasPermission('docket_export') ? 'true' : 'false'; ?>
        };
        
        // Use in JavaScript
        function showContextMenu(event, docketId) {
            let menuItems = [];
            
            if (userPermissions.canUpdateStatus) {
                menuItems.push('<li onclick="updateStatus(' + docketId + ')">Update Status</li>');
            }
            
            if (userPermissions.canAssignDriver) {
                menuItems.push('<li onclick="assignDriver(' + docketId + ')">Assign Driver</li>');
            }
            
            if (userPermissions.canPrint) {
                menuItems.push('<li onclick="printDocket(' + docketId + ')">Print</li>');
            }
            
            // Show context menu with only permitted options
            // ... menu display code
        }
        </script>
        
        
        <!-- EXAMPLE: Check in PHP Logic -->
        <?php
        // Example: Fetch data based on permission
        if (hasPermission('docket_view_all')) {
            // User can see all dockets
            $query = "SELECT * FROM tbl_register ORDER BY created_at DESC";
        } elseif (hasPermission('docket_view_own')) {
            // User can only see their own dockets
            $user_id = $_SESSION['user_id'];
            $query = "SELECT * FROM tbl_register WHERE created_by = $user_id ORDER BY created_at DESC";
        } else {
            // No permission to view dockets
            echo "<div class='alert alert-danger'>You don't have permission to view dockets.</div>";
            exit;
        }
        
        // Example: Validate action before processing
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_status'])) {
            if (!hasPermission('docket_status_update')) {
                die("Error: You don't have permission to update status");
            }
            
            // Process status update
            // ...
        }
        ?>
        
      </div>
      
      <?php require 'footer.php'; ?>
    </div>
  </div>
</body>
</html>


<?php
/**
 * ADDITIONAL USAGE PATTERNS
 */

// Pattern 1: Check permission and show different UI
function renderDocketActions($docket_id) {
    $actions = [];
    
    if (hasPermission('docket_edit')) {
        $actions[] = '<a href="edit.php?id=' . $docket_id . '">Edit</a>';
    }
    
    if (hasPermission('docket_delete')) {
        $actions[] = '<a href="delete.php?id=' . $docket_id . '">Delete</a>';
    }
    
    if (hasPermission('docket_print')) {
        $actions[] = '<a href="print.php?id=' . $docket_id . '">Print</a>';
    }
    
    return implode(' | ', $actions);
}


// Pattern 2: Gate entire sections
function canAccessFinancialData() {
    return hasPermission('report_view_financial') || 
           hasPermission('client_view_pricing') || 
           hasPermission('staff_view_salary');
}

if (canAccessFinancialData()) {
    // Show financial dashboard
} else {
    // Show limited dashboard
}


// Pattern 3: Build dynamic WHERE clause based on permissions
function buildDocketQuery() {
    $where = [];
    
    if (hasPermission('docket_view_all')) {
        // No restrictions
    } elseif (hasPermission('docket_view_own')) {
        $where[] = "created_by = " . $_SESSION['user_id'];
    } else {
        return null; // No access
    }
    
    $sql = "SELECT * FROM tbl_register";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    
    return $sql;
}


// Pattern 4: Permission-based column visibility in tables
$table_columns = [
    'docket_number' => true, // Always show
    'client_name' => true,
    'status' => true,
    'driver' => hasPermission('docket_view'),
    'amount' => hasPermission('client_view_pricing'),
    'payment_status' => hasPermission('report_view_financial'),
];

// Then in your table header:
echo '<th>Docket #</th>';
echo '<th>Client</th>';
echo '<th>Status</th>';
if ($table_columns['driver']) echo '<th>Driver</th>';
if ($table_columns['amount']) echo '<th>Amount</th>';
if ($table_columns['payment_status']) echo '<th>Payment</th>';
?>
