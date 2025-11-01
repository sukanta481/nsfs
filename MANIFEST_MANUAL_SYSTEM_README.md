# Manifest Manual Input System - Complete Implementation

## ✅ What's Been Implemented

### 1. **Car & Driver Selection**
- Added car dropdown in `manifest_new_entry.php` (fetches from `tbl_car`)
- Added driver dropdown in `manifest_new_entry.php` (fetches from `tbl_driver`)
- Both are **required fields** with validation before save
- Styled with gradient purple boxes and icons

### 2. **Manual Input Checkbox**
- Purple checkbox: "Enable Manual Input"
- When checked:
  - All client fields become editable (Consignee, Item, Address, Box, Weight)
  - Docket number auto-fetch is disabled
  - Form fields have white background instead of gray
- When unchecked:
  - Fields return to readonly mode (auto-fetch from existing dockets)

### 3. **Database Updates**

#### `tbl_manifest` table:
- Added `car_id` column (INT)
- Added `driver_id` column (INT)
- Auto-migration: columns are added if they don't exist

#### Saving Logic:
- Car and driver info saved with each manifest
- If **manual mode is enabled**: data also saves to `tbl_shipping_details`
- This allows manually entered dockets to be fetched in future manifests

### 4. **Display Updates**

#### `manifest_view.php`:
- Shows Car Number with truck icon
- Shows Driver Name with user icon
- Larger, more readable styling (40-60% size increase)

#### `manifest_print.php`:
- Car and Driver appear on printed manifest
- Dynamic values pulled from database

---

## 🚀 How to Use

### **Option 1: Auto-Fetch Mode (Default)**
1. Go to **Manifest** page
2. Select **Office** from dropdown
3. Click **Create New Manifest**
4. Select **Car** and **Driver** (required)
5. Enter **Docket Number** in any row
6. System auto-fetches: Consignee, Item, Address, Box, Weight
7. Enter **Rate** manually
8. Amount calculates automatically (Rate × Box)
9. Click **Save Manifest**

### **Option 2: Manual Input Mode**
1. Go to **Manifest** page
2. Select **Office** from dropdown
3. Click **Create New Manifest**
4. Select **Car** and **Driver** (required)
5. **Check the "Enable Manual Input" checkbox** 🟣
6. All fields become editable (white background)
7. Manually enter:
   - Docket Number
   - Consignee Name
   - Item Description
   - Delivery Address
   - Box Count
   - Weight
   - Rate
   - E-Way Bill (optional)
   - Pay To Amount (optional)
8. Amount auto-calculates (Rate × Box)
9. Click **Save Manifest**
10. **Data saves to both `tbl_manifest_details` AND `tbl_shipping_details`**

---

## 📊 Database Structure

### `tbl_manifest`
```sql
manifest_id       INT (Primary Key, Auto Increment)
manifest_no       VARCHAR(60) (Unique, e.g., "DAL25/000002")
office_id         INT (Foreign Key to tbl_offices)
car_id            INT (Foreign Key to tbl_car) ✨ NEW
driver_id         INT (Foreign Key to tbl_driver) ✨ NEW
created_at        DATETIME
total_gross       DECIMAL(12,2)
total_pay_to      DECIMAL(12,2)
net_total         DECIMAL(12,2)
```

### `tbl_manifest_details`
```sql
id                INT (Primary Key, Auto Increment)
manifest_id       INT (Foreign Key to tbl_manifest)
doc_no            VARCHAR(255)
client_name       VARCHAR(255)
item              VARCHAR(255)
client_address    TEXT
box               INT
weight            DECIMAL(10,2)
rate              DECIMAL(12,2)
amount            DECIMAL(12,2)
eway_bill         VARCHAR(255)
pay_to            DECIMAL(12,2)
```

### `tbl_shipping_details` (Manual entries save here too)
```sql
doc_no            VARCHAR(255)
client_name       VARCHAR(255)
item              VARCHAR(255)
client_address    TEXT
box               INT
weight            DECIMAL(10,2)
rate              DECIMAL(12,2)
eway_bill         VARCHAR(255)
pay_to            DECIMAL(12,2)
branch_office     VARCHAR(255)
car_id            INT ✨
driver_id         INT ✨
delivery_status   VARCHAR(50) (default: 'pending')
created_at        DATETIME
```

---

## 🎨 UI Features

### Visual Indicators:
- 🚛 **Car Dropdown**: Blue gradient box with truck icon
- 👤 **Driver Dropdown**: Purple gradient box with user icon
- 🟣 **Manual Checkbox**: Purple checkbox with pencil icon
- 📊 **Totals Display**: Large gradient cards (Gross, Pay To, Net)
- 💚 **Print Button**: Pulsing green button with animation

### Larger Fonts:
- Manifest Number: **3rem** (48px)
- Office/Date: **1.35rem** (21.6px)
- Table Text: **1.2-1.3rem** (19.2-20.8px)
- Financial Totals: **3.5rem** (56px)

---

## 🔧 Files Modified

1. `admin/manifest_new_entry.php` - Added car/driver dropdowns, manual checkbox
2. `admin/manifest_save.php` - Added car_id, driver_id, is_manual handling, dual-table save
3. `admin/manifest_view.php` - Display car & driver info
4. `admin/manifest_print.php` - Print car & driver on manifest

---

## ✅ Testing Checklist

### Test Auto-Fetch Mode:
- [ ] Select office, car, driver
- [ ] Enter existing docket number
- [ ] Verify auto-fetch works
- [ ] Save manifest successfully
- [ ] View manifest shows car & driver
- [ ] Print manifest shows car & driver

### Test Manual Mode:
- [ ] Check "Enable Manual Input" checkbox
- [ ] Fields become editable (white background)
- [ ] Enter all data manually
- [ ] Save manifest successfully
- [ ] Verify data appears in `tbl_manifest_details`
- [ ] Verify data ALSO appears in `tbl_shipping_details`
- [ ] Try auto-fetching the manually entered docket in a new manifest
- [ ] View manifest shows car & driver
- [ ] Print manifest shows car & driver

### Test Validation:
- [ ] Try saving without selecting car (should show error)
- [ ] Try saving without selecting driver (should show error)
- [ ] Verify success message shows correct manifest number
- [ ] Verify success message shows "Manual Entry" note when applicable

---

## 💡 Key Benefits

1. **Dual Mode System**: Auto-fetch existing OR manually create new entries
2. **Future-Proof**: Manual entries become available for auto-fetch later
3. **Car/Driver Tracking**: Every manifest linked to vehicle and driver
4. **Better UX**: Clear visual indicators, larger text, smooth toggles
5. **Data Integrity**: Validation ensures car/driver are always selected

---

## 🎯 Next Steps (Optional Enhancements)

1. Add car/driver filter in manifest list view
2. Show car/driver statistics (manifests per vehicle/driver)
3. Add "Copy from Previous Manifest" feature (copies car/driver selection)
4. Export manifest data with car/driver info to Excel/PDF

---

**System Status**: ✅ Fully Functional & Ready for Testing!
