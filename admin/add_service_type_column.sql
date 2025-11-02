-- SQL queries to add service_type column to tbl_shipping_details
-- Run this query in phpMyAdmin or MySQL command line

-- Add service_type column
ALTER TABLE `tbl_shipping_details` 
ADD COLUMN `service_type` VARCHAR(50) NULL DEFAULT 'Standard' AFTER `doc_type`;

-- Verify the change
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'tbl_shipping_details' 
AND COLUMN_NAME = 'service_type'
AND TABLE_SCHEMA = DATABASE();
