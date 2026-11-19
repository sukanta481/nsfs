<?php
/**
 * OpsDesk append-only ledgers: salary advances and staff hand balance.
 * Rows are never UPDATEd or DELETEd. A mistake is corrected by a 'reversal'
 * row pointing at the original (reversal_of_id is UNIQUE, so it can only be reversed once).
 */

/* ---------------- salary advances ---------------- */

/**
 * Outstanding advance in paise (advances − recoveries, reversals undo their original).
 * $upTo 'Y-m-d' limits to entries on or before that date (used by payroll for month end).
 */
function ops_advance_outstanding($staffId, $upTo = null)
{
    $sql = "SELECT a.entry_type, a.amount, o.entry_type AS orig_type
            FROM tbl_ops_staff_advances a
            LEFT JOIN tbl_ops_staff_advances o ON o.id = a.reversal_of_id
            WHERE a.staff_id = ?" . ($upTo ? " AND a.entry_date <= ?" : '');
    $params = $upTo ? array($staffId, $upTo) : array($staffId);
    $total = 0;
    foreach (ops_rows($sql, $params) as $r) {
        $total += ops_advance_sign($r['entry_type'], $r['orig_type']) * ops_paise_from_db($r['amount']);
    }
    return $total;
}

/** +1 raises what the staff member owes, −1 lowers it. */
function ops_advance_sign($type, $origType)
{
    if ($type === 'advance') return 1;
    if ($type === 'recovery') return -1;
    return $origType === 'advance' ? -1 : 1; // reversal
}

function ops_advance_add($staffId, $date, $paise, $note)
{
    if ($paise <= 0) throw new InvalidArgumentException('Amount must be more than zero.');
    if (ops_staff_locked($staffId, $date)) throw new InvalidArgumentException('Payroll for that month is already finalized for this staff member. Use a date in an open month.');
    return ops_write(
        "INSERT INTO tbl_ops_staff_advances (staff_id, entry_date, entry_type, amount, note, created_by, created_by_name)
         VALUES (?, ?, 'advance', ?, ?, ?, ?)",
        array($staffId, $date, ops_paise_to_db($paise), $note, ops_user_id(), ops_user_name())
    );
}

function ops_advance_reverse($id, $reason)
{
    $o = ops_one('SELECT * FROM tbl_ops_staff_advances WHERE id = ?', array($id));
    if (!$o) throw new InvalidArgumentException('Entry not found.');
    if ($o['entry_type'] !== 'advance') throw new InvalidArgumentException('Only an advance can be reversed here. Payroll recoveries are fixed once the month is finalized.');
    if (ops_value('SELECT COUNT(*) FROM tbl_ops_staff_advances WHERE reversal_of_id = ?', array($id))) {
        throw new InvalidArgumentException('This entry is already reversed.');
    }
    if (trim($reason) === '') throw new InvalidArgumentException('Please give a reason for the reversal.');
    return ops_write(
        "INSERT INTO tbl_ops_staff_advances (staff_id, entry_date, entry_type, amount, note, reversal_of_id, created_by, created_by_name)
         VALUES (?, ?, 'reversal', ?, ?, ?, ?, ?)",
        array($o['staff_id'], date('Y-m-d'), $o['amount'], $reason, $id, ops_user_id(), ops_user_name())
    );
}

/* ---------------- hand balance (road-expense cash) ---------------- */

/** Positive = staff holds company cash; negative = company owes staff. */
function ops_hand_balance($staffId, $upTo = null)
{
    $sql = 'SELECT COALESCE(SUM(direction * amount), 0) FROM tbl_ops_hand_ledger WHERE staff_id = ?' . ($upTo ? ' AND entry_date <= ?' : '');
    return ops_paise_from_db(ops_value($sql, $upTo ? array($staffId, $upTo) : array($staffId)));
}

/** All non-zero balances: staff_id => paise (scoped to the user's branch) */
function ops_hand_balances_all()
{
    $rows = ops_rows(
        'SELECT h.staff_id, SUM(h.direction * h.amount) AS bal
         FROM tbl_ops_hand_ledger h JOIN tbl_staff s ON s.staff_id = h.staff_id
         WHERE 1=1 ' . ops_office_scope('s') . '
         GROUP BY h.staff_id HAVING bal <> 0'
    );
    $out = array();
    foreach ($rows as $r) $out[(int)$r['staff_id']] = ops_paise_from_db($r['bal']);
    return $out;
}

const OPS_HAND_CATEGORIES = array('Toll', 'Petrol', 'Diesel', 'Repair', 'Parking', 'Food', 'Loading', 'Other');

function ops_hand_add($staffId, $type, $date, $paise, $category, $note)
{
    if (!in_array($type, array('given', 'expense'), true)) throw new InvalidArgumentException('Unknown entry type.');
    if ($paise <= 0) throw new InvalidArgumentException('Amount must be more than zero.');
    if ($date > date('Y-m-d')) throw new InvalidArgumentException('Date cannot be in the future.');
    if ($type === 'expense' && !in_array($category, OPS_HAND_CATEGORIES, true)) throw new InvalidArgumentException('Choose a category for the expense.');
    $dir = $type === 'given' ? 1 : -1;
    return ops_write(
        'INSERT INTO tbl_ops_hand_ledger (staff_id, entry_date, entry_type, amount, direction, category, note, created_by, created_by_name)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        array($staffId, $date, $type, ops_paise_to_db($paise), $dir, $type === 'expense' ? $category : null, $note, ops_user_id(), ops_user_name())
    );
}

function ops_hand_reverse($id, $reason)
{
    $o = ops_one('SELECT * FROM tbl_ops_hand_ledger WHERE id = ?', array($id));
    if (!$o) throw new InvalidArgumentException('Entry not found.');
    if ($o['entry_type'] === 'reversal') throw new InvalidArgumentException('A reversal cannot be reversed. Add a new entry instead.');
    if ($o['entry_type'] === 'salary_settlement') throw new InvalidArgumentException('This was settled through a finalized salary slip and cannot be reversed here.');
    if (ops_value('SELECT COUNT(*) FROM tbl_ops_hand_ledger WHERE reversal_of_id = ?', array($id))) {
        throw new InvalidArgumentException('This entry is already reversed.');
    }
    if (trim($reason) === '') throw new InvalidArgumentException('Please give a reason for the reversal.');
    return ops_write(
        "INSERT INTO tbl_ops_hand_ledger (staff_id, entry_date, entry_type, amount, direction, category, note, reversal_of_id, created_by, created_by_name)
         VALUES (?, ?, 'reversal', ?, ?, NULL, ?, ?, ?, ?)",
        array($o['staff_id'], date('Y-m-d'), $o['amount'], -(int)$o['direction'], $reason, $id, ops_user_id(), ops_user_name())
    );
}
