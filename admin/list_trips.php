<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('conn.php');

// Check if database connection exists
if(!isset($conn) || !$conn) {
    die("Database connection failed! Please check conn.php");
}

// Get filter parameters
$fromdate = $_GET['fromdate'] ?? '';
$todate = $_GET['todate'] ?? '';
$trip_no = trim($_GET['trip_no'] ?? '');
$car_number = trim($_GET['car_number'] ?? '');
$driver_name = trim($_GET['driver_name'] ?? '');
$status = $_GET['status'] ?? '';

// Validate date range
$dateError = '';
if (!empty($fromdate) && !empty($todate)) {
    if (strtotime($fromdate) > strtotime($todate)) {
        $dateError = "From date cannot be greater than To date";
    }
}

// Build WHERE clause
$where = [];

// Date filter
if (!empty($fromdate) && !empty($todate) && empty($dateError)) {
    $fromDateTime = mysqli_real_escape_string($conn, $fromdate) . ' 00:00:00';
    $toDateTime = mysqli_real_escape_string($conn, $todate) . ' 23:59:59';
    $where[] = "(dd.created_at >= '$fromDateTime' AND dd.created_at <= '$toDateTime')";
} elseif (!empty($fromdate) && empty($todate)) {
    $fromDateTime = mysqli_real_escape_string($conn, $fromdate) . ' 00:00:00';
    $where[] = "dd.created_at >= '$fromDateTime'";
} elseif (empty($fromdate) && !empty($todate)) {
    $toDateTime = mysqli_real_escape_string($conn, $todate) . ' 23:59:59';
    $where[] = "dd.created_at <= '$toDateTime'";
}

// Trip number filter
if (!empty($trip_no)) {
    $where[] = "dd.trip_group_id LIKE '%" . mysqli_real_escape_string($conn, $trip_no) . "%'";
}

// Car number filter
if (!empty($car_number)) {
    $where[] = "dd.car_number LIKE '%" . mysqli_real_escape_string($conn, $car_number) . "%'";
}

// Driver name filter
if (!empty($driver_name)) {
    $where[] = "dd.driver_name LIKE '%" . mysqli_real_escape_string($conn, $driver_name) . "%'";
}

// Status filter
if (!empty($status)) {
    $where[] = "dd.status = '" . mysqli_real_escape_string($conn, $status) . "'";
}

$whereSQL = !empty($where) ? "WHERE dd.trip_group_id IS NOT NULL AND dd.trip_group_id != '' AND " . implode(" AND ", $where) : "WHERE dd.trip_group_id IS NOT NULL AND dd.trip_group_id != ''";

// Fetch trips with docket count
$sql = "SELECT 
            dd.trip_group_id,
            MIN(dd.created_at) as created_at,
            COUNT(dd.docket_id) as docket_count,
            dd.car_number,
            dd.driver_name,
            dd.driver_phone,
            GROUP_CONCAT(DISTINCT dd.status) as statuses
        FROM docket_details dd
        $whereSQL
        GROUP BY dd.trip_group_id
        ORDER BY MIN(dd.created_at) DESC";

$result = mysqli_query($conn, $sql);

// Check for SQL errors
if(!$result) {
    $sqlError = mysqli_error($conn);
    error_log("SQL Error in list_trips.php: " . $sqlError);
}

$totalRecords = $result ? mysqli_num_rows($result) : 0;
?>

<style>
/* Disable loading indicators */
.pace, .pace-progress, .pace-activity { display: none !important; }
</style>

<script>
window.Pace = { start: function(){}, restart: function(){}, stop: function(){}, options: { startOnPageLoad: false } };
window.__NO_DATATABLES__ = true;
</script>

