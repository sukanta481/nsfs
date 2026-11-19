<?php
/**
 * OpsDesk consignor billing, DB side. Maths in billing_calc.php.
 * A bill is a snapshot: amounts are never edited. Mistakes -> cancel with a reason.
 * A docket can be on only one active bill (tbl_ops_bill_items.active_docket_id is UNIQUE).
 */
require_once __DIR__ . '/billing_calc.php';

/** SQL expression for a docket's delivery moment */
const OPS_DELIVERED_AT = 'COALESCE(d.actual_delivery, d.delivery_datetime)';

function ops_bill_consignors()
{
    return ops_rows('SELECT company_id, company_title, company_address, company_phone, company_email, gstin FROM tbl_company ORDER BY company_title');
}

function ops_bill_consignor($companyId)
{
    return ops_one('SELECT company_id, company_title, company_address, company_phone, company_email, gstin FROM tbl_company WHERE company_id = ?', array($companyId));
}

/** Delivered dockets of a consignor in [from, to] that are not on an active bill (branch-scoped). */
function ops_bill_unbilled_dockets($companyId, $from, $to, array $onlyIds = null, $lock = false)
{
    $sql = 'SELECT d.docket_id, d.doc_no, DATE(' . OPS_DELIVERED_AT . ') AS delivered_on, d.client_name, d.delivery_location,
                   d.box, d.weight, d.invoice_no, d.office_id, d.branch_office
            FROM docket_details d
            LEFT JOIN tbl_ops_bill_items i ON i.active_docket_id = d.docket_id
            WHERE d.company_id = ? AND d.status = \'Delivered\'
              AND ' . OPS_DELIVERED_AT . ' >= ? AND ' . OPS_DELIVERED_AT . ' < DATE_ADD(?, INTERVAL 1 DAY)
              AND i.item_id IS NULL' . ops_office_scope('d');
    $args = array($companyId, $from . ' 00:00:00', $to);
    if ($onlyIds !== null) {
        if (!$onlyIds) return array();
        $sql .= ' AND d.docket_id IN (' . implode(',', array_fill(0, count($onlyIds), '?')) . ')';
        $args = array_merge($args, array_values($onlyIds));
    }
    $sql .= ' ORDER BY delivered_on, d.doc_no' . ($lock ? ' FOR UPDATE' : '');
    return ops_rows($sql, $args);
}

/** How many delivered dockets of this consignor in the range are already billed (for the info line). */
function ops_bill_already_billed_count($companyId, $from, $to)
{
    return (int)ops_value(
        'SELECT COUNT(*) FROM docket_details d JOIN tbl_ops_bill_items i ON i.active_docket_id = d.docket_id
         WHERE d.company_id = ? AND d.status = \'Delivered\'
           AND ' . OPS_DELIVERED_AT . ' >= ? AND ' . OPS_DELIVERED_AT . ' < DATE_ADD(?, INTERVAL 1 DAY)' . ops_office_scope('d'),
        array($companyId, $from . ' 00:00:00', $to)
    );
}

/**
 * Create a bill.
 * @param array $rates docket_id => rate in paise (only the ticked dockets)
 * @return int bill_id
 */
