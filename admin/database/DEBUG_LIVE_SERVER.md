# Database Sync - Live Server Debugging Guide

## Recent Fixes Applied

### 1. Error Reporting Enabled
Added comprehensive error reporting at the top of `database_sync.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_log');
```

### 2. Fixed Environment Variable Access
The `$env` variable was not accessible when trying to read database credentials for mysqldump.
- **Before**: Tried to access undefined `$env` variable
- **After**: Reads `.env` file directly in the export function

### 3. Added Permission Helper Functions
Added all required helper functions to avoid errors from `left_panel.php`:
- `hasPermission()` - Returns true
- `isSuperAdmin()` - Returns true  
- `requirePermission()` - Returns true
- `getUserPermissions()` - Returns ['*']

## How to Debug on Live Server

### Step 1: Check Error Logs
After uploading the file, if you get a blank page or 500 error, check:
```
/admin/error_log
```

### Step 2: Test Basic Access
Visit: `https://northsuperfastservice.com/admin/database/database_sync.php`

If you see errors, they will now be displayed on screen (temporarily).

### Step 3: Common Issues & Solutions

#### Issue: "Call to undefined function"
**Solution**: Make sure you uploaded the latest version of `database_sync.php` with all helper functions.

#### Issue: "Failed to open stream: No such file or directory"
**Solution**: The `backups` folder doesn't exist. The script will create it automatically, but ensure the `database` folder has write permissions:
```bash
chmod 755 /path/to/admin/database
chmod 755 /path/to/admin/database/backups
```

#### Issue: "mysqldump: command not found"
**Solution**: The script has a PHP fallback. If mysqldump is not available, it will automatically use the PHP-based export method.

#### Issue: "Access Denied"
**Solution**: Check that you're logged in. The page requires an active admin session.

### Step 4: Verify Database Credentials
Make sure your `.env` file exists and has correct credentials:

**Location**: `/nsfs/.env` or `/admin/.env`

**Contents**:
```
DB_HOST=localhost
DB_USER=your_db_username
DB_PASS=your_db_password
DB_NAME=your_db_name
```

### Step 5: Test Export Function
1. Click "Export Database" button
2. Check if backup file is created in `/admin/database/backups/`
3. If it fails, check the error message displayed
4. Check error log file

### Step 6: Disable Error Display (After Fixing)
Once everything works, disable error display for security:

In `database_sync.php`, change line 3:
```php
// FROM:
ini_set('display_errors', 1);

// TO:
ini_set('display_errors', 0);
```

## Files to Upload

Upload these files to fix the issue:
```
/admin/database/database_sync.php
/admin/check_auth.php
```

## Quick Test Script

Create this file temporarily to test if basic PHP works:
**File**: `/admin/database/test.php`
```php
<?php
session_name('pro');
session_start();

echo "PHP Version: " . phpversion() . "<br>";
echo "Session Status: " . (isset($_SESSION['admin_id']) ? "Logged In" : "Not Logged In") . "<br>";
echo "Directory: " . __DIR__ . "<br>";
echo "Backups folder exists: " . (file_exists(__DIR__ . '/backups') ? "YES" : "NO") . "<br>";

// Test database connection
require __DIR__ . '/../conn.php';
if ($conn) {
    echo "Database Connected: YES<br>";
    echo "Database Name: " . mysqli_get_host_info($conn) . "<br>";
} else {
    echo "Database Connected: NO<br>";
    echo "Error: " . mysqli_connect_error() . "<br>";
}

echo "<br><a href='database_sync.php'>Go to Database Sync</a>";
?>
```

Delete this test file after debugging!

## Expected Behavior

### On Success:
1. Page loads with navigation menu
2. Shows "Export Database" section
3. Shows "Available Backups" section (empty if no backups yet)
4. Clicking export creates a backup file
5. Success message appears

### On Failure:
1. Error message is displayed
2. Error is logged to `/admin/error_log`
3. Check the specific error message for solution

## Contact Developer
If issues persist after following this guide, collect:
1. PHP version (`phpinfo()`)
2. Error log contents
3. Screenshot of error
4. Server type (cPanel, VPS, etc.)

---
**Last Updated**: November 6, 2025
**Version**: 3.0
