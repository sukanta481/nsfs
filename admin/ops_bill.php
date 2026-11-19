<?php
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
requirePermission('ops_billing_view');
require 'opsdesk/bootstrap.php';

/* Consignor bill: printable A4 (standalone, no admin sidebar) + payments + cancel. */
$company = require 'opsdesk/company.php';
$billId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$back = 'ops_bill.php?id=' . $billId;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ops_check_csrf();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    try {
        if ($action === 'pay') {
            ops_require('ops_billing_payment');
            $amt = ops_paise_from_input(isset($_POST['amount']) ? $_POST['amount'] : '');
            $tds = trim(isset($_POST['tds']) ? $_POST['tds'] : '') === '' ? 0 : ops_paise_from_input($_POST['tds']);
            if ($amt === null && trim($_POST['amount']) !== '') throw new InvalidArgumentException('Amount received is not a valid number.');
            if ($tds === null) throw new InvalidArgumentException('TDS is not a valid number.');
            ops_bill_pay($billId, ops_date_param('pay_date', ''), (int)$amt, (int)$tds, isset($_POST['mode']) ? $_POST['mode'] : '',
                trim(mb_substr(isset($_POST['reference']) ? $_POST['reference'] : '', 0, 100)), trim(mb_substr(isset($_POST['note']) ? $_POST['note'] : '', 0, 255)));
            ops_flash('Payment of ' . ops_inr((int)$amt + (int)$tds) . ' recorded.');
        } elseif ($action === 'pay_reverse') {
            ops_require('ops_billing_payment');
            ops_bill_pay_reverse((int)$_POST['payment_id'], isset($_POST['reason']) ? $_POST['reason'] : '');
            ops_flash('Payment reversed.');
        } elseif ($action === 'cancel') {
            ops_require('ops_billing_manage');
            ops_bill_cancel($billId, isset($_POST['reason']) ? $_POST['reason'] : '');
            ops_flash('Bill cancelled. Its dockets can be billed again.');
        }
    } catch (InvalidArgumentException $e) {
        ops_flash($e->getMessage(), 'err');
    }
    ops_redirect($back);
}

