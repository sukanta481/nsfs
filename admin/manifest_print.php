<?php
require 'conn.php';

$manifest_id = intval($_GET['manifest_id'] ?? 0);
if (!$manifest_id) {
    die("Invalid manifest ID. Please provide a valid manifest_id parameter.");
}

// -- Get Manifest info with Office details --
$manifest_sql = "SELECT m.*, o.*, c.car_number, s.staff_name as driver_name
    FROM tbl_manifest m 
    JOIN tbl_offices o ON m.office_id = o.office_id 
    LEFT JOIN tbl_car c ON m.car_id = c.car_id
    LEFT JOIN tbl_staff s ON m.driver_id = s.staff_id AND s.staff_role = 'Driver'
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
// Use manifest_date if available, otherwise fall back to created_at or today
$manifest_date_value = $manifest['manifest_date'] ?? ($manifest['created_at'] ? date('Y-m-d', strtotime($manifest['created_at'])) : date('Y-m-d'));
$date = date('d.m.Y', strtotime($manifest_date_value));
$manifest_no = $manifest['manifest_no'] ?? 'A-M23-24/000XXXX';
$vehicle = $manifest['car_number'] ?? 'N/A';
$driver = $manifest['driver_name'] ?? 'N/A';

// Office details (from the JOIN query, it's all in $manifest array)
$office = [
    'office_name' => $manifest['office_name'] ?? '',
    'office_person_name' => $manifest['office_person_name'] ?? '',
    'office_address' => $manifest['office_address'] ?? '',
    'office_phone' => $manifest['office_phone'] ?? ''
];

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
      size: A4 portrait;
      margin: 10mm 8mm;
    }
    body { 
      font-family: 'Calibri', Arial, sans-serif; 
      font-size: 12px; 
      color: #222; 
      margin: 0; 
      background: #fff;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .header { 
      border: 1.5px solid #111; 
      margin-bottom: 4px; 
      padding: 8px 12px 4px 12px; 
      display: flex; 
      align-items: flex-start;
      page-break-inside: avoid;
    }
    .header img { width: 140px; margin-right: 12px; }
    .company-meta { flex:1; }
    .company-meta .company-name { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
    .company-meta .meta { margin-bottom: 2px; font-size: 11px; }
    .company-meta .bold { font-weight: 600; font-size: 12px; }
    .manifest-row { 
      border: 1.5px solid #111; 
      margin-bottom: 2px; 
      padding: 5px 10px; 
      display: flex; 
      justify-content: space-between; 
      background: #fafbfe;
      page-break-inside: avoid;
    }
    .manifest-row .info { font-size: 11px; line-height: 1.4; }
    .manifest-row .info strong { font-size: 11.5px; margin-right: 4px;}
    .manifest-title { 
      text-align: center; 
      font-size: 14px; 
      font-weight: 600; 
      letter-spacing:1px; 
      margin: 4px 0 3px 0;
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
      border: 1px solid #222; 
      padding: 3px 4px; 
      font-size: 10px;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    th { 
      background: #f3f7fa; 
      font-size: 10.5px;
      white-space: nowrap;
      font-weight: 700;
    }
    .noprint { margin: 16px 5px 0 0; }
    
    /* Column widths optimized for portrait */
    table th:nth-child(1) { width: 4%; }   /* SL.NO */
    table th:nth-child(2) { width: 11%; }  /* DOCKET NO */
    table th:nth-child(3) { width: 14%; }  /* CONSIGNEE */
    table th:nth-child(4) { width: 12%; }  /* ITEM */
    table th:nth-child(5) { width: 18%; }  /* ADDRESS */
    table th:nth-child(6) { width: 5%; }   /* BOX */
    table th:nth-child(7) { width: 6%; }   /* WEIGHT */
    table th:nth-child(8) { width: 6%; }   /* RATE */
    table th:nth-child(9) { width: 8%; }   /* AMOUNT */
    table th:nth-child(10) { width: 8%; }  /* E-WAY BILL */
    table th:nth-child(11) { width: 8%; }  /* PAY TO */
    
    @media print {
      .noprint { display: none; }
      body { margin: 0; font-size: 10px; }
      table { page-break-inside: auto; }
      tr { page-break-inside: avoid; page-break-after: auto; }
      th, td { font-size: 9px; padding: 2px 3px; border: 1px solid #222 !important; }
      thead { display: table-header-group; }
      tfoot { display: table-footer-group; }
      .header img { width: 120px; }
      .company-meta .company-name { font-size: 16px; }
      .company-meta .meta { font-size: 10px; }
    }
    
    .manifest-footer-table {
      width: 100%;
      margin-top: 15px;
      background: #fff;
      page-break-inside: avoid;
    }
.manifest-footer-table table {
  width: 100%;
  font-size: 12px;
  border: 0;
  border-collapse: collapse;
}
.manifest-footer-table td {
  border: 0;
  padding: 10px 4px 0 4px;
  width: 33%;
}
@media print {
  .manifest-footer-table {
    position: relative;
    margin-top: 10px;
  }
  .manifest-footer-table table {
    font-size: 11px;
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
  <div class="info">
    <strong>TO</strong><br>
    <?php if(!empty($office['office_person_name'])): ?>
      <?= strtoupper(htmlspecialchars($office['office_person_name'])) ?><br>
    <?php endif; ?>
    <?= strtoupper(htmlspecialchars($office['office_name'] ?? '')) ?><br>
    <?= strtoupper(htmlspecialchars($office['office_address'] ?? '')) ?><br>
    <?= htmlspecialchars($office['office_phone'] ?? '') ?>
  </div>
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

<div style="height:25px"></div>
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
    const pdf = new jsPDF('p','pt','a4'); // 'p' for portrait
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    // Draw image, scale to fit width, keep ratio
    const imgWidth = pageWidth - 30;
    const imgHeight = canvas.height * imgWidth / canvas.width;
    
    // Handle multiple pages if content is too long
    let heightLeft = imgHeight;
    let position = 15;
    
    pdf.addImage(imgData, 'PNG', 15, position, imgWidth, imgHeight);
    heightLeft -= pageHeight;
    
    while (heightLeft >= 0) {
      position = heightLeft - imgHeight;
      pdf.addPage();
      pdf.addImage(imgData, 'PNG', 15, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;
    }
    
    pdf.save("manifest_<?= $manifest['manifest_no'] ?>.pdf");
  });
}
</script>
</body>
</html>
