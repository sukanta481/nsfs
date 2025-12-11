# Docket PDF Field Updates - December 12, 2025

## Issue Report
User reported that **consignee phone number** was not showing in the PDF docket, and requested verification of all fields according to database table columns.

## Database Verification
Checked `docket_details` table structure - Found 70 columns including:
- `company_name`, `company_phone`, `company_email`, `company_address`
- `client_name`, `client_phone`, `client_email`, `client_address`
- `invoice_no`, `invoice_amount`, `eway_bill`
- All other vehicle, driver, helper, and tracking fields

## Fields Added to PDF (`admin/download_docket.php`)

### 1. **Consignee Phone Number** ✅
   - **Location**: After consignee address in the Party Section
   - **Format**: `Phone: 8961900050`
   - **Variable**: `$consignee_phone` (from `client_phone` column)

### 2. **Consignor Phone Number** ✅
   - **Location**: After consignor address in the Party Section
   - **Format**: `Phone: 8100608780`
   - **Variable**: `$consignor_phone` (from `company_phone` column)

### 3. **Invoice Amount** ✅
   - **Location**: New column in Invoice Table
   - **Format**: `₹ 5,000.00` (formatted with rupee symbol and decimals)
   - **Variable**: `$invoice_amount` (from `invoice_amount` column)
   - **Position**: Between "Invoice No" and "Eway bill No" columns

### 4. **Email Variables** ✅ (Prepared for future use)
   - Added `$consignor_email` (from `company_email`)
   - Added `$consignee_email` (from `client_email`)
   - Currently stored but not displayed in PDF (can be added if needed)

## Code Changes

### File: `admin/download_docket.php`

#### Change 1: Added Email Variables (Lines 36-43)
```php
$consignor_name = $data['company_name'] ?? '-';
$consignor_addr = $data['company_address'] ?? '-';
$consignor_phone = $data['company_phone'] ?? '-';
$consignor_email = $data['company_email'] ?? '-';  // NEW
$consignee_name = $data['client_name'] ?? '-';
$consignee_addr = $data['client_address'] ?? '-';
$consignee_phone = $data['client_phone'] ?? '-';
$consignee_email = $data['client_email'] ?? '-';   // NEW
```

#### Change 2: Added Invoice Amount Variable (Lines 47-49)
```php
$eway_bill = $data['eway_bill'] ?? '-';
$invoice_no = $data['invoice_no'] ?? '-';
$invoice_amount = $data['invoice_amount'] ?? '0.00';  // NEW
$item = $data['item'] ?? 'CONSUMER GOODS';
```

#### Change 3: Added Phone Numbers in Party Section (Lines 450-467)
```php
<div class="party-section">
    <div class="party-box">
        <div class="party-title">Consignor:</div>
        <div class="party-detail"><?= htmlspecialchars($consignor_name) ?></div>
        <div class="party-detail"><?= htmlspecialchars($consignor_addr) ?></div>
        <div class="party-detail"><strong>Phone:</strong> <?= htmlspecialchars($consignor_phone) ?></div> <!-- NEW -->
        <div class="location-info">
            <div><strong>From:</strong> <?= htmlspecialchars($pickup_location) ?></div>
        </div>
    </div>
    <div class="party-box">
        <div class="party-title">Consignee:</div>
        <div class="party-detail"><?= htmlspecialchars($consignee_name) ?></div>
        <div class="party-detail"><?= htmlspecialchars($consignee_addr) ?></div>
        <div class="party-detail"><strong>Phone:</strong> <?= htmlspecialchars($consignee_phone) ?></div> <!-- NEW -->
        <div class="location-info">
            <div><strong>To:</strong> <?= htmlspecialchars($delivery_location) ?></div>
        </div>
    </div>
</div>
```

#### Change 4: Updated Invoice Table Header (Lines 505-513)
```php
<thead>
    <tr>
        <th>Invoice No</th>
        <th>Invoice Amount</th>  <!-- NEW COLUMN -->
        <th>Eway bill No</th>
        <th>Description of Goods (said to contain)</th>
        <th>No of Pkg</th>
        <th>Remarks</th>
        <th>Trip number</th>
    </tr>
</thead>
```

