<?php
require('conn.php');
$docket_id = intval($_REQUEST['docket_id'] ?? 0);

if($docket_id <= 0) {
    die("Invalid docket ID");
}

// Fetch docket details from docket_details table
$sql = "SELECT dd.*, o.office_name, o.office_address, o.office_phone
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        WHERE dd.docket_id = $docket_id LIMIT 1";
$result = mysqli_query($conn, $sql);

if(!$result) {
    die("Database Error: " . mysqli_error($conn));
}

$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Docket not found. Please check the docket ID.");
}

// Helper: Format date
function formatDate($d) { 
    return $d ? date('d-M-Y', strtotime($d)) : ''; 
}

// Pull fields safely
$doc_no = $data['doc_no'] ?? '-';
$pickup_date = formatDate($data['pickup_datetime'] ?? '');
$car_no = $data['car_number'] ?? '-';
$car_type = $data['car_model'] ?? '';
$consignor_name = $data['company_name'] ?? '-';
$consignor_addr = $data['company_address'] ?? '-';
$consignor_phone = $data['company_phone'] ?? '-';
$consignor_email = $data['company_email'] ?? '-';
$consignee_name = $data['client_name'] ?? '-';
$consignee_addr = $data['client_address'] ?? '-';
$consignee_phone = $data['client_phone'] ?? '-';
$consignee_email = $data['client_email'] ?? '-';
$pickup_location = $data['pickup_location'] ?? '-';
$delivery_location = $data['delivery_location'] ?? '-';
$box = $data['box'] ?? '0';
$weight = $data['weight'] ?? '0.00';
$dimensions = $data['dimensions'] ?? '-';
$eway_bill = $data['eway_bill'] ?? '-';
$invoice_no = $data['invoice_no'] ?? '-';
$invoice_amount = $data['invoice_amount'] ?? '0.00';
$item = $data['item'] ?? 'CONSUMER GOODS';
$service_mode = $data['service_type'] ?? 'SURFACE-NORMAL';
$trip_group = $data['trip_group_id'] ?? '-';
$office_name = $data['office_name'] ?? 'NORTH SUPER FAST SERVICE';
$office_addr = $data['office_address'] ?? 'Barasat, Algoria, Moynacheck, Kolkata - 700125';
$office_phone = $data['office_phone'] ?? '9933999998';
$office_email = 'onestepup@northsuperfastservice.com'; // Default email
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Docket - <?= htmlspecialchars($doc_no) ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            @page { 
                size: A4 portrait; 
                margin: 10mm;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            background: #f5f5f5;
            padding: 15px;
        }
        
        .docket-container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        /* Header Section */
        .docket-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .company-logo {
            width: 120px;
            height: auto;
        }
        
        .company-info {
            text-align: right;
            font-size: 10px;
            line-height: 1.5;
        }
        
        .company-name {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 3px;
        }
        
        .copy-label {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #e0e0e0;
            padding: 5px 12px;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #999;
        }
        
        /* Top Info Bar */
        .top-info-bar {
            display: flex;
            justify-content: space-between;
            padding: 8px 10px;
            background: #f8f8f8;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            font-size: 10px;
        }
        
        .info-item {
            display: flex;
            gap: 5px;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        /* Title Section */
        .title-section {
            text-align: center;
            padding: 10px;
            background: #f0f0f0;
            border: 1px solid #999;
            margin-bottom: 10px;
        }
        
        .title-main {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        .gcn-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 10px;
            border: 1px solid #ccc;
            margin-bottom: 8px;
            font-size: 10px;
        }
        
        /* Party Details */
        .party-section {
            display: flex;
            border: 1px solid #999;
            margin-bottom: 8px;
        }
        
        .party-box {
            flex: 1;
            padding: 10px;
        }
        
        .party-box:first-child {
            border-right: 1px solid #999;
        }
        
        .party-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        
        .party-detail {
            margin-bottom: 4px;
            font-size: 10px;
        }
        
        .party-detail strong {
            display: inline-block;
            min-width: 60px;
            font-weight: bold;
        }
        
        .location-info {
            display: flex;
            gap: 15px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #ccc;
            font-size: 9px;
        }
        
        /* Package Details */
        .package-section {
            border: 1px solid #999;
            margin-bottom: 8px;
        }
        
        .package-row {
            display: flex;
            background: #f8f8f8;
            border-bottom: 1px solid #999;
        }
        
        .package-cell {
            flex: 1;
            padding: 6px 8px;
            border-right: 1px solid #999;
            font-size: 10px;
            text-align: center;
            font-weight: bold;
        }
        
        .package-cell:last-child {
            border-right: none;
        }
        
        .package-data {
            display: flex;
        }
        
        .package-data-cell {
            flex: 1;
            padding: 8px;
            border-right: 1px solid #999;
            font-size: 10px;
            text-align: center;
        }
        
        .package-data-cell:last-child {
            border-right: none;
        }
        
        /* Invoice Table */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9px;
        }
        
        .invoice-table th {
            background: #f0f0f0;
            padding: 6px 8px;
            border: 1px solid #999;
            font-weight: bold;
            text-align: center;
        }
        
        .invoice-table td {
            padding: 8px;
            border: 1px solid #999;
            text-align: center;
        }
        
        /* Bottom Section */
        .bottom-section {
            display: flex;
            border: 1px solid #999;
            margin-bottom: 8px;
            min-height: 120px;
        }
        
        .bottom-left {
            flex: 1;
            padding: 10px;
            border-right: 1px solid #999;
        }
        
        .bottom-right {
            flex: 1;
            padding: 10px;
        }
        
        .section-title {
            font-weight: bold;
            margin-bottom: 6px;
            font-size: 10px;
        }
        
        .terms-text {
            font-size: 8px;
            line-height: 1.4;
            margin: 8px 0;
            color: #333;
        }
        
        .signature-area {
            margin-top: 15px;
            font-size: 9px;
            font-weight: bold;
        }
        
        /* Proof of Delivery */
        .pod-table {
            width: 100%;
            margin-top: 8px;
            font-size: 9px;
        }
        
        .pod-table td {
            padding: 4px 0;
            border-bottom: 1px solid #ccc;
        }
        
        .barcode-section {
            text-align: center;
            padding: 10px;
            margin: 10px 0;
        }
        
        .barcode-text {
            font-family: 'Libre Barcode 39', monospace;
            font-size: 40px;
            letter-spacing: 2px;
        }
        
        .warning-box {
            background: #fff9e6;
            border: 1px solid #ffc107;
            padding: 8px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            margin: 8px 0;
        }
        
        .footer-note {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
        }
        
        /* Buttons */
        .button-container {
            text-align: center;
            margin: 15px 0;
        }
        
        .btn {
            padding: 10px 25px;
            margin: 0 5px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .btn-print {
            background: #4CAF50;
            color: white;
        }
        
        .btn-download {
            background: #2196F3;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="docket-container">
        <div class="copy-label">Consignor Copy</div>
        
        <!-- Header -->
        <div class="docket-header">
            <img src="images/logo.png" class="company-logo" alt="Company Logo">
            <div class="company-info">
                <div class="company-name"><?= strtoupper(htmlspecialchars($office_name)) ?></div>
                <div><?= htmlspecialchars($office_addr) ?></div>
                <div>E: <?= htmlspecialchars($office_email) ?>, T: <?= htmlspecialchars($office_phone) ?></div>
                <div>Website: www.northsuperfastservice.com</div>
            </div>
        </div>
        
        <!-- Top Info Bar -->
        <div class="top-info-bar">
            <div class="info-item">
                <span class="info-label">PAN No.:</span>
                <span>AADCD1980E</span>
            </div>
            <div class="info-item">
                <span class="info-label">Service Tax No.:</span>
                <span>-</span>
            </div>
            <div class="info-item">
                <span class="info-label">CIN No.:</span>
                <span>-</span>
            </div>
        </div>
        
        <!-- Vehicle & GCN Info -->
        <div class="gcn-info">
            <div>
                <span class="info-label">Vehicle No.:</span>
                <span><?= htmlspecialchars($car_no) ?></span>
            </div>
            <div style="text-align: center; flex: 1;">
                <span class="info-label" style="font-size: 12px;">GOODS CONSIGNMENT NOTE - Non Negotiable</span>
            </div>
            <div style="text-align: right;">
                <div><span class="info-label">G.C.N No.:</span> <?= htmlspecialchars($doc_no) ?></div>
                <div><span class="info-label">Date:</span> <?= htmlspecialchars($pickup_date) ?></div>
                <div><span class="info-label">Ref1:</span> <?= htmlspecialchars($trip_group) ?></div>
                <div><span class="info-label">Ref2:</span> -</div>
            </div>
        </div>
        
        <div class="gcn-info">
            <div><span class="info-label">Vehicle Type:</span> <?= htmlspecialchars($car_type) ?></div>
        </div>
        
        <!-- Consignor & Consignee -->
        <div class="party-section">
            <div class="party-box">
                <div class="party-title">Consignor:</div>
                <div class="party-detail"><?= htmlspecialchars($consignor_name) ?></div>
                <div class="party-detail"><?= htmlspecialchars($consignor_addr) ?></div>
                <div class="party-detail"><strong>Phone:</strong> <?= htmlspecialchars($consignor_phone) ?></div>
                <div class="location-info">
                    <div><strong>From:</strong> <?= htmlspecialchars($pickup_location) ?></div>
                </div>
            </div>
            <div class="party-box">
                <div class="party-title">Consignee:</div>
                <div class="party-detail"><?= htmlspecialchars($consignee_name) ?></div>
                <div class="party-detail"><?= htmlspecialchars($consignee_addr) ?></div>
                <div class="party-detail"><strong>Phone:</strong> <?= htmlspecialchars($consignee_phone) ?></div>
                <div class="location-info">
                    <div><strong>To:</strong> <?= htmlspecialchars($delivery_location) ?></div>
                </div>
            </div>
        </div>
        
        <!-- TIN Numbers -->
        <div class="gcn-info">
            <div><span class="info-label">Tin No:</span></div>
            <div><span class="info-label">Tin No:</span></div>
            <div style="text-align: right;">
                <span class="info-label">Service Tax Payable by:</span> Consignee
            </div>
        </div>
        
        <!-- Package Details -->
        <div class="package-section">
            <div class="package-row">
                <div class="package-cell">Volume</div>
                <div class="package-cell">Gross Wt</div>
                <div class="package-cell">Chargeable Wt</div>
                <div class="package-cell">Service Modes:</div>
                <div class="package-cell">Freight:</div>
            </div>
            <div class="package-data">
                <div class="package-data-cell"><?= htmlspecialchars($dimensions) ?></div>
                <div class="package-data-cell"><?= htmlspecialchars($weight) ?> Kgs</div>
                <div class="package-data-cell"><?= htmlspecialchars($weight) ?> Kgs</div>
                <div class="package-data-cell"><?= htmlspecialchars($service_mode) ?></div>
                <div class="package-data-cell">To Bill</div>
            </div>
        </div>
        
        <!-- Terms -->
        <div class="terms-text" style="text-align: center; padding: 5px; background: #f8f8f8; border: 1px solid #ddd;">
            I received the goods for transportation on terms condition printed on our website.
        </div>
        
        <!-- Invoice Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Invoice Amount</th>
                    <th>Eway bill No</th>
                    <th>Description of Goods (said to contain)</th>
                    <th>No of Pkg</th>
                    <th>Remarks</th>
                    <th>Trip number</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($invoice_no) ?></td>
                    <td>₹ <?= number_format((float)$invoice_amount, 2) ?></td>
                    <td><?= htmlspecialchars($eway_bill) ?></td>
                    <td>• <?= htmlspecialchars($item) ?><br>• <?= htmlspecialchars($box) ?> Units (<?= htmlspecialchars($dimensions) ?>)</td>
                    <td><?= htmlspecialchars($box) ?></td>
                    <td></td>
                    <td><?= htmlspecialchars($trip_group) ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="warning-box">
            *PLEASE DO NOT SIGN OR STAMP ON THE BARCODE*
        </div>
        
        <!-- Barcode -->
        <div class="barcode-section">
            <svg id="barcode"></svg>
        </div>
        
        <!-- Bottom Section -->
        <div class="bottom-section">
            <div class="bottom-left">
                <div class="section-title">Declared Value:</div>
                <div><?= htmlspecialchars($data['amount'] ?? 'NIL') ?></div>
                
                <div class="terms-text">
                    We do hereby certify that the above particulars of goods consigned by us have been and have been correctly entered into and the consignment is booked with full knowledge of the terms and conditions of this G.C Note, which we accept.
                </div>
                
                <div class="signature-area">
                    Signature of Consignor, his Agent or Representative
                </div>
            </div>
            
            <div class="bottom-right">
                <div class="section-title">Delivery Instruction:</div>
                <div class="terms-text">
                    Any Octroi, sales tax, entry tax or any duties or taxes as may be applicable on the consignment will be paid by consignee at the time of delivery of consignment.
                </div>
                
                <div class="section-title" style="margin-top: 10px;">Proof of delivery</div>
                <table class="pod-table">
                    <tr>
                        <td style="width: 33%;">Date:</td>
                        <td style="width: 33%;">Time:</td>
                        <td style="width: 34%;">Received by (Name Sign):</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="height: 30px;"></td>
                    </tr>
                    <tr>
                        <td colspan="3">Remarks:</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="height: 25px;"></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="footer-note">
            The terms and Conditions of this G.C.Note are mentioned on https://northsuperfastservice.com/terms-conditions If you are unable to see the T&Cs or have any queries, Please reach out to us through the contact details provided above.
        </div>
    </div>
    
    <div class="button-container no-print">
        <button class="btn btn-print" onclick="window.print()">
            <i class="fa fa-print"></i> Print Docket
        </button>
        <button class="btn btn-download" onclick="downloadPDF()">
            <i class="fa fa-download"></i> Download PDF
        </button>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    
    <script>
        // Generate Barcode
        JsBarcode("#barcode", "<?= $doc_no ?>", {
            format: "CODE128",
            width: 2,
            height: 50,
            displayValue: true,
            fontSize: 14,
            margin: 10
        });
        
        // Download as PDF (improved: html2canvas + jsPDF to preserve layout and pagination)
        function downloadPDF() {
            const element = document.querySelector('.docket-container');
            if (!element) return;

            const filename = 'Docket_<?= $doc_no ?>.pdf';
            
            // Access jsPDF from window object
            const { jsPDF } = window.jspdf;

            // Use html2canvas to render the element to a canvas
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff'
            }).then(function(canvas) {
                const imgData = canvas.toDataURL('image/png', 1.0);

                // Create jsPDF instance (A4 portrait in mm)
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();

                // Calculate the image dimensions in mm
                const imgWidthPx = canvas.width;
                const imgHeightPx = canvas.height;
                const imgHeightMm = (imgHeightPx * pdfWidth) / imgWidthPx;

                // Simply add the image to fit the page
                // If it's larger than one page, scale it down to fit
                if (imgHeightMm > pdfHeight) {
                    // Scale down to fit one page
                    const scaledWidth = (pdfHeight * imgWidthPx) / imgHeightPx;
                    pdf.addImage(imgData, 'PNG', (pdfWidth - scaledWidth) / 2, 0, scaledWidth, pdfHeight);
                } else {
                    // Fits on one page
                    pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, imgHeightMm);
                }

                pdf.save(filename);
            }).catch(function(err) {
                console.error('PDF generation error:', err);
                alert('Failed to generate PDF. Please try printing instead.');
            });
        }
    </script>
</body>
</html>
