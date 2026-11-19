-- OpsDesk 009: Reopen a finalized (unpaid) salary slip
-- Approved 2026-11-20. Changes OpsDesk tables only. Safe to re-run.
--
-- Reopen = the slip is marked CANCELLED with a reason (the row stays, never deleted),
-- the staff member's attendance for that month becomes editable again, and a new
-- slip can be finalized. Only unpaid slips can be reopened (Undo paid first), and not
-- while a later month is finalized for that staff member.
-- One ACTIVE slip per staff per run: active_run_id = run_id while active, NULL once cancelled.

ALTER TABLE tbl_ops_salary_slips
    ADD COLUMN IF NOT EXISTS status ENUM('final','cancelled') NOT NULL DEFAULT 'final' AFTER hand_balance_at_close,
    ADD COLUMN IF NOT EXISTS active_run_id INT(11) NULL COMMENT '= run_id while the slip is active; NULL once cancelled' AFTER run_id,
    ADD COLUMN IF NOT EXISTS cancel_reason VARCHAR(255) NULL AFTER status,
    ADD COLUMN IF NOT EXISTS cancelled_by INT(11) NULL AFTER cancel_reason,
    ADD COLUMN IF NOT EXISTS cancelled_by_name VARCHAR(255) NULL AFTER cancelled_by,
    ADD COLUMN IF NOT EXISTS cancelled_at DATETIME NULL AFTER cancelled_by_name,
    ADD INDEX IF NOT EXISTS idx_run (run_id);

UPDATE tbl_ops_salary_slips SET active_run_id = run_id WHERE status = 'final' AND active_run_id IS NULL;

-- swap the old "one slip per staff per run" key for "one ACTIVE slip per staff per run"
ALTER TABLE tbl_ops_salary_slips DROP INDEX IF EXISTS uq_run_staff;
ALTER TABLE tbl_ops_salary_slips ADD UNIQUE INDEX IF NOT EXISTS uq_active_run_staff (active_run_id, staff_id);

SET @c := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ops_salary_slips' AND CONSTRAINT_NAME = 'chk_ops_slip_cancel');
SET @sql := IF(@c = 0,
    'ALTER TABLE tbl_ops_salary_slips ADD CONSTRAINT chk_ops_slip_cancel CHECK ((status = ''cancelled'') = (cancel_reason IS NOT NULL) AND (status = ''final'') = (active_run_id IS NOT NULL))',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Permission: Admin and Super Admin
INSERT IGNORE INTO tbl_permissions (permission_key, permission_name, module_name, permission_description) VALUES
    ('ops_payroll_reopen', 'Reopen Salary Slips', 'OpsDesk', 'Reopen a finalized, unpaid salary slip so the month''s attendance can be corrected');

INSERT IGNORE INTO tbl_role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id FROM tbl_roles r JOIN tbl_permissions p ON p.permission_key = 'ops_payroll_reopen'
WHERE r.role_id = 1 OR r.role_name IN ('Super Admin', 'Admin');
