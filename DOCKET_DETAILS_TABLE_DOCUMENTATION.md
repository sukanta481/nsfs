# DOCKET DETAILS TABLE - Complete Documentation

**Created:** November 5, 2025  
**Purpose:** Centralized storage for all docket information with automatic data syncing

---

## 🎯 **OVERVIEW**

The `docket_details` table is a **comprehensive, centralized storage** for all docket/shipment information in the NSFS system. It features:

- ✅ **Only `doc_no` (docket number) is mandatory and unique**
- ✅ **All other fields auto-populate from related tables** (car, driver, helper, company)
- ✅ **Missing data defaults to 'N/A' or appropriate values**
- ✅ **Can be updated later as information becomes available**
- ✅ **Automatic timestamp tracking** (created_at, updated_at)

---

## 📋 **TABLE STRUCTURE**

### **Primary Fields**
| Field | Type | Mandatory | Description |
|-------|------|-----------|-------------|
| `docket_id` | INT(11) | Auto | Primary key (auto-increment) |
| `doc_no` | VARCHAR(255) | **YES** | **Unique docket number** |
| `trip_group_id` | VARCHAR(50) | No | Groups dockets in same trip |
| `manifest_id` | INT(11) | No | Links to manifest |

### **Auto-Synced Fields**

#### **From `tbl_company`:**
- `company_id` → Triggers auto-sync
- `company_name` → Auto-populated
- `company_email` → Auto-populated
- `company_phone` → Auto-populated
- `company_address` → Auto-populated or manual
- `pickup_location` → Auto-populated

#### **From `tbl_car`:**
- `car_id` → Triggers auto-sync
- `car_number` → Auto-populated
- `car_model` → Auto-populated
- `rented_car` → Auto-populated

#### **From `tbl_driver`:**
- `driver_id` → Triggers auto-sync
- `driver_name` → Auto-populated
- `driver_phone` → Auto-populated
- `driver_license` → Auto-populated

#### **From `tbl_helper`:**
- `helper_id` → Triggers auto-sync (optional)
- `helper_name` → Auto-populated
- `helper_phone` → Auto-populated

### **Client/Receiver Fields**
All default to 'N/A' if not provided:
- `client_id` (INT)
- `client_name` (VARCHAR)
- `client_phone` (VARCHAR)
- `client_email` (VARCHAR)
- `client_address` (TEXT)
- `delivery_location` (TEXT)

### **Package Information**
Defaults to 0 or 'N/A':
- `item` → 'N/A'
- `box` → 0
- `weight` → 0.00
- `dimensions` → 'N/A'

### **Financial Fields**
All default to 0.00:
- `rate`
- `amount`
- `unit_price`
- `pay_to`

### **Service & Status**
- `service_type` → Default: 'Standard'
- `doc_type` → Default: 'DRS'
- `status` → Default: 'pending'

---

## 🔄 **AUTO-SYNC LOGIC**

### **How It Works:**

```
1. User provides: doc_no = "ABC123" (mandatory)
2. User provides: car_id = 5 (optional)
   
   ↓ System automatically fetches from tbl_car:
   
   - car_number = "MH12AB1234"
   - car_model = "Tata Ace"
   - rented_car = 0
   
3. User provides: driver_id = 3 (optional)
   
   ↓ System automatically fetches from tbl_driver:
   
   - driver_name = "Rajesh Kumar"
   - driver_phone = "9876543210"
   - driver_license = "MH1234567890"
   
4. Result: Complete docket with all details!
```

### **Example Usage:**

#### **Minimal Input (Only doc_no):**
```php
$docketManager->saveDocket([
    'doc_no' => 'DOC001'
]);
// Result: Docket saved with all defaults (N/A, 0, etc.)
```

#### **With Auto-Sync:**
```php
$docketManager->saveDocket([
    'doc_no' => 'DOC002',
    'car_id' => 5,           // Will auto-sync car details
    'driver_id' => 3,        // Will auto-sync driver details
    'company_id' => 10,      // Will auto-sync company details
    'client_name' => 'John Doe',
    'client_phone' => '9999999999'
]);
// Result: Complete docket with all auto-synced data!
```

