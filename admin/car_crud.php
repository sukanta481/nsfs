<?php
require 'top_header.php';

// Handle Add/Update/Delete operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add') {
            $car_number = strtoupper(mysqli_real_escape_string($conn, $_POST['car_number']));
            $car_details = strtoupper(mysqli_real_escape_string($conn, $_POST['car_details']));
            $active_status = isset($_POST['active_status']) ? 1 : 0;
            
            $query = "INSERT INTO tbl_car (car_number, car_details, active_status) 
                      VALUES ('$car_number', '$car_details', $active_status)";
            
            if (mysqli_query($conn, $query)) {
                $message = "Car added successfully!";
                $messageType = 'success';
            } else {
                $message = "Error adding car: " . mysqli_error($conn);
                $messageType = 'error';
            }
        }
        
        elseif ($action === 'update') {
            $car_id = intval($_POST['car_id']);
            $car_number = strtoupper(mysqli_real_escape_string($conn, $_POST['car_number']));
            $car_details = strtoupper(mysqli_real_escape_string($conn, $_POST['car_details']));
            $active_status = isset($_POST['active_status']) ? 1 : 0;
            
            $query = "UPDATE tbl_car SET 
                      car_number='$car_number', 
                      car_details='$car_details', 
                      active_status=$active_status 
                      WHERE car_id=$car_id";
            
            if (mysqli_query($conn, $query)) {
                $message = "Car updated successfully!";
                $messageType = 'success';
            } else {
                $message = "Error updating car: " . mysqli_error($conn);
                $messageType = 'error';
            }
        }
        
        elseif ($action === 'delete') {
            $car_id = intval($_POST['car_id']);
            $query = "DELETE FROM tbl_car WHERE car_id=$car_id";
            
            if (mysqli_query($conn, $query)) {
                $message = "Car deleted successfully!";
                $messageType = 'success';
            } else {
                $message = "Error deleting car: " . mysqli_error($conn);
                $messageType = 'error';
            }
        }
    }
}

// Fetch all cars
$cars_query = "SELECT * FROM tbl_car ORDER BY car_id DESC";
$cars_result = mysqli_query($conn, $cars_query);
?>

<body class="nav-md">
<div class="container body">
<div class="main_container">
<?php require 'left_panel.php'; ?>
<?php require 'header_banner.php'; ?>

<div class="right_col" role="main">
    <div class="car-crud-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fa fa-car"></i> Car Management</h1>
            <p>Add, edit, and manage all your fleet vehicles</p>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>" id="alertMessage">
            <i class="fa fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <!-- Add Car Form -->
        <div class="card">
            <div class="card-header">
                <i class="fa fa-plus-circle"></i>
                <span id="formTitle">Add New Car</span>
            </div>
            <div class="card-body">
                <form id="carForm" method="POST">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="car_id" id="car_id" value="">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Car Number <span class="required">*</span></label>
                            <input type="text" name="car_number" id="car_number" class="form-control" required placeholder="e.g., WB-12-AB-1234">
                        </div>
                        
                        <div class="form-group">
                            <label>Car Details <span class="required">*</span></label>
                            <input type="text" name="car_details" id="car_details" class="form-control" required placeholder="e.g., Tata Ace / Blue / 2020">
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
                            <i class="fa fa-save"></i> <span id="submitBtnText">Add Car</span>
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelBtn" style="display:none;">
                            <i class="fa fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cars List -->
        <div class="card">
            <div class="card-header">
                <i class="fa fa-list"></i>
                <span>All Cars (<?= mysqli_num_rows($cars_result) ?>)</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Car Number</th>
                                <th>Car Details</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($cars_result) > 0): ?>
                                <?php while ($car = mysqli_fetch_assoc($cars_result)): ?>
                                <tr>
                                    <td><?= $car['car_id'] ?></td>
                                    <td><strong><?= htmlspecialchars($car['car_number']) ?></strong></td>
                                    <td><?= htmlspecialchars($car['car_details']) ?></td>
                                    <td>
                                        <?php if ($car['active_status'] == 1): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn-action btn-edit" onclick='editCar(<?= json_encode($car, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <button class="btn-action btn-delete" onclick="deleteCar(<?= $car['car_id'] ?>, '<?= htmlspecialchars($car['car_number'], ENT_QUOTES) ?>')">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No cars found. Add your first car above!</td>
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

.car-crud-container {
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

.page-header h1 .fa-car {
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
    .car-crud-container {
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

// Edit car function
function editCar(car) {
    console.log('Edit function called with:', car);
    document.getElementById('formTitle').textContent = 'Edit Car';
    document.getElementById('formAction').value = 'update';
    document.getElementById('car_id').value = car.car_id;
    document.getElementById('car_number').value = car.car_number;
    document.getElementById('car_details').value = car.car_details || '';
    document.getElementById('active_status').checked = car.active_status == 1;
    document.getElementById('submitBtnText').textContent = 'Update Car';
    document.getElementById('cancelBtn').style.display = 'inline-flex';
    
    // Scroll to form
    document.getElementById('carForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Delete car function
function deleteCar(id, carNumber) {
    console.log('Delete function called for:', id, carNumber);
    if (confirm('Are you sure you want to delete car "' + carNumber + '"?\n\nThis action cannot be undone.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="car_id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

// Cancel edit
document.getElementById('cancelBtn').addEventListener('click', function() {
    document.getElementById('carForm').reset();
    document.getElementById('formTitle').textContent = 'Add New Car';
    document.getElementById('formAction').value = 'add';
    document.getElementById('car_id').value = '';
    document.getElementById('submitBtnText').textContent = 'Add Car';
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
