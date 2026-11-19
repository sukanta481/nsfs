<?php
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
requirePermission('ops_billing_view');
require 'opsdesk/bootstrap.php';

$flt = array(
    'company_id' => isset($_GET['company_id']) ? (int)$_GET['company_id'] : 0,
    'status'     => isset($_GET['status']) && in_array($_GET['status'], array('unpaid', 'partly', 'paid', 'issued', 'cancelled'), true) ? $_GET['status'] : '',
    'from'       => ops_date_param('from', ''),
    'to'         => ops_date_param('to', ''),
    'q'          => isset($_GET['q']) ? trim(mb_substr($_GET['q'], 0, 60)) : '',
);
$bills = ops_bill_list($flt);
$outstanding = ops_bill_outstanding();
$tot = array('billed' => 0, 'settled' => 0, 'due' => 0, 'n' => 0);
foreach ($bills as $b) {
    if ($b['status'] === 'cancelled') continue;
    $tot['n']++; $tot['billed'] += $b['total_paise']; $tot['settled'] += $b['settled_paise']; $tot['due'] += $b['due_paise'];
}
$filtered = $flt['company_id'] || $flt['status'] || $flt['from'] || $flt['to'] || $flt['q'] !== '';
$canManage = hasPermission('ops_billing_manage');

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="consignor-bills.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, array('Bill no', 'Bill date', 'Consignor', 'GSTIN', 'Period from', 'Period to', 'Dockets', 'Subtotal', 'GST %', 'CGST', 'SGST', 'IGST', 'Round off', 'Total', 'Received + TDS', 'Balance due', 'Status'));
    foreach ($bills as $b) {
        fputcsv($out, array($b['bill_no'], $b['bill_date'], $b['company_name'], $b['company_gstin'], $b['period_from'], $b['period_to'], $b['docket_count'],
            $b['subtotal'], $b['gst_rate'], $b['cgst'], $b['sgst'], $b['igst'], $b['round_off'], $b['total'],
            ops_paise_to_db($b['settled_paise']), ops_paise_to_db($b['due_paise']), $b['status_label']));
    }
    exit;
}

$ops_title = 'Consignor Bills';
require 'opsdesk/layout_start.php';
?>
<div class="page-head">
  <div><h1>Consignor Bills</h1><p>Bills for delivered dockets, sent to the consignor for payment.</p></div>
  <div class="actions">
    <a class="btn" href="?<?= h(http_build_query(array_filter($flt) + array('export' => 1))) ?>"><i class="fa fa-file-csv"></i>Export</a>
    <?php if ($canManage): ?><a class="btn primary" href="ops_bill_new.php"><i class="fa fa-plus"></i>New bill</a><?php endif; ?>
  </div>
</div>

<div class="grid g3">
  <div class="card kpi"><div class="lbl"><i class="fa fa-file-invoice"></i>Billed<?= $filtered ? ' (filtered)' : '' ?></div><div class="val num"><?= ops_inr($tot['billed']) ?></div><div class="hint"><?= $tot['n'] ?> bill<?= $tot['n'] === 1 ? '' : 's' ?>, cancelled not counted</div></div>
  <div class="card kpi"><div class="lbl"><i class="fa fa-circle-check"></i>Received</div><div class="val num in"><?= ops_inr($tot['settled']) ?></div><div class="hint">payments + TDS</div></div>
  <div class="card kpi"><div class="lbl"><i class="fa fa-hourglass-half"></i>Outstanding</div><div class="val num out"><?= ops_inr($tot['due']) ?></div><div class="hint">still to be paid</div></div>
</div>

