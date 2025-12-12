<?php
// Enhanced Delivery History with Detailed Notes
// This file provides comprehensive tracking with office details, manifest info, and rich history

// Detect AJAX
$is_ajax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ||
    (isset($_POST['ajax']) && $_POST['ajax'] == '1')
);

if (!$is_ajax) include("include/header.php");

$doc_no = isset($_GET['doc_no']) ? mysqli_real_escape_string($conn, trim($_GET['doc_no'])) : '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['doc_no'])) {
    $doc_no = mysqli_real_escape_string($conn, trim($_POST['doc_no']));
}

$get_shipping_details_row = false;
$tracking_history = [];
$has_manifest = false;
$manifest_info = [];

if ($doc_no) {
    // Query from docket_details table with all related info
    $get_shipping_details_sql = "SELECT dd.*, 
                                         o.office_name as pickup_office, 
                                         o.office_address as pickup_office_address,
                                         o.contact_number as pickup_office_phone,
                                         u.full_name as creator_name, 
                                         u.username as creator_username
                                  FROM docket_details dd
                                  LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
                                  LEFT JOIN tbl_users u ON dd.created_by = u.user_id
                                  WHERE dd.doc_no='" . $doc_no . "'";
    $get_shipping_details_rs = mysqli_query($conn, $get_shipping_details_sql);
    $get_shipping_details_row = mysqli_fetch_assoc($get_shipping_details_rs);
}

