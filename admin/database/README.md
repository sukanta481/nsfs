# Database Management

This folder contains database-related utilities and migration scripts.

## 📁 Folder Structure

### `/migrations/`
Contains one-time database setup and migration scripts. These files have already been executed and are kept for historical reference.

**Files:**
- `setup_docket_details.php` - Initial docket_details table setup
- `setup_status_history.php` - Status history table setup
- `create_docket_details_table.sql` - SQL for docket_details table
- `create_status_history_table.sql` - SQL for status history table
- `update_table_structure.sql` - Table structure updates
- `add_license_column.php` - License column migration
- `add_license_to_shipping_details.php` - Shipping details migration
- `add_service_type_column.sql` - Service type column migration

⚠️ **Note:** These scripts have already been executed. Do not run them again unless setting up a fresh database.

### Root Files

**`database_sync.php`**
- Database synchronization utility
- Used for syncing data between environments
- Can be run from admin panel: Settings → Database Sync

## 🚀 Usage

### For New Database Setup:
1. Run migrations in order from `/migrations/` folder
2. Import initial data if needed
3. Run `database_sync.php` to verify structure

### For Existing Database:
- Migrations already applied
- Use `database_sync.php` for maintenance and verification
