<?php
require 'conn.php';
$office_id = intval($_GET['office_id'] ?? 0);

if (!$office_id) {
    echo "<div class='alert alert-danger'>Invalid office.</div>";
    exit;
}

$office = mysqli_fetch_assoc(mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id=$office_id"));

// Get latest manifest (change logic as per your grouping, eg. by date)
$res = mysqli_query($conn, "SELECT * FROM tbl_shipping_details WHERE branch_office=$office_id AND doc_type='DRS' ORDER BY shipping_details_id DESC LIMIT 25");


?>
<div class="x_panel" style="border-radius:16px;">
  <div class="x_title" style="margin-bottom:15px;">
    <h2>Manifest List - <?= htmlspecialchars($office['office_name']) ?></h2>
    <div class="clearfix"></div>
  </div>
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
<<<<<<< HEAD
          <th>Pay To</th>
=======
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
        </tr>
      </thead>
      <tbody>
        <?php $i=1; while($row = mysqli_fetch_assoc($res)): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($row['doc_no']) ?></td>
          <td><?= htmlspecialchars($row['client_name']) ?></td>
          <td><?= htmlspecialchars($row['item']) ?></td>
          <td><?= htmlspecialchars($row['client_address']) ?></td>
          <td><?= htmlspecialchars($row['box']) ?></td>
          <td><?= htmlspecialchars($row['weight']) ?></td>
          <td>
            <input type="number" value="<?= htmlspecialchars($row['rate']) ?>" data-doc="<?= $row['doc_no'] ?>" class="form-control manifest-rate-edit" style="max-width:70px;">
          </td>
          <td><?= number_format($row['rate'] * $row['box'], 2) ?></td>
<<<<<<< HEAD
          <td><?= htmlspecialchars($row['eway_bill'] ?? '') ?></td>
          <td>
            <input type="number" value="<?= htmlspecialchars($row['pay_to'] ?? '') ?>" data-doc="<?= $row['doc_no'] ?>" class="form-control manifest-pay-edit" style="max-width:70px;">
          </td>
=======
          <td><?= htmlspecialchars($row['eway_bill']) ?></td>
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <div style="margin-top:20px;">
    <button class="btn btn-primary" ><a href="manifest_print.php?office_id=<?= $office_id ?>" target="_blank" class="btn btn-primary"><i class="fa fa-print"></i> Print</a></button>
  </div>
</div>

<script>
$('.manifest-rate-edit').on('change', function() {
  var rate = $(this).val();
  var doc_no = $(this).data('doc');
  $.post('manifest_rate_update.php', {doc_no: doc_no, rate: rate}, function(resp) {
    // Optionally show a notification or reload search
  });
});
</script>
