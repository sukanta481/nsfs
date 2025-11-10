<?php
/**
 * Contact Settings - Modern UI/UX
 * Manage company contact information
 */

require 'check_auth.php';
require 'top_header.php';

// Handle form submission
$message = '';
$message_type = '';

if (isset($_POST['update_contact'])) {
    $contact_phone = mysqli_real_escape_string($conn, trim($_POST['contact_phone']));
    $contact_phone_inner = mysqli_real_escape_string($conn, trim($_POST['contact_phone_inner']));
    $contact_phone2 = mysqli_real_escape_string($conn, trim($_POST['contact_phone2']));
    $contact_email = mysqli_real_escape_string($conn, trim($_POST['contact_email']));
    $contact_email2 = mysqli_real_escape_string($conn, trim($_POST['contact_email2']));
    $contact_address = mysqli_real_escape_string($conn, trim($_POST['contact_address']));

    $update_sql = "UPDATE tbl_contact SET
        contact_phone = '$contact_phone',
        contact_phone_inner = '$contact_phone_inner',
        contact_phone2 = '$contact_phone2',
        contact_email = '$contact_email',
        contact_email2 = '$contact_email2',
        contact_address = '$contact_address'
        WHERE contact_id = 1";

    if (mysqli_query($conn, $update_sql)) {
        $message = 'Contact settings updated successfully';
        $message_type = 'success';
    } else {
        $message = 'Error updating contact settings';
        $message_type = 'error';
    }
}

// Fetch current settings
$sql = 'SELECT * FROM tbl_contact WHERE contact_id = 1';
$result = mysqli_query($conn, $sql);
$contact = mysqli_fetch_assoc($result);
?>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>

      <div class="right_col" role="main" style="background: #f8fafc; min-height:100vh;">

        <!-- Header Section -->
        <div style="padding: 20px 30px; background: white; margin-bottom: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
          <h2 style="margin: 0; font-size: 1.8rem; font-weight: 800; color: #1a1a1a;">
            <i class="fa fa-address-book" style="color: #667eea; margin-right: 10px;"></i>
            Contact Settings
          </h2>
          <p style="margin: 8px 0 0 0; color: #7f8c8d; font-size: 1rem;">
            Manage company contact information displayed on invoices and website
          </p>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?>" style="margin: 0 30px 25px 30px; padding: 15px 20px; border-radius: 10px; font-size: 1.05rem; animation: slideInDown 0.3s;">
          <i class="fa fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
          <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Form Panel -->
        <div style="padding: 0 30px 30px 30px;">
          <div class="x_panel" style="border-radius: 16px; background: white; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 1200px; margin: 0 auto;">

            <form method="post" action="" id="contactForm" autocomplete="off">

              <!-- Phone Numbers Section -->
              <div style="margin-bottom: 40px;">
                <h3 style="font-size: 1.3rem; font-weight: 700; color: #2c3e50; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e6e9ed;">
                  <i class="fa fa-phone" style="color: #27ae60;"></i> Phone Numbers
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">

                  <!-- Main Phone -->
                  <div class="form-group">
                    <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                      <i class="fa fa-phone-square" style="color: #667eea;"></i>
                      Main Phone Number <span style="color: #e74c3c;">*</span>
                    </label>
                    <textarea name="contact_phone"
                              class="form-control"
                              rows="3"
                              required
                              style="padding: 12px 15px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s; resize: vertical;"
                              placeholder="Enter main phone number(s)"><?php echo htmlspecialchars($contact['contact_phone']); ?></textarea>
                    <small style="color: #7f8c8d; font-size: 0.9rem;">Displayed on website header</small>
                  </div>

                  <!-- Inner Phone -->
                  <div class="form-group">
                    <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                      <i class="fa fa-phone" style="color: #667eea;"></i>
                      Inner Phone Number <span style="color: #e74c3c;">*</span>
                    </label>
                    <textarea name="contact_phone_inner"
                              class="form-control"
                              rows="3"
                              required
                              style="padding: 12px 15px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s; resize: vertical;"
                              placeholder="Enter inner phone number(s)"><?php echo htmlspecialchars($contact['contact_phone_inner']); ?></textarea>
                    <small style="color: #7f8c8d; font-size: 0.9rem;">Internal contact numbers</small>
                  </div>

                  <!-- Invoice Mobile -->
                  <div class="form-group">
                    <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                      <i class="fa fa-mobile" style="color: #667eea;"></i>
                      Invoice Mobile Numbers
                    </label>
                    <textarea name="contact_phone2"
                              class="form-control"
                              rows="3"
                              style="padding: 12px 15px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s; resize: vertical;"
                              placeholder="Enter mobile numbers for invoices"><?php echo htmlspecialchars($contact['contact_phone2']); ?></textarea>
                    <small style="color: #7f8c8d; font-size: 0.9rem;">Displayed on customer invoices</small>
                  </div>

                </div>
              </div>

              <!-- Email Addresses Section -->
              <div style="margin-bottom: 40px;">
                <h3 style="font-size: 1.3rem; font-weight: 700; color: #2c3e50; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e6e9ed;">
                  <i class="fa fa-envelope" style="color: #3498db;"></i> Email Addresses
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">

                  <!-- Main Email -->
                  <div class="form-group">
                    <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                      <i class="fa fa-envelope-o" style="color: #667eea;"></i>
                      Main Email <span style="color: #e74c3c;">*</span>
                    </label>
                    <textarea name="contact_email"
                              class="form-control"
                              rows="3"
                              required
                              style="padding: 12px 15px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s; resize: vertical;"
                              placeholder="Enter main email address(es)"><?php echo htmlspecialchars($contact['contact_email']); ?></textarea>
                    <small style="color: #7f8c8d; font-size: 0.9rem;">Primary contact email</small>
                  </div>

                  <!-- Invoice Email -->
                  <div class="form-group">
                    <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                      <i class="fa fa-file-text-o" style="color: #667eea;"></i>
                      Invoice Email <span style="color: #e74c3c;">*</span>
                    </label>
                    <textarea name="contact_email2"
                              class="form-control"
                              rows="3"
                              required
                              style="padding: 12px 15px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s; resize: vertical;"
                              placeholder="Enter invoice email address(es)"><?php echo htmlspecialchars($contact['contact_email2']); ?></textarea>
                    <small style="color: #7f8c8d; font-size: 0.9rem;">Displayed on customer invoices</small>
                  </div>

                </div>
              </div>

              <!-- Address Section -->
              <div style="margin-bottom: 40px;">
                <h3 style="font-size: 1.3rem; font-weight: 700; color: #2c3e50; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #e6e9ed;">
                  <i class="fa fa-map-marker" style="color: #e74c3c;"></i> Address
                </h3>

                <div class="form-group">
                  <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                    <i class="fa fa-building" style="color: #667eea;"></i>
                    Company Address
                  </label>
                  <textarea name="contact_address"
                            class="form-control"
                            rows="4"
                            style="padding: 12px 15px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s; resize: vertical;"
                            placeholder="Enter complete company address"><?php echo htmlspecialchars($contact['contact_address']); ?></textarea>
                  <small style="color: #7f8c8d; font-size: 0.9rem;">Full address including street, city, state, and postal code</small>
                </div>
              </div>

              <!-- Action Buttons -->
              <div style="display: flex; gap: 15px; justify-content: center; margin-top: 35px; padding-top: 30px; border-top: 2px solid #e6e9ed;">
                <button type="submit"
                        name="update_contact"
                        class="btn btn-success"
                        style="padding: 14px 40px; font-size: 1.1rem; font-weight: 700; border-radius: 10px; transition: all 0.3s;">
                  <i class="fa fa-save"></i> Update Contact Settings
                </button>
                <a href="index.php"
                   class="btn btn-secondary"
                   style="padding: 14px 40px; font-size: 1.1rem; font-weight: 700; border-radius: 10px; transition: all 0.3s;">
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
/* Modern Form Styles */
.form-control:focus {
  outline: none;
  border-color: #667eea !important;
  box-shadow: 0 0 0 3px rgba(102,126,234,0.1) !important;
}

