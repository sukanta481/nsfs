<?php
require 'conn.php';
$office_id = intval($_GET['office_id'] ?? 0);

if (!$office_id) {
    echo "<div class='alert alert-danger'>Invalid office.</div>";
    exit;
}

$office = mysqli_fetch_assoc(mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id=$office_id"));

// Get all manifests for this office
$manifest_query = "SELECT manifest_id, manifest_no, created_at, total_gross, total_pay_to, net_total 
                   FROM tbl_manifest 
                   WHERE office_id = $office_id 
                   ORDER BY manifest_id DESC";
$manifests = mysqli_query($conn, $manifest_query);
?>

<div class="x_panel" style="border-radius:16px;">
  <div class="x_title" style="margin-bottom:15px;">
    <h2>Manifest List - <?= htmlspecialchars($office['office_name']) ?></h2>
    <div class="clearfix"></div>
  </div>

  <!-- Manifest Selection -->
  <div style="margin-bottom:20px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
    <div style="flex:1;min-width:250px;">
      <label for="manifest_select" style="font-weight:600;color:#222;margin-right:8px;">Select Manifest:</label>
      <select id="manifest_select" class="form-control" style="display:inline-block;width:auto;min-width:300px;">
        <option value="">-- Select Manifest --</option>
        <?php 
        if (mysqli_num_rows($manifests) > 0) {
          while($m = mysqli_fetch_assoc($manifests)): 
        ?>
          <option value="<?= $m['manifest_id'] ?>" 
                  data-manifest-no="<?= htmlspecialchars($m['manifest_no']) ?>"
                  data-gross="<?= $m['total_gross'] ?>"
                  data-pay="<?= $m['total_pay_to'] ?>"
                  data-net="<?= $m['net_total'] ?>">
            <?= htmlspecialchars($m['manifest_no']) ?> - <?= date('d M Y', strtotime($m['created_at'])) ?> (Net: ₹<?= number_format($m['net_total'], 2) ?>)
          </option>
        <?php 
          endwhile; 
        } else {
          echo '<option value="">No manifests found for this office</option>';
        }
        ?>
      </select>
    </div>
    <button type="button" id="btn_view_manifest" class="btn btn-primary" style="font-weight:600;padding:8px 22px;">
      <i class="fa fa-search"></i> View Details
    </button>
    <button type="button" id="btn_print_manifest" class="btn btn-success" style="font-weight:600;padding:8px 22px;" disabled>
      <i class="fa fa-print"></i> Print
    </button>
  </div>

  <!-- Manifest Summary -->
  <div id="manifest_summary" style="display:none;margin-bottom:20px;padding:15px;background:#f8fafc;border-radius:8px;border:1px solid #e1e8ed;">
    <h4 style="margin-top:0;margin-bottom:12px;font-weight:700;color:#222;">Manifest Summary</h4>
    <div style="display:flex;gap:20px;flex-wrap:wrap;">
      <div>
        <span style="font-weight:600;color:#666;">Manifest No:</span>
        <span id="summary_manifest_no" style="font-weight:700;color:#222;margin-left:8px;">-</span>
      </div>
      <div>
        <span style="font-weight:600;color:#666;">Gross Total:</span>
        <span id="summary_gross" style="font-weight:700;color:#28a745;margin-left:8px;">₹0.00</span>
      </div>
      <div>
        <span style="font-weight:600;color:#666;">Total Pay To:</span>
        <span id="summary_pay" style="font-weight:700;color:#ffc107;margin-left:8px;">₹0.00</span>
      </div>
      <div>
        <span style="font-weight:600;color:#666;">Net Total:</span>
        <span id="summary_net" style="font-weight:700;color:#007bff;margin-left:8px;">₹0.00</span>
      </div>
    </div>
  </div>

  <!-- Manifest Details Table -->
  <div id="manifest_details_container" style="display:none;">
    <div style="overflow-x:auto;">
      <table class="table table-bordered table-striped" id="manifest_details_table" style="background:#fff;">
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
        <tbody id="manifest_details_body">
          <!-- Details will be loaded here via AJAX -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- No Selection Message -->
  <div id="no_selection_msg" style="padding:40px;text-align:center;color:#666;font-size:1.1rem;">
    <i class="fa fa-info-circle" style="font-size:3rem;color:#ccc;margin-bottom:12px;"></i>
    <p>Please select a manifest from the dropdown above to view details.</p>
  </div>
</div>

<script>
$(function() {
  var currentManifestId = null;

  // Enable/disable print button based on selection
  $('#manifest_select').on('change', function() {
    var manifestId = $(this).val();
    if (manifestId) {
      $('#btn_view_manifest').prop('disabled', false);
      $('#btn_print_manifest').prop('disabled', false);
      currentManifestId = manifestId;
    } else {
      $('#btn_view_manifest').prop('disabled', true);
      $('#btn_print_manifest').prop('disabled', true);
      currentManifestId = null;
      $('#manifest_details_container').hide();
      $('#manifest_summary').hide();
      $('#no_selection_msg').show();
    }
  });

  // View manifest details
  $('#btn_view_manifest').on('click', function() {
    var manifestId = $('#manifest_select').val();
    if (!manifestId) {
      alert('Please select a manifest first.');
      return;
    }

    // Update summary
    var $selected = $('#manifest_select option:selected');
    $('#summary_manifest_no').text($selected.data('manifest-no'));
    $('#summary_gross').text('₹' + parseFloat($selected.data('gross')).toFixed(2));
    $('#summary_pay').text('₹' + parseFloat($selected.data('pay')).toFixed(2));
    $('#summary_net').text('₹' + parseFloat($selected.data('net')).toFixed(2));
    $('#manifest_summary').show();

    // Load details via AJAX
    $('#manifest_details_body').html('<tr><td colspan="11" style="text-align:center;padding:20px;">Loading...</td></tr>');
    $('#manifest_details_container').show();
    $('#no_selection_msg').hide();

    $.get('manifest_get_details.php', { manifest_id: manifestId }, function(data) {
      if (data.success && data.details.length > 0) {
        var html = '';
        $.each(data.details, function(i, row) {
          html += '<tr>';
          html += '<td>' + (i + 1) + '</td>';
          html += '<td>' + escapeHtml(row.doc_no) + '</td>';
          html += '<td>' + escapeHtml(row.client_name) + '</td>';
          html += '<td>' + escapeHtml(row.item) + '</td>';
          html += '<td>' + escapeHtml(row.client_address) + '</td>';
          html += '<td style="text-align:right">' + escapeHtml(row.box) + '</td>';
          html += '<td style="text-align:right">' + parseFloat(row.weight).toFixed(2) + '</td>';
          html += '<td style="text-align:right">₹' + parseFloat(row.rate).toFixed(2) + '</td>';
          html += '<td style="text-align:right">₹' + parseFloat(row.amount).toFixed(2) + '</td>';
          html += '<td>' + escapeHtml(row.eway_bill || '') + '</td>';
          html += '<td style="text-align:right">₹' + parseFloat(row.pay_to).toFixed(2) + '</td>';
          html += '</tr>';
        });
        $('#manifest_details_body').html(html);
      } else {
        $('#manifest_details_body').html('<tr><td colspan="11" style="text-align:center;padding:20px;color:#999;">No details found for this manifest.</td></tr>');
      }
    }, 'json').fail(function() {
      $('#manifest_details_body').html('<tr><td colspan="11" style="text-align:center;padding:20px;color:#dc3545;">Error loading manifest details.</td></tr>');
    });
  });

  // Print manifest
  $('#btn_print_manifest').on('click', function() {
    var manifestId = $('#manifest_select').val();
    if (manifestId) {
      window.open('manifest_print.php?manifest_id=' + manifestId, '_blank');
    } else {
      alert('Please select a manifest first.');
    }
  });

  // Helper function to escape HTML
  function escapeHtml(text) {
    if (!text) return '';
    var map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
  }
});
</script>

<style>
@media (max-width: 768px) {
  #manifest_select {
    width: 100% !important;
    min-width: auto !important;
  }
  .btn {
    width: 100%;
    margin-top: 8px;
  }
}
</style>
