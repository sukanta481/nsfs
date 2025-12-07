<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('conn.php');
require_once 'check_auth.php'; // Required for getOfficeFilter() and access control functions

// Check if database connection exists
if(!isset($conn) || !$conn) {
    die("Database connection failed! Please check conn.php");
}

// Get trip ID
$trip_id = trim($_GET['trip_id'] ?? '');

if(empty($trip_id)) {
    header('Location: trip.php?type=list_trips&error=' . urlencode('Invalid trip ID'));
    exit;
}

// Get filter parameters (same as list_register)
$fromdate = $_GET['fromdate'] ?? '';
$todate = $_GET['todate'] ?? '';
$status = $_GET['status'] ?? '';
$searchType = $_GET['searchType'] ?? '';
$searchValue = $_GET['searchValue'] ?? '';
$consignor = trim($_GET['consignor'] ?? '');
$consignee = trim($_GET['consignee'] ?? '');

// Build WHERE clause
$where = ["dd.trip_group_id = '" . mysqli_real_escape_string($conn, $trip_id) . "'"];

// Date filter
if (!empty($fromdate) && !empty($todate)) {
    $fromDateTime = mysqli_real_escape_string($conn, $fromdate) . ' 00:00:00';
    $toDateTime = mysqli_real_escape_string($conn, $todate) . ' 23:59:59';
    $where[] = "(dd.pickup_datetime >= '$fromDateTime' AND dd.pickup_datetime <= '$toDateTime')";
} elseif (!empty($fromdate)) {
    $fromDateTime = mysqli_real_escape_string($conn, $fromdate) . ' 00:00:00';
    $where[] = "dd.pickup_datetime >= '$fromDateTime'";
} elseif (!empty($todate)) {
    $toDateTime = mysqli_real_escape_string($conn, $todate) . ' 23:59:59';
    $where[] = "dd.pickup_datetime <= '$toDateTime'";
}

if (!empty($status)) {
    $where[] = "dd.status='".mysqli_real_escape_string($conn, $status)."'";
}

if (!empty($searchType) && !empty($searchValue)) {
    if ($searchType == 'doc') {
        $where[] = "dd.doc_no LIKE '%" . mysqli_real_escape_string($conn, $searchValue) . "%'";
    } elseif ($searchType == 'box') {
        $where[] = "dd.box LIKE '%" . mysqli_real_escape_string($conn, $searchValue) . "%'";
    }
}

if (!empty($consignor)) {
    $where[] = "dd.company_name LIKE '%" . mysqli_real_escape_string($conn, $consignor) . "%'";
}

if (!empty($consignee)) {
    $where[] = "dd.client_name LIKE '%" . mysqli_real_escape_string($conn, $consignee) . "%'";
}

// Apply office filter for branch-based access control
$officeFilter = getOfficeFilter('dd');
if (!empty($officeFilter)) {
    // Remove the leading " AND " from the filter since we're adding it to the array
    $where[] = ltrim($officeFilter, ' AND');
}

$whereSQL = "WHERE " . implode(" AND ", $where);

// Fetch dockets for this trip
$sql = "SELECT dd.*, o.office_name as branch_office_name
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        $whereSQL
        ORDER BY dd.docket_id ASC";

$result = mysqli_query($conn, $sql);

if(!$result) {
    $sqlError = mysqli_error($conn);
    error_log("SQL Error in trip_dockets.php: " . $sqlError);
}

$totalRecords = $result ? mysqli_num_rows($result) : 0;

// Get trip info
$trip_info_sql = "SELECT
                    dd.trip_group_id,
                    dd.car_number,
                    dd.driver_name,
                    dd.driver_phone,
                    MIN(dd.created_at) as created_at
                  FROM docket_details dd
                  WHERE dd.trip_group_id = '" . mysqli_real_escape_string($conn, $trip_id) . "'" . $officeFilter . "
                  GROUP BY dd.trip_group_id
                  LIMIT 1";
$trip_info_result = mysqli_query($conn, $trip_info_sql);
$trip_info = mysqli_fetch_assoc($trip_info_result);
?>

<style>
.pace, .pace-progress, .pace-activity { display: none !important; }
</style>

<script>
window.Pace = { start: function(){}, restart: function(){}, stop: function(){}, options: { startOnPageLoad: false } };
window.__NO_DATATABLES__ = true;
</script>

