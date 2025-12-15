# Manifest Receive Feature - Complete Guide

## 🎯 Overview

This feature allows destination offices to acknowledge receipt of manifested dockets before updating their status further.

---

## 📋 Setup Instructions

### **Step 1: Run Database Setup**

Go to: `http://localhost/nsfs/admin/setup_manifest_receive.php`

This will:
- ✅ Add "Received at Destination" status
- ✅ Add tracking columns (received_at_destination, received_by_user_id, received_by_name, received_notes)
- ✅ Create 'manifest_receive' permission

### **Step 2: Assign Permissions**

1. Go to `admin/roles.php`
2. Find the role for office staff (e.g., "Branch Manager", "Office Staff")
3. Check the following permissions:
   - ✅ `manifest_receive` - To mark dockets as received
   - ✅ `docket_view_all` - To see all office dockets (not just their own)
   - ✅ `docket_status_update` - To update status after receiving

---

## 🔄 Workflow

### **Stage 1: Manifest Creation (Origin Office - e.g., Barasat)**

1. User creates manifest to destination office (Bardhaman)
2. System updates:
   - Dockets `status` = "In Transit"
   - Dockets `office_id` = Bardhaman office ID
   - Dockets `manifest_id` = new manifest ID

### **Stage 2: In Transit**

- Bardhaman office can see dockets with status "In Transit"
- Dockets appear on their dashboard
- **But they cannot update status yet** (must receive first)

### **Stage 3: Receive at Destination (Bardhaman)**

1. Bardhaman user goes to **Manifest → Receive Manifest**
2. Sees list of incoming manifests
3. Clicks "Mark as Received" for a manifest
4. System records:
   - Status changed to "Received at Destination"
   - `received_at_destination` = current timestamp
   - `received_by_user_id` = logged-in user ID
   - `received_by_name` = user's name
   - `received_notes` = any notes entered

### **Stage 4: After Receipt**

- Bardhaman can now update dockets to:
  - "Out for Delivery"
  - "Delivered"
  - "Delayed"
  - etc.

---

## 📊 Status Flow

```
Created/Picked Up
    ↓
In Transit (manifest created, sent to destination)
    ↓
Received at Destination (destination office confirms receipt)
    ↓
Out for Delivery / Delivered / Delayed / etc.
```

---

## 🔒 Business Logic

### **Restriction Rules:**

1. **Before Receipt:**
   - Destination office can VIEW dockets
   - But CANNOT update status (must mark as received first)

2. **After Receipt:**
   - Destination office can freely update status
   - Full control over docket lifecycle

3. **Reporting:**
   - Track which dockets are pending receipt
   - Track who received and when
   - Audit trail for accountability

---

## 💻 Key Files Created

| File | Purpose |
|------|---------|
| `setup_manifest_receive.php` | One-time database setup |
| `receive_manifest.php` | Interface for receiving manifests |
| `left_panel.php` | Updated with "Receive Manifest" menu |

---

## 📝 Database Changes

### **New Columns in `docket_details`:**

```sql
received_at_destination    DATETIME NULL
received_by_user_id        INT(11) NULL
received_by_name           VARCHAR(255) NULL
received_notes             TEXT NULL
```

### **New Status:**

- `Received at Destination` (added to status enum)

### **New Permission:**

- `manifest_receive` - Can mark manifested dockets as received

---

## 🧪 Testing

### **Test Scenario:**

1. **Login as Barasat user**
   - Create manifest to Bardhaman
   - Add 3 dockets
   - Save manifest

2. **Login as Bardhaman user**
   - Check dashboard → Should see 3 dockets with "In Transit" status
   - Go to Manifest → Receive Manifest
   - Should see the incoming manifest from Barasat
   - Click "Mark as Received"
   - Add notes (optional)
   - Confirm

3. **Verify:**
   - Status changed to "Received at Destination"
   - Received date/time recorded
   - Bardhaman user name recorded
   - Now Bardhaman can update status further

---

## 🎨 UI Features

### **Receive Manifest Page:**

- Shows all incoming manifests for logged-in office
- Color-coded status badges:
  - 🔵 **In Transit** - Not yet received
  - 🟡 **Partially Received** - Some dockets received
  - 🟢 **All Received** - All dockets received
- One-click receive for entire manifest
- Optional notes field

---

## ⚙️ Configuration

### **Required Permissions by Role:**

**Branch Manager / Office Admin:**
- `docket_view_all` ✅
- `manifest_receive` ✅
- `docket_status_update` ✅

**Delivery Staff:**
- `docket_view` ✅
- `docket_status_update` ✅
- (No need for manifest_receive)

---

## 📞 Support

**Common Issues:**

### **Issue: Bardhaman sees 0 dockets**
**Solution:** User needs `docket_view_all` permission

### **Issue: Can't mark as received**
**Solution:** User needs `manifest_receive` permission

### **Issue: Dockets not showing**
**Solution:** Check if `office_id` in docket_details matches Bardhaman's office_id

---

## ✅ Checklist

- [ ] Run `setup_manifest_receive.php`
- [ ] Assign `manifest_receive` permission to office roles
- [ ] Assign `docket_view_all` permission to office roles
- [ ] Test creating manifest from one office to another
- [ ] Test receiving manifest at destination
- [ ] Verify status updates work after receipt

---

**Created:** December 12, 2025  
**Status:** ✅ Ready for Production
