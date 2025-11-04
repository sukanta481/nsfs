<?php
require 'conn.php';
$office_id = intval($_GET['office_id'] ?? 0);

if (!$office_id) {
    echo "<div class='alert alert-danger'>Invalid office selection.</div>";
    exit;
}

// Get office name for title
$office = mysqli_fetch_assoc(mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id=$office_id"));

// Get cars for dropdown
$cars_query = "SELECT car_id, car_number FROM tbl_car WHERE active_status=1 ORDER BY car_number ASC";
$cars = mysqli_query($conn, $cars_query);
if (!$cars) {
    echo "<!-- Car Query Error: " . mysqli_error($conn) . " -->";
    $cars = mysqli_query($conn, "SELECT car_id, car_number FROM tbl_car ORDER BY car_number ASC"); // fallback without filter
}

// Get drivers for dropdown
$drivers_query = "SELECT driver_id, driver_name, driver_license FROM tbl_driver WHERE active_status=1 ORDER BY driver_name ASC";
$drivers = mysqli_query($conn, $drivers_query);
if (!$drivers) {
    echo "<!-- Driver Query Error: " . mysqli_error($conn) . " -->";
    $drivers = mysqli_query($conn, "SELECT driver_id, driver_name, driver_license FROM tbl_driver ORDER BY driver_name ASC"); // fallback without filter
}

// Debug info
$car_count = $cars ? mysqli_num_rows($cars) : 0;
$driver_count = $drivers ? mysqli_num_rows($drivers) : 0;
?>
<!-- DEBUG: Found <?= $car_count ?> cars and <?= $driver_count ?> drivers -->

<div class="x_panel" style="border-radius:20px;background:white;padding:35px;box-shadow:0 6px 25px rgba(0,0,0,0.1);">
  <div class="x_title" style="margin-bottom:25px;padding-bottom:20px;border-bottom:3px solid #e3f2fd;">
    <h2 style="font-size:2rem;font-weight:800;color:#1a1a1a;">
      <i class="fa fa-plus-circle" style="color:#4caf50;"></i> New Manifest Entry - <?= htmlspecialchars($office['office_name']) ?>
    </h2>
    <div class="clearfix"></div>
  </div>

  <!-- MESSAGE BOX AT THE TOP -->
  <div id="manifest_save_result" style="margin-bottom:20px;"></div>

  <form id="manifest_new_form" autocomplete="off">
    <input type="hidden" name="office_id" value="<?= $office_id ?>">
    
    <!-- Manual Input Checkbox FIRST, then Car, Driver, License -->
    <div style="background:#f5f7fa;padding:25px;border-radius:12px;margin-bottom:25px;border:2px solid #e0e0e0;">
      <div style="display:grid;grid-template-columns:auto 1fr 1fr 1fr;gap:20px;align-items:end;">
        
        <!-- Manual Input Checkbox - FIRST -->
        <div>
          <label style="display:block;font-weight:700;color:#333;margin-bottom:8px;font-size:1.05rem;">
            <i class="fa fa-edit" style="color:#9c27b0;"></i> Input Mode
          </label>
          <div style="background:white;padding:10px 15px;border-radius:8px;border:2px solid #e0e0e0;height:45px;display:flex;align-items:center;">
            <input type="checkbox" id="manual_input_checkbox" style="width:16px;height:16px;margin-right:8px;cursor:pointer;">
            <label for="manual_input_checkbox" style="margin:0;font-size:0.95rem;font-weight:700;color:#9c27b0;cursor:pointer;white-space:nowrap;">
              Manual
            </label>
          </div>
        </div>

        <!-- Car Number (Dropdown or Manual Input) -->
        <div>
          <label style="display:block;font-weight:700;color:#333;margin-bottom:8px;font-size:1.05rem;">
            <i class="fa fa-car" style="color:#2196f3;"></i> Car Number
          </label>
          <select name="car_id" id="car_select" class="form-control" style="height:45px;font-size:1.05rem;font-weight:600;">
            <option value="">-- Select Car --</option>
            <?php while($car = mysqli_fetch_assoc($cars)): ?>
              <option value="<?= $car['car_id'] ?>"><?= htmlspecialchars($car['car_number']) ?></option>
            <?php endwhile; ?>
          </select>
          <input type="text" name="car_number_manual" id="car_number_manual" class="form-control" style="height:45px;font-size:1.05rem;font-weight:600;display:none;text-transform:uppercase;" placeholder="Enter Car Number">
        </div>

        <!-- Driver Name (Dropdown or Manual Input) -->
        <div>
          <label style="display:block;font-weight:700;color:#333;margin-bottom:8px;font-size:1.05rem;">
            <i class="fa fa-user" style="color:#ff9800;"></i> Driver Name
          </label>
          <select name="driver_id" id="driver_select" class="form-control" style="height:45px;font-size:1.05rem;font-weight:600;">
            <option value="">-- Select Driver --</option>
            <?php while($driver = mysqli_fetch_assoc($drivers)): ?>
              <option value="<?= $driver['driver_id'] ?>" data-license="<?= htmlspecialchars($driver['driver_license'] ?? '') ?>"><?= htmlspecialchars($driver['driver_name']) ?></option>
            <?php endwhile; ?>
          </select>
          <input type="text" name="driver_name_manual" id="driver_name_manual" class="form-control" style="height:45px;font-size:1.05rem;font-weight:600;display:none;text-transform:uppercase;" placeholder="Enter Driver Name">
        </div>

        <!-- Driver License (Auto-filled or Manual Input) -->
        <div>
          <label style="display:block;font-weight:700;color:#333;margin-bottom:8px;font-size:1.05rem;">
            <i class="fa fa-id-card" style="color:#4caf50;"></i> Driving License
          </label>
          <input type="text" name="driver_license" id="driver_license" class="form-control" style="height:45px;font-size:1.05rem;font-weight:600;text-transform:uppercase;" placeholder="License Number" readonly>
        </div>
            </label>
          </div>
        </div>

      </div>
    </div>
    
    <!-- Data Entry Table -->
    <div style="overflow-x:auto;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08);">
      <table class="table table-bordered table-hover" id="manifest_entry_table" style="background:#fff;margin:0;font-size:1.05rem;">
        <thead>
          <tr style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;">
            <th style="padding:16px 12px;font-weight:800;font-size:1.15rem;">SL</th>
            <th style="padding:16px 12px;font-weight:800;font-size:1.15rem;min-width:140px;">Docket No</th>
            <th style="padding:16px 12px;font-weight:800;font-size:1.15rem;min-width:180px;">Consignee</th>
            <th style="padding:16px 12px;font-weight:800;font-size:1.15rem;min-width:140px;">Item</th>
            <th style="padding:16px 12px;font-weight:800;font-size:1.15rem;min-width:200px;">Address</th>
            <th style="padding:16px 12px;font-weight:800;font-size:1.15rem;">Box</th>
            <th style="padding:16px 12px;font-weight:800;font-size:1.15rem;">Weight</th>
            <th style="padding:16px 12px;font-weight:800;font-size:1.15rem;">Rate</th>
            <th style="padding:16px 12px;font-weight:800;font-size:1.15rem;">Amount</th>
            <th style="padding:16px 12px;font-weight:800;font-size:1.15rem;min-width:130px;">E-way Bill</th>
            <th style="padding:16px 12px;font-weight:800;font-size:1.15rem;">Pay To</th>
          </tr>
        </thead>
        <tbody>
          <?php for ($i=1; $i<=25; $i++): ?>
          <tr>
            <td style="padding:12px;font-weight:700;color:#666;text-align:center;font-size:1.1rem;"><?= $i ?></td>
            <td>
              <input type="text" name="doc_no[]" class="form-control docket-no" data-row="<?= $i ?>" autocomplete="off" style="font-size:1.05rem;font-weight:600;">
            </td>
            <td><input type="text" name="client_name[]" class="form-control client-field" readonly style="font-size:1.05rem;"></td>
            <td><input type="text" name="item[]" class="form-control client-field" readonly style="font-size:1.05rem;"></td>
            <td><input type="text" name="client_address[]" class="form-control client-field" readonly style="font-size:1.05rem;"></td>
            <td><input type="number" name="box[]" class="form-control client-field box-input" readonly style="font-size:1.05rem;font-weight:600;min-width:80px;text-align:center;"></td>
            <td><input type="number" name="weight[]" class="form-control client-field" readonly step="0.01" style="font-size:1.05rem;min-width:90px;text-align:center;"></td>
            <td>
              <input type="number" name="rate[]" class="form-control rate-input" min="0" step="0.01" style="font-size:1.05rem;font-weight:600;min-width:90px;text-align:center;">
            </td>
            <td>
              <input type="text" name="amount[]" class="form-control amount-field" readonly style="font-size:1.05rem;font-weight:700;color:#4caf50;min-width:100px;text-align:center;">
            </td>
            <td><input type="text" name="eway_bill[]" class="form-control" style="font-size:1.05rem;min-width:120px;"></td>
            <td><input type="number" name="pay_to[]" class="form-control pay-to-input" min="0" step="0.01" style="font-size:1.05rem;font-weight:600;color:#f44336;min-width:90px;text-align:center;"></td>
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
    
    <!-- Totals and Submit Button -->
    <div style="margin-top:25px;display:flex;gap:20px;flex-wrap:wrap;align-items:center;justify-content:space-between;background:#f5f7fa;padding:20px;border-radius:12px;">
      <div>
        <button type="submit" class="btn btn-success btn-lg" style="font-weight:800;font-size:1.3rem;padding:14px 45px;border-radius:10px;box-shadow:0 6px 20px rgba(76,175,80,0.4);">
          <i class="fa fa-save" style="margin-right:10px;"></i> SAVE MANIFEST
        </button>
      </div>
      <div style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
        <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:16px 22px;border-radius:10px;color:white;box-shadow:0 4px 15px rgba(102,126,234,0.4);">
          <div style="font-size:0.9rem;opacity:0.95;margin-bottom:4px;font-weight:600;">Gross Total</div>
          <div id="manifest_gross" style="font-weight:900;font-size:1.6rem;">0.00</div>
        </div>
        <div style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);padding:16px 22px;border-radius:10px;color:white;box-shadow:0 4px 15px rgba(245,87,108,0.4);">
          <div style="font-size:0.9rem;opacity:0.95;margin-bottom:4px;font-weight:600;">Total To Pay</div>
          <div id="manifest_pay_total" style="font-weight:900;font-size:1.6rem;">0.00</div>
        </div>
        <div style="background:linear-gradient(135deg,#4facfe 0%,#00f2fe 100%);padding:16px 22px;border-radius:10px;color:white;box-shadow:0 4px 15px rgba(79,172,254,0.4);">
          <div style="font-size:0.9rem;opacity:0.95;margin-bottom:4px;font-weight:600;">Net Total</div>
          <div id="manifest_net" style="font-weight:900;font-size:1.6rem;">0.00</div>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
