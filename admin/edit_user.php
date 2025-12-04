<?php
require 'check_auth.php';
requirePermission('user_edit');
require 'conn.php';

$error = '';
$success = '';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id == 0) {
    header("Location: users.php?error=Invalid user ID");
    exit;
}

// Fetch user details
$user_query = "SELECT u.*, r.role_name 
               FROM tbl_users u 
               LEFT JOIN tbl_roles r ON u.role_id = r.role_id 
               WHERE u.user_id = $user_id";
$user_result = mysqli_query($conn, $user_query);

if (mysqli_num_rows($user_result) == 0) {
    header("Location: users.php?error=User not found");
    exit;
}

$user = mysqli_fetch_assoc($user_result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $role_id = intval($_POST['role_id']);
    $staff_id = !empty($_POST['staff_id']) ? intval($_POST['staff_id']) : NULL;
    $active_status = isset($_POST['active_status']) ? 1 : 0;
    $change_password = !empty($_POST['new_password']);
    
    // Validation
    if (empty($username) || empty($email) || empty($full_name) || empty($role_id)) {
        $error = "Please fill in all required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        // Check if username exists for other users
        $check_username = mysqli_query($conn, "SELECT user_id FROM tbl_users WHERE username='$username' AND user_id != $user_id");
        if (mysqli_num_rows($check_username) > 0) {
            $error = "Username already exists. Please choose a different username.";
        } else {
            // Check if email exists for other users
            $check_email = mysqli_query($conn, "SELECT user_id FROM tbl_users WHERE email='$email' AND user_id != $user_id");
            if (mysqli_num_rows($check_email) > 0) {
                $error = "Email already exists. Please use a different email address.";
            } else {
                // Get office assignment
                $office_id = !empty($_POST['office_id']) ? intval($_POST['office_id']) : 'NULL';
                $can_access_all = isset($_POST['can_access_all_offices']) ? 1 : 0;
                
                // Build update query
                $staff_id_value = $staff_id ? $staff_id : 'NULL';
                $office_id_value = $office_id !== 'NULL' ? $office_id : 'NULL';
                
                $update_query = "UPDATE tbl_users SET 
                                username = '$username',
                                email = '$email',
                                full_name = '$full_name',
                                role_id = $role_id,
                                staff_id = $staff_id_value,
                                office_id = $office_id_value,
                                can_access_all_offices = $can_access_all,
                                active_status = $active_status,
                                updated_at = NOW()";
                
                // Add password update if provided
                if ($change_password) {
                    $new_password = $_POST['new_password'];
                    $confirm_password = $_POST['confirm_password'];
                    
                    if (strlen($new_password) < 6) {
                        $error = "Password must be at least 6 characters long";
                    } elseif ($new_password !== $confirm_password) {
                        $error = "Passwords do not match";
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_query .= ", password = '$hashed_password'";
                    }
                }
                
                if (empty($error)) {
                    $update_query .= " WHERE user_id = $user_id";
                    
                    if (mysqli_query($conn, $update_query)) {
                        // Update status permissions
                        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_user_status_permissions'");
                        if ($table_check && mysqli_num_rows($table_check) > 0) {
                            // Clear existing status permissions
                            mysqli_query($conn, "DELETE FROM tbl_user_status_permissions WHERE user_id = $user_id");
                            
                            // Insert new status permissions if any selected
                            if (isset($_POST['status_permissions']) && is_array($_POST['status_permissions'])) {
                                foreach ($_POST['status_permissions'] as $status_id) {
                                    $status_id = intval($status_id);
                                    mysqli_query($conn, "INSERT INTO tbl_user_status_permissions (user_id, status_id, can_update) VALUES ($user_id, $status_id, 1)");
                                }
                            }
                        }
                        
                        header("Location: users.php?success=User updated successfully");
                        exit;
                    } else {
                        $error = "Error updating user: " . mysqli_error($conn);
                    }
                }
            }
        }
    }
    
    // Refresh user data if there was an error
    if (!empty($error)) {
        $user_result = mysqli_query($conn, $user_query);
        $user = mysqli_fetch_assoc($user_result);
    }
}

