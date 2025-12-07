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
    $error = 'Docket not found';
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

// Status color configuration
$statusColors = [
    'Pending' => ['bg' => '#fff3cd', 'color' => '#856404', 'icon' => 'hourglass-start', 'iconBg' => '#ffc107'],
    'Confirmed' => ['bg' => '#cfe2ff', 'color' => '#084298', 'icon' => 'check-circle', 'iconBg' => '#17a2b8'],
    'Picked Up' => ['bg' => '#d1ecf1', 'color' => '#0c5460', 'icon' => 'cube', 'iconBg' => '#007bff'],
    'Manifest Created' => ['bg' => '#e8daef', 'color' => '#5b2c6f', 'icon' => 'clipboard', 'iconBg' => '#6f42c1'],
    'In Transit' => ['bg' => '#d4edda', 'color' => '#155724', 'icon' => 'truck', 'iconBg' => '#20c997'],
    'In Transit to Branch' => ['bg' => '#d1ecf1', 'color' => '#0c5460', 'icon' => 'road', 'iconBg' => '#17a2b8'],
    'Received' => ['bg' => '#d4edda', 'color' => '#155724', 'icon' => 'inbox', 'iconBg' => '#198754'],
    'Arrived at Branch' => ['bg' => '#cff4fc', 'color' => '#055160', 'icon' => 'map-marker', 'iconBg' => '#0dcaf0'],
    'Out for Delivery' => ['bg' => '#ffe5d0', 'color' => '#984c0c', 'icon' => 'truck', 'iconBg' => '#fd7e14'],
    'Delivered' => ['bg' => '#d4edda', 'color' => '#155724', 'icon' => 'check', 'iconBg' => '#28a745'],
    'Failed' => ['bg' => '#f8d7da', 'color' => '#721c24', 'icon' => 'exclamation-triangle', 'iconBg' => '#dc3545'],
    'Delayed' => ['bg' => '#fce4ec', 'color' => '#880e4f', 'icon' => 'clock-o', 'iconBg' => '#e83e8c'],
    'Returned' => ['bg' => '#e2e3e5', 'color' => '#41464b', 'icon' => 'undo', 'iconBg' => '#6c757d'],
    'Cancelled' => ['bg' => '#e2e3e5', 'color' => '#41464b', 'icon' => 'times-circle', 'iconBg' => '#6c757d']
];

function getStatusStyle($status, $statusColors) {
    return $statusColors[$status] ?? ['bg' => '#f8f9fa', 'color' => '#495057', 'icon' => 'circle', 'iconBg' => '#6c757d'];
}

