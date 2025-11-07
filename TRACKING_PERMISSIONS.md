# 🔐 Tracking System - Permission Management Guide

## 📋 Available Permissions

The tracking system includes **4 permissions** in the "Tracking" module:

| Permission Key | Permission Name | Required For | Description |
|---------------|----------------|--------------|-------------|
| `tracking_management` | Tracking Management | `admin/tracking_management.php` | Main dashboard - view all shipments, filters, stats |
| `tracking_view` | View Tracking | Various pages | View tracking details and timelines |
| `tracking_update` | Update Tracking Status | `admin/api_tracking_update.php` | Update shipment status via AJAX |
| `tracking_history` | View Tracking History | `admin/tracking_history.php` | View complete tracking history |

---

## 🚀 Quick Setup

### Method 1: Automatic (Recommended)

When you import `admin/create_tracking_system.sql`, permissions are **automatically**:
- ✅ Added to `tbl_permissions` table
- ✅ Assigned to Super Admin role
- ✅ Ready to use

### Method 2: Manual Addition

If you need to add permissions separately:

```sql
-- Import this file in phpMyAdmin
admin/add_tracking_permissions.sql
```

---

## 👥 Assign Permissions to Roles

### Via Admin Panel (Easy Way)

1. Login to admin panel
2. Navigate to: `admin/roles.php`
3. Find the role you want to modify
4. Click **"Edit"** button
5. Scroll to **"Tracking"** module section
6. Check the permissions you want:
   - ☑️ Tracking Management
   - ☑️ View Tracking
   - ☑️ Update Tracking Status
   - ☑️ View Tracking History
7. Click **"Save"**
8. Done! ✅

### Via SQL (Advanced Way)

#### Assign All Tracking Permissions to a Role

```sql
-- Replace 'Manager' with your role name
INSERT INTO tbl_role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM tbl_roles r
CROSS JOIN tbl_permissions p
WHERE r.role_name = 'Manager'
AND p.permission_key IN (
    'tracking_management',
    'tracking_view',
    'tracking_update',
    'tracking_history'
)
AND NOT EXISTS (
    SELECT 1 FROM tbl_role_permissions rp
    WHERE rp.role_id = r.role_id 
    AND rp.permission_id = p.permission_id
);
```

#### Assign Specific Permissions Only

```sql
-- Example: Only view and update, no management dashboard
INSERT INTO tbl_role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM tbl_roles r
CROSS JOIN tbl_permissions p
WHERE r.role_name = 'Staff'
AND p.permission_key IN ('tracking_view', 'tracking_update')
AND NOT EXISTS (
    SELECT 1 FROM tbl_role_permissions rp
    WHERE rp.role_id = r.role_id 
    AND rp.permission_id = p.permission_id
);
```

---

## 📊 Permission Levels (Recommended)

### Super Admin / Admin
```
✅ tracking_management  (Full dashboard)
✅ tracking_view        (View details)
✅ tracking_update      (Update status)
✅ tracking_history     (View history)
```
**Access**: Everything - complete control

### Manager
```
✅ tracking_management  (Dashboard access)
✅ tracking_view        (View details)
✅ tracking_update      (Update status)
✅ tracking_history     (View history)
```
**Access**: Full tracking management

### Supervisor
```
✅ tracking_view        (View details)
✅ tracking_update      (Update status)
✅ tracking_history     (View history)
❌ tracking_management  (No dashboard)
```
**Access**: Can update and view, but no main dashboard

### Staff / Clerk
```
✅ tracking_view        (View details)
✅ tracking_update      (Update status)
❌ tracking_history     (No history)
❌ tracking_management  (No dashboard)
```
**Access**: Basic update only

### View Only / Customer Service
```
✅ tracking_view        (View details)
❌ tracking_update      (No updates)
❌ tracking_history     (No history)
❌ tracking_management  (No dashboard)
```
**Access**: Read-only

---

## ✅ Verify Permissions

### Check All Tracking Permissions

```sql
SELECT 
    permission_id,
    permission_key,
    permission_name,
    module_name,
    permission_description,
    created_at
FROM tbl_permissions 
WHERE module_name = 'Tracking'
ORDER BY permission_id;
```

### Check Which Roles Have Tracking Access

```sql
SELECT 
    r.role_name,
    p.permission_name,
    p.permission_key
FROM tbl_roles r
INNER JOIN tbl_role_permissions rp ON r.role_id = rp.role_id
INNER JOIN tbl_permissions p ON rp.permission_id = p.permission_id
WHERE p.module_name = 'Tracking'
ORDER BY r.role_name, p.permission_name;
```

### Check Specific User's Tracking Permissions

```sql
SELECT 
    u.username,
    u.full_name,
    r.role_name,
    p.permission_name
FROM tbl_users u
INNER JOIN tbl_roles r ON u.role_id = r.role_id
INNER JOIN tbl_role_permissions rp ON r.role_id = rp.role_id
INNER JOIN tbl_permissions p ON rp.permission_id = p.permission_id
WHERE u.username = 'john_doe'  -- Change username
AND p.module_name = 'Tracking'
ORDER BY p.permission_name;
```

