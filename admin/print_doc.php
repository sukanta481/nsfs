<?php
require('conn.php');
$sdid = intval($_REQUEST['shipping_details_id']);

// Fetch shipping & company details
$get_shipping_detail_sql = "SELECT sd.*, c.company_title, c.company_address, c.company_phone 
    FROM tbl_shipping_details sd 
    LEFT JOIN tbl_company c ON sd.company_id = c.company_id 
    WHERE sd.shipping_details_id = '$sdid' LIMIT 1";
$get_shipping_detail_rs = mysqli_query($conn, $get_shipping_detail_sql);
$get_shipping_detail_row = mysqli_fetch_assoc($get_shipping_detail_rs);

if (!$get_shipping_detail_row) die("Docket not found.");

// Helper: Format date
function dt($d) { return $d ? date('d-M-Y', strtotime($d)) : ''; }

// Pull fields safely
$car_no = $get_shipping_detail_row['car_number'] ?? '-';
$doc_no = $get_shipping_detail_row['doc_no'] ?? '-';
$pickup_date = dt($get_shipping_detail_row['pickup_dates'] ?? '');
$consignor_name = $get_shipping_detail_row['company_title'] ?? '-';
$consignor_addr = $get_shipping_detail_row['company_address'] ?? '-';
$consignor_phone = $get_shipping_detail_row['company_phone'] ?? '-';
$consignee_name = $get_shipping_detail_row['client_name'] ?? '-';
$consignee_addr = $get_shipping_detail_row['client_address'] ?? '-';
$consignee_phone = $get_shipping_detail_row['client_phone'] ?? '-';
$box = $get_shipping_detail_row['box'] ?? '-';
$weight = $get_shipping_detail_row['weight'] ?? '-';
$eway_bill = $get_shipping_detail_row['eway_bill'] ?? '-';
$item = $get_shipping_detail_row['item'] ?? 'General Merchandise';
$declared_value = $get_shipping_detail_row['declared_value'] ?? '-';
$delivery_instruction = $get_shipping_detail_row['delivery_instruction'] ?? '-';
?>

