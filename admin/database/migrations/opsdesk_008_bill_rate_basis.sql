-- OpsDesk 008: how each consignor-bill line was priced
-- 2026-11-19. Adds one column to an OpsDesk table. Safe to re-run.
--   kg     : amount = weight × rate   (used when the docket has a weight)
--   box    : amount = boxes × rate    (no weight)
--   docket : amount = rate            (bills made before per-box/per-kg pricing)

ALTER TABLE tbl_ops_bill_items
    ADD COLUMN IF NOT EXISTS rate_basis ENUM('docket','box','kg') NOT NULL DEFAULT 'box' AFTER consignor_invoice_no;

-- Older lines priced per docket (amount = rate) where that differs from boxes × rate
UPDATE tbl_ops_bill_items
SET rate_basis = 'docket'
WHERE rate_basis = 'box' AND amount = rate AND ROUND(rate * GREATEST(COALESCE(boxes, 1), 1), 2) <> amount;