---

## 🔧 Troubleshooting

### Problem: "Permission denied" error

**Solution 1**: Check user's role has tracking permissions
```sql
-- Check user's permissions
SELECT r.role_name, p.permission_key
FROM tbl_users u
INNER JOIN tbl_roles r ON u.role_id = r.role_id
INNER JOIN tbl_role_permissions rp ON r.role_id = rp.role_id
INNER JOIN tbl_permissions p ON rp.permission_id = p.permission_id
WHERE u.username = 'YOUR_USERNAME'
AND p.module_name = 'Tracking';
```

**Solution 2**: Add missing permissions to role
```sql
-- Via admin panel: admin/roles.php
-- Or use SQL above to add permissions
```

### Problem: Permissions not showing in role edit page

**Solution**: Re-import permissions
```sql
-- Run: admin/add_tracking_permissions.sql
```

### Problem: Super Admin doesn't have access

**Solution**: Re-assign to Super Admin
```sql
INSERT INTO tbl_role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM tbl_roles r
CROSS JOIN tbl_permissions p
WHERE r.role_name = 'Super Admin'
AND p.permission_key IN ('tracking_management', 'tracking_view', 'tracking_update', 'tracking_history')
AND NOT EXISTS (
    SELECT 1 FROM tbl_role_permissions rp
    WHERE rp.role_id = r.role_id AND rp.permission_id = p.permission_id
);
```

---

## 🎯 Common Scenarios

### Scenario 1: New Employee - Basic Tracking
**Need**: Can update status only

```sql
-- Assign basic permissions
INSERT INTO tbl_role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM tbl_roles r
CROSS JOIN tbl_permissions p
WHERE r.role_name = 'Employee'
AND p.permission_key IN ('tracking_view', 'tracking_update');
```

### Scenario 2: Team Lead - Full Access
**Need**: Complete tracking management

```sql
-- Assign all permissions
INSERT INTO tbl_role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM tbl_roles r
CROSS JOIN tbl_permissions p
WHERE r.role_name = 'Team Lead'
AND p.module_name = 'Tracking';
```

### Scenario 3: Customer Service - View Only
**Need**: Can view but not update

```sql
-- Assign view only
INSERT INTO tbl_role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM tbl_roles r
CROSS JOIN tbl_permissions p
WHERE r.role_name = 'Customer Service'
AND p.permission_key = 'tracking_view';
```

---

## 📱 Admin Panel Access

After assigning permissions, users can access:

| Permission | Can Access |
|-----------|-----------|
| `tracking_management` | `admin/tracking_management.php` - Main dashboard |
| `tracking_view` | All tracking views and public pages |
| `tracking_update` | Update status via AJAX API |
| `tracking_history` | `admin/tracking_history.php` - Complete history |

---

## 🔒 Security Notes

1. **Super Admin**: Always has all permissions (hardcoded)
2. **Role-Based**: Permissions are assigned to roles, not individual users
3. **Hierarchical**: Users inherit all permissions from their assigned role
4. **Revocable**: Remove permissions by editing role in admin panel
5. **Auditable**: All permission checks are logged in session

---

## 💡 Best Practices

1. ✅ **Use Roles**: Assign permissions to roles, not individual users
2. ✅ **Principle of Least Privilege**: Give minimum required permissions
3. ✅ **Regular Review**: Audit permissions quarterly
4. ✅ **Document Changes**: Note why permissions were granted
5. ✅ **Test Access**: Verify permissions work after assignment

---

## 📚 Related Documentation

- **Main Guide**: `TRACKING_SYSTEM_GUIDE.md`
- **Quick Start**: `TRACKING_QUICKSTART.md`
- **SQL Files**: 
  - `admin/create_tracking_system.sql` (Main install)
  - `admin/add_tracking_permissions.sql` (Permissions only)

---

## ✨ Quick Commands

```bash
# View all tracking permissions
SELECT * FROM tbl_permissions WHERE module_name = 'Tracking';

# Count users with tracking access
SELECT r.role_name, COUNT(u.user_id) as user_count
FROM tbl_roles r
INNER JOIN tbl_users u ON r.role_id = u.role_id
INNER JOIN tbl_role_permissions rp ON r.role_id = rp.role_id
INNER JOIN tbl_permissions p ON rp.permission_id = p.permission_id
WHERE p.module_name = 'Tracking'
GROUP BY r.role_name;

# List all users with tracking_management permission
SELECT u.username, u.full_name, u.email, r.role_name
FROM tbl_users u
INNER JOIN tbl_roles r ON u.role_id = r.role_id
INNER JOIN tbl_role_permissions rp ON r.role_id = rp.role_id
INNER JOIN tbl_permissions p ON rp.permission_id = p.permission_id
WHERE p.permission_key = 'tracking_management'
AND u.is_active = 1;
```

---

**🎉 You're all set! Permissions are now configured for the tracking system.**

Need help? Check the main documentation or test permissions with a test user account.
