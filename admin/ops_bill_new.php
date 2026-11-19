<?php
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
requirePermission('ops_billing_manage');
require 'opsdesk/bootstrap.php';

$today = date('Y-m-d');
$companyId = isset($_REQUEST['company_id']) ? (int)$_REQUEST['company_id'] : 0;
$days = isset($_GET['days']) ? (int)$_GET['days'] : 0;
if (in_array($days, array(7, 15, 30), true)) {
    $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    $to = $today;
} else {
    $days = 0;
    $from = ops_date_param('from', date('Y-m-d', strtotime('-29 days')));
    $to = ops_date_param('to', $today);
}
if ($to > $today) $to = $today;
if ($from > $to) { $t = $from; $from = $to; $to = $t; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ops_check_csrf();
    $from = ops_date_param('from', '');
    $to = ops_date_param('to', '');
    try {
        $lines = json_decode(isset($_POST['lines']) ? $_POST['lines'] : '', true);
        if (!is_array($lines)) throw new InvalidArgumentException('No dockets were sent. Please try again.');
        $rates = array();
        foreach ($lines as $id => $l) {
            if (!is_array($l)) throw new InvalidArgumentException('Please reload the page and try again.');
            $rates[(int)$id] = array(
                'rate' => ops_paise_from_input(isset($l['r']) ? (string)$l['r'] : ''),
                'boxes' => isset($l['b']) ? trim((string)$l['b']) : '',
            );
        }
        $billId = ops_bill_create(
            $companyId, $from, $to, ops_date_param('bill_date', ''), $rates,
            isset($_POST['gst_rate']) ? (int)$_POST['gst_rate'] : 0,
            isset($_POST['tax_type']) ? $_POST['tax_type'] : 'none',
            isset($_POST['gstin']) ? $_POST['gstin'] : '',
            trim(mb_substr(isset($_POST['note']) ? $_POST['note'] : '', 0, 255))
        );
        $b = ops_bill_get($billId);
        ops_flash('Bill ' . $b['bill_no'] . ' created: ' . $b['docket_count'] . ' dockets, total ' . ops_inr(ops_paise_from_db($b['total'])) . '.');
        ops_redirect('ops_bill.php?id=' . $billId);
    } catch (InvalidArgumentException $e) {
        ops_flash($e->getMessage(), 'err');
        ops_redirect('ops_bill_new.php?company_id=' . $companyId . '&from=' . $from . '&to=' . $to);
    }
}

$consignors = ops_bill_consignors();
$co = $companyId ? ops_bill_consignor($companyId) : null;
$dockets = $co ? ops_bill_unbilled_dockets($companyId, $from, $to) : array();
$alreadyBilled = $co ? ops_bill_already_billed_count($companyId, $from, $to) : 0;

$ops_title = 'New Consignor Bill';
require 'opsdesk/layout_start.php';
?>
<div class="page-head">
  <div><h1>New Consignor Bill</h1><p>Choose a consignor and a period. Every <b>delivered</b> docket that is not billed yet appears below: enter the <b>rate per box</b> (amount = boxes × rate) and generate the bill.</p></div>
  <div class="actions"><a class="btn" href="ops_bills.php"><i class="fa fa-arrow-left"></i>All bills</a></div>
</div>

<div class="card">
  <form class="card-h bill-pick" method="get">
    <select class="input auto" name="company_id" required style="min-width:260px">
      <option value="">Choose consignor…</option>
      <?php foreach ($consignors as $c): ?><option value="<?= $c['company_id'] ?>" <?= $companyId === (int)$c['company_id'] ? 'selected' : '' ?>><?= h($c['company_title']) ?></option><?php endforeach; ?>
    </select>
    <?php foreach (array(7, 15, 30) as $n): ?>
      <button class="btn<?= $days === $n ? ' primary' : '' ?>" name="days" value="<?= $n ?>">Last <?= $n ?> days</button>
    <?php endforeach; ?>
    <label class="muted small">From <input class="input auto" type="date" name="from" value="<?= h($from) ?>" max="<?= $today ?>"></label>
    <label class="muted small">To <input class="input auto" type="date" name="to" value="<?= h($to) ?>" max="<?= $today ?>"></label>
    <button class="btn primary"><i class="fa fa-magnifying-glass"></i>Show dockets</button>
  </form>
