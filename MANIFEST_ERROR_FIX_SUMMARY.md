# Manifest Save Error - Fixed Summary

**Date:** November 5, 2025  
**Issue:** Error when saving manifest with manual input mode

---

## 🔴 **THE ERROR (SIMPLIFIED)**

When you tried to save a manifest, you got a database error because the system was trying to insert data into the `tbl_shipping_details` table with **wrong data types and missing required values**.

### **Error Message Translation:**
```
"Failed to insert manifest: Incorrect integer value: 'N/A' for column 'have_eoa_bill_no'"
```

**What it means:** The database expected a **number (integer)** but received the text **'N/A'** instead.

---

## 🔍 **ROOT CAUSES IDENTIFIED**

### **1. Data Type Mismatches**
The code was sending wrong data types to database fields:

| Field | Expected Type | What Code Sent | Fix Applied |
|-------|---------------|----------------|-------------|
| `client_phone` | varchar (text) | 'N/A' | ✅ Changed to '0000000000' |
| `weight` | int (whole number) | float/decimal | ✅ Changed to `intval()` |
| `have_eoa_bill_no` | int (number) | 'N/A' (text) | ✅ Changed to `0` |
| `eoa_bill_no` | int (number) | 'N/A' (text) | ✅ Changed to `0` |
| `pay_to` | int (number) | float/decimal | ✅ Changed to `intval()` |
| `doc_type` | enum | 'N/A' | ✅ Changed to 'NON-DRS' |
| `reason_of_delay` | varchar | 'N/A' | ✅ Changed to 'Manual Entry' |
| `proof_of_delivery` | varchar | 'N/A' | ✅ Changed to 'Pending' |

### **2. NULL Values for Required Fields**
Several fields marked as `NOT NULL` in database were receiving NULL or invalid values.

---

## ✅ **WHAT WAS FIXED**

### **File Modified:** `admin/manifest_save.php`

**Line 192-227:** Fixed the INSERT query for `tbl_shipping_details`

### **Changes Made:**

1. **Changed `doc_type`** from `'N/A'` → `'NON-DRS'`  
   (Valid enum value for the field)

2. **Changed `client_phone`** from `'N/A'` → `'0000000000'`  
   (Valid phone number format)

3. **Changed `weight`** from `floatval()` → `intval()`  
   (Database expects integer, not decimal)

4. **Changed `have_eoa_bill_no`** from `'N/A'` → `0`  
   (Integer instead of text)

5. **Changed `eoa_bill_no`** from `'N/A'` → `0`  
   (Integer instead of text)

6. **Changed `pay_to`** from `floatval()` → `intval()`  
   (Integer instead of decimal)

7. **Changed `reason_of_delay`** from `'N/A'` → `'Manual Entry'`  
   (More meaningful value)

8. **Changed `proof_of_delivery`** from `'N/A'` → `'Pending'`  
   (More meaningful value)

---

## 📋 **HOW THE MANIFEST SAVE WORKS**

### **Process Flow:**

```
1. User fills manifest form
   ├── Selects Office
   ├── Selects Car
   ├── Selects Driver
   └── Enters docket details (25 rows)

2. User clicks "SAVE MANIFEST"
   ├── JavaScript validates Car & Driver selected
   ├── Form data sent to manifest_save.php via AJAX
   └── is_manual flag sent (0 = auto, 1 = manual)

3. manifest_save.php processes:
   ├── Validates office_id, car_id, driver_id
   ├── Creates tables if not exist
   ├── Generates manifest number (e.g., SIL25/000001)
   ├── Calculates totals (gross, pay_to, net)
   ├── Starts database transaction
   ├── Inserts into tbl_manifest
   ├── Inserts into tbl_manifest_details
   └── IF manual mode: Inserts into tbl_shipping_details ← [THIS IS WHERE ERROR WAS]

4. Returns success/error message
   └── Shows print button on success
```

---

## 🎯 **VALIDATION & CONDITIONS**

### **Pre-Save Validations:**

1. ✅ **Office must be selected** (`$office_id > 0`)
2. ✅ **Car must be selected** (`$car_id > 0`)
3. ✅ **Driver must be selected** (`$driver_id > 0`)
4. ✅ **At least one docket entry** (`count($details_to_insert) > 0`)
5. ✅ **Docket number cannot be empty** (empty rows are skipped)

### **Field Processing Logic:**

```php
// For each row in the form:
for ($i = 0; $i < $rows; $i++) {
    $doc = trim($doc_nos[$i] ?? '');
    if ($doc === '') continue; // ← Skip empty rows
    
    $amount = ($amounts[$i] !== '' && $amounts[$i] !== null) 
        ? floatval($amounts[$i]) 
        : ($rate * max(1, $box)); // ← Auto-calculate if not provided
    
    $gross_total += $amount;  // ← Sum all amounts
    $total_pay_to += $pay;    // ← Sum all pay_to values
}

$net_total = $gross_total - $total_pay_to; // ← Final calculation
```