// Fetch roles for dropdown
$roles_query = "SELECT role_id, role_name FROM tbl_roles ORDER BY role_name";
$roles_result = mysqli_query($conn, $roles_query);

// Fetch staff for dropdown
$staff_query = "SELECT staff_id, CONCAT(first_name, ' ', last_name) as staff_name FROM tbl_staff ORDER BY first_name";
$staff_result = mysqli_query($conn, $staff_query);

// Fetch offices for dropdown
$offices_query = "SELECT office_id, office_name FROM tbl_offices ORDER BY office_name";
$offices_result = mysqli_query($conn, $offices_query);

// Fetch status hierarchy for permissions
$statuses_query = "SELECT status_id, status_name, status_order FROM tbl_status_hierarchy ORDER BY status_order";
$statuses_result = mysqli_query($conn, $statuses_query);

// Fetch user's current status permissions
$user_status_perms = [];
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_user_status_permissions'");
if ($table_check && mysqli_num_rows($table_check) > 0) {
    $status_perm_query = mysqli_query($conn, "SELECT status_id FROM tbl_user_status_permissions WHERE user_id = $user_id AND can_update = 1");
    while ($sp = mysqli_fetch_assoc($status_perm_query)) {
        $user_status_perms[] = $sp['status_id'];
    }
}

require 'top_header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
.user-form-container {
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

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
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

.form-control.with-icon {
    padding-left: 45px;
}

.input-wrapper {
    position: relative;
}

.input-wrapper i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 16px;
}

.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
    cursor: pointer;
    font-size: 18px;
    z-index: 10;
}

