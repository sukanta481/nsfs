<?php
require 'check_auth.php';
requirePermission('role_manage');
require 'conn.php';

$error = '';
$success = '';
$role_id = isset($_GET['role_id']) ? intval($_GET['role_id']) : 0;

if ($role_id == 0) {
    header("Location: roles.php?error=Invalid role ID");
    exit;
}

// Fetch role details
$role_query = "SELECT * FROM tbl_roles WHERE role_id = $role_id";
$role_result = mysqli_query($conn, $role_query);

if (mysqli_num_rows($role_result) == 0) {
    header("Location: roles.php?error=Role not found");
    exit;
}

$role = mysqli_fetch_assoc($role_result);

// Get current role permissions
$current_perms_query = "SELECT permission_id FROM tbl_role_permissions WHERE role_id = $role_id";
$current_perms_result = mysqli_query($conn, $current_perms_query);
$current_permissions = [];
while ($cp = mysqli_fetch_assoc($current_perms_result)) {
    $current_permissions[] = $cp['permission_id'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $role_name = mysqli_real_escape_string($conn, trim($_POST['role_name']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    
    // Validation
    if (empty($role_name)) {
        $error = "Please enter a role name";
    } elseif (empty($permissions)) {
        $error = "Please select at least one permission";
    } else {
        // Check if role name already exists for other roles
        $check_role = mysqli_query($conn, "SELECT role_id FROM tbl_roles WHERE role_name='$role_name' AND role_id != $role_id");
        if (mysqli_num_rows($check_role) > 0) {
            $error = "Role name already exists. Please choose a different name.";
        } else {
            // Update role
            $update_role = "UPDATE tbl_roles SET 
                           role_name = '$role_name',
                           role_description = '$description'
                           WHERE role_id = $role_id";
            
            if (mysqli_query($conn, $update_role)) {
                // Delete existing permissions
                mysqli_query($conn, "DELETE FROM tbl_role_permissions WHERE role_id = $role_id");
                
                // Insert new permissions
                $permission_success = true;
                foreach ($permissions as $permission_id) {
                    $perm_id = intval($permission_id);
                    $insert_perm = "INSERT INTO tbl_role_permissions (role_id, permission_id) 
                                   VALUES ($role_id, $perm_id)";
                    if (!mysqli_query($conn, $insert_perm)) {
                        $permission_success = false;
                        break;
                    }
                }
                
                if ($permission_success) {
                    header("Location: roles.php?success=Role updated successfully with " . count($permissions) . " permissions");
                    exit;
                } else {
                    $error = "Role updated but error assigning some permissions";
                }
            } else {
                $error = "Error updating role: " . mysqli_error($conn);
            }
        }
    }
    
    // Refresh role data if there was an error
    if (!empty($error)) {
        $role_result = mysqli_query($conn, $role_query);
        $role = mysqli_fetch_assoc($role_result);
    }
}

// Fetch all permissions grouped by module
$permissions_query = "SELECT * FROM tbl_permissions ORDER BY module_name, permission_name";
$permissions_result = mysqli_query($conn, $permissions_query);

if (!$permissions_result) {
    die("Database Error: " . mysqli_error($conn) . "<br>Query: " . $permissions_query);
}

// Group permissions by module
$grouped_permissions = [];
while ($perm = mysqli_fetch_assoc($permissions_result)) {
    $grouped_permissions[$perm['module_name']][] = $perm;
}

require 'top_header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
.role-form-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
    margin: 20px;
}

.form-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.form-header h2 {
    margin: 0;
    font-size: 24px;
}

.form-header p {
    margin: 5px 0 0 0;
    opacity: 0.9;
    font-size: 14px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
    font-size: 14px;
}

.form-group label .required {
    color: #e74c3c;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

.permissions-section {
    margin-top: 30px;
}

.permissions-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #667eea;
}

.permissions-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 18px;
}

.permissions-header p {
    margin: 5px 0 0 0;
    color: #7f8c8d;
    font-size: 13px;
}

.module-group {
    background: #fff;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    transition: all 0.3s;
}

.module-group:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102,126,234,0.1);
}

