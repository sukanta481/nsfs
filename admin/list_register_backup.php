<?php
include('conn.php');
?>
<script type="text/javascript">
function delconfirmregister(shipping_details_id) {
    var c = confirm("Are you sure to delete?");
    if(c==true) {
        // Preserve current filters in URL after delete
        var search = window.location.search.replace(/^\?/, '');
        if (search) {
            search = search.replace(/([&\?])msg=deleted/, '').replace(/([&\?])action=delete_shipping_details/, '').replace(/([&\?])shipping_details_id=\d+/, '');
            location.href = 'action_handler.php?action=delete_shipping_details&shipping_details_id=' + shipping_details_id + '&' + search;
        } else {
            location.href = 'action_handler.php?action=delete_shipping_details&shipping_details_id=' + shipping_details_id;
        }
    }
}
</script>

<!-- Modern Filter Section -->
<div class="modern-filter-section">
    <div class="filter-header">
        <i class="fa fa-filter"></i> Advanced Filters
    </div>
    <form action="" method="get" id="filterForm">
        <input type="hidden" name="type" value="list_register">
        <input type="hidden" name="lp" value="<?= htmlspecialchars($_REQUEST['lp'] ?? 'ac') ?>">
        
        <div class="filter-grid">
            <!-- Date Range -->
            <div class="filter-group">
                <label><i class="fa fa-calendar"></i> Date Range</label>
                <div class="date-range-inputs">
                    <input type="date" name="fromdate" id="fromdate" placeholder="From Date" value="<?= htmlspecialchars($_REQUEST['fromdate'] ?? '') ?>">
                    <span class="date-separator">to</span>
                    <input type="date" name="todate" id="todate" placeholder="To Date" value="<?= htmlspecialchars($_REQUEST['todate'] ?? '') ?>">
                </div>
            </div>

            <!-- DRS Type -->
            <div class="filter-group">
                <label><i class="fa fa-file-text"></i> DRS Type</label>
                <select name="doc_type" id="doc_type">
                    <option value="">All Types</option>
                    <option value="DRS" <?= (($_REQUEST['doc_type'] ?? '') == 'DRS') ? 'selected' : '' ?>>DRS</option>
                    <option value="NON-DRS" <?= (($_REQUEST['doc_type'] ?? '') == 'NON-DRS') ? 'selected' : '' ?>>NON-DRS</option>
                </select>
            </div>

            <!-- Status -->
            <div class="filter-group">
                <label><i class="fa fa-info-circle"></i> Status</label>
                <select id="status" name="status">
                    <option value="">All Status</option>
                    <option value="Picked Up" <?= (($_REQUEST['status'] ?? '') == 'Picked Up') ? 'selected' : '' ?>>Picked Up</option>
                    <option value="In Transit" <?= (($_REQUEST['status'] ?? '') == 'In Transit') ? 'selected' : '' ?>>In Transit</option>
                    <option value="Out for Delivery" <?= (($_REQUEST['status'] ?? '') == 'Out for Delivery') ? 'selected' : '' ?>>Out for Delivery</option>
                    <option value="Delivered" <?= (($_REQUEST['status'] ?? '') == 'Delivered') ? 'selected' : '' ?>>Delivered</option>
                    <option value="Delayed" <?= (($_REQUEST['status'] ?? '') == 'Delayed') ? 'selected' : '' ?>>Delayed</option>
                    <option value="Processing" <?= (($_REQUEST['status'] ?? '') == 'Processing') ? 'selected' : '' ?>>Processing</option>
                </select>
            </div>

            <!-- Search Type -->
            <div class="filter-group">
                <label><i class="fa fa-search"></i> Search By</label>
                <div class="search-type-group">
                    <select id="type" name="type">
                        <option value="">Select Type</option>
                        <option value="doc" <?= (($_REQUEST['type'] ?? '') == 'doc') ? 'selected' : '' ?>>Doc No</option>
                        <option value="box" <?= (($_REQUEST['type'] ?? '') == 'box') ? 'selected' : '' ?>>Box/Unit</option>
                    </select>
                    <input type="text" id="typedata" name="typedata" placeholder="Enter value" value="<?= htmlspecialchars($_REQUEST['typedata'] ?? '') ?>">
                </div>
            </div>

            <!-- Consignor Company -->
            <div class="filter-group">
                <label><i class="fa fa-building"></i> Consignor Company</label>
                <input type="text" name="consignor" placeholder="Search company..." value="<?= htmlspecialchars($_REQUEST['consignor'] ?? '') ?>">
            </div>

            <!-- Consignee Name -->
            <div class="filter-group">
                <label><i class="fa fa-user"></i> Consignee Name</label>
                <input type="text" name="consignee" placeholder="Search name..." value="<?= htmlspecialchars($_REQUEST['consignee'] ?? '') ?>">
            </div>
        </div>

        <div class="filter-actions">
            <button type="submit" name="submit_filters" class="btn-filter-search">
                <i class="fa fa-search"></i> Search
            </button>
            <button type="button" id="resetBtn" class="btn-filter-reset">
                <i class="fa fa-refresh"></i> Reset
            </button>
        </div>
    </form>
