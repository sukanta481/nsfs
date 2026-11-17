<?php
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
requirePermission('ops_attendance_view');
require 'opsdesk/bootstrap.php';

$thisMonth = date('Y-m-01');
$month = ops_month_param('month', $thisMonth);
if ($month > $thisMonth) $month = $thisMonth;
$filters = array(
    'office_id' => isset($_GET['office_id']) ? (int)$_GET['office_id'] : 0,
    'role'      => isset($_GET['role']) ? trim($_GET['role']) : '',
    'q'         => isset($_GET['q']) ? trim(mb_substr($_GET['q'], 0, 60)) : '',
);
$shows = array(
    ''         => 'Everyone',
    'absent'   => 'Has absences',
    'unmarked' => 'Has unmarked days',
    'leave'    => 'Took paid leave',
    'full'     => 'Full attendance',
);
$show = isset($_GET['show'], $shows[$_GET['show']]) ? $_GET['show'] : '';
$days = ops_days_in_month($month);
$grid = ops_attendance_for_month($month);
$staffAll = ops_staff_list($filters, $month);

// "show only" filter works on the month's counts (days up to today only for the current month)
$countDays = ($month === $thisMonth) ? (int)date('j') : $days;
$staff = array();
foreach ($staffAll as $s) {
    $g = isset($grid[(int)$s['staff_id']]) ? $grid[(int)$s['staff_id']] : array();
    $c = ops_count_attendance($g, $countDays);
    $keep = true;
    if ($show === 'absent')   $keep = $c['absent_total'] > 0;
    if ($show === 'unmarked') $keep = $c['unmarked'] > 0;
    if ($show === 'leave')    $keep = $c['paid_leave'] > 0;
    if ($show === 'full')     $keep = $c['absent_total'] === 0;
    if ($keep) $staff[] = $s;
}
$qs = function ($extra) use ($filters, $show, $month) {
    return '?' . http_build_query(array_filter($filters + array('show' => $show, 'month' => substr($month, 0, 7))) + $extra);
};
$filtered = $filters['office_id'] || $filters['role'] !== '' || $filters['q'] !== '' || $show !== '';
$totalStaff = $filtered ? count(ops_staff_list(array(), $month)) : count($staffAll);
$locked = ops_month_locked($month);
$canMark = hasPermission('ops_attendance_mark') && !$locked;
$doneStaff = ops_finalized_staff($month);
$today = date('Y-m-d');
$letters = array('present' => 'P', 'paid_leave' => 'L', 'week_off' => 'W', 'absent' => 'A');
$prev = date('Y-m', strtotime($month . ' -1 month'));
$next = date('Y-m', strtotime($month . ' +1 month'));

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="attendance-' . substr($month, 0, 7) . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $head = array('Staff ID', 'Name', 'Role', 'Branch');
    for ($d = 1; $d <= $days; $d++) $head[] = $d;
    fputcsv($out, array_merge($head, array('Present', 'Paid leave', 'Week off', 'Absent', 'Payable days')));
    foreach ($staff as $s) {
        $sid = (int)$s['staff_id'];
        $row = array($s['staff_unique_id'], $s['staff_name'], $s['staff_role'], $s['office_name']);
        $g = isset($grid[$sid]) ? $grid[$sid] : array();
        for ($d = 1; $d <= $days; $d++) $row[] = isset($g[$d]) ? $letters[$g[$d]] : '';
        $c = ops_count_attendance($g, $days);
        fputcsv($out, array_merge($row, array($c['present'], $c['paid_leave'], $c['week_off'], $c['absent_total'], $c['payable'])));
    }
    exit;
}

$ops_title = 'Monthly Register';
require 'opsdesk/layout_start.php';
?>
<div class="page-head">
  <div><h1>Monthly Register</h1><p><?= date('F Y', strtotime($month)) ?> · <?= $days ?> days · <?= count($staff) ?><?= $filtered ? ' of ' . $totalStaff : '' ?> staff</p></div>
  <div class="actions no-print">
    <a class="btn" href="<?= h($qs(array('export' => 1))) ?>"><i class="fa fa-file-csv"></i>Export</a>
    <button type="button" class="btn" onclick="window.print()"><i class="fa fa-print"></i>Print</button>
  </div>
</div>
<div class="card no-print" style="margin-bottom:14px">
  <form class="card-h" method="get" style="flex-wrap:wrap;gap:10px">
    <div class="datebox">
      <a href="<?= h($qs(array('month' => $prev))) ?>" title="Previous month"><i class="fa fa-chevron-left"></i></a>
      <input type="month" name="month" value="<?= substr($month, 0, 7) ?>" max="<?= substr($thisMonth, 0, 7) ?>" onchange="this.form.submit()">
      <?php if ($month < $thisMonth): ?><a href="<?= h($qs(array('month' => $next))) ?>" title="Next month"><i class="fa fa-chevron-right"></i></a><?php endif; ?>
    </div>
    <select class="input auto" name="office_id" onchange="this.form.submit()">
      <option value="">All branches</option>
      <?php foreach (ops_offices() as $o): ?><option value="<?= $o['office_id'] ?>" <?= $filters['office_id'] == $o['office_id'] ? 'selected' : '' ?>><?= h($o['office_name']) ?></option><?php endforeach; ?>
    </select>
    <select class="input auto" name="role" onchange="this.form.submit()">
      <option value="">All staff types</option>
      <?php foreach (ops_staff_types() as $t): ?><option <?= $filters['role'] === $t['type_name'] ? 'selected' : '' ?>><?= h($t['type_name']) ?></option><?php endforeach; ?>
    </select>
    <select class="input auto" name="show" onchange="this.form.submit()">
      <?php foreach ($shows as $k => $label): ?><option value="<?= $k ?>" <?= $show === $k ? 'selected' : '' ?>><?= $k === '' ? 'Show: everyone' : h($label) ?></option><?php endforeach; ?>
    </select>
    <input class="input auto" style="width:200px" name="q" value="<?= h($filters['q']) ?>" placeholder="Search name, ID, phone">
    <button class="btn"><i class="fa fa-magnifying-glass"></i>Search</button>
    <?php if ($filtered): ?><a class="btn ghost" href="?month=<?= substr($month, 0, 7) ?>"><i class="fa fa-xmark"></i>Clear filters</a><?php endif; ?>
  </form>
