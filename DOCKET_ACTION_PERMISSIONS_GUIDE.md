# Docket Action Permissions - Setup Guide

## ✅ What Has Been Added

### New Granular Permissions for Docket Actions

4 new permissions have been added to give you fine-grained control over docket actions:

| Permission Key | Permission Name | Description |
|---------------|-----------------|-------------|
| `docket_view_details` | View Docket Details | Can view individual docket details page |
| `docket_download_pdf` | Download Docket PDF | Can download docket as PDF |
| `docket_edit` | Edit Dockets | Can edit docket information |
| `docket_delete` | Delete Dockets | Can delete dockets |

---

## 🔧 Setup Instructions

### Step 1: Run the Setup Script

Open your browser and navigate to:
```
http://localhost/nsfs/admin/add_docket_action_permissions.php
```

This will:
- ✅ Add the 4 new permissions to your database
- ✅ Assign all permissions to Super Admin role
- ✅ Show you implementation instructions

### Step 2: Assign Permissions to Roles

1. Go to **Roles Management** (`admin/roles.php`)
2. Click **Edit** on any role you want to configure
3. Scroll to the **Dockets** section in permissions
4. Check the boxes for the permissions you want to grant:
   - ☑️ View Docket Details
   - ☑️ Download Docket PDF
   - ☑️ Edit Dockets
   - ☑️ Delete Dockets
5. Click **Save**

---

## 🎯 How It Works

### Action Buttons in Docket List

The docket listing page (`list_register_new.php`) now shows/hides action buttons based on permissions:

| Button | Permission Required | Fallback Permission |
|--------|-------------------|---------------------|
| 👁️ **View** | `docket_view_details` | `docket_view` |
| 📥 **Download PDF** | `docket_download_pdf` | `docket_view` |
| ✏️ **Edit** | `docket_edit` | - |
| 🗑️ **Delete** | `docket_delete` | - |

### Permission Checks in Action Files

Each action file now verifies permissions:

1. **`view_register.php`** - Requires `docket_view_details` or `docket_view`
2. **`download_docket.php`** - Requires `docket_download_pdf` or `docket_view`
3. **`edit_register_new.php`** - Requires `docket_edit`
4. **`action_handler.php`** (delete action) - Requires `docket_delete`

---

## 📋 Example Use Cases

### Example 1: Read-Only User
**Role**: Viewer
**Permissions**: 
- ✅ `docket_view_details` - Can view dockets
- ✅ `docket_download_pdf` - Can download PDFs
- ❌ Edit - Cannot edit
- ❌ Delete - Cannot delete

**Result**: User can view and download dockets but cannot modify them.

---

### Example 2: Data Entry User
**Role**: Data Entry
**Permissions**: 
- ✅ `docket_view_details` - Can view dockets
- ✅ `docket_edit` - Can edit dockets
- ❌ Download PDF - Cannot download
- ❌ Delete - Cannot delete

**Result**: User can view and edit dockets but cannot download or delete them.

---

### Example 3: Manager
**Role**: Manager
**Permissions**: 
- ✅ `docket_view_details` - Can view dockets
- ✅ `docket_download_pdf` - Can download PDFs
- ✅ `docket_edit` - Can edit dockets
- ✅ `docket_delete` - Can delete dockets

**Result**: Full access to all docket actions.

---

## 🔄 Backward Compatibility

### Legacy Permission Support

The system maintains backward compatibility with existing permissions:

- Users with `docket_view` permission can still view and download dockets
- The new permissions provide more granular control for new roles

### Fallback Mechanism

The `requirePermission()` function now supports fallback permissions:

```php
requirePermission('docket_download_pdf', 'docket_view');
// Checks for docket_download_pdf first, falls back to docket_view
```

This ensures existing users don't lose access while new permissions are being configured.

---

## 🧪 Testing

### Test with Different Roles

1. **Create a test role**:
   - Go to `admin/add_role.php`
   - Create a role like "Docket Viewer"
   - Assign only `docket_view_details` permission

2. **Create a test user**:
   - Go to `admin/add_user.php`
   - Assign the "Docket Viewer" role

3. **Login as test user**:
   - Go to Dockets → All Dockets
   - You should see only the **View** button
   - Edit and Delete buttons should be hidden

4. **Try direct URL access**:
   - Try accessing `edit_register_new.php?docket_id=1`
   - Should show "Access Denied" page

---

## 📊 Current Permission Structure

### All Docket Permissions

```
Dockets Module:
├── docket_view (legacy) - View dockets list
├── docket_create - Create new dockets/trips
├── docket_view_details ⭐ NEW - View individual docket
├── docket_download_pdf ⭐ NEW - Download docket PDF
├── docket_edit ⭐ NEW - Edit dockets
├── docket_delete ⭐ NEW - Delete dockets
├── docket_status_update - Update docket status
└── special_docket_create - Create special dockets
```

---

## 🎨 UI Changes

### Before
All users with docket access saw all 4 action buttons:
```
[👁️ View] [📥 PDF] [✏️ Edit] [🗑️ Delete]
```

### After
Action buttons appear based on user's permissions:

**Full Access User**:
```
[👁️ View] [📥 PDF] [✏️ Edit] [🗑️ Delete]
```

**Read-Only User**:
```
[👁️ View] [📥 PDF]
```

**Editor User**:
```
[👁️ View] [✏️ Edit]
```

---

## 🚀 Next Steps

1. ✅ **Run setup script**: `admin/add_docket_action_permissions.php`
2. ✅ **Configure roles**: Assign new permissions to existing roles
3. ✅ **Test thoroughly**: Create test users with different permission combinations
4. ✅ **Deploy to production**: Once tested, pull changes on live server

---

## 📝 Files Modified

| File | Change |
|------|--------|
| `admin/add_docket_action_permissions.php` | ⭐ NEW - Setup script for permissions |
| `admin/check_auth.php` | Updated `requirePermission()` to support fallback permissions |
| `admin/list_register_new.php` | Added permission checks to show/hide action buttons |
| `admin/download_docket.php` | Added permission check at file start |
| `admin/edit_register_new.php` | Added permission check at file start |
| `admin/action_handler.php` | Added permission check for delete action |

---

## 💡 Tips

- **Always assign `docket_view_details`** if you want users to see docket details
- **Grant `docket_download_pdf`** separately if you want to control PDF downloads
- **Use `docket_edit`** for users who need to modify dockets
- **Restrict `docket_delete`** to managers or admin roles only
- **Combine with office filters** for location-based access control

---

## ✅ Status

- **Commit**: 56dc573
- **Branch**: main
- **Status**: ✅ Pushed to GitHub
- **Ready for**: Testing and role configuration

---

**Need Help?** Check the roles management page (`admin/roles.php`) to configure permissions for each role.
