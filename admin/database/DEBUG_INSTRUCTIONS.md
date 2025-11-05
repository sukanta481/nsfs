# Database Sync Debug Instructions

## Problem
The database sync page works on localhost but gives HTTP ERROR 500 on live server.

## Debug Files Added

1. **test_debug.php** - Diagnostic test file
2. **database_sync.php** - Updated with comprehensive debug logging
3. **debug_sync.log** - Will be created automatically with debug information

## Steps to Debug on Live Server

### Step 1: Run Diagnostic Test
1. Upload `test_debug.php` to your live server at `/admin/database/test_debug.php`
2. Access it via browser: `https://yourdomain.com/admin/database/test_debug.php`
3. Review all the checks - any red ✗ marks indicate problems
4. Common issues to look for:
   - Missing files (conn.php, left_panel.php, etc.)
   - Permission issues
   - Database connection problems
   - Missing PHP extensions

### Step 2: Check Debug Log
1. Upload the updated `database_sync.php` to your live server
2. Try to access: `https://yourdomain.com/admin/database/database_sync.php`
3. Even if it shows 500 error, check if `debug_sync.log` was created
4. Download and read `debug_sync.log` to see exactly where the script fails

### Step 3: Check Server Error Logs
Look for PHP error logs on your live server:
- cPanel: Error Log in File Manager
- Direct access: `/admin/error_log` or `/error_log`
- Server logs: Usually in `/var/log/apache2/error.log` or similar

## Common Issues and Fixes

### Issue 1: Missing Files
**Symptom:** test_debug.php shows missing files
**Fix:** 
- Ensure all files are uploaded to the correct directories
- Check that the directory structure matches localhost

### Issue 2: Permission Issues
**Symptom:** Permission errors in logs, can't create backups directory
**Fix:**
```bash
chmod 755 /path/to/admin/database
chmod 755 /path/to/admin/database/backups
chmod 644 /path/to/admin/database/*.php
```

### Issue 3: Database Connection Failed
**Symptom:** "Database connection error" in test_debug.php
**Fix:**
- Check conn.php has correct live server credentials
- Verify .env file exists and has correct DB_USER, DB_PASS, DB_NAME
- Check if MySQL server is running

### Issue 4: Session Issues
**Symptom:** Session errors in debug_sync.log
**Fix:**
- Check session.save_path in php.ini
- Ensure session directory is writable
- Check if session_name('pro') conflicts with anything

### Issue 5: PHP Memory/Execution Limits
**Symptom:** Script timeout or memory errors
**Fix:**
- Increase in php.ini or .htaccess:
```
php_value max_execution_time 300
php_value memory_limit 256M
php_value upload_max_filesize 50M
php_value post_max_size 50M
```

### Issue 6: Path Resolution Issues
**Symptom:** "File not found" errors for includes
**Fix:**
- The script uses `__DIR__` for absolute paths, but check if file structure differs
- Verify all required files are at the expected locations relative to database_sync.php

### Issue 7: PHP Version Incompatibility
**Symptom:** Syntax errors or undefined functions
**Fix:**
- Check PHP version on live server (should be 7.0+)
- Update hosting PHP version if needed
- Check if mysqli extension is enabled

## Reading the Debug Log

The debug_sync.log file will show:
```
[2025-11-06 10:30:00] Script started
[2025-11-06 10:30:00] Current directory: /home/user/public_html/admin/database
[2025-11-06 10:30:00] PHP Version: 7.4.33
[2025-11-06 10:30:00] Session started successfully
[2025-11-06 10:30:00] Checking authentication
[2025-11-06 10:30:00] User authenticated successfully
[2025-11-06 10:30:00] conn.php loaded successfully
... and so on
```

If the log stops at a certain point, that's where the error occurred.

## After Fixing the Issue

Once the problem is identified and fixed:

1. **Remove or comment out debug code** (for security and performance)
2. **Delete test_debug.php** (don't leave diagnostic files on live server)
3. **Set display_errors to 0** in production:
   ```php
   ini_set('display_errors', 0);
   ```
4. Keep `error_log` enabled to log errors to file instead of displaying them

## Quick Fix: Standalone Version

If the includes (left_panel.php, header_banner.php, footer.php) are causing issues,
I can create a standalone version of database_sync.php that doesn't require these files.

## Need More Help?

If you still can't identify the issue:
1. Send me the contents of debug_sync.log
2. Send me any error messages from your server error logs
3. Send me the output from test_debug.php
4. Let me know your PHP version and hosting environment

## Security Note

After debugging:
- Remove test_debug.php from live server
- Remove or comment out debug_log() calls
- Set display_errors = 0
- Keep error_log enabled but set to log to file only
