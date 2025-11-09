<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('conn.php');

// Fetch companies for dropdown
$companiesQuery = "SELECT DISTINCT company_name FROM docket_details WHERE company_name IS NOT NULL AND company_name != '' AND company_name != 'N/A' ORDER BY company_name ASC";
$companiesResult = $conn->query($companiesQuery);
$companies = [];
while($row = $companiesResult->fetch_assoc()) {
    $companies[] = $row['company_name'];
}

// Fetch clients for dropdown
$clientsQuery = "SELECT DISTINCT client_name FROM docket_details WHERE client_name IS NOT NULL AND client_name != '' AND client_name != 'N/A' ORDER BY client_name ASC";
$clientsResult = $conn->query($clientsQuery);
$clients = [];
while($row = $clientsResult->fetch_assoc()) {
    $clients[] = $row['client_name'];
}

// Get filter parameters
$fromdate = $_GET['fromdate'] ?? '';
$todate = $_GET['todate'] ?? '';
$status = $_GET['status'] ?? '';
$searchType = $_GET['searchType'] ?? '';
$searchValue = $_GET['searchValue'] ?? '';
$consignor = trim($_GET['consignor'] ?? '');
$consignee = trim($_GET['consignee'] ?? '');

// Build WHERE clause
$where = [];
$params = [];
$types = '';

// Date filter
if (!empty($fromdate) && !empty($todate)) {
    $where[] = "dd.pickup_datetime BETWEEN ? AND ?";
    $params[] = $fromdate . ' 00:00:00';
    $params[] = $todate . ' 23:59:59';
    $types .= 'ss';
} elseif (!empty($fromdate)) {
    $where[] = "dd.pickup_datetime >= ?";
    $params[] = $fromdate . ' 00:00:00';
    $types .= 's';
} elseif (!empty($todate)) {
    $where[] = "dd.pickup_datetime <= ?";
    $params[] = $todate . ' 23:59:59';
    $types .= 's';
}

// Status filter
if (!empty($status)) {
    $where[] = "dd.status = ?";
    $params[] = $status;
    $types .= 's';
}

// Search filter (Doc/Box)
if (!empty($searchType) && !empty($searchValue)) {
    if ($searchType == 'doc') {
        $where[] = "dd.doc_no LIKE ?";
        $params[] = "%$searchValue%";
        $types .= 's';
    } elseif ($searchType == 'box') {
        $where[] = "dd.box LIKE ?";
        $params[] = "%$searchValue%";
        $types .= 's';
    }
}

// Consignor filter
if (!empty($consignor)) {
    $where[] = "dd.company_name = ?";
    $params[] = $consignor;
    $types .= 's';
}

// Consignee filter
if (!empty($consignee)) {
    $where[] = "dd.client_name = ?";
    $params[] = $consignee;
    $types .= 's';
}

$whereSQL = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Build and execute query
$sql = "SELECT dd.*, 
               o.office_name as branch_office_name
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        $whereSQL
        ORDER BY dd.pickup_datetime DESC, dd.docket_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$totalRecords = $result->num_rows;

// Check for filter activity
$hasFilters = !empty($fromdate) || !empty($todate) || !empty($status) ||
              !empty($searchValue) || !empty($consignor) || !empty($consignee);

// Fetch cars for status update modal
$cars_query = "SELECT car_id, car_number, car_details FROM tbl_car WHERE status = 'Active' ORDER BY car_number";
$cars_result = mysqli_query($conn, $cars_query);

// Fetch drivers for status update modal
$drivers_query = "SELECT staff_id, staff_name, staff_phone FROM tbl_staff WHERE role = 'Driver' AND status = 'Active' ORDER BY staff_name";
$drivers_result = mysqli_query($conn, $drivers_query);

