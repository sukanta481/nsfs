# Enhanced User Permission System

## Overview

The NSFS user management system has been enhanced with **branch-specific access control** and **granular permissions**. This allows you to:

1. **Assign users to specific offices/branches** - Users only see dockets from their assigned office
2. **Control status update permissions** - Limit which statuses a user can update to (e.g., only "Out for Delivery" and "Delivered")
3. **Grant additional permissions** - Add specific permissions beyond the role's default

## Quick Setup

1. Navigate to: `admin/setup_enhanced_permissions.php`
2. Click to run the setup (creates tables and permissions)
3. Go to **Users → Add New User** to create users with the new features

## New Features

### 1. Office/Branch Assignment

When creating a user, you can:
- **Select a specific office** - User only sees dockets from that office
- **Enable "Access All Offices"** - For admins who need to see everything

The dashboard, docket list, delivery status page, and all reports automatically filter data based on the user's assigned office.

### 2. Status Update Permissions

Users can be restricted to only update to specific statuses:

| Permission Key | Status |
|---------------|--------|
| `status_update_confirmed` | Confirmed |
| `status_update_picked_up` | Picked Up |
| `status_update_in_transit` | In Transit |
| `status_update_out_for_delivery` | Out for Delivery |
| `status_update_delivered` | Delivered |
| `status_update_delayed` | Delayed |
| `status_update_failed` | Failed Delivery |
| `status_update_cancelled` | Cancelled |

### 3. New Roles

| Role | Description |
|------|-------------|
| **Branch Manager** | Full access to assigned branch operations |
| **Branch Staff** | Limited operations within assigned branch |
| **Delivery Agent** | Field agent - status updates and POD upload only |
| **Viewer** | Read-only access to assigned office data |

### 4. New Permissions

#### Office Permissions
- `office_view_all` - View all offices' data
- `office_view_own` - View only assigned office data
- `office_manage` - Create/edit/delete offices

#### POD Permissions
- `pod_upload` - Upload POD documents
- `pod_view` - View POD documents
- `pod_delete` - Delete POD documents

#### Docket Permissions (Additional)
- `docket_print` - Print/download dockets
- `docket_sticker` - Print barcode stickers
- `docket_export` - Export to Excel/PDF
- `docket_history` - View status history
- `docket_assign` - Assign car/driver

## Database Changes

### New Columns in `tbl_users`
```sql
office_id INT(11) NULL          -- FK to tbl_offices
can_access_all_offices TINYINT(1) DEFAULT 0
```

### New Tables

#### `tbl_user_permissions`
User-specific permission overrides (beyond role permissions)

#### `tbl_user_status_permissions`
Controls which statuses a user can update to

#### `tbl_user_access_log`
Audit log of user actions

#### `tbl_permission_groups`
Groups permissions for UI organization

## How It Works

### Login Session
The login process now loads:
```php
$_SESSION['office_id'] = $user['office_id'];
$_SESSION['office_name'] = $user['office_name'];
$_SESSION['can_access_all_offices'] = $user['can_access_all_offices'];
```

### Office Filtering
Use these helper functions in any query:

```php
// Get SQL filter for office-based access
$officeFilter = getOfficeFilter('dd'); // Returns " AND dd.office_id = X"

// Check if user can access a specific docket
if (canAccessDocket($docket_id)) { ... }

// Get user's office info
$office = getUserOffice(); // Returns office array or null
```

### Status Permission Checking
```php
// Check if user can update to a specific status
if (canUpdateToStatus('Delivered')) { ... }

// Get all statuses user can update to
$allowedStatuses = getAllowedStatuses(); // Returns array of status names
```

## Example: Creating a Malda Office User

1. Go to **Users → Add New User**
2. Fill in basic info (username, email, password)
3. Select Role: **Delivery Agent**
4. Select Office: **Malda** (from the office cards)
5. In Status Update Permissions, check only:
   - ☑ Out for Delivery
   - ☑ Delivered
   - ☑ Delayed
   - ☑ Failed Delivery
6. Click **Create User**

This user will:
- Only see dockets from Malda office
- Only be able to update dockets to the selected statuses
- Have POD upload capability (from Delivery Agent role)
- NOT be able to create dockets, manage staff, etc.

## File Changes Summary

| File | Changes |
|------|---------|
| `add_user_new.php` | New enhanced user creation with office & permissions |
| `check_auth.php` | Added `getOfficeFilter()`, `canUpdateToStatus()`, `getAllowedStatuses()`, `canAccessDocket()`, `getUserOffice()`, `logUserAction()` |
| `login_new.php` | Added office session variables |
| `index.php` | Dashboard filtered by office, office banner shown |
| `list_register_new.php` | Docket list filtered by office |
| `delivery_status.php` | Status updates filtered by office & status permissions |
| `users.php` | Shows office assignment in user list |
| `setup_enhanced_permissions.php` | One-click setup script |

## Upgrading Existing Users

Existing users without office assignment will have full access (backwards compatible). To restrict them:

1. Edit the user
2. Uncheck "Access All Offices"
3. Select a specific office
4. Save
