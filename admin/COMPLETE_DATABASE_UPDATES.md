# Complete Database Update Queries for tbl_shipping_details

## Run these queries in phpMyAdmin SQL tab or MySQL command line:

```sql
-- 1. Add company_address column (for pickup location from company)
ALTER TABLE `tbl_shipping_details` 
ADD COLUMN `company_address` TEXT NULL AFTER `company_email`;

-- 2. Add dimensions column (for package dimensions L x W x H)
ALTER TABLE `tbl_shipping_details` 
ADD COLUMN `dimensions` VARCHAR(100) NULL AFTER `weight`;

-- 3. Add service_type column (Standard, Express, Overnight)
ALTER TABLE `tbl_shipping_details` 
ADD COLUMN `service_type` VARCHAR(50) NULL DEFAULT 'Standard' AFTER `doc_type`;
```

## Summary of Changes:

### New Columns Added:
1. **company_address** (TEXT, NULL)
   - Stores the pickup location/company address
   - Auto-filled from tbl_company when company is selected

2. **dimensions** (VARCHAR(100), NULL)
   - Stores package dimensions in format "L x W x H"
   - Example: "10x20x30 cm"

3. **service_type** (VARCHAR(50), NULL, DEFAULT 'Standard')
   - Stores the type of service
   - Options: Standard, Express, Overnight
   - Default value is 'Standard'

### Form & Code Updates:
- ✅ Service type dropdown added to form
- ✅ Company address auto-fills from tbl_company
- ✅ Pickup date uses NOW() for automatic timestamp
- ✅ Trip group ID generated for grouping dockets
- ✅ All fields properly mapped to database columns

## Verification Query:
```sql
-- Check if all columns exist
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'tbl_shipping_details' 
AND COLUMN_NAME IN ('company_address', 'dimensions', 'service_type')
AND TABLE_SCHEMA = DATABASE();
```

## All Database Updates Completed! ✅
