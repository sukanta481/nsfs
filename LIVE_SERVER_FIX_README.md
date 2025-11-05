# Live Server Error 500 Fix

## Problem
Pages like `database_sync.php`, `delivery_status.php`, `users.php`, `roles.php` etc. are showing HTTP ERROR 500 on the live server because they require permission tables that don't exist yet.

## Solution Applied
Updated `check_auth.php` to gracefully handle missing permission tables. Now it:
- ✅ Checks if permission tables exist before querying
- ✅ Grants access to logged-in users if tables don't exist
- ✅ Works normally once tables are created
- ✅ Maintains backward compatibility

## Files to Upload to Live Server

### Priority 1 - Must Upload (Fixes the error)
```
/admin/check_auth.php
```

### Priority 2 - Setup Script (Creates tables)
```
/admin/quick_setup.php
```

### Priority 3 - Other Updated Files
```
/admin/delivery_status.php
/admin/database/database_sync.php
/admin/add_update_status_permission.php
/admin/left_panel.php
```

## Steps to Deploy

### Step 1: Upload Files
Upload all files from the list above to your live server using FTP/cPanel File Manager.

### Step 2: Edit Database Credentials in quick_setup.php
Open `/admin/quick_setup.php` and update lines 15-17 with your live database credentials:

```php
$host = 'localhost';
$username = 'YOUR_LIVE_DB_USERNAME';
$password = 'YOUR_LIVE_DB_PASSWORD';
$database = 'YOUR_LIVE_DB_NAME';
```

### Step 3: Test the Fix
1. Go to: `https://northsuperfastservice.com/admin/index.php`
2. Login with your existing credentials
3. The error should be GONE now! ✅

### Step 4: Create Permission Tables (Optional)
If you want to use the User Management system:

1. Go to: `https://northsuperfastservice.com/admin/quick_setup.php`
2. It will create all necessary tables
3. **DELETE the file immediately after running!**

### Step 5: Start Using New Features
Once tables are created:
- Access User Management: `/admin/users.php`
- Access Role Management: `/admin/roles.php`
- Access Update Status: `/admin/delivery_status.php`
- Access Database Sync: `/admin/database/database_sync.php`

## What Was Fixed?

### Before:
```php
// Would crash if tables don't exist
function hasPermission($permission_key) {
    $query = "SELECT * FROM tbl_permissions...";
    $result = mysqli_query($conn, $query); // ERROR: Table doesn't exist
    return $row['has_perm'] > 0;
}
```

### After:
```php
// Checks if tables exist first
function hasPermission($permission_key) {
    $tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_permissions'");
    if (!$tables_check || mysqli_num_rows($tables_check) == 0) {
        return true; // Grant access if tables don't exist yet
    }
    
    $query = "SELECT * FROM tbl_permissions...";
    $result = mysqli_query($conn, $query);
    return $row['has_perm'] > 0;
}
```

## Tables That Will Be Created (if you run setup)

1. **tbl_users** - User accounts
2. **tbl_roles** - User roles
3. **tbl_permissions** - Permission definitions
4. **tbl_role_permissions** - Role-permission mappings

## Default Permissions Created

- Dashboard (view)
- Dockets (view, create, edit, delete, status update)
- Manifest (view, create, edit, delete, print)
- Staff (view, create, edit, delete)
- Clients (view, create, edit, delete)
- Vehicles (view, create, edit, delete)
- Reports (view, export)
- Settings (view, edit)
- User Management (view, create, edit, delete)
- Role Management (view, create, edit, delete)

## Security Notes

⚠️ **IMPORTANT**: Delete `quick_setup.php` after running it!

```bash
# After running setup, delete the file:
rm /path/to/admin/quick_setup.php
```

## Rollback (if needed)

If something goes wrong, you can revert by uploading the old version of `check_auth.php` from your backup.

## Support

If you encounter any issues:
1. Check if you uploaded all files correctly
2. Verify database credentials in `quick_setup.php`
3. Check server error logs: `/path/to/logs/error.log`
4. Make sure PHP version is 7.4 or higher

---

**Last Updated**: November 6, 2025
**Version**: 2.0
**Status**: Ready for Production ✅