#### Change 5: Updated Invoice Table Data (Lines 515-524)
```php
<tbody>
    <tr>
        <td><?= htmlspecialchars($invoice_no) ?></td>
        <td>₹ <?= number_format((float)$invoice_amount, 2) ?></td>  <!-- NEW -->
        <td><?= htmlspecialchars($eway_bill) ?></td>
        <td>• <?= htmlspecialchars($item) ?><br>• <?= htmlspecialchars($box) ?> Units (<?= htmlspecialchars($dimensions) ?>)</td>
        <td><?= htmlspecialchars($box) ?></td>
        <td></td>
        <td><?= htmlspecialchars($trip_group) ?></td>
    </tr>
</tbody>
```

## Field Mapping (Database → PDF)

| Database Column | PDF Variable | Display Location | Status |
|----------------|--------------|------------------|--------|
| `company_name` | `$consignor_name` | Party Section - Consignor | ✅ Existing |
| `company_address` | `$consignor_addr` | Party Section - Consignor | ✅ Existing |
| `company_phone` | `$consignor_phone` | Party Section - Consignor | ✅ **ADDED** |
| `company_email` | `$consignor_email` | Not displayed (variable ready) | ✅ Prepared |
| `client_name` | `$consignee_name` | Party Section - Consignee | ✅ Existing |
| `client_address` | `$consignee_addr` | Party Section - Consignee | ✅ Existing |
| `client_phone` | `$consignee_phone` | Party Section - Consignee | ✅ **ADDED** |
| `client_email` | `$consignee_email` | Not displayed (variable ready) | ✅ Prepared |
| `invoice_no` | `$invoice_no` | Invoice Table | ✅ Existing |
| `invoice_amount` | `$invoice_amount` | Invoice Table | ✅ **ADDED** |
| `eway_bill` | `$eway_bill` | Invoice Table | ✅ Existing |
| `doc_no` | `$doc_no` | Header & Barcode | ✅ Existing |
| `pickup_datetime` | `$pickup_date` | Header GCN Info | ✅ Existing |
| `car_number` | `$car_no` | Header GCN Info | ✅ Existing |
| `car_model` | `$car_type` | Vehicle Type Row | ✅ Existing |
| `trip_group_id` | `$trip_group` | Header & Invoice Table | ✅ Existing |
| `item` | `$item` | Invoice Table | ✅ Existing |
| `box` | `$box` | Invoice Table & Package Details | ✅ Existing |
| `weight` | `$weight` | Package Details | ✅ Existing |
| `dimensions` | `$dimensions` | Package Details & Invoice Table | ✅ Existing |
| `service_type` | `$service_mode` | Package Details | ✅ Existing |
| `pickup_location` | `$pickup_location` | Party Section - From | ✅ Existing |
| `delivery_location` | `$delivery_location` | Party Section - To | ✅ Existing |
| `amount` | `$data['amount']` | Declared Value Section | ✅ Existing |
| `office_name` | `$office_name` | Header | ✅ Existing |
| `office_address` | `$office_addr` | Header | ✅ Existing |
| `office_phone` | `$office_phone` | Header | ✅ Existing |

## Testing Instructions

1. **Test with existing docket** (SP 3456050):
   ```
   http://localhost/nsfs/admin/download_docket.php?docket_id=1
   ```

2. **Verify displayed fields**:
   - ✅ Consignor Phone: 8100608780
   - ✅ Consignee Phone: 8961900050
   - ✅ Invoice Amount: ₹ 5,000.00

3. **PDF Generation**:
   - Click "Print Docket" button
   - Click "Download PDF" button
   - Verify all fields appear correctly in both formats

## Sample Data (SP 3456050)
```
Doc No: SP 3456050
Company Name: TORQUE PHARMACUTICALS
Company Phone: 8100608780 ✅ NOW VISIBLE
Client Name: ss
Client Phone: 8961900050 ✅ NOW VISIBLE
Client Email: ss@gmail.com
Invoice No: 1234
Invoice Amount: 5000.00 ✅ NOW VISIBLE
Item: tv
```

## Status
✅ **COMPLETED** - All requested fields verified and properly mapped to database columns
✅ **SYNTAX VALIDATED** - No PHP errors detected
✅ **READY FOR TESTING** - User can now test with actual dockets

## Next Steps (Optional Enhancements)
If user wants to add:
1. Email addresses (company_email, client_email) - Variables are ready
2. Driver/Helper details (driver_name, driver_phone, helper_name, helper_phone)
3. Additional fields from the 70 available columns

---
**Updated By**: GitHub Copilot  
**Date**: December 12, 2025  
**Files Modified**: `admin/download_docket.php`