### **Manual Mode Condition:**

```php
if ($is_manual == 1) {
    // Only insert to shipping_details if manual checkbox was checked
    // This allows tracking of manually entered shipments
}
```

---

## 🔢 **DATA FLOW & FIELD MAPPING**

### **From Form → tbl_manifest:**
- `office_id` → Office ID
- `car_id` → Car ID  
- `driver_id` → Driver ID
- `manifest_no` → Generated (e.g., SIL25/000001)
- `created_at` → Current timestamp
- `total_gross` → Sum of all amounts
- `total_pay_to` → Sum of all pay_to values
- `net_total` → Gross - Pay To

### **From Form → tbl_manifest_details:**
- `manifest_id` → From inserted manifest
- `doc_no` → Docket number
- `client_name` → Client name
- `item` → Item description
- `client_address` → Delivery address
- `box` → Number of boxes (default: 0)
- `weight` → Weight in kg (default: 0.00)
- `rate` → Rate per box (default: 0.00)
- `amount` → Total amount (rate × box)
- `eway_bill` → E-way bill number
- `pay_to` → Amount to pay (default: 0.00)

### **From Form → tbl_shipping_details (Manual Mode Only):**
All above fields PLUS:
- `doc_type` → 'NON-DRS'
- `client_phone` → '0000000000'
- `register_id` → 0
- `shipping_id` → 0
- `company_id` → 0
- `client_id` → 0
- `unit_price` → Same as rate
- `have_eoa_bill_no` → 0
- `eoa_bill_no` → 0
- `status` → 'pending'
- `reason_of_delay` → 'Manual Entry'
- `proof_of_delivery` → 'Pending'
- `branch_office` → Office name
- `driver_license` → Driver's license

---

## 🧪 **TESTING CHECKLIST**

After the fix, test these scenarios:

- [ ] **Test 1:** Save manifest with auto-fetch mode (manual checkbox OFF)
  - Should save to `tbl_manifest` and `tbl_manifest_details` only
  - Should NOT save to `tbl_shipping_details`

- [ ] **Test 2:** Save manifest with manual mode (manual checkbox ON)
  - Should save to all three tables
  - Should NOT show any database errors
  - Check that `tbl_shipping_details` has proper data types

- [ ] **Test 3:** Save manifest with empty rows
  - Empty rows should be skipped automatically

- [ ] **Test 4:** Save manifest without selecting car or driver
  - Should show error: "Please select both Car and Driver!"

- [ ] **Test 5:** Save manifest with only docket numbers (no other data in manual mode)
  - Should save with default values (0, empty strings)

---

## 📊 **DATABASE STRUCTURE NOTES**

### **tbl_shipping_details - Important Fields:**

```sql
-- These fields CANNOT be NULL (require values):
client_phone VARCHAR(255) NOT NULL
weight INT(11) NOT NULL
have_eoa_bill_no INT(11) NOT NULL
eoa_bill_no INT(11) NOT NULL
pay_to INT(11) NOT NULL

-- These fields have specific enum values:
doc_type ENUM('DRS','NON-DRS') NOT NULL
```

**Key Insight:** Always ensure integer fields get integers, not strings or decimals.

---

## 🚀 **NEXT STEPS**

1. **Test the fix** by creating a new manifest with manual mode enabled
2. **Verify** the data is correctly saved in all tables
3. **Check** the print function works after saving
4. **Monitor** error logs for any new issues

---

## 📝 **CODE REFERENCE**

### **Before (Buggy Code):**
```php
'N/A',                                    // doc_type - WRONG! Should be 'NON-DRS'
'N/A',                                    // client_phone - WRONG! 
".floatval($d['weight']).",               // weight - WRONG! Should be intval()
'N/A',                                    // have_eoa_bill_no - WRONG! Should be 0
'N/A',                                    // eoa_bill_no - WRONG! Should be 0
".floatval($d['pay']).",                  // pay_to - WRONG! Should be intval()
```

### **After (Fixed Code):**
```php
'NON-DRS',                                // doc_type - CORRECT!
'0000000000',                             // client_phone - CORRECT!
".intval($d['weight']).",                 // weight - CORRECT!
0,                                        // have_eoa_bill_no - CORRECT!
0,                                        // eoa_bill_no - CORRECT!
".intval($d['pay']).",                    // pay_to - CORRECT!
```

---

## ✨ **SUMMARY**

**The Problem:** Database expected numbers but got text ('N/A'), causing insertion failure.

**The Solution:** Changed all data types to match database requirements:
- Text fields → Text values
- Integer fields → Integer values (not 'N/A')
- Enum fields → Valid enum values

**Result:** Manifest save should now work correctly in both auto-fetch and manual modes! ✅

---

*If you encounter any other issues, check the browser console (F12) and server error logs.*
