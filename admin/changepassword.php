<?php
require 'check_auth.php';
require 'top_header.php';

// Handle both old and new session formats
if (is_array($_SESSION['admin'])) {
    // Old login system - admin is an array
    $admin_id = $_SESSION['admin']['id'];
} else {
    // New login system - admin is a string, use user_id or admin_id
    $admin_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? '';
}

$get_admin_email_sql="select * from tbl_administrator where id='".$admin_id."'";
$get_admin_email_rs=mysqli_query($conn,$get_admin_email_sql);
$get_admin_email_row=mysqli_fetch_array($get_admin_email_rs);
?>
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>

      <div class="right_col" role="main" style="background: #f8fafc; min-height:100vh;">
        <div class="x_panel">
          <div class="x_title">
            <h2><i class="fa fa-lock"></i> Change Password</h2>
            <div class="clearfix"></div>
          </div>

          <div class="x_content">
            <?php if(isset($passmessage) && !empty($passmessage)): ?>
              <div class="alert-message">
                <?php echo $passmessage; ?>
              </div>
            <?php endif;?>

            <form id="changepassword" name="changepassword" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="modern-form" novalidate autocomplete="off">
              <?php
              // Include CSRF helper if not already included
              if (!function_exists('csrf_token_field')) {
                require_once 'includes/csrf_helper.php';
              }
              echo csrf_token_field('change_password');
              ?>

              <div class="form-row">
                <div class="form-group">
                  <label for="admin_email">
                    <i class="fa fa-envelope"></i> Email Address <span class="required">*</span>
                  </label>
                  <input type="email"
                         name="admin_email"
                         id="admin_email"
                         value="<?php echo htmlspecialchars($get_admin_email_row['admin_email'] ?? ''); ?>"
                         class="form-control"
                         required
                         placeholder="Enter your email address" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="old_password">
                    <i class="fa fa-key"></i> Current Password <span class="required">*</span>
                  </label>
                  <div class="password-input-wrapper">
                    <input type="password"
                           name="old_password"
                           id="old_password"
                           class="form-control"
                           required
                           placeholder="Enter your current password" />
                    <span class="toggle-password" onclick="togglePassword('old_password')">
                      <i class="fa fa-eye"></i>
                    </span>
                  </div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="new_password1">
                    <i class="fa fa-lock"></i> New Password <span class="required">*</span>
                  </label>
                  <div class="password-input-wrapper">
                    <input type="password"
                           name="new_password1"
                           id="new_password1"
                           class="form-control"
                           required
                           placeholder="Enter new password (min 6 characters)"
                           onkeyup="checkPasswordStrength()" />
                    <span class="toggle-password" onclick="togglePassword('new_password1')">
                      <i class="fa fa-eye"></i>
                    </span>
                  </div>
                  <div id="password-strength" class="password-strength"></div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="new_password2">
                    <i class="fa fa-check-circle"></i> Confirm New Password <span class="required">*</span>
                  </label>
                  <div class="password-input-wrapper">
                    <input type="password"
                           name="new_password2"
                           id="new_password2"
                           class="form-control"
                           required
                           placeholder="Re-enter new password"
                           onkeyup="checkPasswordMatch()" />
                    <span class="toggle-password" onclick="togglePassword('new_password2')">
                      <i class="fa fa-eye"></i>
                    </span>
                  </div>
                  <div id="password-match" class="password-match"></div>
                </div>
              </div>

              <div class="info-box">
                <i class="fa fa-info-circle"></i>
                <div>
                  <strong>Password Requirements:</strong>
                  <ul>
                    <li>Minimum 6 characters</li>
                    <li>Mix of letters and numbers recommended</li>
                    <li>Avoid using common words or personal information</li>
                  </ul>
                </div>
              </div>

              <div class="form-actions">
                <input type="hidden" name="pwdsubmit" value="Submit">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                  <i class="fa fa-save"></i> Update Password
                </button>
                <a href="index.php" class="btn btn-secondary">
                  <i class="fa fa-times"></i> Cancel
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>

      <?php require 'footer.php';?>
    </div>
  </div>