function ops_bill_create($companyId, $from, $to, $billDate, array $rates, $gstRate, $taxType, $gstin, $note)
{
    $co = ops_bill_consignor($companyId);
    if (!$co) throw new InvalidArgumentException('Choose a consignor.');
    if (!$from || !$to || $from > $to) throw new InvalidArgumentException('Choose a valid date range.');
    if (!$billDate || $billDate > date('Y-m-d')) throw new InvalidArgumentException('Bill date cannot be in the future.');
    if (!$rates) throw new InvalidArgumentException('Tick at least one docket.');
    foreach ($rates as $id => $p) {
        if ($p === null || $p <= 0) throw new InvalidArgumentException('Enter a rate for every ticked docket (or untick it).');
    }
    $gstin = strtoupper(trim((string)$gstin));
    if (!ops_valid_gstin($gstin)) throw new InvalidArgumentException('GSTIN looks wrong. It should be 15 characters, like 19ABCDE1234F1Z5.');
    $totals = ops_bill_totals(array_values($rates), $gstRate, $taxType);

    ops_begin();
    try {
        // Re-read the dockets inside the transaction: still delivered, in range, this consignor, not billed.
        $dockets = ops_bill_unbilled_dockets($companyId, $from, $to, array_keys($rates), true);
        if (count($dockets) !== count($rates)) {
            throw new InvalidArgumentException('Some dockets are no longer available (already billed by someone else, or changed). Reload and try again.');
        }
        $fy = ops_fin_year($billDate);
        $seq = (int)ops_value('SELECT COALESCE(MAX(seq), 0) FROM tbl_ops_bills WHERE fy = ? FOR UPDATE', array($fy)) + 1;
        $billId = ops_write(
            'INSERT INTO tbl_ops_bills (bill_no, fy, seq, company_id, company_name, company_address, company_phone, company_email, company_gstin,
                 period_from, period_to, bill_date, docket_count, subtotal, gst_rate, tax_type, cgst, sgst, igst, round_off, total,
                 note, created_by, created_by_name)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            array(ops_bill_number($fy, $seq), $fy, $seq, $companyId, $co['company_title'], $co['company_address'], $co['company_phone'],
                  $co['company_email'], $gstin !== '' ? $gstin : null, $from, $to, $billDate, count($dockets),
                  ops_paise_to_db($totals['subtotal']), $totals['gst_rate'], $totals['tax_type'], ops_paise_to_db($totals['cgst']),
                  ops_paise_to_db($totals['sgst']), ops_paise_to_db($totals['igst']), ops_paise_to_db($totals['round_off']),
                  ops_paise_to_db($totals['total']), $note !== '' ? $note : null, ops_user_id(), ops_user_name())
        );
        foreach ($dockets as $d) {
            $rate = (int)$rates[(int)$d['docket_id']];
            ops_write(
                'INSERT INTO tbl_ops_bill_items (bill_id, docket_id, active_docket_id, doc_no, delivery_date, consignee_name, destination,
                     boxes, weight, consignor_invoice_no, rate, amount)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                array($billId, $d['docket_id'], $d['docket_id'], $d['doc_no'], $d['delivered_on'], $d['client_name'],
                      mb_substr((string)$d['delivery_location'], 0, 255), $d['box'], $d['weight'], $d['invoice_no'],
                      ops_paise_to_db($rate), ops_paise_to_db($rate))
            );
        }
        // remember the consignor's GSTIN for next time
        if ($gstin !== '' && $gstin !== strtoupper((string)$co['gstin'])) {
            ops_write('UPDATE tbl_company SET gstin = ? WHERE company_id = ?', array($gstin, $companyId));
        }
        ops_commit();
        return $billId;
    } catch (Exception $e) {
        ops_rollback();
        if (strpos($e->getMessage(), 'uq_active_docket') !== false || strpos($e->getMessage(), 'Duplicate entry') !== false) {
            throw new InvalidArgumentException('One of these dockets was just billed by someone else. Reload and try again.');
        }
        throw $e;
    }
}

function ops_bill_get($billId)
{
    return ops_one('SELECT * FROM tbl_ops_bills WHERE bill_id = ?', array($billId));
}

function ops_bill_items($billId)
{
    return ops_rows('SELECT * FROM tbl_ops_bill_items WHERE bill_id = ? ORDER BY delivery_date, doc_no, item_id', array($billId));
}

function ops_bill_payments($billId)
{
    return ops_rows(
        'SELECT p.*, (SELECT COUNT(*) FROM tbl_ops_bill_payments x WHERE x.reversal_of_id = p.payment_id) AS is_reversed
         FROM tbl_ops_bill_payments p WHERE p.bill_id = ? ORDER BY p.pay_date, p.payment_id', array($billId)
    );
}

/** Settled amounts for a bill (reversals subtract): ['received'=>, 'tds'=>, 'settled'=>] in paise */
function ops_bill_paid($billId)
{
    $r = ops_one(
        "SELECT COALESCE(SUM(CASE WHEN entry_type = 'payment' THEN amount ELSE -amount END), 0) AS received,
                COALESCE(SUM(CASE WHEN entry_type = 'payment' THEN tds_amount ELSE -tds_amount END), 0) AS tds
         FROM tbl_ops_bill_payments WHERE bill_id = ?", array($billId)
    );
    $rec = ops_paise_from_db($r['received']);
    $tds = ops_paise_from_db($r['tds']);
    return array('received' => $rec, 'tds' => $tds, 'settled' => $rec + $tds);
}

