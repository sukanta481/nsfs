<?php
require 'conn.php';
$manifest_id = intval($_GET['manifest_id'] ?? 0);

if (!$manifest_id) {
    echo "<div class='alert alert-danger' style='padding:20px;font-size:1.1rem;'><i class='fa fa-exclamation-triangle'></i> Invalid manifest ID.</div>";
    exit;
}

// Get manifest header
$manifest_query = "SELECT m.manifest_id, m.manifest_no, m.created_at, m.total_gross, m.total_pay_to, m.net_total, m.car_id, m.driver_id,
                   o.office_name, c.car_number, d.driver_name
                   FROM tbl_manifest m
                   LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                   LEFT JOIN tbl_car c ON m.car_id = c.car_id
                   LEFT JOIN tbl_driver d ON m.driver_id = d.driver_id
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
$details_query = "SELECT id, doc_no, client_name, item, client_address, box, weight, rate, amount, eway_bill, pay_to
                  FROM tbl_manifest_details 
                  WHERE manifest_id = " . intval($manifest_id) . " 
                  ORDER BY id ASC";
$details_result = mysqli_query($conn, $details_query);

if (!$details_result) {
    echo "<div class='alert alert-danger' style='padding:20px;font-size:1.1rem;'><i class='fa fa-exclamation-triangle'></i> Error loading details: " . mysqli_error($conn) . "</div>";
    exit;
}
?>