// Use immediately-invoked function instead of $(function) since this is loaded via AJAX
(function() {
  var isManualMode = false;

  // Handle Enter key to act like Tab (form navigation)
  $(document).on('keydown', '#manifest_new_form input, #manifest_new_form select, #manifest_new_form textarea', function(e) {
    // Check if Enter key was pressed (keyCode 13)
    if (e.keyCode === 13 || e.which === 13) {
      // Prevent default form submission
      e.preventDefault();
      
      // Get all focusable elements in the form (visible and not readonly/disabled)
      var focusableElements = $('#manifest_new_form').find('input:visible:not([readonly]):not([disabled]), select:visible:not([disabled]), textarea:visible:not([readonly]):not([disabled])');
      
      // Find current element index
      var currentIndex = focusableElements.index(this);
      
      // Move to next element
      if (currentIndex > -1 && currentIndex < focusableElements.length - 1) {
        var nextElement = focusableElements.eq(currentIndex + 1);
        nextElement.focus();
        
        // Select text if it's an input field for easier editing
        if (nextElement.is('input[type="text"], input[type="number"]')) {
          nextElement.select();
        }
      }
      
      return false;
    }
  });
  
  // Allow Enter key to work normally ONLY on Save button
  $(document).on('keypress', 'button[type="submit"]', function(e) {
    if (e.keyCode === 13 || e.which === 13) {
      e.stopPropagation(); // Prevent the form handler from catching it
      $(this).click();
      return true;
    }
  });

  // Driver License Auto-Populate (use event delegation)
  $(document).on('change', '#driver_select', function() {
    var selectedOption = $(this).find('option:selected');
    var license = selectedOption.data('license') || '';
    $('#driver_license').val(license);
  });

  // Manual Input Checkbox Toggle (use event delegation)
  $(document).on('change', '#manual_input_checkbox', function() {
    console.log('Checkbox changed!', $(this).is(':checked'));
    isManualMode = $(this).is(':checked');
    
    if (isManualMode) {
      console.log('Enabling manual mode...');
      // Hide dropdowns, show manual text inputs
      $('#car_select').hide();
      $('#car_number_manual').show().focus();
      $('#driver_select').hide();
      $('#driver_name_manual').show();
      $('#driver_license').prop('readonly', false).css('background', '#fff');
      
      // Enable all docket fields for manual input
      $('.client-field, .box-input').prop('readonly', false).css('background', '#fff');
      $('.docket-no').attr('placeholder', 'Enter manually').css('background', '#fffacd');
      
      alert('✓ Manual Input Mode: You can now enter car, driver, license, and all docket details manually');
    } else {
      console.log('Disabling manual mode...');
      // Show dropdowns, hide manual text inputs
      $('#car_select').show();
      $('#car_number_manual').hide().val('');
      $('#driver_select').show();
      $('#driver_name_manual').hide().val('');
      $('#driver_license').prop('readonly', true).css('background', '#f5f5f5').val('');
      
      // Disable docket fields, back to auto-fetch mode
      $('.client-field, .box-input').prop('readonly', true).css('background', '#f5f5f5');
      $('.docket-no').attr('placeholder', 'Auto-fetch').css('background', '#fff');
      
      alert('✓ Auto-Fetch Mode: Select car/driver from dropdowns, docket details will be fetched automatically');
    }
  });
  
  // Test if checkbox exists on page load
  console.log('Manual checkbox found:', $('#manual_input_checkbox').length);

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

  // Docket No: on blur, fetch data for this row via AJAX (only in auto mode)
  $(document).on('blur', '.docket-no', function() {
    var $row = $(this).closest('tr');
    var docket_no = $(this).val().trim();
    
    if (!docket_no) {
      // clear this row
      $row.find('input').not('.docket-no').val('');
      recalcTotals();
      return;
    }

    // If manual mode, skip auto-fetch
    if (isManualMode) {
      return;
    }

    // Auto-fetch mode
    $.get('manifest_fetch_docket.php', { docket_no: docket_no }, function(res) {
      if (!res || res.status === 'not_found') {
        $row.find('input').not('.docket-no').val('');
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
      $row.find('.rate-input').val(res.rate);
      // Calculate amount
      var rate = parseFloat(res.rate) || 0;
      var box = parseFloat(res.box) || 1;
      $row.find('input[name="amount[]"]').val((rate * box).toFixed(2));
      recalcTotals();
    }, 'json');
  });

  // When rate or box changes, update amount and totals
  $(document).on('input', '.rate-input, .box-input', function() {
    var $row = $(this).closest('tr');
    var rate = parseFloat($row.find('.rate-input').val()) || 0;
    var box = parseFloat($row.find('.box-input').val()) || 1;
    $row.find('input[name="amount[]"]').val((rate * box).toFixed(2));
    recalcTotals();
  });

  // When pay_to or amount inputs change, recalc totals
  $(document).on('input', '.pay-to-input, .amount-field', function(){
    recalcTotals();
  });

  // Save manifest form (use event delegation)
  $(document).on('submit', '#manifest_new_form', function(e) {
    e.preventDefault();
    
    // Check if manual mode
    if (isManualMode) {
      // Validate manual inputs
      var carNumberManual = $('#car_number_manual').val().trim();
      var driverNameManual = $('#driver_name_manual').val().trim();
      var driverLicense = $('#driver_license').val().trim();
      
      if (!carNumberManual || !driverNameManual) {
        alert('⚠️ Please enter both Car Number and Driver Name!');
        return;
      }
      
      // Add manual mode flag and data to form
      var formData = $(this).serialize();
      formData += '&is_manual=1';
      formData += '&car_number_manual=' + encodeURIComponent(carNumberManual.toUpperCase());
      formData += '&driver_name_manual=' + encodeURIComponent(driverNameManual.toUpperCase());
      formData += '&driver_license=' + encodeURIComponent(driverLicense.toUpperCase());
      
      saveManifest(formData);
    } else {
      // Auto mode - validate dropdowns
      var carId = $('#car_select').val();
      var driverId = $('#driver_select').val();
      
      if (!carId || !driverId) {
        alert('⚠️ Please select both Car and Driver before saving!');
        return;
      }
      
      saveManifest($(this).serialize());
    }
  });
  
  function saveManifest(formData) {
    $('#manifest_save_result').html('<div class="alert alert-info" style="font-size:1.2rem;"><i class="fa fa-spinner fa-spin"></i> Saving manifest...</div>');
    
    $.post('manifest_save.php', formData, function(resp) {
      $('#manifest_save_result').html(resp);
      // Reset form if success
      if (resp.indexOf('success') !== -1 || resp.indexOf('Success') !== -1) {
        setTimeout(function() {
          $('#manifest_new_form')[0].reset();
          $('#manual_input_checkbox').prop('checked', false);
          isManualMode = false;
          
          // Reset to auto mode
          $('#car_select').show();
          $('#car_number_manual').hide();
          $('#driver_select').show();
          $('#driver_name_manual').hide();
          $('#driver_license').val('').prop('readonly', true).css('background', '#f5f5f5');
          
          $('.client-field, .box-input').prop('readonly', true).css('background', '#f5f5f5');
          $('.docket-no').css('background', '#fff');
          $('#manifest_gross').text('0.00');
          $('#manifest_pay_total').text('0.00');
          $('#manifest_net').text('0.00');
        }, 2000);
      }
      // Scroll to message
      $('html,body').animate({scrollTop: $('#manifest_save_result').offset().top-80}, 400);
    });
  }

  // Initial setup
  $('.client-field, .box-input').prop('readonly', true).css('background', '#f5f5f5');
  recalcTotals();
  
  // Test if checkbox exists on page load
  console.log('Manual checkbox found:', $('#manual_input_checkbox').length);
  console.log('isManualMode initialized:', isManualMode);
})(); // Close immediately-invoked function expression
</script>

<style>
/* Responsive Design for Manifest Entry */
@media (max-width: 1400px) {
  .x_panel { padding: 25px 18px; }
  table { font-size: 1rem !important; }
}

@media (max-width: 1200px) {
  .x_panel { padding: 20px 15px; }
  h2 { font-size: 1.6rem !important; }
  /* Car/Driver/Checkbox grid */
  div[style*="grid-template-columns"] {
    grid-template-columns: 1fr !important;
  }
}

@media (max-width: 992px) {
  table th, table td { padding: 10px 6px !important; font-size: 0.95rem !important; }
  input.form-control { font-size: 0.95rem !important; padding: 6px !important; }
  .btn-lg { font-size: 1.1rem !important; padding: 12px 30px !important; }
}

@media (max-width: 768px) {
  .x_panel { padding: 15px 10px; border-radius: 12px; }
  h2 { font-size: 1.4rem !important; }
  
  /* Make table horizontally scrollable */
  div[style*="overflow-x:auto"] {
    -webkit-overflow-scrolling: touch;
  }
  
  table { min-width: 1200px; font-size: 0.9rem !important; }
  table th, table td { padding: 8px 5px !important; font-size: 0.9rem !important; }
  input.form-control { font-size: 0.9rem !important; min-width: 70px !important; }
  
  /* Totals section */
  div[style*="display:flex"] > div:has(.btn-lg) {
    width: 100%;
    text-align: center;
  }
  .btn-lg { width: 100%; font-size: 1rem !important; padding: 10px 20px !important; }
  
  /* Car/Driver selection boxes */
  div[style*="background:#f5f7fa"] { padding: 15px !important; }
  label { font-size: 1rem !important; }
  select.form-control, input.form-control { height: 42px !important; font-size: 1rem !important; }
}

@media (max-width: 576px) {
  .x_panel { padding: 10px 8px; }
  h2 { font-size: 1.2rem !important; }
  table { font-size: 0.85rem !important; }
  table th, table td { padding: 6px 4px !important; font-size: 0.85rem !important; }
  input.form-control { font-size: 0.85rem !important; padding: 5px !important; min-width: 60px !important; }
  
  /* Gradient total cards */
  div[style*="linear-gradient"] {
    padding: 12px 15px !important;
    font-size: 1rem !important;
  }
  div[style*="linear-gradient"] > div[style*="font-size:1.6rem"] {
    font-size: 1.3rem !important;
  }
}
</style>
