<?php
// Enhanced Delivery History with Detailed Notes
// This file provides comprehensive tracking with office details, manifest info, and rich history

// Detect AJAX first (before any includes)
$is_ajax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ||
    (isset($_POST['ajax']) && $_POST['ajax'] == '1')
);

// Include header which loads apps_top.php (database, sessions, functions)
if (!$is_ajax) {
    include("include/header.php");
} else {
    // For AJAX requests, include apps_top directly
    include("include/apps_top.php");
}

$doc_no = isset($_GET['doc_no']) ? mysqli_real_escape_string($conn, trim($_GET['doc_no'])) : '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['doc_no'])) {
    $doc_no = mysqli_real_escape_string($conn, trim($_POST['doc_no']));
}

$get_shipping_details_row = false;
$tracking_history = [];
$has_manifest = false;
$manifest_info = [];

if ($doc_no) {
    // Query from docket_details table with creator's office info
    $get_shipping_details_sql = "SELECT dd.*, 
                                         u.full_name as creator_full_name,
                                         u.username as creator_username,
                                         o.office_name as creator_office_name
                                  FROM docket_details dd
                                  LEFT JOIN tbl_users u ON dd.created_by = u.user_id
                                  LEFT JOIN tbl_offices o ON u.office_id = o.office_id
                                  WHERE dd.doc_no='" . $doc_no . "'";
    $get_shipping_details_rs = mysqli_query($conn, $get_shipping_details_sql);
    
    if ($get_shipping_details_rs && mysqli_num_rows($get_shipping_details_rs) > 0) {
        $get_shipping_details_row = mysqli_fetch_assoc($get_shipping_details_rs);
    } else {
        $get_shipping_details_row = false;
    }
}

