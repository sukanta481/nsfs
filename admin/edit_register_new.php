<?php
require 'check_auth.php';
requirePermission('docket_edit');
require 'top_header.php';
require 'conn.php';
require_once 'DocketDetailsManager.php';

$docket_id = intval($_REQUEST['docket_id'] ?? 0);
$message = '';
$message_type = '';

// Check DOD (Date of Delivery) permission for editing delivery date after delivery
$has_dod_permission = hasPermission('docket_edit_delivery_date');

// Check for success/error messages
if(isset($_GET['success'])) {
    $message = 'Docket updated successfully!';
    $message_type = 'success';
}
if(isset($_GET['error'])) {
    $message = $_GET['error'];
    $message_type = 'error';
}

// Fetch existing docket entry from docket_details table
$sql = "SELECT dd.*, o.office_name 
        FROM docket_details dd
        LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
        WHERE dd.docket_id = $docket_id";
$res = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    echo '<div class="alert alert-danger">Docket entry not found. <a href="register.php?type=list_register&lp=ac">Back to List</a></div>';
    exit;
}

// Determine if delivery date editing is allowed
// If status is 'Delivered', only allow editing if user has DOD permission
$is_delivered = ($data['status'] === 'Delivered');
$can_edit_delivery_date = !$is_delivered || $has_dod_permission;

