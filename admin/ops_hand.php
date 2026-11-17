<?php
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
requirePermission('ops_hand_ledger_view');
require 'opsdesk/bootstrap.php';

$staffList = ops_staff_list();
$sid = isset($_REQUEST['staff_id']) ? (int)$_REQUEST['staff_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ops_check_csrf();
    ops_require('ops_hand_ledger_manage');
    try {
        $staff = ops_one('SELECT staff_name FROM tbl_staff WHERE staff_id = ?' . ops_office_scope('tbl_staff'), array($sid));
        if (!$staff) throw new InvalidArgumentException('Staff member not found.');
        $paise = ops_paise_from_input(isset($_POST['amount']) ? $_POST['amount'] : '');
        if (!$paise) throw new InvalidArgumentException('Enter a valid amount.');
        $date = ops_date_param('entry_date', '');
        if (!$date) throw new InvalidArgumentException('Choose a date.');
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        ops_hand_add($sid, $type, $date, $paise, isset($_POST['category']) ? $_POST['category'] : '', trim(mb_substr(isset($_POST['note']) ? $_POST['note'] : '', 0, 255)));
        ops_flash(($type === 'given' ? 'Cash given: ' : 'Expense recorded: ') . ops_inr($paise) . ' · new balance ' . ops_inr(ops_hand_balance($sid)));
    } catch (InvalidArgumentException $e) {
        ops_flash($e->getMessage(), 'err');
    }
    ops_redirect('ops_hand.php?staff_id=' . $sid);
}

$balances = ops_hand_balances_all();
$staffById = array();
foreach ($staffList as $s) $staffById[(int)$s['staff_id']] = $s;
if (!$sid && $balances) { arsort($balances); $sid = (int)key($balances); }
if (!$sid && $staffList) $sid = (int)$staffList[0]['staff_id'];
$cur = isset($staffById[$sid]) ? $staffById[$sid] : null;
$canManage = hasPermission('ops_hand_ledger_manage');

$entries = $cur ? ops_rows(
    'SELECT h.*, (SELECT COUNT(*) FROM tbl_ops_hand_ledger x WHERE x.reversal_of_id = h.id) AS is_reversed
     FROM tbl_ops_hand_ledger h WHERE h.staff_id = ? ORDER BY h.entry_date, h.id', array($sid)
) : array();
$bal = $cur ? ops_hand_balance($sid) : 0;
$first = $cur ? explode(' ', ucwords(strtolower($cur['staff_name'])))[0] : '';

$ops_title = 'Hand Balance';
require 'opsdesk/layout_start.php';
?>
<div class="page-head">
  <div><h1>Hand Balance</h1><p>Road-expense cash given to staff and the hisab they give back. Whatever is left carries forward to the next day.</p></div>
  <form class="actions" method="get">
    <select class="input auto" name="staff_id" onchange="this.form.submit()">
      <?php foreach ($staffList as $s): ?><option value="<?= $s['staff_id'] ?>" <?= (int)$s['staff_id'] === $sid ? 'selected' : '' ?>><?= h($s['staff_name']) ?> — <?= h($s['staff_role']) ?><?= isset($balances[(int)$s['staff_id']]) ? ' (' . ops_inr($balances[(int)$s['staff_id']]) . ')' : '' ?></option><?php endforeach; ?>
    </select>
  </form>
</div>

