<?php 
require 'check_auth.php';
requirePermission('docket_create');
require 'top_header.php'; 
?>
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>      
      <!-- page content -->
      <div class="right_col" role="main">
        <div class="">
          
<?php
// Fetch data for dropdowns
$companies = mysqli_query($conn, "SELECT company_id, company_title, company_address FROM tbl_company ORDER BY company_title ASC");
$cars = mysqli_query($conn, "SELECT * FROM tbl_car ORDER BY car_number ASC");
$drivers = mysqli_query($conn, "SELECT staff_id, staff_name, staff_phone, driving_license FROM tbl_staff WHERE staff_role = 'Driver' AND active_status = 1 ORDER BY staff_name ASC");
$helpers = mysqli_query($conn, "SELECT staff_id, staff_name, staff_phone FROM tbl_staff WHERE staff_role = 'Helper' AND active_status = 1 ORDER BY staff_name ASC");
$offices = mysqli_query($conn, "SELECT * FROM tbl_offices ORDER BY office_name ASC");

// Get Barasat office as default
$barasat_office = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tbl_offices WHERE office_name = 'Barasat' LIMIT 1"));
$default_office_id = $barasat_office['office_id'] ?? 0;
$default_office_name = $barasat_office['office_name'] ?? '';
$default_office_address = $barasat_office['office_address'] ?? '';
$default_office_phone = $barasat_office['office_phone'] ?? '';

