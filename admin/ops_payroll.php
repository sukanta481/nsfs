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
            $sid = (int)$_POST['staff_id'];
            $r = ops_payroll_finalize_staff($month, $sid);
            ops_flash($r['name'] . ' finalized for ' . date('F Y', strtotime($month)) . ': net pay ' . ops_inr($r['net'])
                . '. Attendance is locked. After paying the salary, click Mark paid: that settles the advance and hand balance.');
            ops_redirect('ops_slip.php?month=' . substr($month, 0, 7) . '&staff_id=' . $sid . '&print=1');
        } elseif ($_POST['action'] === 'mark_paid') {
            $r = ops_salary_mark_paid((int)$_POST['slip_id'], ops_date_param('pay_date', ''), isset($_POST['mode']) ? $_POST['mode'] : '',
                trim(mb_substr(isset($_POST['reference']) ? $_POST['reference'] : '', 0, 100)), trim(mb_substr(isset($_POST['note']) ? $_POST['note'] : '', 0, 255)));
            ops_flash($r['staff_name'] . ': ' . $r['month'] . ' salary ' . ops_inr($r['net']) . ' marked Paid. Advance and hand balance are now settled.');
        } elseif ($_POST['action'] === 'undo_paid') {
            $name = ops_salary_undo_paid((int)$_POST['slip_id'], isset($_POST['reason']) ? $_POST['reason'] : '');
            ops_flash($name . ': payment undone. Advance and hand balance entries were reversed; the slip is Unpaid again.');
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
$tot = array('earned' => 0, 'extra' => 0, 'ded' => 0, 'hand' => 0, 'net' => 0, 'missing' => 0, 'unmarked' => 0, 'nomark' => 0, 'done' => 0, 'pending' => 0, 'pendingNet' => 0, 'paid' => 0, 'unpaid' => 0, 'unpaidNet' => 0);
foreach ($rows as $r) {
    if ($r['missing_salary']) { $tot['missing']++; continue; }
    $tot['earned'] += $r['gross']; $tot['extra'] += $r['extra_pay']; $tot['ded'] += $r['advance_deduction']; $tot['hand'] += $r['hand_settlement']; $tot['net'] += $r['net'];
    if ($r['finalized']) {
        $tot['done']++;
        if ($r['paid']) $tot['paid']++; else { $tot['unpaid']++; $tot['unpaidNet'] += $r['net']; }
        continue;
    }
    $tot['pending']++; $tot['pendingNet'] += $r['net'];
    if ($r['counts']['unmarked']) $tot['unmarked']++;
    if ($r['counts']['unmarked'] >= $sheet['days']) $tot['nomark']++;
}
$monthLabel = date('F Y', strtotime($month));
$ended = $month < $thisMonth;
$canFinalizeOne = !$final && $ended && hasPermission('ops_payroll_finalize');
$allOffices = !(function_exists('getOfficeFilter') && getOfficeFilter('s') !== '');
$canCloseMonth = $canFinalizeOne && $allOffices;
$canPay = hasPermission('ops_payroll_finalize');

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="salary-sheet-' . substr($month, 0, 7) . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, array('Staff ID', 'Name', 'Role', 'Branch', 'Monthly salary', 'Days in month', 'Present', 'Paid leave', 'Week off', 'Absent', 'Payable days', 'Earned', 'Extra duty days', 'Extra duty pay', 'Gross (total salary)', 'Advance deducted', 'Cash in hand settled (+deducted / -added)', 'Net pay', 'Status'));
    foreach ($rows as $r) {
        if ($r['missing_salary']) { fputcsv($out, array($r['uid'], $r['name'], $r['role'], $r['office'], 'NOT SET')); continue; }
        $c = $r['counts'];
        fputcsv($out, array($r['uid'], $r['name'], $r['role'], $r['office'], ops_paise_to_db($r['salary']), $days,
            $c['present'], $c['paid_leave'], $c['week_off'], $c['absent_total'], $c['payable'],
            ops_paise_to_db($r['earned']), $c['extra_duty'], ops_paise_to_db($r['extra_pay']), ops_paise_to_db($r['gross']), ops_paise_to_db($r['advance_deduction']), ops_paise_to_db($r['hand_settlement']), ops_paise_to_db($r['net']), $r['finalized'] ? 'Finalized' : 'Draft'));
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
    <?php if ($tot['done']): ?><div class="hint"><b class="in"><?= $tot['paid'] ?> paid</b> · <b class="<?= $tot['unpaid'] ? 'out' : '' ?>"><?= $tot['unpaid'] ?> unpaid</b><?= $tot['unpaid'] ? ' (' . ops_inr($tot['unpaidNet']) . ' to pay)' : '' ?></div><?php endif; ?>
    <div class="hint"><?= $final ? 'by ' . h($sheet['run']['finalized_by_name']) . ' on ' . date('j M Y', strtotime($sheet['run']['finalized_at'])) : ($tot['pending'] ? $tot['pending'] . ' still recalculating live from attendance' : 'Everyone with a salary is finalized') ?></div></div>
  <div class="card kpi"><div class="lbl">Gross earned</div><div class="val num"><?= ops_inr($tot['earned']) ?></div><?php if ($tot['extra']): ?><div class="hint">incl. <?= ops_inr($tot['extra']) ?> extra duty</div><?php endif; ?></div>
  <div class="card kpi"><div class="lbl">Advances recovered</div><div class="val num out"><?= $tot['ded'] ? '−' . ops_inr($tot['ded']) : '₹0' ?></div><div class="hint">Cash in hand settled: <?= $tot['hand'] ? ($tot['hand'] > 0 ? '−' : '+') . ops_inr(abs($tot['hand'])) : '₹0' ?></div></div>
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

<?php $blockedPrev = (!$final && $ended) ? ops_unpaid_slips_before(null, $month) : array(); ?>
<?php if ($blockedPrev): $bn = array_values(array_unique(array_map(function ($b) { return $b['staff_name']; }, $blockedPrev))); ?>
<div class="note warn mt"><i class="fa fa-hand"></i><div><b><?= count($bn) ?> staff still have an earlier salary not marked Paid</b> (<?= h(implode(', ', array_slice($bn, 0, 5))) ?><?= count($bn) > 5 ? ' …' : '' ?>).
  They cannot be finalized for <?= $monthLabel ?> until that is paid. <a href="ops_payroll.php?month=<?= substr($blockedPrev[0]['pay_month'], 0, 7) ?>">Open <?= date('F Y', strtotime($blockedPrev[0]['pay_month'])) ?> →</a></div></div>
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
      <th class="center">Payable</th><th class="center" title="Extra duty days">EXD</th><th class="right">Earned</th><th class="right" title="Extra duty pay">Extra duty</th><th class="right">Advance</th><th class="right" title="Road-expense cash: held by staff is deducted, owed to staff is added">Cash in hand</th><th class="right">Net pay</th><th class="right no-print">Status</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $c = $r['counts']; ?>
      <?php if ($r['missing_salary']): ?>
        <tr><td><div class="who"><?= ops_avatar($r['staff_id'], $r['name']) ?><div><b><?= h($r['name']) ?></b><span><?= h($r['role']) ?> · <?= h($r['office']) ?></span></div></div></td>
          <td colspan="13"><span class="pill draft"><i class="fa fa-circle-exclamation"></i>Salary not set</span> <a class="small" href="ops_salaries.php?q=<?= urlencode($r['name']) ?>">Set salary</a></td></tr>
      <?php else: ?>
        <tr class="click" onclick="location.href='ops_slip.php?month=<?= substr($month, 0, 7) ?>&staff_id=<?= $r['staff_id'] ?>'">
          <td><div class="who"><?= ops_avatar($r['staff_id'], $r['name']) ?><div><b><?= h($r['name']) ?></b><span><?= h($r['role']) ?> · <?= h($r['office']) ?></span></div></div></td>
          <td class="right num"><?= ops_inr($r['salary']) ?></td>
          <td class="center num in"><?= $c['present'] ?></td>
          <td class="center num"><?= $c['paid_leave'] ?></td>
          <td class="center num"><?= $c['week_off'] ?></td>
          <td class="center num out"><?= $c['absent_total'] ?><?php if (!$r['finalized'] && $c['unmarked']): ?><div class="small unmarked-tag"><?= $c['unmarked'] ?> not marked</div><?php endif; ?></td>
          <td class="center num"><b><?= $c['payable'] ?></b><span class="muted">/<?= $days ?></span></td>
          <td class="center num" style="color:var(--exd)"><?= $c['extra_duty'] ?: '—' ?></td>
          <td class="right num"><?= ops_inr($r['earned']) ?></td>
          <td class="right num" style="color:var(--exd)"><?= $r['extra_pay'] ? '+' . ops_inr($r['extra_pay']) : '—' ?></td>
          <td class="right num <?= $r['advance_deduction'] ? 'out' : 'muted' ?>"><?= $r['advance_deduction'] ? '−' . ops_inr($r['advance_deduction']) : '—' ?>
            <?php if (!empty($r['advance_carry'])): ?><div class="small" style="color:var(--warn)"><?= ops_inr($r['advance_carry']) ?> carried forward</div><?php endif; ?></td>
          <td class="right num <?= $r['hand_settlement'] > 0 ? 'out' : ($r['hand_settlement'] < 0 ? 'in' : 'muted') ?>"><?= $r['hand_settlement'] ? ($r['hand_settlement'] > 0 ? '−' : '+') . ops_inr(abs($r['hand_settlement'])) : '—' ?>
            <?php if (!empty($r['hand_carry']) && $r['hand_carry'] > 0): ?><div class="small" style="color:var(--warn)"><?= ops_inr($r['hand_carry']) ?> stays in hand</div><?php endif; ?></td>
          <td class="right num"><b><?= ops_inr($r['net']) ?></b></td>
          <td class="right no-print" onclick="event.stopPropagation()">
            <?php if ($r['finalized'] && $r['paid']): $pd = $r['paid']; ?>
              <span class="pill final" title="Paid <?= date('j M Y', strtotime($pd['pay_date'])) ?><?= $pd['mode'] ? ' by ' . h(OPS_PAY_MODES[$pd['mode']]) : '' ?><?= $pd['reference'] ? ' · ' . h($pd['reference']) : '' ?>"><i class="fa fa-circle-check"></i>Paid <?= date('j M', strtotime($pd['pay_date'])) ?></span>
              <?php if ($canPay): ?><a class="small muted" href="javascript:void(0)" style="margin-left:6px" onclick="undoPaid(<?= (int)$r['slip_id'] ?>, <?= h(json_encode($r['name'])) ?>)">Undo</a><?php endif; ?>
            <?php elseif ($r['finalized']): ?>
              <span class="pill draft" title="Finalized by <?= h($r['finalized_by']) ?> on <?= date('j M Y', strtotime($r['finalized_at'])) ?>"><i class="fa fa-lock"></i>Unpaid</span>
              <?php if ($canPay): ?><button class="btn sm success" style="margin-left:6px" onclick='payOne(<?= h(json_encode(array('slip' => (int)$r['slip_id'], 'name' => $r['name'], 'net' => ops_inr($r['net']), 'adv' => $r['advance_deduction'] ? ops_inr($r['advance_deduction']) : '', 'hs' => (int)$r['hand_settlement'], 'hsTxt' => ops_inr(abs((int)$r['hand_settlement']))))) ?>)'><i class="fa fa-indian-rupee-sign"></i>Mark paid</button><?php endif; ?>
            <?php elseif ($canFinalizeOne): ?><button class="btn sm" onclick='finalizeOne(<?= h(json_encode(array(
                'id' => $r['staff_id'], 'name' => $r['name'], 'p' => $c['present'], 'l' => $c['paid_leave'], 'w' => $c['week_off'],
                'a' => $c['absent_total'], 'u' => $c['unmarked'], 'pay' => $c['payable'], 'salary' => ops_inr($r['salary']),
                'earned' => ops_inr($r['earned']), 'x' => $c['extra_duty'], 'extra' => ops_inr($r['extra_pay']), 'gross' => ops_inr($r['gross']), 'adv' => $r['advance_deduction'] ? ops_inr($r['advance_deduction']) : '', 'absent' => ops_inr($r['absent_deduction']),
                'hs' => (int)$r['hand_settlement'], 'hsTxt' => ops_inr(abs((int)$r['hand_settlement'])), 'hcarry' => !empty($r['hand_carry']) && $r['hand_carry'] > 0 ? ops_inr($r['hand_carry']) : '', 'carry' => !empty($r['advance_carry']) ? ops_inr($r['advance_carry']) : '',
                'net' => ops_inr($r['net'])))) ?>)'><i class="fa fa-lock"></i>Finalize</button>
            <?php else: ?><span class="pill grey">Draft</span><?php endif; ?></td>
        </tr>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="14" class="empty">No staff for this month.</td></tr><?php endif; ?>
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
    (d.x ? '<tr><td>Extra duty <span class="muted small">(' + d.x + ' × per-day salary)</span></td><td class="right num" style="color:var(--exd)">+' + e(d.extra) + '</td></tr>' +
           '<tr><td><b>Gross</b></td><td class="right num"><b>' + e(d.gross) + '</b></td></tr>' : '') +
    '<tr><td>Advance deducted</td><td class="right num out">' + (d.adv ? '−' + e(d.adv) : '₹0') + '</td></tr>' +
    (d.hs > 0 ? '<tr><td>Cash in hand (company cash with staff)' + (d.hcarry ? ' <span class="muted small">· ' + e(d.hcarry) + ' stays in hand</span>' : '') + '</td><td class="right num out">−' + e(d.hsTxt) + '</td></tr>'
     : d.hs < 0 ? '<tr><td>Road expense owed to staff</td><td class="right num in">+' + e(d.hsTxt) + '</td></tr>' : '') +
    (d.carry ? '<tr><td class="small" style="color:var(--warn)">Advance still due, carried to next month</td><td class="right num small" style="color:var(--warn)">' + e(d.carry) + '</td></tr>' : '') +
    '</tbody></table>' +
    '<div class="calc mt"><span>Net pay</span><b class="num">' + e(d.net) + '</b></div>' +
    (d.u >= DAYS
      ? '<div class="note err mt"><i class="fa fa-triangle-exclamation"></i><div><b>No attendance is marked for any day of ' + e(MONTH) + '.</b> Net pay will be ₹0. <a href="ops_register.php?month=' + MONTH_YM + '&q=' + encodeURIComponent(d.name) + '">Mark attendance first →</a></div></div>' +
        '<label class="mt" style="display:flex;gap:8px;align-items:center"><input type="checkbox" onchange="document.getElementById(&quot;finGo&quot;).disabled=!this.checked"> I understand ' + e(d.name) + ' will be paid ₹0 for ' + e(MONTH) + '</label>'
      : (d.u ? '<div class="note warn mt"><i class="fa fa-circle-exclamation"></i><div>' + d.u + ' day(s) are not marked and will be paid as <b>absent</b>. Mark them first if that is wrong.</div></div>' : '')) +
    '<p class="small muted">This saves and prints the salary slip and <b>locks ' + e(d.name) + '’s attendance for ' + e(MONTH) + '</b>. The advance and hand balance are settled later, when you click <b>Mark paid</b>.</p></div>' +
    '<div class="foot"><button class="btn" onclick="OPS.close()">Cancel</button><button class="btn primary" id="finGo"' + (d.u >= DAYS ? ' disabled' : '') + ' onclick="this.disabled=true;submitFin(\'finalize_staff\',' + d.id + ')">Finalize ' + e(d.name.split(' ')[0]) + '</button></div>');
}
<?php if ($canCloseMonth): ?>
function confirmFinalize() {
  OPS.open('<div class="card-h"><h3><i class="fa fa-lock"></i> Close <?= $monthLabel ?>?</h3></div><div class="card-b">' +
    '<p style="margin-top:0">This covers <b>all branches</b> and will:</p><ul>' +
    '<li>save salary slips for the <b><?= $tot['pending'] ?> staff</b> not finalized yet (net <?= ops_inr($tot['pendingNet']) ?>), each calculated from their attendance and leave,</li>' +
    '<?= $tot['done'] ? '<li>keep the ' . $tot['done'] . ' slips already finalized as they are,</li>' : '' ?>' +
    '<li><b>lock <?= $monthLabel ?> attendance</b> for everyone.</li></ul><p class="small muted">Advances and hand balances are settled later, staff by staff, when you click <b>Mark paid</b>.</p>' +
    '<?= $tot['nomark'] ? '<div class="note err"><i class="fa fa-triangle-exclamation"></i><div><b>' . $tot['nomark'] . ' staff have no attendance for the whole month</b> and will be paid ₹0.</div></div>' : '' ?>' +
    '<?= $tot['unmarked'] ? '<div class="note warn mt"><i class="fa fa-circle-exclamation"></i><div>' . $tot['unmarked'] . ' staff have unmarked days, which will be paid as absent.</div></div>' : '' ?>' +
    '<?= $tot['missing'] ? '<div class="note warn mt"><i class="fa fa-circle-exclamation"></i><div>' . $tot['missing'] . ' staff have no salary set and will be skipped.</div></div>' : '' ?>' +
    '<p class="small muted">This cannot be undone from the screen.</p></div>' +
    '<div class="foot"><button class="btn" onclick="OPS.close()">Cancel</button><?= $tot['nomark'] ? '<label style="display:flex;gap:8px;align-items:center;margin-right:auto"><input type="checkbox" onchange="document.getElementById(&quot;closeGo&quot;).disabled=!this.checked"> I understand ' . $tot['nomark'] . ' staff will get ₹0</label>' : '' ?><button class="btn primary" id="closeGo"<?= $tot['nomark'] ? ' disabled' : '' ?> onclick="this.disabled=true;submitFin(\'finalize\')">Close month</button></div>');
}
<?php endif; ?>
</script>
<?php endif; ?>
<?php if ($canPay): ?>
<form method="post" id="paidForm"><?= ops_csrf_field() ?><input type="hidden" name="action" id="paidAction" value="mark_paid"><input type="hidden" name="slip_id" id="paidSlip">
  <input type="hidden" name="pay_date" id="paidDate"><input type="hidden" name="mode" id="paidMode"><input type="hidden" name="reference" id="paidRef"><input type="hidden" name="note" id="paidNote"><input type="hidden" name="reason" id="paidReason"><input type="hidden" name="office_id" value="<?= $officeId ?>"></form>
