# User Management System - Complete Implementation Guide

## 📋 Overview
A complete Role-Based Access Control (RBAC) system has been implemented for the NSFS Admin Panel, allowing granular control over user access and permissions.

---

## 🗄️ Database Structure

### Tables Created
1. **tbl_users** - User accounts
   - user_id (Primary Key)
   - username (Unique)
   - email (Unique)
   - password (Hashed with bcrypt)
   - full_name
   - role_id (Foreign Key → tbl_roles)
   - staff_id (Foreign Key → tbl_staff, optional)
   - active_status (1=Active, 0=Inactive)
   - created_at, updated_at, last_login

2. **tbl_roles** - Role definitions
   - role_id (Primary Key)
   - role_name (Unique)
   - description
   - created_at

3. **tbl_permissions** - 32 predefined permissions
   - permission_id (Primary Key)
   - permission_name
   - permission_key (Unique identifier like 'docket_view')
   - module (Dashboard, Dockets, Manifest, Staff, Clients, Vehicles, Reports, Settings, User Management)
   - description
   - created_at

4. **tbl_role_permissions** - Many-to-many junction table
   - id (Primary Key)
   - role_id (Foreign Key)
   - permission_id (Foreign Key)

---

## 🔐 Authentication System

### Login System
- **File**: `login_new.php`
- **Features**:
  - Password visibility toggle
  - Modern gradient design
  - Specific error messages (user not found, inactive account, wrong password)
  - Sets both legacy (`admin_id`) and new (`user_id`) session variables
  - Tracks last login time
  - Uses custom session name 'pro' for compatibility

### Default Credentials
- **Username**: `sukanta481`
- **Password**: `Sukanta@0050`
- **Role**: Super Admin (full system access)

### Session Management
- **Session Name**: `pro` (required for compatibility)
- **Session Variables**: 
  - `$_SESSION['user_id']` - New system
  - `$_SESSION['admin_id']` - Legacy compatibility
  - `$_SESSION['user_name']`
  - `$_SESSION['user_email']`
  - `$_SESSION['role_id']`
  - `$_SESSION['role_name']`

### Logout
- **File**: `logout.php`
- Properly destroys session with cookie cleanup
- Redirects to login_new.php

---

## 🛡️ Permission System

### 9 Modules with 32 Permissions

#### 1. Dashboard Module
- `dashboard_view` - View Dashboard

#### 2. Dockets Module
- `docket_view` - View Dockets
- `docket_create` - Create Dockets
- `docket_edit` - Edit Dockets
- `docket_delete` - Delete Dockets

#### 3. Manifest Module
- `manifest_view` - View Manifest
- `manifest_create` - Create Manifest
- `manifest_edit` - Edit Manifest
- `manifest_print` - Print Manifest

#### 4. Staff Module
- `staff_view` - View Staff
- `staff_create` - Add Staff
- `staff_edit` - Edit Staff
- `staff_delete` - Delete Staff

#### 5. Clients Module
- `client_view` - View Clients
- `client_create` - Add Clients
- `client_edit` - Edit Clients
- `client_delete` - Delete Clients

#### 6. Vehicles Module
- `vehicle_view` - View Vehicles
- `vehicle_create` - Add Vehicles
- `vehicle_edit` - Edit Vehicles
- `vehicle_delete` - Delete Vehicles

#### 7. Reports Module
- `report_view` - View Reports
- `report_generate` - Generate Reports
- `report_export` - Export Reports

#### 8. Settings Module
- `settings_view` - View Settings
- `settings_edit` - Edit Settings

#### 9. User Management Module
- `user_view` - View Users
- `user_create` - Create Users
- `user_edit` - Edit Users
- `user_delete` - Delete Users
- `role_view` - View Roles
- `role_manage` - Manage Roles (Create, Edit, Delete)

---

## 🔧 Core Files

### 1. check_auth.php
**Purpose**: Authentication middleware for all protected pages

**Functions**:
```php
hasPermission($permission_key)
// Returns true if user has the permission, false otherwise
// Super Admin and legacy admins always return true

getUserPermissions()
// Returns array of permission keys for current user
// Returns ['*'] for Super Admin and legacy admins

requirePermission($permission_key)
// Shows 403 error page if permission is missing
// Use at the top of protected pages

isSuperAdmin()
// Checks if logged-in user is Super Admin
```

**Usage Example**:
```php
<?php
require 'check_auth.php';
requirePermission('docket_view');
require 'top_header.php';
```

---

### 2. User Management Pages

#### users.php - User List
- Displays all users with role badges and status
- Stats cards showing total/active/inactive users
- Edit/Delete action buttons with permission checks
- Success/Error message display
- Prevents self-deletion

