<?php
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
if (!hasPermission('ops_attendance_view') && !hasPermission('ops_payroll_view') && !hasPermission('ops_hand_ledger_view')) {
    requirePermission('ops_attendance_view');
}
require 'opsdesk/bootstrap.php';

$today = date('Y-m-d');
$staff = ops_staff_list();
$att = ops_attendance_for_date($today);
$unmarked = array();
foreach ($staff as $s) {
    if (!isset($att[(int)$s['staff_id']])) $unmarked[] = $s;
}
$total = count($staff);
$marked = $total - count($unmarked);

$balances = ops_hand_balances_all();
$holding = 0; $owed = 0; $holdingN = 0; $owedN = 0;
foreach ($balances as $v) {
    if ($v > 0) { $holding += $v; $holdingN++; } else { $owed += -$v; $owedN++; }
}

$advOut = ops_paise_from_db(ops_value(
    "SELECT COALESCE(SUM(CASE WHEN a.entry_type='advance' THEN a.amount WHEN a.entry_type='recovery' THEN -a.amount
                               WHEN o.entry_type='advance' THEN -a.amount ELSE a.amount END), 0)
     FROM tbl_ops_staff_advances a JOIN tbl_staff s ON s.staff_id = a.staff_id
     LEFT JOIN tbl_ops_staff_advances o ON o.id = a.reversal_of_id WHERE 1=1" . ops_office_scope('s')
));

$noSalary = 0;
foreach ($staff as $s) {
    if (ops_salary_for_month((int)$s['staff_id'], date('Y-m-01'), $s['salary']) === null) $noSalary++;
}
$lastMonth = date('Y-m-01', strtotime('first day of last month'));
$lastFinal = ops_month_locked($lastMonth);

$ops_title = 'OpsDesk Overview';
require 'opsdesk/layout_start.php';
?>
<div class="page-head">
  <div><h1>OpsDesk</h1><p><?= date('l, j F Y') ?><?= function_exists('getOfficeFilter') && getOfficeFilter('s') !== '' ? ' · your branch' : ' · all branches' ?></p></div>
</div>

<div class="grid g4">
  <a class="card kpi" href="ops_attendance.php"><div class="lbl"><i class="fa fa-calendar-check"></i>Attendance today</div>
    <div class="val num"><?= $marked ?><span class="muted" style="font-size:16px"> / <?= $total ?></span></div>
    <div class="bar"><i style="width:<?= $total ? round($marked / $total * 100) : 0 ?>%"></i></div><div class="hint"><?= count($unmarked) ?> not marked yet</div></a>
  <a class="card kpi" href="ops_hand.php"><div class="lbl"><i class="fa fa-wallet"></i>Cash with staff</div>
    <div class="val num in"><?= ops_inr($holding) ?></div><div class="hint"><?= $holdingN ?> staff · still to give hisab</div></a>
  <a class="card kpi" href="ops_hand.php"><div class="lbl"><i class="fa fa-triangle-exclamation"></i>Company owes staff</div>
    <div class="val num out"><?= ops_inr($owed) ?></div><div class="hint"><?= $owedN ?> staff · spent from own pocket</div></a>
  <a class="card kpi" href="ops_advances.php"><div class="lbl"><i class="fa fa-hand-holding-dollar"></i>Advances outstanding</div>
    <div class="val num"><?= ops_inr($advOut) ?></div><div class="hint">deducted at month end</div></a>
</div>

<div class="grid g2 mt">
  <div class="card">
    <div class="card-h"><h3>Not marked today</h3><span class="pill draft"><?= count($unmarked) ?></span><div class="r"><a class="btn sm" href="ops_attendance.php">Mark now</a></div></div>
    <?php if (!$unmarked): ?><div class="empty"><i class="fa fa-circle-check in"></i> Everyone is marked</div>
    <?php else: ?><table class="t">
      <?php foreach (array_slice($unmarked, 0, 8) as $s): ?>
        <tr><td><div class="who"><?= ops_avatar($s['staff_id'], $s['staff_name']) ?><div><b><?= h($s['staff_name']) ?></b><span><?= h($s['staff_role']) ?> · <?= h($s['office_name']) ?></span></div></div></td><td class="right unmarked-tag">counts as absent</td></tr>
      <?php endforeach; ?>
      <?php if (count($unmarked) > 8): ?><tr><td colspan="2" class="muted small"><a href="ops_attendance.php">and <?= count($unmarked) - 8 ?> more →</a></td></tr><?php endif; ?>
    </table><?php endif; ?>
  </div>
  <div class="card">
    <div class="card-h"><h3>To do</h3></div>
    <div class="card-b" style="display:flex;flex-direction:column;gap:10px">
      <?php if ($noSalary): ?><div class="note warn"><i class="fa fa-circle-exclamation"></i><div><b><?= $noSalary ?> staff have no salary set.</b> They are skipped in payroll. <a href="ops_salaries.php?show=missing">Set salaries →</a></div></div><?php endif; ?>
      <?php if (!$lastFinal): ?><div class="note info"><i class="fa fa-indian-rupee-sign"></i><div><b><?= date('F Y', strtotime($lastMonth)) ?> payroll is not finalized yet.</b> <a href="ops_payroll.php?month=<?= substr($lastMonth, 0, 7) ?>">Review payroll →</a></div></div>
      <?php else: ?><div class="note ok"><i class="fa fa-circle-check"></i><div><?= date('F Y', strtotime($lastMonth)) ?> payroll is finalized.</div></div><?php endif; ?>
      <?php if ($owedN): ?><div class="note warn"><i class="fa fa-wallet"></i><div><b><?= $owedN ?> staff</b> spent from their own pocket (<?= ops_inr($owed) ?>). It is added to their salary at month end. <a href="ops_hand.php">Open hand balance →</a></div></div><?php endif; ?>
    </div>
  </div>
</div>
<?php require 'opsdesk/layout_end.php'; ?>
