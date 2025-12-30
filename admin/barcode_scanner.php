<?php
/**
 * Barcode Scanner - Scan-to-Print Label Automation
 * Flow: Scan barcode → Fetch docket → Generate label → Auto print
 */

require 'check_auth.php';
requirePermission('docket_view');
require 'conn.php';

require 'top_header.php';
?>
<body class="nav-md">
<div class="container body">
<div class="main_container">
<?php require 'left_panel.php'; ?>
<?php require 'header_banner.php'; ?>

<div class="right_col" role="main">

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.scanner-container {
    font-family: 'Inter', sans-serif;
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.scanner-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 25px;
    text-align: center;
}

.scanner-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
    font-weight: 700;
}

.scanner-header p {
    margin: 0;
    opacity: 0.9;
}

.scanner-grid {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 25px;
}

@media (max-width: 1200px) {
    .scanner-grid {
        grid-template-columns: 1fr;
    }
}

.scanner-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 20px;
    font-weight: 700;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-body {
    padding: 25px;
}

/* Scanner Input Section */
.scanner-input-section {
    text-align: center;
}

.barcode-input-wrapper {
    position: relative;
    margin-bottom: 20px;
}

#barcodeInput {
    width: 100%;
    padding: 20px 60px 20px 20px;
    font-size: 24px;
    font-weight: 600;
    text-align: center;
    border: 3px solid #667eea;
    border-radius: 15px;
    outline: none;
    transition: all 0.3s;
    letter-spacing: 2px;
}

#barcodeInput:focus {
    border-color: #27ae60;
    box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
}

#barcodeInput.error {
    border-color: #e74c3c;
    animation: shake 0.5s;
}

#barcodeInput.success {
    border-color: #27ae60;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

.scan-icon {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 28px;
    color: #667eea;
}

.scan-status {
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: none;
    font-weight: 600;
}

.scan-status.ready {
    display: block;
    background: #e8f5e9;
    color: #27ae60;
    border-left: 4px solid #27ae60;
}

.scan-status.error {
    display: block;
    background: #ffebee;
    color: #e74c3c;
    border-left: 4px solid #e74c3c;
}

.scan-status.warning {
    display: block;
    background: #fff3e0;
    color: #f57c00;
    border-left: 4px solid #f57c00;
}

.scan-status.processing {
    display: block;
    background: #e3f2fd;
    color: #1976d2;
    border-left: 4px solid #1976d2;
}

/* Print Settings */
.print-settings {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    margin-top: 20px;
}

.print-settings h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 16px;
}

.setting-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.setting-row label {
    font-weight: 600;
    color: #34495e;
}

.qty-control {
    display: flex;
    align-items: center;
    gap: 10px;
}

.qty-btn {
    width: 40px;
    height: 40px;
    border: 2px solid #667eea;
    background: white;
    color: #667eea;
    font-size: 20px;
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.qty-btn:hover {
    background: #667eea;
    color: white;
}

#printQty {
    width: 60px;
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 8px;
}

.toggle-switch {
    position: relative;
    width: 60px;
    height: 30px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 30px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #27ae60;
}

input:checked + .toggle-slider:before {
    transform: translateX(30px);
}

/* Docket Info Section */
.docket-info {
    display: none;
}

.docket-info.active {
    display: block;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.info-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    border-left: 4px solid #667eea;
}

.info-item label {
    display: block;
    font-size: 12px;
    color: #7f8c8d;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.info-item strong {
    font-size: 16px;
    color: #2c3e50;
}

