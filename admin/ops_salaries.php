<?php
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
requirePermission('ops_salary_manage');
require 'opsdesk/bootstrap.php';

$dayNames = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ops_check_csrf();
    $sid = (int)(isset($_POST['staff_id']) ? $_POST['staff_id'] : 0);
    $staff = ops_one('SELECT staff_id, staff_name FROM tbl_staff WHERE staff_id = ?' . ops_office_scope('tbl_staff'), array($sid));
    try {
        if (!$staff) throw new InvalidArgumentException('Staff member not found.');
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        if ($action === 'salary') {
            $paise = ops_paise_from_input(isset($_POST['amount']) ? $_POST['amount'] : '');
            if ($paise === null) throw new InvalidArgumentException('Enter a valid salary amount, e.g. 15000.');
            $from = ops_month_param('effective_month', date('Y-m-01'));
            ops_salary_set($sid, $paise, $from, trim(isset($_POST['note']) ? $_POST['note'] : ''));
            ops_flash($staff['staff_name'] . ': salary ' . ops_inr($paise) . ' from ' . date('F Y', strtotime($from)) . ' saved.');
        } elseif ($action === 'weekoff') {
            $wo = isset($_POST['week_off_day']) && $_POST['week_off_day'] !== '' ? (int)$_POST['week_off_day'] : null;
            if ($wo !== null && ($wo < 0 || $wo > 6)) throw new InvalidArgumentException('Invalid day.');
            ops_write('UPDATE tbl_staff SET week_off_day = ? WHERE staff_id = ?', array($wo, $sid));
            ops_flash($staff['staff_name'] . ': usual week off set to ' . ($wo === null ? 'none' : $dayNames[$wo]) . '.');
        }
    } catch (InvalidArgumentException $e) {
        ops_flash($e->getMessage(), 'err');
    }
    ops_redirect('ops_salaries.php' . (isset($_POST['back']) ? '?' . preg_replace('/[^a-z0-9_=&%-]/i', '', $_POST['back']) : ''));
}

$filter = isset($_GET['show']) ? $_GET['show'] : '';
$staff = ops_staff_list(array('q' => isset($_GET['q']) ? trim($_GET['q']) : ''));
$thisMonth = date('Y-m-01');
$rows = array();
$missing = 0;
foreach ($staff as $s) {
    $cur = ops_salary_for_month((int)$s['staff_id'], $thisMonth, $s['salary']);
    if ($cur === null) $missing++;
    if ($filter === 'missing' && $cur !== null) continue;
    $s['current'] = $cur;
    $s['history'] = ops_rows('SELECT monthly_salary, effective_from FROM tbl_ops_salary_revisions WHERE staff_id = ? ORDER BY effective_from DESC, id DESC LIMIT 3', array($s['staff_id']));
    $rows[] = $s;
}
$back = h($_SERVER['QUERY_STRING']);

$ops_title = 'Salary Setup';
require 'opsdesk/layout_start.php';
?>
<div class="page-head">
  <div><h1>Salary Setup</h1><p>Fixed monthly salary and usual week off for each staff member. A change applies from the month you choose; old months keep their old salary.</p></div>
  <form class="actions" method="get">
    <input class="input auto" style="width:220px" name="q" value="<?= h(isset($_GET['q']) ? $_GET['q'] : '') ?>" placeholder="Search name, ID, phone">
    <a class="btn <?= $filter === 'missing' ? 'primary' : '' ?>" href="?<?= $filter === 'missing' ? '' : 'show=missing' ?>"><i class="fa fa-circle-exclamation"></i>No salary (<?= $missing ?>)</a>
  </form>
</div>
<?php if ($missing): ?>
  <div class="note warn" style="margin-bottom:14px"><i class="fa fa-circle-exclamation"></i><div><b><?= $missing ?> of <?= count($staff) ?> staff have no salary set.</b> They are skipped in payroll until a salary is entered.</div></div>
<?php endif; ?>
<div class="card"><div class="scroll-x"><table class="t">
  <thead><tr><th>Staff</th><th class="hide-m">Branch</th><th class="right">Current salary</th><th>Set new salary</th><th>Usual week off</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $s): $sid = (int)$s['staff_id']; ?>
    <tr>
      <td><div class="who"><?= ops_avatar($sid, $s['staff_name']) ?><div><b><?= h($s['staff_name']) ?></b><span><?= h($s['staff_role']) ?> · <?= h($s['staff_unique_id']) ?></span></div></div></td>
      <td class="hide-m muted"><?= h($s['office_name']) ?></td>
      <td class="right num">
        <?php if ($s['current'] === null): ?><span class="pill draft">Not set</span>
        <?php else: ?><b><?= ops_inr($s['current']) ?></b>
          <?php if ($s['history']): ?><div class="small muted">from <?= date('M Y', strtotime($s['history'][0]['effective_from'])) ?></div><?php endif; ?>
        <?php endif; ?>
      </td>
      <td>
        <form method="post" class="toolbar" style="flex-wrap:nowrap">
          <?= ops_csrf_field() ?><input type="hidden" name="action" value="salary"><input type="hidden" name="staff_id" value="<?= $sid ?>"><input type="hidden" name="back" value="<?= $back ?>">
          <input class="input num" name="amount" inputmode="decimal" placeholder="₹ per month" style="width:120px" required>
          <input class="input" type="month" name="effective_month" value="<?= date('Y-m') ?>" style="width:150px" title="Applies from this month">
          <button class="btn sm primary"><i class="fa fa-check"></i>Save</button>
        </form>
      </td>
      <td>
        <form method="post">
          <?= ops_csrf_field() ?><input type="hidden" name="action" value="weekoff"><input type="hidden" name="staff_id" value="<?= $sid ?>"><input type="hidden" name="back" value="<?= $back ?>">
          <select class="input auto" name="week_off_day" onchange="this.form.submit()">
            <option value="">None</option>
            <?php foreach ($dayNames as $i => $dn): ?><option value="<?= $i ?>" <?= $s['week_off_day'] !== null && (int)$s['week_off_day'] === $i ? 'selected' : '' ?>><?= $dn ?></option><?php endforeach; ?>
          </select>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="5" class="empty">Nothing to show.</td></tr><?php endif; ?>
  </tbody>
</table></div></div>
<p class="small muted">Week off is only a suggestion on the attendance screen. It still has to be marked each day.</p>
<?php require 'opsdesk/layout_end.php'; ?>
