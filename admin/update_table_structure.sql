-- SQL queries to update tbl_shipping_details structure
-- Run these queries in phpMyAdmin or MySQL command line

-- 1. Add company_address column
ALTER TABLE `tbl_shipping_details` 
ADD COLUMN `company_address` TEXT NULL AFTER `company_email`;

-- 2. Add dimensions column
ALTER TABLE `tbl_shipping_details` 
ADD COLUMN `dimensions` VARCHAR(100) NULL AFTER `weight`;

-- 3. Modify pickup_dates to be automatically set to current timestamp (if needed)
-- Note: pickup_dates already exists as datetime, we'll just use it properly in code

-- Verify the changes
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'tbl_shipping_details' 
AND TABLE_SCHEMA = DATABASE()
ORDER BY ORDINAL_POSITION;
