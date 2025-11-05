<?php
require 'conn.php';

/**
 * Add Granular Permissions to User Management System
 * This adds more specific permissions for fine-grained access control
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Add Granular Permissions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #667eea; padding-bottom: 15px; }
        h2 { color: #667eea; margin-top: 30px; }
        h3 { color: #34495e; margin-top: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .permission-list { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .permission-item { padding: 8px; border-bottom: 1px solid #dee2e6; }
        .permission-item:last-child { border-bottom: none; }
        .module-badge { background: #667eea; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px; margin-right: 10px; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔐 Add Granular Permissions</h1>
<p>This will add more specific permissions for fine-grained access control over dashboard cards, status updates, and specific features.</p>";

$all_success = true;

// Define granular permissions
$granular_permissions = [
    // Dashboard Module - Granular
    ['dashboard_cards_view', 'View Dashboard Cards', 'Dashboard', 'View statistics cards on dashboard'],
    ['dashboard_total_trips_card', 'View Total Trips Card', 'Dashboard', 'View total trips statistics card'],
    ['dashboard_active_trips_card', 'View Active Trips Card', 'Dashboard', 'View active trips statistics card'],
    ['dashboard_completed_trips_card', 'View Completed Trips Card', 'Dashboard', 'View completed trips statistics card'],
    ['dashboard_revenue_card', 'View Revenue Card', 'Dashboard', 'View revenue statistics card'],
    ['dashboard_charts_view', 'View Dashboard Charts', 'Dashboard', 'View charts and graphs on dashboard'],
    ['dashboard_recent_activities', 'View Recent Activities', 'Dashboard', 'View recent activities section'],
    ['dashboard_export_data', 'Export Dashboard Data', 'Dashboard', 'Export dashboard statistics to Excel/PDF'],
    
    // Dockets Module - Granular
    ['docket_view_all', 'View All Dockets', 'Dockets', 'View all dockets in the system'],
    ['docket_view_own', 'View Own Dockets Only', 'Dockets', 'View only dockets created by self'],
    ['docket_status_update', 'Update Docket Status', 'Dockets', 'Change status of dockets (pending, in-progress, completed)'],
    ['docket_assign_driver', 'Assign Driver to Docket', 'Dockets', 'Assign or change driver for dockets'],
    ['docket_assign_vehicle', 'Assign Vehicle to Docket', 'Dockets', 'Assign or change vehicle for dockets'],
    ['docket_print', 'Print Dockets', 'Dockets', 'Print docket documents'],
    ['docket_export', 'Export Dockets', 'Dockets', 'Export docket list to Excel/PDF'],
    ['docket_search', 'Search Dockets', 'Dockets', 'Use search and filter features'],
    ['docket_view_history', 'View Docket History', 'Dockets', 'View change history and audit log'],
    ['docket_bulk_actions', 'Bulk Docket Actions', 'Dockets', 'Perform bulk operations on multiple dockets'],
    
    // Manifest Module - Granular
    ['manifest_create_from_dockets', 'Create Manifest from Dockets', 'Manifest', 'Create new manifest by selecting dockets'],
    ['manifest_status_change', 'Change Manifest Status', 'Manifest', 'Update manifest status (draft, finalized, dispatched)'],
    ['manifest_finalize', 'Finalize Manifest', 'Manifest', 'Finalize manifest (cannot be edited after)'],
    ['manifest_reopen', 'Reopen Finalized Manifest', 'Manifest', 'Reopen a finalized manifest for editing'],
    ['manifest_print_summary', 'Print Manifest Summary', 'Manifest', 'Print manifest summary report'],
    ['manifest_print_labels', 'Print Shipping Labels', 'Manifest', 'Print shipping labels from manifest'],
    ['manifest_export', 'Export Manifest', 'Manifest', 'Export manifest to Excel/PDF'],
    ['manifest_send_email', 'Email Manifest', 'Manifest', 'Send manifest via email'],
    
    // Staff Module - Granular
    ['staff_view_salary', 'View Staff Salary', 'Staff', 'View salary information of staff'],
    ['staff_edit_salary', 'Edit Staff Salary', 'Staff', 'Edit salary information'],
    ['staff_view_attendance', 'View Staff Attendance', 'Staff', 'View attendance records'],
    ['staff_mark_attendance', 'Mark Staff Attendance', 'Staff', 'Mark attendance for staff'],
    ['staff_view_performance', 'View Staff Performance', 'Staff', 'View performance metrics'],
    ['staff_export', 'Export Staff Data', 'Staff', 'Export staff list to Excel/PDF'],
    ['staff_assign_to_trips', 'Assign Staff to Trips', 'Staff', 'Assign drivers/helpers to trips'],
    
    // Clients Module - Granular
    ['client_view_pricing', 'View Client Pricing', 'Clients', 'View pricing information for clients'],
    ['client_edit_pricing', 'Edit Client Pricing', 'Clients', 'Edit pricing and rates for clients'],
    ['client_view_history', 'View Client History', 'Clients', 'View shipment history and transactions'],
    ['client_credit_limit', 'Manage Credit Limit', 'Clients', 'Set and manage client credit limits'],
    ['client_export', 'Export Client Data', 'Clients', 'Export client list to Excel/PDF'],
    ['client_send_notifications', 'Send Client Notifications', 'Clients', 'Send SMS/Email notifications to clients'],
    
    // Vehicles Module - Granular
    ['vehicle_view_maintenance', 'View Vehicle Maintenance', 'Vehicles', 'View maintenance records'],
    ['vehicle_add_maintenance', 'Add Maintenance Record', 'Vehicles', 'Add new maintenance entry'],
    ['vehicle_view_costs', 'View Vehicle Costs', 'Vehicles', 'View fuel and operational costs'],
    ['vehicle_add_costs', 'Add Vehicle Costs', 'Vehicles', 'Add fuel and cost entries'],
    ['vehicle_assign_driver', 'Assign Vehicle to Driver', 'Vehicles', 'Assign vehicles to drivers'],
    ['vehicle_export', 'Export Vehicle Data', 'Vehicles', 'Export vehicle list to Excel/PDF'],
    
    // Reports Module - Granular
    ['report_view_financial', 'View Financial Reports', 'Reports', 'View revenue and expense reports'],
    ['report_view_operational', 'View Operational Reports', 'Reports', 'View trip and delivery reports'],
    ['report_view_staff', 'View Staff Reports', 'Reports', 'View staff performance reports'],
    ['report_view_client', 'View Client Reports', 'Reports', 'View client-wise reports'],
    ['report_view_vehicle', 'View Vehicle Reports', 'Reports', 'View vehicle utilization reports'],
    ['report_custom_date_range', 'Custom Date Range Reports', 'Reports', 'Generate reports for custom date ranges'],
    ['report_schedule', 'Schedule Reports', 'Reports', 'Schedule automatic report generation'],
    ['report_email', 'Email Reports', 'Reports', 'Email reports to recipients'],
    
    // Settings Module - Granular
    ['settings_company_info', 'Edit Company Information', 'Settings', 'Edit company details and branding'],
    ['settings_email_config', 'Configure Email Settings', 'Settings', 'Configure SMTP and email settings'],
    ['settings_sms_config', 'Configure SMS Settings', 'Settings', 'Configure SMS gateway settings'],
    ['settings_notification', 'Manage Notifications', 'Settings', 'Configure notification preferences'],
    ['settings_backup', 'Database Backup', 'Settings', 'Create and manage database backups'],
    ['settings_restore', 'Database Restore', 'Settings', 'Restore database from backup'],
    ['settings_system_logs', 'View System Logs', 'Settings', 'View system activity logs'],
    ['settings_api_keys', 'Manage API Keys', 'Settings', 'Manage API keys and integrations'],
    
    // User Management Module - Granular
    ['user_view_activity', 'View User Activity', 'User Management', 'View user login and activity logs'],
    ['user_reset_password', 'Reset User Password', 'User Management', 'Reset password for any user'],
    ['user_enable_disable', 'Enable/Disable Users', 'User Management', 'Activate or deactivate user accounts'],
    ['user_bulk_actions', 'Bulk User Actions', 'User Management', 'Perform bulk operations on users'],
    ['role_clone', 'Clone Roles', 'User Management', 'Create copy of existing roles'],
    ['permission_audit', 'View Permission Audit', 'User Management', 'View permission change history'],
];

echo "<h2>Adding Granular Permissions...</h2>";
echo "<div class='info'>📝 Total Permissions to Add: " . count($granular_permissions) . "</div>";

$added_count = 0;
$skipped_count = 0;
$error_count = 0;

echo "<div class='permission-list'>";

foreach ($granular_permissions as $perm) {
    $key = mysqli_real_escape_string($conn, $perm[0]);
    $name = mysqli_real_escape_string($conn, $perm[1]);
    $module = mysqli_real_escape_string($conn, $perm[2]);
    $desc = mysqli_real_escape_string($conn, $perm[3]);
    
    // Check if permission already exists
    $check = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key = '$key'");
    
    if (mysqli_num_rows($check) > 0) {
        echo "<div class='permission-item'>
                <span class='module-badge'>$module</span>
                <strong>$name</strong> - <em>Already exists</em>
              </div>";
        $skipped_count++;
    } else {
        $insert = "INSERT INTO tbl_permissions (permission_key, permission_name, module_name, permission_description) 
                   VALUES ('$key', '$name', '$module', '$desc')";
        
        if (mysqli_query($conn, $insert)) {
            echo "<div class='permission-item'>
                    <span class='module-badge'>$module</span>
                    <strong>$name</strong> - ✓ Added
                  </div>";
            $added_count++;
        } else {
            echo "<div class='permission-item'>
                    <span class='module-badge'>$module</span>
                    <strong>$name</strong> - ✗ Error: " . mysqli_error($conn) . "
                  </div>";
            $error_count++;
            $all_success = false;
        }
    }
}

echo "</div>";

echo "<h2>Summary</h2>";
echo "<div class='success'>✓ Successfully added: $added_count permissions</div>";
if ($skipped_count > 0) {
    echo "<div class='info'>ℹ Already existed: $skipped_count permissions</div>";
}
if ($error_count > 0) {
    echo "<div class='error'>✗ Errors: $error_count permissions</div>";
}

if ($all_success || $error_count == 0) {
    echo "<div class='success'>
            <h3>✅ Granular Permissions Setup Complete!</h3>
            <p><strong>Total Permissions in System:</strong> " . ($added_count + $skipped_count) . "</p>
            <p>You can now assign these granular permissions to roles from the Roles & Permissions page.</p>
            <p><a href='roles.php' style='color: #667eea; font-weight: bold;'>→ Go to Roles & Permissions</a></p>
          </div>";
} else {
    echo "<div class='error'>
            <h3>⚠️ Some errors occurred</h3>
            <p>Please check the errors above and try again.</p>
          </div>";
}

echo "</div>
</body>
</html>";
?>