.toggle-password:hover {
    color: #764ba2;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox-group input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.checkbox-group label {
    margin: 0;
    cursor: pointer;
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
    transform: translateY(-2px);
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
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

.password-strength {
    margin-top: 5px;
    font-size: 12px;
}

.strength-bar {
    height: 4px;
    border-radius: 2px;
    margin-top: 5px;
    background: #e1e1e1;
    overflow: hidden;
}

.strength-bar-fill {
    height: 100%;
    transition: all 0.3s;
    width: 0%;
}

.strength-weak { background: #dc3545; width: 33%; }
.strength-medium { background: #ffc107; width: 66%; }
.strength-strong { background: #28a745; width: 100%; }

.info-box {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-size: 14px;
    color: #856404;
}

.password-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 2px dashed #dee2e6;
}

.password-section h3 {
    margin: 0 0 15px 0;
    color: #495057;
    font-size: 16px;
}
</style>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <div class="right_col" role="main">
        <div class="user-form-container">
          
          <div class="form-header">
            <h2><i class="fas fa-user-edit"></i> Edit User</h2>
            <p>Update user account details, role, and permissions</p>
          </div>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-circle"></i>
              <span><?php echo $error; ?></span>
            </div>
          <?php endif; ?>

          <?php if (!empty($success)): ?>
            <div class="alert alert-success">
              <i class="fas fa-check-circle"></i>
              <span><?php echo $success; ?></span>
            </div>
          <?php endif; ?>

          <form method="POST" action="" id="userForm">
            
            <div class="form-row">
              <div class="form-group">
                <label for="username">
                  <i class="fas fa-user"></i> Username <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <i class="fas fa-user"></i>
                  <input type="text" name="username" id="username" class="form-control with-icon" 
                         placeholder="Enter username" required autocomplete="off"
                         value="<?php echo htmlspecialchars($user['username']); ?>">
                </div>
              </div>

              <div class="form-group">
                <label for="email">
                  <i class="fas fa-envelope"></i> Email <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <i class="fas fa-envelope"></i>
                  <input type="email" name="email" id="email" class="form-control with-icon" 
                         placeholder="Enter email address" required autocomplete="off"
                         value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="full_name">
                <i class="fas fa-id-card"></i> Full Name <span class="required">*</span>
              </label>
              <div class="input-wrapper">
                <i class="fas fa-id-card"></i>
                <input type="text" name="full_name" id="full_name" class="form-control with-icon" 
                       placeholder="Enter full name" required autocomplete="off"
                       value="<?php echo htmlspecialchars($user['full_name']); ?>">
              </div>
            </div>

            <div class="password-section">
              <h3><i class="fas fa-key"></i> Change Password (Optional)</h3>
              <div class="info-box">
                <i class="fas fa-info-circle"></i>
                Leave password fields empty if you don't want to change the password.
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="new_password">
                    <i class="fas fa-lock"></i> New Password
                  </label>
                  <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="new_password" id="new_password" class="form-control with-icon" 
                           placeholder="Enter new password (optional)" minlength="6" autocomplete="new-password">
                    <span class="toggle-password" onclick="togglePassword('new_password', this)">
                      <i class="fas fa-eye"></i>
                    </span>
                  </div>
                  <div class="password-strength">
                    <div class="strength-bar">
                      <div class="strength-bar-fill" id="strengthBar"></div>
                    </div>
                    <span id="strengthText"></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="confirm_password">
                    <i class="fas fa-lock"></i> Confirm New Password
                  </label>
                  <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control with-icon" 
                           placeholder="Re-enter new password" minlength="6" autocomplete="new-password">
                    <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
                      <i class="fas fa-eye"></i>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="role_id">
                  <i class="fas fa-user-tag"></i> Role <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <i class="fas fa-user-tag"></i>
                  <select name="role_id" id="role_id" class="form-control with-icon" required>
                    <option value="">-- Select Role --</option>
                    <?php while ($role = mysqli_fetch_assoc($roles_result)): ?>
                      <option value="<?php echo $role['role_id']; ?>" 
                              <?php echo ($user['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($role['role_name']); ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label for="staff_id">
                  <i class="fas fa-users"></i> Link to Staff (Optional)
                </label>
                <div class="input-wrapper">
                  <i class="fas fa-users"></i>
                  <select name="staff_id" id="staff_id" class="form-control with-icon">
                    <option value="">-- Select Staff (Optional) --</option>
                    <?php 
                    if ($staff_result && mysqli_num_rows($staff_result) > 0):
                      while ($staff = mysqli_fetch_assoc($staff_result)): 
                    ?>
                      <option value="<?php echo $staff['staff_id']; ?>" 
                              <?php echo ($user['staff_id'] == $staff['staff_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($staff['staff_name']); ?>
                      </option>
                    <?php 
                      endwhile;
                    endif;
                    ?>
                  </select>
                </div>
              </div>
            </div>

            <!-- OFFICE ACCESS SECTION -->
            <div class="form-section" style="background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 20px 0; border-left: 4px solid #3498db;">
              <h4 style="margin-bottom: 15px; color: #2c3e50;"><i class="fas fa-building"></i> Office/Branch Access</h4>
              <p style="color: #666; font-size: 13px; margin-bottom: 15px;">Select which branch/office this user belongs to. Users can only see dockets from their assigned office.</p>
              
              <div class="form-row">
                <div class="form-group">
                  <label for="office_id">
                    <i class="fas fa-map-marker-alt"></i> Assigned Office
                  </label>
                  <div class="input-wrapper">
                    <i class="fas fa-map-marker-alt"></i>
                    <select name="office_id" id="office_id" class="form-control with-icon">
                      <option value="">-- All Offices / Head Office --</option>
                      <?php 
                      if ($offices_result && mysqli_num_rows($offices_result) > 0):
                        while ($office = mysqli_fetch_assoc($offices_result)): 
                      ?>
                        <option value="<?php echo $office['office_id']; ?>"
                                <?php echo (isset($user['office_id']) && $user['office_id'] == $office['office_id']) ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars(ucfirst($office['office_name'])); ?>
                        </option>
                      <?php 
                        endwhile;
                      endif;
                      ?>
                    </select>
                  </div>
                </div>
              </div>
              
              <div class="checkbox-group" style="margin-top: 10px;">
                <input type="checkbox" name="can_access_all_offices" id="can_access_all_offices" value="1"
                       <?php echo (isset($user['can_access_all_offices']) && $user['can_access_all_offices'] == 1) ? 'checked' : ''; ?>>
                <label for="can_access_all_offices">
                  <i class="fas fa-globe"></i> <strong>Access All Offices</strong> - Can view dockets from all branches
                </label>
              </div>
            </div>

            <!-- STATUS UPDATE PERMISSIONS SECTION -->
            <div class="form-section" style="background: #fff8e6; border-radius: 10px; padding: 20px; margin: 20px 0; border-left: 4px solid #f39c12;">
              <h4 style="margin-bottom: 15px; color: #2c3e50;"><i class="fas fa-exchange-alt"></i> Status Update Permissions</h4>
              <p style="color: #666; font-size: 13px; margin-bottom: 15px;">
                <strong>Optional:</strong> Select which statuses this user can update dockets to. 
                <br>If none selected, user can update to all statuses (based on their role permissions).
              </p>
              
              <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px;">
                <?php 
                if ($statuses_result && mysqli_num_rows($statuses_result) > 0):
                  mysqli_data_seek($statuses_result, 0);
                  while ($status = mysqli_fetch_assoc($statuses_result)): 
                    $status_icon = match($status['status_name']) {
                      'Pending' => 'fa-clock',
                      'Confirmed' => 'fa-check-circle',
                      'Picked Up' => 'fa-box',
                      'In Transit' => 'fa-truck',
                      'Out for Delivery' => 'fa-shipping-fast',
                      'Delivered' => 'fa-check-double',
                      'Delayed' => 'fa-exclamation-triangle',
                      'Failed' => 'fa-times-circle',
                      'Cancelled' => 'fa-ban',
                      default => 'fa-circle'
                    };
                    $is_checked = in_array($status['status_id'], $user_status_perms);
                ?>
                  <div class="checkbox-group" style="background: white; padding: 10px; border-radius: 5px; border: 1px solid #eee;">
                    <input type="checkbox" name="status_permissions[]" 
                           id="status_<?php echo $status['status_id']; ?>" 
                           value="<?php echo $status['status_id']; ?>"
                           <?php echo $is_checked ? 'checked' : ''; ?>>
                    <label for="status_<?php echo $status['status_id']; ?>" style="font-size: 13px;">
                      <i class="fas <?php echo $status_icon; ?>"></i>
                      <?php echo htmlspecialchars($status['status_name']); ?>
                    </label>
                  </div>
                <?php 
                  endwhile;
                endif;
                ?>
              </div>
            </div>

            <div class="form-group">
              <div class="checkbox-group">
                <input type="checkbox" name="active_status" id="active_status" value="1" 
                       <?php echo ($user['active_status'] == 1) ? 'checked' : ''; ?>>
                <label for="active_status">
                  <i class="fas fa-check-circle"></i> Active (User can login)
                </label>
              </div>
            </div>

            <div class="btn-group">
              <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update User
              </button>
              <a href="users.php" class="btn btn-secondary">
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
// Toggle password visibility
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);
    const i = icon.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        i.classList.remove('fa-eye');
        i.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        i.classList.remove('fa-eye-slash');
        i.classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    if (password.length === 0) {
        strengthBar.className = 'strength-bar-fill';
        strengthText.textContent = '';
        return;
    }
    
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    strengthBar.className = 'strength-bar-fill';
    
    if (strength <= 2) {
        strengthBar.classList.add('strength-weak');
        strengthText.textContent = 'Weak password';
        strengthText.style.color = '#dc3545';
    } else if (strength <= 4) {
        strengthBar.classList.add('strength-medium');
        strengthText.textContent = 'Medium password';
        strengthText.style.color = '#ffc107';
    } else {
        strengthBar.classList.add('strength-strong');
        strengthText.textContent = 'Strong password';
        strengthText.style.color = '#28a745';
    }
});

// Form validation
document.getElementById('userForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Only validate if user is trying to change password
    if (newPassword || confirmPassword) {
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
            return false;
        }
    }
});
</script>

</body>
</html>
