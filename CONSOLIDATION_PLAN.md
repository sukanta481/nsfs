# Status Update System Consolidation Plan

## Issues Identified

### 1. **Duplicate Tracking Tables**
- ❌ `tbl_tracking_history` - NEW table (just created, NOT used in live)
- ✅ `docket_status_history` - EXISTING table (used in live, has 2 records)
- **Decision:** USE `docket_status_history` only, enhance it instead

### 2. **Multiple Status Update Pages (Confusing!)**
Found pages:
- `delivery_status.php`
- `delivery_status_enhanced.php`
- `update_status.php`
- `update_docket_status.php`
- `tracking_management.php`
- Plus status update in `view_register.php`

**Decision:** Consolidate to ONE primary page

### 3. **Table Usage Confusion**
- ❌ References to `tbl_shipping_details` (OLD system)
- ✅ Should use `docket_details` ONLY

## Solution Strategy

### Phase 1: Database Consolidation

**Action:** Enhance `docket_status_history` instead of `tbl_tracking_history`

```sql
-- Drop the newly created tbl_tracking_history (not used yet)
DROP TABLE IF EXISTS tbl_tracking_history;

-- Enhance docket_status_history instead
ALTER TABLE docket_status_history
ADD COLUMN IF NOT EXISTS status_date DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS car_id INT(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS car_number VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS driver_id INT(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS driver_name VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS delay_reason VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS pod_file VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS pod_uploaded_at DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS location VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS updated_by INT(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS updated_by_name VARCHAR(150) DEFAULT NULL;
```

### Phase 2: Page Consolidation

**Single Source of Truth:** `delivery_status.php`

**Remove/Deprecate:**
- `delivery_status_enhanced.php` → merge into `delivery_status.php`
- `update_status.php` → redirect to `delivery_status.php`
- `update_docket_status.php` → keep as API only
- `tracking_management.php` → review and possibly merge

**Keep and Enhance:**
- `delivery_status.php` - Main status update page
- `view_register.php` - Add inline status update
- `update_docket_status.php` - API endpoint for AJAX

### Phase 3: Feature Implementation

Update `delivery_status.php` with:
1. No reverse status updates
2. Conditional date fields
3. Car/driver assignment
4. Delay reason dropdown
5. POD upload

Update `view_register.php` with same features inline.

## Implementation Files

### File 1: Corrected Database Migration
### File 2: Enhanced delivery_status.php (single source)
### File 3: Enhanced view_register.php status section
### File 4: API endpoint for status updates
