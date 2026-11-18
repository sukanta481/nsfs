-- OpsDesk rollback: removes ONLY what opsdesk_001..004 added.
-- For local/test use. On live this destroys OpsDesk data (attendance, ledgers, payroll), so take a backup first.

ALTER TABLE tbl_car DROP FOREIGN KEY IF EXISTS fk_ops_car_default_vendor;

-- (004 columns live on OpsDesk tables, so dropping the tables below removes them too.)

DROP TABLE IF EXISTS
    tbl_ops_activity_log,
    tbl_ops_hand_ledger,
    tbl_ops_rental_ledger,
    tbl_ops_vehicle_expenses,
    tbl_ops_trip_crew,
    tbl_ops_vehicle_trips,
    tbl_ops_vendors,
    tbl_ops_staff_advances,
    tbl_ops_salary_slips,
    tbl_ops_payroll_runs,
    tbl_ops_salary_revisions,
    tbl_ops_attendance,
    tbl_ops_staff_types;

ALTER TABLE tbl_car
    DROP INDEX IF EXISTS idx_ownership,
    DROP COLUMN IF EXISTS ownership,
    DROP COLUMN IF EXISTS default_vendor_id,
    DROP COLUMN IF EXISTS fuel_type;

ALTER TABLE tbl_staff DROP COLUMN IF EXISTS week_off_day;

DELETE rp FROM tbl_role_permissions rp
JOIN tbl_permissions p ON p.permission_id = rp.permission_id
WHERE p.module_name = 'OpsDesk';
DELETE FROM tbl_permissions WHERE module_name = 'OpsDesk';
