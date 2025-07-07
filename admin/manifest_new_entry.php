<?php
require 'conn.php';
$office_id = intval($_GET['office_id'] ?? 0);

if (!$office_id) {
    echo "<div class='alert alert-danger'>Invalid office selection.</div>";
    exit;
}

// Get office name for title
$office = mysqli_fetch_assoc(mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id=$office_id"));
?>

<div class="x_panel" style="border-radius:16px;">
  <div class="x_title" style="margin-bottom:15px;">
    <h2>New Manifest Entry - <?= htmlspecialchars($office['office_name']) ?></h2>
    <div class="clearfix"></div>
  </div>

  <!-- MESSAGE BOX AT THE TOP -->
  <div id="manifest_save_result" style="margin-bottom:18px;"></div>

  <form id="manifest_new_form" autocomplete="off">
    <input type="hidden" name="office_id" value="<?= $office_id ?>">
    <div style="overflow-x:auto;">
      <table class="table table-bordered table-striped" id="manifest_entry_table" style="background:#fff;">
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
          </tr>
        </thead>
        <tbody>
          <?php for ($i=1; $i<=25; $i++): ?>
          <tr>
            <td><?= $i ?></td>
            <td>
              <input type="text" name="docket_no[]" class="form-control docket-no" data-row="<?= $i ?>" autocomplete="off">
            </td>
            <td><input type="text" name="consignee[]" class="form-control" readonly></td>
            <td><input type="text" name="item[]" class="form-control" readonly></td>
            <td><input type="text" name="address[]" class="form-control" readonly></td>
            <td><input type="text" name="box[]" class="form-control" readonly></td>
            <td><input type="text" name="weight[]" class="form-control" readonly></td>
            <td>
              <input type="number" name="rate[]" class="form-control rate-input" min="0" step="0.01">
            </td>
            <td>
              <input type="text" name="amount[]" class="form-control" readonly>
            </td>
            <td><input type="text" name="eway_bill[]" class="form-control" readonly></td>
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
    <div style="margin-top:20px;">
      <button type="submit" class="btn btn-success" style="font-weight:600;font-size:1.06rem;padding:8px 35px;">Save</button>
    </div>
  </form>
</div>

<script>
$(function() {
  // Docket No: on blur, fetch data for this row via AJAX
  $(document).on('blur', '.docket-no', function() {
    var $row = $(this).closest('tr');
    var docket_no = $(this).val().trim();
    if (!docket_no) {
      // clear this row
      $row.find('input').not('.docket-no,.rate-input').val('');
      $row.find('.rate-input').val('');
      $row.find('input[name="amount[]"]').val('');
      return;
    }
    $.get('manifest_fetch_docket.php', { docket_no: docket_no }, function(res) {
      if (!res || res.status === 'not_found') {
        $row.find('input').not('.docket-no,.rate-input').val('');
        $row.find('.rate-input').val('');
        $row.find('input[name="amount[]"]').val('');
        return;
      }
      $row.find('input[name="consignee[]"]').val(res.consignee);
      $row.find('input[name="item[]"]').val(res.item);
      $row.find('input[name="address[]"]').val(res.address);
      $row.find('input[name="box[]"]').val(res.box);
      $row.find('input[name="weight[]"]').val(res.weight);
      $row.find('input[name="eway_bill[]"]').val(res.eway_bill);
      $row.find('.rate-input').val(res.rate);
      // Calculate amount
      var rate = parseFloat(res.rate) || 0;
      var box = parseFloat(res.box) || 1;
      $row.find('input[name="amount[]"]').val((rate * box).toFixed(2));
    }, 'json');
  });

  // When rate changes, update amount
  $(document).on('input', '.rate-input', function() {
    var $row = $(this).closest('tr');
    var rate = parseFloat($(this).val()) || 0;
    var box = parseFloat($row.find('input[name="box[]"]').val()) || 1;
    $row.find('input[name="amount[]"]').val((rate * box).toFixed(2));
  });

  // Save manifest form
  $('#manifest_new_form').on('submit', function(e) {
    e.preventDefault();
    $('#manifest_save_result').html('<span style="color:#888;">Saving…</span>');
    $.post('manifest_save.php', $(this).serialize(), function(resp) {
      $('#manifest_save_result').html(resp);
      // Optionally reset form if needed
      if (resp.indexOf('success') !== -1) {
        $('#manifest_new_form')[0].reset();
      }
      // Scroll to message
      $('html,body').animate({scrollTop: $('#manifest_save_result').offset().top-80}, 400);
    });
  });
});
</script>
