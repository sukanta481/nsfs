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
    'exd'      => 'Did extra duty',
);
$show = isset($_GET['show'], $shows[$_GET['show']]) ? $_GET['show'] : '';
$days = ops_days_in_month($month);
$grid = ops_attendance_for_month($month);
$detail = ops_attendance_detail_for_month($month);
$exdCount = ops_extra_duty_counts($month);
$staffAll = ops_staff_list($filters, $month);

// "show only" filter works on the month's counts (days up to today only for the current month)
$countDays = ($month === $thisMonth) ? (int)date('j') : $days;
$staff = array();
foreach ($staffAll as $s) {
    $g = isset($grid[(int)$s['staff_id']]) ? $grid[(int)$s['staff_id']] : array();
    $c = ops_count_attendance($g, $countDays, isset($exdCount[(int)$s['staff_id']]) ? $exdCount[(int)$s['staff_id']] : 0);
    $keep = true;
    if ($show === 'absent')   $keep = $c['absent_total'] > 0;
    if ($show === 'unmarked') $keep = $c['unmarked'] > 0;
    if ($show === 'leave')    $keep = $c['paid_leave'] > 0;
    if ($show === 'full')     $keep = $c['absent_total'] === 0;
    if ($show === 'exd')      $keep = $c['extra_duty'] > 0;
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
// register letter for one day: Present Day = D, Present Night = N
$cellCode = function ($cell) use ($letters) {
    if (!$cell) return null;
    if ($cell['status'] === 'present') return $cell['shift'] === 'night' ? 'N' : 'D';
    return $letters[$cell['status']];
};
$prev = date('Y-m', strtotime($month . ' -1 month'));
$next = date('Y-m', strtotime($month . ' +1 month'));

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="attendance-' . substr($month, 0, 7) . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $head = array('Staff ID', 'Name', 'Role', 'Branch');
    for ($d = 1; $d <= $days; $d++) $head[] = $d;
    fputcsv($out, array_merge($head, array('Present', 'Paid leave', 'Week off', 'Absent', 'Payable days', 'Extra duty (EXD)')));
    fputcsv($out, array('', 'D = Present (Day), N = Present (Night), +EXD = extra duty, L = Paid leave, W = Week off, A = Absent, blank = not marked'));
    foreach ($staff as $s) {
        $sid = (int)$s['staff_id'];
        $row = array($s['staff_unique_id'], $s['staff_name'], $s['staff_role'], $s['office_name']);
        $g = isset($grid[$sid]) ? $grid[$sid] : array();
        $dt = isset($detail[$sid]) ? $detail[$sid] : array();
        for ($d = 1; $d <= $days; $d++) {
            $row[] = isset($dt[$d]) ? $cellCode($dt[$d]) . (!empty($dt[$d]['extra']) ? '+EXD' : '') : '';
        }
        $c = ops_count_attendance($g, $days, isset($exdCount[$sid]) ? $exdCount[$sid] : 0);
        fputcsv($out, array_merge($row, array($c['present'], $c['paid_leave'], $c['week_off'], $c['absent_total'], $c['payable'], $c['extra_duty'])));
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
    <div class="legend"><span><i class="c-P">D</i>Present · Day</span><span><i class="c-N">N</i>Present · Night</span><span><i class="c-P" style="position:relative">D<b style="position:absolute;top:-2px;right:1px;font-size:9px;color:var(--exd)">+</b></i>Extra duty</span><span><i class="c-L">L</i>Paid leave</span><span><i class="c-W">W</i>Week off</span><span><i class="c-A">A</i>Absent</span><span><i class="c-U" style="border:1px solid var(--line)">·</i>Not marked = absent</span></div>
    <div class="r"><?= $locked ? '<span class="pill final"><i class="fa fa-lock"></i>Payroll finalized · locked</span>' : '<span class="pill draft"><i class="fa fa-lock-open"></i>Editable until payroll is finalized</span>' ?></div>
  </div>
  <?php if (!$staff): ?><div class="empty"><?= $filtered ? 'No staff match these filters. <a href="?month=' . substr($month, 0, 7) . '">Clear filters</a>' : 'No staff for this month.' ?></div><?php else: ?>
  <div class="reg-wrap"><table class="reg">
    <thead><tr><th class="name">Staff</th>
      <?php for ($d = 1; $d <= $days; $d++): $ts = strtotime(substr($month, 0, 8) . sprintf('%02d', $d)); ?>
        <th class="<?= date('w', $ts) == 0 ? 'sun' : '' ?>"><?= $d ?><br><span style="font-weight:500"><?= substr(date('D', $ts), 0, 1) ?></span></th>
      <?php endfor; ?>
      <th>P</th><th>L</th><th>W</th><th>A</th><th>Payable</th><th title="Extra duty days">EXD</th></tr></thead>
    <tbody>
    <?php foreach ($staff as $s): $sid = (int)$s['staff_id']; $g = isset($grid[$sid]) ? $grid[$sid] : array(); $dt = isset($detail[$sid]) ? $detail[$sid] : array();
          $c = ops_count_attendance($g, $days, isset($exdCount[$sid]) ? $exdCount[$sid] : 0); ?>
      <tr><td class="name"><?= h($s['staff_name']) ?><?= isset($doneStaff[$sid]) ? ' <i class="fa fa-lock muted" title="Payroll finalized"></i>' : '' ?><div class="muted small" style="font-weight:500"><?= h($s['staff_role']) ?> · <?= h($s['office_name']) ?></div></td>
      <?php for ($d = 1; $d <= $days; $d++):
          $date = substr($month, 0, 8) . sprintf('%02d', $d);
          $cell = isset($dt[$d]) ? $dt[$d] : null;
          $code = $cell ? $cellCode($cell) : ($date > $today ? 'F' : 'U');
          $cls = $code === 'D' ? 'P' : $code;
          $txt = $code === 'U' ? '·' : ($code === 'F' ? '' : $code);
          $x = ($cell && !empty($cell['extra'])) ? $cell['extra'] : '';
          $tip = h($s['staff_name']) . ' · ' . date('j M', strtotime($date)) . ($x ? ' · Extra duty (' . ($x === 'day' ? 'Day' : 'Night') . ')' : '');
          $mark = $x ? '<span class="x">+</span>' : ''; ?>
        <td><?php if ($canMark && !isset($doneStaff[$sid]) && $code !== 'F'): ?><button type="button" class="cell c-<?= $cls ?>" onclick="pick(event, <?= $sid ?>, '<?= $date ?>', this)" data-st="<?= $cell ? h($cell['status']) : '' ?>" data-sh="<?= $cell ? h($cell['shift']) : '' ?>" data-x="<?= h($x) ?>" title="<?= $tip ?>"><?= $txt ?><?= $mark ?></button>
            <?php else: ?><span class="cell c-<?= $cls ?>" title="<?= $tip ?>"><?= $txt ?><?= $mark ?></span><?php endif; ?></td>
      <?php endfor; ?>
        <td class="tot in"><?= $c['present'] ?></td><td class="tot"><?= $c['paid_leave'] ?></td><td class="tot"><?= $c['week_off'] ?></td>
        <td class="tot out"><?= $c['absent_total'] ?></td><td class="tot"><?= $c['payable'] ?>/<?= $days ?></td><td class="tot exd"><?= $c['extra_duty'] ?: '' ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
<?php if ($canMark): ?><p class="small muted no-print">Click any day to change it. EXD (extra duty) is only for a Present day, in the other shift.</p><?php endif; ?>

<div class="picker" id="picker" style="display:none">
  <div class="seg">
    <button type="button" class="present" data-s="present" data-sh="day" title="Present · Day">D</button><button type="button" class="present_night" data-s="present" data-sh="night" title="Present · Night">N</button>
    <button type="button" class="paid_leave" data-s="paid_leave">L</button>
    <button type="button" class="week_off" data-s="week_off">W</button><button type="button" class="absent" data-s="absent">A</button>
  </div>
  <div class="row2" id="pickExd">EXD
    <button type="button" data-x="day">Day</button><button type="button" data-x="night">Night</button>
  </div>
</div>
<script>
var pickCtx = null, LET = {present:'P', paid_leave:'L', week_off:'W', absent:'A'};
function pick(e, sid, date, cell) {
  e.stopPropagation();
  var p = document.getElementById('picker'), r = cell.getBoundingClientRect(), host = document.querySelector('.ops').getBoundingClientRect();
  pickCtx = {sid: sid, date: date, cell: cell};
  var st = cell.dataset.st, sh = cell.dataset.sh, x = cell.dataset.x;
  p.querySelectorAll('.seg button').forEach(function (b) {
    b.classList.toggle('on', b.dataset.s === st && (st !== 'present' || b.dataset.sh === sh));
  });
  p.querySelectorAll('#pickExd button').forEach(function (b) {
    b.disabled = st !== 'present' || b.dataset.x === sh;
    b.classList.toggle('on', b.dataset.x === x);
  });
  p.style.display = 'block';
  p.style.left = (r.left - host.left - 60) + 'px';
  p.style.top = (r.bottom - host.top + 4) + 'px';
}
document.getElementById('picker').addEventListener('click', function (e) {
  e.stopPropagation();
  var b = e.target.closest('button'); if (!b || b.disabled || !pickCtx) return;
  var ctx = pickCtx, req;
  if (b.dataset.x) {
    req = OPS.api('attendance_extra', {staff_id: ctx.sid, date: ctx.date, extra: b.classList.contains('on') ? '' : b.dataset.x});
  } else {
    var data = {staff_id: ctx.sid, date: ctx.date, status: b.dataset.s};
    if (b.dataset.s === 'present') data.shift = b.dataset.sh;
    req = OPS.api('attendance_set', data);
  }
  req.then(function () { location.reload(); }).catch(function (err) { OPS.toast(err.message, true); });
  this.style.display = 'none';
});
document.addEventListener('click', function () { document.getElementById('picker').style.display = 'none'; });
</script>
<?php require 'opsdesk/layout_end.php'; ?>
