# 🎯 DOCKET DETAILS TABLE - QUICK START GUIDE

## ✅ **WHAT I CREATED FOR YOU**

### **1. New Database Table: `docket_details`**
- **Only `doc_no` (docket number) is mandatory and unique**
- All other fields automatically sync from related tables or default to 'N/A'
- Can save incomplete data and update later

### **2. Files Created:**

| File | Purpose |
|------|---------|
| `create_docket_details_table.sql` | SQL script to create the table |
| `DocketDetailsManager.php` | PHP class to manage dockets with auto-sync |
| `setup_docket_details.php` | **Run this first to create table** |
| `DOCKET_DETAILS_TABLE_DOCUMENTATION.md` | Complete documentation |

### **3. Files Updated:**

| File | Changes |
|------|---------|
| `save_trip_modern.php` | Now uses docket_details with auto-sync |
| `check_duplicate_docket.php` | Checks new table for duplicates |

---

## 🚀 **HOW TO SET IT UP**

### **Step 1: Create the Table**

Open your browser and visit:
```
http://localhost/nsfs/admin/setup_docket_details.php
```

This will:
- ✅ Create the `docket_details` table
- ✅ Show the table structure
- ✅ Test the table
- ✅ Confirm everything works

### **Step 2: Test It**

Visit your trip page:
```
http://localhost/nsfs/admin/add_trip_modern.php
```

Create a trip with dockets - it will automatically:
- ✅ Save to `docket_details` table
- ✅ Auto-sync car details from `tbl_car`
- ✅ Auto-sync driver details from `tbl_driver`
- ✅ Auto-sync helper details from `tbl_helper` (if selected)
- ✅ Auto-sync company details from `tbl_company`
- ✅ Also save to `tbl_shipping_details` for backward compatibility

---

## 🎨 **HOW IT WORKS**

### **Auto-Sync Magic:**

```
You provide:                System automatically fills:
────────────                ───────────────────────────

doc_no: "ABC123"            ✓ Saved as unique identifier
car_id: 5                   → car_number, car_model (from tbl_car)
driver_id: 3                → driver_name, driver_phone, driver_license (from tbl_driver)
company_id: 10              → company_name, company_email, company_phone (from tbl_company)
helper_id: 2                → helper_name, helper_phone (from tbl_helper)

Not provided:               → Defaults to 'N/A' or 0
```

### **Example:**

**Before (Old Way):**
```php
// Had to manually get all details
$car = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tbl_car WHERE car_id = 5"));
$driver = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tbl_driver WHERE driver_id = 3"));
// ... then manually insert all fields
```

**After (New Way):**
```php
$docketManager->saveDocket([
    'doc_no' => 'ABC123',
    'car_id' => 5,      // Auto-syncs car details!
    'driver_id' => 3,   // Auto-syncs driver details!
    'company_id' => 10  // Auto-syncs company details!
]);
// Done! All details automatically filled!
```

---

## 📊 **TABLE FEATURES**

### **Smart Defaults:**

| Field Type | Default Value | Can Update Later? |
|------------|---------------|-------------------|
| Docket No | **REQUIRED** | ✗ (Unique identifier) |
| Car Details | Auto-sync from tbl_car | ✓ Yes |
| Driver Details | Auto-sync from tbl_driver | ✓ Yes |
| Helper Details | Auto-sync from tbl_helper | ✓ Yes |
| Company Details | Auto-sync from tbl_company | ✓ Yes |
| Client Name | 'N/A' if not provided | ✓ Yes |
| Client Phone | 'N/A' if not provided | ✓ Yes |
| Weight | 0.00 if not provided | ✓ Yes |
| Status | 'pending' by default | ✓ Yes |
| All Others | Defaults or N/A | ✓ Yes |

### **Auto-Timestamps:**
- `created_at` → Set automatically when docket is created
- `updated_at` → Updates automatically whenever docket is modified

---

## 💡 **USAGE EXAMPLES**

### **Example 1: Minimal Docket (Just Number)**
```php
require 'DocketDetailsManager.php';
$docketManager = new DocketDetailsManager($conn);

$result = $docketManager->saveDocket([
    'doc_no' => 'DOC001'
]);
// Saved! All other fields = N/A or 0
```