$b = ops_bill_get($billId);
$items = $b ? ops_bill_items($billId) : array();
// Bills made before 2026-11-19 used a rate per docket (amount = rate). Newer bills: amount = boxes × rate.
$perBox = true;
foreach ($items as $it) {
    if (ops_paise_from_db($it['amount']) !== ops_paise_from_db($it['rate']) * max(1, (int)$it['boxes'])) { $perBox = false; break; }
}
$pays = $b ? ops_bill_payments($billId) : array();
$paid = $b ? ops_bill_paid($billId) : array('received' => 0, 'tds' => 0, 'settled' => 0);
$total = $b ? ops_paise_from_db($b['total']) : 0;
$due = $b && $b['status'] === 'issued' ? $total - $paid['settled'] : 0;
list($stLabel, $stClass) = $b ? ops_bill_status_label($b, $paid['settled']) : array('', '');
$flash = ops_take_flash();
$canPay = hasPermission('ops_billing_payment') && $b && $b['status'] === 'issued' && $due > 0;
$canCancel = hasPermission('ops_billing_manage') && $b && $b['status'] === 'issued';
$bank = isset($company['bank']) ? array_filter($company['bank']) : array();
$cancelled = $b && $b['status'] === 'cancelled';
$gst = $b ? (float)$b['gst_rate'] : 0;
$gstTxt = rtrim(rtrim(number_format($gst / 2, 2), '0'), '.');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $b ? h($b['bill_no']) . ' · ' . h($b['company_name']) : 'Bill' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{--navy:#1e3a52;--line:#d5dde5;--soft:#f4f7fa;--text:#1b2733;--muted:#5d6d7e;--green:#1e8449;--red:#b03a2e}
  *{box-sizing:border-box}
  body{margin:0;background:#e9edf1;font-family:Inter,Arial,sans-serif;color:var(--text);font-size:12px;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .bar{position:sticky;top:0;z-index:5;background:var(--navy);color:#fff;padding:10px 16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
  .bar a,.bar button{font:inherit;font-weight:600;font-size:13px;color:#fff;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);border-radius:6px;padding:7px 12px;cursor:pointer;text-decoration:none}
  .bar button.primary{background:#fff;color:var(--navy)}
  .bar .sp{flex:1}
  .flash{max-width:210mm;margin:12px auto 0;padding:10px 14px;border-radius:6px;font-weight:600}
  .flash.ok{background:#d5f5e3;color:#145a32} .flash.err{background:#fadbd8;color:#922b21}
  .page{width:210mm;min-height:297mm;margin:16px auto;background:#fff;padding:12mm 13mm;position:relative;box-shadow:0 2px 12px rgba(0,0,0,.12)}
  .wm{position:absolute;top:38%;left:0;right:0;text-align:center;font-size:100px;font-weight:800;color:rgba(176,58,46,.10);transform:rotate(-24deg);pointer-events:none;letter-spacing:6px}
  .head{display:flex;align-items:center;gap:16px;padding-bottom:10px;border-bottom:3px solid var(--navy)}
  .head img{height:60px}
  .head .co{flex:1;text-align:right}
  .head h1{margin:0;font-size:20px;font-weight:800;color:var(--navy)}
  .head .co div{color:var(--muted);font-size:11px;line-height:1.55}
  .title{display:flex;justify-content:space-between;align-items:center;background:var(--navy);color:#fff;padding:8px 14px;margin-top:12px;border-radius:4px}
  .title h2{margin:0;font-size:15px;letter-spacing:1.5px}
  .parties{display:grid;grid-template-columns:1.3fr 1fr;gap:12px;margin-top:12px}
  .box{border:1px solid var(--line);border-radius:4px;padding:9px 12px}
  .box .k{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px}
  .box b{font-size:13px}
  .box div{line-height:1.55}
  .meta td{padding:2px 0}.meta td:first-child{color:var(--muted);padding-right:10px;white-space:nowrap}
  table{width:100%;border-collapse:collapse}
  .lines{margin-top:12px;border:1px solid var(--line)}
  .lines th{background:var(--soft);font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);text-align:left;padding:7px 6px;border-bottom:1px solid var(--line)}
  .lines td{padding:3px 6px;border-bottom:1px solid #edf1f4;vertical-align:top;font-size:11px;line-height:1.35}
  .lines thead{display:table-header-group}
  .lines tr{page-break-inside:avoid}
  .r{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}.c{text-align:center}
  .sumwrap{display:grid;grid-template-columns:1fr 280px;gap:14px;margin-top:12px;page-break-inside:avoid}
  .sum td{padding:6px 10px;border-bottom:1px dashed var(--line)}
  .sum tr.grand td{border-top:2px solid var(--navy);border-bottom:0;font-weight:800;font-size:15px;color:var(--navy)}
  .words{font-weight:600;font-style:italic;margin-top:4px}
  .sign{display:flex;justify-content:space-between;margin-top:46px;font-size:11px;page-break-inside:avoid}
  .sign div{border-top:1px solid var(--text);padding-top:6px;width:220px;text-align:center;font-weight:600}
  .foot{margin-top:14px;padding-top:6px;border-top:1px solid var(--line);font-size:10px;color:var(--muted);display:flex;justify-content:space-between;gap:10px}
  .panel{width:210mm;margin:0 auto 30px;background:#fff;border-radius:8px;padding:16px 18px;box-shadow:0 2px 12px rgba(0,0,0,.08)}
  .panel h3{margin:0 0 10px;font-size:15px}
  .panel table td,.panel table th{padding:7px 8px;border-bottom:1px solid #edf1f4;text-align:left;font-size:12.5px}
  .panel .rev{opacity:.5;text-decoration:line-through}
  .panel form.inline{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;margin-top:12px}
  .panel label{display:flex;flex-direction:column;gap:3px;font-size:11.5px;font-weight:600;color:var(--muted)}
  .panel input,.panel select{font:inherit;font-size:13px;padding:7px 9px;border:1px solid var(--line);border-radius:6px}
  .panel button{font:inherit;font-weight:700;font-size:13px;padding:8px 14px;border-radius:6px;border:1px solid var(--navy);background:var(--navy);color:#fff;cursor:pointer}
  .panel button.ghost{background:#fff;color:var(--red);border-color:#e6b0aa}
  .pill{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700}
  .pill.final{background:#d5f5e3;color:var(--green)}.pill.draft{background:#fdebd0;color:#a04000}.pill.blue{background:#d6eaf8;color:#1f618d}.pill.grey{background:#eaecee;color:#566573}
  .panel:target{outline:3px solid #27ae60;outline-offset:2px}
  .kpis{display:flex;gap:18px;flex-wrap:wrap;margin-bottom:6px}.kpis div{font-size:12px;color:var(--muted)}.kpis b{display:block;font-size:17px;color:var(--text)}
  @media screen and (max-width:820px){.page,.panel{width:auto;min-height:0;margin:10px;padding:14px}.parties,.sumwrap{grid-template-columns:1fr}}
  @page{size:A4;margin:10mm 0}
  @media print{body{background:#fff}.bar,.panel,.flash{display:none}.page{margin:0 auto;box-shadow:none;min-height:0;padding:0 13mm}}
</style>
</head>
<body>
<div class="bar">
  <a href="ops_bills.php">← All bills</a>
  <strong><?= $b ? h($b['bill_no']) : 'Bill not found' ?></strong>
  <?php if ($b): ?><span class="pill <?= $stClass ?>"><?= h($stLabel) ?></span><?php endif; ?>
  <span class="sp"></span>
  <?php if ($b): ?><button class="primary" onclick="window.print()">🖨 Print bill</button><?php endif; ?>
</div>
<?php if ($flash): ?><div class="flash <?= $flash['type'] === 'err' ? 'err' : 'ok' ?>"><?= h($flash['msg']) ?></div><?php endif; ?>

<?php if (!$b): ?>
  <div class="panel" style="margin-top:30px">Bill not found.</div>
<?php else: ?>
<div class="page">
  <?php if ($cancelled): ?><div class="wm">CANCELLED</div><?php endif; ?>
  <div class="head">
    <img src="<?= h($company['logo']) ?>" alt="">
    <div class="co">
      <h1><?= h($company['name']) ?></h1>
      <div><?= h($company['address']) ?></div>
      <div>Mob: <?= h($company['phones']) ?> · <?= h($company['email']) ?></div>
      <div><b>GSTIN:</b> <?= h($company['gst']) ?><?= !empty($company['sac']) ? ' · <b>SAC:</b> ' . h($company['sac']) : '' ?></div>
    </div>
  </div>

  <div class="title"><h2><?= $b['tax_type'] !== 'none' ? 'TAX INVOICE' : 'FREIGHT BILL' ?></h2><span>Bill No: <b><?= h($b['bill_no']) ?></b></span></div>

  <div class="parties">
    <div class="box"><div class="k">Bill to (consignor)</div>
      <div><b><?= h($b['company_name']) ?></b></div>
      <?php if ($b['company_address']): ?><div><?= h($b['company_address']) ?></div><?php endif; ?>
      <?php if ($b['company_phone']): ?><div>Phone: <?= h($b['company_phone']) ?></div><?php endif; ?>
      <?php if ($b['company_email']): ?><div>Email: <?= h($b['company_email']) ?></div><?php endif; ?>
      <div>GSTIN: <b><?= h($b['company_gstin'] ?: '—') ?></b></div>
    </div>
    <div class="box"><div class="k">Bill details</div>
      <table class="meta">
        <tr><td>Bill no</td><td><b><?= h($b['bill_no']) ?></b></td></tr>
        <tr><td>Bill date</td><td><?= date('d M Y', strtotime($b['bill_date'])) ?></td></tr>
        <tr><td>Period</td><td><?= date('d M Y', strtotime($b['period_from'])) ?> – <?= date('d M Y', strtotime($b['period_to'])) ?></td></tr>
        <tr><td>Dockets</td><td><?= (int)$b['docket_count'] ?> delivered · <?= array_sum(array_map(function ($it) { return (int)$it['boxes']; }, $items)) ?> boxes</td></tr>
        <tr><td>Service</td><td>Courier / freight</td></tr>
      </table>
    </div>
  </div>

  <table class="lines">
    <thead><tr><th style="width:28px">#</th><th>Docket no</th><th>Delivered</th><th>Consignee</th><th>Destination</th><th>Invoice no</th><th class="c">Boxes</th><th class="r"><?= $perBox ? 'Rate/box' : 'Rate/docket' ?></th><th class="r">Amount (₹)</th></tr></thead>
    <tbody>
    <?php foreach ($items as $n => $it): ?>
      <tr><td><?= $n + 1 ?></td><td><b><?= h($it['doc_no']) ?></b></td>
        <td><?= $it['delivery_date'] ? date('d-m-Y', strtotime($it['delivery_date'])) : '—' ?></td>
        <td><?= h($it['consignee_name']) ?></td><td><?= h(mb_strimwidth((string)$it['destination'], 0, 38, '…')) ?></td>
        <td><?= h($it['consignor_invoice_no'] && $it['consignor_invoice_no'] !== 'N/A' ? $it['consignor_invoice_no'] : '—') ?></td>
        <td class="c"><?= (int)$it['boxes'] ?: '—' ?></td>
        <td class="r"><?= number_format((float)$it['rate'], 2) ?></td>
        <td class="r"><?= number_format((float)$it['amount'], 2) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="sumwrap">
    <div>
      <div class="box"><div class="k">Amount in words</div><div class="words"><?= h(ops_amount_words($total)) ?></div></div>
      <?php if ($b['note']): ?><div class="box" style="margin-top:8px"><div class="k">Note</div><?= h($b['note']) ?></div><?php endif; ?>
      <?php if ($bank): ?>
      <div class="box" style="margin-top:8px"><div class="k">Payment details</div>
        <?php foreach (array('account_name' => 'A/c name', 'account_no' => 'A/c no', 'ifsc' => 'IFSC', 'bank_name' => 'Bank', 'branch' => 'Branch', 'upi' => 'UPI') as $k => $label): ?>
          <?php if (!empty($bank[$k])): ?><div><?= $label ?>: <b><?= h($bank[$k]) ?></b></div><?php endif; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <table class="sum box" style="padding:0">
      <tr><td>Subtotal</td><td class="r"><?= ops_inr(ops_paise_from_db($b['subtotal'])) ?></td></tr>
      <?php if ($b['tax_type'] === 'cgst_sgst'): ?>
        <tr><td>CGST @ <?= $gstTxt ?>%</td><td class="r"><?= ops_inr(ops_paise_from_db($b['cgst'])) ?></td></tr>
        <tr><td>SGST @ <?= $gstTxt ?>%</td><td class="r"><?= ops_inr(ops_paise_from_db($b['sgst'])) ?></td></tr>
      <?php elseif ($b['tax_type'] === 'igst'): ?>
        <tr><td>IGST @ <?= rtrim(rtrim(number_format($gst, 2), '0'), '.') ?>%</td><td class="r"><?= ops_inr(ops_paise_from_db($b['igst'])) ?></td></tr>
      <?php endif; ?>
      <?php if ((float)$b['round_off'] != 0): ?><tr><td>Round off</td><td class="r"><?= ops_inr(ops_paise_from_db($b['round_off']), true) ?></td></tr><?php endif; ?>
      <tr class="grand"><td>Total</td><td class="r"><?= ops_inr($total) ?></td></tr>
    </table>
  </div>

  <div class="sign"><div>Receiver's signature &amp; stamp</div><div>for <?= h($company['name']) ?><br><span style="font-weight:500;color:var(--muted)">Authorised signatory</span></div></div>
  <div class="foot"><span>Computer-generated bill · created by <?= h($b['created_by_name']) ?> on <?= date('d M Y', strtotime($b['created_at'])) ?></span>
    <span><?= $cancelled ? 'CANCELLED on ' . date('d M Y', strtotime($b['cancelled_at'])) . ': ' . h($b['cancel_reason']) : '' ?></span></div>
</div>

<!-- Screen only: payments and actions -->
<div class="panel" id="payment">
  <h3>Payments</h3>
  <div class="kpis">
    <div>Bill total<b><?= ops_inr($total) ?></b></div>
    <div>Received<b style="color:var(--green)"><?= ops_inr($paid['received']) ?></b></div>
    <?php if ($paid['tds']): ?><div>TDS deducted<b><?= ops_inr($paid['tds']) ?></b></div><?php endif; ?>
    <div>Balance due<b style="color:<?= $due > 0 ? 'var(--red)' : 'var(--green)' ?>"><?= ops_inr($due) ?></b></div>
    <div>Status<b><span class="pill <?= $stClass ?>"><?= h($stLabel) ?></span></b></div>
  </div>
  <?php if ($pays): ?>
  <table>
    <tr><th>Date</th><th>Type</th><th>Mode / ref</th><th class="r">Received</th><th class="r">TDS</th><th>Entered by</th><th></th></tr>
    <?php foreach ($pays as $p): $rev = (int)$p['is_reversed'] > 0; ?>
      <tr class="<?= $rev ? 'rev' : '' ?>"><td><?= date('d M Y', strtotime($p['pay_date'])) ?></td>
        <td><?= $p['entry_type'] === 'payment' ? 'Payment' : 'Reversal of #' . (int)$p['reversal_of_id'] ?></td>
        <td><?= h(isset(OPS_PAY_MODES[$p['mode']]) ? OPS_PAY_MODES[$p['mode']] : '') ?><?= $p['reference'] ? ' · ' . h($p['reference']) : '' ?><?= $p['note'] ? '<div style="color:var(--muted);font-size:11px">' . h($p['note']) . '</div>' : '' ?></td>
        <td class="r"><?= ($p['entry_type'] === 'reversal' ? '−' : '') . ops_inr(ops_paise_from_db($p['amount'])) ?></td>
        <td class="r"><?= (float)$p['tds_amount'] ? ($p['entry_type'] === 'reversal' ? '−' : '') . ops_inr(ops_paise_from_db($p['tds_amount'])) : '—' ?></td>
        <td><?= h($p['created_by_name']) ?> <span style="color:var(--muted)">#<?= (int)$p['payment_id'] ?></span></td>
        <td><?php if (hasPermission('ops_billing_payment') && $p['entry_type'] === 'payment' && !$rev): ?>
          <form method="post" onsubmit="var r=prompt('Reason for reversing this payment?'); if(!r) return false; this.reason.value=r; return true;" style="margin:0">
            <?= ops_csrf_field() ?><input type="hidden" name="action" value="pay_reverse"><input type="hidden" name="payment_id" value="<?= (int)$p['payment_id'] ?>"><input type="hidden" name="reason">
            <button class="ghost" style="padding:4px 9px;font-size:11.5px">Reverse</button></form>
        <?php endif; ?></td></tr>
    <?php endforeach; ?>
  </table>
  <?php else: ?><div style="color:var(--muted)">No payments yet.</div><?php endif; ?>

  <?php if ($canPay): ?>
  <form method="post" class="inline">
    <?= ops_csrf_field() ?><input type="hidden" name="action" value="pay">
    <label>Date<input type="date" name="pay_date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required></label>
    <label>Amount received (₹)<input name="amount" inputmode="decimal" placeholder="<?= h(number_format($due / 100, 2, '.', '')) ?>" style="width:140px"></label>
    <label>TDS deducted (₹)<input name="tds" inputmode="decimal" placeholder="0" style="width:110px"></label>
    <label>Mode<select name="mode" required><option value="">Choose…</option><?php foreach (OPS_PAY_MODES as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select></label>
    <label>Reference<input name="reference" maxlength="100" placeholder="UTR / cheque no"></label>
    <label>Note<input name="note" maxlength="255" placeholder="optional"></label>
    <button>Record payment</button>
  </form>
  <?php endif; ?>

  <?php if ($canCancel): ?>
  <form method="post" style="margin-top:18px;border-top:1px solid #edf1f4;padding-top:12px"
        onsubmit="var r=prompt('Why are you cancelling bill <?= h($b['bill_no']) ?>? Its dockets will become billable again.'); if(!r) return false; this.reason.value=r; return true;">
    <?= ops_csrf_field() ?><input type="hidden" name="action" value="cancel"><input type="hidden" name="reason">
    <button class="ghost">Cancel this bill</button>
    <span style="color:var(--muted);font-size:11.5px;margin-left:8px">Bills are never edited. If something is wrong, cancel it and make a new one.<?= $paid['settled'] > 0 ? ' Reverse the payments first.' : '' ?></span>
  </form>
  <?php endif; ?>
</div>
<?php endif; ?>
<script>if (location.hash === '#payment') { var a = document.querySelector('#payment input[name=amount]'); if (a) a.focus(); }</script>
</body>
</html>