.module-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.module-title {
    font-size: 16px;
    font-weight: 700;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.module-icon {
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}

.select-all-module {
    font-size: 13px;
    color: #667eea;
    cursor: pointer;
    font-weight: 600;
}

.select-all-module:hover {
    text-decoration: underline;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}

.permission-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.permission-item:hover {
    background: #e9ecef;
}

.permission-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.permission-item label {
    margin: 0;
    cursor: pointer;
    font-size: 14px;
    color: #495057;
    flex: 1;
}

.btn-group {
    display: flex;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

.btn {
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102,126,234,0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.selection-summary {
    background: #e7f3ff;
    padding: 15px 20px;
    border-radius: 8px;
    margin: 20px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.selection-summary strong {
    color: #0c5460;
}

.warning-box {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-size: 14px;
    color: #856404;
}
</style>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <div class="right_col" role="main">
        <div class="role-form-container">
          
          <div class="form-header">
            <h2><i class="fas fa-edit"></i> Edit Role</h2>
            <p>Update role details and permissions</p>
          </div>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-circle"></i>
              <span><?php echo $error; ?></span>
            </div>
          <?php endif; ?>

          <?php if ($role['role_name'] == 'Super Admin'): ?>
            <div class="warning-box">
              <i class="fas fa-exclamation-triangle"></i>
              <strong>Warning:</strong> You are editing the Super Admin role. Changes will affect system administrators.
            </div>
          <?php endif; ?>

          <form method="POST" action="" id="roleForm">
            
            <div class="form-group">
              <label for="role_name">
                <i class="fas fa-user-tag"></i> Role Name <span class="required">*</span>
              </label>
              <input type="text" name="role_name" id="role_name" class="form-control" 
                     placeholder="e.g., Manager, Staff, Viewer" required autocomplete="off"
                     value="<?php echo htmlspecialchars($role['role_name']); ?>">
            </div>

            <div class="form-group">
              <label for="description">
                <i class="fas fa-align-left"></i> Description
              </label>
              <textarea name="description" id="description" class="form-control" 
                        placeholder="Describe what this role can do..."><?php echo htmlspecialchars($role['role_description']); ?></textarea>
            </div>

            <div class="permissions-section">
              <div class="permissions-header">
                <h3><i class="fas fa-key"></i> Assign Permissions <span class="required">*</span></h3>
                <p>Select the permissions this role should have</p>
              </div>

              <div class="selection-summary">
                <strong><i class="fas fa-check-circle"></i> Selected: <span id="selectedCount">0</span> permissions</strong>
                <button type="button" class="btn-success" style="padding: 8px 16px; font-size: 14px;" onclick="selectAllPermissions()">
                  <i class="fas fa-check-double"></i> Select All
                </button>
              </div>

              <?php foreach ($grouped_permissions as $module => $permissions): ?>
                <div class="module-group">
                  <div class="module-header">
                    <div class="module-title">
                      <div class="module-icon">
                        <i class="fas fa-<?php 
                          echo $module == 'Dashboard' ? 'tachometer-alt' : 
                               ($module == 'Dockets' ? 'file-alt' : 
                               ($module == 'Manifest' ? 'clipboard-list' : 
                               ($module == 'Staff' ? 'users' : 
                               ($module == 'Clients' ? 'user-tie' : 
                               ($module == 'Vehicles' ? 'truck' : 
                               ($module == 'Reports' ? 'chart-bar' : 
                               ($module == 'Settings' ? 'cog' : 
                               ($module == 'User Management' ? 'users-cog' : 'shield-alt'))))))));
                        ?>"></i>
                      </div>
                      <?php echo htmlspecialchars($module); ?>
                    </div>
                    <span class="select-all-module" onclick="selectModulePermissions('<?php echo $module; ?>')">
                      <i class="fas fa-check-square"></i> Select All in Module
                    </span>
                  </div>

                  <div class="permissions-grid">
                    <?php foreach ($permissions as $perm): ?>
                      <div class="permission-item" onclick="togglePermission(<?php echo $perm['permission_id']; ?>)">
                        <input type="checkbox" 
                               name="permissions[]" 
                               value="<?php echo $perm['permission_id']; ?>" 
                               id="perm_<?php echo $perm['permission_id']; ?>"
                               data-module="<?php echo $module; ?>"
                               <?php echo in_array($perm['permission_id'], $current_permissions) ? 'checked' : ''; ?>
                               onchange="updateCount()">
                        <label for="perm_<?php echo $perm['permission_id']; ?>">
                          <?php echo htmlspecialchars($perm['permission_name']); ?>
                        </label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endforeach; ?>

            </div>

            <div class="btn-group">
              <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Role
              </button>
              <a href="roles.php" class="btn btn-secondary">
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
// Toggle permission checkbox
function togglePermission(permId) {
    const checkbox = document.getElementById('perm_' + permId);
    checkbox.checked = !checkbox.checked;
    updateCount();
}

// Update selected count
function updateCount() {
    const count = document.querySelectorAll('input[name="permissions[]"]:checked').length;
    document.getElementById('selectedCount').textContent = count;
}

// Select all permissions in a module
function selectModulePermissions(module) {
    const checkboxes = document.querySelectorAll(`input[data-module="${module}"]`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
    
    updateCount();
}

// Select all permissions
function selectAllPermissions() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
    
    updateCount();
}

// Form validation
document.getElementById('roleForm').addEventListener('submit', function(e) {
    const selectedCount = document.querySelectorAll('input[name="permissions[]"]:checked').length;
    
    if (selectedCount === 0) {
        e.preventDefault();
        alert('Please select at least one permission for this role!');
        return false;
    }
});

// Initialize count on page load
updateCount();
</script>

</body>
</html>
