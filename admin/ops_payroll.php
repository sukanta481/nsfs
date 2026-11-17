<?php
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
requirePermission('ops_payroll_view');
require 'opsdesk/bootstrap.php';

$thisMonth = date('Y-m-01');
$lastMonth = date('Y-m-01', strtotime('first day of last month'));
$month = ops_month_param('month', $lastMonth);
if ($month > $thisMonth) $month = $thisMonth;
$officeId = isset($_REQUEST['office_id']) ? (int)$_REQUEST['office_id'] : 0;
$back = 'ops_payroll.php?month=' . substr($month, 0, 7) . ($officeId ? '&office_id=' . $officeId : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    ops_check_csrf();
    ops_require('ops_payroll_finalize');
    try {
        if ($_POST['action'] === 'finalize_staff') {
            $r = ops_payroll_finalize_staff($month, (int)$_POST['staff_id']);
            ops_flash($r['name'] . ' finalized for ' . date('F Y', strtotime($month)) . ': net pay ' . ops_inr($r['net'])
                . ($r['advance_deduction'] ? ' (advance ' . ops_inr($r['advance_deduction']) . ' recovered)' : '')
                . '. Their attendance for the month is now locked.');
        } elseif ($_POST['action'] === 'finalize') {
            $r = ops_payroll_finalize($month);
            ops_flash(date('F Y', strtotime($month)) . ' closed: ' . $r['saved'] . ' salary slips saved now'
                . ($r['earlier'] ? ', ' . $r['earlier'] . ' finalized earlier' : '')
                . ($r['skipped'] ? ', ' . $r['skipped'] . ' skipped (no salary set)' : '') . '. Attendance for the month is now locked.');
        }
    } catch (InvalidArgumentException $e) {
        ops_flash($e->getMessage(), 'err');
    }
    ops_redirect($back);
}

$sheet = ops_payroll_sheet($month);
$days = $sheet['days'];
$final = $sheet['run'] && $sheet['run']['status'] === 'finalized';
$rows = array_values(array_filter($sheet['rows'], function ($r) use ($officeId) { return !$officeId || (int)$r['office_id'] === $officeId; }));
$tot = array('earned' => 0, 'ded' => 0, 'net' => 0, 'missing' => 0, 'unmarked' => 0, 'nomark' => 0, 'done' => 0, 'pending' => 0, 'pendingNet' => 0);
foreach ($rows as $r) {
    if ($r['missing_salary']) { $tot['missing']++; continue; }
    $tot['earned'] += $r['earned']; $tot['ded'] += $r['advance_deduction']; $tot['net'] += $r['net'];
    if ($r['finalized']) { $tot['done']++; continue; }
    $tot['pending']++; $tot['pendingNet'] += $r['net'];
    if ($r['counts']['unmarked']) $tot['unmarked']++;
    if ($r['counts']['unmarked'] >= $sheet['days']) $tot['nomark']++;
}
$monthLabel = date('F Y', strtotime($month));
$ended = $month < $thisMonth;
$canFinalizeOne = !$final && $ended && hasPermission('ops_payroll_finalize');
$allOffices = !(function_exists('getOfficeFilter') && getOfficeFilter('s') !== '');
$canCloseMonth = $canFinalizeOne && $allOffices;

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="salary-sheet-' . substr($month, 0, 7) . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, array('Staff ID', 'Name', 'Role', 'Branch', 'Monthly salary', 'Days in month', 'Present', 'Paid leave', 'Week off', 'Absent', 'Payable days', 'Earned', 'Advance deducted', 'Net pay', 'Status'));
    foreach ($rows as $r) {
        if ($r['missing_salary']) { fputcsv($out, array($r['uid'], $r['name'], $r['role'], $r['office'], 'NOT SET')); continue; }
        $c = $r['counts'];
        fputcsv($out, array($r['uid'], $r['name'], $r['role'], $r['office'], ops_paise_to_db($r['salary']), $days,
            $c['present'], $c['paid_leave'], $c['week_off'], $c['absent_total'], $c['payable'],
            ops_paise_to_db($r['earned']), ops_paise_to_db($r['advance_deduction']), ops_paise_to_db($r['net']), $r['finalized'] ? 'Finalized' : 'Draft'));
    }
    exit;
}