if ($get_shipping_details_row) {
    $docket_id = $get_shipping_details_row['docket_id'];
    
    // Check manifest info with office names - corrected based on actual table structure
    $manifest_check_sql = "SELECT m.*, 
                                   md.doc_no,
                                   o.office_name as manifest_office_name,
                                   c.car_number,
                                   d.driver_name
                            FROM tbl_manifest_details md
                            LEFT JOIN tbl_manifest m ON md.manifest_id = m.manifest_id
                            LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                            LEFT JOIN tbl_car c ON m.car_id = c.car_id
                            LEFT JOIN tbl_driver d ON m.driver_id = d.driver_id
                            WHERE md.doc_no = '$doc_no'
                            LIMIT 1";
    $manifest_check_rs = @mysqli_query($conn, $manifest_check_sql);
    
    if ($manifest_check_rs && mysqli_num_rows($manifest_check_rs) > 0) {
        $has_manifest = true;
        $manifest_info = mysqli_fetch_assoc($manifest_check_rs);
    }
    
    // Fetch tracking history from docket_status_history
    $history_sql = "SELECT * FROM docket_status_history 
                    WHERE docket_id = '$docket_id'
                    ORDER BY changed_at ASC";
    $history_rs = @mysqli_query($conn, $history_sql);
    
    if ($history_rs && mysqli_num_rows($history_rs) > 0) {
        while ($row = mysqli_fetch_assoc($history_rs)) {
            $tracking_history[] = $row;
        }
    }
    
    // Build enhanced timeline
    $timeline = [];
    $current_status = $get_shipping_details_row['status'] ?? '';
    
    // 1. PICKUP STATUS - Blue color for pickup/creation
    $pickup_time = $get_shipping_details_row['pickup_datetime'] ?? $get_shipping_details_row['created_at'] ?? '';
    
    // Get creator info from joined tables
    $creator_name = $get_shipping_details_row['creator_full_name'] ?: $get_shipping_details_row['creator_username'] ?: '';
    $creator_office = $get_shipping_details_row['creator_office_name'] ?? '';
    
    // Fallback to branch_office if creator office not available
    $pickup_office = $creator_office ?: ($get_shipping_details_row['branch_office'] ?? $get_shipping_details_row['current_location'] ?? '');
    
    // Build creation message
    if (!empty($creator_name) && !empty($creator_office)) {
        // Both creator name and office are available
        $creation_details = "Docket created by <strong>{$creator_name}</strong> from <strong>{$creator_office}</strong> office";
    } elseif (!empty($creator_name)) {
        // Only creator name available
        $creation_details = "Docket created by <strong>{$creator_name}</strong>";
    } elseif (!empty($creator_office)) {
        // Only office available
        $creation_details = "Docket created by <strong>{$creator_office}</strong> office";
    } else {
        // Fallback
        $creation_details = "Docket created" . ($pickup_office ? " at <strong>{$pickup_office}</strong>" : "");
    }
    
    $timeline[] = [
        'status' => 'Picked Up',
        'icon' => 'fa-box-open',
        'time' => $pickup_time ? date('d M Y, h:i A', strtotime($pickup_time)) : '',
        'location' => $pickup_office,
        'office' => $pickup_office,
        'office_phone' => '',
        'details' => $creation_details,
        'completed' => true,
        'is_current' => false,
        'color' => 'info'  // Blue color for pickup
    ];
    
    // 2. MANIFEST / IN TRANSIT STATUS (if manifest exists)
    if ($has_manifest && $manifest_info) {
        $manifest_created = $manifest_info['created_at'] ?? '';
        $manifest_no = $manifest_info['manifest_no'] ?? $manifest_info['manifest_id'] ?? '';
        $to_office = $manifest_info['manifest_office_name'] ?? '';
        $manifest_car = $manifest_info['car_number'] ?? '';
        $manifest_driver = $manifest_info['driver_name'] ?? '';
        
        $transit_details = "On the way to <strong>{$to_office}</strong> office via Manifest #{$manifest_no}";
        if ($manifest_car || $manifest_driver) {
            $transit_details .= "<br>";
            if ($manifest_car) $transit_details .= "Vehicle: <strong>{$manifest_car}</strong>";
            if ($manifest_driver) $transit_details .= ($manifest_car ? ", " : "") . "Driver: <strong>{$manifest_driver}</strong>";
        }
        
        $timeline[] = [
            'status' => 'In Transit',
            'icon' => 'fa-truck-loading',
            'time' => $manifest_created ? date('d M Y, h:i A', strtotime($manifest_created)) : '',
            'location' => $pickup_office,
            'office' => $to_office,
            'office_phone' => '',
            'details' => $transit_details,
            'completed' => true,
            'is_current' => ($current_status == 'In Transit'),
            'color' => 'info'
        ];
        
        // Check if branch received
        foreach ($tracking_history as $h) {
            if (isset($h['new_status']) && ($h['new_status'] == 'Arrived at Branch' || $h['new_status'] == 'Received at Branch' || $h['new_status'] == 'Received at Destination')) {
                $received_office = $to_office;
                $received_notes = $h['notes'] ?? '';
                
                // Extract office name from notes if available
                if (preg_match('/received at ([^\s]+(?:\s+[^\s]+)?\s+office)/i', $received_notes, $matches)) {
                    $received_office = trim(str_replace(' office', '', $matches[1]));
                }
                
                $timeline[] = [
                    'status' => $h['new_status'] == 'Received at Destination' ? 'Received at Destination' : 'Received at Branch',
                    'icon' => 'fa-warehouse',
                    'time' => date('d M Y, h:i A', strtotime($h['changed_at'])),
                    'location' => $received_office,
                    'office' => $received_office,
                    'office_phone' => '',
                    'details' => $received_notes ?: "Parcel received at <strong>{$received_office}</strong> office and ready for local delivery",
                    'completed' => true,
                    'is_current' => ($current_status == 'Received at Destination' || $current_status == 'Received at Branch'),
                    'color' => 'success'
                ];
                break;
            }
        }
    } else {
        // Direct delivery - add In Transit
        foreach ($tracking_history as $h) {
            if (isset($h['new_status']) && $h['new_status'] == 'In Transit') {
                $timeline[] = [
                    'status' => 'In Transit',
                    'icon' => 'fa-truck',
                    'time' => date('d M Y, h:i A', strtotime($h['changed_at'])),
                    'location' => $h['location'] ?? '',
                    'office' => '',
                    'office_phone' => '',
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
    foreach ($tracking_history as $h) {
        if (isset($h['new_status']) && $h['new_status'] == 'Out for Delivery') {
            $car_no = $h['car_number'] ?? $get_shipping_details_row['car_number'] ?? '';
            $driver_name = $h['driver_name'] ?? $get_shipping_details_row['driver_name'] ?? '';
            $driver_phone = $h['driver_phone'] ?? $get_shipping_details_row['driver_phone'] ?? '';
            
            $details = "Out for delivery";
            if ($car_no) $details .= "<br>Vehicle: <strong>{$car_no}</strong>";
            if ($driver_name) $details .= "<br>Driver: <strong>{$driver_name}</strong>";
            if ($driver_phone) $details .= " ({$driver_phone})";
            
            $timeline[] = [
                'status' => 'Out for Delivery',
                'icon' => 'fa-shipping-fast',
                'time' => date('d M Y, h:i A', strtotime($h['changed_at'])),
                'location' => $h['location'] ?? '',
                'office' => '',
                'office_phone' => '',
                'car_no' => $car_no,
                'driver_name' => $driver_name,
                'driver_phone' => $driver_phone,
                'details' => $details,
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
            if (isset($h['new_status']) && ($h['new_status'] == 'Delivered' || $h['new_status'] == 'Pending POD')) {
                $status_label = $has_pod ? 'Delivered' : 'Delivered (POD Pending)';
                $timeline[] = [
                    'status' => $status_label,
                    'icon' => $has_pod ? 'fa-check-circle' : 'fa-clock',
                    'time' => date('d M Y, h:i A', strtotime($h['changed_at'])),
                    'location' => $h['location'] ?? '',
                    'office' => '',
                    'office_phone' => '',
                    'pod_status' => $has_pod ? 'available' : 'pending',
                    'pod_file' => $pod_file,
                    'details' => $has_pod ? 'Parcel successfully delivered with proof of delivery' : 'Parcel delivered, waiting for POD upload',
                    'completed' => true,
                    'is_current' => true,
                    'color' => 'success'  // Always green for delivered status
                ];
                break;
            }
        }
    }
    
    // 5. DELAYED STATUS (can occur at any stage)
    $delayed_entries = [];
    foreach ($tracking_history as $h) {
        if (isset($h['new_status']) && ($h['new_status'] == 'Delayed' || (isset($h['is_delayed']) && $h['is_delayed'] == 1))) {
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
            if (isset($h['new_status']) && $h['new_status'] == 'Cancelled') {
                $timeline[] = [
                    'status' => 'Cancelled',
                    'icon' => 'fa-times-circle',
                    'time' => date('d M Y, h:i A', strtotime($h['changed_at'])),
                    'location' => $h['location'] ?? '',
                    'office' => '',
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
    
    // Shipment details from docket_details table
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e8f0f7 0%, #f5f8fb 100%);
            color: #2c3e50;
            min-height: 100vh;
        }
        
        /* Fix for header text rendering */
        .header_sec,
        .header_sec * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .tracking-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        /* Ensure tracking container works with site header */
        .header_sec + .tracking-container {
            margin-top: 30px;
            padding-bottom: 50px;
        }
        
        /* Prevent tracking styles from affecting header */
        .tracking-container .tracking-header h1 {
            font-family: 'Inter', sans-serif;
        }
        
        .tracking-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.6s ease-out;
        }
        
        .tracking-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #5551c0;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
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
        
        @media (max-width: 991px) {
            .tracking-container {
                padding: 15px;
                margin: 20px auto;
            }
            
            .tracking-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .search-box {
                padding: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .tracking-container {
                padding: 10px;
                margin: 15px auto;
            }
            
            .tracking-content {
                gap: 15px;
            }
            
            .tracking-header h1 {
                font-size: 1.75rem;
            }
            
            .tracking-header p {
                font-size: 0.95rem;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-btn {
                width: 100%;
                padding: 14px;
            }
            
            .search-input {
                font-size: 14px;
                padding: 12px 15px;
            }
            
            .search-box {
                padding: 20px 15px;
            }
            
            .timeline-card, .details-card {
                padding: 20px 15px;
                border-radius: 15px;
            }
            
            .card-title {
                font-size: 1.25rem;
            }
            
            .timeline-status span {
                font-size: 15px;
            }
            
            .timeline-details {
                font-size: 13px;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .detail-item {
                padding: 12px;
            }
            
            .detail-label {
                font-size: 12px;
            }
            
            .detail-value {
                font-size: 14px;
            }
            
            .modal-dialog {
                margin: 10px;
            }
            
            .table {
                font-size: 11px;
            }
            
            .full-history-btn {
                font-size: 14px;
                padding: 12px 20px;
            }
        }
        
        @media (max-width: 480px) {
            .tracking-container {
                padding: 8px;
            }
            
            .tracking-header {
                margin-bottom: 25px;
            }
            
            .tracking-header h1 {
                font-size: 1.5rem;
            }
            
            .tracking-header p {
                font-size: 0.85rem;
            }
            
            .search-box {
                padding: 15px;
                border-radius: 12px;
            }
            
            .search-input {
                font-size: 13px;
            }
            
            .timeline-icon {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
            
            .timeline-card, .details-card {
                padding: 15px;
            }
            
            .card-title {
                font-size: 1.1rem;
            }
            
            .pod-buttons {
                flex-direction: column;
            }
            
            .btn-pod {
                width: 100%;
                margin: 5px 0;
            }
            
            .timeline-time {
                font-size: 12px;
            }
            
            .timeline-location {
                font-size: 12px;
            }
        }
        
    </style>
    
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
                        
                        <?php 
                        $destination = $get_shipping_details_row['client_address'] ?? '';
                        if (!empty($destination) && $destination != '-'): 
                        ?>
                        <div class="detail-item">
                            <span class="detail-label">Destination</span>
                            <span class="detail-value"><?= htmlspecialchars($destination) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Full History Modal -->
    <div class="modal fade" id="fullHistoryModal" tabindex="-1" role="dialog" aria-labelledby="fullHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #5551c0 0%, #7b68ee 100%); color: white;">
                    <h5 class="modal-title" id="fullHistoryModalLabel">
                        <i class="fas fa-history"></i> Complete Status History - <?= htmlspecialchars($doc_no) ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($tracking_history)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead style="background: #f8f9fa;">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Status Change</th>
                                        <th>Location</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tracking_history as $history): ?>
                                        <tr>
                                            <td style="white-space: nowrap;">
                                                <i class="fas fa-clock" style="color: #5551c0;"></i>
                                                <?= date('d M Y', strtotime($history['changed_at'])) ?><br>
                                                <small style="color: #666;"><?= date('h:i A', strtotime($history['changed_at'])) ?></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($history['old_status'])): ?>
                                                    <span style="background: #e3f2fd; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                                                        <?= htmlspecialchars($history['old_status']) ?>
                                                    </span>
                                                    <i class="fas fa-arrow-right" style="color: #999; margin: 0 5px;"></i>
                                                <?php endif; ?>
                                                <span style="background: #c8e6c9; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: 600;">
                                                    <?= htmlspecialchars($history['new_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($history['location'])): ?>
                                                    <i class="fas fa-map-marker-alt" style="color: #f44336;"></i>
                                                    <?= htmlspecialchars($history['location']) ?>
                                                <?php else: ?>
                                                    <span style="color: #999;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($history['notes'])): ?>
                                                    <?= nl2br(htmlspecialchars($history['notes'])) ?>
                                                <?php else: ?>
                                                    <span style="color: #999;">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No detailed status history available for this shipment.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showFullHistory() {
            $('#fullHistoryModal').modal('show');
        }
    </script>
<?php 
    include("include/footer.php");
} 
?>