function ops_bill_status_label(array $bill, $settled)
{
    if ($bill['status'] === 'cancelled') return array('Cancelled', 'grey');
    $total = ops_paise_from_db($bill['total']);
    if ($settled <= 0) return array('Unpaid', 'draft');
    if ($settled >= $total) return array('Paid', 'final');
    return array('Partly paid', 'blue');
}

function ops_bill_cancel($billId, $reason)
{
    $reason = trim((string)$reason);
    if ($reason === '') throw new InvalidArgumentException('Please give a reason for cancelling.');
    ops_begin();
    try {
        $b = ops_one('SELECT * FROM tbl_ops_bills WHERE bill_id = ? FOR UPDATE', array($billId));
        if (!$b) throw new InvalidArgumentException('Bill not found.');
        if ($b['status'] === 'cancelled') throw new InvalidArgumentException('This bill is already cancelled.');
        if (ops_bill_paid($billId)['settled'] > 0) {
            throw new InvalidArgumentException('This bill has payments. Reverse the payments first, then cancel.');
        }
        ops_write(
            "UPDATE tbl_ops_bills SET status = 'cancelled', cancel_reason = ?, cancelled_by = ?, cancelled_by_name = ?, cancelled_at = NOW()
             WHERE bill_id = ?",
            array(mb_substr($reason, 0, 255), ops_user_id(), ops_user_name(), $billId)
        );
        ops_write('UPDATE tbl_ops_bill_items SET active_docket_id = NULL WHERE bill_id = ?', array($billId)); // dockets can be billed again
        ops_commit();
    } catch (Exception $e) {
        ops_rollback();
        throw $e;
    }
}

const OPS_PAY_MODES = array('cash' => 'Cash', 'bank' => 'Bank transfer', 'upi' => 'UPI', 'cheque' => 'Cheque', 'other' => 'Other');

function ops_bill_pay($billId, $date, $amountPaise, $tdsPaise, $mode, $reference, $note)
{
    if (!isset(OPS_PAY_MODES[$mode])) throw new InvalidArgumentException('Choose a payment mode.');
    if (!$date || $date > date('Y-m-d')) throw new InvalidArgumentException('Payment date cannot be in the future.');
    $amountPaise = (int)$amountPaise;
    $tdsPaise = (int)$tdsPaise;
    if ($amountPaise < 0 || $tdsPaise < 0 || $amountPaise + $tdsPaise <= 0) throw new InvalidArgumentException('Enter the amount received.');
    ops_begin();
    try {
        $b = ops_one('SELECT * FROM tbl_ops_bills WHERE bill_id = ? FOR UPDATE', array($billId));
        if (!$b) throw new InvalidArgumentException('Bill not found.');
        if ($b['status'] === 'cancelled') throw new InvalidArgumentException('This bill is cancelled.');
        $due = ops_paise_from_db($b['total']) - ops_bill_paid($billId)['settled'];
        if ($amountPaise + $tdsPaise > $due) {
            throw new InvalidArgumentException('That is more than the balance due (' . ops_inr($due) . ').');
        }
        $id = ops_write(
            "INSERT INTO tbl_ops_bill_payments (bill_id, company_id, pay_date, entry_type, amount, tds_amount, mode, reference, note, created_by, created_by_name)
             VALUES (?, ?, ?, 'payment', ?, ?, ?, ?, ?, ?, ?)",
            array($billId, $b['company_id'], $date, ops_paise_to_db($amountPaise), ops_paise_to_db($tdsPaise), $mode,
                  $reference !== '' ? $reference : null, $note !== '' ? $note : null, ops_user_id(), ops_user_name())
        );
        ops_commit();
        return $id;
    } catch (Exception $e) {
        ops_rollback();
        throw $e;
    }
}