<style>
@media print { .no-print { display: none; } }
.print-invoice-main { font-family: Arial, sans-serif; width: 900px; margin: auto; background: #fff; padding: 20px; }
.print-header { display: flex; justify-content: space-between; align-items: flex-start; }
.company-logo { height: 80px; }
.company-details { text-align: right; font-size: 13px; }
hr { border: 1px solid #aaa; margin-top: 10px; margin-bottom: 14px; }
.section { border: 1px solid #555; padding: 13px 20px; margin: 18px 0 0 0; }
.section-title { font-weight: bold; text-transform: uppercase; font-size: 15px; margin-bottom: 7px; }
.info-table, .info-table td, .info-table th { border: none; font-size: 15px; }
.info-table { width: 100%; }
</style>

<div class="print-invoice-main">
  <div class="print-header">
    <img src="images/logo.png" class="company-logo">
    <div class="company-details">
      <strong>NORTH SUPER FAST SERVICE</strong><br>
      Barasat.algoria.moynacheck,<br>
      kolkata - 700125<br>
      Mob: 9933999998<br>
      Email: onestepup@northsuperfastservice.com
    </div>
  </div>
  <hr>
  <table class="info-table">
    <tr>
      <td>
        <b>Vehicle No.:</b> <?= htmlspecialchars($car_no) ?>
      </td>
      <td>
        <b>GOODS CONSIGNMENT NOTE - Non Negotiable</b>
      </td>
      <td>
        <b>GCN No.:</b> <?= htmlspecialchars($doc_no) ?><br>
        <b>Date:</b> <?= htmlspecialchars($pickup_date) ?>
      </td>
    </tr>
  </table>
  <div class="section">
    <table style="width:100%">
      <tr>
        <td valign="top" width="50%">
          <div class="section-title">Consignor</div>
          <b>Name:</b> <?= htmlspecialchars($consignor_name) ?><br>
          <b>Address:</b> <?= htmlspecialchars($consignor_addr) ?><br>
          <b>Phone:</b> <?= htmlspecialchars($consignor_phone) ?>
        </td>
        <td valign="top" width="50%">
          <div class="section-title">Consignee</div>
          <b>Name:</b> <?= htmlspecialchars($consignee_name) ?><br>
          <b>Address:</b> <?= htmlspecialchars($consignee_addr) ?><br>
          <b>Phone:</b> <?= htmlspecialchars($consignee_phone) ?>
        </td>
      </tr>
    </table>
  </div>

  <div class="section">
    <table class="info-table" style="width:100%;">
      <tr>
        <th>Volume</th>
        <th>Gross Wt</th>
        <th>Chargeable Wt</th>
        <th>Box</th>
        <th>Eway Bill</th>
        <th>Description of Goods</th>
      </tr>
      <tr>
        <td>-</td>
        <td><?= htmlspecialchars($weight) ?></td>
        <td><?= htmlspecialchars($weight) ?></td>
        <td><?= htmlspecialchars($box) ?></td>
        <td><?= htmlspecialchars($eway_bill) ?></td>
        <td><?= htmlspecialchars($item) ?></td>
      </tr>
    </table>
  </div>

  <div class="section" style="padding:0;">
  <table style="width:100%; border-collapse:collapse;" border="1" cellpadding="7">
    <tr style="background:#fafafa;">
      <th style="font-weight:700;">Invoice No</th>
      <th style="font-weight:700;">Eway bill No</th>
      <th style="font-weight:700;">Description of Goods<br>(said to contain)</th>
      <th style="font-weight:700;">No of Pkg</th>
      <th style="font-weight:700;">Remarks</th>
      <th style="font-weight:700;">Trip Number</th>
    </tr>
    <tr>
      <td><?= htmlspecialchars($get_shipping_detail_row['invoice_no'] ?? '-') ?></td>
      <td><?= htmlspecialchars($eway_bill) ?></td>
      <td><?= htmlspecialchars($item) ?></td>
      <td><?= htmlspecialchars($box) ?></td>
      <td></td>
      <td><?= htmlspecialchars($get_shipping_detail_row['trip_number'] ?? '-') ?></td>
    </tr>
  </table>
</div>

<div class="section" style="display:flex;justify-content:space-between;align-items:stretch;gap:10px;">
  <div style="width:50%;border-right:1px solid #999;padding-right:14px;">
    <div><b>Declared Value:</b> <?= htmlspecialchars($declared_value) ?></div>
    <div style="font-size:12px;margin-top:8px;line-height:1.4;">
      <small>We do hereby certify that the above particulars of goods consigned by us have been and have been correctly entered into and the consignment is booked with full knowledge of the terms and conditions of this G.C Note, which we accept.</small>
    </div>
    <div style="margin-top:16px;"><b>Signature of Consignor, his Agent or Representative</b></div>
  </div>
  <div style="width:50%;padding-left:14px;">
    <div><b>Delivery Instruction:</b> <?= htmlspecialchars($delivery_instruction) ?></div>
    <div style="font-size:12px;margin-top:8px;line-height:1.4;">
      <small>Any Octroi, sales tax, entry tax, duties or taxes as may be applicable on the consignment will be paid by consignee at the time of delivery of consignment.</small>
    </div>
    <div style="margin-top:16px;"><b>Proof of delivery</b></div>
    <table style="width:100%;margin-top:5px;font-size:14px;">
      <tr>
        <td style="width:33%;">Date:</td>
        <td style="width:33%;">Time:</td>
        <td style="width:34%;">Received by (Name Sign):</td>
      </tr>
      <tr>
        <td style="height:28px;"></td>
        <td></td>
        <td></td>
      </tr>
      <tr>
        <td colspan="3">Remarks:</td>
      </tr>
      <tr>
        <td colspan="3" style="height:22px;"></td>
      </tr>
    </table>
  </div>
</div>

<div style="margin-top:18px; font-size:12px;">
  If you have any questions concerning this invoice, please call us on the number above.
</div>
<div class="no-print" style="margin-top:15px;">
  <button onclick="window.print()" style="padding:7px 22px; font-size:15px;">Print</button>
</div>

</div>