<?php if ($cur): ?>
<div class="grid g3">
  <div class="card balance-card" style="grid-column:span 2">
    <?php $holding = $bal >= 0; ?>
    <div class="ico" style="background:<?= $holding ? 'var(--present-bg)' : 'var(--absent-bg)' ?>;color:<?= $holding ? 'var(--present)' : 'var(--absent)' ?>"><i class="fa fa-wallet"></i></div>
    <div><div class="muted small" style="font-weight:600"><?= h(strtoupper($cur['staff_name'])) ?> · CURRENT HAND BALANCE</div>
      <div class="big num <?= $bal > 0 ? 'in' : ($bal < 0 ? 'out' : '') ?>"><?= ops_inr(abs($bal)) ?></div>
      <div><?php if ($bal > 0): ?><b><?= h($first) ?> is holding company cash</b> — still to give hisab.
           <?php elseif ($bal < 0): ?><b>Company owes <?= h($first) ?></b> — spent from own pocket. Settle by giving cash.
           <?php else: ?>All settled.<?php endif; ?></div></div>
  </div>
  <?php if ($canManage): ?>
  <div class="card card-b" style="display:flex;flex-direction:column;gap:10px;justify-content:center">
    <button class="btn success" onclick="entryForm('given')"><i class="fa fa-arrow-down"></i>Give cash</button>
    <button class="btn primary" onclick="entryForm('expense')"><i class="fa fa-receipt"></i>Add expense (hisab)</button>
  </div>
  <?php endif; ?>
</div>

<div class="card mt">
  <div class="card-h"><h3>Passbook</h3><span class="muted small">Never edited — mistakes are corrected with a reversal</span><div class="r"><button class="btn sm" onclick="window.print()"><i class="fa fa-print"></i>Print</button></div></div>
  <?php if (!$entries): ?><div class="empty">No entries yet for <?= h($cur['staff_name']) ?>.</div><?php else: ?>
  <div class="scroll-x"><table class="t">
    <thead><tr><th>Type</th><th>Details</th><th class="right">Given</th><th class="right">Spent</th><th class="right">Balance</th><th class="no-print"></th></tr></thead>
    <tbody>
    <?php
    $run = 0; $day = null;
    foreach ($entries as $i => $e):
        if ($e['entry_date'] !== $day):
            if ($day !== null): ?><tr class="sub-row"><td colspan="4">Closing balance, carried to next day</td><td class="right num"><b><?= ops_inr($run) ?></b></td><td></td></tr><?php endif;
            $day = $e['entry_date']; ?>
            <tr class="pb-day"><td colspan="3"><?= date('l, j F Y', strtotime($day)) ?></td><td class="right num" colspan="3">Opening <?= ops_inr($run) ?></td></tr>
        <?php endif;
        $v = (int)$e['direction'] * ops_paise_from_db($e['amount']); $run += $v; $rev = (int)$e['is_reversed'] > 0; ?>
        <tr class="<?= $rev ? 'rev' : '' ?>">
          <td><?php if ($e['entry_type'] === 'given'): ?><span class="pill final"><i class="fa fa-arrow-down"></i>Given</span>
              <?php elseif ($e['entry_type'] === 'expense'): ?><span class="pill grey"><?= h($e['category'] ?: 'Expense') ?></span>
              <?php else: ?><span class="pill grey"><i class="fa fa-rotate-left"></i>Reversal</span><?php endif; ?></td>
          <td><span class="desc"><?= h($e['note'] ?: ($e['entry_type'] === 'given' ? 'Cash given' : '—')) ?></span>
              <?= $e['entry_type'] === 'reversal' ? '<div class="small muted">undoes entry #' . (int)$e['reversal_of_id'] . '</div>' : '' ?>
              <div class="small muted">#<?= (int)$e['id'] ?> · <?= h($e['created_by_name']) ?></div></td>
          <td class="right num amt in"><?= $v > 0 ? ops_inr($v) : '' ?></td>
          <td class="right num amt out"><?= $v < 0 ? ops_inr(-$v) : '' ?></td>
          <td class="right num"><b><?= ops_inr($run) ?></b></td>
          <td class="right no-print"><?php if ($canManage && $e['entry_type'] !== 'reversal' && !$rev): ?>
            <button class="btn sm ghost danger" title="Reverse" onclick='OPS.reverse("hand", <?= (int)$e['id'] ?>, <?= h(json_encode('#' . $e['id'] . ' · ' . ($e['entry_type'] === 'given' ? 'Cash given' : ($e['category'] ?: 'Expense')) . ' · ' . ops_inr(ops_paise_from_db($e['amount'])) . ' on ' . date('j M', strtotime($e['entry_date'])))) ?>)'><i class="fa fa-rotate-left"></i></button>
          <?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
      <tr class="sub-row"><td colspan="4"><b>Current balance</b></td><td class="right num"><b><?= ops_inr($run) ?></b></td><td></td></tr>
    </tbody>
  </table></div>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="card mt">
  <div class="card-h"><h3>All staff balances</h3></div>
  <?php if (!$balances): ?><div class="empty">Nobody has an open balance.</div><?php else: ?>
  <table class="t"><tbody>
    <?php foreach ($balances as $bid => $v): $s = isset($staffById[$bid]) ? $staffById[$bid] : null; if (!$s) continue; ?>
    <tr class="click" onclick="location.href='ops_hand.php?staff_id=<?= $bid ?>'">
      <td><div class="who"><?= ops_avatar($bid, $s['staff_name']) ?><div><b><?= h($s['staff_name']) ?></b><span><?= h($s['staff_role']) ?> · <?= h($s['office_name']) ?></span></div></div></td>
      <td class="muted small"><?= $v > 0 ? 'Holding company cash' : 'Company owes' ?></td>
      <td class="right num amt <?= $v > 0 ? 'in' : 'out' ?>"><?= ops_inr(abs($v)) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody></table>
  <?php endif; ?>