if ($get_shipping_details_row) {
    $docket_id = $get_shipping_details_row['docket_id'];
    
    // Check manifest info
    $manifest_check_sql = "SELECT m.*, 
                                   from_office.office_name as from_office_name,
                                   from_office.contact_number as from_office_phone,
                                   to_office.office_name as to_office_name,
                                   to_office.office_address as to_office_address,
                                   to_office.contact_number as to_office_phone
                            FROM manifest_dockets md
                            LEFT JOIN manifest m ON md.manifest_id = m.manifest_id
                            LEFT JOIN tbl_offices from_office ON m.from_office = from_office.office_id
                            LEFT JOIN tbl_offices to_office ON m.to_office = to_office.office_id
                            WHERE md.docket_id = '$docket_id'
                            LIMIT 1";
    $manifest_check_rs = @mysqli_query($conn, $manifest_check_sql);
    
    if ($manifest_check_rs && mysqli_num_rows($manifest_check_rs) > 0) {
        $has_manifest = true;
        $manifest_info = mysqli_fetch_assoc($manifest_check_rs);
    }
    
    // Fetch comprehensive tracking history
    $history_sql = "SELECT dsh.*, 
                           o.office_name, 
                           o.contact_number as office_phone,
                           o.office_address,
                           u.full_name as updated_by_full_name,
                           u.username as updated_by_username
                    FROM docket_status_history dsh
                    LEFT JOIN tbl_offices o ON dsh.office_id = o.office_id
                    LEFT JOIN tbl_users u ON dsh.updated_by = u.user_id
                    WHERE dsh.docket_id = '$docket_id'
                    ORDER BY dsh.changed_at ASC";
    $history_rs = @mysqli_query($conn, $history_sql);
    
    if ($history_rs && mysqli_num_rows($history_rs) > 0) {
        while ($row = mysqli_fetch_assoc($history_rs)) {
            $tracking_history[] = $row;
        }
    }
    
    // Build enhanced timeline
    $timeline = [];
    $current_status = $get_shipping_details_row['status'] ?? '';
    
    // 1. PICKUP STATUS
    $pickup_time = $get_shipping_details_row['pickup_datetime'] ?? $get_shipping_details_row['created_at'];
    $pickup_location = $get_shipping_details_row['pickup_location'] ?? $get_shipping_details_row['pickup_office'] ?? '';
    $creator_name = $get_shipping_details_row['creator_name'] ?: $get_shipping_details_row['creator_username'] ?: 'System';
    
    $timeline[] = [
        'status' => 'Picked Up',
        'icon' => 'fa-box-open',
        'time' => $pickup_time ? date('d M Y, h:i A', strtotime($pickup_time)) : '',
        'location' => $pickup_location,
        'office' => $get_shipping_details_row['pickup_office'] ?? '',
        'office_phone' => $get_shipping_details_row['pickup_office_phone'] ?? '',
        'details' => "Docket created by <strong>{$creator_name}</strong> at <strong>{$pickup_location}</strong>",
        'completed' => true,
        'is_current' => false,
        'color' => 'success'
    ];
    
    // 2. MANIFEST / TRANSIT TO BRANCH (if applicable)
    if ($has_manifest && $manifest_info) {
        $manifest_created = $manifest_info['created_at'] ?? '';
        $to_office = $manifest_info['to_office_name'] ?? '';
        $to_office_phone = $manifest_info['to_office_phone'] ?? '';
        
        $timeline[] = [
            'status' => 'In Transit to Branch',
            'icon' => 'fa-truck-loading',
            'time' => $manifest_created ? date('d M Y, h:i A', strtotime($manifest_created)) : '',
            'location' => "Transferring to {$to_office}",
            'office' => $to_office,
            'office_phone' => $to_office_phone,
            'details' => "Parcel transferred to <strong>{$to_office}</strong> via Manifest #{$manifest_info['manifest_no']}. Contact: {$to_office_phone}",
            'completed' => (strtotime($current_status) >= strtotime($manifest_created)),
            'is_current' => ($current_status == 'In Transit'),
            'color' => 'info'
        ];
        
        // Check if branch received
        $branch_received = false;
        foreach ($tracking_history as $h) {
            if ($h['new_status'] == 'Arrived at Branch' || $h['new_status'] == 'Received at Branch') {
                $branch_received = true;
                $timeline[] = [
                    'status' => 'Received at Branch',
                    'icon' => 'fa-warehouse',
                    'time' => date('d M Y, h:i A', strtotime($h['changed_at'])),
                    'location' => $h['location'] ?? $to_office,
                    'office' => $h['office_name'] ?? $to_office,
                    'office_phone' => $h['office_phone'] ?? $to_office_phone,
                    'details' => "Parcel received at <strong>{$to_office}</strong> and ready for local delivery",
                    'completed' => true,
                    'is_current' => false,
                    'color' => 'success'
                ];
                break;
            }
        }
    } else {
        // Direct delivery - add In Transit
        $in_transit = false;
        foreach ($tracking_history as $h) {
            if ($h['new_status'] == 'In Transit') {
                $in_transit = true;
                $timeline[] = [
                    'status' => 'In Transit',
                    'icon' => 'fa-truck',
                    'time' => date('d M Y, h:i A', strtotime($h['changed_at'])),
                    'location' => $h['location'] ?? '',
                    'office' => $h['office_name'] ?? '',
                    'office_phone' => $h['office_phone'] ?? '',
                    'details' => $h['notes'] ?? 'Parcel is in transit',
                    'completed' => true,
                    'is_current' => ($current_status == 'In Transit'),
                    'color' => 'info'
                ];
                break;
            }
        }
    }
    
    // 3. OUT FOR DELIVERY
    $out_for_delivery = false;
    foreach ($tracking_history as $h) {
        if ($h['new_status'] == 'Out for Delivery') {
            $out_for_delivery = true;
            $delivery_office = $h['office_name'] ?? $get_shipping_details_row['pickup_office'] ?? '';
            $car_no = $h['car_number'] ?? $get_shipping_details_row['car_number'] ?? '';
            $driver_name = $h['driver_name'] ?? $get_shipping_details_row['driver_name'] ?? '';
            $driver_phone = $h['driver_phone'] ?? $get_shipping_details_row['driver_phone'] ?? '';
            
            $timeline[] = [
                'status' => 'Out for Delivery',
                'icon' => 'fa-shipping-fast',
                'time' => date('d M Y, h:i A', strtotime($h['changed_at'])),
                'location' => $h['location'] ?? '',
                'office' => $delivery_office,
                'office_phone' => $h['office_phone'] ?? '',
                'car_no' => $car_no,
                'driver_name' => $driver_name,
                'driver_phone' => $driver_phone,
                'details' => "Out for delivery from <strong>{$delivery_office}</strong><br>Vehicle: <strong>{$car_no}</strong><br>Driver: <strong>{$driver_name}</strong> ({$driver_phone})",
                'completed' => true,
                'is_current' => ($current_status == 'Out for Delivery'),
                'color' => 'warning'
            ];
            break;
        }
    }
    
    // 4. DELIVERED / POD PENDING
    $is_delivered = ($current_status == 'Delivered' || $current_status == 'Pending POD');
    $pod_file = $get_shipping_details_row['proof_of_delivery'] ?? '';
    $has_pod = !empty($pod_file);
    
    if ($is_delivered) {
        foreach ($tracking_history as $h) {
            if ($h['new_status'] == 'Delivered' || $h['new_status'] == 'Pending POD') {
                $status_label = $has_pod ? 'Delivered' : 'Delivered (POD Pending)';
                $timeline[] = [
                    'status' => $status_label,
                    'icon' => $has_pod ? 'fa-check-circle' : 'fa-clock',
                    'time' => date('d M Y, h:i A', strtotime($h['changed_at'])),
                    'location' => $h['location'] ?? '',
                    'office' => $h['office_name'] ?? '',
                    'office_phone' => $h['office_phone'] ?? '',
                    'pod_status' => $has_pod ? 'available' : 'pending',
                    'pod_file' => $pod_file,
                    'details' => $has_pod ? 'Parcel successfully delivered with proof of delivery' : 'Parcel delivered, waiting for POD upload',
                    'completed' => true,
                    'is_current' => true,
                    'color' => $has_pod ? 'success' : 'warning'
                ];
                break;
            }
        }
    }
    
    // 5. DELAYED STATUS (can occur at any stage)
    $delayed_entries = [];
    foreach ($tracking_history as $h) {
        if ($h['new_status'] == 'Delayed' || $h['is_delayed'] == 1) {
            $delayed_entries[] = [
                'time' => date('d M Y, h:i A', strtotime($h['changed_at'])),
                'reason' => $h['delay_reason'] ?? 'Delay reported',
                'notes' => $h['notes'] ?? '',
                'location' => $h['location'] ?? ''
            ];
        }
    }
    
    // 6. CANCELLED STATUS
    $is_cancelled = ($current_status == 'Cancelled');
    if ($is_cancelled) {
        foreach ($tracking_history as $h) {
            if ($h['new_status'] == 'Cancelled') {
                $timeline[] = [
                    'status' => 'Cancelled',
                    'icon' => 'fa-times-circle',
                    'time' => date('d M Y, h:i A', strtotime($h['changed_at'])),
                    'location' => $h['location'] ?? '',
                    'office' => $h['office_name'] ?? '',
                    'details' => $h['notes'] ?? 'Shipment cancelled',
                    'completed' => true,
                    'is_current' => true,
                    'color' => 'danger'
                ];
                break;
            }
        }
    }
    
    // Determine current step
    $current_step_index = 0;
    foreach ($timeline as $i => $step) {
        if ($step['is_current'] || $step['completed']) {
            $current_step_index = $i;
        }
    }
    
    // Shipment details
    $client_name = $get_shipping_details_row['client_name'] ?? '-';
    $pickup_date = $pickup_time ? date('d M Y', strtotime($pickup_time)) : '-';
    $car_no = $get_shipping_details_row['car_number'] ?? '-';
    $delivery_agent = $get_shipping_details_row['driver_name'] ?? '-';
    $contact_number = $get_shipping_details_row['driver_phone'] ?? '-';
    
} else {
    $timeline = [];
    $tracking_history = [];
    $delayed_entries = [];
    $current_step_index = 0;
}

