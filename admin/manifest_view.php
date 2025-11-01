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

<div class="x_panel" style="border-radius:16px;">
  <div class="x_title" style="margin-bottom:15px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;">
    <div>
      <h2>Manifest Details - <?= htmlspecialchars($manifest['manifest_no']) ?></h2>
      <div style="color:#666;font-size:0.95rem;margin-top:5px;">
        Office: <strong><?= htmlspecialchars($manifest['office_name']) ?></strong> | 
        Date: <strong><?= date('d M Y', strtotime($manifest['created_at'])) ?></strong>
      </div>
    </div>
    <button type="button" class="btn btn-success" onclick="window.open('manifest_print.php?manifest_id=<?= $manifest_id ?>', '_blank')" style="font-weight:600;padding:8px 22px;">
      <i class="fa fa-print"></i> Print
    </button>
  </div>

  <!-- Summary -->
  <div style="margin-bottom:20px;padding:15px;background:#f8fafc;border-radius:8px;border:1px solid #e1e8ed;">
    <h4 style="margin-top:0;margin-bottom:12px;font-weight:700;color:#222;">Summary</h4>
    <div style="display:flex;gap:25px;flex-wrap:wrap;">
      <div>
        <span style="font-weight:600;color:#666;">Gross Total:</span>
        <span style="font-weight:700;color:#28a745;margin-left:8px;">₹<?= number_format($manifest['total_gross'], 2) ?></span>
      </div>
      <div>
        <span style="font-weight:600;color:#666;">Total Pay To:</span>
        <span style="font-weight:700;color:#ffc107;margin-left:8px;">₹<?= number_format($manifest['total_pay_to'], 2) ?></span>
      </div>
      <div>
        <span style="font-weight:600;color:#666;">Net Total:</span>
        <span style="font-weight:700;color:#007bff;margin-left:8px;">₹<?= number_format($manifest['net_total'], 2) ?></span>
      </div>
    </div>
  </div>

  <!-- Details Table -->
  <div style="overflow-x:auto;">
    <table class="table table-bordered table-striped" style="background:#fff;">
      <thead>
        <tr style="background:#f6faff;">
          <th>SL. No</th>
          <th>Docket No</th>
          <th>Consignee</th>
          <th>Item</th>
          <th>Address</th>
          <th>Box</th>
          <th>Weight</th>
          <th>Rate</th>
          <th>Amount</th>
          <th>E-way Bill</th>
          <th>Pay To</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        if (mysqli_num_rows($details_result) > 0) {
          $sl = 1;
          while ($row = mysqli_fetch_assoc($details_result)): 
        ?>
          <tr>
            <td><?= $sl++ ?></td>
            <td><?= htmlspecialchars($row['doc_no']) ?></td>
            <td><?= htmlspecialchars($row['client_name']) ?></td>
            <td><?= htmlspecialchars($row['item']) ?></td>
            <td><?= htmlspecialchars($row['client_address']) ?></td>
            <td style="text-align:right"><?= htmlspecialchars($row['box']) ?></td>
            <td style="text-align:right"><?= number_format($row['weight'], 2) ?></td>
            <td style="text-align:right">₹<?= number_format($row['rate'], 2) ?></td>
            <td style="text-align:right">₹<?= number_format($row['amount'], 2) ?></td>
            <td><?= htmlspecialchars($row['eway_bill'] ?: '-') ?></td>
            <td style="text-align:right">₹<?= number_format($row['pay_to'], 2) ?></td>
          </tr>
        <?php 
          endwhile; 
        } else {
          echo '<tr><td colspan="11" style="text-align:center;padding:20px;color:#999;">No details found.</td></tr>';
        }
        ?>
      </tbody>
    </table>
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