.btn {
  transition: all 0.3s ease;
  border: none;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translate3d(0, -20px, 0);
  }
  to {
    opacity: 1;
    transform: translate3d(0, 0, 0);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .right_col {
    padding: 15px 10px !important;
  }

  .x_panel {
    padding: 20px 15px !important;
    margin: 10px !important;
  }

  .btn {
    padding: 12px 25px !important;
    font-size: 1rem !important;
    width: 100%;
    margin-bottom: 10px;
  }

  div[style*="display: flex"] {
    flex-direction: column;
  }

  h2 {
    font-size: 1.4rem !important;
  }

  h3 {
    font-size: 1.1rem !important;
  }
}

@media (max-width: 576px) {
  .form-control {
    font-size: 0.95rem !important;
  }

  label {
    font-size: 1rem !important;
  }
}
</style>

<script>
// Auto-hide success messages
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert-success');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.5s';
      setTimeout(function() {
        alert.style.display = 'none';
      }, 500);
    }, 3000);
  });
});

// Form validation
document.getElementById('contactForm').addEventListener('submit', function(e) {
  const requiredFields = this.querySelectorAll('[required]');
  let isValid = true;

  requiredFields.forEach(function(field) {
    if (!field.value.trim()) {
      isValid = false;
      field.style.borderColor = '#e74c3c';
    } else {
      field.style.borderColor = '#e1e8ed';
    }
  });

  if (!isValid) {
    e.preventDefault();
    alert('Please fill in all required fields');
    return false;
  }

  return true;
});

// Remove error highlighting on input
document.querySelectorAll('.form-control').forEach(function(field) {
  field.addEventListener('input', function() {
    this.style.borderColor = '#e1e8ed';
  });
});
</script>