<div class="trips-list-container">
    
    <!-- Success Message -->
    <?php if(isset($_GET['success'])): ?>
    <div class="alert-message alert-success">
        <i class="fa fa-check-circle"></i>
        <span>Trip operation completed successfully!</span>
        <button class="close-alert" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    <?php endif; ?>
    
    <!-- Error Message -->
    <?php if(isset($_GET['error'])): ?>
    <div class="alert-message alert-error">
        <i class="fa fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($_GET['error']) ?></span>
        <button class="close-alert" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    <?php endif; ?>
    
    <!-- Date Validation Error -->
    <?php if(!empty($dateError)): ?>
    <div class="alert-message alert-error">
        <i class="fa fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($dateError) ?></span>
        <button class="close-alert" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    <?php endif; ?>
    
    <!-- SQL Error -->
    <?php if(!empty($sqlError)): ?>
    <div class="alert-message alert-error">
        <i class="fa fa-exclamation-circle"></i>
        <span><strong>Database Error:</strong> <?= htmlspecialchars($sqlError) ?></span>
        <button class="close-alert" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    <?php endif; ?>
    
    <!-- Advanced Filters -->
    <div class="filter-section">
        <div class="filter-header">
            <i class="fa fa-filter"></i>
            <span>Advanced Filters</span>
        </div>
        
        <form method="get" action="" class="filter-form" id="filterForm">
            <input type="hidden" name="type" value="list_trips">
            
            <div class="filter-row">
                <div class="filter-col">
                    <label><i class="fa fa-calendar"></i> Date Range (Created)</label>
                    <div class="date-inputs">
                        <input type="date" name="fromdate" value="<?= htmlspecialchars($fromdate) ?>" placeholder="From">
                        <span>to</span>
                        <input type="date" name="todate" value="<?= htmlspecialchars($todate) ?>" placeholder="To">
                    </div>
                </div>
                
                <div class="filter-col">
                    <label><i class="fa fa-hashtag"></i> Trip Number</label>
                    <input type="text" name="trip_no" value="<?= htmlspecialchars($trip_no) ?>" placeholder="Search trip number...">
                </div>
                
                <div class="filter-col">
                    <label><i class="fa fa-car"></i> Car Number</label>
                    <input type="text" name="car_number" value="<?= htmlspecialchars($car_number) ?>" placeholder="Search car...">
                </div>
            </div>
            
            <div class="filter-row">
                <div class="filter-col">
                    <label><i class="fa fa-user"></i> Driver Name</label>
                    <input type="text" name="driver_name" value="<?= htmlspecialchars($driver_name) ?>" placeholder="Search driver...">
                </div>
                
                <div class="filter-col">
                    <label><i class="fa fa-info-circle"></i> Docket Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="Pending" <?= $status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Picked Up" <?= $status == 'Picked Up' ? 'selected' : '' ?>>Picked Up</option>
                        <option value="In Transit" <?= $status == 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                        <option value="Out for Delivery" <?= $status == 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                        <option value="Delivered" <?= $status == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="Delayed" <?= $status == 'Delayed' ? 'selected' : '' ?>>Delayed</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-search">
                    <i class="fa fa-search"></i> Search
                </button>
                <button type="button" class="btn-reset" onclick="window.location.href='trip.php?type=list_trips'">
                    <i class="fa fa-refresh"></i> Reset
                </button>
            </div>
        </form>
    </div>
    
    <!-- Trips Table -->
    <div class="table-section">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-truck"></i>
                <span>All Trips</span>
            </div>
            <div class="header-right">
                Total: <strong><?= $totalRecords ?></strong> trips
            </div>
        </div>
        
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sl</th>
                        <th>Created Date</th>
                        <th>Trip Number</th>
                        <th>Car Number</th>
                        <th>Driver Name</th>
                        <th>Driver Phone</th>
                        <th>Docket Count</th>
                        <th>Status Overview</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($result && $totalRecords > 0) {
                        $sl = 1;
                        while($row = mysqli_fetch_assoc($result)) {
                            // Format date
                            $created_date = 'N/A';
                            if(!empty($row['created_at'])) {
                                $date = DateTime::createFromFormat('Y-m-d H:i:s', $row['created_at']);
                                if(!$date) $date = DateTime::createFromFormat('Y-m-d', $row['created_at']);
                                if($date) $created_date = $date->format('d-m-Y h:i A');
                            }
                            
                            // Parse statuses
                            $statuses = explode(',', $row['statuses']);
                            $status_counts = array_count_values($statuses);
                    ?>
                    <tr>
                        <td><?= $sl ?></td>
                        <td><?= $created_date ?></td>
                        <td><strong><?= htmlspecialchars($row['trip_group_id'] ?? '-') ?></strong></td>
                        <td><?= htmlspecialchars($row['car_number'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['driver_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['driver_phone'] ?? '-') ?></td>
                        <td>
                            <a href="trip.php?type=trip_dockets&trip_id=<?= urlencode($row['trip_group_id']) ?>" class="docket-count-link">
                                <span class="docket-count-badge"><?= $row['docket_count'] ?></span>
                                <span class="docket-count-text">Dockets</span>
                            </a>
                        </td>
                        <td>
                            <div class="status-overview">
                                <?php foreach($status_counts as $status => $count): ?>
                                    <?php
                                    $status_class = match(trim($status)) {
                                        'In Transit' => 'status-transit',
                                        'Delivered' => 'status-delivered',
                                        'Picked Up' => 'status-pending',
                                        'Out for Delivery' => 'status-out',
                                        'Delayed' => 'status-delayed',
                                        default => 'status-default'
                                    };
                                    ?>
                                    <span class="status-mini <?= $status_class ?>" title="<?= htmlspecialchars(trim($status)) ?>">
                                        <?= $count ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                            $sl++;
                        }
                    } else {
                        $hasFilters = !empty($fromdate) || !empty($todate) || !empty($trip_no) || 
                                     !empty($car_number) || !empty($driver_name) || !empty($status);
                    ?>
                    <tr>
                        <td colspan="8" class="no-data">
                            <i class="fa fa-truck" style="font-size: 48px; color: #95a5a6; margin-bottom: 10px;"></i>
                            <p style="font-size: 18px; font-weight: 600; margin: 10px 0 5px;">No trips found</p>
                            <?php if($hasFilters): ?>
                            <p style="font-size: 14px; color: #7f8c8d;">
                                No trips match your filter criteria. Try adjusting your filters.
                            </p>
                            <?php else: ?>
                            <p style="font-size: 14px; color: #7f8c8d;">
                                No trips have been created yet.
                            </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Block DataTables
if (typeof jQuery !== 'undefined') {
    jQuery.fn.DataTable = function() { return this; };
    jQuery.fn.dataTable = function() { return this; };
}

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.pace, .pace-progress, .pace-activity').forEach(el => el.remove());
        document.body.classList.remove('pace-running', 'pace-active');
    }, 100);
    
    const alerts = document.querySelectorAll('.alert-message');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'slideUp 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Date validation
    const filterForm = document.getElementById('filterForm');
    if(filterForm) {
        filterForm.addEventListener('submit', function(e) {
            const fromDate = document.querySelector('input[name="fromdate"]').value;
            const toDate = document.querySelector('input[name="todate"]').value;
            
            if(fromDate && toDate) {
                const from = new Date(fromDate);
                const to = new Date(toDate);
                
                if(from > to) {
                    e.preventDefault();
                    alert('Error: "From Date" cannot be greater than "To Date".\n\nPlease adjust your date range.');
                    return false;
                }
            }
        });
    }
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.trips-list-container {
    font-family: 'Inter', sans-serif;
    padding: 0px 15px 10px 15px;
    min-height: calc(100vh - 160px);
    margin-top: 0;
}

/* Alert Messages */
.alert-message {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    position: relative;
    animation: slideDown 0.3s ease;
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

.alert-message i {
    font-size: 1.3rem;
}

.alert-message span {
    flex: 1;
}

.close-alert {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: inherit;
    opacity: 0.7;
    padding: 0 5px;
}

.close-alert:hover {
    opacity: 1;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideUp {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-20px);
    }
}

/* Filter Section */
.filter-section {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    overflow: hidden;
}

.filter-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 20px 28px;
    font-size: 1.3rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.filter-form {
    padding: 28px;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.filter-col label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-col label i {
    color: #667eea;
}

.filter-col input[type="text"],
.filter-col input[type="date"],
.filter-col select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e0e6ed;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s;
    background: #f8f9fa;
}

.filter-col input:focus,
.filter-col select:focus {
    border-color: #667eea;
    outline: none;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.date-inputs {
    display: flex;
    align-items: center;
    gap: 10px;
}

.date-inputs span {
    font-weight: 600;
    color: #6c757d;
}

.filter-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 8px;
}

.btn-search,
.btn-reset {
    padding: 14px 32px;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: 0.5px;
}

.btn-search {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}

.btn-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102,126,234,0.4);
}

