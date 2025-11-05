<?php
/**
 * Manifest New Entry Form
 * Professional rewrite with clean code structure
 * Handles both auto-fetch and manual input modes
 */

require 'conn.php';

// Validate office selection
$office_id = intval($_GET['office_id'] ?? 0);
if (!$office_id) {
    echo "<div class='alert alert-danger'>Invalid office selection.</div>";
    exit;
}

// Fetch office details
$office_result = mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id = $office_id");
$office = mysqli_fetch_assoc($office_result);

// Fetch active cars
$cars_result = mysqli_query($conn, "SELECT car_id, car_number FROM tbl_car WHERE active_status = 1 ORDER BY car_number ASC");

// Fetch active drivers from tbl_staff
$drivers_result = mysqli_query($conn, "SELECT staff_id, staff_name, driving_license FROM tbl_staff WHERE staff_role = 'Driver' AND active_status = 1 ORDER BY staff_name ASC");
?>

<div class="x_panel" style="border-radius: 20px; background: white; padding: 35px; box-shadow: 0 6px 25px rgba(0,0,0,0.1);">
  
  <!-- Header -->
  <div class="x_title" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 3px solid #e3f2fd;">
    <h2 style="font-size: 2rem; font-weight: 800; color: #1a1a1a;">
      <i class="fa fa-plus-circle" style="color: #4caf50;"></i> 
      New Manifest Entry - <?= htmlspecialchars($office['office_name']) ?>
    </h2>
  </div>

  <!-- Alert Message Container -->
  <div id="manifest_save_result"></div>

  <!-- Manifest Form -->
  <form id="manifestForm" autocomplete="off">
    <input type="hidden" name="office_id" value="<?= $office_id ?>">
    
    <!-- Car & Driver Selection Section -->
    <div style="background: #f5f7fa; padding: 25px; border-radius: 12px; margin-bottom: 25px; border: 2px solid #e0e0e0;">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        
        <!-- Car Dropdown -->
        <div>
          <label style="display: block; font-weight: 700; color: #333; margin-bottom: 8px; font-size: 1.05rem;">
            <i class="fa fa-car" style="color: #2196f3;"></i> Select Car
          </label>
          <select name="car_id" id="carSelect" class="form-control" required style="height: 45px; font-size: 1.05rem; font-weight: 600;">
            <option value="">-- Select Car --</option>
            <?php while($car = mysqli_fetch_assoc($cars_result)): ?>
              <option value="<?= $car['car_id'] ?>"><?= htmlspecialchars($car['car_number']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- Driver Dropdown -->
        <div>
          <label style="display: block; font-weight: 700; color: #333; margin-bottom: 8px; font-size: 1.05rem;">
            <i class="fa fa-user" style="color: #ff9800;"></i> Select Driver
          </label>
          <select name="driver_id" id="driverSelect" class="form-control" required style="height: 45px; font-size: 1.05rem; font-weight: 600;">
            <option value="">-- Select Driver --</option>
            <?php while ($driver = mysqli_fetch_assoc($drivers_result)): ?>
              <option value="<?= $driver['staff_id'] ?>" data-license="<?= htmlspecialchars($driver['driving_license'] ?? '') ?>">
                <?= htmlspecialchars($driver['staff_name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- Driver License (Auto-populated) -->
        <div>
          <label style="display: block; font-weight: 700; color: #333; margin-bottom: 8px; font-size: 1.05rem;">
            <i class="fa fa-id-card" style="color: #4caf50;"></i> Driving License
          </label>
          <input type="text" name="driver_license" id="driverLicense" class="form-control" readonly 
                 style="height: 45px; font-size: 1.05rem; font-weight: 600; background: #f5f5f5;" 
                 placeholder="Auto-filled">
        </div>

        <!-- Manual Input Toggle -->
        <div>
          <label style="display: block; font-weight: 700; color: #333; margin-bottom: 8px; font-size: 1.05rem;">
            <i class="fa fa-edit" style="color: #9c27b0;"></i> Input Mode
          </label>
          <div style="background: white; padding: 10px 15px; border-radius: 8px; border: 2px solid #e0e0e0; height: 45px; display: flex; align-items: center;">
            <input type="checkbox" id="manualModeCheckbox" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
            <label for="manualModeCheckbox" style="margin: 0; font-size: 1rem; font-weight: 700; color: #9c27b0; cursor: pointer;">
              Manual Input
            </label>
          </div>
        </div>

      </div>
    </div>

    <!-- Dockets Table -->
    <div style="overflow-x: auto; margin-bottom: 25px;">
      <table class="table table-bordered" style="background: white; border-radius: 12px; overflow: hidden; font-size: 0.85rem; table-layout: fixed; width: 100%;">
        <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
          <tr>
            <th style="width: 3%; text-align: center; font-weight: 800; padding: 8px 4px;">#</th>
            <th style="width: 9%; font-weight: 800; padding: 8px 6px;">Docket No</th>
            <th style="width: 13%; font-weight: 800; padding: 8px 6px;">Client Name</th>
            <th style="width: 10%; font-weight: 800; padding: 8px 6px;">Item</th>
            <th style="width: 15%; font-weight: 800; padding: 8px 6px;">Address</th>
            <th style="width: 6%; font-weight: 800; padding: 8px 4px;">Box</th>
            <th style="width: 7%; font-weight: 800; padding: 8px 4px;">Weight</th>
            <th style="width: 7%; font-weight: 800; padding: 8px 4px;">Rate</th>
            <th style="width: 8%; font-weight: 800; padding: 8px 4px;">Amount</th>
            <th style="width: 12%; font-weight: 800; padding: 8px 6px;">E-Way Bill</th>
            <th style="width: 7%; font-weight: 800; padding: 8px 4px;">Pay To</th>
          </tr>
        </thead>
        <tbody id="docketTableBody">
          <?php for($i = 1; $i <= 25; $i++): ?>
          <tr>
            <td style="text-align: center; font-weight: 700; color: #666; padding: 4px;"><?= $i ?></td>
            <td style="padding: 2px;">
              <input type="text" name="doc_no[]" class="form-control docket-input" style="text-transform: uppercase; font-size: 0.85rem; padding: 5px 6px; width: 100%;" placeholder="Docket #">
            </td>
            <td style="padding: 2px;">
              <input type="text" name="client_name[]" class="form-control client-field" readonly style="background: #f5f5f5; text-transform: uppercase; font-size: 0.85rem; padding: 5px 6px; width: 100%;">
            </td>
            <td style="padding: 2px;">
              <input type="text" name="item[]" class="form-control editable-field" style="background: #fff; text-transform: uppercase; font-size: 0.85rem; padding: 5px 6px; width: 100%;">
            </td>
            <td style="padding: 2px;">
              <input type="text" name="client_address[]" class="form-control client-field" readonly style="background: #f5f5f5; text-transform: uppercase; font-size: 0.85rem; padding: 5px 6px; width: 100%;">
            </td>
            <td style="padding: 2px;">
              <input type="number" name="box[]" class="form-control box-input" readonly style="background: #f5f5f5; font-size: 0.85rem; padding: 5px 4px; width: 100%;">
            </td>
            <td style="padding: 2px;">
              <input type="number" name="weight[]" class="form-control client-field" readonly step="0.01" style="background: #f5f5f5; font-size: 0.85rem; padding: 5px 4px; width: 100%;">
            </td>
            <td style="padding: 2px;">
              <input type="number" name="rate[]" class="form-control rate-input editable-field" step="0.01" style="background: #fff; font-size: 0.85rem; padding: 5px 4px; width: 100%;">
            </td>
            <td style="padding: 2px;">
              <input type="number" name="amount[]" class="form-control amount-field" readonly step="0.01" style="background: #f5f5f5; font-weight: 700; font-size: 0.85rem; padding: 5px 4px; width: 100%;">
            </td>
            <td style="padding: 2px;">
              <input type="text" name="eway_bill[]" class="form-control editable-field" style="background: #fff; text-transform: uppercase; font-size: 0.85rem; padding: 5px 6px; width: 100%;">
            </td>
            <td style="padding: 2px;">
              <input type="number" name="pay_to[]" class="form-control pay-to-input editable-field" step="0.01" style="background: #fff; font-size: 0.85rem; padding: 5px 4px; width: 100%;">
            </td>
          </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>

    <!-- Totals and Submit Button -->
    <div style="margin-top: 25px; display: flex; gap: 20px; flex-wrap: wrap; align-items: center; justify-content: space-between; background: #f5f7fa; padding: 20px; border-radius: 12px;">
      
      <!-- Save Button -->
      <div>
        <button type="button" id="saveManifestBtn" class="btn btn-success btn-lg" style="font-weight: 800; font-size: 1.3rem; padding: 14px 45px; border-radius: 10px; box-shadow: 0 6px 20px rgba(76,175,80,0.4);">
          <i class="fa fa-save" style="margin-right: 10px;"></i> SAVE MANIFEST
        </button>
      </div>

      <!-- Totals Display -->
      <div style="display: flex; gap: 18px; align-items: center; flex-wrap: wrap;">
        
        <!-- Gross Total -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 16px 22px; border-radius: 10px; color: white; box-shadow: 0 4px 15px rgba(102,126,234,0.4);">
          <div style="font-size: 0.9rem; opacity: 0.95; margin-bottom: 4px; font-weight: 600;">Gross Total</div>
          <div id="grossTotal" style="font-weight: 900; font-size: 1.6rem;">0.00</div>
        </div>

        <!-- Total To Pay -->
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 16px 22px; border-radius: 10px; color: white; box-shadow: 0 4px 15px rgba(245,87,108,0.4);">
          <div style="font-size: 0.9rem; opacity: 0.95; margin-bottom: 4px; font-weight: 600;">Total To Pay</div>
          <div id="payTotal" style="font-weight: 900; font-size: 1.6rem;">0.00</div>
        </div>

        <!-- Net Total -->
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 16px 22px; border-radius: 10px; color: white; box-shadow: 0 4px 15px rgba(79,172,254,0.4);">
          <div style="font-size: 0.9rem; opacity: 0.95; margin-bottom: 4px; font-weight: 600;">Net Total</div>
          <div id="netTotal" style="font-weight: 900; font-size: 1.6rem;">0.00</div>
        </div>

      </div>
    </div>

  </form>
</div>

<script>
/**
 * Manifest Entry JavaScript
 * Clean, professional implementation with proper event handling
 */
(function() {
  'use strict';
  
  // State management
  let isManualMode = false;
  
  // DOM element references
  const form = document.getElementById('manifestForm');
  const driverSelect = document.getElementById('driverSelect');
  const driverLicense = document.getElementById('driverLicense');
  const manualCheckbox = document.getElementById('manualModeCheckbox');
  const saveResult = document.getElementById('manifest_save_result');
  
  /**
   * Initialize the form
   */
  function init() {
    // Set up event listeners
    setupEventListeners();
    
    // Initialize field states
    setFieldsReadonly(true);
    
    // Calculate initial totals
    calculateTotals();
    
    console.log('Manifest form initialized successfully');
  }
  
  /**
   * Set up all event listeners
   */
  function setupEventListeners() {
    // Save button click handler
    const saveBtn = document.getElementById('saveManifestBtn');
    if (saveBtn) {
      saveBtn.addEventListener('click', function(e) {
        // Trigger form submission
        form.dispatchEvent(new Event('submit'));
      });
    }
    
    // Driver selection - auto-populate license
    driverSelect.addEventListener('change', handleDriverChange);
    
    // Manual mode toggle
    manualCheckbox.addEventListener('change', handleManualModeToggle);
    
    // Docket input - auto-fetch or manual entry
    document.addEventListener('blur', handleDocketBlur, true);
    
    // Rate/Box changes - recalculate amount
    document.addEventListener('input', handleAmountCalculation, true);
    
    // Form submission
    form.addEventListener('submit', handleFormSubmit);
    
    // Enter key navigation
    form.addEventListener('keydown', handleEnterKeyNavigation);
  }
  
  /**
   * Handle driver selection change
   */
  function handleDriverChange(e) {
    const selectedOption = e.target.options[e.target.selectedIndex];
    const license = selectedOption.dataset.license || '';
    driverLicense.value = license;
  }
  
  /**
   * Handle manual mode toggle
   */
  function handleManualModeToggle(e) {
    isManualMode = e.target.checked;
    
    if (isManualMode) {
      setFieldsReadonly(false);
      showAlert('✓ Manual Input Mode: You can now enter all details manually', 'info');
    } else {
      setFieldsReadonly(true);
      showAlert('✓ Auto-Fetch Mode: Docket details will be fetched automatically', 'info');
    }
  }
  
  /**
   * Set readonly state for docket fields
   * Note: item, rate, eway_bill, and pay_to are always editable
   */
  function setFieldsReadonly(readonly) {
    // Only control client-field and box-input (not editable-field)
    const fields = document.querySelectorAll('.client-field, .box-input');
    fields.forEach(field => {
      field.readOnly = readonly;
      field.style.background = readonly ? '#f5f5f5' : '#fff';
    });
  }
  
  /**
   * Handle docket number blur event
   */
  function handleDocketBlur(e) {
    if (!e.target.classList.contains('docket-input')) return;
    
    const docketNo = e.target.value.trim().toUpperCase();
    e.target.value = docketNo;
    
    if (!docketNo) {
      clearRow(e.target.closest('tr'));
      calculateTotals();
      return;
    }
    
    // In manual mode, don't fetch data
    if (isManualMode) {
      return;
    }
    
    // Fetch docket data
    fetchDocketData(docketNo, e.target.closest('tr'));
  }
  
  /**
   * Fetch docket data from server
   */
  function fetchDocketData(docketNo, row) {
    fetch(`manifest_fetch_docket.php?docket_no=${encodeURIComponent(docketNo)}`)
      .then(response => response.json())
      .then(data => {
        if (data && data.status !== 'not_found') {
          populateRow(row, data);
        } else {
          clearRow(row);
        }
        calculateTotals();
      })
      .catch(error => {
        console.error('Error fetching docket data:', error);
        clearRow(row);
        calculateTotals();
      });
  }
  
  /**
   * Populate row with docket data
   */
  function populateRow(row, data) {
    row.querySelector('input[name="client_name[]"]').value = data.client_name || '';
    row.querySelector('input[name="item[]"]').value = data.item || '';
    row.querySelector('input[name="client_address[]"]').value = data.client_address || '';
    row.querySelector('input[name="box[]"]').value = data.box || '';
    row.querySelector('input[name="weight[]"]').value = data.weight || '';
    row.querySelector('input[name="rate[]"]').value = data.rate || '';
    row.querySelector('input[name="eway_bill[]"]').value = data.eway_bill || '';
    row.querySelector('input[name="pay_to[]"]').value = data.pay_to || '';
    
    // Calculate amount
    const rate = parseFloat(data.rate) || 0;
    const box = parseFloat(data.box) || 1;
    row.querySelector('input[name="amount[]"]').value = (rate * box).toFixed(2);
  }
  
  /**
   * Clear row data
   */
  function clearRow(row) {
    const inputs = row.querySelectorAll('input:not(.docket-input)');
    inputs.forEach(input => input.value = '');
  }
  
  /**
   * Handle amount calculation when rate or box changes
   * Also recalculate totals when pay_to changes
   */
  function handleAmountCalculation(e) {
    // Recalculate amount if rate or box changed
    if (e.target.classList.contains('rate-input') || e.target.classList.contains('box-input')) {
      const row = e.target.closest('tr');
      const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
      const box = parseFloat(row.querySelector('.box-input').value) || 1;
      row.querySelector('input[name="amount[]"]').value = (rate * box).toFixed(2);
    }
    
    // Recalculate totals for any numeric field change
    if (e.target.name === 'amount[]' || e.target.name === 'pay_to[]' || 
        e.target.classList.contains('rate-input') || e.target.classList.contains('box-input')) {
      calculateTotals();
    }
  }
  
  /**
   * Calculate all totals
   */
  function calculateTotals() {
    let grossTotal = 0;
    let payTotal = 0;
    
    const amounts = document.querySelectorAll('input[name="amount[]"]');
    const payTos = document.querySelectorAll('input[name="pay_to[]"]');
    
    amounts.forEach(input => {
      grossTotal += parseFloat(input.value) || 0;
    });
    
    payTos.forEach(input => {
      payTotal += parseFloat(input.value) || 0;
    });
    
    const netTotal = grossTotal - payTotal;
    
    document.getElementById('grossTotal').textContent = grossTotal.toFixed(2);
    document.getElementById('payTotal').textContent = payTotal.toFixed(2);
    document.getElementById('netTotal').textContent = netTotal.toFixed(2);
  }
  
  /**
   * Handle Enter key as Tab for navigation
   */
  function handleEnterKeyNavigation(e) {
    if (e.key !== 'Enter' || e.target.tagName === 'BUTTON') return;
    
    e.preventDefault();
    
    const focusableElements = Array.from(form.querySelectorAll(
      'input:not([readonly]):not([disabled]), select:not([disabled])'
    ));
    
    const currentIndex = focusableElements.indexOf(e.target);
    if (currentIndex > -1 && currentIndex < focusableElements.length - 1) {
      focusableElements[currentIndex + 1].focus();
      if (focusableElements[currentIndex + 1].select) {
        focusableElements[currentIndex + 1].select();
      }
    }
  }
  
  /**
   * Handle form submission
   */
  function handleFormSubmit(e) {
    e.preventDefault();
    
    // Validate car and driver selection
    const carId = document.getElementById('carSelect').value;
    const driverId = document.getElementById('driverSelect').value;
    
    if (!carId || !driverId) {
      showAlert('⚠️ Please select both Car and Driver before saving!', 'danger');
      return;
    }
    
    // Get form data
    const formData = new FormData(form);
    
    // Add manual mode flag
    formData.append('is_manual', isManualMode ? '1' : '0');
    
    // Show loading message
    showAlert('<i class="fa fa-spinner fa-spin"></i> Saving manifest...', 'info');
    
    // Submit via AJAX
    fetch('manifest_save.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(html => {
      saveResult.innerHTML = html;
      
      // Reset form on success
      if (html.includes('success') || html.includes('Success')) {
        setTimeout(() => {
          form.reset();
          manualCheckbox.checked = false;
          isManualMode = false;
          setFieldsReadonly(true);
          driverLicense.value = '';
          calculateTotals();
        }, 2000);
      }
      
      // Scroll to message
      saveResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    })
    .catch(error => {
      console.error('Error saving manifest:', error);
      showAlert('❌ Error saving manifest. Please try again.', 'danger');
    });
  }
  
  /**
   * Show alert message
   */
  function showAlert(message, type) {
    saveResult.innerHTML = `<div class="alert alert-${type}" style="font-size: 1.2rem; padding: 20px;">${message}</div>`;
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
  
})();
</script>

<style>
/* Responsive Design */
@media (max-width: 1200px) {
  .x_panel { padding: 20px 15px; }
  table { font-size: 0.95rem; }
}

@media (max-width: 768px) {
  .x_panel { padding: 15px 10px; }
  h2 { font-size: 1.5rem !important; }
  table { font-size: 0.85rem; }
  input.form-control { font-size: 0.85rem; padding: 5px; }
}
</style>
