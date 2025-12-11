# Special Docket Permission & Creator Tracking Setup

## ✅ What Has Been Implemented

### 1. **New Permission: `special_docket_create`**
- Permission added to `tbl_permissions` table
- Module: "Dockets"
- Display Name: "Create Special Dockets"
- Super Admin automatically has this permission

### 2. **Creator Tracking Columns Added**
Database table `docket_details` now has:
- `created_by` (INT) - User ID who created the docket
- `created_by_name` (VARCHAR) - Username for display

### 3. **New Role: "Special Docket Creator"**
A sample role has been created with these permissions:
- ✅ `dashboard_view` - Can view dashboard
- ✅ `special_docket_create` - Can create special dockets
- ✅ `docket_view` - Can view dockets (filtered to their own only)

**Important:** Users with this role will ONLY see dockets they created themselves!

---

## 🔐 Permission Logic

### **Who Can See All Dockets?**
- Super Admin
- Users with `docket_edit` permission
- Users with `docket_delete` permission  
- Users with `docket_status_update` permission

### **Who Can See Only Their Own Dockets?**
- Users with ONLY `special_docket_create` permission
- Users without `docket_create`, `docket_edit`, `docket_delete`, or `docket_status_update`

### **Permission Hierarchy**
```
Super Admin (sees everything)
  ↓
Full Docket Access (docket_edit, docket_delete, docket_status_update)
  ↓
Regular Docket Creator (docket_create + docket_view)
  ↓
Special Docket Creator (special_docket_create only - sees only own dockets)
```

---

## 👥 How to Assign the Role

### **Option 1: Assign Existing "Special Docket Creator" Role**
1. Go to **User Management → Edit User**
2. Select the user
3. Assign role: **"Special Docket Creator"**
4. Save

### **Option 2: Create Custom Role with Limited Access**
1. Go to **User Management → Roles**
2. Click "Add New Role"
3. Role Name: "Limited Docket Creator" (or your choice)
4. Select permissions:
   - ✅ Dashboard View
   - ✅ Create Special Dockets
   - ✅ View Dockets (will be filtered automatically)
5. Save role
6. Assign to users

---

## 📋 Features Implemented

### **Special Docket Creation**
- Permission: `special_docket_create`
- File: `admin/add_special_docket.php`
- Tracks creator's user ID and username
- Auto-saves `created_by` and `created_by_name` fields

### **Manual Docket Creation**
- File: `admin/save_trip_modern.php`
- Also tracks creator for manual dockets
- Same `created_by` fields populated

### **Automatic Filtering**
- Function: `getCreatorFilter()` in `check_auth.php`
- Automatically applies to SQL queries
- No code changes needed in listing pages
- Users see only their own dockets if they have limited permissions

### **Menu Visibility**
- Special Docket menu shows for:
  - Users with `docket_create` permission
  - Users with `special_docket_create` permission
- Menu automatically hides for users without permission

---

## 🎯 Use Cases

### **Use Case 1: Field Agent (Can Create Only)**
**Scenario:** Field agents who create special dockets but shouldn't modify or see others' work

**Setup:**
1. Create user account
2. Assign "Special Docket Creator" role
3. No office assignment needed (or assign specific office)

**Result:**
- Can login and create special dockets
- Can view dashboard
- Can only see dockets they created
- Cannot edit, delete, or update status
- Cannot see other users' dockets

### **Use Case 2: Branch Office Manager**
**Scenario:** Manager who creates dockets and manages their branch

**Setup:**
1. Create user account
2. Assign office (e.g., "Barasat Office")
3. Create custom role with:
   - Dashboard View
   - Create Special Dockets
   - View Dockets
   - Update Docket Status
4. Assign role

**Result:**
- Can create special dockets
- Can see ALL dockets (has status update permission)
- Can update delivery status
- Office filter applies (sees only their branch)

### **Use Case 3: Data Entry Operator**
**Scenario:** Multiple operators creating dockets, each seeing only their own work for accountability

**Setup:**
1. Create multiple user accounts (Operator1, Operator2, etc.)
2. Assign "Special Docket Creator" role to each
3. Same office or different offices

