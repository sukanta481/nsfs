<?php
/**
 * OpsDesk payroll maths. Pure functions, no DB, so they can be unit tested.
 * Rule ("no work, no pay"):
 *   earned  = round(monthly_salary × payable_days / days_in_month)   -- rounded once, half up
 *   payable = earned − advance (advance recovered up to earned; rest carries forward)
 */

const OPS_STATUSES = array('present', 'paid_leave', 'week_off', 'absent');
const OPS_PAYABLE_STATUSES = array('present', 'paid_leave', 'week_off');

/** Days in the month of a 'Y-m-d' or 'Y-m' string: 28/29/30/31 */
function ops_days_in_month($ym)
{
    $y = (int)substr($ym, 0, 4);
    $m = (int)substr($ym, 5, 2);
    return (int)date('t', mktime(0, 0, 0, $m, 1, $y));
}

/**
 * Count statuses for one staff member in one month.
 * $statusByDay: array day(int 1..31) => status string. Missing days = absent (never marked).
 */
function ops_count_attendance(array $statusByDay, $daysInMonth)
{
    $c = array('present' => 0, 'paid_leave' => 0, 'week_off' => 0, 'absent' => 0, 'unmarked' => 0);
    for ($d = 1; $d <= $daysInMonth; $d++) {
        if (!isset($statusByDay[$d])) {
            $c['unmarked']++;
        } elseif (isset($c[$statusByDay[$d]])) {
            $c[$statusByDay[$d]]++;
        } else {
            $c['unmarked']++;
        }
    }
    $c['payable'] = $c['present'] + $c['paid_leave'] + $c['week_off'];
    $c['absent_total'] = $c['absent'] + $c['unmarked'];
    return $c;
}

/** round(salary × payable / days) in integer maths, half up. */
function ops_earned_paise($salaryPaise, $payableDays, $daysInMonth)
{
    $salaryPaise = (int)$salaryPaise;
    $payableDays = (int)$payableDays;
    $daysInMonth = (int)$daysInMonth;
    if ($daysInMonth < 28 || $daysInMonth > 31) {
        throw new InvalidArgumentException('days_in_month must be 28-31');
    }
    if ($payableDays < 0 || $payableDays > $daysInMonth) {
        throw new InvalidArgumentException('payable_days out of range');
    }
    if ($salaryPaise < 0) {
        throw new InvalidArgumentException('salary must not be negative');
    }
    return intdiv(2 * $salaryPaise * $payableDays + $daysInMonth, 2 * $daysInMonth);
}

/**
 * Full slip numbers.
 * @return array earned, advance_deduction, net, advance_carry, per_day (display only, may have fraction)
 */
function ops_payroll_calc($salaryPaise, $payableDays, $daysInMonth, $advanceOutstandingPaise)
{
    $earned = ops_earned_paise($salaryPaise, $payableDays, $daysInMonth);
    $adv = max(0, (int)$advanceOutstandingPaise);
    $ded = min($adv, $earned);
    return array(
        'earned' => $earned,
        'advance_deduction' => $ded,
        'net' => $earned - $ded,
        'advance_carry' => $adv - $ded,
        'per_day' => $salaryPaise / $daysInMonth,
    );
}