// Fetch dropdowns data
$companies = mysqli_query($conn, "SELECT * FROM tbl_company ORDER BY company_title ASC");
$cars = mysqli_query($conn, "SELECT * FROM tbl_car ORDER BY car_number ASC");
$drivers = mysqli_query($conn, "SELECT staff_id, staff_name, staff_phone, driving_license FROM tbl_staff WHERE staff_role = 'Driver' AND active_status = 1 ORDER BY staff_name ASC");
$helpers = mysqli_query($conn, "SELECT staff_id, staff_name, staff_phone FROM tbl_staff WHERE staff_role = 'Helper' AND active_status = 1 ORDER BY staff_name ASC");
$offices = mysqli_query($conn, "SELECT * FROM tbl_offices ORDER BY office_name ASC");
?>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <div class="right_col" role="main">
        <div class="edit-docket-container">
          
          <!-- Header -->
          <div class="page-header">
            <div class="header-left">
              <a href="register.php?type=list_register&lp=ac" class="btn-back">
                <i class="fa fa-arrow-left"></i> Back to List
              </a>
            </div>
            <div class="header-center">
              <h2><i class="fa fa-edit"></i> Edit Docket</h2>
              <p>Docket No: <strong><?= htmlspecialchars($data['doc_no']) ?></strong></p>
            </div>
            <div class="header-right">
              <a href="view_register.php?docket_id=<?= $docket_id ?>" class="btn-view-docket">
                <i class="fa fa-eye"></i> View Docket
              </a>
            </div>
          </div>

          <!-- Success/Error Message -->
          <?php if($message): ?>
          <div class="alert-message <?= $message_type ?>">
            <i class="fa fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
            <button class="close-alert" onclick="this.parentElement.style.display='none'">&times;</button>
          </div>
          <?php endif; ?>

          <!-- Edit Form -->
          <form id="editDocketForm" method="POST" action="update_docket.php" class="docket-form">
            <input type="hidden" name="docket_id" value="<?= $docket_id ?>">
            
            <!-- Basic Information -->
            <div class="form-card">
              <div class="card-header">
                <i class="fa fa-info-circle"></i>
                <h3>Basic Information</h3>
              </div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group">
                    <label>Docket Number <span class="required">*</span></label>
                    <input type="text" name="doc_no" value="<?= htmlspecialchars($data['doc_no']) ?>" class="form-control" required readonly style="background: #f5f5f5;">
                  </div>
                  
                  <div class="form-group">
                    <label>Service Type</label>
                    <select name="service_type" class="form-control">
                      <option value="Standard" <?= $data['service_type'] == 'Standard' ? 'selected' : '' ?>>Standard</option>
                      <option value="Express" <?= $data['service_type'] == 'Express' ? 'selected' : '' ?>>Express</option>
                      <option value="Same Day" <?= $data['service_type'] == 'Same Day' ? 'selected' : '' ?>>Same Day</option>
                    </select>
                  </div>
                  
                  <div class="form-group">
                    <label>Document Type</label>
                    <select name="doc_type" class="form-control">
                      <option value="DRS" <?= $data['doc_type'] == 'DRS' ? 'selected' : '' ?>>DRS</option>
                      <option value="NON-DRS" <?= $data['doc_type'] == 'NON-DRS' ? 'selected' : '' ?>>NON-DRS</option>
                    </select>
                  </div>
                  
                  <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                      <option value="Pending" <?= $data['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                      <option value="Picked Up" <?= $data['status'] == 'Picked Up' ? 'selected' : '' ?>>Picked Up</option>
                      <option value="In Transit" <?= $data['status'] == 'In Transit' ? 'selected' : '' ?>>In Transit</option>
                      <option value="Out for Delivery" <?= $data['status'] == 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                      <option value="Delivered" <?= $data['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                      <option value="Delayed" <?= $data['status'] == 'Delayed' ? 'selected' : '' ?>>Delayed</option>
                    </select>
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="form-group">
                    <label>Office</label>
                    <select name="office_id" class="form-control">
                      <option value="">Select Office</option>
                      <?php while($office = mysqli_fetch_assoc($offices)): ?>
                        <option value="<?= $office['office_id'] ?>" <?= $data['office_id'] == $office['office_id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($office['office_name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  
                  <div class="form-group">
                    <label>Pickup Date/Time</label>
                    <input type="datetime-local" name="pickup_datetime" value="<?= $data['pickup_datetime'] ? date('Y-m-d\TH:i', strtotime($data['pickup_datetime'])) : '' ?>" class="form-control">
                  </div>
                  
                  <div class="form-group">
                    <label>Delivery Date/Time <?php if($is_delivered && !$has_dod_permission): ?><span style="color:#e67e22; font-size:11px;">(Locked - DOD permission required)</span><?php endif; ?></label>
                    <input type="datetime-local" name="delivery_datetime" 
                           value="<?= $data['delivery_datetime'] ? date('Y-m-d\TH:i', strtotime($data['delivery_datetime'])) : '' ?>" 
                           class="form-control" 
                           <?php if(!$can_edit_delivery_date): ?>disabled readonly style="background: #f5f5f5; cursor: not-allowed;"<?php endif; ?>>
                    <?php if($is_delivered && !$has_dod_permission): ?>
                    <small style="color: #e67e22; display: block; margin-top: 5px;">
                      <i class="fa fa-lock"></i> This docket is marked as Delivered. Only users with DOD permission can edit the delivery date.
                    </small>
                    <?php endif; ?>
                    <?php if($is_delivered && $has_dod_permission): ?>
                    <small style="color: #27ae60; display: block; margin-top: 5px;">
                      <i class="fa fa-unlock"></i> You have DOD permission - delivery date can be edited.
                    </small>
                    <?php endif; ?>
                    <?php if(!$can_edit_delivery_date): ?>
                    <input type="hidden" name="delivery_datetime" value="<?= $data['delivery_datetime'] ? date('Y-m-d\TH:i', strtotime($data['delivery_datetime'])) : '' ?>">
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- Company/Sender Information -->
            <div class="form-card">
              <div class="card-header">
                <i class="fa fa-building"></i>
                <h3>Sender Information</h3>
              </div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group">
                    <label>Company <span class="required">*</span></label>
                    <select name="company_id" id="company_id" class="form-control" required>
                      <option value="">Select Company</option>
                      <?php 
                      mysqli_data_seek($companies, 0);
                      while($company = mysqli_fetch_assoc($companies)): ?>
                        <option value="<?= $company['company_id'] ?>" 
                                data-address="<?= htmlspecialchars($company['company_address']) ?>"
                                data-phone="<?= htmlspecialchars($company['company_phone']) ?>"
                                data-email="<?= htmlspecialchars($company['company_email']) ?>"
                                <?= $data['company_id'] == $company['company_id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($company['company_title']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  
                  <div class="form-group">
                    <label>Company Phone</label>
                    <input type="text" name="company_phone" id="company_phone" value="<?= htmlspecialchars($data['company_phone']) ?>" class="form-control">
                  </div>
                  
                  <div class="form-group">
                    <label>Company Email</label>
                    <input type="email" name="company_email" id="company_email" value="<?= htmlspecialchars($data['company_email']) ?>" class="form-control">
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="form-group full-width">
                    <label>Company Address</label>
                    <textarea name="company_address" id="company_address" class="form-control" rows="2"><?= htmlspecialchars($data['company_address']) ?></textarea>
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="form-group full-width">
                    <label>Pickup Location</label>
                    <textarea name="pickup_location" class="form-control" rows="2"><?= htmlspecialchars($data['pickup_location']) ?></textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- Client/Receiver Information -->
            <div class="form-card">
              <div class="card-header">
                <i class="fa fa-user"></i>
                <h3>Receiver Information</h3>
              </div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group">
                    <label>Client Name <span class="required">*</span></label>
                    <input type="text" name="client_name" value="<?= htmlspecialchars($data['client_name']) ?>" class="form-control" required>
                  </div>
                  
                  <div class="form-group">
                    <label>Client Phone</label>
                    <input type="text" name="client_phone" value="<?= htmlspecialchars($data['client_phone']) ?>" class="form-control">
                  </div>
                  
                  <div class="form-group">
                    <label>Client Email</label>
                    <input type="email" name="client_email" value="<?= htmlspecialchars($data['client_email']) ?>" class="form-control">
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="form-group full-width">
                    <label>Client Address</label>
                    <textarea name="client_address" class="form-control" rows="2"><?= htmlspecialchars($data['client_address']) ?></textarea>
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="form-group full-width">
                    <label>Delivery Location</label>
                    <textarea name="delivery_location" class="form-control" rows="2"><?= htmlspecialchars($data['delivery_location']) ?></textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- Vehicle & Staff Information -->
            <div class="form-card">
              <div class="card-header">
                <i class="fa fa-truck"></i>
                <h3>Vehicle & Staff Information</h3>
              </div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group">
                    <label>Rented Car</label>
                    <div class="radio-group">
                      <label class="radio-label">
                        <input type="radio" name="rented_car" value="1" <?= $data['rented_car'] == 1 ? 'checked' : '' ?>>
                        <span>Yes</span>
                      </label>
                      <label class="radio-label">
                        <input type="radio" name="rented_car" value="0" <?= $data['rented_car'] == 0 ? 'checked' : '' ?>>
                        <span>No</span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label>Car</label>
                    <select name="car_id" id="car_id" class="form-control">
                      <option value="">Select Car</option>
                      <?php 
                      mysqli_data_seek($cars, 0);
                      while($car = mysqli_fetch_assoc($cars)): ?>
                        <option value="<?= $car['car_id'] ?>" 
                                data-number="<?= htmlspecialchars($car['car_number']) ?>"
                                data-details="<?= htmlspecialchars($car['car_details'] ?? '') ?>"
                                <?= $data['car_id'] == $car['car_id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($car['car_number']) ?><?= !empty($car['car_details']) ? ' - ' . htmlspecialchars($car['car_details']) : '' ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="form-group">
                    <label>Driver</label>
                    <select name="driver_id" id="driver_id" class="form-control">
                      <option value="">Select Driver</option>
                      <?php 
                      mysqli_data_seek($drivers, 0);
                      while($driver = mysqli_fetch_assoc($drivers)): ?>
                        <option value="<?= $driver['staff_id'] ?>"
                                data-phone="<?= htmlspecialchars($driver['staff_phone']) ?>"
                                data-license="<?= htmlspecialchars($driver['driving_license']) ?>"
                                <?= $data['driver_id'] == $driver['staff_id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($driver['staff_name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  
                  <div class="form-group">
                    <label>Driver Phone</label>
                    <input type="text" name="driver_phone" id="driver_phone" value="<?= htmlspecialchars($data['driver_phone']) ?>" class="form-control">
                  </div>
                  
                  <div class="form-group">
                    <label>Helper</label>
                    <select name="helper_id" id="helper_id" class="form-control">
                      <option value="">Select Helper</option>
                      <?php 
                      mysqli_data_seek($helpers, 0);
                      while($helper = mysqli_fetch_assoc($helpers)): ?>
                        <option value="<?= $helper['staff_id'] ?>"
                                data-phone="<?= htmlspecialchars($helper['staff_phone']) ?>"
                                <?= $data['helper_id'] == $helper['staff_id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($helper['staff_name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  
                  <div class="form-group">
                    <label>Helper Phone</label>
                    <input type="text" name="helper_phone" id="helper_phone" value="<?= htmlspecialchars($data['helper_phone']) ?>" class="form-control">
                  </div>
                </div>
              </div>
            </div>

            <!-- Package Details -->
            <div class="form-card">
              <div class="card-header">
                <i class="fa fa-cube"></i>
                <h3>Package Details</h3>
              </div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group">
                    <label>Item Description</label>
                    <input type="text" name="item" value="<?= htmlspecialchars($data['item']) ?>" class="form-control">
                  </div>
                  
                  <div class="form-group">
                    <label>Box/Packages</label>
                    <input type="number" name="box" value="<?= htmlspecialchars($data['box']) ?>" class="form-control" min="0">
                  </div>
                  
                  <div class="form-group">
                    <label>Weight (kg)</label>
                    <input type="number" name="weight" value="<?= htmlspecialchars($data['weight']) ?>" class="form-control" min="0" step="0.01">
                  </div>
                  
                  <div class="form-group">
                    <label>Dimensions</label>
                    <input type="text" name="dimensions" value="<?= htmlspecialchars($data['dimensions']) ?>" class="form-control" placeholder="L x W x H">
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="form-group">
                    <label>Invoice Number</label>
                    <input type="text" name="invoice_no" value="<?= htmlspecialchars($data['invoice_no']) ?>" class="form-control">
                  </div>
                  
                  <div class="form-group">
                    <label>E-way Bill</label>
                    <input type="text" name="eway_bill" value="<?= htmlspecialchars($data['eway_bill']) ?>" class="form-control">
                  </div>
                  
                  <div class="form-group">
                    <label>Rate</label>
                    <input type="number" name="rate" value="<?= htmlspecialchars($data['rate']) ?>" class="form-control" min="0" step="0.01">
                  </div>
                  
                  <div class="form-group">
                    <label>Amount</label>
                    <input type="number" name="amount" value="<?= htmlspecialchars($data['amount']) ?>" class="form-control" min="0" step="0.01">
                  </div>
                </div>
              </div>
            </div>

            <!-- Additional Information -->
            <div class="form-card">
              <div class="card-header">
                <i class="fa fa-sticky-note"></i>
                <h3>Additional Information</h3>
              </div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group full-width">
                    <label>Special Instructions</label>
                    <textarea name="special_instructions" class="form-control" rows="3"><?= htmlspecialchars($data['special_instructions']) ?></textarea>
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="form-group full-width">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3"><?= htmlspecialchars($data['remarks']) ?></textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Update Docket
              </button>
              <a href="register.php?type=list_register&lp=ac" class="btn btn-secondary">
                <i class="fa fa-times"></i> Cancel
              </a>
            </div>
          </form>

        </div>
      </div>
      
      <?php require 'footer.php';?>
    </div>
  </div>
</body>

<script>
// Auto-fill company details
document.getElementById('company_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if(selected.value) {
        document.getElementById('company_address').value = selected.dataset.address || '';
        document.getElementById('company_phone').value = selected.dataset.phone || '';
        document.getElementById('company_email').value = selected.dataset.email || '';
    }
});

// Auto-fill driver phone
document.getElementById('driver_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if(selected.value) {
        document.getElementById('driver_phone').value = selected.dataset.phone || '';
    }
});

// Auto-fill helper phone
document.getElementById('helper_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if(selected.value) {
        document.getElementById('helper_phone').value = selected.dataset.phone || '';
    }
});

// Form validation
document.getElementById('editDocketForm').addEventListener('submit', function(e) {
    const docNo = document.querySelector('input[name="doc_no"]').value;
    const companyId = document.querySelector('select[name="company_id"]').value;
    const clientName = document.querySelector('input[name="client_name"]').value;
    
    if(!docNo || !companyId || !clientName) {
        e.preventDefault();
        alert('Please fill all required fields (marked with *)');
        return false;
    }
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.edit-docket-container {
    font-family: 'Inter', sans-serif;
    padding: 20px;
    background: #f5f7fa;
    min-height: calc(100vh - 100px);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    background: #fff;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.header-center {
    text-align: center;
    flex: 1;
}

.header-center h2 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 1.8rem;
    font-weight: 700;
}

.header-center p {
    margin: 0;
    color: #7f8c8d;
    font-size: 1rem;
}

.header-center strong {
    color: #3498db;
    font-weight: 700;
}

.btn-back, .btn-view-docket {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-back {
    background: #6c757d;
    color: #fff;
}

.btn-back:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.btn-view-docket {
    background: #3498db;
    color: #fff;
}

.btn-view-docket:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.alert-message {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    position: relative;
    animation: slideDown 0.3s ease;
}

.alert-message.success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-message.error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.alert-message i {
    font-size: 1.3rem;
}

.close-alert {
    position: absolute;
    right: 15px;
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: inherit;
    opacity: 0.7;
}

.close-alert:hover {
    opacity: 1;
}

.form-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 18px 25px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-header i {
    font-size: 1.3rem;
}

.card-header h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
}

.card-body {
    padding: 25px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.required {
    color: #e74c3c;
    margin-left: 3px;
}

.form-control {
    padding: 10px 12px;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s;
    font-family: 'Inter', sans-serif;
    line-height: 1.4;
}

select.form-control {
    padding: 8px 12px;
    height: auto;
}

select.form-control option {
    padding: 8px 12px;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

.radio-group {
    display: flex;
    gap: 20px;
    padding-top: 8px;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: 500;
}

.radio-label input[type="radio"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    padding: 25px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.btn {
    padding: 14px 35px;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102,126,234,0.4);
}

.btn-secondary {
    background: #6c757d;
    color: #fff;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
