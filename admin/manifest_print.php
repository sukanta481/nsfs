<?php
require 'conn.php';

$office_id = intval($_GET['office_id'] ?? 0);

// -- Get Office/Branch info --
$office = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tbl_offices WHERE office_id=$office_id"));

// -- Get Manifest/Shipping Details --
$res_data = mysqli_query($conn, "SELECT * FROM tbl_shipping_details WHERE branch_office=$office_id AND doc_type='DRS' ORDER BY shipping_details_id ASC");

// -- Set Company Info (edit as needed) --
$company = [
  'name'    => 'NORTH SUPER FAST SERVICE',
  'address' => '16/1/H/13/2, Jaharlal Dutta Lane, Kolkata-700067',
  'mobiles' => '9933999998 / 7003272240 / 9088753192',
  'email'   => 'tanmoy.absh100@gmail.com',
  'gst'     => '19BJPFG8832C1ZT',
  'service_no' => '996812',
];

// Manifest details (make dynamic as needed)
$date = date('d.m.Y');
$manifest_no = 'A-M23-24/000XXXX'; // or auto
$vehicle = 'WB07K0098'; // optionally get from DB
$driver = 'JAMALUDDIN'; // optionally get from DB

// Calculate totals
$total_box = 0;
$total_amount = 0;
$dockets = [];
while($row = mysqli_fetch_assoc($res_data)) {
    $total_box += floatval($row['box']);
    $total_amount += floatval($row['amount']);
    $dockets[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manifest Print</title>
  <style>
    body { font-family: 'Calibri', Arial, sans-serif; font-size: 15px; color: #222; margin: 0; background: #fff; }
    .header { border: 1.8px solid #111; margin-bottom: 5px; padding: 12px 16px 6px 16px; display: flex; align-items: flex-start; }
    .header img { width: 200px; margin-right: 18px; }
    .company-meta { flex:1; }
    .company-meta .company-name { font-size: 27px; font-weight: bold; margin-bottom: 6px; }
    .company-meta .meta { margin-bottom: 3px; }
    .company-meta .bold { font-weight: 600; }
    .manifest-row { border: 1.6px solid #111; margin-bottom: 2px; padding: 6px 14px; display: flex; justify-content: space-between; background: #fafbfe;}
    .manifest-row .info { font-size: 15.7px; }
    .manifest-row .info strong { font-size: 15.9px; margin-right: 6px;}
    .manifest-title { text-align: center; font-size: 17.2px; font-weight: 600; letter-spacing:1px; margin: 7px 0 6px 0;}
    table { border: 1px solid #111; border-collapse: collapse; width: 100%; margin: 0 auto; }
    th, td { border: 1.2px solid #222; padding: 4.5px 8px; font-size: 14.3px; }
    th { background: #f3f7fa; font-size: 14.5px;}
    .noprint { margin: 16px 5px 0 0; }
    @media print {
      .noprint { display: none; }
      th, td { font-size: 13px;}
    }
    .manifest-footer-table {
  width: 100%;
  position: fixed;
  bottom: 0;
  left: 0;
  background: #fff;
}
.manifest-footer-table table {
  width: 100%;
  font-size: 16px;
  border: 0;
  border-collapse: collapse;
}
.manifest-footer-table td {
  border: 0;
  padding: 12px 4px 0 4px;
  width: 33%;
}
@media print {
  .manifest-footer-table {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
  }
}

  </style>
</head>
<body>
<div class="header">
    <div class="company-meta">
        <img src="images/logo.png" alt="Logo">
    </div>
    <div class="company-meta">
        <div class="company-name"><?= $company['name'] ?></div>
        <div class="meta"><?= $company['address'] ?></div>
        <div class="meta">Mobile Number: <?= $company['mobiles'] ?></div>
        <div class="meta">EMAIL: <?= $company['email'] ?></div>
        <div class="meta">GST NO: <?= $company['gst'] ?> | Service Number: <?= $company['service_no'] ?></div>
        <div class="meta bold">MANIFEST #: <?= $manifest_no ?></div>
    </div>
</div>
<div class="manifest-row">
  <div class="info"><strong>TO:</strong> <?= htmlspecialchars($office['office_name'] ?? '') ?><br><?= htmlspecialchars($office['office_address'] ?? '') ?><br><?= htmlspecialchars($office['office_phone'] ?? '') ?></div>
  <div class="info"><strong>DATE:</strong> <?= $date ?><br>
  <strong>VEHICLE NO:</strong> <?= $vehicle ?><br>
  <strong>DRIVER NAME:</strong> <?= $driver ?></div>
</div>
<div class="manifest-title">MANIFEST</div>
<table>
  <thead>
    <tr>
      <th>SLNO</th>
      <th>DOCKET NO</th>
      <th>CONSIGNEE</th>
      <th>ITEM</th>
      <th>ADDRESS</th>
      <th>BOX</th>
      <th>WEIGHT</th>
      <th>RATE</th>
      <th>AMOUNT</th>
      <th>E-WAY BILL</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($dockets as $idx => $row): ?>
    <tr>
      <td><?= $idx+1 ?></td>
      <td><?= htmlspecialchars($row['doc_no']) ?></td>
      <td><?= htmlspecialchars($row['client_name']) ?></td>
      <td><?= htmlspecialchars($row['item']) ?></td>
      <td><?= htmlspecialchars($row['client_address']) ?></td>
      <td><?= htmlspecialchars($row['box']) ?></td>
      <td><?= htmlspecialchars($row['weight']) ?></td>
      <td><?= htmlspecialchars($row['rate']) ?></td>
      <td><?= htmlspecialchars($row['amount']) ?></td>
      <td><?= htmlspecialchars($row['eway_bill']) ?></td>
    </tr>
    <?php endforeach; ?>
    <!-- Totals row -->
    <tr style="font-weight:bold;background:#f5f3e8;">
      <td colspan="5" style="text-align:right;">TOTAL BOX</td>
      <td><?= $total_box ?></td>
      <td></td>
      <td style="text-align:right;">TOTAL AMOUNT</td>
      <td><?= number_format($total_amount, 2) ?></td>
      <td></td>
    </tr>
  </tbody>
</table>

<div style="height:42px"></div>
<div class="manifest-footer-table">
  <table>
    <tr>
      <td>RECEIVED BY</td>
      <td>VERIFIED BY</td>
      <td style="text-align:right;font-weight:bold;">NORTH SUPER FAST SERVICE</td>
    </tr>
  </table>
</div>


<div class="noprint">
  <button onclick="window.print();" style="padding:7px 24px;font-size:15px;">Print</button>
  <button onclick="downloadPDF();" style="padding:7px 24px;font-size:15px;">Download PDF</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
function downloadPDF() {
  const { jsPDF } = window.jspdf;
  html2canvas(document.body, {scale:2}).then(function(canvas) {
    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF('l','pt','a4');
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    // Draw image, scale to fit width, keep ratio
    const imgWidth = pageWidth - 30, imgHeight = canvas.height * imgWidth / canvas.width;
    pdf.addImage(imgData, 'PNG', 15, 15, imgWidth, imgHeight);
    pdf.save("manifest.pdf");
  });
}
</script>
</body>
</html>
