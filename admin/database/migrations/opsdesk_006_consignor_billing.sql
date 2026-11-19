-- OpsDesk 006: Consignor billing (bill delivered dockets to the consignor, track payments)
-- Approved 2026-11-19, see opsdesk/docs/03-consignor-billing-proposal.md
-- ADD only. The existing tbl_company only gets a new nullable column. Safe to re-run.

-- 1. Existing table: consignor GSTIN (printed on bills)
ALTER TABLE tbl_company
    ADD COLUMN IF NOT EXISTS gstin VARCHAR(20) NULL COMMENT 'OpsDesk billing: consignor GSTIN';

-- 2. Bills (one per bill; amounts are a snapshot and never edited; mistakes = cancel with reason)
CREATE TABLE IF NOT EXISTS tbl_ops_bills (
    bill_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    bill_no VARCHAR(30) NOT NULL,
    fy CHAR(7) NOT NULL COMMENT 'financial year, e.g. 2026-27',
    seq INT(11) NOT NULL COMMENT 'running number inside the financial year',
    company_id INT(11) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    company_address VARCHAR(255) NULL,
    company_phone VARCHAR(255) NULL,
    company_email VARCHAR(255) NULL,
    company_gstin VARCHAR(20) NULL,
    period_from DATE NOT NULL,
    period_to DATE NOT NULL,
    bill_date DATE NOT NULL,
    docket_count INT(11) NOT NULL DEFAULT 0,
    subtotal DECIMAL(12,2) NOT NULL,
    gst_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    tax_type ENUM('none','cgst_sgst','igst') NOT NULL DEFAULT 'none',
    cgst DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    sgst DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    igst DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    round_off DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL,
    status ENUM('issued','cancelled') NOT NULL DEFAULT 'issued',
    cancel_reason VARCHAR(255) NULL,
    cancelled_by INT(11) NULL,
    cancelled_by_name VARCHAR(255) NULL,
    cancelled_at DATETIME NULL,
    note VARCHAR(255) NULL,
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_bill_no (bill_no),
    UNIQUE KEY uq_fy_seq (fy, seq),
    KEY idx_company (company_id, bill_date),
    KEY idx_status (status),
    CONSTRAINT chk_ops_bill_period CHECK (period_to >= period_from),
    CONSTRAINT chk_ops_bill_total CHECK (total >= 0 AND subtotal >= 0),
    CONSTRAINT chk_ops_bill_cancel CHECK ((status = 'cancelled') = (cancel_reason IS NOT NULL)),
    CONSTRAINT fk_ops_bill_company FOREIGN KEY (company_id) REFERENCES tbl_company (company_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bill lines (one per docket). active_docket_id is UNIQUE: a docket can be on only one active bill.
--    When a bill is cancelled, active_docket_id is set to NULL so the docket can be billed again.
CREATE TABLE IF NOT EXISTS tbl_ops_bill_items (
    item_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    bill_id INT(11) NOT NULL,
    docket_id INT(11) NOT NULL,
    active_docket_id INT(11) NULL,
    doc_no VARCHAR(255) NOT NULL,
    delivery_date DATE NULL,
    consignee_name VARCHAR(255) NULL,
    destination VARCHAR(255) NULL,
    boxes INT(11) NULL,
    weight DECIMAL(10,2) NULL,
    consignor_invoice_no VARCHAR(100) NULL,
    rate DECIMAL(12,2) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    UNIQUE KEY uq_active_docket (active_docket_id),
    KEY idx_bill (bill_id),
    KEY idx_docket (docket_id),
    CONSTRAINT chk_ops_bill_item_amount CHECK (rate >= 0 AND amount >= 0),
    CONSTRAINT fk_ops_bill_item_bill FOREIGN KEY (bill_id) REFERENCES tbl_ops_bills (bill_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_bill_item_docket FOREIGN KEY (docket_id) REFERENCES docket_details (docket_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Payments received against a bill. APPEND-ONLY; corrections are reversal rows.
CREATE TABLE IF NOT EXISTS tbl_ops_bill_payments (
    payment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    bill_id INT(11) NOT NULL,
    company_id INT(11) NOT NULL,
    pay_date DATE NOT NULL,
    entry_type ENUM('payment','reversal') NOT NULL DEFAULT 'payment',
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'money received',
    tds_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'TDS deducted by the consignor; counts towards the bill',
    mode ENUM('cash','bank','upi','cheque','other') NULL,
    reference VARCHAR(100) NULL,
    note VARCHAR(255) NULL,
    reversal_of_id INT(11) NULL,
    created_by INT(11) NULL,
    created_by_name VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_bill (bill_id),
    KEY idx_company (company_id, pay_date),
    UNIQUE KEY uq_bill_pay_reversal_of (reversal_of_id),
    CONSTRAINT chk_ops_bill_pay_amount CHECK (amount >= 0 AND tds_amount >= 0 AND amount + tds_amount > 0),
    CONSTRAINT chk_ops_bill_pay_reversal CHECK ((entry_type = 'reversal') = (reversal_of_id IS NOT NULL)),
    CONSTRAINT fk_ops_bill_pay_bill FOREIGN KEY (bill_id) REFERENCES tbl_ops_bills (bill_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_bill_pay_company FOREIGN KEY (company_id) REFERENCES tbl_company (company_id) ON DELETE RESTRICT,
    CONSTRAINT fk_ops_bill_pay_reversal FOREIGN KEY (reversal_of_id) REFERENCES tbl_ops_bill_payments (payment_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Permissions (module OpsDesk), granted to Super Admin (role 1). Other roles: User Management > Roles.
INSERT IGNORE INTO tbl_permissions (permission_key, permission_name, module_name, permission_description) VALUES
    ('ops_billing_view',    'View Consignor Bills',      'OpsDesk', 'See consignor bills and outstanding'),
    ('ops_billing_manage',  'Create / Cancel Bills',     'OpsDesk', 'Generate bills for delivered dockets and cancel bills'),
    ('ops_billing_payment', 'Record Bill Payments',      'OpsDesk', 'Record and reverse payments received against bills');

INSERT IGNORE INTO tbl_role_permissions (role_id, permission_id)
SELECT 1, permission_id FROM tbl_permissions WHERE permission_key IN ('ops_billing_view', 'ops_billing_manage', 'ops_billing_payment');