<div class="card mt">
  <form class="card-h" method="get" style="flex-wrap:wrap;gap:10px">
    <select class="input auto" name="company_id" onchange="this.form.submit()" style="max-width:260px">
      <option value="">All consignors</option>
      <?php foreach (ops_bill_consignors() as $c): ?><option value="<?= $c['company_id'] ?>" <?= $flt['company_id'] === (int)$c['company_id'] ? 'selected' : '' ?>><?= h($c['company_title']) ?></option><?php endforeach; ?>
    </select>
    <select class="input auto" name="status" onchange="this.form.submit()">
      <option value="">All statuses</option>
      <?php foreach (array('unpaid' => 'Unpaid', 'partly' => 'Partly paid', 'paid' => 'Paid', 'issued' => 'All active', 'cancelled' => 'Cancelled') as $k => $v): ?><option value="<?= $k ?>" <?= $flt['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
    </select>
    <label class="muted small">Bill date from <input class="input auto" type="date" name="from" value="<?= h($flt['from']) ?>"></label>
    <label class="muted small">to <input class="input auto" type="date" name="to" value="<?= h($flt['to']) ?>"></label>
    <input class="input auto" name="q" value="<?= h($flt['q']) ?>" placeholder="Bill no or consignor" style="width:180px">
    <button class="btn"><i class="fa fa-magnifying-glass"></i>Search</button>
    <?php if ($filtered): ?><a class="btn ghost" href="ops_bills.php"><i class="fa fa-xmark"></i>Clear</a><?php endif; ?>
  </form>
  <div class="scroll-x"><table class="t">
    <thead><tr><th>Bill no</th><th>Date</th><th>Consignor</th><th class="hide-m">Period</th><th class="center">Dockets</th><th class="right">Total</th><th class="right">Received</th><th class="right">Due</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($bills as $b): ?>
      <tr class="click<?= $b['status'] === 'cancelled' ? ' rev' : '' ?>" onclick="location.href='ops_bill.php?id=<?= (int)$b['bill_id'] ?>'">
        <td><b><?= h($b['bill_no']) ?></b></td>
        <td class="num"><?= date('j M Y', strtotime($b['bill_date'])) ?></td>
        <td><?= h($b['company_name']) ?></td>
        <td class="hide-m muted small"><?= date('j M', strtotime($b['period_from'])) ?> – <?= date('j M Y', strtotime($b['period_to'])) ?></td>
        <td class="center num"><?= (int)$b['docket_count'] ?></td>
        <td class="right num"><?= ops_inr($b['total_paise']) ?></td>
        <td class="right num in"><?= $b['settled_paise'] ? ops_inr($b['settled_paise']) : '—' ?></td>
        <td class="right num <?= $b['due_paise'] > 0 ? 'out' : 'muted' ?>"><b><?= $b['due_paise'] > 0 ? ops_inr($b['due_paise']) : '—' ?></b></td>
        <td><span class="pill <?= $b['status_class'] ?>"><?= h($b['status_label']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$bills): ?><tr><td colspan="9" class="empty"><?= $filtered ? 'No bills match these filters.' : 'No bills yet.' ?><?= $canManage && !$filtered ? ' <a href="ops_bill_new.php">Make the first bill →</a>' : '' ?></td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

<?php if ($outstanding): ?>
<div class="card mt">
  <div class="card-h"><h3>Outstanding by consignor</h3></div>
  <table class="t"><thead><tr><th>Consignor</th><th class="center">Open bills</th><th class="right">Billed</th><th class="right">Received</th><th class="right">Due</th></tr></thead><tbody>
  <?php foreach ($outstanding as $o): ?>
    <tr class="click" onclick="location.href='ops_bills.php?company_id=<?= $o['company_id'] ?>'">
      <td><b><?= h($o['name']) ?></b></td><td class="center num"><?= $o['bills'] ?></td>
      <td class="right num"><?= ops_inr($o['total']) ?></td><td class="right num in"><?= ops_inr($o['settled']) ?></td>
      <td class="right num out"><b><?= ops_inr($o['due']) ?></b></td></tr>
  <?php endforeach; ?>
  </tbody></table>
</div>
<?php endif; ?>
<?php require 'opsdesk/layout_end.php'; ?>
