<?php
require 'includes/top.php';   // first: sends logged-out users to login_new.php
require 'check_auth.php';
requirePermission('ops_payroll_view');
require 'opsdesk/bootstrap.php';

/*
 * Printable salary slip (A4). Standalone page: no admin sidebar, so it prints cleanly.
 *   ?month=YYYY-MM&staff_id=N            one slip
 *   ?month=YYYY-MM&all=1[&office_id=N]    every staff member with a salary, one slip per page
 */
$company = require 'opsdesk/company.php';
$month = ops_month_param('month', date('Y-m-01', strtotime('first day of last month')));
$sid = isset($_GET['staff_id']) ? (int)$_GET['staff_id'] : 0;
$all = !empty($_GET['all']);
$officeId = isset($_GET['office_id']) ? (int)$_GET['office_id'] : 0;

$sheet = ops_payroll_sheet($month);
$days = $sheet['days'];
$slips = array();
foreach ($sheet['rows'] as $row) {
    if ($row['missing_salary']) continue;
    if ($all ? ($officeId && (int)$row['office_id'] !== $officeId) : $row['staff_id'] !== $sid) continue;
    $slips[] = $row;
}

// extra staff + branch details for the slips being printed
$info = array();
if ($slips) {
    $ids = array_map(function ($r) { return (int)$r['staff_id']; }, $slips);
    $rows = ops_rows(
        'SELECT s.staff_id, s.staff_phone, s.date_of_joining, o.office_name, o.office_address, o.office_phone
         FROM tbl_staff s LEFT JOIN tbl_offices o ON o.office_id = s.office_id
         WHERE s.staff_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')', $ids
    );
    foreach ($rows as $r) $info[(int)$r['staff_id']] = $r;
}