.btn-reset {
    background: #6c757d;
    color: #fff;
}

.btn-reset:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

/* Table Section */
.table-section {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
}

.table-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #fff;
    padding: 20px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.3rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.header-right {
    font-size: 1rem;
    font-weight: 600;
}

.header-right strong {
    font-size: 1.3rem;
    color: #ffc107;
    font-weight: 800;
}

.table-wrapper {
    overflow-x: auto;
    max-height: 65vh;
    overflow-y: auto;
}

.data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.data-table thead {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #34495e;
}

.data-table thead th {
    padding: 16px 18px;
    text-align: left;
    font-weight: 800;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #fff;
    background: #34495e;
    border-bottom: 2px solid #2c3e50;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.data-table tbody tr {
    border-bottom: 1px solid #ecf0f1;
    transition: background 0.2s;
}

.data-table tbody tr:hover {
    background: #f8f9fa;
}

.data-table tbody td {
    padding: 16px 18px;
    font-size: 0.95rem;
    color: #2c3e50;
    font-weight: 500;
}

.data-table tbody td strong {
    font-weight: 800;
    color: #1a1a1a;
}

/* Docket Count Link */
.docket-count-link {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s;
    padding: 8px 12px;
    border-radius: 8px;
}

.docket-count-link:hover {
    background: #e3f2fd;
    transform: scale(1.05);
}

.docket-count-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    font-size: 1.2rem;
    font-weight: 900;
    padding: 8px 16px;
    border-radius: 50%;
    min-width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(102,126,234,0.3);
}

.docket-count-text {
    font-weight: 700;
    color: #667eea;
    font-size: 0.9rem;
}

.docket-count-link:hover .docket-count-badge {
    box-shadow: 0 6px 20px rgba(102,126,234,0.5);
}

/* Status Overview */
.status-overview {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.status-mini {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 700;
    display: inline-block;
}

.status-transit { background: #d4edff; color: #0066cc; }
.status-delivered { background: #d4f4dd; color: #0d7d2d; }
.status-pending { background: #fff3cd; color: #856404; }
.status-out { background: #e7f3ff; color: #004085; }
.status-delayed { background: #f8d7da; color: #721c24; }
.status-default { background: #e9ecef; color: #495057; }

/* No Data */
.no-data {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

/* Responsive */
@media (max-width: 992px) {
    .trips-list-container {
        padding: 0 15px 15px 15px;
    }
    
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .btn-search,
    .btn-reset {
        width: 100%;
        justify-content: center;
    }
    
    .table-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .date-inputs {
        flex-direction: column;
    }
    
    .data-table thead th,
    .data-table tbody td {
        padding: 12px 10px;
        font-size: 0.85rem;
    }
}
</style>
