# 🚀 Professional Delivery Tracking System - Installation Guide

## Overview
This is a complete, professional delivery tracking system with real-time status updates, timeline visualization, and comprehensive tracking history. The system is designed to be user-friendly, modern, and fully integrated with your existing NSFS logistics platform.

## ✨ Features

### Backend (Admin Panel)
- **Tracking Management Dashboard** - Centralized view of all shipments
- **Real-time Status Updates** - Update shipment status with notes and location
- **Complete History Tracking** - Every status change is logged with timestamp
- **AJAX-powered Updates** - Smooth, no-refresh status updates
- **Advanced Filtering** - Search by doc number, status, date range
- **Statistics Dashboard** - Visual stats cards for quick overview
- **Permission-based Access** - Secure, role-based access control

### Frontend (Customer View)
- **Public Tracking Page** - Clean, modern tracking interface
- **Timeline Visualization** - Visual journey of shipment progress
- **Real-time Status** - Shows current status and location
- **Complete History Modal** - View all status updates
- **Mobile Responsive** - Works perfectly on all devices
- **SEO Friendly** - Optimized for search engines

### Additional Features
- **Reusable Widget** - Embed tracking anywhere on your site
- **Email Notifications** - (Ready for integration)
- **SMS Notifications** - (Ready for integration)
- **Multiple Status Types** - 13 pre-configured status types
- **Branch Transfer Support** - Special timeline for branch shipments
- **POD (Proof of Delivery)** - Display delivery proof when available

---

## 📦 Installation Steps

### Step 1: Import Database Schema

1. Open phpMyAdmin or your MySQL client
2. Select your database
3. Import the SQL file:
   ```
   admin/create_tracking_system.sql
   ```
4. This will create:
   - `tbl_tracking_history` - Main tracking history table
   - `tbl_tracking_status_config` - Status configuration table
   - `tbl_tracking_notifications` - Notification log table
   - `vw_tracking_details` - View for easy data access
   - Stored procedures for tracking management
   - Additional columns in existing tables

### Step 2: Verify Table Creation

Run this query to verify all tables are created:

```sql
SHOW TABLES LIKE 'tbl_tracking%';
```

You should see:
- tbl_tracking_history
- tbl_tracking_status_config
- tbl_tracking_notifications

### Step 3: Check Permissions

The tracking system includes 4 permissions in the "Tracking" module:

1. **tracking_management** - Access tracking management dashboard
2. **tracking_view** - View tracking details
3. **tracking_update** - Update shipment status
4. **tracking_history** - View complete tracking history

#### Auto-Assignment to Super Admin

When you import `create_tracking_system.sql`, these permissions are automatically assigned to the Super Admin role.

#### Manual Permission Assignment

**Option 1: Via Admin Panel (Recommended)**
1. Login to admin panel
2. Go to `admin/roles.php`
3. Click "Edit" on the role you want to grant access
4. Scroll to "Tracking" module
5. Check the permissions you want to grant
6. Click "Save"

**Option 2: Via SQL (If needed separately)**
Import the file:
```
admin/add_tracking_permissions.sql
```

This will:
- Add all 4 tracking permissions
- Auto-assign to Super Admin role
- Provide verification queries

#### Assign to Specific Role (SQL)

To assign tracking permissions to a specific role:

```sql
-- Replace 'Manager' with your role name
INSERT INTO tbl_role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM tbl_roles r
CROSS JOIN tbl_permissions p
WHERE r.role_name = 'Manager'
AND p.permission_key IN ('tracking_management', 'tracking_view', 'tracking_update', 'tracking_history')
AND NOT EXISTS (
    SELECT 1 FROM tbl_role_permissions rp
    WHERE rp.role_id = r.role_id AND rp.permission_id = p.permission_id
);
```

#### Verify Permissions

Run this query to check permissions:

```sql
-- View all tracking permissions
SELECT * FROM tbl_permissions WHERE module_name = 'Tracking';

-- View which roles have tracking permissions
SELECT 
    r.role_name,
    p.permission_name
FROM tbl_roles r
INNER JOIN tbl_role_permissions rp ON r.role_id = rp.role_id
INNER JOIN tbl_permissions p ON rp.permission_id = p.permission_id
WHERE p.module_name = 'Tracking';
```

### Step 4: Update Navigation Menu

Add tracking management to your admin menu (`admin/left_panel.php`):