</div>

<?php if ($co): ?>
<div class="note info mt"><i class="fa fa-circle-info"></i><div>
  <b><?= h($co['company_title']) ?></b> · delivered <?= date('j M Y', strtotime($from)) ?> – <?= date('j M Y', strtotime($to)) ?>:
  <b><?= count($dockets) ?></b> docket<?= count($dockets) === 1 ? '' : 's' ?> not billed yet<?= $alreadyBilled ? ', <b>' . $alreadyBilled . '</b> already on a bill (not shown)' : '' ?>.
</div></div>

<?php $hint = ops_bill_unbilled_hint($companyId); ?>
<?php if ($hint['future']): ?>
  <div class="note warn mt"><i class="fa fa-triangle-exclamation"></i><div><b><?= $hint['future'] ?> delivered docket<?= $hint['future'] > 1 ? 's have' : ' has' ?> a delivery date in the future</b> for this consignor (probably typed wrong). They will not appear in any bill until the delivery date is corrected in the docket.</div></div>
<?php endif; ?>

<?php if (!$dockets): $lm = $hint['latest'] ? date('Y-m-01', strtotime($hint['latest'])) : null; ?>
  <div class="card mt"><div class="empty">No unbilled delivered dockets for this consignor in this period.
    <?php if ($hint['latest']): ?>
      <div class="mt">The latest unbilled delivery for <?= h($co['company_title']) ?> is on <b><?= date('j M Y', strtotime($hint['latest'])) ?></b>.
        <a href="ops_bill_new.php?company_id=<?= $companyId ?>&from=<?= $lm ?>&to=<?= $hint['latest'] ?>">Show <?= date('F Y', strtotime($lm)) ?> →</a></div>
    <?php else: ?><div class="mt">This consignor has no unbilled delivered dockets at all.</div><?php endif; ?>
  </div></div>
