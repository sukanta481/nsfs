<?php
require 'conn.php';
$manifest_id = intval($_GET['manifest_id'] ?? 0);

if (!$manifest_id) {
    echo "<div class='alert alert-danger'>Invalid manifest ID.</div>";
    exit;
}

// Get manifest header
$manifest_query = "SELECT m.manifest_id, m.manifest_no, m.created_at, m.total_gross, m.total_pay_to, m.net_total, o.office_name
                   FROM tbl_manifest m
                   LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                   WHERE m.manifest_id = $manifest_id";
$manifest_result = mysqli_query($conn, $manifest_query);

if (!$manifest_result || mysqli_num_rows($manifest_result) == 0) {
    echo "<div class='alert alert-danger'>Manifest not found.</div>";
    exit;
}

$manifest = mysqli_fetch_assoc($manifest_result);

// Get manifest details
$details_query = "SELECT detail_id, doc_no, client_name, item, client_address, box, weight, rate, amount, eway_bill, pay_to
                  FROM tbl_manifest_details 
                  WHERE manifest_id = $manifest_id 
                  ORDER BY detail_id ASC";
$details_result = mysqli_query($conn, $details_query);
?>

<div class="x_panel" style="border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.08);border:none;">
  <div class="x_title" style="margin-bottom:20px;padding-bottom:15px;border-bottom:3px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;">
    <div>
      <h2 style="margin:0;color:#222;font-size:1.5rem;font-weight:700;">
        <i class="fa fa-file-text-o" style="color:#007bff;"></i> 
        <?= htmlspecialchars($manifest['manifest_no']) ?>
      </h2>
      <div style="color:#666;font-size:0.9rem;margin-top:8px;display:flex;gap:20px;flex-wrap:wrap;">
        <span><i class="fa fa-building" style="color:#28a745;"></i> <strong><?= htmlspecialchars($manifest['office_name']) ?></strong></span>
        <span><i class="fa fa-calendar"></i> <?= date('d M Y, h:i A', strtotime($manifest['created_at'])) ?></span>
      </div>
    </div>
    <div style="display:flex;gap:10px;">
      <button type="button" class="btn btn-success" onclick="window.open('manifest_print.php?manifest_id=<?= $manifest_id ?>', '_blank')" style="font-weight:600;padding:10px 24px;">
        <i class="fa fa-print"></i> Print
      </button>
    </div>
  </div>

  <!-- Summary -->
  <div style="margin-bottom:25px;padding:20px;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);border-radius:10px;color:white;">
    <h4 style="margin:0 0 15px 0;font-weight:700;font-size:1.1rem;opacity:0.9;">💰 Financial Summary</h4>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;">
      <div style="background:rgba(255,255,255,0.15);padding:15px;border-radius:8px;backdrop-filter:blur(10px);">
        <div style="font-size:0.85rem;opacity:0.9;margin-bottom:5px;">Gross Total</div>
        <div style="font-size:1.8rem;font-weight:700;">₹<?= number_format($manifest['total_gross'], 2) ?></div>
      </div>
      <div style="background:rgba(255,255,255,0.15);padding:15px;border-radius:8px;backdrop-filter:blur(10px);">
        <div style="font-size:0.85rem;opacity:0.9;margin-bottom:5px;">Total Pay To</div>
        <div style="font-size:1.8rem;font-weight:700;">₹<?= number_format($manifest['total_pay_to'], 2) ?></div>
      </div>
      <div style="background:rgba(255,255,255,0.15);padding:15px;border-radius:8px;backdrop-filter:blur(10px);">
        <div style="font-size:0.85rem;opacity:0.9;margin-bottom:5px;">Net Total</div>
        <div style="font-size:1.8rem;font-weight:700;">₹<?= number_format($manifest['net_total'], 2) ?></div>
      </div>
    </div>
  </div>

  <!-- Details Table -->
  <div>
    <h4 style="margin:0 0 15px 0;font-weight:700;color:#222;font-size:1.1rem;">
      <i class="fa fa-list-alt" style="color:#007bff;"></i> Shipment Details
    </h4>
    <div style="overflow-x:auto;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
      <table class="table table-bordered table-hover" style="background:#fff;margin:0;">
        <thead>
          <tr style="background:linear-gradient(to right, #4facfe 0%, #00f2fe 100%);color:white;">
            <th style="padding:12px 10px;font-weight:600;">SL</th>
            <th style="padding:12px 10px;font-weight:600;">Docket No</th>
            <th style="padding:12px 10px;font-weight:600;">Consignee</th>
            <th style="padding:12px 10px;font-weight:600;">Item</th>
            <th style="padding:12px 10px;font-weight:600;">Address</th>
            <th style="padding:12px 10px;font-weight:600;text-align:center;">Box</th>
            <th style="padding:12px 10px;font-weight:600;text-align:right;">Weight</th>
            <th style="padding:12px 10px;font-weight:600;text-align:right;">Rate</th>
            <th style="padding:12px 10px;font-weight:600;text-align:right;">Amount</th>
            <th style="padding:12px 10px;font-weight:600;">E-way Bill</th>
            <th style="padding:12px 10px;font-weight:600;text-align:right;">Pay To</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (mysqli_num_rows($details_result) > 0) {
            $sl = 1;
            while ($row = mysqli_fetch_assoc($details_result)): 
          ?>
            <tr style="transition:background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
              <td style="padding:10px;font-weight:600;color:#666;"><?= $sl++ ?></td>
              <td style="padding:10px;font-weight:600;color:#007bff;"><?= htmlspecialchars($row['doc_no']) ?></td>
              <td style="padding:10px;"><?= htmlspecialchars($row['client_name']) ?></td>
              <td style="padding:10px;font-size:0.9rem;"><?= htmlspecialchars($row['item']) ?></td>
              <td style="padding:10px;font-size:0.9rem;max-width:200px;"><?= htmlspecialchars($row['client_address']) ?></td>
              <td style="padding:10px;text-align:center;font-weight:600;"><?= htmlspecialchars($row['box']) ?></td>
              <td style="padding:10px;text-align:right;"><?= number_format($row['weight'], 2) ?> kg</td>
              <td style="padding:10px;text-align:right;">₹<?= number_format($row['rate'], 2) ?></td>
              <td style="padding:10px;text-align:right;font-weight:600;color:#28a745;">₹<?= number_format($row['amount'], 2) ?></td>
              <td style="padding:10px;font-size:0.9rem;"><?= htmlspecialchars($row['eway_bill'] ?: '-') ?></td>
              <td style="padding:10px;text-align:right;font-weight:600;color:#dc3545;">₹<?= number_format($row['pay_to'], 2) ?></td>
            </tr>
          <?php 
            endwhile; 
          } else {
            echo '<tr><td colspan="11" style="text-align:center;padding:30px;color:#999;"><i class="fa fa-inbox fa-2x" style="display:block;margin-bottom:10px;opacity:0.3;"></i>No details found.</td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
@media (max-width: 768px) {
  .x_title > div {
    margin-bottom: 10px;
  }
  .btn {
    width: 100%;
  }
}
</style>