```php
<li>
    <a href="tracking_management.php">
        <i class="fas fa-location-arrow"></i>
        Tracking Management
    </a>
</li>
```

### Step 5: Update Main Website Menu

Add tracking link to your public website header:

```php
<li>
    <a href="track_shipment.php">
        <i class="fa fa-search-location"></i>
        Track Shipment
    </a>
</li>
```

---

## 🔧 Configuration

### 1. Update Connection File

Ensure `admin/conn.php` is properly configured with your database credentials.

### 2. Session Variables

The system uses these session variables:
- `$_SESSION['user_id']` or `$_SESSION['admin_id']` - User ID
- `$_SESSION['user_name']` or `$_SESSION['admin_name']` - User name
- `$_SESSION['permissions']` - Array of user permissions

Make sure your login system sets these correctly.

### 3. Permission Check Function

If you don't have the `requirePermission()` function, add it to `admin/check_auth.php`:

```php
function requirePermission($permission) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        return true;
    }
    
    if (!isset($_SESSION['permissions']) || !in_array($permission, $_SESSION['permissions'])) {
        header('Location: index.php?error=no_permission');
        exit;
    }
}
```

---

## 📁 File Structure

```
nsfs/
├── admin/
│   ├── tracking_management.php          # Main tracking dashboard
│   ├── tracking_history.php             # View complete history
│   ├── api_tracking_update.php          # AJAX API for updates
│   ├── delivery_status.php              # Updated with tracking
│   └── create_tracking_system.sql       # Database schema
│
├── includes/
│   └── tracking_widget.php              # Reusable widget component
│
├── track_shipment.php                   # Public tracking page
└── deliveryHistory.php                  # Alternative tracking page
```

---

## 🎯 Usage Guide

### For Administrators

#### 1. Access Tracking Management
- Navigate to: `admin/tracking_management.php`
- View all shipments with current status
- Use filters to find specific shipments

#### 2. Update Shipment Status
1. Click "Update Status" on any shipment
2. Select new status from dropdown
3. (Optional) Add current location
4. (Optional) Add notes/remarks
5. Click "Update Status"

#### 3. View Tracking History
- Click "View History" on any shipment
- See complete timeline of all status changes
- Each update shows: status, time, location, notes, and who updated it

#### 4. From Delivery Status Page
- Navigate to: `admin/delivery_status.php`
- Click "Update Status" on any docket
- Status updates will automatically be logged in tracking history

### For Customers

#### 1. Track Shipment
- Visit: `track_shipment.php`
- Enter document number
- Click "Track Now"

#### 2. View Shipment Journey
- See visual timeline of shipment progress
- Active status is highlighted
- View location and time for each status

#### 3. View Complete History
- Click "View Full History" button
- Modal shows all status updates
- Includes location, notes, and update times

---

## 🎨 Customization

### Change Status Types

Edit statuses in database:

```sql
UPDATE tbl_tracking_status_config 
SET status_label = 'Your Custom Label',
    status_color = '#FF5733'
WHERE status_name = 'In Transit';
```

Add new status:

```sql
INSERT INTO tbl_tracking_status_config 
(status_name, status_label, status_icon, status_color, display_order, is_terminal) 
VALUES 
('Custom Status', 'Custom Label', 'icon-name', '#FF5733', 15, 0);
```

### Modify Colors and Styling

All styles are inline in the PHP files. Search for `<style>` tags to customize:

- **Primary Color**: #667eea (purple gradient)
- **Success Color**: #28a745 (green)
- **Warning Color**: #ffc107 (yellow)
- **Danger Color**: #dc3545 (red)

### Add Email Notifications

In `admin/api_tracking_update.php`, uncomment and implement:

```php
sendTrackingNotification($doc_no, $status, $notes);
```

Implement the function to send emails using your mail system.

---

## 🔗 Integration Points

### 1. Existing Docket System
The tracking system integrates with:
- `docket_details` table
- `tbl_shipping_details` table
- Both tables are updated when status changes

### 2. Trip Status System
For backward compatibility:
- Updates to `tbl_shipping_details` also create entries in `tbl_trip_status`
- Maintains compatibility with existing trip management

### 3. Branch Transfer System
- Automatically detects branch transfers
- Shows different timeline for branch shipments
- Includes: Manifest Created, In Transit to Branch, Arrived at Branch

---

## 📊 Database Schema Details

