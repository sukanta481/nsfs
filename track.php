<?php
require 'admin/conn.php';

$doc_no = $_GET['doc_no'] ?? '';

if(empty($doc_no)) {
    header('Location: index.php');
    exit;
}

// Fetch docket details
$doc_no_escaped = mysqli_real_escape_string($conn, $doc_no);
$sql = "SELECT dd.*, o.office_name 
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        WHERE dd.doc_no = '$doc_no_escaped'";
$result = mysqli_query($conn, $sql);
$docket = mysqli_fetch_assoc($result);

if(!$docket) {
    $error = "Docket not found";
}

// Fetch status history
$history = [];
if($docket) {
    $docket_id = $docket['docket_id'];
    $history_sql = "SELECT * FROM docket_status_history WHERE docket_id = $docket_id ORDER BY changed_at DESC";
    $history_result = mysqli_query($conn, $history_sql);
    if($history_result) {
        while($row = mysqli_fetch_assoc($history_result)) {
            $history[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Docket - <?= htmlspecialchars($doc_no) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .tracking-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .tracking-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #fff;
            padding: 30px;
            text-align: center;
        }
        
        .tracking-header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .tracking-number {
            font-size: 1.5rem;
            font-weight: 800;
            color: #ffc107;
            margin-top: 10px;
        }
        
        .tracking-body {
            padding: 40px 30px;
        }
        
        <?php if(isset($error)): ?>
        .error-message {
            text-align: center;
            padding: 40px;
            color: #e74c3c;
        }
        
        .error-message i {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        <?php else: ?>
        .status-current {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .status-current h2 {
            font-size: 1.3rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .status-badge-huge {
            display: inline-block;
            padding: 15px 40px;
            border-radius: 30px;
            font-size: 1.5rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-picked-up { background: #cce5ff; color: #004085; }
        .status-in-transit { background: #d1ecf1; color: #0c5460; }
        .status-out-for-delivery { background: #d4edda; color: #155724; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-delayed { background: #f8d7da; color: #721c24; }
        
        .docket-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-box {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #4a90e2;
        }
        
        .info-box h3 {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .info-box p {
            font-size: 1.1rem;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .timeline-section {
            margin-top: 40px;
        }
        
        .timeline-section h2 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .timeline {
            position: relative;
            padding-left: 40px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 30px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -33px;
            top: 5px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: #4a90e2;
            border: 3px solid #fff;
            box-shadow: 0 0 0 3px #4a90e2;
        }
        
        .timeline-item.active::before {
            background: #27ae60;
            box-shadow: 0 0 0 3px #27ae60;
        }
        
        .timeline-content {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 10px;
        }
        
        .timeline-status {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .timeline-date {
            color: #6c757d;
            font-size: 0.9rem;
        }
        <?php endif; ?>
        
        @media (max-width: 768px) {
            .docket-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="tracking-container">
        <div class="tracking-header">
            <h1><i class="fa fa-truck"></i> Track Your Docket</h1>
            <div class="tracking-number"><?= htmlspecialchars($doc_no) ?></div>
        </div>
        
        <div class="tracking-body">
            <?php if(isset($error)): ?>
                <div class="error-message">
                    <i class="fa fa-exclamation-triangle"></i>
                    <h2><?= htmlspecialchars($error) ?></h2>
                    <p>Please check your tracking number and try again.</p>
                </div>
            <?php else: ?>
                <div class="status-current">
                    <h2>Current Status</h2>
                    <span class="status-badge-huge status-<?= strtolower(str_replace(' ', '-', $docket['status'])) ?>">
                        <?= htmlspecialchars($docket['status']) ?>
                    </span>
                </div>
                
                <div class="docket-info-grid">
                    <div class="info-box">
                        <h3>From</h3>
                        <p><?= htmlspecialchars($docket['company_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="info-box">
                        <h3>To</h3>
                        <p><?= htmlspecialchars($docket['client_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="info-box">
                        <h3>Pickup Location</h3>
                        <p><?= htmlspecialchars($docket['pickup_location'] ?? 'N/A') ?></p>
                    </div>
                    <div class="info-box">
                        <h3>Delivery Location</h3>
                        <p><?= htmlspecialchars($docket['delivery_location'] ?? 'N/A') ?></p>
                    </div>
                </div>
                
                <div class="timeline-section">
                    <h2><i class="fa fa-history"></i> Tracking History</h2>
                    <div class="timeline">
                        <?php if(!empty($history)): ?>
                            <?php foreach($history as $index => $h): ?>
                                <div class="timeline-item <?= $index === 0 ? 'active' : '' ?>">
                                    <div class="timeline-content">
                                        <div class="timeline-status"><?= htmlspecialchars($h['new_status']) ?></div>
                                        <div class="timeline-date"><?= date('M d, Y g:i A', strtotime($h['changed_at'])) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="timeline-item active">
                                <div class="timeline-content">
                                    <div class="timeline-status"><?= htmlspecialchars($docket['status']) ?></div>
                                    <div class="timeline-date"><?= date('M d, Y g:i A', strtotime($docket['created_at'])) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