$ops_title = 'Payroll';
require 'opsdesk/layout_start.php';
?>
<div class="page-head">
  <div><h1>Payroll</h1><p>Pay is worked out from attendance: <b>salary × payable days ÷ days in month</b>, then the advance is taken off. Present, paid leave and week off are payable; absent and unmarked days are not.</p></div>
  <form class="actions" method="get">
    <div class="datebox">
      <a href="?month=<?= date('Y-m', strtotime($month . ' -1 month')) ?>&office_id=<?= $officeId ?>"><i class="fa fa-chevron-left"></i></a>
      <input type="month" name="month" value="<?= substr($month, 0, 7) ?>" max="<?= substr($thisMonth, 0, 7) ?>" onchange="this.form.submit()">
      <?php if ($ended): ?><a href="?month=<?= date('Y-m', strtotime($month . ' +1 month')) ?>&office_id=<?= $officeId ?>"><i class="fa fa-chevron-right"></i></a><?php endif; ?>
    </div>
    <select class="input auto" name="office_id" onchange="this.form.submit()">
      <option value="">All branches</option>
      <?php foreach (ops_offices() as $o): ?><option value="<?= $o['office_id'] ?>" <?= $officeId == $o['office_id'] ? 'selected' : '' ?>><?= h($o['office_name']) ?></option><?php endforeach; ?>
    </select>
  </form>
</div>

<div class="grid g4">
  <div class="card kpi"><div class="lbl">Status</div>
    <div class="val" style="font-size:18px;margin-top:10px">
      <?php if ($final): ?><span class="pill final"><i class="fa fa-lock"></i>Month closed</span>
      <?php elseif (!$ended): ?><span class="pill blue"><i class="fa fa-hourglass-half"></i>Month in progress</span>
      <?php elseif ($tot['done']): ?><span class="pill blue"><i class="fa fa-list-check"></i><?= $tot['done'] ?> of <?= $tot['done'] + $tot['pending'] ?> finalized</span>
      <?php else: ?><span class="pill draft"><i class="fa fa-pen"></i>Draft</span><?php endif; ?></div>
    <div class="hint"><?= $final ? 'by ' . h($sheet['run']['finalized_by_name']) . ' on ' . date('j M Y', strtotime($sheet['run']['finalized_at'])) : ($tot['pending'] ? $tot['pending'] . ' still recalculating live from attendance' : 'Everyone with a salary is finalized') ?></div></div>
  <div class="card kpi"><div class="lbl">Earned</div><div class="val num"><?= ops_inr($tot['earned']) ?></div></div>
  <div class="card kpi"><div class="lbl">Advances recovered</div><div class="val num out"><?= $tot['ded'] ? '−' . ops_inr($tot['ded']) : '₹0' ?></div></div>
  <div class="card kpi"><div class="lbl">Net to pay</div><div class="val num"><?= ops_inr($tot['net']) ?></div></div>
</div>

<?php if (!$final && $ended && $tot['nomark']): ?>
<div class="note err mt"><i class="fa fa-triangle-exclamation"></i><div><b><?= $tot['nomark'] ?> staff have no attendance marked for any day of <?= $monthLabel ?>.</b> If you finalize them now they are paid <b>₹0</b>. Mark their attendance first. <a href="ops_register.php?month=<?= substr($month, 0, 7) ?>&show=unmarked">Open the register →</a></div></div>
<?php endif; ?>
<?php if (!$final && ($tot['missing'] || $tot['unmarked'])): ?>
<div class="note warn mt"><i class="fa fa-circle-exclamation"></i><div>
  <?php if ($tot['missing']): ?><b><?= $tot['missing'] ?> staff have no salary set</b> and cannot be finalized. <a href="ops_salaries.php?show=missing">Set salaries →</a><br><?php endif; ?>
  <?php if ($tot['unmarked']): ?><b><?= $tot['unmarked'] ?> staff have days with no attendance marked</b>; those days count as absent. <a href="ops_register.php?month=<?= substr($month, 0, 7) ?>&show=unmarked">Check the register →</a><?php endif; ?>
