<?php
require 'conn.php';
$manifest_id = intval($_GET['manifest_id'] ?? 0);

if (!$manifest_id) {
    echo "<div class='alert alert-danger' style='padding:20px;font-size:1.1rem;'><i class='fa fa-exclamation-triangle'></i> Invalid manifest ID.</div>";
    exit;
}

// Get manifest header
$manifest_query = "SELECT m.manifest_id, m.manifest_no, m.created_at, m.total_gross, m.total_pay_to, m.net_total, o.office_name
                   FROM tbl_manifest m
                   LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                   WHERE m.manifest_id = " . intval($manifest_id);
$manifest_result = mysqli_query($conn, $manifest_query);

if (!$manifest_result) {
    echo "<div class='alert alert-danger' style='padding:20px;font-size:1.1rem;'><i class='fa fa-exclamation-triangle'></i> Database error: " . mysqli_error($conn) . "</div>";
    exit;
}

if (mysqli_num_rows($manifest_result) == 0) {
    echo "<div class='alert alert-danger' style='padding:20px;font-size:1.1rem;'><i class='fa fa-exclamation-triangle'></i> Manifest not found.</div>";
    exit;
}

$manifest = mysqli_fetch_assoc($manifest_result);

// Get manifest details
$details_query = "SELECT detail_id, doc_no, client_name, item, client_address, box, weight, rate, amount, eway_bill, pay_to
                  FROM tbl_manifest_details 
                  WHERE manifest_id = " . intval($manifest_id) . " 
                  ORDER BY detail_id ASC";
$details_result = mysqli_query($conn, $details_query);

if (!$details_result) {
    echo "<div class='alert alert-danger' style='padding:20px;font-size:1.1rem;'><i class='fa fa-exclamation-triangle'></i> Error loading details: " . mysqli_error($conn) . "</div>";
    exit;
}
?>

