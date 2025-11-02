<?php
include('conn.php');

// Get filter parameters
$doc_type = $_GET['doc_type'] ?? '';
$status = $_GET['status'] ?? '';
$fromdate = $_GET['fromdate'] ?? '';
$todate = $_GET['todate'] ?? '';
$type = $_GET['type'] ?? '';
$typedata = $_GET['typedata'] ?? '';
$consignor = trim($_GET['consignor'] ?? '');
$consignee = trim($_GET['consignee'] ?? '');

// Build WHERE clause
$where = [];
if (!empty($fromdate) && !empty($todate)) {
    $where[] = "(pickup_dates BETWEEN '".mysqli_real_escape_string($conn, $fromdate)."' AND '".mysqli_real_escape_string($conn, $todate)."')";
}
if (!empty($doc_type)) {
    $where[] = "sd.doc_type='".mysqli_real_escape_string($conn, $doc_type)."'";
}
if (!empty($status)) {
    $where[] = "sd.status='".mysqli_real_escape_string($conn, $status)."'";
}
if (!empty($type) && !empty($typedata)) {
    if ($type == 'doc') {
        $where[] = "sd.doc_no='".mysqli_real_escape_string($conn, $typedata)."'";
    }
    if ($type == 'box') {
        $where[] = "sd.box='".mysqli_real_escape_string($conn, $typedata)."'";
    }
}
if (!empty($consignor)) {
    $where[] = "c.company_title LIKE '%" . mysqli_real_escape_string($conn, $consignor) . "%'";
}
if (!empty($consignee)) {
    $where[] = "sd.client_name LIKE '%" . mysqli_real_escape_string($conn, $consignee) . "%'";
}
$whereSQL = (count($where) > 0) ? ("WHERE " . implode(" AND ", $where)) : "";

// Fetch data
$sql = "SELECT sd.*, c.company_title
        FROM tbl_shipping_details sd
        LEFT JOIN tbl_company c ON sd.company_id = c.company_id
        $whereSQL
        ORDER BY sd.shipping_details_id DESC";

$result = mysqli_query($conn, $sql);
$totalRecords = $result ? mysqli_num_rows($result) : 0;
?>

<!DOCTYPE html>
<style>
/* Disable ALL loading indicators */
.pace, .pace-progress, .pace-activity, .pace-running .pace, 
.dataTables_processing, .dataTables_wrapper { 
    display: none !important; 
    visibility: hidden !important;
}
</style>

<script>
// Block Pace.js completely
window.Pace = { start: function(){}, restart: function(){}, stop: function(){}, options: { startOnPageLoad: false } };
// Block DataTables on this page
window.__NO_DATATABLES__ = true;
</script>

