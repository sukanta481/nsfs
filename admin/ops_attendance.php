<?php
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
requirePermission('ops_attendance_view');
require 'opsdesk/bootstrap.php';

$today = date('Y-m-d');
$date = ops_date_param('date', $today);
if ($date > $today) $date = $today;
$filters = array(
    'office_id' => isset($_GET['office_id']) ? (int)$_GET['office_id'] : 0,
    'role' => isset($_GET['role']) ? trim($_GET['role']) : '',
    'q' => isset($_GET['q']) ? trim($_GET['q']) : '',
);
$staff = ops_staff_list($filters);
$att = ops_attendance_for_date($date);
$locked = ops_month_locked($date);
$canMark = hasPermission('ops_attendance_mark') && !$locked;
$doneStaff = ops_finalized_staff($date); // staff whose payroll for this month is already finalized
$dow = (int)date('w', strtotime($date));
$days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

$counts = array('present' => 0, 'paid_leave' => 0, 'week_off' => 0, 'absent' => 0, 'un' => 0);
$unmarkedIds = array();
$weekOffDue = array();
foreach ($staff as $s) {
    $sid = (int)$s['staff_id'];
    if (isset($att[$sid])) {
        $counts[$att[$sid]['status']]++;
    } else {
        $counts['un']++;
        if (isset($doneStaff[$sid])) continue; // payroll already finalized: cannot be marked
        $unmarkedIds[] = $sid;
        if ($s['week_off_day'] !== null && (int)$s['week_off_day'] === $dow) $weekOffDue[] = $s;
    }
}
$statusMeta = array(
    'present' => array('P', 'Present'), 'paid_leave' => array('L', 'Paid leave'),
    'week_off' => array('W', 'Week off'), 'absent' => array('A', 'Absent'),
);
$prev = date('Y-m-d', strtotime($date . ' -1 day'));
$next = date('Y-m-d', strtotime($date . ' +1 day'));
$qs = function ($d) use ($filters) { return '?' . http_build_query(array_filter($filters) + array('date' => $d)); };

$ops_title = 'Daily Attendance';
require 'opsdesk/layout_start.php';
?>
<div class="page-head">
  <div><h1>Daily Attendance</h1><p>One status per staff per day. An unmarked day counts as <b>absent</b>.</p></div>
  <div class="actions"><a class="btn" href="ops_register.php?month=<?= substr($date, 0, 7) ?>"><i class="fa fa-table-cells"></i>Monthly register</a></div>
</div>

<?php if ($locked): ?>
  <div class="note warn" style="margin-bottom:14px"><i class="fa fa-lock"></i><div>Payroll for <b><?= date('F Y', strtotime($date)) ?></b> is finalized, so this day is locked.</div></div>
<?php endif; ?>