</div>

<?php if ($cur && $canManage): ?>
<script>
var CATS = <?= json_encode(OPS_HAND_CATEGORIES) ?>, BAL = <?= (int)$bal ?>;
function entryForm(type) {
  var g = type === 'given';
  OPS.open('<form method="post"><input type="hidden" name="csrf" value="' + window.OPS_CSRF + '"><input type="hidden" name="staff_id" value="<?= $sid ?>"><input type="hidden" name="type" value="' + type + '">' +
    '<div class="card-h"><h3>' + (g ? 'Give cash' : 'Add expense (hisab)') + ' · ' + OPS.esc(<?= json_encode($cur['staff_name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>) + '</h3></div><div class="card-b"><div class="form-grid">' +
    '<div class="field"><label>Date</label><input class="input" type="date" name="entry_date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required></div>' +
    '<div class="field"><label>Amount (₹)</label><input class="input num" name="amount" id="amt" inputmode="decimal" placeholder="0" required oninput="preview(' + (g ? 1 : -1) + ')"></div>' +
    (g ? '' : '<div class="field full"><label>Category</label><select class="input" name="category" required><option value="">Choose…</option>' + CATS.map(function (c) { return '<option>' + c + '</option>'; }).join('') + '</select></div>') +
    '<div class="field full"><label>Note</label><input class="input" name="note" maxlength="255" placeholder="' + (g ? 'e.g. Cash for Durgapur run' : 'e.g. NH19 toll x2') + '"></div>' +
    '</div><div class="calc mt"><span>Balance after this entry</span><b class="num" id="after">—</b></div></div>' +
    '<div class="foot"><button type="button" class="btn" onclick="OPS.close()">Cancel</button><button class="btn ' + (g ? 'success' : 'primary') + '">Save</button></div></form>');
}
function preview(dir) {
  var v = parseFloat((document.getElementById('amt').value || '').replace(/,/g, ''));
  var el = document.getElementById('after');
  if (isNaN(v)) { el.textContent = '—'; return; }
  var after = BAL + dir * Math.round(v * 100);
  el.textContent = (after < 0 ? '−' : '') + '₹' + (Math.abs(after) / 100).toLocaleString('en-IN', {maximumFractionDigits: 2});
  el.className = 'num ' + (after < 0 ? 'out' : 'in');
}
</script>
<?php endif; ?>
<?php require 'opsdesk/layout_end.php'; ?>