</body>

<style>
/* Modern Form Styling */
.x_panel {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 8px 32px 0 rgba(78,110,255,0.07);
  padding: 30px;
  max-width: 800px;
  margin: 20px auto;
}

.x_title {
  border-bottom: 2px solid #e6e9ed;
  margin-bottom: 30px;
  padding-bottom: 15px;
}

.x_title h2 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 800;
  letter-spacing: -.5px;
  color: #222;
}

.x_content {
  padding: 0;
}

.modern-form {
  max-width: 600px;
}

.form-row {
  margin-bottom: 25px;
}

.form-group {
  width: 100%;
}

.form-group label {
  display: block;
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
}

.form-group label i {
  color: #667eea;
  margin-right: 5px;
}

.required {
  color: #dc3545;
  font-weight: bold;
}

.form-control {
  width: 100%;
  padding: 12px 15px;
  font-size: 1.05rem;
  border: 2px solid #e1e8ed;
  border-radius: 10px;
  transition: all 0.3s;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.password-input-wrapper {
  position: relative;
}

.password-input-wrapper .form-control {
  padding-right: 45px;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #667eea;
  padding: 5px 10px;
  border-radius: 5px;
  transition: all 0.2s;
  z-index: 10;
}

.toggle-password:hover {
  background: rgba(102,126,234,0.1);
  color: #764ba2;
}

.password-strength {
  margin-top: 8px;
  font-size: 0.9rem;
  font-weight: 600;
}

.password-strength.weak {
  color: #dc3545;
}

.password-strength.medium {
  color: #ffc107;
}

.password-strength.strong {
  color: #28a745;
}

.password-match {
  margin-top: 8px;
  font-size: 0.9rem;
  font-weight: 600;
}

.password-match.match {
  color: #28a745;
}

.password-match.no-match {
  color: #dc3545;
}

.info-box {
  background: #e3f2fd;
  border-left: 4px solid #2196f3;
  padding: 15px 20px;
  margin: 25px 0;
  border-radius: 8px;
  display: flex;
  gap: 15px;
  align-items: flex-start;
}

.info-box i {
  font-size: 1.5rem;
  color: #2196f3;
  margin-top: 3px;
}

.info-box strong {
  color: #1565c0;
}

.info-box ul {
  margin: 8px 0 0 0;
  padding-left: 20px;
  color: #555;
}

.info-box li {
  margin-bottom: 5px;
}

.alert-message {
  margin-bottom: 25px;
}

.form-actions {
  display: flex;
  gap: 15px;
  margin-top: 30px;
  padding-top: 25px;
  border-top: 1px solid #e6e9ed;
}

.btn {
  padding: 12px 30px;
  font-size: 1.05rem;
  font-weight: 600;
  border: none;
  border-radius: 10px;
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

/* Loading state */
.btn-primary.loading {
  background: #999;
  cursor: not-allowed;
  pointer-events: none;
}

.btn-primary.loading::before {
  content: '';
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid #fff;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-right: 8px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
  .right_col {
    padding: 15px 10px !important;
  }

  .x_panel {
    padding: 20px 15px;
    margin: 10px;
    border-radius: 12px;
  }

  .x_title h2 {
    font-size: 1.4rem;
  }

  .modern-form {
    max-width: 100%;
  }

  .form-group label {
    font-size: 1rem;
  }

  .form-control {
    font-size: 1rem;
    padding: 10px 12px;
  }

  .password-input-wrapper .form-control {
    padding-right: 40px;
  }

  .btn {
    font-size: 1rem;
    padding: 10px 20px;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions .btn {
    width: 100%;
    justify-content: center;
  }

  .info-box {
    flex-direction: column;
    padding: 12px 15px;
  }
}

@media (max-width: 576px) {
  .x_panel {
    padding: 15px 10px;
    margin: 5px;
  }

  .x_title h2 {
    font-size: 1.2rem;
  }

  .form-group label {
    font-size: 0.95rem;
  }

  .form-control {
    font-size: 0.95rem;
    padding: 10px;
  }

  .btn {
    font-size: 0.95rem;
    padding: 10px 15px;
  }

  .info-box {
    font-size: 0.9rem;
  }

  .info-box i {
    font-size: 1.2rem;
  }
}

@media (max-width: 400px) {
  .x_panel {
    padding: 12px 8px;
  }

  .x_title h2 {
    font-size: 1.1rem;
  }

  .form-group label {
    font-size: 0.9rem;
  }

  .form-control {
    font-size: 0.9rem;
  }
}
</style>

<script>
// Toggle password visibility
function togglePassword(fieldId) {
  const field = document.getElementById(fieldId);
  const icon = event.currentTarget.querySelector('i');

  if (field.type === 'password') {
    field.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    field.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
}

// Check password strength
function checkPasswordStrength() {
  const password = document.getElementById('new_password1').value;
  const strengthDiv = document.getElementById('password-strength');

  if (password.length === 0) {
    strengthDiv.textContent = '';
    strengthDiv.className = 'password-strength';
    return;
  }

  let strength = 0;

  // Length
  if (password.length >= 6) strength++;
  if (password.length >= 10) strength++;

  // Contains number
  if (/\d/.test(password)) strength++;

  // Contains lowercase and uppercase
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;

  // Contains special character
  if (/[^A-Za-z0-9]/.test(password)) strength++;

  if (strength <= 2) {
    strengthDiv.textContent = '⚠️ Weak password';
    strengthDiv.className = 'password-strength weak';
  } else if (strength <= 3) {
    strengthDiv.textContent = '✓ Medium strength password';
    strengthDiv.className = 'password-strength medium';
  } else {
    strengthDiv.textContent = '✓ Strong password';
    strengthDiv.className = 'password-strength strong';
  }

  checkPasswordMatch();
}

// Check if passwords match
function checkPasswordMatch() {
  const password1 = document.getElementById('new_password1').value;
  const password2 = document.getElementById('new_password2').value;
  const matchDiv = document.getElementById('password-match');

  if (password2.length === 0) {
    matchDiv.textContent = '';
    matchDiv.className = 'password-match';
    return;
  }

  if (password1 === password2) {
    matchDiv.textContent = '✓ Passwords match';
    matchDiv.className = 'password-match match';
  } else {
    matchDiv.textContent = '✗ Passwords do not match';
    matchDiv.className = 'password-match no-match';
  }
}

// Form validation and loading state
document.getElementById('changepassword').addEventListener('submit', function(e) {
  const password1 = document.getElementById('new_password1').value;
  const password2 = document.getElementById('new_password2').value;
  const oldPassword = document.getElementById('old_password').value;
  const submitBtn = document.getElementById('submitBtn');

  // Basic validation
  if (!oldPassword || !password1 || !password2) {
    e.preventDefault();
    alert('Please fill in all required fields');
    return false;
  }

  if (password1.length < 6) {
    e.preventDefault();
    alert('New password must be at least 6 characters long');
    return false;
  }

  if (password1 !== password2) {
    e.preventDefault();
    alert('New passwords do not match');
    return false;
  }

  // Add loading state
  submitBtn.classList.add('loading');
  submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating...';
  submitBtn.disabled = true;

  return true;
});

// Auto-fade success messages
document.addEventListener('DOMContentLoaded', function() {
  const alertMessage = document.querySelector('.alert-message');
  if (alertMessage && alertMessage.textContent.includes('success')) {
    setTimeout(function() {
      alertMessage.style.opacity = '0';
      alertMessage.style.transition = 'opacity 0.5s';
      setTimeout(function() {
        alertMessage.style.display = 'none';
      }, 500);
    }, 3000);
  }
});
</script>
