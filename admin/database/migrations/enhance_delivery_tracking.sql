-- Enhanced Delivery History with Detailed Notes
-- Add columns to track comprehensive delivery information

-- Add columns to docket_details for creator and office info
ALTER TABLE `docket_details`
ADD COLUMN IF NOT EXISTS `created_by_name` VARCHAR(255) DEFAULT NULL COMMENT 'Name of user who created the docket',
ADD COLUMN IF NOT EXISTS `pickup_office_id` INT(11) DEFAULT NULL COMMENT 'Office where docket was picked up',
ADD COLUMN IF NOT EXISTS `pickup_office_name` VARCHAR(255) DEFAULT NULL COMMENT 'Name of pickup office',
ADD COLUMN IF NOT EXISTS `manifest_id` INT(11) DEFAULT NULL COMMENT 'Manifest ID if transferred to branch',
ADD COLUMN IF NOT EXISTS `manifest_office_id` INT(11) DEFAULT NULL COMMENT 'Destination office ID for manifest',
ADD COLUMN IF NOT EXISTS `manifest_office_name` VARCHAR(255) DEFAULT NULL COMMENT 'Destination office name',
ADD COLUMN IF NOT EXISTS `manifest_office_phone` VARCHAR(50) DEFAULT NULL COMMENT 'Destination office phone',
ADD COLUMN IF NOT EXISTS `delivery_office_id` INT(11) DEFAULT NULL COMMENT 'Office handling delivery',
ADD COLUMN IF NOT EXISTS `delivery_office_name` VARCHAR(255) DEFAULT NULL COMMENT 'Name of delivery office',
ADD COLUMN IF NOT EXISTS `driver_phone` VARCHAR(50) DEFAULT NULL COMMENT 'Driver contact number';

-- Enhance docket_status_history with more detailed tracking
ALTER TABLE `docket_status_history`
ADD COLUMN IF NOT EXISTS `office_id` INT(11) DEFAULT NULL COMMENT 'Office ID where status was updated',
ADD COLUMN IF NOT EXISTS `office_name` VARCHAR(255) DEFAULT NULL COMMENT 'Office name',
ADD COLUMN IF NOT EXISTS `office_phone` VARCHAR(50) DEFAULT NULL COMMENT 'Office contact number',
ADD COLUMN IF NOT EXISTS `manifest_id` INT(11) DEFAULT NULL COMMENT 'Manifest ID for transfers',
ADD COLUMN IF NOT EXISTS `manifest_no` VARCHAR(100) DEFAULT NULL COMMENT 'Manifest number',
ADD COLUMN IF NOT EXISTS `from_office` VARCHAR(255) DEFAULT NULL COMMENT 'Source office for transfer',
ADD COLUMN IF NOT EXISTS `to_office` VARCHAR(255) DEFAULT NULL COMMENT 'Destination office for transfer',
ADD COLUMN IF NOT EXISTS `driver_phone` VARCHAR(50) DEFAULT NULL COMMENT 'Driver contact number',
ADD COLUMN IF NOT EXISTS `is_delayed` TINYINT(1) DEFAULT 0 COMMENT 'Is this status delayed',
ADD COLUMN IF NOT EXISTS `is_cancelled` TINYINT(1) DEFAULT 0 COMMENT 'Is this status cancelled';

-- Add indexes
ALTER TABLE `docket_details`
ADD INDEX IF NOT EXISTS `idx_pickup_office` (`pickup_office_id`),
ADD INDEX IF NOT EXISTS `idx_manifest_id` (`manifest_id`),
ADD INDEX IF NOT EXISTS `idx_delivery_office` (`delivery_office_id`);

ALTER TABLE `docket_status_history`
ADD INDEX IF NOT EXISTS `idx_office_id` (`office_id`),
ADD INDEX IF NOT EXISTS `idx_manifest_id` (`manifest_id`),
ADD INDEX IF NOT EXISTS `idx_is_delayed` (`is_delayed`),
ADD INDEX IF NOT EXISTS `idx_is_cancelled` (`is_cancelled`);
