-- OpsDesk 001: add columns to existing tables
-- Spec: opsdesk/docs/01-schema-proposal.md (B1). ADD COLUMN only, nothing dropped or renamed.
-- Safe to re-run (IF NOT EXISTS).

ALTER TABLE tbl_staff
    ADD COLUMN IF NOT EXISTS week_off_day TINYINT NULL DEFAULT 0 COMMENT '0=Sun..6=Sat, only pre-fills attendance; week off must still be marked';

ALTER TABLE tbl_car
    ADD COLUMN IF NOT EXISTS ownership ENUM('own','rented') NOT NULL DEFAULT 'own',
    ADD COLUMN IF NOT EXISTS default_vendor_id INT(11) NULL COMMENT 'tbl_ops_vendors; usual vendor, pre-fills rented trips',
    ADD COLUMN IF NOT EXISTS fuel_type VARCHAR(20) NULL,
    ADD INDEX IF NOT EXISTS idx_ownership (ownership);

-- Existing "External Vehicle" rows are rented vehicles
UPDATE tbl_car SET ownership = 'rented' WHERE car_details = 'External Vehicle';
