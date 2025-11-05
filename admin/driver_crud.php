<?php
require 'top_header.php';

// Handle Add/Update/Delete operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $driver_name = strtoupper(mysqli_real_escape_string($conn, $_POST['driver_name']));
            $alise = strtoupper(mysqli_real_escape_string($conn, $_POST['alise']));
            $driver_number = strtoupper(mysqli_real_escape_string($conn, $_POST['driver_number']));
            $driver_license = strtoupper(mysqli_real_escape_string($conn, $_POST['driver_license']));
            $active_status = isset($_POST['active_status']) ? 1 : 0;
            
            $query = "INSERT INTO tbl_driver (driver_name, alise, driver_number, driver_license, active_status) 
                      VALUES ('$driver_name', '$alise', '$driver_number', '$driver_license', $active_status)";
            
            if (mysqli_query($conn, $query)) {
                $message = "Driver added successfully!";
                $messageType = 'success';
            } else {
                $message = "Error adding driver: " . mysqli_error($conn);
                $messageType = 'error';
            }
        }
        
        elseif ($action === 'update') {
            $driver_id = intval($_POST['driver_id']);
            $driver_name = strtoupper(mysqli_real_escape_string($conn, $_POST['driver_name']));
            $alise = strtoupper(mysqli_real_escape_string($conn, $_POST['alise']));
            $driver_number = strtoupper(mysqli_real_escape_string($conn, $_POST['driver_number']));
            $driver_license = strtoupper(mysqli_real_escape_string($conn, $_POST['driver_license']));
            $active_status = isset($_POST['active_status']) ? 1 : 0;
            
            $query = "UPDATE tbl_driver SET 
                      driver_name='$driver_name', 
                      alise='$alise', 
                      driver_number='$driver_number', 
                      driver_license='$driver_license', 
                      active_status=$active_status 
                      WHERE driver_id=$driver_id";
            
            if (mysqli_query($conn, $query)) {
                $message = "Driver updated successfully!";
                $messageType = 'success';
            } else {
                $message = "Error updating driver: " . mysqli_error($conn);
                $messageType = 'error';
            }
        }
        
        elseif ($action === 'delete') {
            $driver_id = intval($_POST['driver_id']);
            $query = "DELETE FROM tbl_driver WHERE driver_id=$driver_id";
            
            if (mysqli_query($conn, $query)) {
                $message = "Driver deleted successfully!";
                $messageType = 'success';
            } else {
                $message = "Error deleting driver: " . mysqli_error($conn);
                $messageType = 'error';
            }
        }
    }
}

// Fetch all drivers
$drivers_query = "SELECT * FROM tbl_driver ORDER BY driver_id DESC";
$drivers_result = mysqli_query($conn, $drivers_query);
?>

<body class="nav-md">
<div class="container body">
<div class="main_container">
<?php require 'left_panel.php'; ?>
<?php require 'header_banner.php'; ?>

<div class="right_col" role="main">
    <div class="driver-crud-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fa fa-user-tie"></i> Driver Management</h1>
            <p>Add, edit, and manage all your drivers</p>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>" id="alertMessage">
            <i class="fa fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <!-- Add Driver Form -->
        <div class="card">
            <div class="card-header">
                <i class="fa fa-plus-circle"></i>
                <span id="formTitle">Add New Driver</span>
            </div>
            <div class="card-body">
                <form id="driverForm" method="POST">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="driver_id" id="driver_id" value="">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Driver Name <span class="required">*</span></label>
                            <input type="text" name="driver_name" id="driver_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Alias/Nickname</label>
                            <input type="text" name="alise" id="alise" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>Contact Number <span class="required">*</span></label>
                            <input type="text" name="driver_number" id="driver_number" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Driving License Number <span class="required">*</span></label>
                            <input type="text" name="driver_license" id="driver_license" class="form-control" required>
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
                            <i class="fa fa-save"></i> <span id="submitBtnText">Add Driver</span>
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelBtn" style="display:none;">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Drivers List -->
        <div class="card">
            <div class="card-header">
                <i class="fa fa-list"></i>
                <span>All Drivers (<?= mysqli_num_rows($drivers_result) ?>)</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Driver Name</th>
                                <th>Alias</th>
                                <th>Contact Number</th>
                                <th>License Number</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($drivers_result) > 0): ?>
                                <?php while ($driver = mysqli_fetch_assoc($drivers_result)): ?>
                                <tr>
                                    <td><?= $driver['driver_id'] ?></td>
                                    <td><strong><?= htmlspecialchars($driver['driver_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($driver['alise']) ?></td>
                                    <td><?= htmlspecialchars($driver['driver_number']) ?></td>
                                    <td><?= htmlspecialchars($driver['driver_license'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($driver['active_status'] == 1): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <button class="btn-action btn-edit" 
                                                onclick="editDriver({
                                                    driver_id: '<?= $driver['driver_id'] ?>',
                                                    driver_name: '<?= addslashes(htmlspecialchars($driver['driver_name'])) ?>',
                                                    alise: '<?= addslashes(htmlspecialchars($driver['alise'] ?? '')) ?>',
                                                    driver_number: '<?= addslashes(htmlspecialchars($driver['driver_number'] ?? '')) ?>',
                                                    driver_license: '<?= addslashes(htmlspecialchars($driver['driver_license'] ?? '')) ?>',
                                                    active_status: '<?= $driver['active_status'] ?>'
                                                }); return false;">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <button class="btn-action btn-delete" 
                                                onclick="deleteDriver('<?= $driver['driver_id'] ?>', '<?= addslashes(htmlspecialchars($driver['driver_name'])) ?>'); return false;">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No drivers found. Add your first driver above!</td>
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

.driver-crud-container {
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
    .driver-crud-container {
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

// Edit driver function
function editDriver(driver) {
    console.log('Edit function called with:', driver);
    document.getElementById('formTitle').textContent = 'Edit Driver';
    document.getElementById('formAction').value = 'update';
    document.getElementById('driver_id').value = driver.driver_id;
    document.getElementById('driver_name').value = driver.driver_name;
    document.getElementById('alise').value = driver.alise || '';
    document.getElementById('driver_number').value = driver.driver_number || '';
    document.getElementById('driver_license').value = driver.driver_license || '';
    document.getElementById('active_status').checked = driver.active_status == 1;
    document.getElementById('submitBtnText').textContent = 'Update Driver';
    document.getElementById('cancelBtn').style.display = 'inline-flex';
    
    // Scroll to form
    document.getElementById('driverForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Delete driver function
function deleteDriver(id, name) {
    console.log('Delete function called for:', id, name);
    if (confirm('Are you sure you want to delete driver "' + name + '"?\n\nThis action cannot be undone.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="driver_id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

// Cancel edit
document.getElementById('cancelBtn').addEventListener('click', function() {
    document.getElementById('driverForm').reset();
    document.getElementById('formTitle').textContent = 'Add New Driver';
    document.getElementById('formAction').value = 'add';
    document.getElementById('driver_id').value = '';
    document.getElementById('submitBtnText').textContent = 'Add Driver';
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