</div>
<div class="card">
  <div class="card-h">
    <div class="legend"><span><i class="c-P">P</i>Present</span><span><i class="c-L">L</i>Paid leave</span><span><i class="c-W">W</i>Week off</span><span><i class="c-A">A</i>Absent</span><span><i class="c-U" style="border:1px solid var(--line)">·</i>Not marked = absent</span></div>
    <div class="r"><?= $locked ? '<span class="pill final"><i class="fa fa-lock"></i>Payroll finalized · locked</span>' : '<span class="pill draft"><i class="fa fa-lock-open"></i>Editable until payroll is finalized</span>' ?></div>
  </div>
  <?php if (!$staff): ?><div class="empty"><?= $filtered ? 'No staff match these filters. <a href="?month=' . substr($month, 0, 7) . '">Clear filters</a>' : 'No staff for this month.' ?></div><?php else: ?>
  <div class="reg-wrap"><table class="reg">
    <thead><tr><th class="name">Staff</th>
      <?php for ($d = 1; $d <= $days; $d++): $ts = strtotime(substr($month, 0, 8) . sprintf('%02d', $d)); ?>
        <th class="<?= date('w', $ts) == 0 ? 'sun' : '' ?>"><?= $d ?><br><span style="font-weight:500"><?= substr(date('D', $ts), 0, 1) ?></span></th>
      <?php endfor; ?>
      <th>P</th><th>L</th><th>W</th><th>A</th><th>Payable</th></tr></thead>
    <tbody>
    <?php foreach ($staff as $s): $sid = (int)$s['staff_id']; $g = isset($grid[$sid]) ? $grid[$sid] : array(); $c = ops_count_attendance($g, $days); ?>
      <tr><td class="name"><?= h($s['staff_name']) ?><?= isset($doneStaff[$sid]) ? ' <i class="fa fa-lock muted" title="Payroll finalized"></i>' : '' ?><div class="muted small" style="font-weight:500"><?= h($s['staff_role']) ?> · <?= h($s['office_name']) ?></div></td>
      <?php for ($d = 1; $d <= $days; $d++):
          $date = substr($month, 0, 8) . sprintf('%02d', $d);
          $k = isset($g[$d]) ? $letters[$g[$d]] : ($date > $today ? 'F' : 'U');
          $txt = $k === 'U' ? '·' : ($k === 'F' ? '' : $k); ?>
        <td><?php if ($canMark && !isset($doneStaff[$sid]) && $k !== 'F'): ?><button type="button" class="cell c-<?= $k ?>" onclick="pick(event, <?= $sid ?>, '<?= $date ?>', this)" title="<?= h($s['staff_name']) ?> · <?= date('j M', strtotime($date)) ?>"><?= $txt ?></button>
            <?php else: ?><span class="cell c-<?= $k ?>"><?= $txt ?></span><?php endif; ?></td>
      <?php endfor; ?>
        <td class="tot in"><?= $c['present'] ?></td><td class="tot"><?= $c['paid_leave'] ?></td><td class="tot"><?= $c['week_off'] ?></td>
        <td class="tot out"><?= $c['absent_total'] ?></td><td class="tot"><?= $c['payable'] ?>/<?= $days ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
<?php if ($canMark): ?><p class="small muted no-print">Click any day to change it.</p><?php endif; ?>

<div class="picker" id="picker" style="display:none">
  <div class="seg">
    <button type="button" class="present" data-s="present">P</button><button type="button" class="paid_leave" data-s="paid_leave">L</button>
    <button type="button" class="week_off" data-s="week_off">W</button><button type="button" class="absent" data-s="absent">A</button>
  </div>
</div>
<script>
var pickCtx = null, LET = {present:'P', paid_leave:'L', week_off:'W', absent:'A'};
function pick(e, sid, date, cell) {
  e.stopPropagation();
  var p = document.getElementById('picker'), r = cell.getBoundingClientRect(), host = document.querySelector('.ops').getBoundingClientRect();
  pickCtx = {sid: sid, date: date, cell: cell};
  p.style.display = 'block';
  p.style.left = (r.left - host.left - 60) + 'px';
  p.style.top = (r.bottom - host.top + 4) + 'px';
}
document.getElementById('picker').addEventListener('click', function (e) {
  var s = e.target.dataset.s; if (!s || !pickCtx) return;
  var ctx = pickCtx;
  OPS.api('attendance_set', {staff_id: ctx.sid, date: ctx.date, status: s}).then(function () {
    location.reload();
  }).catch(function (err) { OPS.toast(err.message, true); });
  this.style.display = 'none';
});
document.addEventListener('click', function () { document.getElementById('picker').style.display = 'none'; });
</script>
<?php require 'opsdesk/layout_end.php'; ?>
