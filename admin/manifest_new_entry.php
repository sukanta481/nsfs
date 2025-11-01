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
<<<<<<< HEAD
            <th>Pay To</th>
=======
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
          </tr>
        </thead>
        <tbody>
          <?php for ($i=1; $i<=25; $i++): ?>
          <tr>
            <td><?= $i ?></td>
            <td>
<<<<<<< HEAD
              <input type="text" name="doc_no[]" class="form-control docket-no" data-row="<?= $i ?>" autocomplete="off">
            </td>
            <td><input type="text" name="client_name[]" class="form-control" readonly></td>
            <td><input type="text" name="item[]" class="form-control" readonly></td>
            <td><input type="text" name="client_address[]" class="form-control" readonly></td>
=======
              <input type="text" name="docket_no[]" class="form-control docket-no" data-row="<?= $i ?>" autocomplete="off">
            </td>
            <td><input type="text" name="consignee[]" class="form-control" readonly></td>
            <td><input type="text" name="item[]" class="form-control" readonly></td>
            <td><input type="text" name="address[]" class="form-control" readonly></td>
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
            <td><input type="text" name="box[]" class="form-control" readonly></td>
            <td><input type="text" name="weight[]" class="form-control" readonly></td>
            <td>
              <input type="number" name="rate[]" class="form-control rate-input" min="0" step="0.01">
            </td>
            <td>
              <input type="text" name="amount[]" class="form-control" readonly>
            </td>
<<<<<<< HEAD
            <td><input type="text" name="eway_bill[]" class="form-control"></td>
            <td><input type="number" name="pay_to[]" class="form-control" min="0"></td>
=======
            <td><input type="text" name="eway_bill[]" class="form-control" readonly></td>
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
<<<<<<< HEAD
    <div style="margin-top:12px;display:flex;gap:18px;flex-wrap:wrap;align-items:center;justify-content:space-between;">
      <div>
        <button type="submit" class="btn btn-success" style="font-weight:600;font-size:1.06rem;padding:8px 35px;">Save</button>
      </div>
      <div style="display:flex;gap:12px;align-items:center;">
        <div style="background:#fff;padding:10px 14px;border-radius:8px;border:1px solid #eee;">
          <div style="font-size:12px;color:#666;">Gross Total</div>
          <div id="manifest_gross" style="font-weight:700;font-size:1.05rem;">0.00</div>
        </div>
        <div style="background:#fff;padding:10px 14px;border-radius:8px;border:1px solid #eee;">
          <div style="font-size:12px;color:#666;">Total To Pay</div>
          <div id="manifest_pay_total" style="font-weight:700;font-size:1.05rem;">0.00</div>
        </div>
        <div style="background:#fff;padding:10px 14px;border-radius:8px;border:1px solid #eee;">
          <div style="font-size:12px;color:#666;">Net Total</div>
          <div id="manifest_net" style="font-weight:700;font-size:1.05rem;color:#d9534f;">0.00</div>
        </div>
      </div>
=======
    <div style="margin-top:20px;">
      <button type="submit" class="btn btn-success" style="font-weight:600;font-size:1.06rem;padding:8px 35px;">Save</button>
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
    </div>
  </form>
</div>

<script>
$(function() {
<<<<<<< HEAD
  // Helper: recalc totals
  function recalcTotals() {
    var gross = 0.00;
    var payTotal = 0.00;
    $('input[name="amount[]"]').each(function(){
      var v = parseFloat($(this).val()) || 0;
      gross += v;
    });
    $('input[name="pay_to[]"]').each(function(){
      var v = parseFloat($(this).val()) || 0;
      payTotal += v;
    });
    var net = gross - payTotal;
    $('#manifest_gross').text(gross.toFixed(2));
    $('#manifest_pay_total').text(payTotal.toFixed(2));
    $('#manifest_net').text(net.toFixed(2));
  }

=======
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
  // Docket No: on blur, fetch data for this row via AJAX
  $(document).on('blur', '.docket-no', function() {
    var $row = $(this).closest('tr');
    var docket_no = $(this).val().trim();
    if (!docket_no) {
      // clear this row
      $row.find('input').not('.docket-no,.rate-input').val('');
      $row.find('.rate-input').val('');
      $row.find('input[name="amount[]"]').val('');
<<<<<<< HEAD
      recalcTotals();
=======
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
      return;
    }
    $.get('manifest_fetch_docket.php', { docket_no: docket_no }, function(res) {
      if (!res || res.status === 'not_found') {
        $row.find('input').not('.docket-no,.rate-input').val('');
        $row.find('.rate-input').val('');
        $row.find('input[name="amount[]"]').val('');
<<<<<<< HEAD
        recalcTotals();
        return;
      }
      $row.find('input[name="client_name[]"]').val(res.client_name);
      $row.find('input[name="item[]"]').val(res.item);
      $row.find('input[name="client_address[]"]').val(res.client_address);
      $row.find('input[name="box[]"]').val(res.box);
      $row.find('input[name="weight[]"]').val(res.weight);
      $row.find('input[name="eway_bill[]"]').val(res.eway_bill || '');
      $row.find('input[name="pay_to[]"]').val(res.pay_to || '');
=======
        return;
      }
      $row.find('input[name="consignee[]"]').val(res.consignee);
      $row.find('input[name="item[]"]').val(res.item);
      $row.find('input[name="address[]"]').val(res.address);
      $row.find('input[name="box[]"]').val(res.box);
      $row.find('input[name="weight[]"]').val(res.weight);
      $row.find('input[name="eway_bill[]"]').val(res.eway_bill);
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
      $row.find('.rate-input').val(res.rate);
      // Calculate amount
      var rate = parseFloat(res.rate) || 0;
      var box = parseFloat(res.box) || 1;
      $row.find('input[name="amount[]"]').val((rate * box).toFixed(2));
<<<<<<< HEAD
      recalcTotals();
    }, 'json');
  });

  // When rate changes, update amount and totals
=======
    }, 'json');
  });

  // When rate changes, update amount
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
  $(document).on('input', '.rate-input', function() {
    var $row = $(this).closest('tr');
    var rate = parseFloat($(this).val()) || 0;
    var box = parseFloat($row.find('input[name="box[]"]').val()) || 1;
    $row.find('input[name="amount[]"]').val((rate * box).toFixed(2));
<<<<<<< HEAD
    recalcTotals();
  });

  // When pay_to or amount inputs change, recalc totals
  $(document).on('input', 'input[name="pay_to[]"], input[name="amount[]"]', function(){
    recalcTotals();
=======
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
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
<<<<<<< HEAD
        $('#manifest_gross').text('0.00');
        $('#manifest_pay_total').text('0.00');
        $('#manifest_net').text('0.00');
=======
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
      }
      // Scroll to message
      $('html,body').animate({scrollTop: $('#manifest_save_result').offset().top-80}, 400);
    });
  });
<<<<<<< HEAD

  // initial totals
  recalcTotals();
=======
>>>>>>> 48325984ca2d6349a2fc0072c845c0d9d4a4c417
});
</script>
