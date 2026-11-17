<?php
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
requirePermission('ops_advance_manage');
require 'opsdesk/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ops_check_csrf();
    try {
        $sid = (int)$_POST['staff_id'];
        $staff = ops_one('SELECT staff_name FROM tbl_staff WHERE staff_id = ? AND active_status = 1' . ops_office_scope('tbl_staff'), array($sid));
        if (!$staff) throw new InvalidArgumentException('Choose a staff member.');
        $paise = ops_paise_from_input($_POST['amount']);
        if (!$paise) throw new InvalidArgumentException('Enter a valid amount.');
        $date = ops_date_param('entry_date', '');
        if (!$date || $date > date('Y-m-d')) throw new InvalidArgumentException('Choose a valid date (not in the future).');
        ops_advance_add($sid, $date, $paise, trim(mb_substr($_POST['note'], 0, 255)));
        ops_flash('Advance of ' . ops_inr($paise) . ' to ' . $staff['staff_name'] . ' saved. It will be deducted from the ' . date('F Y', strtotime($date)) . ' salary.');
    } catch (InvalidArgumentException $e) {
        ops_flash($e->getMessage(), 'err');
    }
    ops_redirect('ops_advances.php');
}

// outstanding per staff (branch-scoped)
$outstanding = array();
$rows = ops_rows(
    'SELECT a.staff_id, a.entry_type, a.amount, o.entry_type AS orig_type
     FROM tbl_ops_staff_advances a JOIN tbl_staff s ON s.staff_id = a.staff_id
     LEFT JOIN tbl_ops_staff_advances o ON o.id = a.reversal_of_id WHERE 1=1' . ops_office_scope('s')
);
foreach ($rows as $r) {
    $sid = (int)$r['staff_id'];
    if (!isset($outstanding[$sid])) $outstanding[$sid] = 0;
    $outstanding[$sid] += ops_advance_sign($r['entry_type'], $r['orig_type']) * ops_paise_from_db($r['amount']);
}
$outstanding = array_filter($outstanding);
$staffById = array();
foreach (ops_staff_list() as $s) $staffById[(int)$s['staff_id']] = $s;

$entries = ops_rows(
    'SELECT a.*, s.staff_name, (SELECT COUNT(*) FROM tbl_ops_staff_advances x WHERE x.reversal_of_id = a.id) AS is_reversed
     FROM tbl_ops_staff_advances a JOIN tbl_staff s ON s.staff_id = a.staff_id
     WHERE 1=1' . ops_office_scope('s') . ' ORDER BY a.entry_date DESC, a.id DESC LIMIT 200'
);

$ops_title = 'Salary Advances';
require 'opsdesk/layout_start.php';
?>
<div class="page-head">
  <div><h1>Salary Advances</h1><p>Money a staff member borrows against this month's salary. It is deducted in full when that month's payroll is finalized.</p></div>
  <div class="actions"><button class="btn primary" onclick="OPS.open(document.getElementById('advForm').innerHTML)"><i class="fa fa-plus"></i>Give advance</button></div>
</div>

<?php if ($outstanding): ?>
<div class="grid g3">
  <?php foreach ($outstanding as $sid => $v): $s = isset($staffById[$sid]) ? $staffById[$sid] : array('staff_name' => '#' . $sid, 'staff_role' => '', 'office_name' => ''); ?>
  <div class="card kpi"><div class="who"><?= ops_avatar($sid, $s['staff_name']) ?><div><b><?= h($s['staff_name']) ?></b><span><?= h($s['staff_role']) ?> · <?= h($s['office_name']) ?></span></div></div>
    <div class="val num <?= $v > 0 ? 'out' : 'in' ?>" style="font-size:22px"><?= ops_inr(abs($v)) ?></div><div class="hint"><?= $v > 0 ? 'outstanding · deducted at month end' : 'over-recovered' ?></div></div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="note ok"><i class="fa fa-circle-check"></i><div>No advances outstanding.</div></div>
<?php endif; ?>

<div class="card mt">
  <div class="card-h"><h3>All entries</h3><span class="muted small">Entries are never edited. Use <b>Reverse</b> to correct a mistake.</span></div>
  <div class="scroll-x"><table class="t">
    <thead><tr><th>Date</th><th>Staff</th><th>Type</th><th>Note</th><th class="right">Amount</th><th class="hide-m">Entered by</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($entries as $e): $rev = (int)$e['is_reversed'] > 0; ?>
      <tr class="<?= $rev ? 'rev' : '' ?>">
        <td class="num"><?= ops_fmt_date($e['entry_date'], 'j M Y') ?></td>
        <td><?= h($e['staff_name']) ?></td>
        <td><?php if ($e['entry_type'] === 'advance'): ?><span class="pill draft">Advance</span>
            <?php elseif ($e['entry_type'] === 'recovery'): ?><span class="pill final">Recovered from salary</span>
            <?php else: ?><span class="pill grey"><i class="fa fa-rotate-left"></i>Reversal of #<?= (int)$e['reversal_of_id'] ?></span><?php endif; ?></td>
        <td><span class="desc"><?= h($e['note'] ?: '—') ?></span> <span class="muted small">#<?= (int)$e['id'] ?></span></td>
        <td class="right num amt <?= $e['entry_type'] === 'advance' ? 'out' : 'in' ?>"><?= ops_inr(ops_paise_from_db($e['amount'])) ?></td>
        <td class="hide-m muted small"><?= h($e['created_by_name']) ?></td>
        <td class="right"><?php if ($e['entry_type'] === 'advance' && !$rev): ?>
          <button class="btn sm ghost danger" onclick='OPS.reverse("advance", <?= (int)$e['id'] ?>, <?= h(json_encode('Advance #' . $e['id'] . ' · ' . $e['staff_name'] . ' · ' . ops_inr(ops_paise_from_db($e['amount'])))) ?>)'><i class="fa fa-rotate-left"></i>Reverse</button>
        <?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$entries): ?><tr><td colspan="7" class="empty">No advances yet.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

<template id="advForm">
  <form method="post">
    <?= ops_csrf_field() ?>
    <div class="card-h"><h3>Give salary advance</h3></div>
    <div class="card-b"><div class="form-grid">
      <div class="field full"><label>Staff</label><select class="input" name="staff_id" required><option value="">Choose…</option>
        <?php foreach ($staffById as $s): ?><option value="<?= $s['staff_id'] ?>"><?= h($s['staff_name']) ?> — <?= h($s['staff_role']) ?>, <?= h($s['office_name']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Date</label><input class="input" type="date" name="entry_date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required></div>
      <div class="field"><label>Amount (₹)</label><input class="input num" name="amount" inputmode="decimal" placeholder="0" required></div>
      <div class="field full"><label>Note</label><input class="input" name="note" maxlength="255" placeholder="Reason (optional)"></div>
      <div class="full note info"><i class="fa fa-circle-info"></i><div>Deducted from the salary of the month you choose above.</div></div>
    </div></div>
    <div class="foot"><button type="button" class="btn" onclick="OPS.close()">Cancel</button><button class="btn primary">Save advance</button></div>
  </form>
</template>
<?php require 'opsdesk/layout_end.php'; ?>
