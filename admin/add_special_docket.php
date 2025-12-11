<?php
require 'check_auth.php';
requirePermission('docket_create');
require 'conn.php';

$error = '';
$success = '';

// Get next special docket number with transaction lock to prevent duplicates
function getNextSpecialDocketNo($conn) {
    // Start transaction for atomic operation
    mysqli_begin_transaction($conn);
    
    try {
        // Lock the table row to prevent concurrent access
        $query = "SELECT doc_no FROM docket_details WHERE doc_no LIKE 'SP %' ORDER BY docket_id DESC LIMIT 1 FOR UPDATE";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $lastNo = str_replace('SP ', '', $row['doc_no']);
            $nextNo = intval($lastNo) + 1;
        } else {
            $nextNo = 3456050;
        }
        
        mysqli_commit($conn);
        return 'SP ' . $nextNo;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return 'SP 3456050';
    }
}

$next_docket_no = getNextSpecialDocketNo($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $doc_no = mysqli_real_escape_string($conn, trim($_POST['doc_no']));
    $company_name = mysqli_real_escape_string($conn, trim($_POST['company_name']));
    $company_phone = mysqli_real_escape_string($conn, trim($_POST['company_phone']));
    $client_name = mysqli_real_escape_string($conn, trim($_POST['client_name']));
    $client_phone = mysqli_real_escape_string($conn, trim($_POST['client_phone']));
    $client_address = mysqli_real_escape_string($conn, trim($_POST['client_address']));
    $client_email = mysqli_real_escape_string($conn, trim($_POST['client_email']));
    $company_email = mysqli_real_escape_string($conn, trim($_POST['company_email']));
    $item = mysqli_real_escape_string($conn, trim($_POST['item']));
    $invoice_no = !empty(trim($_POST['invoice_no'])) ? mysqli_real_escape_string($conn, trim($_POST['invoice_no'])) : 'N/A';
    $invoice_amount = !empty(trim($_POST['invoice_amount'])) ? floatval($_POST['invoice_amount']) : 0;
    $box = intval($_POST['box']);
    $weight = floatval($_POST['weight']);
    $rate = floatval($_POST['rate']);
    $amount = floatval($_POST['amount']);
    $pay_to = floatval($_POST['pay_to']);
    $eway_bill = mysqli_real_escape_string($conn, trim($_POST['eway_bill']));
    $office_id = !empty($_POST['office_id']) ? intval($_POST['office_id']) : 'NULL';
    $branch_office = mysqli_real_escape_string($conn, trim($_POST['branch_office']));
    
    // Validation
    if (empty($doc_no) || empty($client_name)) {
        $error = "Please fill in required fields";
    } else {
        // Check if docket number already exists
        $check_query = "SELECT docket_id FROM docket_details WHERE doc_no = '$doc_no'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Docket number already exists. Please use a different number.";
        } else {
            // Start transaction to ensure atomic docket creation
            mysqli_begin_transaction($conn);
            
            try {
                // Double-check docket number doesn't exist (race condition protection)
                $recheck_query = "SELECT docket_id FROM docket_details WHERE doc_no = '$doc_no' FOR UPDATE";
                $recheck_result = mysqli_query($conn, $recheck_query);
                
                if (mysqli_num_rows($recheck_result) > 0) {
                    throw new Exception("Docket number already exists. Please try again.");
                }
                
                // Insert special docket
                $office_id_value = $office_id !== 'NULL' ? $office_id : 'NULL';
                $created_at = date('Y-m-d H:i:s');
                
                $insert_query = "INSERT INTO docket_details (
                doc_no, doc_type, status, created_at, pickup_datetime,
                company_name, company_phone, client_name, client_phone, client_address, client_email, company_email,
                item, invoice_no, invoice_amount, box, weight, rate, amount, unit_price, pay_to, eway_bill,
                office_id, branch_office, service_type
            ) VALUES (
                    '$doc_no', 'SPECIAL', 'Pending',
                    '$created_at', '$created_at',
                    '$company_name', '$company_phone', '$client_name', '$client_phone', '$client_address', '$client_email', '$company_email',
                    '$item', '$invoice_no', $invoice_amount, $box, $weight, $rate, $amount, $rate, $pay_to, '$eway_bill',
                    $office_id_value, '$branch_office', 'Special Docket'
                )";
                
                if (mysqli_query($conn, $insert_query)) {
                    mysqli_commit($conn);
                    $success = "Special Docket $doc_no created successfully!";
                    $next_docket_no = getNextSpecialDocketNo($conn);
                    // Clear form data
                    $_POST = array();
                } else {
                    throw new Exception("Error creating docket: " . mysqli_error($conn));
                }
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = $e->getMessage();
            }
        }
    }
}

