<?php
require 'top_header.php';

// Handle Add/Update/Delete operations
$message = '';
$messageType = '';

// Function to generate unique staff ID
function generateStaffId($conn, $office_id) {
    // Get office details
    $office_query = "SELECT office_name FROM tbl_offices WHERE office_id = " . intval($office_id);
    $office_result = mysqli_query($conn, $office_query);
    
    if ($office_result && mysqli_num_rows($office_result) > 0) {
        $office = mysqli_fetch_assoc($office_result);
        $office_name = strtoupper($office['office_name']);
        
        // Get first 3 characters of office name (remove spaces)
        $office_code = substr(preg_replace('/[^A-Z]/', '', $office_name), 0, 3);
        
        // If less than 3 characters, pad with X
        $office_code = str_pad($office_code, 3, 'X', STR_PAD_RIGHT);
        
        // Get the last staff number for this office
        $last_id_query = "SELECT staff_unique_id FROM tbl_staff 
                          WHERE staff_unique_id LIKE 'NSFS" . $office_code . "%' 
                          ORDER BY staff_id DESC LIMIT 1";
        $last_id_result = mysqli_query($conn, $last_id_query);
        
        if ($last_id_result && mysqli_num_rows($last_id_result) > 0) {
            $last_staff = mysqli_fetch_assoc($last_id_result);
            $last_number = intval(substr($last_staff['staff_unique_id'], -3));
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }
        
        // Format: NSFS + Office Code + 3-digit number
        return 'NSFS' . $office_code . str_pad($new_number, 3, '0', STR_PAD_LEFT);
    }
    
    return 'NSFS' . 'XXX' . '001'; // Fallback
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $office_id = intval($_POST['office_id']);
            $staff_name = strtoupper(mysqli_real_escape_string($conn, $_POST['staff_name']));
            $staff_email = mysqli_real_escape_string($conn, $_POST['staff_email']);
            $staff_phone = mysqli_real_escape_string($conn, $_POST['staff_phone']);
            $staff_role = mysqli_real_escape_string($conn, $_POST['staff_role']);
            $branch_office = mysqli_real_escape_string($conn, $_POST['branch_office']);
            $date_of_joining = mysqli_real_escape_string($conn, $_POST['date_of_joining']);
            $address = mysqli_real_escape_string($conn, $_POST['address']);
            $emergency_contact = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
            $emergency_contact_name = strtoupper(mysqli_real_escape_string($conn, $_POST['emergency_contact_name']));
            $salary = mysqli_real_escape_string($conn, $_POST['salary']);
            $active_status = isset($_POST['active_status']) ? 1 : 0;
            
            // Generate unique staff ID
            $staff_unique_id = generateStaffId($conn, $office_id);
            
            $query = "INSERT INTO tbl_staff (
                        staff_unique_id, staff_name, staff_email, staff_phone, staff_role, 
                        office_id, branch_office, date_of_joining, address, 
                        emergency_contact, emergency_contact_name, salary, active_status
                      ) VALUES (
                        '$staff_unique_id', '$staff_name', '$staff_email', '$staff_phone', '$staff_role',
                        $office_id, '$branch_office', " . ($date_of_joining ? "'$date_of_joining'" : "NULL") . ", '$address',
                        '$emergency_contact', '$emergency_contact_name', " . ($salary ? "'$salary'" : "NULL") . ", $active_status
                      )";
            
            if (mysqli_query($conn, $query)) {
                $message = "Staff added successfully! Staff ID: <strong>$staff_unique_id</strong>";
                $messageType = 'success';
            } else {
                $message = "Error adding staff: " . mysqli_error($conn);
                $messageType = 'error';
            }
        }
        
        elseif ($action === 'update') {
            $staff_id = intval($_POST['staff_id']);
            $staff_name = strtoupper(mysqli_real_escape_string($conn, $_POST['staff_name']));
            $staff_email = mysqli_real_escape_string($conn, $_POST['staff_email']);
            $staff_phone = mysqli_real_escape_string($conn, $_POST['staff_phone']);
            $staff_role = mysqli_real_escape_string($conn, $_POST['staff_role']);
            $office_id = intval($_POST['office_id']);
            $branch_office = mysqli_real_escape_string($conn, $_POST['branch_office']);
            $date_of_joining = mysqli_real_escape_string($conn, $_POST['date_of_joining']);
            $address = mysqli_real_escape_string($conn, $_POST['address']);
            $emergency_contact = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
            $emergency_contact_name = strtoupper(mysqli_real_escape_string($conn, $_POST['emergency_contact_name']));
            $salary = mysqli_real_escape_string($conn, $_POST['salary']);
            $active_status = isset($_POST['active_status']) ? 1 : 0;
            
            $query = "UPDATE tbl_staff SET 
                      staff_name='$staff_name', 
                      staff_email='$staff_email', 
                      staff_phone='$staff_phone', 
                      staff_role='$staff_role',
                      office_id=$office_id,
                      branch_office='$branch_office',
                      date_of_joining=" . ($date_of_joining ? "'$date_of_joining'" : "NULL") . ",
                      address='$address',
                      emergency_contact='$emergency_contact',
                      emergency_contact_name='$emergency_contact_name',
                      salary=" . ($salary ? "'$salary'" : "NULL") . ",
                      active_status=$active_status 
                      WHERE staff_id=$staff_id";
            
            if (mysqli_query($conn, $query)) {
                $message = "Staff updated successfully!";
                $messageType = 'success';
            } else {
                $message = "Error updating staff: " . mysqli_error($conn);
                $messageType = 'error';
            }
        }
        
        elseif ($action === 'delete') {
            $staff_id = intval($_POST['staff_id']);
            $query = "DELETE FROM tbl_staff WHERE staff_id=$staff_id";
            
            if (mysqli_query($conn, $query)) {
                $message = "Staff deleted successfully!";
                $messageType = 'success';
            } else {
                $message = "Error deleting staff: " . mysqli_error($conn);
                $messageType = 'error';
            }
        }
    }
}

