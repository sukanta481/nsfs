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

  <div class="section">
    <div><b>Declared Value:</b> <?= htmlspecialchars($declared_value) ?></div>
    <div><b>Delivery Instruction:</b> <?= htmlspecialchars($delivery_instruction) ?></div>
  </div>
  <div class="section">
    <table class="info-table" style="width:100%;">
      <tr>
        <th>Proof of delivery</th>
        <th>Date</th>
        <th>Time</th>
        <th>Received by</th>
      </tr>
      <tr>
        <td style="height:32px;"></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    </table>
  </div>
  <div style="margin-top:18px; font-size:12px;">
    If you have any questions concerning this invoice, please call us on the number above.
  </div>
  <div class="no-print" style="margin-top:15px;">
    <button onclick="window.print()" style="padding:7px 22px; font-size:15px;">Print</button>
  </div>
</div>