#### **Manual Entry (No IDs):**
```php
$docketManager->saveDocket([
    'doc_no' => 'DOC003',
    'car_number' => 'MH01XX9999',
    'driver_name' => 'Unknown Driver',
    'company_name' => 'ABC Corp',
    'client_name' => 'Jane Smith'
]);
// Result: Saved with provided values, rest default to N/A
```

---

## 🚀 **IMPLEMENTATION**

### **Files Created:**

1. **`create_docket_details_table.sql`**
   - SQL script to create the table
   - Run once to initialize

2. **`DocketDetailsManager.php`**
   - PHP class for all docket operations
   - Handles auto-sync logic
   - Provides CRUD operations

3. **Updated Files:**
   - `save_trip_modern.php` - Uses new docket_details table
   - `check_duplicate_docket.php` - Checks new table for duplicates

---

## 📖 **USAGE GUIDE**

### **1. Create/Update Docket:**

```php
require 'DocketDetailsManager.php';

$docketManager = new DocketDetailsManager($conn);

$result = $docketManager->saveDocket([
    'doc_no' => 'ABC123',
    'car_id' => 5,
    'driver_id' => 3,
    'company_id' => 10,
    'client_name' => 'John Doe',
    'client_phone' => '9876543210'
]);

if ($result['success']) {
    echo "Docket saved! ID: " . $result['docket_id'];
} else {
    echo "Error: " . $result['error'];
}
```

### **2. Get Docket by Number:**

```php
$docket = $docketManager->getDocketByNumber('ABC123');

if ($docket) {
    echo "Docket found!";
    echo "Driver: " . $docket['driver_name'];
    echo "Car: " . $docket['car_number'];
    echo "Status: " . $docket['status'];
}
```

### **3. Get Dockets by Trip:**

```php
$dockets = $docketManager->getDocketsByTripGroup('TRIP-20251105-0001');

foreach ($dockets as $docket) {
    echo $docket['doc_no'] . " - " . $docket['client_name'] . "<br>";
}
```

### **4. Check if Docket Exists:**

```php
if ($docketManager->docketExists('ABC123')) {
    echo "Docket already exists!";
}
```

### **5. Update Existing Docket:**

```php
// Simply call saveDocket with same doc_no
$result = $docketManager->saveDocket([
    'doc_no' => 'ABC123',
    'status' => 'Delivered',
    'delivery_datetime' => '2025-11-05 14:30:00',
    'proof_of_delivery' => 'Signed'
]);
// Existing docket will be updated, not duplicated!
```

---

## 🔧 **DATABASE SETUP**

### **Step 1: Run SQL Script**

```bash
# In phpMyAdmin or MySQL client:
mysql -u root -p nsfs_database < create_docket_details_table.sql
```

OR via PHP:

```php
$sql = file_get_contents('create_docket_details_table.sql');
mysqli_multi_query($conn, $sql);
```

### **Step 2: Verify Table**

```sql
DESCRIBE docket_details;
```

### **Step 3: Test Insert**

```sql
INSERT INTO docket_details (doc_no) VALUES ('TEST001');
-- Should work! Only doc_no is mandatory
```

---

## 🎨 **FIELD DEFAULTS**

| Field Type | Default Value |
|------------|---------------|
| VARCHAR/TEXT | 'N/A' |
| INT | 0 |
| DECIMAL | 0.00 |
| DATETIME | NULL (except created_at/updated_at) |
| TINYINT | 0 |
| ENUM | First value (e.g., 'DRS') |

---

## 📊 **DATA FLOW DIAGRAM**

```
User Input                 System Processing              Database
─────────                  ─────────────────              ────────

doc_no: "ABC123"    →      Validate: Required      →      ✓ Saved
                                                           
car_id: 5           →      Query tbl_car           →      car_number: "MH12AB1234"
                           WHERE car_id = 5                car_model: "Tata Ace"
                                                           
driver_id: 3        →      Query tbl_driver        →      driver_name: "Rajesh"
                           WHERE driver_id = 3             driver_phone: "9876543210"
                                                           driver_license: "MH123..."
                                                           
company_id: 10      →      Query tbl_company       →      company_name: "ABC Corp"
                           WHERE company_id = 10           company_email: "abc@corp.com"
                                                           company_phone: "0123456789"
                                                           
client_name: "John" →      Use as-is               →      client_name: "John"

[Not Provided]      →      Use defaults            →      status: "pending"
                                                           service_type: "Standard"
                                                           weight: 0.00
                                                           box: 0
```