</div>

<!-- Modern Table Section -->
<div class="modern-table-section">
    <div class="table-header">
        <div class="table-title">
            <i class="fa fa-list"></i> All Dockets
        </div>
        <div class="table-info">
            Total: <span id="totalCount">0</span> records
        </div>
    </div>
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Sl</th>
                    <th>Pickup Date</th>
                    <th>Doc No</th>
                    <th>DRS Type</th>
                    <th>Consignor Company</th>
                    <th>Consignee Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tableBody">
<?php
// --- Filter section ---
$doc_type = $_REQUEST['doc_type'] ?? '';
$status = $_REQUEST['status'] ?? '';
$fromdate = $_REQUEST['fromdate'] ?? '';
$todate = $_REQUEST['todate'] ?? '';
$type = $_REQUEST['type'] ?? '';
$typedata = $_REQUEST['typedata'] ?? '';
$consignor = trim($_REQUEST['consignor'] ?? '');
$consignee = trim($_REQUEST['consignee'] ?? '');

// Support POST preference if needed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doc_type = $_POST['doc_type'] ?? $doc_type;
    $status   = $_POST['status']   ?? $status;
    $fromdate = $_POST['fromdate'] ?? $fromdate;
    $todate   = $_POST['todate']   ?? $todate;
    $type     = $_POST['type']     ?? $type;
    $typedata = $_POST['typedata'] ?? $typedata;
    $consignor= trim($_POST['consignor'] ?? $consignor);
    $consignee= trim($_POST['consignee'] ?? $consignee);
}

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

// Use JOIN for consignor company name
$sql = "SELECT sd.*, c.company_title
        FROM tbl_shipping_details sd
        LEFT JOIN tbl_company c ON sd.company_id = c.company_id
        $whereSQL
        ORDER BY sd.shipping_details_id DESC";

$exe = mysqli_query($conn, $sql);
if(!$exe) {
    echo '<tr><td colspan="8" class="error-message"><i class="fa fa-exclamation-triangle"></i> Database Error: '.htmlspecialchars(mysqli_error($conn)).'</td></tr>';
} else {
    $rowCount = 1;
    $totalRecords = mysqli_num_rows($exe);
    
    if($totalRecords > 0) {
        while($row = mysqli_fetch_array($exe)) {
            $status_class = '';
            switch($row['status']) {
                case 'In Transit': $status_class = 'status-transit'; break;
                case 'Delivered': $status_class = 'status-delivered'; break;
                case 'Picked Up': $status_class = 'status-pending'; break;
                case 'Out for Delivery': $status_class = 'status-out'; break;
                case 'Delayed': $status_class = 'status-delayed'; break;
                case 'Processing': $status_class = 'status-processing'; break;
                default: $status_class = 'status-default';
            }
?>
                    <tr>
                        <td><?= $rowCount ?></td>
                        <td><?= htmlspecialchars($row['pickup_dates'] ?? 'N/A') ?></td>
                        <td><strong><?= htmlspecialchars($row['doc_no'] ?? '-') ?></strong></td>
                        <td><span class="doc-type-badge <?= strtolower($row['doc_type'] ?? '') ?>"><?= htmlspecialchars($row['doc_type'] ?? '-') ?></span></td>
                        <td><?= htmlspecialchars($row['company_title'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['client_name'] ?? '-') ?></td>
                        <td><span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($row['status'] ?? '-') ?></span></td>
                        <td>
                            <div class="action-buttons">
                                <a class="action-btn btn-edit" href="edit_register.php?shipping_details_id=<?= $row['shipping_details_id']; ?>&<?= session_name().'='.session_id();?>" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a class="action-btn btn-delete" href="javascript:void(0);" onclick="delconfirmregister('<?= $row['shipping_details_id'] ?>');" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
<?php
            $rowCount++;
        }
        echo '<script>document.getElementById("totalCount").textContent = '.$totalRecords.';</script>';
    } else {
        echo '<tr><td colspan="8" class="no-data-message"><i class="fa fa-inbox"></i><br>No dockets found matching your criteria</td></tr>';
        echo '<script>document.getElementById("totalCount").textContent = 0;</script>';
    }
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(function () {
    $('#filterForm').on('submit', function (e) {
        // Default GET: reloads with query params, no AJAX needed
    });
    $("#resetBtn").click(function (e) {
        e.preventDefault();
        $("#filterForm")[0].reset();
        window.location.href = "register.php?type=list_register"; // reset filter and reload page
    });
});
</script>