$monthName = date('F Y', strtotime($month));
$monthEnd = date('Y-m-t', strtotime($month));
$period = date('j M', strtotime($month)) . ' – ' . date('j M Y', strtotime($monthEnd));
$back = 'ops_payroll.php?month=' . substr($month, 0, 7) . ($officeId ? '&office_id=' . $officeId : '');
$fmtDate = function ($d) { return ($d && $d !== '0000-00-00') ? date('d M Y', strtotime($d)) : '—'; };
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $all ? 'Salary slips' : 'Salary slip' ?> · <?= h($monthName) ?><?= (!$all && $slips) ? ' · ' . h($slips[0]['name']) : '' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{--navy:#1e3a52;--navy2:#2c4a61;--line:#d5dde5;--soft:#f4f7fa;--text:#1b2733;--muted:#5d6d7e;--green:#1e8449;--red:#b03a2e}
  *{box-sizing:border-box}
  body{margin:0;background:#e9edf1;font-family:Inter,Arial,sans-serif;color:var(--text);font-size:12.5px;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .bar{position:sticky;top:0;z-index:5;background:var(--navy);color:#fff;padding:10px 16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
  .bar a,.bar button{font:inherit;font-weight:600;font-size:13px;color:#fff;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);border-radius:6px;padding:7px 12px;cursor:pointer;text-decoration:none}
  .bar button.primary{background:#fff;color:var(--navy)}
  .bar .sp{flex:1}
  .page{width:210mm;min-height:297mm;margin:16px auto;background:#fff;padding:13mm 14mm;position:relative;box-shadow:0 2px 12px rgba(0,0,0,.12);display:flex;flex-direction:column}
  .wm{position:absolute;top:42%;left:0;right:0;text-align:center;font-size:110px;font-weight:800;color:rgba(176,58,46,.08);transform:rotate(-24deg);pointer-events:none;letter-spacing:8px}
  .head{display:flex;align-items:center;gap:16px;padding-bottom:12px;border-bottom:3px solid var(--navy)}
  .head img{height:62px;width:auto}
  .head .co{flex:1;text-align:right}
  .head .co h1{margin:0;font-size:21px;font-weight:800;color:var(--navy);letter-spacing:.4px}
  .head .co .tag{font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:4px}
  .head .co div{line-height:1.55;color:var(--muted);font-size:11.5px}
  .title{display:flex;justify-content:space-between;align-items:center;background:var(--navy);color:#fff;padding:9px 14px;margin-top:14px;border-radius:4px}
  .title h2{margin:0;font-size:15px;letter-spacing:1.2px;font-weight:700}
  .title span{font-size:11.5px;opacity:.9}
  .st{display:inline-block;font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:8px;vertical-align:1px}
  .st.final{background:#d5f5e3;color:var(--green)} .st.draft{background:#fdebd0;color:#a04000}
  table{width:100%;border-collapse:collapse}
  .emp{margin-top:12px;border:1px solid var(--line)}
  .emp td{padding:7px 10px;border:1px solid var(--line);width:25%;vertical-align:top}
  .emp td.k{background:var(--soft);color:var(--muted);font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.4px;width:17%}
  .emp td.v{font-weight:600}
  .sec{margin-top:14px;font-size:11px;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px}
  .att{display:grid;grid-template-columns:repeat(7,1fr);border:1px solid var(--line)}
  .att div{padding:8px;text-align:center;border-right:1px solid var(--line)}
  .att div:last-child{border-right:0;background:var(--soft)}
  .att b{display:block;font-size:17px;font-weight:800}
  .att small{color:var(--muted);font-weight:600;font-size:10.5px;text-transform:uppercase;letter-spacing:.4px}
  .ed{display:grid;grid-template-columns:1fr 1fr;border:1px solid var(--line)}
  .ed > div:first-child{border-right:1px solid var(--line)}
  .ed table th{background:var(--soft);text-align:left;padding:8px 10px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);border-bottom:1px solid var(--line)}
  .ed table td{padding:8px 10px;border-bottom:1px dashed var(--line)}
  .ed table tr.tot td{border-top:1px solid var(--line);border-bottom:0;font-weight:800;background:var(--soft)}
  .ed2{border:1px solid var(--line)}
  .ed2 th{background:var(--soft);text-align:left;padding:8px 10px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);border-bottom:1px solid var(--line)}
  .ed2 td{padding:8px 10px;border-bottom:1px dashed var(--line);width:34%;vertical-align:top}
  .ed2 td.r{width:16%}
  .ed2 .split{border-left:1px solid var(--line)}
  .ed2 tr.tot td{border-top:1px solid var(--line);border-bottom:0;font-weight:800;background:var(--soft)}
  .brk{border:1px solid var(--line)}
  .brk td{padding:8px 12px;border-bottom:1px dashed var(--line);vertical-align:top}
  .brk td.op{width:34px;text-align:center;font-weight:800;font-size:15px;color:var(--muted);background:var(--soft)}
  .brk tr.plus td.r,.brk tr.plus td.op{color:#1e8449}
  .brk tr.minus td.r,.brk tr.minus td.op{color:var(--red)}
  .brk tr.sum td{border-top:1px solid var(--line);border-bottom:1px solid var(--line);font-weight:800;background:var(--soft)}
  .brk tr.sum td.op{color:var(--navy)}
  .brk tr.final td{font-size:14.5px;color:var(--navy);border-bottom:0}
  .r{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
  .sub{display:block;color:var(--muted);font-size:10.5px;font-weight:500;margin-top:1px}
  .net{margin-top:14px;display:flex;align-items:center;justify-content:space-between;gap:16px;border:2px solid var(--navy);border-radius:6px;padding:12px 16px}
  .net .lbl{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:1px}
  .net .amt{font-size:26px;font-weight:800;color:var(--navy);font-variant-numeric:tabular-nums}
  .net .words{font-weight:600;font-style:italic;margin-top:3px}
  .notes{margin-top:12px;font-size:11.5px;color:var(--muted);line-height:1.6}
  .notes b{color:var(--text)}
  .pay{margin-top:14px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;font-size:11.5px}
  .pay div{border-bottom:1px solid #9aa7b4;padding-bottom:4px;color:var(--muted)}
  .sign{margin-top:auto;padding-top:46px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:24px;text-align:center;font-size:11.5px}
  .sign div{border-top:1px solid var(--text);padding-top:6px;font-weight:600}
  .sign span{display:block;color:var(--muted);font-weight:500;font-size:10.5px}
  .foot{margin-top:16px;padding-top:8px;border-top:1px solid var(--line);display:flex;justify-content:space-between;gap:10px;font-size:10px;color:var(--muted)}
  .empty{width:210mm;margin:40px auto;background:#fff;padding:30px;text-align:center;border-radius:8px}
  @media screen and (max-width:820px){.page{width:auto;min-height:0;margin:10px;padding:16px}.att{grid-template-columns:repeat(3,1fr)}.ed{grid-template-columns:1fr}.ed > div:first-child{border-right:0;border-bottom:1px solid var(--line)}.emp td{display:block;width:auto!important}}
  @page{size:A4;margin:0}
  @media print{body{background:#fff}.bar{display:none}.page{margin:0;box-shadow:none;width:210mm;height:297mm;min-height:0;page-break-after:always;break-after:page}.page:last-child{page-break-after:auto;break-after:auto}}
</style>
</head>
<body>
<div class="bar">
  <a href="<?= h($back) ?>">← Back to payroll</a>
  <strong><?= $all ? count($slips) . ' salary slip' . (count($slips) === 1 ? '' : 's') : 'Salary slip' ?> · <?= h($monthName) ?></strong>
  <span class="sp"></span>
  <?php if ($slips): ?><button class="primary" onclick="window.print()">🖨 Print<?= $all ? ' all' : '' ?></button><?php endif; ?>
</div>

<?php if (!$slips): ?>
  <div class="empty">No salary slip for <?= $all ? 'this selection' : 'this staff member' ?> in <?= h($monthName) ?>. Only staff with a salary set get a slip.</div>
<?php endif; ?>

<?php foreach ($slips as $r):
    $c = $r['counts'];
    $i = isset($info[$r['staff_id']]) ? $info[$r['staff_id']] : array();
    $final = !empty($r['finalized']);
    $carry = $final ? ops_advance_outstanding($r['staff_id'], $monthEnd) : (int)$r['advance_carry'];
    $slipNo = $final ? 'NSFS/SAL/' . substr($month, 0, 7) . '/' . str_pad($r['slip_id'], 4, '0', STR_PAD_LEFT) : 'DRAFT';
    $perDay = intdiv($r['salary'] * 2 + $days, 2 * $days); // display only
    $absentDed = isset($r['absent_deduction']) ? (int)$r['absent_deduction'] : (int)$r['salary'] - (int)$r['earned'];
    $hs = isset($r['hand_settlement']) ? (int)$r['hand_settlement'] : 0;   // + deducted, − added
    $handCarry = ($r['hand_balance'] !== null && $hs > 0) ? (int)$r['hand_balance'] - $hs : 0;
?>
<div class="page">
  <?php if (!$final): ?><div class="wm">DRAFT</div><?php endif; ?>
  <div class="head">
    <img src="<?= h($company['logo']) ?>" alt="<?= h($company['name']) ?>">
    <div class="co">
      <h1><?= h($company['name']) ?></h1>
      <div class="tag"><?= h($company['tagline']) ?></div>
      <div><?= h($company['address']) ?></div>
      <div>Mob: <?= h($company['phones']) ?> · <?= h($company['email']) ?></div>
      <div><b>GSTIN:</b> <?= h($company['gst']) ?></div>
    </div>
  </div>

  <div class="title">
    <h2>SALARY SLIP · <?= strtoupper(h($monthName)) ?><span class="st <?= $final ? 'final' : 'draft' ?>"><?= $final ? 'FINAL' : 'DRAFT · MAY CHANGE' ?></span></h2>
    <span>Slip No: <b><?= h($slipNo) ?></b></span>
  </div>

  <table class="emp">
    <tr><td class="k">Employee</td><td class="v"><?= h($r['name']) ?></td><td class="k">Employee ID</td><td class="v"><?= h($r['uid'] ?: '—') ?></td></tr>
    <tr><td class="k">Designation</td><td class="v"><?= h($r['role']) ?></td><td class="k">Branch</td><td class="v"><?= h($r['office'] ?: '—') ?></td></tr>
    <tr><td class="k">Mobile</td><td class="v"><?= h(!empty($i['staff_phone']) ? $i['staff_phone'] : '—') ?></td><td class="k">Date of joining</td><td class="v"><?= h($fmtDate(isset($i['date_of_joining']) ? $i['date_of_joining'] : null)) ?></td></tr>
    <tr><td class="k">Pay period</td><td class="v"><?= h($period) ?></td><td class="k">Days in month</td><td class="v"><?= $days ?></td></tr>
  </table>

  <div class="sec">Attendance</div>
  <div class="att">
    <div><b style="color:var(--green)"><?= $c['present'] ?></b><small>Present</small></div>
    <div><b><?= $c['paid_leave'] ?></b><small>Paid leave</small></div>
    <div><b><?= $c['week_off'] ?></b><small>Week off</small></div>
    <div><b style="color:var(--red)"><?= $c['absent_total'] ?></b><small>Absent<?= (!$final && $c['unmarked']) ? '*' : '' ?></small></div>
    <div><b style="color:#d35400"><?= (int)$c['extra_duty'] ?></b><small>Extra duty</small></div>
    <div><b><?= $days ?></b><small>Days in month</small></div>
    <div><b><?= $c['payable'] ?></b><small>Payable days</small></div>
  </div>

  <div class="sec">Salary calculation</div>
  <table class="brk">
    <tr><td class="op"></td><td>Fixed monthly salary<span class="sub">per day <?= ops_inr($perDay) ?> (<?= ops_inr($r['salary']) ?> ÷ <?= $days ?> days)</span></td><td class="r"><?= ops_inr($r['salary']) ?></td></tr>
    <?php if (!empty($c['extra_duty'])): ?>
    <tr class="plus"><td class="op">+</td><td>Extra duty (EXD)<span class="sub"><?= (int)$c['extra_duty'] ?> day<?= $c['extra_duty'] > 1 ? 's' : '' ?> × per-day salary</span></td><td class="r">+ <?= ops_inr($r['extra_pay']) ?></td></tr>
    <?php endif; ?>
    <tr class="minus"><td class="op">−</td><td>Absent<span class="sub"><?= (int)$c['absent_total'] ?> day<?= $c['absent_total'] == 1 ? '' : 's' ?> not paid<?= (!$final && $c['unmarked']) ? ', incl. ' . (int)$c['unmarked'] . ' not marked' : '' ?></span></td><td class="r"><?= $absentDed ? '− ' . ops_inr($absentDed) : ops_inr(0) ?></td></tr>
    <tr class="sum"><td class="op">=</td><td>Total salary</td><td class="r"><?= ops_inr($r['gross']) ?></td></tr>
    <tr class="minus"><td class="op">−</td><td>Salary advance recovered<?php if ($carry > 0): ?><span class="sub">still due <?= ops_inr($carry) ?>, carried to next month</span><?php endif; ?></td><td class="r"><?= $r['advance_deduction'] ? '− ' . ops_inr($r['advance_deduction']) : ops_inr(0) ?></td></tr>
    <?php if ($hs >= 0): ?>
    <tr class="minus"><td class="op">−</td><td>Cash in hand<span class="sub"><?= $hs > 0 ? 'company cash held by the employee (road expense), settled from salary' : 'no company cash with the employee' ?><?= $handCarry > 0 ? ' · ' . ops_inr($handCarry) . ' still in hand, carried forward' : '' ?></span></td><td class="r"><?= $hs ? '− ' . ops_inr($hs) : ops_inr(0) ?></td></tr>
    <?php else: ?>
    <tr class="plus"><td class="op">+</td><td>Road expense owed to employee<span class="sub">spent from own pocket, paid with salary</span></td><td class="r">+ <?= ops_inr(-$hs) ?></td></tr>
    <?php endif; ?>
    <tr class="sum final"><td class="op">=</td><td>Amount payable</td><td class="r"><?= ops_inr($r['net']) ?></td></tr>
  </table>

  <div class="net">
    <div><div class="lbl">Net pay</div><div class="words"><?= h(ops_amount_words($r['net'])) ?></div></div>
    <div class="amt"><?= ops_inr($r['net']) ?></div>
  </div>

  <div class="notes">
    <?php if ($final && !$hs && $r['hand_balance'] !== null && $r['hand_balance'] != 0): ?>
      <div>Hand balance on <?= date('j M Y', strtotime($monthEnd)) ?>: <b><?= ops_inr(abs($r['hand_balance'])) ?></b> <?= $r['hand_balance'] > 0 ? '(cash held by employee)' : '(owed to employee)' ?> — not settled through this slip.</div>
    <?php endif; ?>
    <div>Payable days = present + paid leave + week off (<?= (int)$c['payable'] ?> of <?= $days ?>). Absent and unmarked days are not paid. Each extra duty (EXD) adds one day's salary.</div>
  </div>

  <div class="pay">
    <div>Payment mode: </div><div>Paid on: </div><div>Ref / Cheque no: </div>
  </div>

  <div class="sign">
    <div>Employee signature<span><?= h($r['name']) ?></span></div>
    <div>Prepared by<span>Accounts</span></div>
    <div>Authorised signatory<span>for <?= h($company['name']) ?></span></div>
  </div>

  <div class="foot">
    <span><?= !empty($i['office_address']) ? h(trim(($i['office_name'] ? $i['office_name'] . ' branch: ' : '') . $i['office_address'] . (!empty($i['office_phone']) ? ' · ' . $i['office_phone'] : ''))) : '&nbsp;' ?></span>
    <span>Computer-generated slip · <?= $final ? 'finalized by ' . h($r['finalized_by']) . ' on ' . date('d M Y', strtotime($r['finalized_at'])) : 'printed ' . date('d M Y, g:i a') ?></span>
  </div>
</div>
<?php endforeach; ?>
</body>
</html>