<div class="card">
  <form class="card-h" method="get">
    <div class="datebox">
      <a href="<?= h($qs($prev)) ?>" title="Previous day"><i class="fa fa-chevron-left"></i></a>
      <input type="date" name="date" value="<?= h($date) ?>" max="<?= $today ?>" onchange="this.form.submit()">
      <?php if ($date < $today): ?><a href="<?= h($qs($next)) ?>" title="Next day"><i class="fa fa-chevron-right"></i></a><?php endif; ?>
    </div>
    <strong><?= $days[$dow] ?></strong>
    <select class="input auto" name="office_id" onchange="this.form.submit()">
      <option value="">All branches</option>
      <?php foreach (ops_offices() as $o): ?><option value="<?= $o['office_id'] ?>" <?= $filters['office_id'] == $o['office_id'] ? 'selected' : '' ?>><?= h($o['office_name']) ?></option><?php endforeach; ?>
    </select>
    <select class="input auto" name="role" onchange="this.form.submit()">
      <option value="">All staff types</option>
      <?php foreach (ops_staff_types() as $t): ?><option <?= $filters['role'] === $t['type_name'] ? 'selected' : '' ?>><?= h($t['type_name']) ?></option><?php endforeach; ?>
    </select>
    <input class="input auto" style="width:200px" name="q" value="<?= h($filters['q']) ?>" placeholder="Search name, ID, phone">
    <div class="r">
      <?php if ($canMark && $unmarkedIds): ?>
      <button type="button" class="btn success" onclick="bulk('present', <?= h(json_encode(implode(',', $unmarkedIds))) ?>, <?= count($unmarkedIds) ?>)"><i class="fa fa-check-double"></i>Mark <?= count($unmarkedIds) ?> unmarked as Present</button>
      <?php endif; ?>
    </div>
  </form>
  <div class="card-b" style="padding:14px 18px;border-bottom:1px solid var(--line2)">
    <div class="chips">
      <div class="chip"><span class="dot" style="background:var(--present)"></span>Present <b><?= $counts['present'] ?></b></div>
      <div class="chip"><span class="dot" style="background:var(--leave)"></span>Paid leave <b><?= $counts['paid_leave'] ?></b></div>
      <div class="chip"><span class="dot" style="background:var(--off)"></span>Week off <b><?= $counts['week_off'] ?></b></div>
      <div class="chip"><span class="dot" style="background:var(--absent)"></span>Absent <b><?= $counts['absent'] ?></b></div>
      <div class="chip unmarked"><i class="fa fa-circle-exclamation"></i>Not marked <b><?= $counts['un'] ?></b></div>
    </div>
    <?php if ($canMark && $weekOffDue): ?>
      <div class="note info mt"><i class="fa fa-lightbulb"></i><div>
        <?= h(implode(', ', array_map(function ($s) { return $s['staff_name']; }, array_slice($weekOffDue, 0, 6)))) ?><?= count($weekOffDue) > 6 ? ' and ' . (count($weekOffDue) - 6) . ' more' : '' ?>
        usually <?= count($weekOffDue) > 1 ? 'have' : 'has' ?> <b><?= $days[$dow] ?></b> as week off.
        <a href="javascript:void(0)" onclick="bulk('week_off', <?= h(json_encode(implode(',', array_map(function ($s) { return $s['staff_id']; }, $weekOffDue)))) ?>, <?= count($weekOffDue) ?>)">Mark them as week off</a>
      </div></div>
    <?php endif; ?>
  </div>

  <?php if (!$staff): ?>
    <div class="empty">No active staff match these filters.</div>
  <?php else: ?>
  <table class="t att-table">
    <thead><tr><th>Staff</th><th class="hide-m">Branch</th><th>Status</th><th class="hide-m">Remarks</th></tr></thead>
    <tbody>
    <?php foreach ($staff as $s): $sid = (int)$s['staff_id']; $cur = isset($att[$sid]) ? $att[$sid]['status'] : null; $rowMark = $canMark && !isset($doneStaff[$sid]); ?>
      <tr class="<?= $cur ? '' : 'unmarked' ?>" data-sid="<?= $sid ?>">
        <td><div class="who"><?= ops_avatar($sid, $s['staff_name']) ?><div><b><?= h($s['staff_name']) ?></b>
          <span><?= h($s['staff_role']) ?><?= $s['staff_unique_id'] ? ' · ' . h($s['staff_unique_id']) : '' ?><?= $cur ? '' : ' · <span class="unmarked-tag">not marked</span>' ?></span></div></div></td>
        <td class="hide-m muted"><?= h($s['office_name']) ?></td>
        <td><div class="seg<?= $rowMark ? '' : ' locked' ?>"<?= isset($doneStaff[$sid]) ? ' title="Payroll finalized for this month"' : '' ?>>
          <?php foreach ($statusMeta as $k => $m): ?>
            <button type="button" class="<?= $k ?><?= $cur === $k ? ' on' : '' ?>" title="<?= $m[1] ?>" onclick="mark(<?= $sid ?>,'<?= $k ?>',this)"><?= $m[0] ?></button>
          <?php endforeach; ?>
        </div></td>
        <td class="hide-m"><input class="input" style="max-width:240px" placeholder="<?= $cur ? 'Optional' : 'Mark status first' ?>" value="<?= h(isset($att[$sid]) ? $att[$sid]['remarks'] : '') ?>"
             <?= ($rowMark && $cur) ? '' : 'disabled' ?> data-orig="<?= h(isset($att[$sid]) ? $att[$sid]['remarks'] : '') ?>" onblur="remark(<?= $sid ?>, this)"></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div class="card-b small muted" style="border-top:1px solid var(--line2)">
    <span class="kbd">P</span> Present · <span class="kbd">L</span> Paid leave · <span class="kbd">W</span> Week off · <span class="kbd">A</span> Absent — keys mark the first unmarked person. Changes save instantly.
  </div>
  <?php endif; ?>