// Fetch all staff
$staff_query = "SELECT s.*, o.office_name 
                FROM tbl_staff s 
                LEFT JOIN tbl_offices o ON s.office_id = o.office_id 
                ORDER BY s.staff_id DESC";
$staff_result = mysqli_query($conn, $staff_query);

// Fetch all offices for dropdown
$offices_query = "SELECT office_id, office_name FROM tbl_offices ORDER BY office_name ASC";
$offices_result = mysqli_query($conn, $offices_query);
$offices = [];
while ($office = mysqli_fetch_assoc($offices_result)) {
    $offices[] = $office;
}

// Define staff roles
$staff_roles = [
    'Manager',
    'Assistant Manager',
    'Driver',
    'Helper',
    'Accountant',
    'Data Entry Operator',
    'Office Assistant',
    'Supervisor',
    'Warehouse Staff',
    'Delivery Boy',
    'Security Guard',
    'Cleaner',
    'Other'
];
?>

<body class="nav-md">
<div class="container body">
<div class="main_container">
<?php require 'left_panel.php'; ?>
<?php require 'header_banner.php'; ?>

<div class="right_col" role="main">
    <div class="staff-crud-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fa fa-users"></i> Staff Management</h1>
            <p>Add, edit, and manage all your staff members</p>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>" id="alertMessage">
            <i class="fa fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $message ?>
        </div>
        <?php endif; ?>

        <!-- Add Staff Form -->
        <div class="card">
            <div class="card-header">
                <i class="fa fa-plus-circle"></i>
                <span id="formTitle">Add New Staff Member</span>
            </div>
            <div class="card-body">
                <form id="staffForm" method="POST">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="staff_id" id="staff_id" value="">
                    
                    <div class="form-section-title">Basic Information</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Staff Name <span class="required">*</span></label>
                            <input type="text" name="staff_name" id="staff_name" class="form-control" required placeholder="Enter full name">
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="staff_email" id="staff_email" class="form-control" placeholder="staff@example.com">
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number <span class="required">*</span></label>
                            <input type="text" name="staff_phone" id="staff_phone" class="form-control" required placeholder="Enter phone number">
                        </div>
                        
                        <div class="form-group">
                            <label>Staff Role <span class="required">*</span></label>
                            <select name="staff_role" id="staff_role" class="form-control" required>
                                <option value="">-- Select Role --</option>
                                <?php foreach ($staff_roles as $role): ?>
                                    <option value="<?= $role ?>"><?= $role ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-section-title">Office & Location</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Branch Office <span class="required">*</span></label>
                            <select name="office_id" id="office_id" class="form-control" required onchange="updateBranchName()">
                                <option value="">-- Select Office --</option>
                                <?php foreach ($offices as $office): ?>
                                    <option value="<?= $office['office_id'] ?>" data-name="<?= htmlspecialchars($office['office_name']) ?>">
                                        <?= htmlspecialchars($office['office_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Office Name (Auto-filled)</label>
                            <input type="text" name="branch_office" id="branch_office" class="form-control" readonly placeholder="Will auto-fill">
                        </div>
                        
                        <div class="form-group">
                            <label>Date of Joining</label>
                            <input type="date" name="date_of_joining" id="date_of_joining" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>Monthly Salary (₹)</label>
                            <input type="number" name="salary" id="salary" class="form-control" placeholder="Enter salary amount" step="0.01">
                        </div>
                    </div>

                    <div class="form-section-title">Contact & Emergency Details</div>
                    <div class="form-grid">
                        <div class="form-group form-group-full">
                            <label>Address</label>
                            <textarea name="address" id="address" class="form-control" rows="2" placeholder="Enter full address"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" id="emergency_contact_name" class="form-control" placeholder="Contact person name">
                        </div>
                        
                        <div class="form-group">
                            <label>Emergency Contact Number</label>
                            <input type="text" name="emergency_contact" id="emergency_contact" class="form-control" placeholder="Emergency phone number">
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="active_status" id="active_status" checked>
                                <span>Active Status</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> <span id="submitBtnText">Add Staff Member</span>
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelBtn" style="display:none;">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Staff List -->
        <div class="card">
            <div class="card-header">
                <i class="fa fa-list"></i>
                <span>All Staff Members (<?= mysqli_num_rows($staff_result) ?>)</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Staff ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Phone</th>
                                <th>Office</th>
                                <th>Salary</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($staff_result) > 0): ?>
                                <?php while ($staff = mysqli_fetch_assoc($staff_result)): ?>
                                <tr>
                                    <td><strong class="staff-id-badge"><?= htmlspecialchars($staff['staff_unique_id']) ?></strong></td>
                                    <td><?= htmlspecialchars($staff['staff_name']) ?></td>
                                    <td><span class="role-badge"><?= htmlspecialchars($staff['staff_role']) ?></span></td>
                                    <td><?= htmlspecialchars($staff['staff_phone']) ?></td>
                                    <td><?= htmlspecialchars($staff['branch_office'] ?: 'N/A') ?></td>
                                    <td><?= $staff['salary'] ? '₹' . number_format($staff['salary'], 2) : 'N/A' ?></td>
                                    <td>
                                        <?php if ($staff['active_status'] == 1): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn-action btn-edit" onclick='editStaff(<?= json_encode($staff, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <button class="btn-action btn-delete" onclick="deleteStaff(<?= $staff['staff_id'] ?>, '<?= htmlspecialchars($staff['staff_name'], ENT_QUOTES) ?>')">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No staff members found. Add your first staff member above!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
</div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.staff-crud-container {
    font-family: 'Inter', sans-serif;
    padding: 0 35px 60px 35px;
    min-height: calc(100vh - 160px);
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    color: #2c3e50;
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-header h1 .fa-users {
    color: #667eea;
}

.page-header p {
    color: #7f8c8d;
    font-size: 1.05rem;
    margin: 0;
}

.alert {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 20px 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.3rem;
    font-weight: 700;
}

.card-body {
    padding: 30px;
}

.form-section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #667eea;
    margin: 25px 0 15px 0;
    padding-bottom: 8px;
    border-bottom: 2px solid #e0e6ed;
}

.form-section-title:first-child {
    margin-top: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group-full {
    grid-column: 1 / -1;
}

.form-group label {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.form-group label .required {
    color: #e74c3c;
}

.form-control {
    padding: 12px 15px;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    color: #2c3e50;
    transition: all 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.form-control[readonly] {
    background: #f8f9fa;
    cursor: not-allowed;
}

select.form-control {
    cursor: pointer;
    padding: 10px 35px 10px 12px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23667eea' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 12px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
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

textarea.form-control {
    resize: vertical;
    min-height: 60px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 12px 15px;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    transition: all 0.3s;
}

.checkbox-label:hover {
    border-color: #667eea;
    background: #f8f9ff;
}

.checkbox-label input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    border: none;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(102,126,234,0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102,126,234,0.4);
}

.btn-secondary {
    background: #95a5a6;
    color: #fff;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}

.table th {
    padding: 15px 12px;
    text-align: left;
    font-weight: 700;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: background 0.2s;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.table td {
    padding: 15px 12px;
    color: #495057;
    font-size: 0.95rem;
}

.staff-id-badge {
    color: #667eea;
    font-weight: 700;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
}

.role-badge {
    display: inline-block;
    padding: 4px 10px;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: #fff;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-secondary {
    background: #e2e3e5;
    color: #6c757d;
}

.actions {
    display: flex;
    gap: 8px;
}

.btn-action {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-edit {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: #fff;
}

.btn-edit:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(240,147,251,0.4);
}

.btn-delete {
    background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
    color: #fff;
}

.btn-delete:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(235,51,73,0.4);
}

.text-center {
    text-align: center;
}

@media (max-width: 768px) {
    .staff-crud-container {
        padding: 0 15px 40px 15px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .table {
        font-size: 0.9rem;
    }
    
    .actions {
        flex-direction: column;
    }
}
</style>

<script>
// Auto-hide alert after 5 seconds
setTimeout(function() {
    var alert = document.getElementById('alertMessage');
    if (alert) {
        alert.style.animation = 'slideOut 0.3s ease';
        setTimeout(function() { alert.remove(); }, 300);
    }
}, 5000);

// Update branch name when office is selected
function updateBranchName() {
    var officeSelect = document.getElementById('office_id');
    var branchInput = document.getElementById('branch_office');
    var selectedOption = officeSelect.options[officeSelect.selectedIndex];
    
    if (selectedOption.value) {
        branchInput.value = selectedOption.getAttribute('data-name');
    } else {
        branchInput.value = '';
    }
}

// Edit staff function
function editStaff(staff) {
    console.log('Edit function called with:', staff);
    document.getElementById('formTitle').textContent = 'Edit Staff Member';
    document.getElementById('formAction').value = 'update';
    document.getElementById('staff_id').value = staff.staff_id;
    document.getElementById('staff_name').value = staff.staff_name;
    document.getElementById('staff_email').value = staff.staff_email || '';
    document.getElementById('staff_phone').value = staff.staff_phone || '';
    document.getElementById('staff_role').value = staff.staff_role || '';
    document.getElementById('office_id').value = staff.office_id || '';
    document.getElementById('branch_office').value = staff.branch_office || '';
    document.getElementById('date_of_joining').value = staff.date_of_joining || '';
    document.getElementById('address').value = staff.address || '';
    document.getElementById('emergency_contact').value = staff.emergency_contact || '';
    document.getElementById('emergency_contact_name').value = staff.emergency_contact_name || '';
    document.getElementById('salary').value = staff.salary || '';
    document.getElementById('active_status').checked = staff.active_status == 1;
    document.getElementById('submitBtnText').textContent = 'Update Staff';
    document.getElementById('cancelBtn').style.display = 'inline-flex';
    
    // Scroll to form
    document.getElementById('staffForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Delete staff function
function deleteStaff(id, name) {
    console.log('Delete function called for:', id, name);
    if (confirm('Are you sure you want to delete staff member "' + name + '"?\n\nThis action cannot be undone.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="staff_id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

// Cancel edit
document.getElementById('cancelBtn').addEventListener('click', function() {
    document.getElementById('staffForm').reset();
    document.getElementById('formTitle').textContent = 'Add New Staff Member';
    document.getElementById('formAction').value = 'add';
    document.getElementById('staff_id').value = '';
    document.getElementById('submitBtnText').textContent = 'Add Staff Member';
    document.getElementById('active_status').checked = true;
    this.style.display = 'none';
});
</script>

<style>
@keyframes slideOut {
    from { transform: translateY(0); opacity: 1; }
    to { transform: translateY(-20px); opacity: 0; }
}
</style>

</body>
</html>