// Fetch delay reasons for status update modal
$delay_reasons_query = "SELECT reason_id, reason_category, reason_text FROM tbl_delay_reasons ORDER BY reason_category, reason_text";
$delay_reasons_result = mysqli_query($conn, $delay_reasons_query);
?>

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
        /* Scoped styles for docket list */
        .docket-list-wrapper * {
            box-sizing: border-box;
        }

        .docket-list-wrapper {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Roboto', 'Helvetica Neue', Arial, sans-serif !important;
            font-size: 15px !important;
            width: 100%;
        }

        .docket-list-wrapper .container {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Modern Card Style */
        .docket-list-wrapper .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .docket-list-wrapper .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .docket-list-wrapper .card-header h2 {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
        }

        .docket-list-wrapper .card-body {
            padding: 20px;
        }

        /* Filter Form Styles */
        .docket-list-wrapper .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .docket-list-wrapper .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .docket-list-wrapper .form-group label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .docket-list-wrapper .form-group label i {
            color: #667eea;
            font-size: 0.875rem;
        }

        .docket-list-wrapper .form-control {
            padding: 9px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
            background: #f9fafb;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .date-range-group {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 10px;
            align-items: center;
        }

        .date-range-group span {
            text-align: center;
            color: #6b7280;
            font-weight: 600;
        }

        .search-combo {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 10px;
        }

        /* Button Styles */
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        /* Results Info Bar */
        .results-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        .results-count {
            font-size: 0.9375rem;
            color: #374151;
            font-weight: 600;
        }

        .results-count span {
            color: #667eea;
            font-size: 1.125rem;
        }

        /* Table Styles */
        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead th {
            background: #f9fafb;
            padding: 14px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .data-table tbody td {
            padding: 14px;
            border-bottom: 1px solid #f3f4f6;
            color: #1f2937;
            font-size: 0.9375rem;
        }

        .data-table tbody tr {
            transition: background 0.2s ease;
        }

        .data-table tbody tr:hover {
            background: #f9fafb;
        }

        /* Status Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-picked { background: #dbeafe; color: #1e40af; }
        .badge-transit { background: #e0e7ff; color: #3730a3; }
        .badge-delivery { background: #fed7aa; color: #92400e; }
        .badge-delivered { background: #d1fae5; color: #065f46; }
        .badge-delayed { background: #fee2e2; color: #991b1b; }

        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .action-btn.view { background: #dbeafe; color: #1e40af; }
        .action-btn.view:hover { background: #3b82f6; color: white; }

        .action-btn.download { background: #d1fae5; color: #065f46; }
        .action-btn.download:hover { background: #10b981; color: white; }

        .action-btn.edit { background: #fef3c7; color: #92400e; }
        .action-btn.edit:hover { background: #f59e0b; color: white; }

        .action-btn.delete { background: #fee2e2; color: #991b1b; }
        .action-btn.delete:hover { background: #ef4444; color: white; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 0.95rem;
            margin-bottom: 20px;
        }

        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .filter-tag {
            background: #e5e7eb;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #374151;
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }

            .results-bar {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .btn-group {
                width: 100%;
            }

            .btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>

<div class="docket-list-wrapper">
    <div class="container">
        <!-- Alerts -->
        <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>Docket updated successfully!</span>
        </div>
        <?php endif; ?>

        <?php if(isset($_GET['deleted'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-trash-alt"></i>
            <span>Docket deleted successfully!</span>
        </div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_GET['error']) ?></span>
        </div>
        <?php endif; ?>

        <!-- Filter Card -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-filter"></i>
                <h2>Advanced Filters</h2>
            </div>
            <div class="card-body">
                <form method="GET" action="register.php" id="filterForm">
                    <input type="hidden" name="type" value="list_register">
                    <input type="hidden" name="lp" value="<?= htmlspecialchars($_GET['lp'] ?? 'ac') ?>">
                    
                    <div class="filter-grid">
                        <!-- Date Range -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar-alt"></i>
                                Date Range
                            </label>
                            <div class="date-range-group">
                                <input type="date" name="fromdate" class="form-control" value="<?= htmlspecialchars($fromdate) ?>" placeholder="From Date">
                                <span>to</span>
                                <input type="date" name="todate" class="form-control" value="<?= htmlspecialchars($todate) ?>" placeholder="To Date">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-info-circle"></i>
                                Status
                            </label>
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="Pending" <?= $status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Picked Up" <?= $status == 'Picked Up' ? 'selected' : '' ?>>Picked Up</option>
                                <option value="In Transit" <?= $status == 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                                <option value="Out for Delivery" <?= $status == 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                <option value="Delivered" <?= $status == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="Delayed" <?= $status == 'Delayed' ? 'selected' : '' ?>>Delayed</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-search"></i>
                                Search By
                            </label>
                            <div class="search-combo">
                                <select name="searchType" class="form-control">
                                    <option value="">Select</option>
                                    <option value="doc" <?= $searchType == 'doc' ? 'selected' : '' ?>>Doc No</option>
                                    <option value="box" <?= $searchType == 'box' ? 'selected' : '' ?>>Box No</option>
                                </select>
                                <input type="text" name="searchValue" class="form-control" value="<?= htmlspecialchars($searchValue) ?>" placeholder="Enter value...">
                            </div>
                        </div>

                        <!-- Consignor -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-building"></i>
                                Consignor Company
                            </label>
                            <select name="consignor" class="form-control">
                                <option value="">All Companies</option>
                                <?php foreach($companies as $company): ?>
                                <option value="<?= htmlspecialchars($company) ?>" <?= $consignor == $company ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($company) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Consignee -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-user"></i>
                                Consignee Name
                            </label>
                            <select name="consignee" class="form-control">
                                <option value="">All Clients</option>
                                <?php foreach($clients as $client): ?>
                                <option value="<?= htmlspecialchars($client) ?>" <?= $consignee == $client ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($client) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                            <i class="fas fa-redo"></i>
                            Reset
                        </button>
                        <?php if($totalRecords > 0): ?>
                        <button type="button" class="btn btn-success" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i>
                            Export to Excel
                        </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Card -->
        <div class="card">
            <div class="results-bar">
                <div class="results-count">
                    Showing <span><?= $totalRecords ?></span> docket(s)
                    <?php if($hasFilters): ?>
                    <span style="color: #667eea; font-weight: normal;"> (filtered)</span>
                    <?php endif; ?>
                </div>
                <?php if($totalRecords > 0): ?>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button type="button" id="bulkUpdateBtn" class="btn btn-primary" style="display: none;" onclick="openBulkStatusModal()">
                        <i class="fas fa-edit"></i> Update Status (<span id="selectedCount">0</span>)
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div class="card-body" style="padding: 0;">
                <?php if($totalRecords > 0): ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" style="cursor: pointer;">
                                </th>
                                <th>#</th>
                                <th>Date</th>
                                <th>Docket No</th>
                                <th>Consignor</th>
                                <th>Consignee</th>
                                <th>Delivery Address</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sl = 1;
                            while($row = $result->fetch_assoc()): 
                                // Format date
                                $pickup_date = 'N/A';
                                if(!empty($row['pickup_datetime'])) {
                                    $date = new DateTime($row['pickup_datetime']);
                                    $pickup_date = $date->format('d M Y');
                                }
                                
                                // Status badge class
                                $badgeClass = match($row['status']) {
                                    'Pending' => 'badge-pending',
                                    'Picked Up' => 'badge-picked',
                                    'In Transit' => 'badge-transit',
                                    'Out for Delivery' => 'badge-delivery',
                                    'Delivered' => 'badge-delivered',
                                    'Delayed' => 'badge-delayed',
                                    default => 'badge-pending'
                                };
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="docket-checkbox" value="<?= $row['docket_id'] ?>"
                                           data-doc-no="<?= htmlspecialchars($row['doc_no']) ?>"
                                           data-status="<?= htmlspecialchars($row['status']) ?>"
                                           onchange="updateBulkButton()" style="cursor: pointer;">
                                </td>
                                <td><?= $sl++ ?></td>
                                <td><?= $pickup_date ?></td>
                                <td><strong><?= htmlspecialchars($row['doc_no'] ?? '-') ?></strong></td>
                                <td><?= htmlspecialchars($row['company_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['client_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['client_address'] ?? '-') ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>"
                                          style="cursor: pointer;"
                                          onclick="openStatusModal(<?= $row['docket_id'] ?>, '<?= htmlspecialchars($row['doc_no']) ?>', '<?= htmlspecialchars($row['status']) ?>')"
                                          title="Click to update status">
                                        <?= htmlspecialchars($row['status'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="view_register.php?docket_id=<?= $row['docket_id'] ?>" class="action-btn view" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="download_docket.php?docket_id=<?= $row['docket_id'] ?>" class="action-btn download" title="Download PDF" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="edit_register_new.php?docket_id=<?= $row['docket_id'] ?>" class="action-btn edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="deleteDocket(<?= $row['docket_id'] ?>)" class="action-btn delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Dockets Found</h3>
                    <?php if($hasFilters): ?>
                    <p>No records match your filter criteria. Try adjusting your filters.</p>
                    <div class="filter-tags">
                        <?php if($fromdate || $todate): ?>
                        <span class="filter-tag">
                            <i class="fas fa-calendar"></i> <?= $fromdate ?: 'Any' ?> to <?= $todate ?: 'Any' ?>
                        </span>
                        <?php endif; ?>
                        <?php if($status): ?>
                        <span class="filter-tag">
                            <i class="fas fa-info-circle"></i> Status: <?= htmlspecialchars($status) ?>
                        </span>
                        <?php endif; ?>
                        <?php if($searchValue): ?>
                        <span class="filter-tag">
                            <i class="fas fa-search"></i> <?= ucfirst($searchType) ?>: <?= htmlspecialchars($searchValue) ?>
                        </span>
                        <?php endif; ?>
                        <?php if($consignor): ?>
                        <span class="filter-tag">
                            <i class="fas fa-building"></i> Consignor: <?= htmlspecialchars($consignor) ?>
                        </span>
                        <?php endif; ?>
                        <?php if($consignee): ?>
                        <span class="filter-tag">
                            <i class="fas fa-user"></i> Consignee: <?= htmlspecialchars($consignee) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <p>No dockets have been created yet.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Reset filters
        function resetFilters() {
            const urlParams = new URLSearchParams(window.location.search);
            const type = urlParams.get('type');
            const lp = urlParams.get('lp');
            window.location.href = `?type=${type}&lp=${lp}`;
        }

        // Delete docket
        function deleteDocket(docketId) {
            if(confirm('Are you sure you want to delete this docket?')) {
                window.location.href = `action_handler.php?action=delete_docket&docket_id=${docketId}&${window.location.search.substring(1)}`;
            }
        }

        // Export to Excel
        function exportToExcel() {
            const urlParams = new URLSearchParams(window.location.search);
            const params = [];
            
            if(urlParams.get('fromdate')) params.push('fromdate=' + urlParams.get('fromdate'));
            if(urlParams.get('todate')) params.push('todate=' + urlParams.get('todate'));
            if(urlParams.get('status')) params.push('status=' + urlParams.get('status'));
            if(urlParams.get('searchType')) params.push('searchType=' + urlParams.get('searchType'));
            if(urlParams.get('searchValue')) params.push('searchValue=' + urlParams.get('searchValue'));
            if(urlParams.get('consignor')) params.push('consignor=' + urlParams.get('consignor'));
            if(urlParams.get('consignee')) params.push('consignee=' + urlParams.get('consignee'));
            
            const exportUrl = 'export_dockets.php?' + params.join('&');
            window.open(exportUrl, '_blank');
        }

        // Date validation
        document.getElementById('filterForm').addEventListener('submit', function(e) {
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

        // Auto-dismiss alerts
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.animation = 'slideDown 0.3s ease reverse';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Bulk selection functions
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.docket-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            updateBulkButton();
        }

        function updateBulkButton() {
            const checkboxes = document.querySelectorAll('.docket-checkbox:checked');
            const bulkBtn = document.getElementById('bulkUpdateBtn');
            const countSpan = document.getElementById('selectedCount');

            if (checkboxes.length > 0) {
                bulkBtn.style.display = 'block';
                countSpan.textContent = checkboxes.length;
            } else {
                bulkBtn.style.display = 'none';
                document.getElementById('selectAll').checked = false;
            }
        }

        // Open status modal for single docket
        function openStatusModal(docketId, docketNo, currentStatus) {
            const modal = document.getElementById('statusModal');
            if (!modal) {
                console.error('Status modal not found in DOM');
                return;
            }

            // Reset form first
            const form = document.getElementById('statusForm');
            if (form) form.reset();
            hideAllConditionalFields();

            // Then set values
            const modalDocketId = document.getElementById('modalDocketId');
            const modalDocketNo = document.getElementById('modalDocketNo');
            const modalCurrentStatus = document.getElementById('modalCurrentStatus');
            const statusSelect = document.getElementById('statusSelect');
            const isBulkUpdate = document.getElementById('isBulkUpdate');
            const bulkDocketIds = document.getElementById('bulkDocketIds');
            const currentStatusInfo = document.getElementById('currentStatusInfo');

            if (modalDocketId) modalDocketId.value = docketId;
            if (modalDocketNo) modalDocketNo.textContent = docketNo;
            if (modalCurrentStatus) modalCurrentStatus.value = currentStatus;
            if (statusSelect) {
                statusSelect.setAttribute('data-current-status', currentStatus);
                statusSelect.value = ''; // Clear selection
            }
            if (isBulkUpdate) isBulkUpdate.value = '0';
            if (bulkDocketIds) bulkDocketIds.value = '';

            // Show current status info in two places
            if (currentStatusInfo) {
                currentStatusInfo.innerHTML = 'Current: <strong style="color: #667eea;">' + currentStatus + '</strong>';
                currentStatusInfo.style.display = 'block';
            }

            // Also show below dropdown
            const statusSmall = document.getElementById('currentStatusSmall');
            const statusStrong = document.getElementById('currentStatusStrong');
            if (statusSmall && statusStrong) {
                statusStrong.textContent = currentStatus;
                statusSmall.style.display = 'block';
            }

            modal.style.display = 'flex';
            disablePreviousStatusOptions();
        }

        // Open bulk status modal
        function openBulkStatusModal() {
            const modal = document.getElementById('statusModal');
            if (!modal) {
                console.error('Status modal not found in DOM');
                return;
            }

            const checkboxes = document.querySelectorAll('.docket-checkbox:checked');
            if (checkboxes.length === 0) return;

            const docketIds = Array.from(checkboxes).map(cb => cb.value);
            const docketNos = Array.from(checkboxes).map(cb => cb.dataset.docNo);

            // Reset form first
            const form = document.getElementById('statusForm');
            if (form) form.reset();
            hideAllConditionalFields();

            // Then set values
            const modalDocketId = document.getElementById('modalDocketId');
            const modalDocketNo = document.getElementById('modalDocketNo');
            const modalCurrentStatus = document.getElementById('modalCurrentStatus');
            const statusSelect = document.getElementById('statusSelect');
            const isBulkUpdate = document.getElementById('isBulkUpdate');
            const bulkDocketIds = document.getElementById('bulkDocketIds');
            const currentStatusInfo = document.getElementById('currentStatusInfo');

            if (modalDocketId) modalDocketId.value = '';
            if (modalDocketNo) modalDocketNo.textContent = docketNos.join(', ');
            if (modalCurrentStatus) modalCurrentStatus.value = '';
            if (statusSelect) {
                statusSelect.removeAttribute('data-current-status');
                statusSelect.value = ''; // Clear selection
            }
            if (isBulkUpdate) isBulkUpdate.value = '1';
            if (bulkDocketIds) bulkDocketIds.value = docketIds.join(',');

            // Hide current status info for bulk
            if (currentStatusInfo) currentStatusInfo.style.display = 'none';

            const statusSmall = document.getElementById('currentStatusSmall');
            if (statusSmall) statusSmall.style.display = 'none';

            modal.style.display = 'flex';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        function hideAllConditionalFields() {
            const dateField = document.getElementById('dateField');
            const carDriverField = document.getElementById('carDriverField');
            const delayReasonField = document.getElementById('delayReasonField');
            const podField = document.getElementById('podField');
            const errorMsg = document.getElementById('statusErrorMessage');

            if (dateField) dateField.style.display = 'none';
            if (carDriverField) carDriverField.style.display = 'none';
            if (delayReasonField) delayReasonField.style.display = 'none';
            if (podField) podField.style.display = 'none';
            if (errorMsg) errorMsg.style.display = 'none';
        }

        function handleStatusChange() {
            const status = document.getElementById('statusSelect').value;
            hideAllConditionalFields();

            if (status === 'Out for Delivery') {
                document.getElementById('dateLabelText').textContent = 'Out for Delivery Date';
                document.getElementById('dateField').style.display = 'block';
                document.getElementById('carDriverField').style.display = 'block';
            } else if (status === 'Delivered') {
                document.getElementById('dateLabelText').textContent = 'Delivery Date';
                document.getElementById('dateField').style.display = 'block';
                document.getElementById('podField').style.display = 'block';
            } else if (status === 'Delayed') {
                document.getElementById('dateLabelText').textContent = 'Delay Date';
                document.getElementById('dateField').style.display = 'block';
                document.getElementById('delayReasonField').style.display = 'block';
            }
        }

        function disablePreviousStatusOptions() {
            const statusSelect = document.getElementById('statusSelect');
            const currentStatus = statusSelect.getAttribute('data-current-status');

            if (!currentStatus) return;

            let currentOrder = 0;
            const options = statusSelect.querySelectorAll('option');

            options.forEach(option => {
                if (option.value === currentStatus) {
                    currentOrder = parseInt(option.getAttribute('data-order') || 0);
                }
            });

            // Re-enable all first
            options.forEach(option => {
                option.disabled = false;
                option.style.color = '';
                option.textContent = option.textContent.replace(' (unavailable)', '');
            });

            // Disable previous options
            options.forEach(option => {
                if (!option.value) return;

                const optionOrder = parseInt(option.getAttribute('data-order') || 0);
                const allowAnytime = option.getAttribute('data-allow-anytime') === 'true';

                if (optionOrder < currentOrder && !allowAnytime) {
                    option.disabled = true;
                    option.style.color = '#ccc';
                    if (!option.textContent.includes('(unavailable)')) {
                        option.textContent = option.textContent + ' (unavailable)';
                    }
                }
            });
        }

        function showError(message) {
            const errorDiv = document.getElementById('statusErrorMessage');
            const errorText = document.getElementById('statusErrorText');
            errorText.textContent = message;
            errorDiv.style.display = 'block';
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }

        // Auto-fill car_id and driver_id
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('carNumberInput').addEventListener('input', function() {
                const value = this.value;
                const options = document.querySelectorAll('#carList option');
                const hiddenInput = document.getElementById('carIdHidden');

                let matched = false;
                options.forEach(option => {
                    if (option.value === value) {
                        hiddenInput.value = option.getAttribute('data-id') || '';
                        matched = true;
                    }
                });

                if (!matched) {
                    hiddenInput.value = '';
                }
            });

            document.getElementById('driverNameInput').addEventListener('input', function() {
                const value = this.value;
                const options = document.querySelectorAll('#driverList option');
                const hiddenInput = document.getElementById('driverIdHidden');

                let matched = false;
                options.forEach(option => {
                    if (option.value === value) {
                        hiddenInput.value = option.getAttribute('data-id') || '';
                        matched = true;
                    }
                });

                if (!matched) {
                    hiddenInput.value = '';
                }
            });

            // Form validation
            document.getElementById('statusForm').addEventListener('submit', function(e) {
                const status = document.getElementById('statusSelect').value;

                if (!status) {
                    e.preventDefault();
                    showError('Please select a status');
                    return false;
                }

                if (status === 'Out for Delivery') {
                    const carNumber = document.getElementById('carNumberInput').value;
                    const driverName = document.getElementById('driverNameInput').value;
                    if (!carNumber || !driverName) {
                        e.preventDefault();
                        showError('Vehicle number and Driver name are required for Out for Delivery status');
                        return false;
                    }
                }

                if (status === 'Delayed') {
                    const delayReason = document.querySelector('[name="delay_reason"]').value;
                    if (!delayReason) {
                        e.preventDefault();
                        showError('Delay reason is required for Delayed status');
                        return false;
                    }
                }

                if (status === 'Delivered') {
                    const podFile = document.querySelector('[name="pod_file"]').files.length;
                    if (!podFile) {
                        e.preventDefault();
                        showError('POD file is required for Delivered status');
                        return false;
                    }
                }
            });

            // Check for error/success in URL
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            const success = urlParams.get('success');

            if (error) {
                showError(decodeURIComponent(error));
                const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]error=[^&]*/, '').replace(/^&/, '?');
                window.history.replaceState({}, document.title, cleanUrl);
            }

            if (success) {
                // Show success message (you can customize this)
                alert(decodeURIComponent(success));
                const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]success=[^&]*/, '').replace(/^&/, '?');
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });

        // Close modal on outside click
        document.addEventListener('click', function(e) {
            if (e.target.id === 'statusModal') {
                closeStatusModal();
            }
        });
    </script>

    <!-- Status Update Modal -->
    <div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 12px; width: 90%; max-width: 550px; max-height: 90vh; overflow-y: auto; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 12px 12px 0 0;">
                <h2 style="margin: 0; color: white; font-size: 22px; font-weight: 700;">
                    <i class="fa fa-refresh"></i> Update Status
                </h2>
            </div>

            <div style="padding: 25px;">
                <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #667eea;">
                    <p style="color: #7f8c8d; margin: 0; font-size: 12px; font-weight: 600; text-transform: uppercase;">Selected Docket(s)</p>
                    <p style="margin: 5px 0 0 0; font-size: 15px; font-weight: 600;">
                        <strong id="modalDocketNo" style="color: #2c3e50;"></strong>
                    </p>
                    <p id="currentStatusInfo" style="margin: 5px 0 0 0; font-size: 13px; color: #7f8c8d; display: none;"></p>
                </div>

                <form id="statusForm" method="POST" action="update_docket_status.php" enctype="multipart/form-data">
                    <input type="hidden" name="docket_id" id="modalDocketId">
                    <input type="hidden" name="current_status" id="modalCurrentStatus">
                    <input type="hidden" name="is_bulk" id="isBulkUpdate" value="0">
                    <input type="hidden" name="bulk_docket_ids" id="bulkDocketIds">

                    <!-- Error Message -->
                    <div id="statusErrorMessage" style="display: none; background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; border-left: 4px solid #dc3545; margin-bottom: 15px;">
                        <i class="fa fa-exclamation-circle"></i>
                        <span id="statusErrorText"></span>
                    </div>

                    <div class="form-group-modal">
                        <label class="label-modal">
                            <i class="fa fa-flag"></i> Status <span style="color: #dc3545;">*</span>
                        </label>
                        <select name="status" id="statusSelect" class="input-modal" required onchange="handleStatusChange()">
                            <option value="">-- Select Status --</option>
                            <option value="Pending" data-order="1">Pending</option>
                            <option value="Confirmed" data-order="2">Confirmed</option>
                            <option value="Picked Up" data-order="3">Picked Up</option>
                            <option value="In Transit" data-order="4">In Transit</option>
                            <option value="Delayed" data-order="4" data-allow-anytime="true">Delayed</option>
                            <option value="Out for Delivery" data-order="5">Out for Delivery</option>
                            <option value="Delivered" data-order="6" data-final="true">Delivered</option>
                            <option value="Failed" data-order="6" data-final="true">Failed Delivery</option>
                            <option value="Cancelled" data-order="6" data-final="true">Cancelled</option>
                        </select>
                        <small id="currentStatusSmall" class="text-muted-modal" style="display: none;">Current: <strong id="currentStatusStrong"></strong></small>
                    </div>

                    <!-- Conditional: Date Field -->
                    <div id="dateField" style="display: none; margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <label style="display: block; font-weight: 600; color: #34495e; margin-bottom: 8px; font-size: 14px;">
                            <i class="fa fa-calendar"></i> <span id="dateLabelText">Date</span> <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="datetime-local" name="status_date" style="width: 100%; padding: 10px 12px; border: 2px solid #e1e8ed; border-radius: 6px; font-size: 14px;" value="<?= date('Y-m-d\TH:i') ?>">
                    </div>

                    <!-- Conditional: Car and Driver Fields -->
                    <div id="carDriverField" style="display: none; margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; color: #34495e; margin-bottom: 8px; font-size: 14px;">
                                <i class="fa fa-car"></i> Vehicle Number <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" name="car_number" id="carNumberInput" list="carList" placeholder="Type or select vehicle number" autocomplete="off" style="width: 100%; padding: 10px 12px; border: 2px solid #e1e8ed; border-radius: 6px; font-size: 14px;">
                            <datalist id="carList">
                                <?php
                                mysqli_data_seek($cars_result, 0);
                                while ($car = mysqli_fetch_assoc($cars_result)):
                                ?>
                                <option value="<?= htmlspecialchars($car['car_number']) ?>" data-id="<?= $car['car_id'] ?>" data-details="<?= htmlspecialchars($car['car_details']) ?>">
                                    <?= htmlspecialchars($car['car_number'] . ' - ' . $car['car_details']) ?>
                                </option>
                                <?php endwhile; ?>
                            </datalist>
                            <input type="hidden" name="car_id" id="carIdHidden">
                            <small style="color: #7f8c8d; font-size: 12px;">Type manually for external vehicles or select from dropdown</small>
                        </div>

                        <div style="margin-bottom: 0;">
                            <label style="display: block; font-weight: 600; color: #34495e; margin-bottom: 8px; font-size: 14px;">
                                <i class="fa fa-user"></i> Driver Name <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" name="driver_name" id="driverNameInput" list="driverList" placeholder="Type or select driver name" autocomplete="off" style="width: 100%; padding: 10px 12px; border: 2px solid #e1e8ed; border-radius: 6px; font-size: 14px;">
                            <datalist id="driverList">
                                <?php
                                mysqli_data_seek($drivers_result, 0);
                                while ($driver = mysqli_fetch_assoc($drivers_result)):
                                ?>
                                <option value="<?= htmlspecialchars($driver['staff_name']) ?>" data-id="<?= $driver['staff_id'] ?>" data-phone="<?= htmlspecialchars($driver['staff_phone']) ?>">
                                    <?= htmlspecialchars($driver['staff_name'] . ' - ' . $driver['staff_phone']) ?>
                                </option>
                                <?php endwhile; ?>
                            </datalist>
                            <input type="hidden" name="driver_id" id="driverIdHidden">
                            <small style="color: #7f8c8d; font-size: 12px;">Type manually for external drivers or select from dropdown</small>
                        </div>
                    </div>

                    <!-- Conditional: Delay Reason -->
                    <div id="delayReasonField" style="display: none; margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <label style="display: block; font-weight: 600; color: #34495e; margin-bottom: 8px; font-size: 14px;">
                            <i class="fa fa-exclamation-triangle"></i> Delay Reason <span style="color: #dc3545;">*</span>
                        </label>
                        <select name="delay_reason" style="width: 100%; padding: 10px 12px; border: 2px solid #e1e8ed; border-radius: 6px; font-size: 14px;">
                            <option value="">-- Select Delay Reason --</option>
                            <?php
                            $current_category = '';
                            mysqli_data_seek($delay_reasons_result, 0);
                            while ($reason = mysqli_fetch_assoc($delay_reasons_result)):
                                if ($current_category != $reason['reason_category']) {
                                    if ($current_category != '') echo '</optgroup>';
                                    echo '<optgroup label="' . htmlspecialchars($reason['reason_category']) . '">';
                                    $current_category = $reason['reason_category'];
                                }
                            ?>
                            <option value="<?= htmlspecialchars($reason['reason_text']) ?>">
                                <?= htmlspecialchars($reason['reason_text']) ?>
                            </option>
                            <?php
                            endwhile;
                            if ($current_category != '') echo '</optgroup>';
                            ?>
                        </select>
                    </div>

                    <!-- Conditional: POD Upload -->
                    <div id="podField" style="display: none; margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <label style="display: block; font-weight: 600; color: #34495e; margin-bottom: 8px; font-size: 14px;">
                            <i class="fa fa-file-upload"></i> Proof of Delivery (POD) <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="file" name="pod_file" accept=".jpg,.jpeg,.png,.pdf" style="width: 100%; padding: 10px 12px; border: 2px solid #e1e8ed; border-radius: 6px; font-size: 14px;">
                        <small style="color: #7f8c8d; font-size: 12px;">Accepted: JPG, PNG, PDF (Max 5MB)</small>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; color: #34495e; margin-bottom: 8px; font-size: 14px;">
                            <i class="fa fa-map-marker-alt"></i> Current Location (Optional)
                        </label>
                        <input type="text" name="location" placeholder="e.g., Mumbai Warehouse, Delhi Hub" style="width: 100%; padding: 10px 12px; border: 2px solid #e1e8ed; border-radius: 6px; font-size: 14px;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; color: #34495e; margin-bottom: 8px; font-size: 14px;">
                            <i class="fa fa-comment"></i> Remarks (Optional)
                        </label>
                        <textarea name="remarks" rows="3" placeholder="Add any notes or comments..." style="width: 100%; padding: 10px 12px; border: 2px solid #e1e8ed; border-radius: 6px; font-size: 14px; resize: vertical;"></textarea>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <button type="submit" name="update_status" style="flex: 1; padding: 12px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                            <i class="fa fa-check"></i> Update Status
                        </button>
                        <button type="button" onclick="closeStatusModal()" style="flex: 1; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer;">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Specific Styles -->
    <style>
        .form-group-modal {
            margin-bottom: 15px;
        }

        .label-modal {
            display: block;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .label-modal i {
            margin-right: 5px;
            color: #667eea;
        }

        .input-modal {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .input-modal:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea.input-modal {
            resize: vertical;
            font-family: inherit;
        }

        .text-muted-modal {
            color: #7f8c8d;
            font-size: 13px;
            display: block;
            margin-top: 5px;
        }

        .conditional-field-modal {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

</div>
<!-- End docket-list-wrapper -->