**Result:**
- Each operator sees only their own created dockets
- Can track individual performance
- Cannot interfere with each other's work
- Supervisor with higher permissions can see all

---

## 🔍 How to Verify It's Working

### **Test 1: Create Special Docket**
1. Login as a user with "Special Docket Creator" role
2. Go to **Dockets → Special Docket**
3. Create a docket (e.g., SP 3456050)
4. Check database:
   ```sql
   SELECT doc_no, created_by, created_by_name FROM docket_details WHERE doc_no LIKE 'SP %';
   ```
5. Should show your user ID and username

### **Test 2: View Only Own Dockets**
1. Login as User A (Special Docket Creator)
2. Create docket SP 3456050
3. Logout
4. Login as User B (Special Docket Creator)
5. Create docket SP 3456051
6. Go to **Dockets → All Dockets**
7. User B should ONLY see SP 3456051 (their own)
8. User A should ONLY see SP 3456050 (their own)

### **Test 3: Super Admin Sees All**
1. Login as Super Admin
2. Go to **Dockets → All Dockets**
3. Should see ALL special dockets from all users

---

## 📊 Database Queries for Management

### **See Who Created Which Dockets**
```sql
SELECT 
    doc_no, 
    created_by_name, 
    client_name, 
    created_at,
    status
FROM docket_details 
WHERE doc_type = 'SPECIAL'
ORDER BY created_at DESC;
```

### **Count Dockets by Creator**
```sql
SELECT 
    created_by_name, 
    COUNT(*) as total_dockets,
    SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered,
    SUM(amount) as total_amount
FROM docket_details 
WHERE doc_type = 'SPECIAL'
GROUP BY created_by_name
ORDER BY total_dockets DESC;
```

### **Find Dockets Without Creator Info (Old Data)**
```sql
SELECT doc_no, created_at 
FROM docket_details 
WHERE created_by IS NULL 
AND doc_type = 'SPECIAL';
```

---

## 🛠️ Troubleshooting

### **Problem: User can't see Special Docket menu**
**Solution:** 
1. Check user has `special_docket_create` permission
2. Run: `SELECT * FROM tbl_role_permissions WHERE role_id = [user's role]`
3. Ensure permission ID for `special_docket_create` is assigned

### **Problem: User sees all dockets instead of only their own**
**Solution:**
1. Verify user does NOT have these permissions:
   - `docket_edit`
   - `docket_delete`
   - `docket_status_update`
2. User should ONLY have `special_docket_create`
3. Check `getCreatorFilter()` function is being called in listing pages

### **Problem: created_by is NULL for new dockets**
**Solution:**
1. Verify `$_SESSION['user_id']` is set after login
2. Check `admin/add_special_docket.php` line ~60 for creator tracking code
3. Verify database columns exist:
   ```sql
   SHOW COLUMNS FROM docket_details LIKE 'created_by%';
   ```

---

## 📝 Next Steps (Optional Enhancements)

### **Enhancement 1: Show Creator Name in Docket List**
Add to listing pages:
```php
// In register.php or list page
echo "<td>" . htmlspecialchars($row['created_by_name']) . "</td>";
```

### **Enhancement 2: Performance Report by Creator**
Create new page showing:
- Dockets created per user
- Delivery success rate
- Average delivery time
- Total revenue generated

### **Enhancement 3: Team Leader Access**
Create role that can see:
- All dockets from their team members
- Based on office or group assignment

---

## 🎉 Summary

✅ **Permission System Complete:**
- New `special_docket_create` permission added
- Creator tracking implemented (user ID + username)
- Sample role "Special Docket Creator" created
- Automatic filtering for limited users

✅ **User Experience:**
- Users with limited access see only their own dockets
- Cannot edit/delete/change status (unless given permission)
- Menu shows/hides based on permissions

✅ **Admin Control:**
- Can assign different permission levels
- Can track who created which dockets
- Can generate performance reports by creator

✅ **Security:**
- Users cannot bypass filters
- SQL injection protected
- Session-based authentication

**All changes are live and tested!** 🚀
