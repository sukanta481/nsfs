-- OpsDesk 004: Day/Night shift on Present, and Extra Duty (EXD)
-- Approved 2026-11-18. ADD COLUMN only; nothing dropped. Safe to re-run.
--
-- Rules:
--   * present  -> shift is required: 'day' or 'night' (night pays the same as day)
--   * extra_duty (EXD) only on a present day, max one per day, and only the OTHER shift
--     (present day -> EXD night, present night -> EXD day). Each EXD pays one extra day:
--     salary / days_in_month.
--   * Slips keep earned_amount as the attendance pay; extra duty is stored separately.
--     gross = earned_amount + extra_duty_amount; net_payable = gross - advance_deduction.

ALTER TABLE tbl_ops_attendance
    ADD COLUMN IF NOT EXISTS shift ENUM('day','night') NULL AFTER status,
    ADD COLUMN IF NOT EXISTS extra_duty ENUM('day','night') NULL COMMENT 'EXD shift; only with present, opposite of shift' AFTER shift;

-- Present days marked before this change had no shift: treat them as Day.
UPDATE tbl_ops_attendance SET shift = 'day' WHERE status = 'present' AND shift IS NULL;
UPDATE tbl_ops_attendance SET shift = NULL, extra_duty = NULL WHERE status <> 'present' AND (shift IS NOT NULL OR extra_duty IS NOT NULL);

SET @c := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ops_attendance' AND CONSTRAINT_NAME = 'chk_ops_att_shift');
SET @sql := IF(@c = 0,
    'ALTER TABLE tbl_ops_attendance ADD CONSTRAINT chk_ops_att_shift CHECK ((status = ''present'') = (shift IS NOT NULL))',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ops_attendance' AND CONSTRAINT_NAME = 'chk_ops_att_extra');
SET @sql := IF(@c = 0,
    'ALTER TABLE tbl_ops_attendance ADD CONSTRAINT chk_ops_att_extra CHECK (extra_duty IS NULL OR (status = ''present'' AND extra_duty <> shift))',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

ALTER TABLE tbl_ops_salary_slips
    ADD COLUMN IF NOT EXISTS extra_duty_days TINYINT NOT NULL DEFAULT 0 AFTER payable_days,
    ADD COLUMN IF NOT EXISTS extra_duty_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'EXD pay; gross = earned_amount + this' AFTER earned_amount;
