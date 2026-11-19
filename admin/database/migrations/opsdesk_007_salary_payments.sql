-- OpsDesk 007: "Paid / Settle" step for salary slips
-- Approved 2026-11-20. New table only (+ marks old slips as paid). Safe to re-run.
--
-- Flow:
--   Finalize  -> slip amounts and attendance are locked, slip is printed. Ledgers are NOT touched yet.
--   Mark paid -> one payment row (full net pay); the advance recovery and the hand-balance
--                salary_settlement entries are written now (dated the slip's month end so later
--                months see them). A staff member's next month cannot be finalized until this is paid.
--   Undo paid -> reason required; a reversal payment row plus reversal rows for the advance recovery
--                and hand settlement (append-only). Not allowed once a later month is finalized.

CREATE TABLE IF NOT EXISTS tbl_ops_salary_payments (
    payment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    slip_id INT(11) NOT NULL,
    active_slip_id INT(11) NULL COMMENT '= slip_id while this payment stands; NULL once reversed (one active payment per slip)',
    staff_id INT(11) NOT NULL,
    pay_month DATE NOT NULL,
    entry_type ENUM('payment','reversal') NOT NULL DEFAULT 'payment',
    pay_date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL COMMENT 'net pay handed over',
    mode ENUM('cash','bank','upi','cheque','other') NULL,
    reference VARCHAR(100) NULL,
    note VARCHAR(255) NULL,
    reversal_of_id INT(11) NULL,
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_active_slip (active_slip_id),
    UNIQUE KEY uq_sal_pay_reversal_of (reversal_of_id),
    KEY idx_slip (slip_id),
    KEY idx_staff_month (staff_id, pay_month),
    CONSTRAINT chk_ops_sal_pay_amount CHECK (amount >= 0),
    CONSTRAINT chk_ops_sal_pay_reversal CHECK ((entry_type = 'reversal') = (reversal_of_id IS NOT NULL)),
    CONSTRAINT fk_ops_sal_pay_slip FOREIGN KEY (slip_id) REFERENCES tbl_ops_salary_slips (slip_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_sal_pay_staff FOREIGN KEY (staff_id) REFERENCES tbl_staff (staff_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_sal_pay_reversal FOREIGN KEY (reversal_of_id) REFERENCES tbl_ops_salary_payments (payment_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Slips finalized before this step existed already had their advance recovery / hand settlement
-- written at finalize, so they are recorded as paid (on the day they were finalized).
INSERT INTO tbl_ops_salary_payments (slip_id, active_slip_id, staff_id, pay_month, entry_type, pay_date, amount, note, created_by_name)
SELECT s.slip_id, s.slip_id, s.staff_id, s.pay_month, 'payment', DATE(s.created_at), s.net_payable,
       'Recorded as paid automatically: finalized before the Paid step existed', 'system'
FROM tbl_ops_salary_slips s
WHERE NOT EXISTS (SELECT 1 FROM tbl_ops_salary_payments p WHERE p.slip_id = s.slip_id);