.status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-delivered { background: #d4edda; color: #155724; }
.status-in-transit { background: #cce5ff; color: #004085; }
.status-out-for-delivery { background: #d1ecf1; color: #0c5460; }

/* Sticker Preview */
.sticker-preview-container {
    background: #f5f5f5;
    padding: 30px;
    border-radius: 15px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
}

.sticker-preview {
    width: 70mm;
    height: 70mm;
    border: 2px solid #333;
    background: white;
    padding: 2mm;
    font-family: Arial, sans-serif;
    font-size: 8px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.sticker-header-preview {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #000;
    padding-bottom: 1mm;
    margin-bottom: 1mm;
}

.sticker-table-preview {
    width: 100%;
    border-collapse: collapse;
    flex: 1;
}

.sticker-table-preview td {
    padding: 0.5mm 1mm;
    border-bottom: 1px solid #ddd;
    font-size: 7px;
}

.sticker-barcode-preview {
    text-align: center;
    padding: 1mm 0;
    border-top: 1px solid #000;
    margin-top: auto;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.btn-print {
    flex: 1;
    padding: 18px 30px;
    font-size: 18px;
    font-weight: 700;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-print-now {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    color: white;
}

.btn-print-now:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
}

.btn-print-now:disabled {
    background: #95a5a6;
    cursor: not-allowed;
    transform: none;
}

.btn-clear {
    background: #e74c3c;
    color: white;
    flex: 0.4;
}

.btn-clear:hover {
    background: #c0392b;
}

/* History Section */
.print-history {
    max-height: 400px;
    overflow-y: auto;
}

.history-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #eee;
    transition: background 0.3s;
}

.history-item:hover {
    background: #f8f9fa;
}

.history-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.history-icon.success {
    background: #d4edda;
    color: #27ae60;
}

.history-icon.error {
    background: #ffebee;
    color: #e74c3c;
}

.history-details {
    flex: 1;
}

.history-doc {
    font-weight: 700;
    color: #2c3e50;
    font-size: 16px;
}

.history-time {
    font-size: 12px;
    color: #7f8c8d;
}

.history-qty {
    background: #667eea;
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-weight: 600;
    font-size: 13px;
}

/* Audio Indicators */
.audio-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 20px 30px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 18px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
    display: none;
}

.audio-indicator.success {
    background: #27ae60;
    color: white;
}

.audio-indicator.error {
    background: #e74c3c;
    color: white;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Duplicate Confirmation Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 20px;
    max-width: 450px;
    text-align: center;
    animation: modalPop 0.3s ease-out;
}

@keyframes modalPop {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.modal-icon {
    font-size: 60px;
    color: #f39c12;
    margin-bottom: 20px;
}

.modal-title {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 15px;
}

.modal-message {
    color: #7f8c8d;
    margin-bottom: 25px;
    line-height: 1.6;
}

.modal-buttons {
    display: flex;
    gap: 15px;
}

.modal-btn {
    flex: 1;
    padding: 15px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
}

.modal-btn-cancel {
    background: #e0e0e0;
    color: #2c3e50;
}

.modal-btn-confirm {
    background: #27ae60;
    color: white;
}

.modal-btn:hover {
    transform: translateY(-2px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #95a5a6;
}

.empty-state i {
    font-size: 60px;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin: 0 0 10px 0;
    color: #7f8c8d;
}
</style>

<div class="scanner-container">
    <div class="scanner-header">
        <h1><i class="fa fa-barcode"></i> Barcode Scanner - Label Printer</h1>
        <p>Scan docket barcode to automatically print shipping labels</p>
    </div>

    <div class="scanner-grid">
        <!-- Left Column - Scanner Input -->
        <div>
            <div class="scanner-card">
                <div class="card-header">
                    <i class="fa fa-crosshairs"></i> Scan Barcode
                </div>
                <div class="card-body">
                    <div class="scanner-input-section">
                        <div class="barcode-input-wrapper">
                            <input type="text" id="barcodeInput" placeholder="Scan or type barcode..." autofocus autocomplete="off">
                            <i class="fa fa-barcode scan-icon"></i>
                        </div>
                        
                        <div id="scanStatus" class="scan-status ready">
                            <i class="fa fa-check-circle"></i> Ready to scan. Focus is on input field.
                        </div>
                    </div>

                    <div class="print-settings">
                        <h4><i class="fa fa-cog"></i> Print Settings</h4>
                        
                        <div class="setting-row">
                            <label>Labels to Print</label>
                            <div id="boxCountDisplay" style="font-weight: bold; color: #667eea; font-size: 18px;">-</div>
                        </div>
                        <p style="font-size: 12px; color: #7f8c8d; margin: 0 0 15px 0;"><i class="fa fa-info-circle"></i> Auto-determined by number of boxes</p>
                        
                        <div class="setting-row">
                            <label>Auto-Print on Scan</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="autoPrint" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-row">
                            <label>Sound Effects</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="soundEnabled" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print History -->
            <div class="scanner-card" style="margin-top: 20px;">
                <div class="card-header">
                    <i class="fa fa-history"></i> Print History
                </div>
                <div class="card-body print-history" id="printHistory">
                    <div class="empty-state">
                        <i class="fa fa-inbox"></i>
                        <h3>No prints yet</h3>
                        <p>Scanned labels will appear here</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Docket Info & Preview -->
        <div>
            <div class="scanner-card">
                <div class="card-header">
                    <i class="fa fa-file-text"></i> Docket Information
                </div>
                <div class="card-body">
                    <div id="docketInfo" class="docket-info">
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Docket Number</label>
                                <strong id="infoDocNo">-</strong>
                            </div>
                            <div class="info-item">
                                <label>Status</label>
                                <strong><span id="infoStatus" class="status-badge">-</span></strong>
                            </div>
                            <div class="info-item">
                                <label>Consignee</label>
                                <strong id="infoConsignee">-</strong>
                            </div>
                            <div class="info-item">
                                <label>Destination</label>
                                <strong id="infoDestination">-</strong>
                            </div>
                            <div class="info-item">
                                <label>Company</label>
                                <strong id="infoCompany">-</strong>
                            </div>
                            <div class="info-item">
                                <label>Service Type</label>
                                <strong id="infoService">-</strong>
                            </div>
                        </div>

                        <h4 style="margin: 20px 0 15px 0; color: #2c3e50;"><i class="fa fa-eye"></i> Label Preview</h4>
                        <div class="sticker-preview-container">
                            <div class="sticker-preview" id="stickerPreview">
                                <div class="sticker-header-preview">
                                    <span style="font-weight: bold; font-size: 9px;">NSFS</span>
                                    <span id="previewService" style="font-size: 8px;">SURFACE-NORMAL</span>
                                </div>
                                <table class="sticker-table-preview">
                                    <tr><td style="font-weight:bold;width:25mm;">Invoice #:</td><td id="previewInvoice">-</td></tr>
                                    <tr><td style="font-weight:bold;">Ref #:</td><td id="previewRef">-</td></tr>
                                    <tr><td style="font-weight:bold;">Package:</td><td id="previewPackage">1 of 1</td></tr>
                                    <tr><td style="font-weight:bold;">GCN:</td><td id="previewGCN">-</td></tr>
                                    <tr><td style="font-weight:bold;">Origin:</td><td id="previewOrigin">-</td></tr>
                                    <tr><td style="font-weight:bold;">Destination:</td><td id="previewDestination">-</td></tr>
                                    <tr><td style="font-weight:bold;">Consignee:</td><td id="previewConsignee">-</td></tr>
                                </table>
                                <div class="sticker-barcode-preview">
                                    <svg id="previewBarcode"></svg>
                                </div>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button class="btn-print btn-print-now" id="btnPrint" onclick="printLabels()" disabled>
                                <i class="fa fa-print"></i> Print Labels
                            </button>
                            <button class="btn-print btn-clear" onclick="clearScanner()">
                                <i class="fa fa-times"></i> Clear
                            </button>
                        </div>
                    </div>

                    <div id="emptyDocket" class="empty-state">
                        <i class="fa fa-barcode"></i>
                        <h3>Scan a Barcode</h3>
                        <p>Docket information will appear here after scanning</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Confirmation Modal -->
<div class="modal-overlay" id="duplicateModal">
    <div class="modal-content">
        <div class="modal-icon"><i class="fa fa-exclamation-triangle"></i></div>
        <h2 class="modal-title">Duplicate Print</h2>
        <p class="modal-message">
            This label was already printed <span id="duplicateCount">0</span> time(s) today.<br>
            Are you sure you want to print again?
        </p>
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-cancel" onclick="closeDuplicateModal()">Cancel</button>
            <button class="modal-btn modal-btn-confirm" onclick="confirmDuplicatePrint()">Print Anyway</button>
        </div>
    </div>
</div>

<!-- Delivered Warning Modal -->
<div class="modal-overlay" id="deliveredModal">
    <div class="modal-content">
        <div class="modal-icon" style="color: #e74c3c;"><i class="fa fa-ban"></i></div>
        <h2 class="modal-title">Cannot Print</h2>
        <p class="modal-message">
            This docket is marked as <strong>Delivered</strong>.<br>
            Printing labels for delivered shipments is not allowed.
        </p>
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-cancel" onclick="closeDeliveredModal()" style="flex: 1;">OK, Got it</button>
        </div>
    </div>
</div>

<!-- Audio Indicator -->
<div class="audio-indicator" id="audioIndicator"></div>

<!-- Barcode Library -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<!-- Audio Files (Base64 encoded beeps) -->
<audio id="successSound" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleVIiEU6h3+LCd0osGYKf1tuvV0YYJK3h6bhcPiYqyeXtt1MuJTLI5u+tTigoNMHg7a5PIi0yT9bz9LFLKSg6TOT4/LZLKCc8VOf7/7lLJyc+WOn9ALtLJic+Wur+ALxLJiY+W+r/ALxLJiY+W+r/ALxL" type="audio/wav">
</audio>
<audio id="errorSound" preload="auto">
    <source src="data:audio/wav;base64,UklGRl9vT19teleVIiEU6h3+LCdownsGYGCAACBhYqFbF1fdJir0osNMHg7a5PIi0yT9bz9LFLKSg6TOT4/LZLKCc8VOf7/7lLJyc+WOn9ALtLJic+Wur+ALxLJiY+W+r/ALxLJiY+W+r/ALxLJiY+W+r/" type="audio/wav">
</audio>

<script>
// Global Variables
let currentDocket = null;
let printHistory = [];
let printCounts = {}; // Track print counts per docket per day

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('barcodeInput');
    input.focus();
    
    // Handle barcode scan (Enter key from scanner)
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            processScan(this.value.trim());
        }
    });
    
    // Keep focus on input
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.qty-control') && !e.target.closest('.toggle-switch') && !e.target.closest('.btn-print')) {
            input.focus();
        }
    });
    
    // Load print counts from localStorage
    loadPrintCounts();
    
    // Generate empty barcode preview
    JsBarcode("#previewBarcode", "SCAN", {
        format: "CODE128",
        width: 1.5,
        height: 25,
        displayValue: true,
        fontSize: 10,
        margin: 2
    });
});

