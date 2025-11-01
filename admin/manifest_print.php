<?php
require 'conn.php';

$manifest_id = intval($_GET['manifest_id'] ?? 0);
if (!$manifest_id) {
    die("Invalid manifest ID. Please provide a valid manifest_id parameter.");
}

// -- Get Manifest info with Office details --
$manifest_sql = "SELECT m.*, o.*, c.car_number, d.driver_name
    FROM tbl_manifest m 
    JOIN tbl_offices o ON m.office_id = o.office_id 
    LEFT JOIN tbl_car c ON m.car_id = c.car_id
    LEFT JOIN tbl_driver d ON m.driver_id = d.driver_id
    WHERE m.manifest_id = $manifest_id";
$result = mysqli_query($conn, $manifest_sql);

if (!$result) {
    die("Database error: " . mysqli_error($conn));
}

$manifest = mysqli_fetch_assoc($result);

if (!$manifest) {
    die("Manifest not found with ID: $manifest_id. Please check if the manifest exists in the database.");
}

// -- Get Manifest Details --
$res_data = mysqli_query($conn, "SELECT * FROM tbl_manifest_details WHERE manifest_id = $manifest_id ORDER BY id ASC");

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
$manifest_no = $manifest['manifest_no'] ?? 'A-M23-24/000XXXX';
$vehicle = $manifest['car_number'] ?? 'N/A';
$driver = $manifest['driver_name'] ?? 'N/A';

// Get all items and calculate totals (for verification)
$total_box = 0;
$total_amount = 0;
$total_pay_to = 0;
$dockets = [];
while($row = mysqli_fetch_assoc($res_data)) {
    $total_box += floatval($row['box']);
    $total_amount += floatval($row['amount']);
    $total_pay_to += floatval($row['pay_to']);
    $dockets[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manifest Print</title>
  <style>
    @page {
      size: A4 landscape;
      margin: 10mm 5mm;
    }
    body { 
      font-family: 'Calibri', Arial, sans-serif; 
      font-size: 15px; 
      color: #222; 
      margin: 0; 
      background: #fff;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .header { 
      border: 1.8px solid #111; 
      margin-bottom: 5px; 
      padding: 12px 16px 6px 16px; 
      display: flex; 
      align-items: flex-start;
      page-break-inside: avoid;
    }
    .header img { width: 200px; margin-right: 18px; }
    .company-meta { flex:1; }
    .company-meta .company-name { font-size: 27px; font-weight: bold; margin-bottom: 6px; }
    .company-meta .meta { margin-bottom: 3px; }
    .company-meta .bold { font-weight: 600; }
    .manifest-row { 
      border: 1.6px solid #111; 
      margin-bottom: 2px; 
      padding: 6px 14px; 
      display: flex; 
      justify-content: space-between; 
      background: #fafbfe;
      page-break-inside: avoid;
    }
    .manifest-row .info { font-size: 15.7px; }
    .manifest-row .info strong { font-size: 15.9px; margin-right: 6px;}
    .manifest-title { 
      text-align: center; 
      font-size: 17.2px; 
      font-weight: 600; 
      letter-spacing:1px; 
      margin: 7px 0 6px 0;
      page-break-before: avoid;
      page-break-after: avoid;
    }
    table { 
      border: 1px solid #111; 
      border-collapse: collapse; 
      width: 100%; 
      margin: 0 auto;
      table-layout: fixed;
    }
    th, td { 
      border: 1.2px solid #222; 
      padding: 4.5px 8px; 
      font-size: 14.3px;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    th { 
      background: #f3f7fa; 
      font-size: 14.5px;
      white-space: nowrap;
    }
    .noprint { margin: 16px 5px 0 0; }
    
    /* Column widths */
    table th:nth-child(1) { width: 4%; }  /* SL.NO */
    table th:nth-child(2) { width: 10%; } /* DOCKET NO */
    table th:nth-child(3) { width: 15%; } /* CONSIGNEE */
    table th:nth-child(4) { width: 10%; } /* ITEM */
    table th:nth-child(5) { width: 20%; } /* ADDRESS */
    table th:nth-child(6) { width: 5%; }  /* BOX */
    table th:nth-child(7) { width: 7%; }  /* WEIGHT */
    table th:nth-child(8) { width: 7%; }  /* RATE */
    table th:nth-child(9) { width: 8%; }  /* AMOUNT */
    table th:nth-child(10) { width: 7%; } /* E-WAY BILL */
    table th:nth-child(11) { width: 7%; } /* PAY TO */
    
    @media print {
      .noprint { display: none; }
      body { margin: 0; }
      table { page-break-inside: auto; }
      tr { page-break-inside: avoid; page-break-after: auto; }
      th, td { font-size: 13px; border: 1.2px solid #222 !important; }
      thead { display: table-header-group; }
      tfoot { display: table-footer-group; }
    }
    
    .manifest-footer-table {
      width: 100%;
      margin-top: 20px;
      background: #fff;
      page-break-inside: avoid;
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
        <div class="meta bold">MANIFEST #: <?= htmlspecialchars($manifest['manifest_no']) ?></div>
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
      <th>PAY TO</th>
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
      <td style="text-align:right"><?= htmlspecialchars($row['box']) ?></td>
      <td style="text-align:right"><?= number_format($row['weight'], 2) ?></td>
      <td style="text-align:right"><?= number_format($row['rate'], 2) ?></td>
      <td style="text-align:right"><?= number_format($row['amount'], 2) ?></td>
      <td><?= htmlspecialchars($row['eway_bill']) ?></td>
      <td style="text-align:right"><?= number_format($row['pay_to'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
    <!-- Totals row -->
    <tr style="font-weight:bold;background:#f5f3e8;">
      <td colspan="5" style="text-align:right;">TOTAL</td>
      <td style="text-align:right"><?= $total_box ?></td>
      <td></td>
      <td style="text-align:right">GROSS AMOUNT</td>
      <td style="text-align:right"><?= number_format($manifest['total_gross'], 2) ?></td>
      <td style="text-align:right">TOTAL PAY TO</td>
      <td style="text-align:right"><?= number_format($manifest['total_pay_to'], 2) ?></td>
    </tr>
    <tr style="font-weight:bold;background:#f5f3e8;">
      <td colspan="8" style="text-align:right">NET TOTAL (Gross - Pay To)</td>
      <td colspan="3" style="text-align:right"><?= number_format($manifest['net_total'], 2) ?></td>
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