function ops_bill_pay_reverse($paymentId, $reason)
{
    $reason = trim((string)$reason);
    if ($reason === '') throw new InvalidArgumentException('Please give a reason for the reversal.');
    $o = ops_one('SELECT * FROM tbl_ops_bill_payments WHERE payment_id = ?', array($paymentId));
    if (!$o) throw new InvalidArgumentException('Payment not found.');
    if ($o['entry_type'] !== 'payment') throw new InvalidArgumentException('A reversal cannot be reversed.');
    if (ops_value('SELECT COUNT(*) FROM tbl_ops_bill_payments WHERE reversal_of_id = ?', array($paymentId))) {
        throw new InvalidArgumentException('This payment is already reversed.');
    }
    return ops_write(
        "INSERT INTO tbl_ops_bill_payments (bill_id, company_id, pay_date, entry_type, amount, tds_amount, mode, reference, note, reversal_of_id, created_by, created_by_name)
         VALUES (?, ?, ?, 'reversal', ?, ?, ?, ?, ?, ?, ?, ?)",
        array($o['bill_id'], $o['company_id'], date('Y-m-d'), $o['amount'], $o['tds_amount'], $o['mode'], $o['reference'],
              mb_substr($reason, 0, 255), $paymentId, ops_user_id(), ops_user_name())
    );
}

/** Bills list with paid amounts. $f: company_id, status (issued|cancelled|unpaid|paid|partly), from, to */
function ops_bill_list(array $f = array())
{
    $where = array('1=1');
    $args = array();
    if (!empty($f['company_id'])) { $where[] = 'b.company_id = ?'; $args[] = (int)$f['company_id']; }
    if (!empty($f['from'])) { $where[] = 'b.bill_date >= ?'; $args[] = $f['from']; }
    if (!empty($f['to'])) { $where[] = 'b.bill_date <= ?'; $args[] = $f['to']; }
    if (!empty($f['q'])) { $where[] = '(b.bill_no LIKE ? OR b.company_name LIKE ?)'; $args[] = '%' . $f['q'] . '%'; $args[] = '%' . $f['q'] . '%'; }
    $rows = ops_rows(
        "SELECT b.*,
                COALESCE((SELECT SUM(CASE WHEN p.entry_type = 'payment' THEN p.amount + p.tds_amount ELSE -(p.amount + p.tds_amount) END)
                          FROM tbl_ops_bill_payments p WHERE p.bill_id = b.bill_id), 0) AS settled
         FROM tbl_ops_bills b WHERE " . implode(' AND ', $where) . ' ORDER BY b.bill_date DESC, b.bill_id DESC LIMIT 500',
        $args
    );
    $out = array();
    foreach ($rows as $r) {
        $settled = ops_paise_from_db($r['settled']);
        $r['settled_paise'] = $settled;
        $r['total_paise'] = ops_paise_from_db($r['total']);
        $r['due_paise'] = $r['status'] === 'cancelled' ? 0 : $r['total_paise'] - $settled;
        list($r['status_label'], $r['status_class']) = ops_bill_status_label($r, $settled);
        $st = isset($f['status']) ? $f['status'] : '';
        if ($st === 'cancelled' && $r['status'] !== 'cancelled') continue;
        if ($st === 'issued' && $r['status'] !== 'issued') continue;
        if ($st === 'unpaid' && $r['status_label'] !== 'Unpaid') continue;
        if ($st === 'partly' && $r['status_label'] !== 'Partly paid') continue;
        if ($st === 'paid' && $r['status_label'] !== 'Paid') continue;
        $out[] = $r;
    }
    return $out;
}

/** Outstanding per consignor (issued bills only): company_id => [name, bills, total, settled, due] */
function ops_bill_outstanding()
{
    $rows = ops_rows(
        "SELECT b.company_id, MAX(b.company_name) AS company_name, COUNT(*) AS bills, SUM(b.total) AS total,
                COALESCE(SUM((SELECT SUM(CASE WHEN p.entry_type = 'payment' THEN p.amount + p.tds_amount ELSE -(p.amount + p.tds_amount) END)
                              FROM tbl_ops_bill_payments p WHERE p.bill_id = b.bill_id)), 0) AS settled
         FROM tbl_ops_bills b WHERE b.status = 'issued' GROUP BY b.company_id"
    );
    $out = array();
    foreach ($rows as $r) {
        $t = ops_paise_from_db($r['total']);
        $s = ops_paise_from_db($r['settled']);
        if ($t - $s <= 0) continue;
        $out[] = array('company_id' => (int)$r['company_id'], 'name' => $r['company_name'], 'bills' => (int)$r['bills'],
                       'total' => $t, 'settled' => $s, 'due' => $t - $s);
    }
    usort($out, function ($a, $b) { return $b['due'] - $a['due']; });
    return $out;
}