// Process scanned barcode
async function processScan(barcode) {
    if (!barcode) return;
    
    const input = document.getElementById('barcodeInput');
    const status = document.getElementById('scanStatus');
    
    // Clear input immediately for next scan
    input.value = '';
    
    // Update status
    status.className = 'scan-status processing';
    status.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing barcode: ' + barcode;
    
    try {
        // Fetch docket details
        const response = await fetch('api_barcode_lookup.php?barcode=' + encodeURIComponent(barcode));
        
        // Check if response is OK
        if (!response.ok) {
            showError('Server error: ' + response.status + ' ' + response.statusText);
            playSound('error');
            return;
        }
        
        // Try to parse JSON
        let data;
        const responseText = await response.text();
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', responseText);
            showError('Server returned invalid response. Check console for details.');
            playSound('error');
            return;
        }
        
        if (!data.success) {
            let errorMsg = data.message || 'Docket not found';
            if (data.error_code === 'AUTH_REQUIRED') {
                errorMsg = 'Session expired. Please refresh the page and login again.';
            }
            showError(errorMsg);
            playSound('error');
            input.classList.add('error');
            setTimeout(() => input.classList.remove('error'), 500);
            return;
        }
        
        currentDocket = data.docket;
        
        // Check if delivered - block printing
        if (currentDocket.status === 'Delivered') {
            showDeliveredWarning();
            playSound('error');
            return;
        }
        
        // Update UI with docket info
        updateDocketInfo(currentDocket);
        playSound('success');
        
        // Show success status
        status.className = 'scan-status ready';
        status.innerHTML = '<i class="fa fa-check-circle"></i> Docket found: ' + currentDocket.doc_no;
        
        input.classList.add('success');
        setTimeout(() => input.classList.remove('success'), 500);
        
        // Check for duplicate print
        const today = new Date().toDateString();
        const key = currentDocket.doc_no + '_' + today;
        
        if (printCounts[key] && printCounts[key] > 0) {
            // Show duplicate warning
            if (document.getElementById('autoPrint').checked) {
                showDuplicateWarning(printCounts[key]);
                return;
            }
        }
        
        // Auto-print if enabled
        if (document.getElementById('autoPrint').checked) {
            printLabels();
        }
        
    } catch (error) {
        console.error('Scan error:', error);
        showError('Network error. Please try again.');
        playSound('error');
    }
}