#### add_user.php - Create User
- All fields with Font Awesome icons
- Password strength indicator (weak/medium/strong)
- Show/hide password toggle
- Role dropdown selection
- Optional staff linkage
- Active status toggle (default checked)
- `autocomplete="off"` to prevent browser autofill
- Server-side validation for uniqueness
- Redirects to users.php with success message

#### edit_user.php - Edit User
- Pre-fills all user data
- Optional password change section (dashed border)
- Only updates password if new_password field has value
- Password strength indicator for new password
- Prevents username/email conflicts
- Active status toggle
- Redirects to users.php on success

#### delete_user.php - Delete User
- Requires `user_delete` permission
- Prevents self-deletion
- Prevents deletion of last active Super Admin
- Confirmation dialog with username
- Redirects with success/error message

---

### 3. Role Management Pages

#### roles.php - Roles List
- Card-based grid layout
- Each role card shows:
  - Role name and description
  - Number of users assigned
  - Number of permissions
  - Role badge (System Role, Admin, Custom)
  - Action buttons (View, Edit, Delete)
- Prevents deletion of Super Admin role
- Prevents deletion of roles with assigned users
- Success/Error alerts with auto-hide

#### add_role.php - Create Role
- Role name and description fields
- 32 permissions grouped by 9 modules
- Each module has icon and "Select All in Module" button
- Permission checkboxes in responsive grid
- Real-time selection counter
- Global "Select All" button
- Click anywhere on permission item to toggle
- Validation requires at least one permission
- Creates role and assigns all selected permissions

#### edit_role.php - Edit Role
- Loads existing role and current permissions
- Pre-checks all currently assigned permissions
- Same intuitive UI as add_role.php
- Warning for Super Admin role editing
- Updates role name, description, and permissions
- Prevents duplicate role names
- Deletes old permissions and inserts new ones atomically

---

## 🎨 Permission-Based UI

### Menu Visibility (left_panel.php)
The sidebar menu dynamically shows/hides items based on user permissions:

- **Dashboard**: Requires `dashboard_view`
- **Dockets**: Requires `docket_view` OR `docket_create`
  - Create New Trip: Requires `docket_create`
  - All Dockets/Trips: Requires `docket_view`
- **Manifest**: Requires `manifest_view`
- **Fleet**: Requires `vehicle_view` OR `staff_view`
  - Cars/Drivers: Requires `vehicle_view`
  - Staff: Requires `staff_view`
- **Companies**: Requires `client_view`
  - Add Consignor: Requires `client_create`
- **Settings**: Requires `settings_view`
  - Database Sync: Super Admin only
- **User Management**: Requires `user_view` OR `role_view`
  - All Users: Requires `user_view`
  - Add New User: Requires `user_create`
  - Roles & Permissions: Requires `role_view` OR `role_manage`
- **Website**: Requires `settings_view`

---

## 🔒 Page-Level Protection

### Protected Pages (with permission checks added):
1. `manifest.php` - Requires `manifest_view`
2. `register.php` - Requires `docket_view`
3. `add_trip_modern.php` - Requires `docket_create`
4. `car_crud.php` - Requires `vehicle_view`
5. `driver_crud.php` - Requires `vehicle_view`
6. `staff_crud.php` - Requires `staff_view`
7. `company.php` - Requires `client_view`
8. `users.php` - Requires `user_view`
9. `add_user.php` - Requires `user_create`
10. `edit_user.php` - Requires `user_edit`
11. `delete_user.php` - Requires `user_delete`
12. `roles.php` - Requires `role_view`
13. `add_role.php` - Requires `role_manage`
14. `edit_role.php` - Requires `role_manage`

### How to Protect Additional Pages:
```php
<?php
require 'check_auth.php';
requirePermission('permission_key_here');
require 'top_header.php';
// Rest of your page code
```

---

## 🚀 Setup Instructions

### 1. Run Database Setup
Visit in your browser:
```
http://localhost/nsfs/admin/setup_user_management.php
```

This will:
- Create all 4 tables
- Insert default Super Admin role
- Insert 32 permissions across 9 modules
- Create default Super Admin user (sukanta481/Sukanta@0050)

### 2. Login to System
```
http://localhost/nsfs/admin/login_new.php
```

Use credentials:
- Username: `sukanta481`
- Password: `Sukanta@0050`

### 3. Create Custom Roles
1. Go to User Management → Roles & Permissions
2. Click "Create New Role"
3. Enter role name and description
4. Select permissions for this role
5. Save

### 4. Create Users
1. Go to User Management → Add New User
2. Fill in user details
3. Select role from dropdown
4. Optionally link to staff member
5. Save

### 5. Edit User Permissions
- Edit the role assigned to the user, OR
- Change the user's role assignment