### **Example 2: Full Docket with Auto-Sync**
```php
$result = $docketManager->saveDocket([
    'doc_no' => 'DOC002',
    'car_id' => 5,           // Auto-syncs: car_number, car_model
    'driver_id' => 3,        // Auto-syncs: driver_name, driver_phone, driver_license
    'company_id' => 10,      // Auto-syncs: company_name, company_email, company_phone
    'helper_id' => 2,        // Auto-syncs: helper_name, helper_phone
    'client_name' => 'John Doe',
    'client_phone' => '9999999999',
    'status' => 'Picked Up'
]);
// All details auto-filled from related tables!
```

### **Example 3: Update Existing Docket**
```php
// Just call saveDocket again with same doc_no
$result = $docketManager->saveDocket([
    'doc_no' => 'DOC002',    // Existing docket
    'status' => 'Delivered',  // Update status
    'delivery_datetime' => '2025-11-05 14:30:00'
]);
// Updated! Not duplicated!
```

### **Example 4: Get Docket Info**
```php
$docket = $docketManager->getDocketByNumber('DOC002');

echo "Docket: " . $docket['doc_no'];
echo "Driver: " . $docket['driver_name'];
echo "Car: " . $docket['car_number'];
echo "Status: " . $docket['status'];
```

---

## 🔧 **INTEGRATION WITH YOUR TRIP FORM**

The `add_trip_modern.php` form now automatically:

1. ✅ **Collects docket information** from the form
2. ✅ **Checks for duplicates** in `docket_details` table
3. ✅ **Auto-syncs** car, driver, helper, company details
4. ✅ **Saves to `docket_details`** (new centralized table)
5. ✅ **Also saves to `tbl_shipping_details`** (for backward compatibility)

**No code changes needed in your form!** Everything happens automatically in `save_trip_modern.php`.

---

## 📋 **QUICK REFERENCE**

### **Check if Docket Exists:**
```php
if ($docketManager->docketExists('ABC123')) {
    echo "Docket already exists!";
}
```

### **Get All Dockets for a Trip:**
```php
$dockets = $docketManager->getDocketsByTripGroup('TRIP-20251105-0001');
foreach ($dockets as $docket) {
    echo $docket['doc_no'] . " - " . $docket['client_name'];
}
```

### **Get All Dockets for a Manifest:**
```php
$dockets = $docketManager->getDocketsByManifest(123);
```

### **Delete Docket:**
```php
$docketManager->deleteDocket('ABC123');
```

---

## ✨ **BENEFITS**

### **For You:**
- ✅ No need to manually sync data from multiple tables
- ✅ Can save incomplete dockets and update later
- ✅ Single query gets all docket information
- ✅ Automatic validation and defaults
- ✅ Less code, fewer errors

### **For Your System:**
- ✅ Centralized data storage
- ✅ Consistent data structure
- ✅ Better performance (indexed fields)
- ✅ Easier reporting and queries
- ✅ Backward compatible with existing code

---

## 🎯 **WHAT TO DO NOW**

1. **Run the setup:**
   - Visit: `http://localhost/nsfs/admin/setup_docket_details.php`
   - Confirm table is created

2. **Test the trip form:**
   - Visit: `http://localhost/nsfs/admin/add_trip_modern.php`
   - Create a trip with dockets
   - See auto-sync in action!

3. **Check the database:**
   - Open phpMyAdmin
   - Look at `docket_details` table
   - See all the auto-synced data!

4. **Read full documentation:**
   - Open: `DOCKET_DETAILS_TABLE_DOCUMENTATION.md`
   - Contains complete API reference

---

## 🆘 **TROUBLESHOOTING**

### **Table not creating?**
- Make sure `create_docket_details_table.sql` is in `admin` folder
- Check database connection in `conn.php`
- Try running SQL manually in phpMyAdmin

### **Auto-sync not working?**
- Verify related tables exist: `tbl_car`, `tbl_driver`, `tbl_helper`, `tbl_company`
- Check that IDs are valid (exist in those tables)
- Look for errors in browser console or PHP error log

### **Duplicates still happening?**
- Make sure `check_duplicate_docket.php` is updated
- Clear browser cache
- Check that `DocketDetailsManager.php` is included

---

## 📞 **NEED HELP?**

- Check `DOCKET_DETAILS_TABLE_DOCUMENTATION.md` for detailed docs
- Look at code comments in `DocketDetailsManager.php`
- Review examples in this guide

---

**Ready to go! Your docket management system is now smarter and more efficient! 🚀**