<?php else: ?>
<form method="post" id="billForm" class="mt" onsubmit="return beforeSubmit()">
  <?= ops_csrf_field() ?>
  <input type="hidden" name="company_id" value="<?= $companyId ?>">
  <input type="hidden" name="from" value="<?= h($from) ?>">
  <input type="hidden" name="to" value="<?= h($to) ?>">
  <input type="hidden" name="lines" id="lines">
  <div class="grid bill-grid">
    <div class="card">
      <div class="card-h">
        <h3>Dockets</h3>
        <div class="r bill-apply">
          <span class="muted small">Rate per box for ticked dockets ₹</span>
          <input class="input auto num" id="allRate" inputmode="decimal" placeholder="0" style="width:100px">
          <button type="button" class="btn sm" onclick="applyRate(false)">Apply to all</button>
          <button type="button" class="btn sm" onclick="applyRate(true)">Fill empty only</button>
        </div>
      </div>
      <div class="scroll-x"><table class="t bill-lines">
        <thead><tr><th style="width:34px"><input type="checkbox" id="tickAll" checked onchange="tickAll(this.checked)"></th><th>#</th><th>Docket no</th><th>Delivered</th><th>Consignee</th><th class="center" style="width:80px">Boxes</th><th class="right" style="width:120px">Rate / box (₹)</th><th class="right" style="width:110px">Amount</th><th class="hide-m">Destination</th><th class="hide-m">Invoice no</th></tr></thead>
        <tbody>
        <?php foreach ($dockets as $i => $d): ?>
          <tr data-id="<?= (int)$d['docket_id'] ?>">
            <td><input type="checkbox" class="tick" checked onchange="recalc()"></td>
            <td class="muted"><?= $i + 1 ?></td>
            <td><b><?= h($d['doc_no']) ?></b></td>
            <td class="num"><?= $d['delivered_on'] ? date('j M', strtotime($d['delivered_on'])) : '—' ?></td>
            <td><?= h($d['client_name']) ?></td>
            <td class="center"><input class="input num boxes" inputmode="numeric" value="<?= (int)$d['box'] > 0 ? (int)$d['box'] : '' ?>" placeholder="?" oninput="recalc()" style="width:64px;text-align:center" title="Boxes from the docket. Change only if it is wrong."></td>
            <td class="right"><input class="input num rate" inputmode="decimal" placeholder="0.00" oninput="recalc()" style="width:110px;text-align:right"></td>
            <td class="right num amt-cell">—</td>
            <td class="hide-m muted small"><?= h(mb_strimwidth((string)$d['delivery_location'], 0, 40, '…')) ?></td>
            <td class="hide-m muted small"><?= h($d['invoice_no'] && $d['invoice_no'] !== 'N/A' ? $d['invoice_no'] : '—') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>

    <div class="card bill-sum">
      <div class="card-h"><h3>Bill</h3></div>
      <div class="card-b">
        <div class="form-grid">
          <div class="field full"><label>Bill date</label><input class="input" type="date" name="bill_date" value="<?= $today ?>" max="<?= $today ?>" required></div>
          <div class="field full"><label>Consignor GSTIN <span class="muted small">(optional, saved for next time)</span></label>
            <input class="input" name="gstin" id="gstin" maxlength="15" value="<?= h($co['gstin']) ?>" placeholder="e.g. 19ABCDE1234F1Z5" oninput="this.value=this.value.toUpperCase();suggestTax()"></div>
          <div class="field"><label>GST</label>
            <select class="input" name="gst_rate" id="gstRate" onchange="recalc()">
              <?php foreach (OPS_GST_RATES as $g): ?><option value="<?= $g ?>"><?= $g ?>%</option><?php endforeach; ?>
            </select></div>
          <div class="field"><label>Tax type</label>
            <select class="input" name="tax_type" id="taxType" onchange="recalc()">
              <option value="cgst_sgst">CGST + SGST</option><option value="igst">IGST</option>
            </select></div>
          <div class="field full"><label>Note on bill <span class="muted small">(optional)</span></label><input class="input" name="note" maxlength="255" placeholder="e.g. Freight charges for November"></div>
        </div>
        <table class="t bill-totals mt">
          <tr><td>Dockets ticked</td><td class="right num" id="tCount">0</td></tr>
          <tr><td>Subtotal</td><td class="right num" id="tSub">₹0</td></tr>
          <tr id="rCgst"><td>CGST</td><td class="right num" id="tCgst">₹0</td></tr>
          <tr id="rSgst"><td>SGST</td><td class="right num" id="tSgst">₹0</td></tr>
          <tr id="rIgst"><td>IGST</td><td class="right num" id="tIgst">₹0</td></tr>
          <tr><td>Round off</td><td class="right num" id="tRound">₹0</td></tr>
          <tr class="grand"><td><b>Total</b></td><td class="right num"><b id="tTotal">₹0</b></td></tr>
        </table>
        <div class="note warn mt" id="missing" style="display:none"><i class="fa fa-circle-exclamation"></i><div id="missingTxt"></div></div>
        <button class="btn primary mt" id="genBtn" style="width:100%" disabled><i class="fa fa-file-invoice"></i>Generate bill</button>
      </div>
    </div>
  </div>
</form>

