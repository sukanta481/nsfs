# Deployment Guide: Enhanced Status Update System

## Overview
This guide covers the deployment of enhanced status tracking features for the NSFS docket management system.

## New Features Implemented

### 1. **No Reverse Status Updates**
- Status updates can only move forward in the workflow
- Exception: "Delayed" status can be set at any time
- Final statuses (Delivered, Failed, Cancelled) cannot be changed

### 2. **Conditional Date Fields**
- **Out for Delivery**: Requires date/time selection
- **Delivered**: Requires delivery date/time
- **Delayed**: Requires delay date/time

### 3. **Car and Driver Assignment**
- When marking "Out for Delivery", must select:
  - Vehicle from `tbl_car`
  - Driver from `tbl_staff` (role = 'Driver')
- Details are automatically stored in tracking history

### 4. **Delay Reason Dropdown**
- When marking "Delayed", must select reason from predefined list
- Reasons are categorized: Traffic, Weather, Vehicle, Customer, Other
- Reasons are manageable via `tbl_delay_reasons` table

### 5. **POD (Proof of Delivery) Upload**
- When marking "Delivered", can upload POD file
- Supported formats: JPG, PNG, PDF
- Files stored in: `uploads/pod/{year}/{month}/{doc_no}/`
- File path saved in tracking history and docket_details

## Files Created/Modified

### New Files:
1. `admin/database/migrations/enhance_tracking_history.sql` - Database schema updates
2. `admin/delivery_status_enhanced.php` - Enhanced status update page
3. `DEPLOYMENT_GUIDE_STATUS_ENHANCEMENTS.md` - This file

### Files to be Modified (Next Steps):
1. `admin/delivery_status.php` - Replace with enhanced version
2. `admin/api_tracking_update.php` - Add validation logic
3. `admin/conn.php` - Remove debug code
4. `admin/add_user.php` - Remove debug code

## Deployment Steps

### Step 1: Backup Current System
```bash
# Backup database
mysqldump -u root -p nsfs > backup_before_enhancement_$(date +%Y%m%d).sql

# Backup files
cp admin/delivery_status.php admin/delivery_status_backup.php
cp admin/api_tracking_update.php admin/api_tracking_update_backup.php
```

### Step 2: Run Database Migration
```bash
# Option 1: Via MySQL command line
mysql -u root -p nsfs < admin/database/migrations/enhance_tracking_history.sql

# Option 2: Via phpMyAdmin
# 1. Open phpMyAdmin
# 2. Select 'nsfs' database
# 3. Click 'Import' tab
# 4. Choose file: admin/database/migrations/enhance_tracking_history.sql
# 5. Click 'Go'
```

### Step 3: Create Upload Directory
```bash
# Create POD upload directory with proper permissions
mkdir -p uploads/pod
chmod 755 uploads/pod

# On Windows (via PHP or manually):
# Just create the folder: C:\xampp\htdocs\nsfs\uploads\pod\
```

### Step 4: Deploy Enhanced Files

#### Option A: Replace existing file
```bash
cp admin/delivery_status_enhanced.php admin/delivery_status.php
```

#### Option B: Keep both versions (Recommended for testing)
```
# Access enhanced version at:
# http://localhost/nsfs/admin/delivery_status_enhanced.php

# Keep original at:
# http://localhost/nsfs/admin/delivery_status.php
```

### Step 5: Test on Local Server

#### Test Scenarios:

1. **Test Forward Status Update** ✓
   - Go to delivery_status_enhanced.php
   - Select a docket with status "Pending"
   - Update to "In Transit" → Should succeed

2. **Test Reverse Status Prevention** ✓
   - Select a docket with status "Delivered"
   - Try to update to "In Transit" → Should show error

3. **Test Out for Delivery with Car/Driver** ✓
   - Select a docket
   - Update to "Out for Delivery"
   - Must select date, car, and driver → Should succeed
   - Check tracking history for car/driver details

4. **Test Delivered with POD** ✓
   - Select a docket
   - Update to "Delivered"
   - Must select date and upload POD file → Should succeed
   - Verify file is saved in uploads/pod/

5. **Test Delayed with Reason** ✓
   - Select a docket
   - Update to "Delayed"
   - Must select date and delay reason → Should succeed
   - Check tracking history for reason

### Step 6: Verify Database Updates

Run these queries to verify:

```sql
-- Check if new columns exist
SHOW COLUMNS FROM tbl_tracking_history;
SHOW COLUMNS FROM docket_details;

-- Check delay reasons
SELECT * FROM tbl_delay_reasons WHERE is_active = 1;

-- Check status hierarchy
SELECT * FROM tbl_status_hierarchy ORDER BY status_order;

-- Test query: Get tracking history with new fields
SELECT
    th.*,
    d.doc_no,
    d.status as current_status
FROM tbl_tracking_history th
LEFT JOIN docket_details d ON th.docket_id = d.docket_id
ORDER BY th.created_at DESC
LIMIT 10;
```

## Database Schema Changes

### New Columns in `tbl_tracking_history`:
- `status_date` - Date for Out for Delivery, Delivered, Delayed
- `car_id` - Vehicle assigned
- `car_number` - Vehicle number (denormalized)
- `driver_id` - Driver assigned
- `driver_name` - Driver name (denormalized)
- `delay_reason` - Reason for delay
- `pod_file` - Proof of Delivery file path
- `pod_uploaded_at` - POD upload timestamp