</div></div>
<?php endif; ?>

<div class="card mt">
  <div class="card-h"><h3>Salary sheet · <?= $monthLabel ?></h3><span class="muted small">Click a row for the salary slip</span>
    <div class="r">
      <a class="btn" href="?month=<?= substr($month, 0, 7) ?>&office_id=<?= $officeId ?>&export=1"><i class="fa fa-file-csv"></i>Export</a>
      <a class="btn" href="ops_slip.php?month=<?= substr($month, 0, 7) ?>&all=1<?= $officeId ? '&office_id=' . $officeId : '' ?>" target="_blank"><i class="fa fa-print"></i>Print slips</a>
      <?php if ($canCloseMonth): ?><button class="btn primary" onclick="confirmFinalize()"><i class="fa fa-lock"></i><?= $tot['done'] ? 'Finalize remaining & close ' . date('F', strtotime($month)) : 'Finalize all · ' . date('F', strtotime($month)) ?></button><?php endif; ?>
    </div></div>
  <div class="scroll-x"><table class="t">
    <thead><tr><th>Staff</th><th class="right">Monthly salary</th>
      <th class="center" title="Present">P</th><th class="center" title="Paid leave">L</th><th class="center" title="Week off">W</th><th class="center" title="Absent, incl. not marked">A</th>
      <th class="center">Payable</th><th class="right">Earned</th><th class="right">Advance</th><th class="right">Net pay</th><th class="right no-print">Status</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $c = $r['counts']; ?>
      <?php if ($r['missing_salary']): ?>
        <tr><td><div class="who"><?= ops_avatar($r['staff_id'], $r['name']) ?><div><b><?= h($r['name']) ?></b><span><?= h($r['role']) ?> · <?= h($r['office']) ?></span></div></div></td>
          <td colspan="10"><span class="pill draft"><i class="fa fa-circle-exclamation"></i>Salary not set</span> <a class="small" href="ops_salaries.php?q=<?= urlencode($r['name']) ?>">Set salary</a></td></tr>
      <?php else: ?>
        <tr class="click" onclick="location.href='ops_slip.php?month=<?= substr($month, 0, 7) ?>&staff_id=<?= $r['staff_id'] ?>'">
          <td><div class="who"><?= ops_avatar($r['staff_id'], $r['name']) ?><div><b><?= h($r['name']) ?></b><span><?= h($r['role']) ?> · <?= h($r['office']) ?></span></div></div></td>
          <td class="right num"><?= ops_inr($r['salary']) ?></td>
          <td class="center num in"><?= $c['present'] ?></td>
          <td class="center num"><?= $c['paid_leave'] ?></td>
          <td class="center num"><?= $c['week_off'] ?></td>
          <td class="center num out"><?= $c['absent_total'] ?><?php if (!$r['finalized'] && $c['unmarked']): ?><div class="small unmarked-tag"><?= $c['unmarked'] ?> not marked</div><?php endif; ?></td>
          <td class="center num"><b><?= $c['payable'] ?></b><span class="muted">/<?= $days ?></span></td>
          <td class="right num"><?= ops_inr($r['earned']) ?></td>
          <td class="right num <?= $r['advance_deduction'] ? 'out' : 'muted' ?>"><?= $r['advance_deduction'] ? '−' . ops_inr($r['advance_deduction']) : '—' ?>
            <?php if (!empty($r['advance_carry'])): ?><div class="small" style="color:var(--warn)"><?= ops_inr($r['advance_carry']) ?> carried forward</div><?php endif; ?></td>
          <td class="right num"><b><?= ops_inr($r['net']) ?></b></td>
          <td class="right no-print" onclick="event.stopPropagation()">
            <?php if ($r['finalized']): ?><span class="pill final" title="by <?= h($r['finalized_by']) ?> on <?= date('j M Y', strtotime($r['finalized_at'])) ?>"><i class="fa fa-lock"></i>Finalized</span>
            <?php elseif ($canFinalizeOne): ?><button class="btn sm" onclick='finalizeOne(<?= h(json_encode(array(
                'id' => $r['staff_id'], 'name' => $r['name'], 'p' => $c['present'], 'l' => $c['paid_leave'], 'w' => $c['week_off'],
                'a' => $c['absent_total'], 'u' => $c['unmarked'], 'pay' => $c['payable'], 'salary' => ops_inr($r['salary']),
                'earned' => ops_inr($r['earned']), 'adv' => $r['advance_deduction'] ? ops_inr($r['advance_deduction']) : '', 'carry' => !empty($r['advance_carry']) ? ops_inr($r['advance_carry']) : '',
                'net' => ops_inr($r['net'])))) ?>)'><i class="fa fa-lock"></i>Finalize</button>
            <?php else: ?><span class="pill grey">Draft</span><?php endif; ?></td>
        </tr>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="11" class="empty">No staff for this month.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<?php if (!$ended && !$final): ?><p class="small muted">Finalizing opens once <?= $monthLabel ?> has ended.</p><?php endif; ?>

