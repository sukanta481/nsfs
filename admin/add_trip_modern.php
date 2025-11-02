<?php require 'top_header.php'; ?>
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
$drivers = mysqli_query($conn, "SELECT * FROM tbl_driver ORDER BY driver_name ASC");
$helpers = mysqli_query($conn, "SELECT * FROM tbl_helper ORDER BY helper_name ASC");

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
                Error creating trip. Please try again.
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
                                <i class="fas fa-car"></i>
                                Select Car <span class="required">*</span>
                            </label>
                            <select name="car_id" class="form-control" required style="color: #000 !important; background: #fff !important;">
                                <option value="" style="color: #000 !important;">Choose Car</option>
                                <?php while ($car = mysqli_fetch_assoc($cars)): ?>
                                    <option value="<?= $car['car_id'] ?>" style="color: #000 !important;">
                                        <?= htmlspecialchars($car['car_number'] ?? 'N/A') ?>
                                        <?php if (isset($car['car_model']) && !empty($car['car_model'])): ?>
                                            - <?= htmlspecialchars($car['car_model']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-user-tie"></i>
                                Select Driver <span class="required">*</span>
                            </label>
                            <select name="driver_id" id="driver_select" class="form-control" required style="color: #000 !important; background: #fff !important;">
                                <option value="">Choose Driver</option>
                                <?php while ($driver = mysqli_fetch_assoc($drivers)): ?>
                                    <option value="<?= $driver['driver_id'] ?>" data-phone="<?= htmlspecialchars($driver['driver_phone'] ?? '') ?>">
                                        <?= htmlspecialchars($driver['driver_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-phone"></i>
                                Driver Phone
                            </label>
                            <input type="text" name="driver_phone" id="driver_phone" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-user"></i>
                                Select Helper
                            </label>
                            <select name="helper_id" id="helper_select" class="form-control" style="color: #000 !important; background: #fff !important;">
                                <option value="">Choose Helper (Optional)</option>
                                <?php while ($helper = mysqli_fetch_assoc($helpers)): ?>
                                    <option value="<?= $helper['helper_id'] ?>" data-phone="<?= htmlspecialchars($helper['helper_phone'] ?? '') ?>">
                                        <?= htmlspecialchars($helper['helper_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-phone"></i>
                                Helper Phone
                            </label>
                            <input type="text" name="helper_phone" id="helper_phone" class="form-control" readonly>
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
            
            // Setup driver phone auto-fill
            const driverSelect = document.getElementById('driver_select');
            const driverPhoneInput = document.getElementById('driver_phone');
            
            if (driverSelect && driverPhoneInput) {
                driverSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const phone = selectedOption.getAttribute('data-phone') || '';
                    driverPhoneInput.value = phone;
                });
            }
            
            // Setup helper phone auto-fill
            const helperSelect = document.getElementById('helper_select');
            const helperPhoneInput = document.getElementById('helper_phone');
            
            if (helperSelect && helperPhoneInput) {
                helperSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const phone = selectedOption.getAttribute('data-phone') || '';
                    helperPhoneInput.value = phone;
                });
            }
        });

        function addDocket() {
            docketCount++;
            const container = document.getElementById('dockets-container');
            
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
                            <input type="text" name="dockets[${docketCount}][doc_no]" class="form-control" placeholder="Enter docket number" required style="color: #000 !important;">
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
                                Sender Information
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
                                <textarea name="dockets[${docketCount}][company_address]" class="form-control company-address-${docketCount}" rows="2" required style="color: #000 !important;"></textarea>
                            </div>
                        </div>

                        <div class="docket-section">
                            <div class="section-title">
                                <i class="fas fa-user-check"></i>
                                Receiver Information
                            </div>
                            <div class="form-group">
                                <label>Name <span class="required">*</span></label>
                                <input type="text" name="dockets[${docketCount}][client_name]" class="form-control" required style="color: #000 !important;">
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
                                <textarea name="dockets[${docketCount}][client_address]" class="form-control" rows="2" required style="color: #000 !important;"></textarea>
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
                            <input type="text" name="dockets[${docketCount}][dimensions]" class="form-control" placeholder="e.g., 10x20x30 cm" style="color: #000 !important;">
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', docketHTML);
            
            // Setup company address auto-fill for the newly added docket
            setupCompanyAddressAutoFill(docketCount);
        }
        
        // Function to setup company address auto-fill
        function setupCompanyAddressAutoFill(docketId) {
            const companySelect = document.querySelector(`select[data-docket="${docketId}"]`);
            const addressField = document.querySelector(`.company-address-${docketId}`);
            
            if (companySelect && addressField) {
                companySelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const address = selectedOption.getAttribute('data-address') || '';
                    addressField.value = address;
                });
            }
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
        });
    </script>
</html>