<script>
var PAY_MIN = <?= json_encode(date('Y-m-t', strtotime($month))) ?>, TODAY = <?= json_encode(date('Y-m-d')) ?>, PAY_MODES = <?= json_encode(OPS_PAY_MODES) ?>;
function payOne(d) {
  var e = OPS.esc, opts = '<option value="">Choose…</option>';
  for (var k in PAY_MODES) opts += '<option value="' + k + '">' + PAY_MODES[k] + '</option>';
  OPS.open('<div class="card-h"><h3><i class="fa fa-indian-rupee-sign"></i> Mark paid · ' + e(d.name) + '</h3></div><div class="card-b">' +
    '<div class="calc"><span>Net pay handed over</span><b class="num">' + e(d.net) + '</b></div>' +
    '<p class="small muted" style="margin:10px 0">Marking Paid will now:</p><ul class="small" style="margin-top:0">' +
    (d.adv ? '<li>record the advance recovery of <b>' + e(d.adv) + '</b></li>' : '') +
    (d.hs > 0 ? '<li>settle <b>' + e(d.hsTxt) + '</b> cash in hand (hand balance goes down)</li>' : d.hs < 0 ? '<li>settle <b>' + e(d.hsTxt) + '</b> owed to the staff member (hand balance goes up)</li>' : '') +
    '<li>stamp the salary slip <b>PAID</b></li></ul>' +
    '<div class="form-grid"><div class="field"><label>Payment date</label><input class="input" type="date" id="pDate" value="' + TODAY + '" min="' + PAY_MIN + '" max="' + TODAY + '"></div>' +
    '<div class="field"><label>Mode</label><select class="input" id="pMode">' + opts + '</select></div>' +
    '<div class="field full"><label>Reference <span class="muted small">(UTR / cheque no, optional)</span></label><input class="input" id="pRef" maxlength="100"></div>' +
    '<div class="field full"><label>Note <span class="muted small">(optional)</span></label><input class="input" id="pNote" maxlength="255"></div></div></div>' +
    '<div class="foot"><button class="btn" onclick="OPS.close()">Cancel</button><button class="btn success" onclick="sendPaid(' + d.slip + ', this)">Mark paid</button></div>');
}
function sendPaid(slip, btn) {
  var mode = document.getElementById('pMode').value;
  if (!mode) { OPS.toast('Choose how the salary was paid', true); return; }
  btn.disabled = true;
  document.getElementById('paidAction').value = 'mark_paid';
  document.getElementById('paidSlip').value = slip;
  document.getElementById('paidDate').value = document.getElementById('pDate').value;
  document.getElementById('paidMode').value = mode;
  document.getElementById('paidRef').value = document.getElementById('pRef').value;
  document.getElementById('paidNote').value = document.getElementById('pNote').value;
  document.getElementById('paidForm').submit();
}
function undoPaid(slip, name) {
  var r = prompt('Undo the salary payment for ' + name + '?\nThe advance recovery and hand-balance settlement will be reversed.\n\nReason:');
  if (!r) return;
  document.getElementById('paidAction').value = 'undo_paid';
  document.getElementById('paidSlip').value = slip;
  document.getElementById('paidReason').value = r;
  document.getElementById('paidForm').submit();
}
</script>
<?php endif; ?>
<?php require 'opsdesk/layout_end.php'; ?>