<script>
function paise(v) {
  v = (v || '').replace(/[,\s₹]/g, '');
  if (!/^\d+(\.\d{1,2})?$/.test(v)) return null;
  var p = v.split('.'); return parseInt(p[0], 10) * 100 + (p[1] ? parseInt((p[1] + '0').slice(0, 2), 10) : 0);
}
function divRound(a, b) { return Math.floor((2 * a + b) / (2 * b)); }
function inr(p) {
  var neg = p < 0; p = Math.abs(p);
  var s = '₹' + Math.floor(p / 100).toLocaleString('en-IN') + (p % 100 ? '.' + String(p % 100).padStart(2, '0') : '');
  return (neg ? '−' : '') + s;
}
function rows() { return Array.prototype.slice.call(document.querySelectorAll('.bill-lines tbody tr')); }
function tickAll(on) { rows().forEach(function (r) { r.querySelector('.tick').checked = on; }); recalc(); }
function applyRate(emptyOnly) {
  var v = document.getElementById('allRate').value;
  if (paise(v) === null || paise(v) <= 0) { OPS.toast('Enter a rate first', true); return; }
  rows().forEach(function (r) {
    var inp = r.querySelector('.rate');
    if (r.querySelector('.tick').checked && (!emptyOnly || inp.value.trim() === '')) inp.value = v;
  });
  recalc();
}
function suggestTax() {
  var g = document.getElementById('gstin').value.trim();
  document.getElementById('taxType').value = (g.length >= 2 && g.slice(0, 2) !== '19') ? 'igst' : 'cgst_sgst';
  recalc();
}
function recalc() {
  var sub = 0, n = 0, missing = 0, bad = 0, noBox = 0, boxesTot = 0;
  rows().forEach(function (r) {
    var on = r.querySelector('.tick').checked, inp = r.querySelector('.rate'), p = paise(inp.value);
    var bi = r.querySelector('.boxes'), bv = bi.value.trim(), b = /^\d+$/.test(bv) ? parseInt(bv, 10) : 0;
    r.classList.toggle('off', !on);
    inp.classList.toggle('bad', on && inp.value.trim() !== '' && (p === null || p <= 0));
    bi.classList.toggle('bad', on && b < 1);
    var cell = r.querySelector('.amt-cell');
    cell.textContent = (p && p > 0 && b >= 1) ? inr(p * b) : '—';
    if (!on) return;
    n++;
    if (b < 1) { noBox++; return; }
    boxesTot += b;
    if (inp.value.trim() === '') missing++;
    else if (p === null || p <= 0) bad++;
    else sub += p * b;
  });
  var rate = parseInt(document.getElementById('gstRate').value, 10), type = rate ? document.getElementById('taxType').value : 'none';
  var cg = 0, sg = 0, ig = 0;
  if (type === 'cgst_sgst') { cg = divRound(sub * rate, 200); sg = cg; }
  if (type === 'igst') ig = divRound(sub * rate, 100);
  var before = sub + cg + sg + ig, total = divRound(before, 100) * 100;
  document.getElementById('tCount').textContent = n + (boxesTot ? ' · ' + boxesTot + ' boxes' : '');
  document.getElementById('tSub').textContent = inr(sub);
  document.getElementById('tCgst').textContent = inr(cg); document.getElementById('tSgst').textContent = inr(sg); document.getElementById('tIgst').textContent = inr(ig);
  document.getElementById('rCgst').style.display = type === 'cgst_sgst' ? '' : 'none';
  document.getElementById('rSgst').style.display = type === 'cgst_sgst' ? '' : 'none';
  document.getElementById('rIgst').style.display = type === 'igst' ? '' : 'none';
  document.getElementById('tCgst').parentNode.firstChild.textContent = 'CGST ' + (rate / 2) + '%';
  document.getElementById('tSgst').parentNode.firstChild.textContent = 'SGST ' + (rate / 2) + '%';
  document.getElementById('tIgst').parentNode.firstChild.textContent = 'IGST ' + rate + '%';
  document.getElementById('tRound').textContent = inr(total - before);
  document.getElementById('tTotal').textContent = inr(total);
  var msg = [];
  if (missing) msg.push(missing + ' ticked docket' + (missing > 1 ? 's have' : ' has') + ' no rate');
  if (bad) msg.push(bad + ' rate' + (bad > 1 ? 's are' : ' is') + ' not a valid amount');
  if (noBox) msg.push(noBox + ' ticked docket' + (noBox > 1 ? 's have' : ' has') + ' no box count');
  document.getElementById('missing').style.display = msg.length ? '' : 'none';
  document.getElementById('missingTxt').textContent = msg.join(' · ') + (msg.length ? '. Fill it in or untick the docket.' : '');
  document.getElementById('genBtn').disabled = !n || missing > 0 || bad > 0 || noBox > 0;
}
function beforeSubmit() {
  var lines = {};
  rows().forEach(function (r) {
    if (r.querySelector('.tick').checked) lines[r.dataset.id] = {r: r.querySelector('.rate').value.trim(), b: r.querySelector('.boxes').value.trim()};
  });
  document.getElementById('lines').value = JSON.stringify(lines);
  var t = document.getElementById('tTotal').textContent, n = document.getElementById('tCount').textContent;
  if (!confirm('Generate a bill for ' + n + ' dockets, total ' + t + '?')) return false;
  document.getElementById('genBtn').disabled = true;
  return true;
}
<?php if (!$co['gstin']): ?>document.getElementById('taxType').value = 'cgst_sgst';<?php else: ?>suggestTax();<?php endif; ?>
recalc();
</script>
<?php endif; ?>
<?php endif; ?>
<?php require 'opsdesk/layout_end.php'; ?>