<div class="register-list-container">
    <!-- Advanced Filters -->
    <div class="filter-section">
        <div class="filter-header">
            <i class="fa fa-filter"></i>
            <span>Advanced Filters</span>
        </div>
        
        <form method="get" action="" class="filter-form" id="filterForm">
            <input type="hidden" name="type" value="list_register">
            <input type="hidden" name="lp" value="<?= htmlspecialchars($_GET['lp'] ?? 'ac') ?>">
            
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
                    <label><i class="fa fa-file-text"></i> DRS Type</label>
                    <select name="doc_type">
                        <option value="">All Types</option>
                        <option value="DRS" <?= $doc_type == 'DRS' ? 'selected' : '' ?>>DRS</option>
                        <option value="NON-DRS" <?= $doc_type == 'NON-DRS' ? 'selected' : '' ?>>NON-DRS</option>
                    </select>
                </div>
                
                <div class="filter-col">
                    <label><i class="fa fa-info-circle"></i> Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="Picked Up" <?= $status == 'Picked Up' ? 'selected' : '' ?>>Picked Up</option>
                        <option value="In Transit" <?= $status == 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                        <option value="Out for Delivery" <?= $status == 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                        <option value="Delivered" <?= $status == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="Delayed" <?= $status == 'Delayed' ? 'selected' : '' ?>>Delayed</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-row">
                <div class="filter-col">
                    <label><i class="fa fa-search"></i> Search By</label>
                    <div class="search-inputs">
                        <select name="type" style="width: 35%;">
                            <option value="">Type</option>
                            <option value="doc" <?= $type == 'doc' ? 'selected' : '' ?>>Doc No</option>
                            <option value="box" <?= $type == 'box' ? 'selected' : '' ?>>Box</option>
                        </select>
                        <input type="text" name="typedata" value="<?= htmlspecialchars($typedata) ?>" placeholder="Enter value" style="width: 63%;">
                    </div>
                </div>
                
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
                <button type="button" class="btn-reset" onclick="window.location.href='register.php?type=list_register&lp=<?= htmlspecialchars($_GET['lp'] ?? 'ac') ?>'">
                    <i class="fa fa-refresh"></i> Reset
                </button>
            </div>
        </form>
    </div>
    
    <!-- Data Table -->
    <div class="table-section">
        <div class="table-header">
            <div class="header-left">
                <i class="fa fa-list"></i>
                <span>All Dockets</span>
            </div>
            <div class="header-right">
                Total: <strong><?= $totalRecords ?></strong> records
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
                            // Format date
                            $pickup_date = 'N/A';
                            if(!empty($row['pickup_dates'])) {
                                $date = DateTime::createFromFormat('Y-m-d H:i:s', $row['pickup_dates']);
                                if(!$date) $date = DateTime::createFromFormat('Y-m-d', $row['pickup_dates']);
                                if($date) $pickup_date = $date->format('d-m-Y');
                            }
                            
                            // Status class
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
                        <td><?= htmlspecialchars($row['company_title'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['client_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['client_address'] ?? '-') ?></td>
                        <td><span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($row['status'] ?? '-') ?></span></td>
                        <td>
                            <div class="action-btns">
                                <a href="edit_register.php?shipping_details_id=<?= $row['shipping_details_id'] ?>&<?= session_name().'='.session_id() ?>" class="btn-edit" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="javascript:void(0)" onclick="deleteRecord(<?= $row['shipping_details_id'] ?>)" class="btn-delete" title="Delete">
                                    <i class="fa fa-trash"></i>
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
                            <i class="fa fa-inbox"></i>
                            <p>No dockets found</p>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Block DataTables and other initializations
if (typeof jQuery !== 'undefined') {
    jQuery.fn.DataTable = function() { return this; };
    jQuery.fn.dataTable = function() { return this; };
}
window.handleDataTableButtons = function() {};
window.TableManageButtons = { init: function() {} };

function deleteRecord(id) {
    if(confirm('Are you sure you want to delete this record?')) {
        window.location.href = 'action_handler.php?action=delete_shipping_details&shipping_details_id=' + id + '&' + window.location.search.substring(1);
    }
}

// Remove pace elements
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.pace, .pace-progress, .pace-activity').forEach(el => el.remove());
        document.body.classList.remove('pace-running', 'pace-active');
    }, 100);
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.register-list-container {
    font-family: 'Inter', sans-serif;
    padding: 0 35px 120px 35px;
    min-height: calc(100vh - 160px);
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
}

.btn-edit,
.btn-delete {
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

/* No Data */
.no-data {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.no-data i {
    font-size: 3.5rem;
    opacity: 0.5;
    margin-bottom: 15px;
    display: block;
}

.no-data p {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
}

/* Responsive */
@media (max-width: 992px) {
    .register-list-container {
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
    .date-inputs,
    .search-inputs {
        flex-direction: column;
    }
    
    .search-inputs select,
    .search-inputs input {
        width: 100% !important;
    }
    
    .data-table thead th,
    .data-table tbody td {
        padding: 12px 10px;
        font-size: 0.85rem;
    }
}
</style>