// Fetch companies for dropdown
$companies_query = "SELECT company_id, company_title, company_phone FROM tbl_company ORDER BY company_title ASC";
$companies_result = mysqli_query($conn, $companies_query);

// Fetch offices for dropdown
$offices_query = "SELECT office_id, office_name FROM tbl_offices ORDER BY office_name";
$offices_result = mysqli_query($conn, $offices_query);

require 'top_header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
/* Mobile-first responsive design */
* {
    box-sizing: border-box;
}

.special-docket-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 15px;
    margin: 10px;
    max-width: 100%;
}

.form-header {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.form-header h2 {
    margin: 0 0 5px 0;
    font-size: 18px;
    line-height: 1.3;
}

.form-header p {
    margin: 5px 0 0 0;
    font-size: 12px;
    opacity: 0.9;
}

.form-header .badge {
    background: rgba(255,255,255,0.2);
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 11px;
    display: inline-block;
    margin-top: 5px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 15px;
    width: 100%;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    color: #333;
    font-weight: 600;
    font-size: 13px;
}

.form-group label i {
    margin-right: 5px;
    color: #e74c3c;
}

.form-group label .required {
    color: #e74c3c;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

select.form-control {
    padding: 8px 12px;
}

.form-control:focus {
    outline: none;
    border-color: #e74c3c;
    box-shadow: 0 0 0 3px rgba(231,76,60,0.1);
}

.form-control[readonly] {
    background: #f8f9fa;
    cursor: not-allowed;
    font-weight: 600;
    color: #495057;
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

.btn-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

.btn {
    width: 100%;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-align: center;
}

.btn-primary {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(231,76,60,0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.alert {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 13px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.info-box {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 15px;
    font-size: 12px;
    line-height: 1.5;
}

/* Tablet and above */
@media (min-width: 768px) {
    .special-docket-container {
        padding: 25px;
        margin: 15px;
    }
    
    .form-header h2 {
        font-size: 22px;
    }
    
    .form-header p {
        font-size: 13px;
    }
    
    .form-row {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .form-group label {
        font-size: 14px;
    }
    
    .form-control {
        padding: 12px 15px;
        font-size: 15px;
    }
    
    .btn-group {
        flex-direction: row;
        justify-content: flex-start;
    }
    
    .btn {
        width: auto;
        min-width: 180px;
    }
}

/* Desktop */
@media (min-width: 1024px) {
    .special-docket-container {
        padding: 30px;
        margin: 20px;
    }
    
    .form-header {
        padding: 20px;
    }
    
    .form-header h2 {
        font-size: 24px;
    }
    
    .form-row {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .form-row.two-col {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Touch-friendly adjustments */
@media (hover: none) and (pointer: coarse) {
    .form-control {
        min-height: 44px; /* iOS touch target size */
    }
    
    .btn {
        min-height: 44px;
    }
}
</style>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <div class="right_col" role="main">
        <div class="special-docket-container">
          
          <div class="form-header">
            <h2><i class="fas fa-star"></i> Create Special Docket </h2>
            <p>Auto-generated docket number with SP prefix</p>
          </div>

          <?php if (!empty($error)): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
          </div>
          <?php endif; ?>

          <?php if (!empty($success)): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
          </div>
          <?php endif; ?>

          <div class="info-box">
            <i class="fas fa-info-circle"></i> <strong>Note:</strong> Special dockets use <strong>auto-generated numbers</strong> in format "SP 100001". System handles multiple users creating dockets simultaneously. All data saves to the same <code>docket_details</code> table with type "SPECIAL". Fields marked with <span style="color:#e74c3c;">*</span> are required.
          </div>

          <form method="POST" id="specialDocketForm">
            
            <div class="form-group">
              <label><i class="fas fa-hashtag"></i> Docket Number <span class="required">*</span></label>
              <input type="text" name="doc_no" id="doc_no" class="form-control" 
                     value="<?php echo $next_docket_no; ?>" readonly required>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label><i class="fas fa-building"></i> Company Name</label>
                <select name="company_id" id="company_id" class="form-control" onchange="fillCompanyDetails()">
                  <option value="">Select Company</option>
                  <?php
                  if ($companies_result && mysqli_num_rows($companies_result) > 0):
                      while ($company = mysqli_fetch_assoc($companies_result)):
                  ?>
                  <option value="<?php echo $company['company_id']; ?>" 
                          data-name="<?php echo htmlspecialchars($company['company_title']); ?>"
                          data-phone="<?php echo htmlspecialchars($company['company_phone'] ?? ''); ?>"
                          <?php echo (isset($_POST['company_id']) && $_POST['company_id'] == $company['company_id']) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($company['company_title']); ?>
                  </option>
                  <?php 
                      endwhile;
                  endif;
                  ?>
                </select>
                <input type="hidden" name="company_name" id="company_name" 
                       value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : ''; ?>">
              </div>

              <div class="form-group">
                <label><i class="fas fa-phone"></i> Company Phone</label>
                <input type="tel" name="company_phone" id="company_phone" class="form-control" 
                       placeholder="Auto-filled from company" readonly
                       value="<?php echo isset($_POST['company_phone']) ? htmlspecialchars($_POST['company_phone']) : ''; ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label><i class="fas fa-user"></i> Client Name <span class="required">*</span></label>
                <input type="text" name="client_name" class="form-control" 
                       placeholder="Enter client name" required
                       value="<?php echo isset($_POST['client_name']) ? htmlspecialchars($_POST['client_name']) : ''; ?>">
              </div>

              <div class="form-group">
                <label><i class="fas fa-phone"></i> Client Phone</label>
                <input type="tel" name="client_phone" class="form-control" 
                       placeholder="Enter client phone"
                       value="<?php echo isset($_POST['client_phone']) ? htmlspecialchars($_POST['client_phone']) : ''; ?>">
              </div>
            </div>

            <div class="form-group">
              <label><i class="fas fa-map-marker-alt"></i> Client Address</label>
              <input type="text" name="client_address" class="form-control" 
                     placeholder="Enter client address"
                     value="<?php echo isset($_POST['client_address']) ? htmlspecialchars($_POST['client_address']) : ''; ?>">
            </div>

            <div class="form-row">
              <div class="form-group">
                <label><i class="fas fa-envelope"></i> Client Email</label>
                <input type="email" name="client_email" class="form-control" 
                       placeholder="client@example.com"
                       value="<?php echo isset($_POST['client_email']) ? htmlspecialchars($_POST['client_email']) : ''; ?>">
              </div>

              <div class="form-group">
                <label><i class="fas fa-envelope"></i> Company Email</label>
                <input type="email" name="company_email" class="form-control" 
                       placeholder="company@example.com"
                       value="<?php echo isset($_POST['company_email']) ? htmlspecialchars($_POST['company_email']) : ''; ?>">
              </div>
            </div>

            <div class="form-group">
              <label><i class="fas fa-box"></i> Item Description</label>
              <textarea name="item" class="form-control" rows="3" 
                        placeholder="Enter item description"><?php echo isset($_POST['item']) ? htmlspecialchars($_POST['item']) : ''; ?></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label><i class="fas fa-file-invoice"></i> Invoice Number</label>
                <input type="text" name="invoice_no" class="form-control" 
                       placeholder="Enter invoice number (optional)"
                       value="<?php echo isset($_POST['invoice_no']) ? htmlspecialchars($_POST['invoice_no']) : ''; ?>">
              </div>

              <div class="form-group">
                <label><i class="fas fa-rupee-sign"></i> Invoice Amount</label>
                <input type="number" name="invoice_amount" class="form-control" 
                       placeholder="0.00" min="0" step="0.01"
                       value="<?php echo isset($_POST['invoice_amount']) ? htmlspecialchars($_POST['invoice_amount']) : ''; ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label><i class="fas fa-boxes"></i> Number of Boxes</label>
                <input type="number" name="box" class="form-control" 
                       placeholder="0" min="0" step="1"
                       value="<?php echo isset($_POST['box']) ? htmlspecialchars($_POST['box']) : '0'; ?>">
              </div>

              <div class="form-group">
                <label><i class="fas fa-weight"></i> Weight (kg)</label>
                <input type="number" name="weight" class="form-control" 
                       placeholder="0.00" min="0" step="0.01"
                       value="<?php echo isset($_POST['weight']) ? htmlspecialchars($_POST['weight']) : '0'; ?>">
              </div>

              <div class="form-group">
                <label><i class="fas fa-rupee-sign"></i> Rate</label>
                <input type="number" name="rate" id="rate" class="form-control" 
                       placeholder="0.00" min="0" step="0.01"
                       value="<?php echo isset($_POST['rate']) ? htmlspecialchars($_POST['rate']) : '0'; ?>"
                       oninput="calculateAmount()">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label><i class="fas fa-calculator"></i> Amount</label>
                <input type="number" name="amount" id="amount" class="form-control" 
                       placeholder="0.00" min="0" step="0.01"
                       value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : '0'; ?>">
              </div>

              <div class="form-group">
                <label><i class="fas fa-money-bill"></i> Pay To</label>
                <input type="number" name="pay_to" class="form-control" 
                       placeholder="0.00" min="0" step="0.01"
                       value="<?php echo isset($_POST['pay_to']) ? htmlspecialchars($_POST['pay_to']) : '0'; ?>">
              </div>

              <div class="form-group">
                <label><i class="fas fa-file-invoice"></i> E-Way Bill</label>
                <input type="text" name="eway_bill" class="form-control" 
                       placeholder="Enter E-Way Bill number"
                       value="<?php echo isset($_POST['eway_bill']) ? htmlspecialchars($_POST['eway_bill']) : ''; ?>">
              </div>
            </div>

            <div class="form-group">
              <label><i class="fas fa-map-pin"></i> Branch Office</label>
              <select name="office_id" id="office_id" class="form-control" onchange="updateBranchName()">
                <option value="">Select Office</option>
                <?php
                if ($offices_result && mysqli_num_rows($offices_result) > 0):
                    while ($office = mysqli_fetch_assoc($offices_result)):
                        $is_barasat = (stripos($office['office_name'], 'barasat') !== false);
                        $is_selected = (isset($_POST['office_id']) && $_POST['office_id'] == $office['office_id']) || (!isset($_POST['office_id']) && $is_barasat);
                ?>
                <option value="<?php echo $office['office_id']; ?>" 
                        data-name="<?php echo htmlspecialchars($office['office_name']); ?>"
                        <?php echo $is_selected ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($office['office_name']); ?>
                </option>
                <?php 
                    endwhile;
                endif;
                ?>
              </select>
              <input type="hidden" name="branch_office" id="branch_office" 
                     value="<?php echo isset($_POST['branch_office']) ? htmlspecialchars($_POST['branch_office']) : ''; ?>">
            </div>

            <div class="btn-group">
              <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Special Docket
              </button>
              <a href="register.php?type=list_register" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
              </a>
            </div>

          </form>

        </div>
      </div>

      <?php require 'footer.php'; ?>
    </div>
  </div>

<script>
function calculateAmount() {
    const rate = parseFloat(document.getElementById('rate').value) || 0;
    document.getElementById('amount').value = rate.toFixed(2);
}

function updateBranchName() {
    const select = document.getElementById('office_id');
    const selectedOption = select.options[select.selectedIndex];
    const branchName = selectedOption.getAttribute('data-name') || '';
    document.getElementById('branch_office').value = branchName;
}

function fillCompanyDetails() {
    const select = document.getElementById('company_id');
    const selectedOption = select.options[select.selectedIndex];
    const companyName = selectedOption.getAttribute('data-name') || '';
    const companyPhone = selectedOption.getAttribute('data-phone') || '';
    
    document.getElementById('company_name').value = companyName;
    document.getElementById('company_phone').value = companyPhone;
}

// Set default branch name on page load
document.addEventListener('DOMContentLoaded', function() {
    updateBranchName(); // Set the default selected office name
});

// Form validation
document.getElementById('specialDocketForm').addEventListener('submit', function(e) {
    const clientName = document.querySelector('[name="client_name"]').value.trim();
    
    if (!clientName) {
        e.preventDefault();
        alert('Please enter client name');
        return false;
    }
    
    return true;
});
</script>

</body>
</html>
