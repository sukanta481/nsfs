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

$counts = array('present' => 0, 'paid_leave' => 0, 'week_off' => 0, 'absent' => 0, 'un' => 0, 'exd' => 0);
$unmarkedIds = array();
$weekOffDue = array();
foreach ($staff as $s) {
    $sid = (int)$s['staff_id'];
    if (isset($att[$sid])) {
        $counts[$att[$sid]['status']]++;
        if (!empty($att[$sid]['extra_duty'])) $counts['exd']++;
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
      <button type="button" class="btn success" onclick="bulk('present', <?= h(json_encode(implode(',', $unmarkedIds))) ?>, <?= count($unmarkedIds) ?>)"><i class="fa fa-check-double"></i>Mark <?= count($unmarkedIds) ?> unmarked as Present (Day)</button>
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
      <div class="chip"><span class="dot exd"></span>Extra duty <b><?= $counts['exd'] ?></b></div>
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
    <thead><tr><th>Staff</th><th class="hide-m">Branch</th><th>Status</th><th>Extra duty</th><th class="hide-m">Remarks</th></tr></thead>
    <tbody>
    <?php foreach ($staff as $s): $sid = (int)$s['staff_id']; $cur = isset($att[$sid]) ? $att[$sid]['status'] : null; $rowMark = $canMark && !isset($doneStaff[$sid]);
          $shift = isset($att[$sid]) ? $att[$sid]['shift'] : null; $exd = isset($att[$sid]) ? $att[$sid]['extra_duty'] : null; ?>
      <tr class="<?= $cur ? '' : 'unmarked' ?>" data-sid="<?= $sid ?>">
        <td><div class="who"><?= ops_avatar($sid, $s['staff_name']) ?><div><b><?= h($s['staff_name']) ?></b>
          <span><?= h($s['staff_role']) ?><?= $s['staff_unique_id'] ? ' · ' . h($s['staff_unique_id']) : '' ?><?= $cur ? '' : ' · <span class="unmarked-tag">not marked</span>' ?></span></div></div></td>
        <td class="hide-m muted"><?= h($s['office_name']) ?></td>
        <td><div class="seg<?= $rowMark ? '' : ' locked' ?>"<?= isset($doneStaff[$sid]) ? ' title="Payroll finalized for this month"' : '' ?>>
          <?php foreach ($statusMeta as $k => $m): ?>
            <button type="button" class="<?= $k ?><?= $cur === $k ? ' on' : '' ?>" title="<?= $m[1] ?>" onclick="<?= $k === 'present' ? 'pickShift(event,' . $sid . ',this)' : 'mark(' . $sid . ',\'' . $k . '\',this)' ?>"><?= $m[0] ?><?php if ($k === 'present'): ?><span class="sh"><?= $cur === 'present' ? ($shift === 'night' ? 'N' : 'D') : '' ?></span><?php endif; ?></button>
          <?php endforeach; ?>
        </div></td>
        <td><div class="seg exd-seg<?= $rowMark ? '' : ' locked' ?><?= $cur === 'present' ? '' : ' off' ?>" data-shift="<?= h($shift) ?>">
          <button type="button" data-x="day" class="<?= $exd === 'day' ? 'on' : '' ?>" <?= ($cur !== 'present' || $shift === 'day') ? 'disabled' : '' ?> title="Extra duty, Day shift" onclick="extra(<?= $sid ?>,'day',this)">EXD·D</button>
          <button type="button" data-x="night" class="<?= $exd === 'night' ? 'on' : '' ?>" <?= ($cur !== 'present' || $shift === 'night') ? 'disabled' : '' ?> title="Extra duty, Night shift" onclick="extra(<?= $sid ?>,'night',this)">EXD·N</button>
        </div></td>
        <td class="hide-m"><input class="input" style="max-width:240px" placeholder="<?= $cur ? 'Optional' : 'Mark status first' ?>" value="<?= h(isset($att[$sid]) ? $att[$sid]['remarks'] : '') ?>"
             <?= ($rowMark && $cur) ? '' : 'disabled' ?> data-orig="<?= h(isset($att[$sid]) ? $att[$sid]['remarks'] : '') ?>" onblur="remark(<?= $sid ?>, this)"></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div class="card-b small muted" style="border-top:1px solid var(--line2)">
    Click <b>P</b> to choose <b>Day (D)</b> or <b>Night (N)</b>. <b>EXD</b> (extra duty) is only for a Present day, in the other shift, and adds one day's salary.<br>
    <span class="kbd">D</span> Present Day · <span class="kbd">N</span> Present Night · <span class="kbd">L</span> Paid leave · <span class="kbd">W</span> Week off · <span class="kbd">A</span> Absent — keys mark the first unmarked person. Changes save instantly.
  </div>
  <?php endif; ?>
</div>

<script>
var OPS_DATE = <?= json_encode($date) ?>;
var LABEL = {present:'Present', paid_leave:'Paid leave', week_off:'Week off', absent:'Absent'};
var SHIFT = {day:'Day', night:'Night'};
function closePop() { var p = document.getElementById('shiftPop'); if (p) p.parentNode.removeChild(p); }
function pickShift(e, sid, btn) {
  e.stopPropagation(); closePop();
  var row = btn.closest('tr'), curShift = btn.classList.contains('on') ? (row.querySelector('.exd-seg').dataset.shift || '') : '';
  var pop = document.createElement('div');
  pop.id = 'shiftPop'; pop.className = 'shift-pop';
  pop.innerHTML = '<button type="button" data-s="day" class="' + (curShift === 'day' ? 'on' : '') + '"><i class="fa fa-sun"></i> Day (D)</button>' +
                  '<button type="button" data-s="night" class="' + (curShift === 'night' ? 'on' : '') + '"><i class="fa fa-moon"></i> Night (N)</button>';
  var host = document.querySelector('.ops').getBoundingClientRect(), r = btn.getBoundingClientRect();
  pop.style.left = (r.left - host.left) + 'px';
  pop.style.top = (r.bottom - host.top + 4) + 'px';
  pop.addEventListener('click', function (ev) {
    ev.stopPropagation();
    var b = ev.target.closest('button'); if (!b) return;
    closePop();
    mark(sid, 'present', btn, b.dataset.s);
  });
  document.querySelector('.ops').appendChild(pop);
}
document.addEventListener('click', closePop);
function mark(sid, status, btn, shift) {
  var row = btn.closest('tr'), seg = btn.parentNode;
  seg.classList.add('saving');
  var data = {staff_id: sid, date: OPS_DATE, status: status};
  if (status === 'present') data.shift = shift || 'day';
  OPS.api('attendance_set', data).then(function (j) {
    Array.prototype.forEach.call(seg.children, function (b) { b.classList.toggle('on', b === btn); });
    var r = j.row || {}, sh = seg.querySelector('button.present .sh');
    if (sh) sh.textContent = r.status === 'present' ? (r.shift === 'night' ? 'N' : 'D') : '';
    syncExtra(row, r);
    if (row.classList.contains('unmarked')) {
      row.classList.remove('unmarked');
      var tag = row.querySelector('.unmarked-tag'); if (tag) tag.parentNode.removeChild(tag);
      var inp = row.querySelector('input'); if (inp) { inp.disabled = false; inp.placeholder = 'Optional'; }
    }
    OPS.toast(row.querySelector('.who b').textContent + ' → ' + LABEL[status] + (status === 'present' ? ' (' + SHIFT[r.shift] + ')' : ''));
    refreshCounts();
  }).catch(function (e) { OPS.toast(e.message, true); }).then(function () { seg.classList.remove('saving'); });
}
function syncExtra(row, r) {
  var es = row.querySelector('.exd-seg');
  es.dataset.shift = r.shift || '';
  es.classList.toggle('off', r.status !== 'present');
  es.querySelectorAll('button').forEach(function (b) {
    b.disabled = r.status !== 'present' || r.shift === b.dataset.x;
    b.classList.toggle('on', !!r.extra_duty && r.extra_duty === b.dataset.x);
  });
}
function extra(sid, x, btn) {
  var row = btn.closest('tr'), turnOff = btn.classList.contains('on');
  OPS.api('attendance_extra', {staff_id: sid, date: OPS_DATE, extra: turnOff ? '' : x}).then(function () {
    btn.parentNode.querySelectorAll('button').forEach(function (b) { b.classList.toggle('on', !turnOff && b === btn); });
    OPS.toast(row.querySelector('.who b').textContent + (turnOff ? ' → extra duty removed' : ' → extra duty (' + SHIFT[x] + ') added: +1 day salary'));
    refreshCounts();
  }).catch(function (e) { OPS.toast(e.message, true); });
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
  var c = {present:0, paid_leave:0, week_off:0, absent:0, un:0, exd:0};
  document.querySelectorAll('.att-table tbody tr').forEach(function (tr) {
    var on = tr.querySelector('.seg:not(.exd-seg) button.on');
    if (!on) c.un++; else c[on.className.split(' ')[0]]++;
    if (tr.querySelector('.exd-seg button.on')) c.exd++;
  });
  var chips = document.querySelectorAll('.chips .chip b');
  [c.present, c.paid_leave, c.week_off, c.absent, c.un, c.exd].forEach(function (v, i) { chips[i].textContent = v; });
}
document.addEventListener('keydown', function (e) {
  if (/INPUT|SELECT|TEXTAREA/.test(e.target.tagName) || e.ctrlKey || e.metaKey || e.altKey) return;
  var k = e.key.toLowerCase(), map = {d:'present', n:'present', l:'paid_leave', w:'week_off', a:'absent'}, st = map[k];
  if (!st) return;
  var row = document.querySelector('.att-table tr.unmarked');
  if (row) mark(+row.dataset.sid, st, row.querySelector('.seg button.' + st), k === 'n' ? 'night' : 'day');
});
</script>
<?php require 'opsdesk/layout_end.php'; ?>
