<?php
require 'check_auth.php';
requirePermission('user_create');
require 'conn.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $role_id = intval($_POST['role_id']);
    $staff_id = !empty($_POST['staff_id']) ? intval($_POST['staff_id']) : NULL;
    $active_status = isset($_POST['active_status']) ? 1 : 0;
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($role_id)) {
        $error = "Please fill in all required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if username already exists
        $check_username = mysqli_query($conn, "SELECT user_id FROM tbl_users WHERE username='$username'");
        if (mysqli_num_rows($check_username) > 0) {
            $error = "Username already exists. Please choose a different username.";
        } else {
            // Check if email already exists
            $check_email = mysqli_query($conn, "SELECT user_id FROM tbl_users WHERE email='$email'");
            if (mysqli_num_rows($check_email) > 0) {
                $error = "Email already exists. Please use a different email address.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $staff_id_value = $staff_id ? $staff_id : 'NULL';
                $insert_query = "INSERT INTO tbl_users 
                                (username, email, password, full_name, role_id, staff_id, active_status, created_at) 
                                VALUES 
                                ('$username', '$email', '$hashed_password', '$full_name', $role_id, $staff_id_value, $active_status, NOW())";
                
                if (mysqli_query($conn, $insert_query)) {
                    $success = "User created successfully!";
                    // Redirect to users list page after success
                    header("Location: users.php?success=User created successfully");
                    exit;
                } else {
                    $error = "Error creating user: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Fetch roles for dropdown
$roles_query = "SELECT role_id, role_name FROM tbl_roles ORDER BY role_name";
$roles_result = mysqli_query($conn, $roles_query);

// Fetch staff for dropdown
$staff_query = "SELECT staff_id, CONCAT(first_name, ' ', last_name) as staff_name FROM tbl_staff ORDER BY first_name";
$staff_result = mysqli_query($conn, $staff_query);

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
    background: #e7f3ff;
    border-left: 4px solid #2196F3;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-size: 14px;
    color: #0c5460;
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
            <h2><i class="fas fa-user-plus"></i> Add New User</h2>
            <p>Create a new user account with role and permissions</p>
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

          <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> Password must be at least 6 characters long. User will be able to change their password after first login.
          </div>

          <form method="POST" action="" id="userForm">
            
            <div class="form-row">
              <div class="form-group">
                <label for="username">
                  <i class="fas fa-user"></i> Username <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <i class="fas fa-user"></i>
                  <input type="text" name="username" id="username" class="form-control with-icon" 
                         placeholder="Enter username" required 
                         value="<?php echo (!empty($error) && isset($_POST['username'])) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
              </div>

              <div class="form-group">
                <label for="email">
                  <i class="fas fa-envelope"></i> Email <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <i class="fas fa-envelope"></i>
                  <input type="email" name="email" id="email" class="form-control with-icon" 
                         placeholder="Enter email address" required 
                         value="<?php echo (!empty($error) && isset($_POST['email'])) ? htmlspecialchars($_POST['email']) : ''; ?>">
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
                       placeholder="Enter full name" required 
                       value="<?php echo (!empty($error) && isset($_POST['full_name'])) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="password">
                  <i class="fas fa-lock"></i> Password <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <i class="fas fa-lock"></i>
                  <input type="password" name="password" id="password" class="form-control with-icon" 
                         placeholder="Enter password" required minlength="6">
                  <span class="toggle-password" onclick="togglePassword('password', this)">
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
                  <i class="fas fa-lock"></i> Confirm Password <span class="required">*</span>
                </label>
                <div class="input-wrapper">
                  <i class="fas fa-lock"></i>
                  <input type="password" name="confirm_password" id="confirm_password" class="form-control with-icon" 
                         placeholder="Re-enter password" required minlength="6">
                  <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
                    <i class="fas fa-eye"></i>
                  </span>
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
                              <?php echo (!empty($error) && isset($_POST['role_id']) && $_POST['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
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
                              <?php echo (!empty($error) && isset($_POST['staff_id']) && $_POST['staff_id'] == $staff['staff_id']) ? 'selected' : ''; ?>>
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

            <div class="form-group">
              <div class="checkbox-group">
                <input type="checkbox" name="active_status" id="active_status" value="1" 
                       <?php echo (empty($error) || (isset($_POST['active_status']) && $_POST['active_status'])) ? 'checked' : ''; ?>>
                <label for="active_status">
                  <i class="fas fa-check-circle"></i> Active (User can login)
                </label>
              </div>
            </div>

            <div class="btn-group">
              <button type="submit" name="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create User
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
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
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
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
    }
});
</script>

</body>
</html>
