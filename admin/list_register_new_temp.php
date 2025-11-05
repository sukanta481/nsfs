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
        $where[] = "dd.box_no LIKE ?";
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

        .docket-list-wrapper .docket-list-wrapper {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Roboto', 'Helvetica Neue', Arial, sans-serif !important;
            font-size: 15px !important;
        }

        .docket-list-wrapper .container {
            max-width: 100%;
            margin: 0;
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

        .docket-list-wrapper .date-range-group {
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

        .docket-list-wrapper .search-combo {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 10px;
        }

        /* Button Styles */
        .docket-list-wrapper .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .docket-list-wrapper .btn {
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

        .docket-list-wrapper .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .docket-list-wrapper .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .docket-list-wrapper .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        /* Results Info Bar */
        .docket-list-wrapper .results-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        .docket-list-wrapper .results-count {
            font-size: 0.9375rem;
            color: #374151;
            font-weight: 600;
        }

        .results-count span {
            color: #667eea;
            font-size: 1.125rem;
        }

        /* Table Styles */
        .docket-list-wrapper .table-wrapper {
            overflow-x: auto;
        }

        .docket-list-wrapper .data-table {
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
        .docket-list-wrapper .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .docket-list-wrapper .badge-pending { background: #fef3c7; color: #92400e; }
        .docket-list-wrapper .badge-picked { background: #dbeafe; color: #1e40af; }
        .docket-list-wrapper .badge-transit { background: #e0e7ff; color: #3730a3; }
        .docket-list-wrapper .badge-delivery { background: #fed7aa; color: #92400e; }
        .docket-list-wrapper .badge-delivered { background: #d1fae5; color: #065f46; }
        .docket-list-wrapper .badge-delayed { background: #fee2e2; color: #991b1b; }

        /* Action Buttons */
        .docket-list-wrapper .action-btns {
            display: flex;
            gap: 8px;
        }

        .docket-list-wrapper .action-btn {
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
        .docket-list-wrapper .empty-state {
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

        .docket-list-wrapper .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .docket-list-wrapper .filter-tag {
            background: #e5e7eb;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #374151;
        }

        /* Alert Messages */
        .docket-list-wrapper .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }

        .docket-list-wrapper .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .docket-list-wrapper .alert-error {
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
        .docket-list-wrapper .filter-grid {
                grid-template-columns: 1fr;
            }

        .docket-list-wrapper .results-bar {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

        .docket-list-wrapper .btn-group {
                width: 100%;
            }

        .docket-list-wrapper .btn {
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
                <form method="GET" action="" id="filterForm">
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
            </div>

            <div class="card-body" style="padding: 0;">
                <?php if($totalRecords > 0): ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
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
                                <td><?= $sl++ ?></td>
                                <td><?= $pickup_date ?></td>
                                <td><strong><?= htmlspecialchars($row['doc_no'] ?? '-') ?></strong></td>
                                <td><?= htmlspecialchars($row['company_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['client_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['client_address'] ?? '-') ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($row['status'] ?? '-') ?></span></td>
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
    </script>
</div>
<!-- End docket-list-wrapper -->