</div>

<script>
var OPS_DATE = <?= json_encode($date) ?>;
var LABEL = {present:'Present', paid_leave:'Paid leave', week_off:'Week off', absent:'Absent'};
function mark(sid, status, btn) {
  var row = btn.closest('tr'), seg = btn.parentNode;
  seg.classList.add('saving');
  OPS.api('attendance_set', {staff_id: sid, date: OPS_DATE, status: status}).then(function () {
    Array.prototype.forEach.call(seg.children, function (b) { b.classList.toggle('on', b === btn); });
    if (row.classList.contains('unmarked')) {
      row.classList.remove('unmarked');
      var tag = row.querySelector('.unmarked-tag'); if (tag) tag.parentNode.removeChild(tag);
      var inp = row.querySelector('input'); if (inp) { inp.disabled = false; inp.placeholder = 'Optional'; }
    }
    OPS.toast(row.querySelector('.who b').textContent + ' → ' + LABEL[status]);
    refreshCounts();
  }).catch(function (e) { OPS.toast(e.message, true); }).then(function () { seg.classList.remove('saving'); });
}
function remark(sid, input) {
  if (input.value === input.dataset.orig) return;
  OPS.api('attendance_remarks', {staff_id: sid, date: OPS_DATE, remarks: input.value})
    .then(function () { input.dataset.orig = input.value; OPS.toast('Remarks saved'); })
    .catch(function (e) { OPS.toast(e.message, true); });
}
function bulk(status, ids, n) {
  if (!confirm('Mark ' + n + ' staff as ' + LABEL[status] + ' for this day?')) return;
  OPS.api('attendance_bulk', {date: OPS_DATE, status: status, staff_ids: ids})
    .then(function (j) { location.reload(); })
    .catch(function (e) { OPS.toast(e.message, true); });
}
function refreshCounts() {
  var c = {present:0, paid_leave:0, week_off:0, absent:0, un:0};
  document.querySelectorAll('.att-table tbody tr').forEach(function (tr) {
    var on = tr.querySelector('.seg button.on');
    if (!on) c.un++; else c[on.className.split(' ')[0]]++;
  });
  var chips = document.querySelectorAll('.chips .chip b');
  [c.present, c.paid_leave, c.week_off, c.absent, c.un].forEach(function (v, i) { chips[i].textContent = v; });
}
document.addEventListener('keydown', function (e) {
  if (/INPUT|SELECT|TEXTAREA/.test(e.target.tagName) || e.ctrlKey || e.metaKey || e.altKey) return;
  var map = {p:'present', l:'paid_leave', w:'week_off', a:'absent'}, st = map[e.key.toLowerCase()];
  if (!st) return;
  var row = document.querySelector('.att-table tr.unmarked');
  if (row) mark(+row.dataset.sid, st, row.querySelector('.seg button.' + st));
});
</script>
<?php require 'opsdesk/layout_end.php'; ?>