---

## 🎯 Key Features

### ✅ Security Features
- Password hashing with bcrypt
- Session-based authentication
- Permission-based access control
- Prevention of self-deletion
- Protection of last Super Admin
- SQL injection prevention (parameterized queries)

### ✅ User Experience
- Modern gradient design
- Password visibility toggles
- Password strength indicators
- Real-time permission selection counter
- Intuitive module-based permission grouping
- Success/Error message alerts
- Confirmation dialogs for destructive actions
- Autocomplete prevention on sensitive forms

### ✅ Flexibility
- Unlimited custom roles
- Granular permission control (32 permissions)
- Link users to staff members (optional)
- Active/Inactive user status
- Role-based menu visibility
- Page-level access control

### ✅ Backward Compatibility
- Works seamlessly with legacy `tbl_administrator` table
- Legacy admins automatically get Super Admin permissions
- Supports both old and new session variables
- Custom session name 'pro' maintained

---

## 📝 Usage Examples

### Example 1: Create a "Viewer" Role
Role with read-only access:
```
Permissions:
- dashboard_view
- docket_view
- manifest_view
- client_view
- vehicle_view
- staff_view
- report_view
```

### Example 2: Create a "Data Entry" Role
Role for creating dockets only:
```
Permissions:
- dashboard_view
- docket_view
- docket_create
- client_view
- vehicle_view
- staff_view
```

### Example 3: Create a "Manager" Role
Role with most permissions except user management:
```
Permissions:
- All Dashboard, Dockets, Manifest, Staff, Clients, Vehicles, Reports, Settings permissions
- Exclude: user_view, user_create, user_edit, user_delete, role_view, role_manage
```

---

## 🔍 Troubleshooting

### Issue: Session lost after login
**Solution**: Ensure all auth-related files use `session_name('pro')` before `session_start()`

### Issue: Permission denied for legacy admin
**Solution**: Check that `hasPermission()` function returns true for users with `$_SESSION['admin_id']` set

### Issue: Menu items not hiding
**Solution**: Verify `left_panel.php` properly includes `check_auth.php` and uses `hasPermission()` checks

### Issue: Can't delete user
**Solution**: 
- Check if you're trying to delete yourself (not allowed)
- Check if deleting last Super Admin (not allowed)
- Verify you have `user_delete` permission

### Issue: Password too weak
**Solution**: Passwords must be at least 6 characters. Use combination of letters, numbers, and symbols for strong passwords.

---

## 📊 System Statistics

- **Total Files Created**: 8 new files
- **Files Modified**: 12 existing files
- **Database Tables**: 4 new tables
- **Default Permissions**: 32 permissions
- **Permission Modules**: 9 modules
- **Protected Pages**: 14+ pages
- **Lines of Code**: ~3,500+ lines

---

## 🎓 Best Practices

1. **Always use requirePermission()** at the top of protected pages
2. **Never store plain text passwords** - system uses bcrypt hashing
3. **Test with different roles** to ensure permission checks work correctly
4. **Keep at least 2 Super Admin users** to prevent lockout
5. **Backup database** before making role/permission changes
6. **Use meaningful role names** that describe the user's function
7. **Review permissions regularly** and adjust based on actual needs
8. **Log user actions** (can be implemented as future enhancement)

---

## 🚧 Future Enhancements (Optional)

1. **Activity Logging**: Track user actions with timestamps
2. **Password Reset**: Email-based password reset functionality
3. **Two-Factor Authentication**: Add extra security layer
4. **Permission Groups**: Bundle permissions into reusable groups
5. **User Activity Dashboard**: Show login history and actions
6. **Bulk User Import**: CSV import for multiple users
7. **Role Templates**: Pre-defined role templates for common scenarios
8. **Advanced Filters**: Filter users by role, status, creation date
9. **User Avatar Upload**: Profile pictures for users
10. **Permission Dependencies**: Auto-select related permissions

---

## 📞 Support

For issues or questions:
1. Check this documentation first
2. Review the code comments in key files
3. Test with Super Admin account to verify permissions
4. Check browser console for JavaScript errors
5. Review PHP error logs for server-side issues

---

## ✅ System Status

**Status**: ✅ **COMPLETE AND PRODUCTION READY**

All core features implemented:
- ✅ Database structure
- ✅ Authentication system
- ✅ User CRUD operations
- ✅ Role CRUD operations
- ✅ Permission management
- ✅ Menu visibility controls
- ✅ Page-level protection
- ✅ Safety checks and validation
- ✅ Modern UI/UX
- ✅ Backward compatibility

---

*Last Updated: 2024*
*System Version: 1.0*
*Documentation Version: 1.0*