<div class="trip-dockets-container">
    
    <!-- Trip Info Header -->
    <div class="trip-info-banner">
        <div class="trip-info-left">
            <a href="trip.php?type=list_trips" class="btn-back">
                <i class="fa fa-arrow-left"></i> Back to Trips
            </a>
        </div>
        <div class="trip-info-center">
            <h2><i class="fa fa-truck"></i> Trip: <?= htmlspecialchars($trip_id) ?></h2>
            <?php if($trip_info): ?>
            <div class="trip-details">
                <span><i class="fa fa-car"></i> <?= htmlspecialchars($trip_info['car_number']) ?></span>
                <span><i class="fa fa-user"></i> <?= htmlspecialchars($trip_info['driver_name']) ?></span>
                <span><i class="fa fa-phone"></i> <?= htmlspecialchars($trip_info['driver_phone']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <div class="trip-info-right">
            <div class="total-dockets">
                <span class="count"><?= $totalRecords ?></span>
                <span class="label">Dockets</span>
            </div>
        </div>
    </div>
    
    <!-- Advanced Filters -->
    <div class="filter-section">
        <div class="filter-header">
            <i class="fa fa-filter"></i>
            <span>Advanced Filters</span>
        </div>
        
        <form method="get" action="" class="filter-form" id="filterForm">
            <input type="hidden" name="type" value="trip_dockets">
            <input type="hidden" name="trip_id" value="<?= htmlspecialchars($trip_id) ?>">
            
            <div class="filter-row">
                <div class="filter-col">
                    <label><i class="fa fa-calendar"></i> Date Range</label>
                    <div class="date-inputs">
                        <input type="date" name="fromdate" value="<?= htmlspecialchars($fromdate) ?>" placeholder="From">
                        <span>to</span>
                        <input type="date" name="todate" value="<?= htmlspecialchars($todate) ?>" placeholder="To">
                    </div>
                </div>
                
                <div class="filter-col">
                    <label><i class="fa fa-info-circle"></i> Status</label>
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
                
                <div class="filter-col">
                    <label><i class="fa fa-search"></i> Search By</label>
                    <div class="search-inputs">
                        <select name="searchType" style="width: 35%;">
                            <option value="">Type</option>
                            <option value="doc" <?= $searchType == 'doc' ? 'selected' : '' ?>>Doc No</option>
                            <option value="box" <?= $searchType == 'box' ? 'selected' : '' ?>>Box</option>
                        </select>
                        <input type="text" name="searchValue" value="<?= htmlspecialchars($searchValue) ?>" placeholder="Enter value" style="width: 63%;">
                    </div>
                </div>
            </div>
            
            <div class="filter-row">
                <div class="filter-col">
                    <label><i class="fa fa-building"></i> Consignor Company</label>
                    <input type="text" name="consignor" value="<?= htmlspecialchars($consignor) ?>" placeholder="Search company...">
                </div>
                
                <div class="filter-col">
                    <label><i class="fa fa-user"></i> Consignee Name</label>
                    <input type="text" name="consignee" value="<?= htmlspecialchars($consignee) ?>" placeholder="Search name...">
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-search">
                    <i class="fa fa-search"></i> Search
                </button>
                <button type="button" class="btn-reset" onclick="window.location.href='trip.php?type=trip_dockets&trip_id=<?= urlencode($trip_id) ?>'">
                    <i class="fa fa-refresh"></i> Reset
                </button>
                <?php if($totalRecords > 0): ?>
                <button type="button" class="btn-export" onclick="exportTripDockets()">
                    <i class="fa fa-file-excel-o"></i> Export to Excel
                </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Data Table -->
    <div class="table-section">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-list"></i>
                <span>Trip Dockets</span>
            </div>
            <div class="header-right">
                Showing: <strong><?= $totalRecords ?></strong> dockets
            </div>
        </div>
        
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sl</th>
                        <th>Pickup Date</th>
                        <th>Doc No</th>
                        <th>Consignor Company</th>
                        <th>Consignee Name</th>
                        <th>Client Address</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($result && $totalRecords > 0) {
                        $sl = 1;
                        while($row = mysqli_fetch_assoc($result)) {
                            $pickup_date = 'N/A';
                            if(!empty($row['pickup_datetime'])) {
                                $date = DateTime::createFromFormat('Y-m-d H:i:s', $row['pickup_datetime']);
                                if(!$date) $date = DateTime::createFromFormat('Y-m-d', $row['pickup_datetime']);
                                if($date) $pickup_date = $date->format('d-m-Y');
                            }
                            
                            $status_class = match($row['status']) {
                                'In Transit' => 'status-transit',
                                'Delivered' => 'status-delivered',
                                'Picked Up' => 'status-pending',
                                'Out for Delivery' => 'status-out',
                                'Delayed' => 'status-delayed',
                                default => 'status-default'
                            };
                    ?>
                    <tr>
                        <td><?= $sl ?></td>
                        <td><?= $pickup_date ?></td>
                        <td><strong><?= htmlspecialchars($row['doc_no'] ?? '-') ?></strong></td>
                        <td><?= htmlspecialchars($row['company_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['client_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['client_address'] ?? '-') ?></td>
                        <td><span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($row['status'] ?? '-') ?></span></td>
                        <td>
                            <div class="action-btns">
                                <a href="view_register.php?docket_id=<?= $row['docket_id'] ?>" class="btn-view" title="View">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="download_docket.php?docket_id=<?= $row['docket_id'] ?>" class="btn-download" title="Download PDF" target="_blank">
                                    <i class="fa fa-download"></i>
                                </a>
                                <a href="edit_register_new.php?docket_id=<?= $row['docket_id'] ?>" class="btn-edit" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php
                            $sl++;
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="8" class="no-data">
                            <i class="fa fa-inbox" style="font-size: 48px; color: #95a5a6; margin-bottom: 10px;"></i>
                            <p style="font-size: 18px; font-weight: 600; margin: 10px 0 5px;">No dockets found</p>
                            <p style="font-size: 14px; color: #7f8c8d;">No dockets match your filter criteria.</p>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
if (typeof jQuery !== 'undefined') {
    jQuery.fn.DataTable = function() { return this; };
    jQuery.fn.dataTable = function() { return this; };
}

function exportTripDockets() {
    const urlParams = new URLSearchParams(window.location.search);
    let exportUrl = 'export_trip_dockets.php?trip_id=<?= urlencode($trip_id) ?>&';
    const params = [];
    
    if(urlParams.get('fromdate')) params.push('fromdate=' + urlParams.get('fromdate'));
    if(urlParams.get('todate')) params.push('todate=' + urlParams.get('todate'));
    if(urlParams.get('status')) params.push('status=' + urlParams.get('status'));
    if(urlParams.get('searchType')) params.push('searchType=' + urlParams.get('searchType'));
    if(urlParams.get('searchValue')) params.push('searchValue=' + urlParams.get('searchValue'));
    if(urlParams.get('consignor')) params.push('consignor=' + urlParams.get('consignor'));
    if(urlParams.get('consignee')) params.push('consignee=' + urlParams.get('consignee'));
    
    exportUrl += params.join('&');
    window.open(exportUrl, '_blank');
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.pace, .pace-progress, .pace-activity').forEach(el => el.remove());
        document.body.classList.remove('pace-running', 'pace-active');
    }, 100);
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.trip-dockets-container {
    font-family: 'Inter', sans-serif;
    padding: 0px 15px 10px 15px;
    min-height: calc(100vh - 160px);
}

/* Trip Info Banner */
.trip-info-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 25px 30px;
    border-radius: 16px;
    margin-bottom: 25px;
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 20px;
    align-items: center;
    box-shadow: 0 4px 20px rgba(102,126,234,0.3);
}

.btn-back {
    background: rgba(255,255,255,0.2);
    color: #fff;
    padding: 12px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-back:hover {
    background: rgba(255,255,255,0.3);
    color: #fff;
    transform: translateX(-5px);
}

.trip-info-center h2 {
    margin: 0 0 10px 0;
    font-size: 1.8rem;
    font-weight: 800;
}

.trip-details {
    display: flex;
    gap: 20px;
    font-size: 0.95rem;
    opacity: 0.95;
}

.trip-details span {
    display: flex;
    align-items: center;
    gap: 6px;
}

.total-dockets {
    background: rgba(255,255,255,0.2);
    padding: 15px 25px;
    border-radius: 12px;
    text-align: center;
}

.total-dockets .count {
    display: block;
    font-size: 2.5rem;
    font-weight: 900;
    line-height: 1;
}

.total-dockets .label {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 5px;
}

/* Filter Section - Same as list_register */
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

.date-inputs,
.search-inputs {
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
.btn-reset,
.btn-export {
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

.btn-export {
    background: #28a745;
    color: #fff;
}

.btn-export:hover {
    background: #218838;
    transform: translateY(-2px);
}

/* Table Section - Same as list_register */
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

/* Status Badges */
.status-badge {
    padding: 6px 14px;
    border-radius: 18px;
    font-size: 0.85rem;
    font-weight: 700;
    display: inline-block;
    text-align: center;
    letter-spacing: 0.3px;
}

.status-transit { background: #d4edff; color: #0066cc; }
.status-delivered { background: #d4f4dd; color: #0d7d2d; }
.status-pending { background: #fff3cd; color: #856404; }
.status-out { background: #e7f3ff; color: #004085; }
.status-delayed { background: #f8d7da; color: #721c24; }
.status-default { background: #e9ecef; color: #495057; }

/* Action Buttons */
.action-btns {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: center;
}

.btn-view,
.btn-download,
.btn-edit {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s;
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

.btn-download {
    background: #27ae60;
    color: #fff;
}

.btn-download:hover {
    background: #229954;
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

/* No Data */
.no-data {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

/* Responsive */
@media (max-width: 992px) {
    .trip-info-banner {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .trip-details {
        flex-direction: column;
        gap: 10px;
    }
    
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .btn-search,
    .btn-reset,
    .btn-export {
        width: 100%;
        justify-content: center;
    }
}
</style>