---

## ✅ **BENEFITS**

### **1. Single Source of Truth**
- All docket data in one place
- No need to query multiple tables
- Consistent data structure

### **2. Flexibility**
- Start with just docket number
- Add details as they become available
- Update anytime without constraints

### **3. Auto-Sync**
- No manual copying of car/driver/company details
- Always up-to-date with master tables
- Reduces human error

### **4. Backward Compatible**
- Still saves to `tbl_shipping_details` for legacy features
- Gradual migration possible
- No disruption to existing functionality

### **5. Performance**
- Indexed on all foreign keys
- Fast lookups by docket number
- Efficient trip group queries

---

## 🔍 **EXAMPLE QUERIES**

### **Get All Pending Dockets:**
```sql
SELECT doc_no, client_name, driver_name, car_number 
FROM docket_details 
WHERE status = 'pending' 
ORDER BY created_at DESC;
```

### **Get Today's Pickups:**
```sql
SELECT * FROM docket_details 
WHERE DATE(pickup_datetime) = CURDATE() 
ORDER BY pickup_datetime ASC;
```

### **Get Dockets by Driver:**
```sql
SELECT doc_no, client_name, client_address, status 
FROM docket_details 
WHERE driver_id = 3 
AND status != 'Delivered' 
ORDER BY pickup_datetime DESC;
```

### **Get Trip Summary:**
```sql
SELECT trip_group_id, COUNT(*) as total_dockets, 
       SUM(amount) as total_amount,
       driver_name, car_number
FROM docket_details 
WHERE trip_group_id = 'TRIP-20251105-0001'
GROUP BY trip_group_id;
```

---

## 🛠️ **MAINTENANCE**

### **Update Master Data Sync:**

If car/driver/helper/company details change in their master tables, you can re-sync:

```php
// Get docket
$docket = $docketManager->getDocketByNumber('ABC123');

// Re-save with IDs to trigger auto-sync
$docketManager->saveDocket([
    'doc_no' => 'ABC123',
    'car_id' => $docket['car_id'],      // Will re-fetch car details
    'driver_id' => $docket['driver_id'], // Will re-fetch driver details
    'company_id' => $docket['company_id'] // Will re-fetch company details
]);
```

### **Bulk Update Status:**

```sql
UPDATE docket_details 
SET status = 'In Transit' 
WHERE trip_group_id = 'TRIP-20251105-0001' 
AND status = 'pending';
```

---

## 📱 **INTEGRATION EXAMPLES**

### **With Manifest System:**

```php
// When creating manifest
$manifest_id = 123;

foreach ($docket_numbers as $doc_no) {
    $docketManager->saveDocket([
        'doc_no' => $doc_no,
        'manifest_id' => $manifest_id,
        'status' => 'Manifested'
    ]);
}
```

### **With Tracking System:**

```php
// Update tracking
$docketManager->saveDocket([
    'doc_no' => 'ABC123',
    'status' => 'In Transit',
    'tracking_link' => 'https://track.nsfs.com/ABC123',
    'delivery_notes' => 'Out for delivery'
]);
```

---

## 🚨 **ERROR HANDLING**

```php
$result = $docketManager->saveDocket($data);

if (!$result['success']) {
    // Handle error
    switch($result['error']) {
        case 'Docket number is mandatory':
            echo "Please provide docket number!";
            break;
        default:
            echo "Database error: " . $result['error'];
            error_log("Docket save error: " . $result['error']);
    }
}
```

---

## 📚 **SUMMARY**

The `docket_details` table provides a **flexible, centralized, and intelligent** storage solution for all docket information in the NSFS system.

**Key Points:**
- ✅ Only `doc_no` is mandatory
- ✅ Everything else auto-syncs or defaults to N/A
- ✅ Can update anytime
- ✅ Single query gets all docket info
- ✅ Backward compatible with existing system

**Result:** Clean, maintainable, and future-proof docket management! 🎉

---

*For questions or issues, check the code comments in `DocketDetailsManager.php` or review the SQL schema in `create_docket_details_table.sql`.*