<div style="background:white;border-radius:16px;padding:30px;box-shadow:0 4px 20px rgba(0,0,0,0.1);">
  
  <!-- Header Section -->
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:30px;flex-wrap:wrap;gap:20px;padding-bottom:20px;border-bottom:3px solid #e8eaf6;">
    <div style="flex:1;min-width:250px;">
      <div style="display:inline-block;background:#e3f2fd;padding:8px 16px;border-radius:8px;margin-bottom:12px;">
        <span style="font-size:0.9rem;color:#1976d2;font-weight:600;">MANIFEST</span>
      </div>
      <h2 style="margin:0;color:#1a1a1a;font-size:2rem;font-weight:800;margin-bottom:12px;">
        <?= htmlspecialchars($manifest['manifest_no']) ?>
      </h2>
      <div style="display:flex;flex-direction:column;gap:8px;font-size:1rem;">
        <div style="display:flex;align-items:center;gap:8px;color:#555;">
          <i class="fa fa-building" style="color:#4caf50;font-size:1.1rem;"></i>
          <strong style="color:#333;"><?= htmlspecialchars($manifest['office_name']) ?></strong>
        </div>
        <div style="display:flex;align-items:center;gap:8px;color:#555;">
          <i class="fa fa-calendar" style="color:#ff9800;font-size:1.1rem;"></i>
          <span><?= date('d M Y, h:i A', strtotime($manifest['created_at'])) ?></span>
        </div>
      </div>
    </div>
    <div>
      <button type="button" class="btn btn-success btn-lg" onclick="window.open('manifest_print.php?manifest_id=<?= $manifest_id ?>', '_blank')" 
              style="font-weight:700;padding:14px 32px;font-size:1.1rem;border-radius:10px;box-shadow:0 4px 12px rgba(76,175,80,0.3);">
        <i class="fa fa-print" style="margin-right:8px;"></i> Print Manifest
      </button>
    </div>
  </div>

  <!-- Financial Summary Cards -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:35px;">
    <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:25px;border-radius:12px;color:white;box-shadow:0 6px 20px rgba(102,126,234,0.4);">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
        <i class="fa fa-calculator" style="font-size:1.8rem;opacity:0.9;"></i>
        <div style="font-size:0.95rem;opacity:0.95;font-weight:600;">Gross Total</div>
      </div>
      <div style="font-size:2.2rem;font-weight:800;">₹<?= number_format($manifest['total_gross'], 2) ?></div>
    </div>
    
    <div style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);padding:25px;border-radius:12px;color:white;box-shadow:0 6px 20px rgba(245,87,108,0.4);">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
        <i class="fa fa-money" style="font-size:1.8rem;opacity:0.9;"></i>
        <div style="font-size:0.95rem;opacity:0.95;font-weight:600;">Total Pay To</div>
      </div>
      <div style="font-size:2.2rem;font-weight:800;">₹<?= number_format($manifest['total_pay_to'], 2) ?></div>
    </div>
    
    <div style="background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%);padding:25px;border-radius:12px;color:white;box-shadow:0 6px 20px rgba(79,172,254,0.4);">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
        <i class="fa fa-check-circle" style="font-size:1.8rem;opacity:0.9;"></i>
        <div style="font-size:0.95rem;opacity:0.95;font-weight:600;">Net Total</div>
      </div>
      <div style="font-size:2.2rem;font-weight:800;">₹<?= number_format($manifest['net_total'], 2) ?></div>
    </div>
  </div>

  <!-- Shipment Details Section -->
  <div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding:15px;background:#f5f7fa;border-radius:10px;">
      <i class="fa fa-list-alt" style="color:#1976d2;font-size:1.5rem;"></i>
      <h3 style="margin:0;font-weight:700;color:#1a1a1a;font-size:1.4rem;">Shipment Details</h3>
      <span style="margin-left:auto;background:#1976d2;color:white;padding:6px 14px;border-radius:20px;font-size:0.9rem;font-weight:600;">
        <?= mysqli_num_rows($details_result) ?> Items
      </span>
    </div>
    
    <div class="table-responsive" style="border-radius:12px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.08);">
      <table class="table table-hover" style="margin:0;background:white;font-size:1rem;">
        <thead>
          <tr style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;">
            <th style="padding:16px 12px;font-weight:700;font-size:1rem;white-space:nowrap;">SL No</th>
            <th style="padding:16px 12px;font-weight:700;font-size:1rem;white-space:nowrap;">Docket No</th>
            <th style="padding:16px 12px;font-weight:700;font-size:1rem;min-width:150px;">Consignee</th>
            <th style="padding:16px 12px;font-weight:700;font-size:1rem;min-width:120px;">Item</th>
            <th style="padding:16px 12px;font-weight:700;font-size:1rem;min-width:180px;">Address</th>
            <th style="padding:16px 12px;font-weight:700;font-size:1rem;text-align:center;">Box</th>
            <th style="padding:16px 12px;font-weight:700;font-size:1rem;text-align:right;white-space:nowrap;">Weight (kg)</th>
            <th style="padding:16px 12px;font-weight:700;font-size:1rem;text-align:right;">Rate</th>
            <th style="padding:16px 12px;font-weight:700;font-size:1rem;text-align:right;">Amount</th>
            <th style="padding:16px 12px;font-weight:700;font-size:1rem;min-width:120px;">E-way Bill</th>
            <th style="padding:16px 12px;font-weight:700;font-size:1rem;text-align:right;">Pay To</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (mysqli_num_rows($details_result) > 0) {
            $sl = 1;
            while ($row = mysqli_fetch_assoc($details_result)): 
          ?>
            <tr style="border-bottom:1px solid #e0e0e0;">
              <td style="padding:14px 12px;font-weight:700;color:#666;font-size:1rem;"><?= $sl++ ?></td>
              <td style="padding:14px 12px;font-weight:700;color:#1976d2;font-size:1rem;white-space:nowrap;"><?= htmlspecialchars($row['doc_no']) ?></td>
              <td style="padding:14px 12px;color:#333;font-size:1rem;font-weight:600;"><?= htmlspecialchars($row['client_name']) ?></td>
              <td style="padding:14px 12px;color:#555;font-size:0.95rem;"><?= htmlspecialchars($row['item']) ?></td>
              <td style="padding:14px 12px;color:#555;font-size:0.95rem;line-height:1.4;"><?= htmlspecialchars($row['client_address']) ?></td>
              <td style="padding:14px 12px;text-align:center;font-weight:700;color:#333;font-size:1rem;"><?= htmlspecialchars($row['box']) ?></td>
              <td style="padding:14px 12px;text-align:right;color:#333;font-size:1rem;"><?= number_format($row['weight'], 2) ?></td>
              <td style="padding:14px 12px;text-align:right;color:#333;font-size:1rem;">₹<?= number_format($row['rate'], 2) ?></td>
              <td style="padding:14px 12px;text-align:right;font-weight:700;color:#4caf50;font-size:1.05rem;">₹<?= number_format($row['amount'], 2) ?></td>
              <td style="padding:14px 12px;color:#555;font-size:0.95rem;"><?= htmlspecialchars($row['eway_bill'] ?: '-') ?></td>
              <td style="padding:14px 12px;text-align:right;font-weight:700;color:#f44336;font-size:1.05rem;">₹<?= number_format($row['pay_to'], 2) ?></td>
            </tr>
          <?php 
            endwhile; 
          } else {
            echo '<tr><td colspan="11" style="text-align:center;padding:50px;color:#999;">
                  <i class="fa fa-inbox" style="font-size:3rem;display:block;margin-bottom:15px;opacity:0.3;"></i>
                  <div style="font-size:1.2rem;font-weight:600;">No shipment details found</div>
                  </td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
/* Responsive Design */
@media (max-width: 992px) {
  h2 { font-size: 1.5rem !important; }
  .btn-lg { padding: 12px 24px !important; font-size: 1rem !important; }
}

@media (max-width: 768px) {
  h2 { font-size: 1.3rem !important; }
  .table-responsive {
    font-size: 0.9rem !important;
  }
  .table-responsive th,
  .table-responsive td {
    padding: 10px 8px !important;
    white-space: nowrap;
  }
  .btn-lg {
    width: 100%;
    margin-top: 15px;
  }
}

@media (max-width: 576px) {
  h2 { font-size: 1.1rem !important; }
  .table-responsive {
    font-size: 0.85rem !important;
  }
  .table-responsive th,
  .table-responsive td {
    padding: 8px 6px !important;
  }
}

/* Table hover effect */
.table-hover tbody tr:hover {
  background-color: #f5f7fa !important;
  cursor: pointer;
}

/* Smooth transitions */
.btn {
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
}

/* Print button pulse animation */
@keyframes pulse {
  0% { box-shadow: 0 4px 12px rgba(76,175,80,0.3); }
  50% { box-shadow: 0 6px 20px rgba(76,175,80,0.5); }
  100% { box-shadow: 0 4px 12px rgba(76,175,80,0.3); }
}

.btn-success {
  animation: pulse 2s infinite;
}
</style>