### tbl_tracking_history
Stores every status update:
- `tracking_id` - Primary key
- `doc_no` - Document number
- `docket_id` - Link to docket_details
- `shipping_details_id` - Link to tbl_shipping_details
- `status` - Status name
- `notes` - Additional notes
- `location` - Current location
- `updated_by` - User ID who updated
- `updated_by_name` - User name
- `created_at` - Timestamp

### tbl_tracking_status_config
Configurable status types:
- `status_name` - Internal status name
- `status_label` - Display label
- `status_icon` - FontAwesome icon name
- `status_color` - Hex color code
- `display_order` - Sort order
- `is_terminal` - Is final status (Delivered/Cancelled)

---

## 🐛 Troubleshooting

### Issue: Tracking history not showing

**Solution:**
1. Check if `tbl_tracking_history` table exists
2. Verify status updates are being saved
3. Check SQL error logs

### Issue: Permission denied error

**Solution:**
1. Check if user has `tracking_management` permission
2. Verify session variables are set correctly
3. Check `check_auth.php` for permission function

### Issue: Status not updating

**Solution:**
1. Check database connection
2. Verify transaction is committing
3. Check browser console for JavaScript errors
4. Review `api_tracking_update.php` error logs

### Issue: Widget not displaying

**Solution:**
1. Include widget file: `include 'includes/tracking_widget.php';`
2. Verify database connection is available
3. Check if document number exists

---

## 🔐 Security Considerations

1. **SQL Injection Protection**: All inputs use `mysqli_real_escape_string()`
2. **XSS Protection**: All outputs use `htmlspecialchars()`
3. **Permission Checks**: Every page checks user permissions
4. **Transaction Safety**: Uses database transactions for data integrity
5. **Session Management**: Proper session validation on each request

---

## 📱 Mobile Responsiveness

The tracking system is fully responsive:
- Adapts to all screen sizes
- Touch-friendly interface
- Optimized for mobile data usage
- Progressive enhancement approach

---

## 🚀 Performance Tips

1. **Indexing**: All key fields are indexed
2. **Query Optimization**: Uses UNION for efficient data retrieval
3. **Limit Results**: Lists are limited to prevent memory issues
4. **AJAX Loading**: Status updates don't require page reload
5. **Caching**: Consider adding caching for public tracking page

---

## 📞 Support & Maintenance

### Regular Maintenance
- Monitor `tbl_tracking_history` table size
- Archive old tracking data periodically
- Review and optimize queries
- Update status configurations as needed

### Backup
Regularly backup these tables:
- `tbl_tracking_history`
- `tbl_tracking_status_config`
- `tbl_tracking_notifications`

---

## 🎓 Best Practices

1. **Always add location** when updating status
2. **Add meaningful notes** for customer clarity
3. **Update regularly** to keep customers informed
4. **Use appropriate status** for each stage
5. **Test on mobile** after any customization

---

## 🔄 Future Enhancements

Ready for implementation:
- ✅ Email notifications
- ✅ SMS notifications  
- ✅ WhatsApp notifications
- ✅ GPS tracking integration
- ✅ Auto-status updates from drivers
- ✅ Customer feedback system
- ✅ Estimated delivery time prediction
- ✅ Push notifications

---

## 📝 Changelog

### Version 1.0 (Current)
- Complete tracking system
- Admin management dashboard
- Public tracking page
- Reusable widget component
- Integration with existing system
- Comprehensive documentation

---

## ✅ Testing Checklist

Before going live, test:

- [ ] Database tables created successfully
- [ ] Admin can access tracking management
- [ ] Status updates work correctly
- [ ] Tracking history saves properly
- [ ] Public tracking page displays correctly
- [ ] Mobile responsiveness verified
- [ ] Permissions working as expected
- [ ] Integration with existing docket system
- [ ] Widget displays correctly
- [ ] All filters working properly
- [ ] AJAX updates working smoothly
- [ ] No PHP/SQL errors in logs

---

## 📧 Contact

For issues or questions:
- Check error logs in `admin/error_log`
- Review PHP error logs on server
- Test in browser console for JavaScript errors

---

## 🎉 You're All Set!

Your professional tracking system is now ready to use! 

**Admin Access:** `admin/tracking_management.php`  
**Public Tracking:** `track_shipment.php`

Enjoy managing your shipments with complete visibility! 🚚📦✨