if (!$is_ajax) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Shipment - <?= htmlspecialchars($doc_no) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e8f0f7 0%, #f5f8fb 100%);
            color: #2c3e50;
            min-height: 100vh;
        }
        
        .tracking-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .tracking-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.6s ease-out;
        }
        
        .tracking-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #5551c0 0%, #7b77e8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        .tracking-header p {
            font-size: 1.1rem;
            color: #6c757d;
        }
        
        .search-box {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #5551c0;
            box-shadow: 0 0 0 3px rgba(85, 81, 192, 0.1);
        }
        
        .search-btn {
            padding: 15px 35px;
            background: linear-gradient(135deg, #5551c0 0%, #7b77e8 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(85, 81, 192, 0.3);
        }
        
        .tracking-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            animation: fadeInUp 0.7s ease-out;
        }
        
        .timeline-card, .details-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #232351;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .timeline {
            position: relative;
            padding-left: 50px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(to bottom, #e1e8ed 0%, #e1e8ed 100%);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 40px;
            animation: slideInLeft 0.5s ease-out;
            animation-fill-mode: both;
        }
        
        .timeline-item:nth-child(1) { animation-delay: 0.1s; }
        .timeline-item:nth-child(2) { animation-delay: 0.2s; }
        .timeline-item:nth-child(3) { animation-delay: 0.3s; }
        .timeline-item:nth-child(4) { animation-delay: 0.4s; }
        .timeline-item:nth-child(5) { animation-delay: 0.5s; }
        
        .timeline-icon {
            position: absolute;
            left: -30px;
            top: 0;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            z-index: 2;
            transition: all 0.3s;
        }
        
        .timeline-item.completed .timeline-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        
        .timeline-item.current .timeline-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
            animation: pulse 2s infinite;
        }
        
        .timeline-item.pending .timeline-icon {
            background: #f3f4f6;
            color: #9ca3af;
            border: 3px solid #e5e7eb;
        }
        
        .timeline-item.delayed .timeline-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }
        
        .timeline-item.cancelled .timeline-icon {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
        }
        
        .timeline-content {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .timeline-item.completed .timeline-content {
            border-left-color: #10b981;
        }
        
        .timeline-item.current .timeline-content {
            border-left-color: #3b82f6;
            background: #eff6ff;
        }
        
        .timeline-item.delayed .timeline-content {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        
        .timeline-item.cancelled .timeline-content {
            border-left-color: #6b7280;
            background: #f3f4f6;
        }
        
        .timeline-status {
            font-size: 1.2rem;
            font-weight: 700;
            color: #232351;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-badge.current {
            background: #3b82f6;
            color: white;
        }
        
        .status-badge.delayed {
            background: #ef4444;
            color: white;
            animation: blink 1.5s infinite;
        }
        
        .timeline-time {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .timeline-details {
            font-size: 0.95rem;
            color: #4b5563;
            line-height: 1.6;
            margin-top: 10px;
        }
        
        .timeline-location {
            margin-top: 10px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .timeline-location i {
            color: #5551c0;
            margin-right: 6px;
        }
        
        .details-grid {
            display: grid;
            gap: 20px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        
        .detail-value {
            font-weight: 600;
            color: #232351;
            text-align: right;
        }
        
        .pod-section {
            margin-top: 20px;
            padding: 20px;
            background: #f0fdf4;
            border-radius: 12px;
            border: 2px solid #10b981;
        }
        
        .pod-pending {
            background: #fef3c7;
            border-color: #f59e0b;
        }
        
        .pod-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-pod {
            flex: 1;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-view-pod {
            background: #3b82f6;
            color: white;
        }
        
        .btn-view-pod:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
        
        .btn-download-pod {
            background: #10b981;
            color: white;
        }
        
        .btn-download-pod:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        
        .delay-alert {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .delay-alert strong {
            color: #dc2626;
        }
        
        .full-history-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #5551c0 0%, #7b77e8 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .full-history-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(85, 81, 192, 0.3);
        }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        @media (max-width: 768px) {
            .tracking-content {
                grid-template-columns: 1fr;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="tracking-container">
        <div class="tracking-header">
            <h1>Track Your Shipment</h1>
            <p>Enter your tracking number to see real-time updates</p>
        </div>
        
        <div class="search-box">
            <form class="search-form" method="GET" action="">
                <input type="text" name="doc_no" class="search-input" placeholder="Enter Tracking ID (Doc No.)" value="<?= htmlspecialchars($doc_no) ?>" required>
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Track
                </button>
            </form>
        </div>
        
        <?php if (!$get_shipping_details_row && !empty($doc_no)): ?>
            <div class="alert alert-danger text-center">
                <h4><i class="fas fa-exclamation-triangle"></i> Tracking Not Found</h4>
                <p>No shipment found with tracking ID: <strong><?= htmlspecialchars($doc_no) ?></strong></p>
            </div>
        <?php elseif ($get_shipping_details_row): ?>
            <div class="tracking-content">
                <div class="timeline-card">
                    <h2 class="card-title">
                        <i class="fas fa-route"></i> Delivery Timeline
                    </h2>
                    
                    <div class="timeline">
                        <?php foreach ($timeline as $i => $step): 
                            $item_class = 'pending';
                            if ($step['completed']) $item_class = 'completed';
                            if ($step['is_current']) $item_class = 'current';
                            if (isset($step['color']) && $step['color'] == 'danger') $item_class = 'cancelled';
                            if (isset($step['color']) && $step['color'] == 'warning' && !$step['completed']) $item_class = 'delayed';
                        ?>
                            <div class="timeline-item <?= $item_class ?>">
                                <div class="timeline-icon">
                                    <i class="fas <?= $step['icon'] ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-status">
                                        <span><?= htmlspecialchars($step['status']) ?></span>
                                        <?php if ($step['is_current']): ?>
                                            <span class="status-badge current">Current</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($step['time']): ?>
                                        <div class="timeline-time">
                                            <i class="fas fa-clock"></i>
                                            <?= $step['time'] ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="timeline-details">
                                        <?= $step['details'] ?>
                                    </div>
                                    
                                    <?php if (!empty($step['location']) || !empty($step['office'])): ?>
                                        <div class="timeline-location">
                                            <?php if (!empty($step['location'])): ?>
                                                <div><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?= htmlspecialchars($step['location']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($step['office'])): ?>
                                                <div><i class="fas fa-building"></i> <strong>Office:</strong> <?= htmlspecialchars($step['office']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($step['office_phone'])): ?>
                                                <div><i class="fas fa-phone"></i> <strong>Contact:</strong> <?= htmlspecialchars($step['office_phone']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($step['pod_status'])): ?>
                                        <div class="pod-section <?= $step['pod_status'] == 'pending' ? 'pod-pending' : '' ?>">
                                            <div style="font-weight: 700; margin-bottom: 10px;">
                                                <i class="fas <?= $step['pod_status'] == 'available' ? 'fa-check-circle' : 'fa-clock' ?>"></i>
                                                Proof of Delivery: <?= $step['pod_status'] == 'available' ? 'Available' : 'Pending Upload' ?>
                                            </div>
                                            <?php if ($step['pod_status'] == 'available'): ?>
                                                <div class="pod-buttons">
                                                    <button class="btn-pod btn-view-pod" onclick="window.open('view_pod.php?file=<?= urlencode($step['pod_file']) ?>', '_blank')">
                                                        <i class="fas fa-eye"></i> View POD
                                                    </button>
                                                    <button class="btn-pod btn-download-pod" onclick="window.location.href='<?= htmlspecialchars($step['pod_file']) ?>'">
                                                        <i class="fas fa-download"></i> Download
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!empty($delayed_entries)): ?>
                        <div class="delay-alert">
                            <strong><i class="fas fa-exclamation-triangle"></i> Delay Information:</strong>
                            <?php foreach ($delayed_entries as $delay): ?>
                                <div style="margin-top: 10px;">
                                    <div><strong>Time:</strong> <?= $delay['time'] ?></div>
                                    <div><strong>Reason:</strong> <?= htmlspecialchars($delay['reason']) ?></div>
                                    <?php if ($delay['notes']): ?>
                                        <div><strong>Details:</strong> <?= htmlspecialchars($delay['notes']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <button class="full-history-btn" onclick="showFullHistory()">
                        <i class="fas fa-history"></i> View Full History
                    </button>
                </div>
                
                <div class="details-card">
                    <h2 class="card-title">
                        <i class="fas fa-info-circle"></i> Shipment Details
                    </h2>
                    
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Tracking ID</span>
                            <span class="detail-value"><?= htmlspecialchars($get_shipping_details_row['doc_no']) ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Pickup Date</span>
                            <span class="detail-value"><?= $pickup_date ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Client Name</span>
                            <span class="detail-value"><?= htmlspecialchars($client_name) ?></span>
                        </div>
                        
                        <?php if ($delivery_agent != '-'): ?>
                        <div class="detail-item">
                            <span class="detail-label">Delivery Agent</span>
                            <span class="detail-value"><?= htmlspecialchars($delivery_agent) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($contact_number != '-'): ?>
                        <div class="detail-item">
                            <span class="detail-label">Contact Number</span>
                            <span class="detail-value">
                                <a href="tel:<?= htmlspecialchars($contact_number) ?>" style="color: #5551c0; text-decoration: none;">
                                    <?= htmlspecialchars($contact_number) ?>
                                </a>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($car_no != '-'): ?>
                        <div class="detail-item">
                            <span class="detail-label">Vehicle No.</span>
                            <span class="detail-value"><?= htmlspecialchars($car_no) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($has_manifest): ?>
                        <div class="detail-item">
                            <span class="detail-label">Transfer Type</span>
                            <span class="detail-value">Branch Transfer</span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Destination</span>
                            <span class="detail-value"><?= htmlspecialchars($manifest_info['to_office_name'] ?? '-') ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function showFullHistory() {
            alert('Full history modal will be implemented here');
        }
    </script>
</body>
</html>
<?php } ?>
