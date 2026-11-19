-- OpsDesk 005: settle the hand balance (road-expense cash) through salary
-- Approved 2026-11-19. Changes OpsDesk tables only. Safe to re-run.
--
-- Rule (replaces the earlier "never through salary" rule):
--   At finalize, after the advance is recovered:
--     * hand balance > 0 (staff holds company cash): deduct it from pay, but only down to ₹0;
--       anything left stays in the hand balance and carries forward.
--     * hand balance < 0 (company owes the staff member): add it to pay.
--   A 'salary_settlement' entry is written to the hand ledger so the same cash is never settled twice.
--   tbl_ops_salary_slips.hand_settlement: + amount deducted, − amount added.
--   net_payable = earned + extra_duty_amount − advance_deduction − hand_settlement  (never below 0)

-- 1. Hand ledger: new entry type + link to the slip that settled it
ALTER TABLE tbl_ops_hand_ledger DROP CONSTRAINT IF EXISTS chk_ops_hand_type_dir;

ALTER TABLE tbl_ops_hand_ledger
    MODIFY COLUMN entry_type ENUM('given','expense','reversal','salary_settlement') NOT NULL,
    ADD COLUMN IF NOT EXISTS slip_id INT(11) NULL COMMENT 'set for salary_settlement entries' AFTER trip_id;

ALTER TABLE tbl_ops_hand_ledger ADD CONSTRAINT chk_ops_hand_type_dir CHECK (
    (entry_type = 'given' AND direction = 1) OR
    (entry_type = 'expense' AND direction = -1) OR
    (entry_type = 'reversal') OR
    (entry_type = 'salary_settlement' AND slip_id IS NOT NULL)
);

SET @c := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_ops_hand_ledger' AND CONSTRAINT_NAME = 'fk_ops_hand_slip');
SET @sql := IF(@c = 0,
    'ALTER TABLE tbl_ops_hand_ledger ADD CONSTRAINT fk_ops_hand_slip FOREIGN KEY (slip_id) REFERENCES tbl_ops_salary_slips (slip_id) ON DELETE RESTRICT',
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Slip: how much of the hand balance was settled
ALTER TABLE tbl_ops_salary_slips
    ADD COLUMN IF NOT EXISTS hand_settlement DECIMAL(12,2) NOT NULL DEFAULT 0.00
        COMMENT '+ cash in hand deducted, - amount owed to staff added' AFTER advance_deduction;