$currentStatus = isset($docket) ? ($docket['status'] ?? 'Pending') : 'Pending';
$currentStyle = getStatusStyle($currentStatus, $statusColors);
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Inter, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .tracking-container { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden; }
        .tracking-header { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: #fff; padding: 30px; text-align: center; }
        .tracking-header h1 { font-size: 2rem; margin-bottom: 10px; }
        .tracking-number { font-size: 1.5rem; font-weight: 800; color: #ffc107; margin-top: 10px; }
        .tracking-body { padding: 40px 30px; }
        .status-current { text-align: center; padding: 35px; background: linear-gradient(135deg, <?= $currentStyle["bg"] ?> 0%, #fff 100%); border-radius: 20px; margin-bottom: 30px; border: 2px solid <?= $currentStyle["iconBg"] ?>; }
        .status-badge-huge { display: inline-block; padding: 18px 50px; border-radius: 35px; font-size: 1.4rem; font-weight: 800; text-transform: uppercase; background: <?= $currentStyle["bg"] ?>; color: <?= $currentStyle["color"] ?>; }
        .docket-info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 35px; }
        .info-box { padding: 22px; background: #f8f9fa; border-radius: 15px; border-left: 5px solid #4a90e2; }
        .timeline { position: relative; padding-left: 50px; }
        .timeline::before { content: ""; position: absolute; left: 20px; top: 0; bottom: 0; width: 4px; background: linear-gradient(180deg, #28a745 0%, #e9ecef 100%); }
        .timeline-item { position: relative; padding-bottom: 35px; }
        .timeline-icon { position: absolute; left: -42px; top: 0; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 4px solid #fff; }
        .timeline-content { background: #f8f9fa; padding: 20px 25px; border-radius: 15px; }
        .timeline-notes { margin-top: 12px; padding: 15px; background: #fff; border-radius: 10px; border-left: 3px solid #667eea; }
        .badge-latest { background: #28a745; color: #fff; padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; }
        @media (max-width: 768px) { .docket-info-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="tracking-container">
        <div class="tracking-header">
            <h1><i class="fa fa-truck"></i> Track Your Shipment</h1>
            <div class="tracking-number"><?= htmlspecialchars($doc_no) ?></div>
        </div>
        <div class="tracking-body">
            <?php if(isset($error)): ?>
                <div style="text-align: center; padding: 60px; color: #e74c3c;">
                    <i class="fa fa-exclamation-triangle" style="font-size: 5rem; display: block; margin-bottom: 20px;"></i>
                    <h2><?= htmlspecialchars($error) ?></h2>
                </div>
            <?php else: ?>
                <div class="status-current">
                    <h2 style="color: #6c757d; margin-bottom: 15px;">Current Status</h2>
                    <span class="status-badge-huge"><?= htmlspecialchars($currentStatus) ?></span>
                </div>
                <div class="docket-info-grid">
                    <div class="info-box"><h3 style="color: #6c757d;">Consignor</h3><p style="font-weight: 600;"><?= htmlspecialchars($docket["company_name"] ?? "N/A") ?></p></div>
                    <div class="info-box"><h3 style="color: #6c757d;">Consignee</h3><p style="font-weight: 600;"><?= htmlspecialchars($docket["client_name"] ?? "N/A") ?></p></div>
                    <div class="info-box"><h3 style="color: #6c757d;">Pickup Location</h3><p style="font-weight: 600;"><?= htmlspecialchars($docket["pickup_location"] ?? "N/A") ?></p></div>
                    <div class="info-box"><h3 style="color: #6c757d;">Delivery Location</h3><p style="font-weight: 600;"><?= htmlspecialchars($docket["delivery_location"] ?? "N/A") ?></p></div>
                </div>
                <div style="margin-top: 45px;">
                    <h2 style="color: #2c3e50; margin-bottom: 25px;"><i class="fa fa-history" style="color: #667eea;"></i> Tracking History</h2>
                    <div class="timeline">
                        <?php if(!empty($history)): foreach($history as $index => $h): $statusStyle = getStatusStyle($h["new_status"], $statusColors); ?>
                            <div class="timeline-item">
                                <div class="timeline-icon" style="background: <?= $statusStyle["iconBg"] ?>;"><i class="fa fa-<?= $statusStyle["icon"] ?>" style="color: #fff;"></i></div>
                                <div class="timeline-content" style="border-left: 4px solid <?= $statusStyle["iconBg"] ?>;">
                                    <div style="font-weight: 700; color: <?= $statusStyle["color"] ?>;"><?= htmlspecialchars($h["new_status"]) ?><?php if($index === 0): ?> <span class="badge-latest">LATEST</span><?php endif; ?></div>
                                    <div style="color: #6c757d;"><?= date("D, M d, Y g:i A", strtotime($h["changed_at"])) ?></div>
                                    <?php if(!empty($h["notes"])): ?><div class="timeline-notes"><?= nl2br(htmlspecialchars($h["notes"])) ?></div><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <div class="timeline-item">
                                <div class="timeline-icon" style="background: <?= $currentStyle["iconBg"] ?>;"><i class="fa fa-<?= $currentStyle["icon"] ?>" style="color: #fff;"></i></div>
                                <div class="timeline-content"><div style="font-weight: 700; color: <?= $currentStyle["color"] ?>;"><?= htmlspecialchars($currentStatus) ?></div><div style="color: #6c757d;"><?= date("D, M d, Y g:i A", strtotime($docket["created_at"])) ?></div></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>