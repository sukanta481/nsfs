<?php
require 'check_auth.php';
requirePermission('staff_view');
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
            $driving_license = ($staff_role === 'Driver') ? strtoupper(mysqli_real_escape_string($conn, $_POST['driving_license'])) : 'N/A';
            $branch_office = mysqli_real_escape_string($conn, $_POST['branch_office']);
            $date_of_joining = mysqli_real_escape_string($conn, $_POST['date_of_joining']);
            $address = mysqli_real_escape_string($conn, $_POST['address']);
            $emergency_contact = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
            $emergency_contact_name = strtoupper(mysqli_real_escape_string($conn, $_POST['emergency_contact_name']));
            $salary = mysqli_real_escape_string($conn, $_POST['salary']);
            $active_status = isset($_POST['active_status']) ? 1 : 0;
            
            // Validate driving license for drivers
            if ($staff_role === 'Driver' && empty($driving_license)) {
                $message = "Driving license is required for Driver role!";
                $messageType = 'error';
            } else {
                // Generate unique staff ID
                $staff_unique_id = generateStaffId($conn, $office_id);
                
                $query = "INSERT INTO tbl_staff (
                            staff_unique_id, staff_name, staff_email, staff_phone, staff_role, driving_license,
                            office_id, branch_office, date_of_joining, address, 
                            emergency_contact, emergency_contact_name, salary, active_status
                          ) VALUES (
                            '$staff_unique_id', '$staff_name', '$staff_email', '$staff_phone', '$staff_role', '$driving_license',
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
        }
        
        elseif ($action === 'update') {
            $staff_id = intval($_POST['staff_id']);
            $staff_name = strtoupper(mysqli_real_escape_string($conn, $_POST['staff_name']));
            $staff_email = mysqli_real_escape_string($conn, $_POST['staff_email']);
            $staff_phone = mysqli_real_escape_string($conn, $_POST['staff_phone']);
            $staff_role = mysqli_real_escape_string($conn, $_POST['staff_role']);
            $driving_license = ($staff_role === 'Driver') ? strtoupper(mysqli_real_escape_string($conn, $_POST['driving_license'])) : 'N/A';
            $office_id = intval($_POST['office_id']);
            $branch_office = mysqli_real_escape_string($conn, $_POST['branch_office']);
            $date_of_joining = mysqli_real_escape_string($conn, $_POST['date_of_joining']);
            $address = mysqli_real_escape_string($conn, $_POST['address']);
            $emergency_contact = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
            $emergency_contact_name = strtoupper(mysqli_real_escape_string($conn, $_POST['emergency_contact_name']));
            $salary = mysqli_real_escape_string($conn, $_POST['salary']);
            $active_status = isset($_POST['active_status']) ? 1 : 0;
            
            // Validate driving license for drivers
            if ($staff_role === 'Driver' && empty($driving_license)) {
                $message = "Driving license is required for Driver role!";
                $messageType = 'error';
            } else {
                $query = "UPDATE tbl_staff SET 
                          staff_name='$staff_name', 
                          staff_email='$staff_email', 
                          staff_phone='$staff_phone', 
                          staff_role='$staff_role',
                          driving_license='$driving_license',
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
                            <select name="staff_role" id="staff_role" class="form-control" required onchange="toggleLicenseField()">
                                <option value="">-- Select Role --</option>
                                <?php foreach ($staff_roles as $role): ?>
                                    <option value="<?= $role ?>"><?= $role ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group" id="license_field" style="display:none;">
                            <label>Driving License <span class="required">*</span></label>
                            <input type="text" name="driving_license" id="driving_license" class="form-control" placeholder="Enter license number">
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
                                    <td>
                                        <?php if ($staff['active_status'] == 1): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn-action btn-view" onclick='viewStaff(<?= json_encode($staff, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <button class="btn-action btn-edit" onclick='editStaff(<?= json_encode($staff, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" onclick="deleteStaff(<?= $staff['staff_id'] ?>, '<?= htmlspecialchars($staff['staff_name'], ENT_QUOTES) ?>')" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No staff members found. Add your first staff member above!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Staff Details Modal -->
<div id="viewStaffModal" class="modal-overlay" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2><i class="fa fa-user-circle"></i> Staff Details</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="detail-grid">
                <div class="detail-section">
                    <h3><i class="fa fa-info-circle"></i> Basic Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Staff ID:</span>
                        <span class="detail-value" id="view_staff_id"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Full Name:</span>
                        <span class="detail-value" id="view_name"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value" id="view_email"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value" id="view_phone"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Role:</span>
                        <span class="detail-value" id="view_role"></span>
                    </div>
                    <div class="detail-row" id="view_license_row" style="display:none;">
                        <span class="detail-label">Driving License:</span>
                        <span class="detail-value" id="view_license"></span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3><i class="fa fa-building"></i> Office & Employment</h3>
                    <div class="detail-row">
                        <span class="detail-label">Branch Office:</span>
                        <span class="detail-value" id="view_office"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date of Joining:</span>
                        <span class="detail-value" id="view_joining_date"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Monthly Salary:</span>
                        <span class="detail-value" id="view_salary"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value" id="view_status"></span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3><i class="fa fa-map-marker"></i> Contact Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value" id="view_address"></span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3><i class="fa fa-phone"></i> Emergency Contact</h3>
                    <div class="detail-row">
                        <span class="detail-label">Contact Name:</span>
                        <span class="detail-value" id="view_emergency_name"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Contact Number:</span>
                        <span class="detail-value" id="view_emergency_phone"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">
                <i class="fa fa-times"></i> Close
            </button>
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
    padding: 8px 12px;
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

.btn-view {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: #fff;
}

.btn-view:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(79,172,254,0.4);
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

/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    background: #fff;
    border-radius: 16px;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    animation: slideUp 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 25px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 16px 16px 0 0;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-close {
    background: rgba(255,255,255,0.2);
    border: none;
    color: #fff;
    font-size: 2rem;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    line-height: 1;
}

.modal-close:hover {
    background: rgba(255,255,255,0.3);
    transform: rotate(90deg);
}

.modal-body {
    padding: 30px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
}

.detail-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    border: 2px solid #e0e6ed;
}

.detail-section h3 {
    color: #667eea;
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #e0e6ed;
    display: flex;
    align-items: center;
    gap: 10px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #e0e6ed;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #495057;
    flex: 0 0 40%;
}

.detail-value {
    color: #2c3e50;
    font-weight: 500;
    flex: 1;
    text-align: right;
}

.modal-footer {
    padding: 20px 30px;
    background: #f8f9fa;
    border-top: 2px solid #e0e6ed;
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    border-radius: 0 0 16px 16px;
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
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        width: 95%;
        max-height: 95vh;
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

// Toggle driving license field based on role
function toggleLicenseField() {
    var roleSelect = document.getElementById('staff_role');
    var licenseField = document.getElementById('license_field');
    var licenseInput = document.getElementById('driving_license');
    
    if (roleSelect.value === 'Driver') {
        licenseField.style.display = 'flex';
        licenseInput.required = true;
    } else {
        licenseField.style.display = 'none';
        licenseInput.required = false;
        licenseInput.value = '';
    }
}

// View staff details
function viewStaff(staff) {
    console.log('View staff:', staff);
    
    // Populate modal with staff details
    document.getElementById('view_staff_id').textContent = staff.staff_unique_id || 'N/A';
    document.getElementById('view_name').textContent = staff.staff_name || 'N/A';
    document.getElementById('view_email').textContent = staff.staff_email || 'N/A';
    document.getElementById('view_phone').textContent = staff.staff_phone || 'N/A';
    document.getElementById('view_role').innerHTML = '<span class="role-badge">' + (staff.staff_role || 'N/A') + '</span>';
    
    // Show/hide license field based on role
    if (staff.staff_role === 'Driver' && staff.driving_license && staff.driving_license !== 'N/A') {
        document.getElementById('view_license_row').style.display = 'flex';
        document.getElementById('view_license').textContent = staff.driving_license;
    } else {
        document.getElementById('view_license_row').style.display = 'none';
    }
    
    document.getElementById('view_office').textContent = staff.branch_office || 'N/A';
    document.getElementById('view_joining_date').textContent = staff.date_of_joining || 'N/A';
    document.getElementById('view_salary').textContent = staff.salary ? '₹' + parseFloat(staff.salary).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'N/A';
    document.getElementById('view_status').innerHTML = staff.active_status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>';
    document.getElementById('view_address').textContent = staff.address || 'N/A';
    document.getElementById('view_emergency_name').textContent = staff.emergency_contact_name || 'N/A';
    document.getElementById('view_emergency_phone').textContent = staff.emergency_contact || 'N/A';
    
    // Show modal
    document.getElementById('viewStaffModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Close modal
function closeModal(event) {
    if (!event || event.target.classList.contains('modal-overlay') || event.target.classList.contains('modal-close') || event.target.closest('.modal-close')) {
        document.getElementById('viewStaffModal').classList.remove('active');
        document.body.style.overflow = 'auto';
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
    
    // Toggle license field and set value
    toggleLicenseField();
    if (staff.staff_role === 'Driver') {
        document.getElementById('driving_license').value = staff.driving_license || '';
    }
    
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
    document.getElementById('license_field').style.display = 'none';
    document.getElementById('driving_license').required = false;
    this.style.display = 'none';
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal(event);
    }
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