<div style="background:white;border-radius:20px;padding:45px;box-shadow:0 6px 30px rgba(0,0,0,0.12);max-width:100%;">
  
  <!-- Header Section -->
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:40px;flex-wrap:wrap;gap:25px;padding-bottom:30px;border-bottom:4px solid #e3f2fd;">
    <div style="flex:1;min-width:300px;">
      <div style="display:inline-block;background:#e3f2fd;padding:14px 28px;border-radius:12px;margin-bottom:18px;">
        <span style="font-size:1.3rem;color:#1976d2;font-weight:800;letter-spacing:1px;">MANIFEST</span>
      </div>
      <h1 style="margin:0;color:#1a1a1a;font-size:3rem;font-weight:900;margin-bottom:22px;line-height:1.2;">
        <?= htmlspecialchars($manifest['manifest_no']) ?>
      </h1>
      <div style="display:flex;flex-direction:column;gap:14px;font-size:1.35rem;">
        <div style="display:flex;align-items:center;gap:12px;color:#555;">
          <i class="fa fa-building" style="color:#4caf50;font-size:1.6rem;"></i>
          <strong style="color:#222;font-size:1.35rem;"><?= htmlspecialchars($manifest['office_name']) ?></strong>
        </div>
        <div style="display:flex;align-items:center;gap:12px;color:#555;">
          <i class="fa fa-calendar" style="color:#ff9800;font-size:1.6rem;"></i>
          <span style="font-size:1.25rem;font-weight:600;"><?= date('d M Y, h:i A', strtotime($manifest['created_at'])) ?></span>
        </div>
        <?php if (!empty($manifest['car_number'])): ?>
        <div style="display:flex;align-items:center;gap:12px;color:#555;">
          <i class="fa fa-truck" style="color:#2196f3;font-size:1.6rem;"></i>
          <strong style="color:#222;font-size:1.25rem;">Car: <?= htmlspecialchars($manifest['car_number']) ?></strong>
        </div>
        <?php endif; ?>
        <?php if (!empty($manifest['driver_name'])): ?>
        <div style="display:flex;align-items:center;gap:12px;color:#555;">
          <i class="fa fa-user" style="color:#9c27b0;font-size:1.6rem;"></i>
          <strong style="color:#222;font-size:1.25rem;">Driver: <?= htmlspecialchars($manifest['driver_name']) ?></strong>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <div>
      <button type="button" class="btn btn-success" onclick="window.open('manifest_print.php?manifest_id=<?= $manifest_id ?>', '_blank')" 
              style="font-weight:900;padding:22px 50px;font-size:1.5rem;border-radius:14px;box-shadow:0 8px 25px rgba(76,175,80,0.5);text-transform:uppercase;">
        <i class="fa fa-print" style="margin-right:12px;font-size:1.6rem;"></i> PRINT
      </button>
    </div>
  </div>

  <!-- Financial Summary Cards - MUCH LARGER -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:28px;margin-bottom:50px;">
    <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:40px;border-radius:18px;color:white;box-shadow:0 10px 30px rgba(102,126,234,0.5);">
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:18px;">
        <i class="fa fa-calculator" style="font-size:2.8rem;opacity:0.95;"></i>
        <div style="font-size:1.4rem;opacity:0.95;font-weight:800;">Gross Total</div>
      </div>
      <div style="font-size:3.5rem;font-weight:900;letter-spacing:-2px;">₹<?= number_format($manifest['total_gross'], 2) ?></div>
    </div>
    
    <div style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);padding:40px;border-radius:18px;color:white;box-shadow:0 10px 30px rgba(245,87,108,0.5);">
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:18px;">
        <i class="fa fa-money" style="font-size:2.8rem;opacity:0.95;"></i>
        <div style="font-size:1.4rem;opacity:0.95;font-weight:800;">Total Pay To</div>
      </div>
      <div style="font-size:3.5rem;font-weight:900;letter-spacing:-2px;">₹<?= number_format($manifest['total_pay_to'], 2) ?></div>
    </div>
    
    <div style="background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%);padding:40px;border-radius:18px;color:white;box-shadow:0 10px 30px rgba(79,172,254,0.5);">
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:18px;">
        <i class="fa fa-check-circle" style="font-size:2.8rem;opacity:0.95;"></i>
        <div style="font-size:1.4rem;opacity:0.95;font-weight:800;">Net Total</div>
      </div>
      <div style="font-size:3.5rem;font-weight:900;letter-spacing:-2px;">₹<?= number_format($manifest['net_total'], 2) ?></div>
    </div>
  </div>

  <!-- Shipment Details Section -->
  <div>
    <div style="display:flex;align-items:center;gap:18px;margin-bottom:28px;padding:24px;background:#f5f7fa;border-radius:14px;">
      <i class="fa fa-list-alt" style="color:#1976d2;font-size:2.2rem;"></i>
      <h2 style="margin:0;font-weight:900;color:#1a1a1a;font-size:2rem;">Shipment Details</h2>
      <span style="margin-left:auto;background:#1976d2;color:white;padding:12px 24px;border-radius:30px;font-size:1.3rem;font-weight:800;">
        <?= mysqli_num_rows($details_result) ?> Items
      </span>
    </div>
    
    <div class="table-responsive" style="border-radius:18px;overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,0.12);">
      <table class="table table-hover" style="margin:0;background:white;font-size:1.2rem;">
        <thead>
          <tr style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;">
            <th style="padding:22px 18px;font-weight:900;font-size:1.3rem;white-space:nowrap;">SL No</th>
            <th style="padding:22px 18px;font-weight:900;font-size:1.3rem;white-space:nowrap;min-width:160px;">Docket No</th>
            <th style="padding:22px 18px;font-weight:900;font-size:1.3rem;min-width:200px;">Consignee</th>
            <th style="padding:22px 18px;font-weight:900;font-size:1.3rem;min-width:160px;">Item</th>
            <th style="padding:22px 18px;font-weight:900;font-size:1.3rem;min-width:240px;">Address</th>
            <th style="padding:22px 18px;font-weight:900;font-size:1.3rem;text-align:center;">Box</th>
            <th style="padding:22px 18px;font-weight:900;font-size:1.3rem;text-align:right;white-space:nowrap;">Weight (kg)</th>
            <th style="padding:22px 18px;font-weight:900;font-size:1.3rem;text-align:right;">Rate</th>
            <th style="padding:22px 18px;font-weight:900;font-size:1.3rem;text-align:right;">Amount</th>
            <th style="padding:22px 18px;font-weight:900;font-size:1.3rem;min-width:150px;">E-way Bill</th>
            <th style="padding:22px 18px;font-weight:900;font-size:1.3rem;text-align:right;">Pay To</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (mysqli_num_rows($details_result) > 0) {
            $sl = 1;
            while ($row = mysqli_fetch_assoc($details_result)): 
          ?>
            <tr style="border-bottom:2px solid #e0e0e0;">
              <td style="padding:20px 18px;font-weight:900;color:#666;font-size:1.2rem;"><?= $sl++ ?></td>
              <td style="padding:20px 18px;font-weight:900;color:#1976d2;font-size:1.25rem;white-space:nowrap;"><?= htmlspecialchars($row['doc_no']) ?></td>
              <td style="padding:20px 18px;color:#222;font-size:1.2rem;font-weight:800;"><?= htmlspecialchars($row['client_name']) ?></td>
              <td style="padding:20px 18px;color:#444;font-size:1.15rem;font-weight:700;"><?= htmlspecialchars($row['item']) ?></td>
              <td style="padding:20px 18px;color:#555;font-size:1.1rem;line-height:1.6;font-weight:600;"><?= htmlspecialchars($row['client_address']) ?></td>
              <td style="padding:20px 18px;text-align:center;font-weight:900;color:#222;font-size:1.25rem;"><?= htmlspecialchars($row['box']) ?></td>
              <td style="padding:20px 18px;text-align:right;color:#222;font-size:1.2rem;font-weight:700;"><?= number_format($row['weight'], 2) ?></td>
              <td style="padding:20px 18px;text-align:right;color:#222;font-size:1.2rem;font-weight:700;">₹<?= number_format($row['rate'], 2) ?></td>
              <td style="padding:20px 18px;text-align:right;font-weight:900;color:#4caf50;font-size:1.3rem;">₹<?= number_format($row['amount'], 2) ?></td>
              <td style="padding:20px 18px;color:#555;font-size:1.15rem;font-weight:700;"><?= htmlspecialchars($row['eway_bill'] ?: '-') ?></td>
              <td style="padding:20px 18px;text-align:right;font-weight:900;color:#f44336;font-size:1.3rem;">₹<?= number_format($row['pay_to'], 2) ?></td>
            </tr>
          <?php 
            endwhile; 
          } else {
            echo '<tr><td colspan="11" style="text-align:center;padding:70px;color:#999;">
                  <i class="fa fa-inbox" style="font-size:4.5rem;display:block;margin-bottom:25px;opacity:0.3;"></i>
                  <div style="font-size:1.6rem;font-weight:800;">No shipment details found</div>
                  </td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
/* Responsive Design - MUCH LARGER */
@media (max-width: 1200px) {
  h1 { font-size: 2.5rem !important; }
  .btn { padding: 18px 40px !important; font-size: 1.3rem !important; }
}

@media (max-width: 992px) {
  h1 { font-size: 2rem !important; }
  h2 { font-size: 1.6rem !important; }
  .btn { padding: 16px 35px !important; font-size: 1.2rem !important; }
  .table-responsive { font-size: 1.1rem !important; }
}

@media (max-width: 768px) {
  h1 { font-size: 1.8rem !important; }
  h2 { font-size: 1.4rem !important; }
  .table-responsive {
    font-size: 1rem !important;
  }
  .table-responsive th,
  .table-responsive td {
    padding: 14px 10px !important;
  }
  .btn {
    width: 100%;
    margin-top: 20px;
    padding: 18px !important;
    font-size: 1.3rem !important;
  }
}

@media (max-width: 576px) {
  h1 { font-size: 1.5rem !important; }
  h2 { font-size: 1.2rem !important; }
  .table-responsive {
    font-size: 0.95rem !important;
  }
  .table-responsive th,
  .table-responsive td {
    padding: 12px 8px !important;
  }
}

/* Table hover effect */
.table-hover tbody tr:hover {
  background-color: #f0f4f8 !important;
  cursor: pointer;
  transform: scale(1.01);
  transition: all 0.2s ease;
}

/* Smooth transitions */
.btn {
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(0,0,0,0.2) !important;
}

/* Print button pulse animation */
@keyframes pulse {
  0% { box-shadow: 0 8px 25px rgba(76,175,80,0.5); }
  50% { box-shadow: 0 12px 35px rgba(76,175,80,0.7); }
  100% { box-shadow: 0 8px 25px rgba(76,175,80,0.5); }
}

.btn-success {
  animation: pulse 2.5s infinite;
}

/* Zoom effect on financial cards */
@keyframes cardFloat {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-5px); }
}

div[style*="linear-gradient"] {
  animation: cardFloat 3s ease-in-out infinite;
}
</style>