// Update docket info display
function updateDocketInfo(docket) {
    document.getElementById('emptyDocket').style.display = 'none';
    document.getElementById('docketInfo').classList.add('active');
    
    // Update info fields
    document.getElementById('infoDocNo').textContent = docket.doc_no;
    document.getElementById('infoConsignee').textContent = docket.client_name || '-';
    document.getElementById('infoDestination').textContent = docket.delivery_location || docket.client_address || '-';
    document.getElementById('infoCompany').textContent = docket.company_name || '-';
    document.getElementById('infoService').textContent = docket.service_type || 'SURFACE-NORMAL';
    
    // Update status badge
    const statusBadge = document.getElementById('infoStatus');
    statusBadge.textContent = docket.status;
    statusBadge.className = 'status-badge status-' + docket.status.toLowerCase().replace(/ /g, '-');
    
    // Update preview
    document.getElementById('previewService').textContent = docket.service_type || 'SURFACE-NORMAL';
    document.getElementById('previewInvoice').textContent = docket.invoice_no || docket.doc_no;
    document.getElementById('previewRef').textContent = docket.doc_no;
    document.getElementById('previewPackage').textContent = (docket.box || '1') + ' of ' + (docket.box || '1');
    document.getElementById('previewGCN').textContent = docket.doc_no;
    document.getElementById('previewOrigin').textContent = docket.pickup_location || '-';
    document.getElementById('previewDestination').textContent = docket.delivery_location || docket.client_address || '-';
    document.getElementById('previewConsignee').textContent = docket.client_name || '-';
    
    // Update barcode
    JsBarcode("#previewBarcode", docket.doc_no, {
        format: "CODE128",
        width: 1.5,
        height: 25,
        displayValue: true,
        fontSize: 10,
        margin: 2
    });
    
    // Update box count display
    const boxCount = parseInt(docket.box) || 1;
    document.getElementById('boxCountDisplay').textContent = boxCount + ' label' + (boxCount > 1 ? 's' : '');
    
    // Enable print button
    document.getElementById('btnPrint').disabled = false;
}