<?php if ($canFinalizeOne): ?>
<form method="post" id="finForm"><?= ops_csrf_field() ?><input type="hidden" name="action" id="finAction" value="finalize"><input type="hidden" name="staff_id" id="finStaff" value=""><input type="hidden" name="office_id" value="<?= $officeId ?>"></form>
<script>
var DAYS = <?= (int)$days ?>, MONTH = <?= json_encode($monthLabel) ?>, MONTH_YM = <?= json_encode(substr($month, 0, 7)) ?>;
function submitFin(action, sid) {
  document.getElementById('finAction').value = action;
  document.getElementById('finStaff').value = sid || '';
  document.getElementById('finForm').submit();
}
function finalizeOne(d) {
  var e = OPS.esc;
  OPS.open('<div class="card-h"><h3><i class="fa fa-lock"></i> Finalize ' + e(d.name) + ' · ' + e(MONTH) + '?</h3></div><div class="card-b">' +
    '<table class="t" style="margin:0"><tbody>' +
    '<tr><td>Present</td><td class="right num in">' + d.p + '</td></tr>' +
    '<tr><td>Paid leave</td><td class="right num">' + d.l + '</td></tr>' +
    '<tr><td>Week off</td><td class="right num">' + d.w + '</td></tr>' +
    '<tr><td>Absent' + (d.u ? ' <span class="small unmarked-tag">(incl. ' + d.u + ' not marked)</span>' : '') + '</td><td class="right num out">' + d.a + '</td></tr>' +
    '<tr><td><b>Payable days</b></td><td class="right num"><b>' + d.pay + ' / ' + DAYS + '</b></td></tr>' +
    '<tr><td>Earned <span class="muted small">(' + e(d.salary) + ' × ' + d.pay + ' ÷ ' + DAYS + ')</span></td><td class="right num">' + e(d.earned) + '</td></tr>' +
    '<tr><td>Advance deducted</td><td class="right num out">' + (d.adv ? '−' + e(d.adv) : '₹0') + '</td></tr>' +
    (d.carry ? '<tr><td class="small" style="color:var(--warn)">Advance still due, carried to next month</td><td class="right num small" style="color:var(--warn)">' + e(d.carry) + '</td></tr>' : '') +
    '</tbody></table>' +
    '<div class="calc mt"><span>Net pay</span><b class="num">' + e(d.net) + '</b></div>' +
    (d.u >= DAYS
      ? '<div class="note err mt"><i class="fa fa-triangle-exclamation"></i><div><b>No attendance is marked for any day of ' + e(MONTH) + '.</b> Net pay will be ₹0. <a href="ops_register.php?month=' + MONTH_YM + '&q=' + encodeURIComponent(d.name) + '">Mark attendance first →</a></div></div>' +
        '<label class="mt" style="display:flex;gap:8px;align-items:center"><input type="checkbox" onchange="document.getElementById(&quot;finGo&quot;).disabled=!this.checked"> I understand ' + e(d.name) + ' will be paid ₹0 for ' + e(MONTH) + '</label>'
      : (d.u ? '<div class="note warn mt"><i class="fa fa-circle-exclamation"></i><div>' + d.u + ' day(s) are not marked and will be paid as <b>absent</b>. Mark them first if that is wrong.</div></div>' : '')) +
    '<p class="small muted">This saves the salary slip, records the advance recovery and <b>locks ' + e(d.name) + '’s attendance for ' + e(MONTH) + '</b>. It cannot be undone from the screen.</p></div>' +
    '<div class="foot"><button class="btn" onclick="OPS.close()">Cancel</button><button class="btn primary" id="finGo"' + (d.u >= DAYS ? ' disabled' : '') + ' onclick="this.disabled=true;submitFin(\'finalize_staff\',' + d.id + ')">Finalize ' + e(d.name.split(' ')[0]) + '</button></div>');
}
<?php if ($canCloseMonth): ?>
function confirmFinalize() {
  OPS.open('<div class="card-h"><h3><i class="fa fa-lock"></i> Close <?= $monthLabel ?>?</h3></div><div class="card-b">' +
    '<p style="margin-top:0">This covers <b>all branches</b> and will:</p><ul>' +
    '<li>save salary slips for the <b><?= $tot['pending'] ?> staff</b> not finalized yet (net <?= ops_inr($tot['pendingNet']) ?>), each calculated from their attendance and leave,</li>' +
    '<?= $tot['done'] ? '<li>keep the ' . $tot['done'] . ' slips already finalized as they are,</li>' : '' ?>' +
    '<li>record the advance recoveries,</li><li><b>lock <?= $monthLabel ?> attendance</b> for everyone.</li></ul>' +
    '<?= $tot['nomark'] ? '<div class="note err"><i class="fa fa-triangle-exclamation"></i><div><b>' . $tot['nomark'] . ' staff have no attendance for the whole month</b> and will be paid ₹0.</div></div>' : '' ?>' +
    '<?= $tot['unmarked'] ? '<div class="note warn mt"><i class="fa fa-circle-exclamation"></i><div>' . $tot['unmarked'] . ' staff have unmarked days, which will be paid as absent.</div></div>' : '' ?>' +
    '<?= $tot['missing'] ? '<div class="note warn mt"><i class="fa fa-circle-exclamation"></i><div>' . $tot['missing'] . ' staff have no salary set and will be skipped.</div></div>' : '' ?>' +
    '<p class="small muted">This cannot be undone from the screen.</p></div>' +
    '<div class="foot"><button class="btn" onclick="OPS.close()">Cancel</button><?= $tot['nomark'] ? '<label style="display:flex;gap:8px;align-items:center;margin-right:auto"><input type="checkbox" onchange="document.getElementById(&quot;closeGo&quot;).disabled=!this.checked"> I understand ' . $tot['nomark'] . ' staff will get ₹0</label>' : '' ?><button class="btn primary" id="closeGo"<?= $tot['nomark'] ? ' disabled' : '' ?> onclick="this.disabled=true;submitFin(\'finalize\')">Close month</button></div>');
}
<?php endif; ?>
</script>
<?php endif; ?>
<?php require 'opsdesk/layout_end.php'; ?>