$show_success = isset($_GET['msg']) && $_GET['msg'] === 'success';
$show_error = isset($_GET['msg']) && $_GET['msg'] === 'error';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
        /* Force ALL text to be visible - CRITICAL FIX */
        input, select, textarea, option {
            color: #2c3e50 !important;
            -webkit-text-fill-color: #2c3e50 !important;
            background-color: #ffffff !important;
        }

        /* Ensure dropdown options are fully visible */
        select option {
            color: #000000 !important;
            background-color: #ffffff !important;
            font-weight: 500 !important;
            padding: 8px !important;
        }

        select option:hover {
            background-color: #3498db !important;
            color: #ffffff !important;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .trip-container {
            font-family: 'Inter', sans-serif;
            padding: 20px;
            max-width: 100%;
        }

        .page-header {
            background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
            color: #fff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .page-header p {
            font-size: 1rem;
            opacity: 0.9;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateY(0);
                opacity: 1;
            }
            to {
                transform: translateY(-20px);
                opacity: 0;
            }
        }
        
        .alert.hiding {
            animation: slideOut 0.3s ease-out forwards;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #4a6b88 0%, #3b5770 100%);
            color: #fff;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .card-header i {
            font-size: 1.4rem;
        }

        .card-body {
            padding: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .form-group label .required {
            color: #e74c3c;
        }

        .form-control {
            padding: 8px 12px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            color: #2c3e50 !important;
            background: #fff !important;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
            color: #2c3e50 !important;
        }

        .form-control::placeholder {
            color: #95a5a6 !important;
            opacity: 1;
        }

        select.form-control {
            cursor: pointer;
            color: #2c3e50 !important;
        }

        select.form-control option {
            color: #2c3e50 !important;
            background: #fff !important;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
            color: #2c3e50 !important;
        }

        input.form-control {
            color: #2c3e50 !important;
        }
        
        input[type="date"].form-control {
            cursor: pointer;
            position: relative;
        }
        
        input[type="date"].form-control::-webkit-calendar-picker-indicator {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            cursor: pointer;
            opacity: 0;
        }
        
        input[type="datetime-local"].form-control {
            cursor: pointer;
            position: relative;
        }
        
        input[type="datetime-local"].form-control::-webkit-calendar-picker-indicator {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            cursor: pointer;
            opacity: 0;
        }

        input.form-control:disabled,
        input.form-control[readonly] {
            background-color: #f8f9fa !important;
            color: #6c757d !important;
            cursor: not-allowed;
        }

        /* Docket Section */
        .dockets-container {
            margin-top: 20px;
        }

        .docket-item {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            position: relative;
            transition: all 0.3s;
        }

        .docket-item:hover {
            border-color: #3498db;
            box-shadow: 0 4px 15px rgba(52,152,219,0.1);
        }

        .docket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #dee2e6;
        }

        .docket-number {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2b5876;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .remove-docket-btn {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
        }

        .remove-docket-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .docket-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .docket-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #3498db;
        }

        .add-docket-btn {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: #fff;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(39,174,96,0.3);
        }

        .add-docket-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39,174,96,0.4);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
            margin-top: 30px;
        }

        .btn {
            padding: 15px 40px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(52,152,219,0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52,152,219,0.4);
        }

        .btn-secondary {
            background: #95a5a6;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        @media (max-width: 992px) {
            .docket-sections {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="trip-container">
        <div class="page-header">
            <h1><i class="fas fa-route"></i> Create New Trip</h1>
            <p>Add trip details and multiple dockets in one go</p>
        </div>

        <?php if ($show_success): ?>
            <div class="alert alert-success" id="successAlert">
                <i class="fas fa-check-circle"></i>
                Trip created successfully with all dockets!
            </div>
        <?php endif; ?>

        <?php if ($show_error): ?>
            <div class="alert alert-error" id="errorAlert">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                if (isset($_GET['msg']) && $_GET['msg'] === 'duplicate' && isset($_GET['dockets'])) {
                    echo 'Duplicate docket(s) found: <strong>' . htmlspecialchars($_GET['dockets']) . '</strong>. These dockets already exist in the system!';
                } else {
                    echo 'Error creating trip. Please try again.';
                }
                ?>
            </div>
        <?php endif; ?>

        <form id="tripForm" method="POST" action="save_trip_modern.php">
            <!-- Trip Details Card -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-truck"></i>
                    Trip Details
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>
                                <i class="fas fa-building"></i>
                                Branch Office <span class="required">*</span>
                            </label>
                            <select name="office_id" id="office_select" class="form-control" required style="color: #000 !important; background: #fff !important;">
                                <?php 
                                mysqli_data_seek($offices, 0);
                                while ($office = mysqli_fetch_assoc($offices)): 
                                    $selected = ($office['office_id'] == $default_office_id) ? 'selected' : '';
                                ?>
                                    <option value="<?= $office['office_id'] ?>" 
                                            data-name="<?= htmlspecialchars($office['office_name']) ?>"
                                            data-address="<?= htmlspecialchars($office['office_address']) ?>"
                                            data-phone="<?= htmlspecialchars($office['office_phone']) ?>"
                                            <?= $selected ?>
                                            style="color: #000 !important;">
                                        <?= htmlspecialchars($office['office_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-marker-alt"></i>
                                Office Address
                            </label>
                            <input type="text" name="office_address" id="office_address" class="form-control" readonly value="<?= htmlspecialchars($default_office_address) ?>">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-phone-alt"></i>
                                Office Phone
                            </label>
                            <input type="text" name="office_phone" id="office_phone" class="form-control" readonly value="<?= htmlspecialchars($default_office_phone) ?>">
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-car"></i>
                                Car Number <span class="required">*</span>
                            </label>
                            <input type="text" name="car_number" id="carNumberInput" class="form-control" list="carList" placeholder="Type or select car number" autocomplete="off" required style="color: #000 !important; background: #fff !important;">
                            <datalist id="carList">
                                <?php
                                mysqli_data_seek($cars, 0);
                                while ($car = mysqli_fetch_assoc($cars)):
                                ?>
                                    <option value="<?= htmlspecialchars($car['car_number']) ?>" data-id="<?= $car['car_id'] ?>">
                                        <?= htmlspecialchars($car['car_number']) ?>
                                        <?php if (isset($car['car_model']) && !empty($car['car_model'])): ?>
                                            - <?= htmlspecialchars($car['car_model']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endwhile; ?>
                            </datalist>
                            <input type="hidden" name="car_id" id="carIdHidden">
                            <small style="color: #7f8c8d; font-size: 12px;">Type manually for external vehicles or select from dropdown</small>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-user-tie"></i>
                                Driver Name <span class="required">*</span>
                            </label>
                            <input type="text" name="driver_name" id="driverNameInput" class="form-control" list="driverList" placeholder="Type or select driver name" autocomplete="off" required style="color: #000 !important; background: #fff !important;">
                            <datalist id="driverList">
                                <?php
                                mysqli_data_seek($drivers, 0);
                                while ($driver = mysqli_fetch_assoc($drivers)):
                                ?>
                                    <option value="<?= htmlspecialchars($driver['staff_name']) ?>"
                                            data-id="<?= $driver['staff_id'] ?>"
                                            data-phone="<?= htmlspecialchars($driver['staff_phone'] ?? '') ?>"
                                            data-license="<?= htmlspecialchars($driver['driving_license'] ?? '') ?>">
                                        <?= htmlspecialchars($driver['staff_name']) ?> - <?= htmlspecialchars($driver['staff_phone'] ?? '') ?>
                                    </option>
                                <?php endwhile; ?>
                            </datalist>
                            <input type="hidden" name="driver_id" id="driverIdHidden">
                            <small style="color: #7f8c8d; font-size: 12px;">Type manually for external drivers or select from dropdown</small>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-phone"></i>
                                Driver Phone
                            </label>
                            <input type="text" name="driver_phone" id="driver_phone" class="form-control" placeholder="Enter driver phone" style="color: #000 !important;">
                            <small style="color: #7f8c8d; font-size: 12px;">Auto-filled from dropdown or enter manually</small>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-user"></i>
                                Helper Name
                            </label>
                            <input type="text" name="helper_name" id="helperNameInput" class="form-control" list="helperList" placeholder="Type or select helper name" autocomplete="off" style="color: #000 !important; background: #fff !important;">
                            <datalist id="helperList">
                                <?php
                                mysqli_data_seek($helpers, 0);
                                while ($helper = mysqli_fetch_assoc($helpers)):
                                ?>
                                    <option value="<?= htmlspecialchars($helper['staff_name']) ?>"
                                            data-id="<?= $helper['staff_id'] ?>"
                                            data-phone="<?= htmlspecialchars($helper['staff_phone'] ?? '') ?>">
                                        <?= htmlspecialchars($helper['staff_name']) ?> - <?= htmlspecialchars($helper['staff_phone'] ?? '') ?>
                                    </option>
                                <?php endwhile; ?>
                            </datalist>
                            <input type="hidden" name="helper_id" id="helperIdHidden">
                            <small style="color: #7f8c8d; font-size: 12px;">Type manually for external helpers or select from dropdown (Optional)</small>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-phone"></i>
                                Helper Phone
                            </label>
                            <input type="text" name="helper_phone" id="helper_phone" class="form-control" placeholder="Enter helper phone (optional)" style="color: #000 !important;">
                            <small style="color: #7f8c8d; font-size: 12px;">Auto-filled from dropdown or enter manually</small>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar-plus"></i>
                                Pickup Date & Time <span class="required">*</span>
                            </label>
                            <input type="datetime-local" name="pickup_datetime" class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dockets Card -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-boxes"></i>
                    Dockets
                </div>
                <div class="card-body">
                    <div id="dockets-container" class="dockets-container">
                        <!-- First docket will be added here by JavaScript -->
                    </div>

                    <button type="button" class="add-docket-btn" onclick="addDocket()">
                        <i class="fas fa-plus-circle"></i>
                        Add Another Docket
                    </button>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='register.php?type=list_register'">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Save Trip & All Dockets
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

        </div>
      </div>
      <?php require 'footer.php';?>
    </div>
  </div>
</body>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        let docketCount = 0;

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Add first docket on page load
            addDocket();
            
            // Auto-hide alerts after 10 seconds
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');
            
            if (successAlert) {
                setTimeout(function() {
                    successAlert.classList.add('hiding');
                    setTimeout(function() {
                        successAlert.remove();
                    }, 300); // Wait for animation to complete
                }, 10000); // 10 seconds
            }
            
            if (errorAlert) {
                setTimeout(function() {
                    errorAlert.classList.add('hiding');
                    setTimeout(function() {
                        errorAlert.remove();
                    }, 300); // Wait for animation to complete
                }, 10000); // 10 seconds
            }
            
            // Setup office auto-fill
            const officeSelect = document.getElementById('office_select');
            const officeAddress = document.getElementById('office_address');
            const officePhone = document.getElementById('office_phone');
            
            if (officeSelect && officeAddress && officePhone) {
                officeSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    officeAddress.value = selectedOption.getAttribute('data-address') || '';
                    officePhone.value = selectedOption.getAttribute('data-phone') || '';
                });
            }
            
            // Setup driver phone auto-fill for datalist input
            const driverNameInput = document.getElementById('driverNameInput');
            const driverPhoneInput = document.getElementById('driver_phone');
            const driverIdHidden = document.getElementById('driverIdHidden');
            const driverList = document.getElementById('driverList');
            
            if (driverNameInput && driverPhoneInput && driverList) {
                driverNameInput.addEventListener('input', function() {
                    const value = this.value;
                    const options = driverList.querySelectorAll('option');
                    
                    // Find matching option from datalist
                    for (let option of options) {
                        if (option.value === value) {
                            const phone = option.getAttribute('data-phone') || '';
                            const driverId = option.getAttribute('data-id') || '';
                            driverPhoneInput.value = phone;
                            driverIdHidden.value = driverId;
                            break;
                        }
                    }
                });
                
                // Clear driver ID if manually typed name not in list
                driverNameInput.addEventListener('blur', function() {
                    const value = this.value;
                    const options = driverList.querySelectorAll('option');
                    let found = false;
                    
                    for (let option of options) {
                        if (option.value === value) {
                            found = true;
                            break;
                        }
                    }
                    
                    if (!found) {
                        driverIdHidden.value = '';
                    }
                });
            }
            
            // Setup helper phone auto-fill from datalist
            const helperNameInput = document.getElementById('helperNameInput');
            const helperPhoneInput = document.getElementById('helper_phone');
            const helperIdHidden = document.getElementById('helperIdHidden');
            const helperList = document.getElementById('helperList');
            
            if (helperNameInput && helperPhoneInput && helperIdHidden && helperList) {
                helperNameInput.addEventListener('input', function() {
                    const helperName = this.value;
                    const options = helperList.querySelectorAll('option');
                    let found = false;
                    
                    options.forEach(option => {
                        if (option.value === helperName) {
                            const helperId = option.getAttribute('data-id') || '';
                            const phone = option.getAttribute('data-phone') || '';
                            helperPhoneInput.value = phone;
                            helperIdHidden.value = helperId;
                            found = true;
                        }
                    });
                    
                    // If manual entry (not in list), clear hidden ID
                    if (!found) {
                        helperIdHidden.value = '';
                    }
                });
            }
        });

        function addDocket() {
            docketCount++;
            const container = document.getElementById('dockets-container');
            
            // Get first docket's company info if exists
            let defaultCompanyId = '';
            let defaultCompanyAddress = '';
            
            if (docketCount > 1) {
                const firstCompanySelect = document.querySelector('select[name="dockets[1][company_id]"]');
                const firstCompanyAddress = document.querySelector('textarea[name="dockets[1][company_address]"]');
                
                if (firstCompanySelect) {
                    defaultCompanyId = firstCompanySelect.value;
                }
                if (firstCompanyAddress) {
                    defaultCompanyAddress = firstCompanyAddress.value;
                }
            }
            
            const docketHTML = `
                <div class="docket-item" id="docket-${docketCount}">
                    <div class="docket-header">
                        <div class="docket-number">
                            <i class="fas fa-file-invoice"></i>
                            Docket #${docketCount}
                        </div>
                        ${docketCount > 1 ? `<button type="button" class="remove-docket-btn" onclick="removeDocket(${docketCount})">
                            <i class="fas fa-trash"></i> Remove
                        </button>` : ''}
                    </div>

                    <div class="form-grid" style="margin-bottom: 20px;">
                        <div class="form-group">
                            <label>Docket Number <span class="required">*</span></label>
                            <input type="text" name="dockets[${docketCount}][doc_no]" class="form-control docket-number-input auto-uppercase" data-docket-id="${docketCount}" placeholder="Enter docket number" required style="color: #000 !important; text-transform: uppercase;">
                            <div class="duplicate-warning" id="duplicate-warning-${docketCount}" style="display:none; color:#e74c3c; font-size:0.85rem; margin-top:5px; font-weight:600;">
                                <i class="fas fa-exclamation-triangle"></i> This docket number already exists!
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Service Type</label>
                            <select name="dockets[${docketCount}][service_type]" class="form-control" style="color: #000 !important; background: #fff !important;">
                                <option value="Standard" style="color: #000 !important;">Standard</option>
                                <option value="Express" style="color: #000 !important;">Express</option>
                                <option value="Overnight" style="color: #000 !important;">Overnight</option>
                            </select>
                        </div>
                    </div>

                    <div class="docket-sections">
                        <div class="docket-section">
                            <div class="section-title">
                                <i class="fas fa-user"></i>
                                Sender Information ${docketCount > 1 ? '<small style="color:#27ae60;">(Auto-filled from first docket)</small>' : ''}
                            </div>
                            <div class="form-group">
                                <label>Company <span class="required">*</span></label>
                                <select name="dockets[${docketCount}][company_id]" class="form-control company-select" data-docket="${docketCount}" required style="color: #000 !important; background: #fff !important;">
                                    <option value="" style="color: #000 !important;">Choose Company</option>
                                    <?php 
                                    mysqli_data_seek($companies, 0);
                                    while ($company = mysqli_fetch_assoc($companies)): 
                                    ?>
                                        <option value="<?= $company['company_id'] ?>" data-address="<?= htmlspecialchars($company['company_address'] ?? '') ?>" style="color: #000 !important;"><?= $company['company_title'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Company Address (Pickup Location) <span class="required">*</span></label>
                                <textarea name="dockets[${docketCount}][company_address]" class="form-control company-address-${docketCount} auto-uppercase" rows="2" required style="color: #000 !important; text-transform: uppercase;"></textarea>
                            </div>
                        </div>

                        <div class="docket-section">
                            <div class="section-title">
                                <i class="fas fa-user-check"></i>
                                Receiver Information
                            </div>
                            <div class="form-group">
                                <label>Name <span class="required">*</span></label>
                                <input type="text" name="dockets[${docketCount}][client_name]" class="form-control auto-uppercase" required style="color: #000 !important; text-transform: uppercase;">
                            </div>
                            <div class="form-group">
                                <label>Phone <span class="required">*</span></label>
                                <input type="text" name="dockets[${docketCount}][client_phone]" class="form-control" required style="color: #000 !important;">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="dockets[${docketCount}][client_email]" class="form-control" style="color: #000 !important;">
                            </div>
                            <div class="form-group">
                                <label>Client Address (Delivery Location) <span class="required">*</span></label>
                                <textarea name="dockets[${docketCount}][client_address]" class="form-control auto-uppercase" rows="2" required style="color: #000 !important; text-transform: uppercase;"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid" style="margin-top: 20px;">
                        <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="number" name="dockets[${docketCount}][weight]" class="form-control" step="0.01" placeholder="0.00" style="color: #000 !important;">
                        </div>
                        <div class="form-group">
                            <label>Box/Items</label>
                            <input type="number" name="dockets[${docketCount}][box]" class="form-control" placeholder="0" style="color: #000 !important;">
                        </div>
                        <div class="form-group">
                            <label>Dimensions (L x W x H)</label>
                            <input type="text" name="dockets[${docketCount}][dimensions]" class="form-control auto-uppercase" placeholder="e.g., 10x20x30 cm" style="color: #000 !important; text-transform: uppercase;">
                        </div>
                        <div class="form-group">
                            <label>E-way Bill No. <small style="color: #7f8c8d;">(Optional)</small></label>
                            <input type="text" name="dockets[${docketCount}][eway_bill]" class="form-control auto-uppercase" placeholder="Enter e-way bill number" style="color: #000 !important; text-transform: uppercase;">
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', docketHTML);
            
            // Auto-fill sender info if this is not the first docket
            if (docketCount > 1 && defaultCompanyId) {
                const newCompanySelect = document.querySelector(`select[name="dockets[${docketCount}][company_id]"]`);
                const newCompanyAddress = document.querySelector(`textarea[name="dockets[${docketCount}][company_address]"]`);
                
                if (newCompanySelect) {
                    newCompanySelect.value = defaultCompanyId;
                }
                if (newCompanyAddress) {
                    newCompanyAddress.value = defaultCompanyAddress;
                }
            }
            
            // Setup company address auto-fill for the newly added docket
            setupCompanyAddressAutoFill(docketCount);
            
            // Setup duplicate checking for the newly added docket
            setupDuplicateCheck(docketCount);
            
            // Setup uppercase conversion for the newly added docket
            setupUppercaseConversion(docketCount);
        }
        
        // Function to check for duplicate docket numbers
        function setupDuplicateCheck(docketId) {
            const docketInput = document.querySelector(`input[data-docket-id="${docketId}"]`);
            const warningDiv = document.getElementById(`duplicate-warning-${docketId}`);
            
            if (docketInput && warningDiv) {
                let checkTimeout;
                
                docketInput.addEventListener('input', function() {
                    const docketNo = this.value.trim().toUpperCase();
                    
                    // Clear previous timeout
                    clearTimeout(checkTimeout);
                    
                    // Hide warning while typing
                    warningDiv.style.display = 'none';
                    this.style.borderColor = '#e0e6ed';
                    
                    // Check if empty
                    if (!docketNo) {
                        return;
                    }
                    
                    // Debounce the API call (wait 800ms after user stops typing)
                    checkTimeout = setTimeout(function() {
                        (function() {
                            // robust fetch with error handling and timeout
                            const controller = new AbortController();
                            const timeoutId = setTimeout(() => controller.abort(), 7000);
                            fetch('check_duplicate_docket.php?doc_no=' + encodeURIComponent(docketNo), { signal: controller.signal })
                                .then(response => {
                                    clearTimeout(timeoutId);
                                    if (!response.ok) throw new Error('Server returned ' + response.status);
                                    return response.json();
                                })
                                .then(data => {
                                    if (data && data.exists) {
                                        // Show inline warning
                                        warningDiv.style.display = 'block';
                                        warningDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> This docket already exists! (Status: ${data.status || 'N/A'}, Created: ${data.created_at || 'N/A'})`;
                                        docketInput.style.borderColor = '#e74c3c';
                                        docketInput.style.boxShadow = '0 0 0 3px rgba(231,76,60,0.1)';
                                        // Show popup alert with details
                                        showDuplicateAlert(docketNo, data);
                                    } else {
                                        // Hide warning and show success
                                        warningDiv.style.display = 'none';
                                        docketInput.style.borderColor = '#27ae60';
                                        docketInput.style.boxShadow = '0 0 0 3px rgba(39,174,96,0.1)';
                                    }
                                })
                                .catch(error => {
                                    clearTimeout(timeoutId);
                                    console.error('Error checking duplicate:', error);
                                    // Show non-blocking inline message to user
                                    warningDiv.style.display = 'block';
                                    warningDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> Unable to validate duplicate (server error).`; 
                                    warningDiv.style.color = '#856404';
                                    docketInput.style.borderColor = '#f0ad4e';
                                });
                        })();
                    }, 800);
                });
                
                // Also check on blur (when user leaves the field)
                docketInput.addEventListener('blur', function() {
                    const docketNo = this.value.trim().toUpperCase();
                    if (!docketNo) return;
                    
                    (function() {
                        const controller = new AbortController();
                        const timeoutId = setTimeout(() => controller.abort(), 7000);
                        fetch('check_duplicate_docket.php?doc_no=' + encodeURIComponent(docketNo), { signal: controller.signal })
                            .then(response => {
                                clearTimeout(timeoutId);
                                if (!response.ok) throw new Error('Server returned ' + response.status);
                                return response.json();
                            })
                            .then(data => {
                                if (data && data.exists) {
                                    warningDiv.style.display = 'block';
                                    warningDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> This docket already exists! (Status: ${data.status || 'N/A'}, Created: ${data.created_at || 'N/A'})`;
                                    docketInput.style.borderColor = '#e74c3c';
                                    docketInput.style.boxShadow = '0 0 0 3px rgba(231,76,60,0.1)';
                                    showDuplicateAlert(docketNo, data);
                                }
                            })
                            .catch(error => {
                                clearTimeout(timeoutId);
                                console.error('Error checking duplicate (blur):', error);
                                warningDiv.style.display = 'block';
                                warningDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> Duplicate check failed (server error).`;
                                warningDiv.style.color = '#856404';
                                docketInput.style.borderColor = '#f0ad4e';
                            });
                    })();
                });
            }
        }
        
        // Function to show duplicate alert popup
        function showDuplicateAlert(docketNo, data) {
            // Remove existing alert if any
            const existingAlert = document.getElementById('duplicate-alert-modal');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            // Create modal alert
            const alertHTML = `
                <div id="duplicate-alert-modal" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.7);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                    animation: fadeIn 0.3s ease;
                ">
                    <div style="
                        background: white;
                        padding: 30px;
                        border-radius: 15px;
                        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                        max-width: 500px;
                        width: 90%;
                        animation: slideDown 0.3s ease;
                    ">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #e74c3c;"></i>
                        </div>
                        <h3 style="color: #e74c3c; text-align: center; margin-bottom: 20px; font-size: 1.5rem; font-weight: 800;">
                            ⚠️ DUPLICATE DOCKET FOUND!
                        </h3>
                        <div style="background: #fff3cd; padding: 20px; border-radius: 10px; border-left: 5px solid #ffc107; margin-bottom: 20px;">
                            <p style="margin: 0 0 15px 0; font-size: 1.1rem; color: #333;">
                                <strong style="color: #e74c3c;">Docket Number:</strong> 
                                <span style="font-weight: 700; color: #d63031;">${docketNo}</span>
                            </p>
                            <p style="margin: 0 0 15px 0; font-size: 1rem; color: #333;">
                                <strong>Status:</strong> <span style="color: #f39c12; font-weight: 600;">${data.status}</span>
                            </p>
                            <p style="margin: 0 0 15px 0; font-size: 1rem; color: #333;">
                                <strong>Created On:</strong> ${data.created_at}
                            </p>
                            ${data.trip_group_id ? `<p style="margin: 0 0 15px 0; font-size: 1rem; color: #333;">
                                <strong>Trip Group:</strong> ${data.trip_group_id}
                            </p>` : ''}
                            ${data.company_name && data.company_name !== 'N/A' ? `<p style="margin: 0 0 15px 0; font-size: 1rem; color: #333;">
                                <strong>Company:</strong> ${data.company_name}
                            </p>` : ''}
                            ${data.client_name && data.client_name !== 'N/A' ? `<p style="margin: 0; font-size: 1rem; color: #333;">
                                <strong>Client:</strong> ${data.client_name}
                            </p>` : ''}
                        </div>
                        <div style="background: #fee; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                            <p style="margin: 0; color: #c0392b; font-size: 1rem; font-weight: 600;">
                                <i class="fas fa-info-circle"></i> This docket number already exists in the system. Please use a different docket number.
                            </p>
                        </div>
                        <button onclick="document.getElementById('duplicate-alert-modal').remove()" style="
                            width: 100%;
                            padding: 15px;
                            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
                            color: white;
                            border: none;
                            border-radius: 8px;
                            font-size: 1.1rem;
                            font-weight: 700;
                            cursor: pointer;
                            transition: all 0.3s;
                        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(231,76,60,0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            <i class="fas fa-times-circle"></i> CLOSE
                        </button>
                    </div>
                </div>
                <style>
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    @keyframes slideDown {
                        from { transform: translateY(-50px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                </style>
            `;
            
            document.body.insertAdjacentHTML('beforeend', alertHTML);
            
            // Auto close after 10 seconds
            setTimeout(function() {
                const modal = document.getElementById('duplicate-alert-modal');
                if (modal) {
                    modal.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => modal.remove(), 300);
                }
            }, 10000);
            
            // Close on clicking outside
            document.getElementById('duplicate-alert-modal').addEventListener('click', function(e) {
                if (e.target.id === 'duplicate-alert-modal') {
                    this.remove();
                }
            });
        }
        
        // Function to setup company address auto-fill
        function setupCompanyAddressAutoFill(docketId) {
            const companySelect = document.querySelector(`select[data-docket="${docketId}"]`);
            const addressField = document.querySelector(`.company-address-${docketId}`);
            
            if (companySelect && addressField) {
                companySelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const address = selectedOption.getAttribute('data-address') || '';
                    addressField.value = address.toUpperCase();
                });
            }
        }
        
        // Function to setup uppercase conversion for text inputs
        function setupUppercaseConversion(docketId) {
            const docketElement = document.getElementById(`docket-${docketId}`);
            if (!docketElement) return;
            
            // Get all inputs and textareas with auto-uppercase class
            const uppercaseFields = docketElement.querySelectorAll('.auto-uppercase');
            
            uppercaseFields.forEach(function(field) {
                field.addEventListener('input', function() {
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    this.value = this.value.toUpperCase();
                    this.setSelectionRange(start, end);
                });
            });
        }

        function removeDocket(id) {
            if (confirm('Are you sure you want to remove this docket?')) {
                document.getElementById(`docket-${id}`).remove();
            }
        }

        // Form validation
        document.getElementById('tripForm').addEventListener('submit', function(e) {
            const dockets = document.querySelectorAll('.docket-item');
            if (dockets.length === 0) {
                e.preventDefault();
                alert('Please add at least one docket!');
                return false;
            }
            
            // Check for visible duplicate warnings
            const duplicateWarnings = document.querySelectorAll('.duplicate-warning');
            let hasDuplicates = false;
            
            duplicateWarnings.forEach(function(warning) {
                if (warning.style.display !== 'none') {
                    hasDuplicates = true;
                }
            });
            
            if (hasDuplicates) {
                e.preventDefault();
                alert('⚠️ Cannot save! Some docket numbers already exist in the system. Please check the warnings and use different docket numbers.');
                return false;
            }
        });
    </script>
</html>