// Print labels
function printLabels() {
    if (!currentDocket) return;
    
    // Use box count as quantity
    const qty = parseInt(currentDocket.box) || 1;
    
    // Generate and print
    const printWindow = window.open('', '_blank', 'width=300,height=300');
    
    let labelsHTML = '';
    for (let i = 0; i < qty; i++) {
        const isLast = (i === qty - 1);
        labelsHTML += generateLabelHTML(currentDocket, i + 1, qty, isLast);
    }
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Labels - ${currentDocket.doc_no}</title>
            <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
            <style>
                @media print {
                    @page { size: 70mm 70mm; margin: 0; }
                    html, body { margin: 0; padding: 0; }
                    .label { page-break-after: always; }
                    .label.last-label { page-break-after: avoid; }
                }
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; }
                .label {
                    width: 70mm;
                    height: 70mm;
                    border: 1px solid #000;
                    padding: 2mm;
                    background: #fff;
                    display: flex;
                    flex-direction: column;
                }
                .label-header {
                    display: flex;
                    justify-content: space-between;
                    border-bottom: 1px solid #000;
                    padding-bottom: 1mm;
                    margin-bottom: 1mm;
                    font-size: 9px;
                    font-weight: bold;
                }
                .label-table { width: 100%; border-collapse: collapse; flex: 1; }
                .label-table td { padding: 0.5mm 1mm; border-bottom: 1px solid #ddd; font-size: 7px; }
                .label-table td:first-child { font-weight: bold; width: 25mm; }
                .label-barcode { text-align: center; padding: 1mm 0; border-top: 1px solid #000; margin-top: auto; }
            </style>
        </head>
        <body>
            ${labelsHTML}
            <script>
                document.querySelectorAll('.barcode-svg').forEach((svg, index) => {
                    JsBarcode(svg, "${currentDocket.doc_no}", {
                        format: "CODE128",
                        width: 1.5,
                        height: 25,
                        displayValue: true,
                        fontSize: 10,
                        margin: 2
                    });
                });
                setTimeout(() => { window.print(); window.close(); }, 500);
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
    
    // Record print
    recordPrint(currentDocket.doc_no, qty);
    addToHistory(currentDocket.doc_no, qty, true);
    
    showIndicator('success', `Printing ${qty} label(s) for ${currentDocket.doc_no}`);
}

// Generate single label HTML
function generateLabelHTML(docket, current, total, isLast = false) {
    return `
        <div class="label${isLast ? ' last-label' : ''}">
            <div class="label-header">
                <span>NSFS</span>
                <span>${docket.service_type || 'SURFACE-NORMAL'}</span>
            </div>
            <table class="label-table">
                <tr><td>Invoice #:</td><td>${docket.invoice_no || docket.doc_no}</td></tr>
                <tr><td>Ref #:</td><td>${docket.doc_no}</td></tr>
                <tr><td>Package:</td><td>${current} of ${total}</td></tr>
                <tr><td>GCN:</td><td>${docket.doc_no}</td></tr>
                <tr><td>Origin:</td><td>${docket.pickup_location || '-'}</td></tr>
                <tr><td>Destination:</td><td>${docket.delivery_location || docket.client_address || '-'}</td></tr>
                <tr><td>Consignee:</td><td>${docket.client_name || '-'}</td></tr>
            </table>
            <div class="label-barcode">
                <svg class="barcode-svg"></svg>
            </div>
        </div>
    `;
}

// Record print count
function recordPrint(docNo, qty) {
    const today = new Date().toDateString();
    const key = docNo + '_' + today;
    printCounts[key] = (printCounts[key] || 0) + qty;
    savePrintCounts();
}

// Save/Load print counts
function savePrintCounts() {
    localStorage.setItem('labelPrintCounts', JSON.stringify(printCounts));
}

function loadPrintCounts() {
    const saved = localStorage.getItem('labelPrintCounts');
    if (saved) {
        printCounts = JSON.parse(saved);
        // Clean old entries (keep only today)
        const today = new Date().toDateString();
        Object.keys(printCounts).forEach(key => {
            if (!key.endsWith('_' + today)) {
                delete printCounts[key];
            }
        });
        savePrintCounts();
    }
}

// Add to print history
function addToHistory(docNo, qty, success) {
    const historyDiv = document.getElementById('printHistory');
    const time = new Date().toLocaleTimeString();
    
    // Remove empty state if exists
    const emptyState = historyDiv.querySelector('.empty-state');
    if (emptyState) emptyState.remove();
    
    const item = document.createElement('div');
    item.className = 'history-item';
    item.innerHTML = `
        <div class="history-icon ${success ? 'success' : 'error'}">
            <i class="fa fa-${success ? 'check' : 'times'}"></i>
        </div>
        <div class="history-details">
            <div class="history-doc">${docNo}</div>
            <div class="history-time">${time}</div>
        </div>
        <div class="history-qty">${qty}x</div>
    `;
    
    historyDiv.insertBefore(item, historyDiv.firstChild);
}

// Clear scanner
function clearScanner() {
    currentDocket = null;
    document.getElementById('barcodeInput').value = '';
    document.getElementById('barcodeInput').focus();
    document.getElementById('docketInfo').classList.remove('active');
    document.getElementById('emptyDocket').style.display = 'block';
    document.getElementById('btnPrint').disabled = true;
    document.getElementById('boxCountDisplay').textContent = '-';
    
    const status = document.getElementById('scanStatus');
    status.className = 'scan-status ready';
    status.innerHTML = '<i class="fa fa-check-circle"></i> Ready to scan. Focus is on input field.';
}

// Show error
function showError(message) {
    const status = document.getElementById('scanStatus');
    status.className = 'scan-status error';
    status.innerHTML = '<i class="fa fa-times-circle"></i> ' + message;
}

// Play sound
function playSound(type) {
    if (!document.getElementById('soundEnabled').checked) return;
    
    // Use Web Audio API for reliable sound
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    
    if (type === 'success') {
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        gainNode.gain.value = 0.3;
        oscillator.start();
        setTimeout(() => {
            oscillator.frequency.value = 1000;
        }, 100);
        setTimeout(() => oscillator.stop(), 200);
    } else {
        oscillator.frequency.value = 300;
        oscillator.type = 'square';
        gainNode.gain.value = 0.3;
        oscillator.start();
        setTimeout(() => oscillator.stop(), 300);
    }
}

// Show indicator
function showIndicator(type, message) {
    const indicator = document.getElementById('audioIndicator');
    indicator.className = 'audio-indicator ' + type;
    indicator.innerHTML = '<i class="fa fa-' + (type === 'success' ? 'check' : 'times') + '"></i> ' + message;
    indicator.style.display = 'block';
    
    setTimeout(() => {
        indicator.style.display = 'none';
    }, 3000);
}

// Duplicate warning modal
function showDuplicateWarning(count) {
    document.getElementById('duplicateCount').textContent = count;
    document.getElementById('duplicateModal').classList.add('active');
}

function closeDuplicateModal() {
    document.getElementById('duplicateModal').classList.remove('active');
    document.getElementById('barcodeInput').focus();
}

function confirmDuplicatePrint() {
    closeDuplicateModal();
    printLabels();
}

// Delivered warning modal
function showDeliveredWarning() {
    document.getElementById('deliveredModal').classList.add('active');
}

function closeDeliveredModal() {
    document.getElementById('deliveredModal').classList.remove('active');
    clearScanner();
}
</script>

</div><!-- /right_col -->
<?php require 'footer.php'; ?>
</div><!-- /main_container -->
</div><!-- /container body -->
</body>
</html>