### New Columns in `docket_details`:
- `last_status_update` - Last status change timestamp
- `current_location` - Current location from tracking
- `out_for_delivery_date` - Date marked out for delivery
- `actual_delivery` - Actual delivery date/time
- `delay_date` - Date when delayed
- `current_delay_reason` - Current delay reason

### New Tables:
1. **`tbl_delay_reasons`**
   - Stores predefined delay reasons
   - Categorized by type
   - Can be managed by admin

2. **`tbl_status_hierarchy`**
   - Defines status workflow order
   - Specifies required fields per status
   - Controls backward movement

## Configuration

### Delay Reasons Management
Add/edit delay reasons via SQL:

```sql
-- Add new delay reason
INSERT INTO tbl_delay_reasons (reason_text, reason_category, is_active)
VALUES ('New reason here', 'Traffic', 1);

-- Disable a reason
UPDATE tbl_delay_reasons SET is_active = 0 WHERE reason_id = X;

-- Update reason text
UPDATE tbl_delay_reasons
SET reason_text = 'Updated text'
WHERE reason_id = X;
```

### Status Hierarchy Customization
Modify status workflow:

```sql
-- Change if status requires certain fields
UPDATE tbl_status_hierarchy
SET requires_date = 1, requires_car_driver = 1
WHERE status_name = 'Out for Delivery';

-- Make a status final (cannot be changed after)
UPDATE tbl_status_hierarchy
SET is_final = 1
WHERE status_name = 'Delivered';
```

## Live Server Deployment

### Prerequisites:
1. Database backup completed ✓
2. All features tested on local ✓
3. POD upload directory created ✓

### Deployment Steps for Live Server:

1. **Upload Files via FTP/cPanel File Manager:**
   ```
   Upload:
   - admin/database/migrations/enhance_tracking_history.sql
   - admin/delivery_status_enhanced.php
   ```

2. **Run Migration on Live Database:**
   - Via cPanel → phpMyAdmin
   - Select database
   - Import: enhance_tracking_history.sql

3. **Create Upload Directory:**
   ```bash
   # Via SSH or cPanel File Manager
   mkdir -p public_html/uploads/pod
   chmod 755 public_html/uploads/pod
   ```

4. **Test on Live:**
   ```
   https://northsuperfastservice.com/admin/delivery_status_enhanced.php
   ```

5. **Once Verified, Replace Original:**
   ```bash
   # Rename original as backup
   mv admin/delivery_status.php admin/delivery_status_old.php

   # Rename enhanced version
   mv admin/delivery_status_enhanced.php admin/delivery_status.php
   ```

## Troubleshooting

### Issue: "Table doesn't exist" error
**Solution:** Run the migration SQL file on your database

### Issue: POD upload fails
**Solution:**
1. Check directory exists: `uploads/pod/`
2. Check permissions: `chmod 755 uploads/pod`
3. Check PHP upload limits in php.ini:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

### Issue: Car/Driver dropdown empty
**Solution:**
1. Verify `tbl_car` table has active vehicles
2. Verify `tbl_staff` table has staff with role = 'Driver'
3. Check SQL query in delivery_status_enhanced.php lines 206-210

### Issue: Cannot update status - validation error
**Solution:**
1. Check `tbl_status_hierarchy` table exists and has data
2. Verify status names match exactly (case-sensitive)
3. Check current_status is being passed correctly

## Rollback Procedure

If issues occur on live:

```bash
# 1. Restore old file
mv admin/delivery_status_old.php admin/delivery_status.php

# 2. Restore database (if needed)
mysql -u username -p database_name < backup_before_enhancement_YYYYMMDD.sql

# 3. Remove new tables (if needed)
mysql -u username -p database_name -e "
DROP TABLE IF EXISTS tbl_delay_reasons;
DROP TABLE IF EXISTS tbl_status_hierarchy;
"
```

## Support & Maintenance

### Regular Maintenance Tasks:

1. **Monitor POD Storage:**
   ```bash
   # Check upload directory size
   du -sh uploads/pod/

   # Clean old files (optional)
   find uploads/pod/ -type f -mtime +365 -delete
   ```

2. **Review Delay Reasons:**
   ```sql
   -- See most common delay reasons
   SELECT delay_reason, COUNT(*) as count
   FROM tbl_tracking_history
   WHERE delay_reason IS NOT NULL
   GROUP BY delay_reason
   ORDER BY count DESC;
   ```

3. **Audit Status Changes:**
   ```sql
   -- Recent status updates
   SELECT
       th.doc_no,
       th.status,
       th.updated_by_name,
       th.created_at,
       th.car_number,
       th.driver_name,
       th.delay_reason
   FROM tbl_tracking_history th
   WHERE DATE(th.created_at) = CURDATE()
   ORDER BY th.created_at DESC;
   ```

## Next Steps After Deployment

1. **Train Staff:** Show users how to:
   - Update status with new required fields
   - Upload POD files
   - Select delay reasons

2. **Monitor Usage:** Track for 1-2 weeks:
   - Any validation errors
   - POD upload success rate
   - Common delay reasons

3. **Optimize:** Based on usage:
   - Add more delay reasons if needed
   - Adjust status hierarchy if workflow changes
   - Improve UI based on feedback

## Contact & Support

For issues or questions:
- Check logs: `admin/debug_conn.log`, `admin/debug_add_user.log`
- Review PHP error log
- Test with `?debug=1` parameter (if debug mode still active)

---

**Deployment Date:** _____________
**Deployed By:** _____________
**Verified By:** _____________
**Status:** [ ] Local Testing [ ] Staging [ ] Production

