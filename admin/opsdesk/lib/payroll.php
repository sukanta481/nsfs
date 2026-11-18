<?php
/**
 * OpsDesk attendance + payroll, DB side. The maths lives in payroll_calc.php.
 * One payroll run per month for the whole company. Finalizing locks that month's attendance.
 */

/** true when the month containing $date has a finalized payroll run */
function ops_month_locked($date)
{
    static $cache = array();
    $m = substr($date, 0, 7) . '-01';
    if (!isset($cache[$m])) {
        $cache[$m] = (bool)ops_value("SELECT COUNT(*) FROM tbl_ops_payroll_runs WHERE pay_month = ? AND status = 'finalized'", array($m));
    }
    return $cache[$m];
}

/** Set (insert or change) one staff member's status for one day. */
function ops_attendance_set($staffId, $date, $status, $remarks = null, $shift = null)
{
    if (!in_array($status, OPS_STATUSES, true)) throw new InvalidArgumentException('Unknown status.');
    if ($status === 'present') {
        if ($shift === null || $shift === '') $shift = 'day';
        if (!in_array($shift, OPS_SHIFTS, true)) throw new InvalidArgumentException('Choose Day or Night.');
    } else {
        $shift = null;
    }
    if ($date > date('Y-m-d')) throw new InvalidArgumentException('You cannot mark attendance for a future date.');
    if (ops_staff_locked($staffId, $date)) throw new InvalidArgumentException('Payroll for ' . date('F Y', strtotime($date)) . ' is finalized for this staff member, so their attendance is locked.');
    $staff = ops_one('SELECT staff_id FROM tbl_staff WHERE staff_id = ?' . ops_office_scope('tbl_staff'), array($staffId));
    if (!$staff) throw new InvalidArgumentException('Staff member not found in your branch.');
    ops_write(
        "INSERT INTO tbl_ops_attendance (staff_id, att_date, status, shift, remarks, marked_by, marked_by_name)
         VALUES (?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
             extra_duty = IF(VALUES(status) = 'present' AND extra_duty IS NOT NULL AND extra_duty <> VALUES(shift), extra_duty, NULL),
             status = VALUES(status), shift = VALUES(shift),
             remarks = COALESCE(VALUES(remarks), remarks),
             updated_by = VALUES(marked_by), updated_by_name = VALUES(marked_by_name)",
        array($staffId, $date, $status, $shift, $remarks, ops_user_id(), ops_user_name())
    );
}

/**
 * Set or clear Extra Duty (EXD) for one day. Only on a Present day, max one, and only the other shift.
 * $extra: 'day' | 'night' | null (clear)
 */
function ops_attendance_extra($staffId, $date, $extra)
{
    if ($extra === '') $extra = null;
    if ($extra !== null && !in_array($extra, OPS_SHIFTS, true)) throw new InvalidArgumentException('Choose Day or Night for extra duty.');
    if (ops_staff_locked($staffId, $date)) throw new InvalidArgumentException('Payroll for ' . date('F Y', strtotime($date)) . ' is finalized for this staff member, so their attendance is locked.');
    $row = ops_one('SELECT a.status, a.shift FROM tbl_ops_attendance a JOIN tbl_staff s ON s.staff_id = a.staff_id
                    WHERE a.staff_id = ? AND a.att_date = ?' . ops_office_scope('s'), array($staffId, $date));
    if ($extra !== null) {
        if (!$row || $row['status'] !== 'present') throw new InvalidArgumentException('Extra duty can only be added on a Present day. Mark Present first.');
        if ($row['shift'] === $extra) throw new InvalidArgumentException('Extra duty must be the other shift (' . ($row['shift'] === 'day' ? 'Night' : 'Day') . ').');
    }
    if (!$row) return 0;
    return ops_write(
        'UPDATE tbl_ops_attendance SET extra_duty = ?, updated_by = ?, updated_by_name = ? WHERE staff_id = ? AND att_date = ?',
        array($extra, ops_user_id(), ops_user_name(), $staffId, $date)
    );
}

/** Save remarks only (row must exist or is created as absent? -> only when marked) */
function ops_attendance_remarks($staffId, $date, $remarks)
{
    if (ops_staff_locked($staffId, $date)) throw new InvalidArgumentException('This month is locked for this staff member.');
    return ops_write(
        'UPDATE tbl_ops_attendance SET remarks = ?, updated_by = ?, updated_by_name = ? WHERE staff_id = ? AND att_date = ?',
        array($remarks, ops_user_id(), ops_user_name(), $staffId, $date)
    );
}

/**
 * Staff list for attendance/payroll.
 * $includeInactiveWithData: also include inactive staff who have attendance in $month.
 */
function ops_staff_list(array $filters = array(), $month = null)
{
    $where = array('1=1');
    $params = array();
    if (!empty($filters['office_id'])) { $where[] = 's.office_id = ?'; $params[] = $filters['office_id']; }
    if (!empty($filters['role'])) { $where[] = 's.staff_role = ?'; $params[] = $filters['role']; }
    if (!empty($filters['q'])) {
        $where[] = '(s.staff_name LIKE ? OR s.staff_unique_id LIKE ? OR s.staff_phone LIKE ?)';
        $like = '%' . $filters['q'] . '%';
        array_push($params, $like, $like, $like);
    }
    if ($month) {
        $where[] = '(s.active_status = 1 OR EXISTS (SELECT 1 FROM tbl_ops_attendance a WHERE a.staff_id = s.staff_id AND a.att_date BETWEEN ? AND LAST_DAY(?)))';
        array_push($params, $month, $month);
    } else {
        $where[] = 's.active_status = 1';
    }
    return ops_rows(
        'SELECT s.staff_id, s.staff_unique_id, s.staff_name, s.staff_role, s.staff_phone, s.office_id,
                COALESCE(o.office_name, s.branch_office) AS office_name, s.week_off_day, s.salary, s.active_status
         FROM tbl_staff s LEFT JOIN tbl_offices o ON o.office_id = s.office_id
         WHERE ' . implode(' AND ', $where) . ops_office_scope('s') . '
         ORDER BY office_name, s.staff_role = \'Driver\' DESC, s.staff_role = \'Helper\' DESC, s.staff_name',
        $params
    );
}

/** attendance rows for a date: staff_id => [status, remarks] */
function ops_attendance_for_date($date)
{
    $out = array();
    foreach (ops_rows('SELECT staff_id, status, shift, extra_duty, remarks FROM tbl_ops_attendance WHERE att_date = ?', array($date)) as $r) {
        $out[(int)$r['staff_id']] = $r;
    }
    return $out;
}

/** month grid: staff_id => [day => status] */
function ops_attendance_for_month($month)
{
    $out = array();
    $rows = ops_rows('SELECT staff_id, DAY(att_date) AS d, status FROM tbl_ops_attendance WHERE att_date BETWEEN ? AND LAST_DAY(?)', array($month, $month));
    foreach ($rows as $r) {
        $out[(int)$r['staff_id']][(int)$r['d']] = $r['status'];
    }
    return $out;
}

/** month detail: staff_id => [day => ['status'=>..,'shift'=>..,'extra'=>..]] (for the register) */
function ops_attendance_detail_for_month($month)
{
    $out = array();
    $rows = ops_rows('SELECT staff_id, DAY(att_date) AS d, status, shift, extra_duty FROM tbl_ops_attendance WHERE att_date BETWEEN ? AND LAST_DAY(?)', array($month, $month));
    foreach ($rows as $r) {
        $out[(int)$r['staff_id']][(int)$r['d']] = array('status' => $r['status'], 'shift' => $r['shift'], 'extra' => $r['extra_duty']);
    }
    return $out;
}

/** staff_id => number of Extra Duty (EXD) days in the month */
function ops_extra_duty_counts($month)
{
    $out = array();
    foreach (ops_rows('SELECT staff_id, COUNT(*) AS n FROM tbl_ops_attendance WHERE att_date BETWEEN ? AND LAST_DAY(?) AND extra_duty IS NOT NULL GROUP BY staff_id', array($month, $month)) as $r) {
        $out[(int)$r['staff_id']] = (int)$r['n'];
    }
    return $out;
}

/** Monthly salary in paise effective on $month (1st), or null when never set. */
function ops_salary_for_month($staffId, $month, $fallbackDec = null)
{
    $v = ops_value('SELECT monthly_salary FROM tbl_ops_salary_revisions WHERE staff_id = ? AND effective_from <= ? ORDER BY effective_from DESC, id DESC LIMIT 1', array($staffId, $month));
    if ($v !== null) return ops_paise_from_db($v);
    // Has OpsDesk salary history, but it starts after this month: no salary for this month.
    // (tbl_staff.salary only mirrors the CURRENT salary, so it must not leak into earlier months.)
    if (ops_value('SELECT COUNT(*) FROM tbl_ops_salary_revisions WHERE staff_id = ?', array($staffId))) return null;
    // No revision yet: use the existing tbl_staff.salary (if any) so old data still works.
    if ($fallbackDec !== null && $fallbackDec !== '' && (float)$fallbackDec > 0) return ops_paise_from_db($fallbackDec);
    return null;
}

function ops_salary_set($staffId, $paise, $effectiveFrom, $note)
{
    if ($paise <= 0) throw new InvalidArgumentException('Salary must be more than zero.');
    $effectiveFrom = substr($effectiveFrom, 0, 7) . '-01';
    if (ops_staff_locked($staffId, $effectiveFrom)) throw new InvalidArgumentException('Payroll for that month is already finalized for this staff member. Choose a later month.');
    ops_begin();
    try {
        ops_write(
            'INSERT INTO tbl_ops_salary_revisions (staff_id, monthly_salary, effective_from, note, created_by, created_by_name) VALUES (?, ?, ?, ?, ?, ?)',
            array($staffId, ops_paise_to_db($paise), $effectiveFrom, $note, ops_user_id(), ops_user_name())
        );
        // keep tbl_staff.salary as the "current" mirror the old CMS screens show
        $current = ops_salary_for_month($staffId, date('Y-m-01'));
        if ($current !== null) {
            ops_write('UPDATE tbl_staff SET salary = ? WHERE staff_id = ?', array(ops_paise_to_db($current), $staffId));
        }
        ops_commit();
    } catch (Exception $e) {
        ops_rollback();
        throw $e;
    }
}

/** staff_id => slip_id for everyone already finalized (individually or in the full run) for that month */
function ops_finalized_staff($date)
{
    static $cache = array();
    $m = substr($date, 0, 7) . '-01';
    if (!isset($cache[$m])) {
        $cache[$m] = array();
        foreach (ops_rows('SELECT staff_id, slip_id FROM tbl_ops_salary_slips WHERE pay_month = ?', array($m)) as $r) {
            $cache[$m][(int)$r['staff_id']] = (int)$r['slip_id'];
        }
    }
    return $cache[$m];
}

/** true when this staff member's month is closed: their own slip is saved, or the whole month is finalized */
function ops_staff_locked($staffId, $date)
{
    $f = ops_finalized_staff($date);
    return isset($f[(int)$staffId]) || ops_month_locked($date);
}

/** Live calculation for one staff row, from attendance + leave + salary + advances. */
function ops_payroll_live_row(array $s, $month, $days, array $grid, array $extra = array())
{
    $sid = (int)$s['staff_id'];
    $counts = ops_count_attendance(isset($grid[$sid]) ? $grid[$sid] : array(), $days, isset($extra[$sid]) ? $extra[$sid] : 0);
    $salary = ops_salary_for_month($sid, $month, $s['salary']);
    $monthEnd = date('Y-m-t', strtotime($month));
    $row = array(
        'staff_id' => $sid, 'name' => $s['staff_name'], 'uid' => $s['staff_unique_id'], 'role' => $s['staff_role'],
        'office' => $s['office_name'], 'office_id' => $s['office_id'], 'counts' => $counts, 'salary' => $salary,
        'hand_balance' => ops_hand_balance($sid, $monthEnd), 'missing_salary' => $salary === null,
        'slip_id' => null, 'finalized' => false, 'finalized_by' => null, 'finalized_at' => null,
    );
    if ($salary !== null) {
        $calc = ops_payroll_calc($salary, $counts['payable'], $days, ops_advance_outstanding($sid, $monthEnd), $counts['extra_duty']);
        $row += array('earned' => $calc['earned'], 'extra_pay' => $calc['extra_pay'], 'gross' => $calc['gross'],
                      'advance_deduction' => $calc['advance_deduction'], 'net' => $calc['net'], 'advance_carry' => $calc['advance_carry']);
    }
    return $row;
}

/**
 * Salary sheet for a month. Staff already finalized show their saved slip;
 * everyone else is recalculated live from attendance and leave.
 * Returns ['run'=>row|null, 'days'=>int, 'rows'=>[...], 'done'=>int, 'pending'=>int]
 */
function ops_payroll_sheet($month)
{
    $days = ops_days_in_month($month);
    $run = ops_one('SELECT * FROM tbl_ops_payroll_runs WHERE pay_month = ?', array($month));
    $saved = array();
    $slips = ops_rows(
        'SELECT sl.*, s.staff_name, s.staff_unique_id, s.staff_role, COALESCE(o.office_name, s.branch_office) AS office_name
         FROM tbl_ops_salary_slips sl JOIN tbl_staff s ON s.staff_id = sl.staff_id
         LEFT JOIN tbl_offices o ON o.office_id = sl.office_id
         WHERE sl.pay_month = ? ' . ops_office_scope('s'), array($month)
    );
    foreach ($slips as $r) {
        $saved[(int)$r['staff_id']] = array(
            'staff_id' => (int)$r['staff_id'], 'name' => $r['staff_name'], 'uid' => $r['staff_unique_id'],
            'role' => $r['staff_role'], 'office' => $r['office_name'], 'office_id' => $r['office_id'],
            'counts' => array('present' => (int)$r['present_days'], 'paid_leave' => (int)$r['paid_leave_days'], 'week_off' => (int)$r['week_off_days'],
                              'absent_total' => (int)$r['absent_days'], 'payable' => (int)$r['payable_days'], 'unmarked' => 0,
                              'extra_duty' => (int)$r['extra_duty_days']),
            'salary' => ops_paise_from_db($r['monthly_salary']), 'earned' => ops_paise_from_db($r['earned_amount']),
            'extra_pay' => ops_paise_from_db($r['extra_duty_amount']),
            'gross' => ops_paise_from_db($r['earned_amount']) + ops_paise_from_db($r['extra_duty_amount']),
            'advance_deduction' => ops_paise_from_db($r['advance_deduction']), 'net' => ops_paise_from_db($r['net_payable']),
            'advance_carry' => null, 'hand_balance' => $r['hand_balance_at_close'] === null ? null : ops_paise_from_db($r['hand_balance_at_close']),
            'slip_id' => (int)$r['slip_id'], 'missing_salary' => false,
            'finalized' => true, 'finalized_by' => $r['created_by_name'], 'finalized_at' => $r['created_at'],
        );
    }
    $monthFinal = $run && $run['status'] === 'finalized';
    $grid = $monthFinal ? array() : ops_attendance_for_month($month);
    $extra = $monthFinal ? array() : ops_extra_duty_counts($month);
    $out = array();
    $done = 0;
    $pending = 0;
    foreach (ops_staff_list(array(), $month) as $s) {
        $sid = (int)$s['staff_id'];
        if (isset($saved[$sid])) { $out[] = $saved[$sid]; unset($saved[$sid]); $done++; continue; }
        if ($monthFinal) continue; // month closed: only the saved slips matter
        $out[] = ops_payroll_live_row($s, $month, $days, $grid, $extra);
        $pending++;
    }
    foreach ($saved as $r) { $out[] = $r; $done++; } // finalized staff who are no longer in the active list
    return array('run' => $run, 'days' => $days, 'rows' => $out, 'done' => $done, 'pending' => $pending);
}

/** Get (or create as draft) the month's single payroll run, locked for this transaction. */
function ops_payroll_run_id($month)
{
    ops_write(
        "INSERT IGNORE INTO tbl_ops_payroll_runs (pay_month, status, created_by, created_by_name) VALUES (?, 'draft', ?, ?)",
        array($month, ops_user_id(), ops_user_name())
    );
    $run = ops_one('SELECT run_id, status FROM tbl_ops_payroll_runs WHERE pay_month = ? FOR UPDATE', array($month));
    if ($run['status'] === 'finalized') throw new InvalidArgumentException('This month is already finalized.');
    return (int)$run['run_id'];
}

/** Save one slip plus its advance recovery. Call inside a transaction. */
function ops_payroll_save_slip($runId, array $r, $month, $days)
{
    $c = $r['counts'];
    $slipId = ops_write(
        'INSERT INTO tbl_ops_salary_slips (run_id, staff_id, office_id, pay_month, days_in_month, present_days, paid_leave_days, week_off_days,
             absent_days, payable_days, extra_duty_days, monthly_salary, earned_amount, extra_duty_amount, advance_deduction, net_payable,
             hand_balance_at_close, created_by, created_by_name)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        array($runId, $r['staff_id'], $r['office_id'], $month, $days, $c['present'], $c['paid_leave'], $c['week_off'],
              $c['absent_total'], $c['payable'], $c['extra_duty'], ops_paise_to_db($r['salary']), ops_paise_to_db($r['earned']),
              ops_paise_to_db($r['extra_pay']),
              ops_paise_to_db($r['advance_deduction']), ops_paise_to_db($r['net']), ops_paise_to_db($r['hand_balance']),
              ops_user_id(), ops_user_name())
    );
    if ($r['advance_deduction'] > 0) {
        ops_write(
            "INSERT INTO tbl_ops_staff_advances (staff_id, entry_date, entry_type, amount, slip_id, note, created_by, created_by_name)
             VALUES (?, ?, 'recovery', ?, ?, ?, ?, ?)",
            array($r['staff_id'], date('Y-m-t', strtotime($month)), ops_paise_to_db($r['advance_deduction']), $slipId,
                  'Recovered from ' . date('F Y', strtotime($month)) . ' salary', ops_user_id(), ops_user_name())
        );
    }
    return $slipId;
}

/** Finalize ONE staff member: save their slip, recover their advance, lock their attendance for the month. */
function ops_payroll_finalize_staff($month, $staffId)
{
    if ($month >= date('Y-m-01')) throw new InvalidArgumentException('A month can only be finalized after it has ended.');
    if (ops_month_locked($month)) throw new InvalidArgumentException('This month is already finalized.');
    $s = null;
    foreach (ops_staff_list(array(), $month) as $x) {
        if ((int)$x['staff_id'] === (int)$staffId) { $s = $x; break; }
    }
    if (!$s) throw new InvalidArgumentException('Staff member not found in your branch.');
    ops_begin();
    try {
        $runId = ops_payroll_run_id($month);
        if (ops_value('SELECT COUNT(*) FROM tbl_ops_salary_slips WHERE pay_month = ? AND staff_id = ?', array($month, $staffId))) {
            throw new InvalidArgumentException($s['staff_name'] . ' is already finalized for ' . date('F Y', strtotime($month)) . '.');
        }
        $days = ops_days_in_month($month);
        $r = ops_payroll_live_row($s, $month, $days, ops_attendance_for_month($month), ops_extra_duty_counts($month));
        if ($r['missing_salary']) throw new InvalidArgumentException('Set a salary for ' . $s['staff_name'] . ' first.');
        ops_payroll_save_slip($runId, $r, $month, $days);
        ops_commit();
        return $r;
    } catch (Exception $e) {
        ops_rollback();
        throw $e;
    }
}

/** Finalize everyone still pending and close the month. Whole company, all branches. */
function ops_payroll_finalize($month)
{
    if ($month >= date('Y-m-01')) throw new InvalidArgumentException('A month can only be finalized after it has ended.');
    if (ops_month_locked($month)) throw new InvalidArgumentException('This month is already finalized.');
    if (function_exists('getOfficeFilter') && getOfficeFilter('s') !== '') {
        throw new InvalidArgumentException('Closing the whole month covers all branches, so only a user with access to all offices can do it. You can still finalize staff one by one.');
    }
    ops_begin();
    try {
        $runId = ops_payroll_run_id($month);
        $sheet = ops_payroll_sheet($month);
        $saved = 0;
        $skipped = 0;
        foreach ($sheet['rows'] as $r) {
            if ($r['finalized']) continue;
            if ($r['missing_salary']) { $skipped++; continue; }
            ops_payroll_save_slip($runId, $r, $month, $sheet['days']);
            $saved++;
        }
        ops_write(
            "UPDATE tbl_ops_payroll_runs SET status = 'finalized', finalized_by = ?, finalized_by_name = ?, finalized_at = NOW() WHERE run_id = ?",
            array(ops_user_id(), ops_user_name(), $runId)
        );
        ops_commit();
        return array('saved' => $saved, 'skipped' => $skipped, 'earlier' => $sheet['done']);
    } catch (Exception $e) {
        ops_rollback();
        throw $e;
    }
}
