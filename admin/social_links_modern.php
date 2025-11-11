<?php
/**
 * Social Links Management - Modern Interface with All Social Media Platforms
 */

require 'check_auth.php';
require 'top_header.php';

$message = '';
$message_type = '';

// Update operation
if (isset($_POST['update_social'])) {
    // Sanitize all inputs
    $social_facebook = mysqli_real_escape_string($conn, trim($_POST['social_facebook'] ?? ''));
    $social_twitter = mysqli_real_escape_string($conn, trim($_POST['social_twitter'] ?? ''));
    $social_linkedin = mysqli_real_escape_string($conn, trim($_POST['social_linkedin'] ?? ''));
    $social_instagram = mysqli_real_escape_string($conn, trim($_POST['social_instagram'] ?? ''));
    $social_youtube = mysqli_real_escape_string($conn, trim($_POST['social_youtube'] ?? ''));
    $social_whatsapp = mysqli_real_escape_string($conn, trim($_POST['social_whatsapp'] ?? ''));
    $social_pinterest = mysqli_real_escape_string($conn, trim($_POST['social_pinterest'] ?? ''));
    $social_tiktok = mysqli_real_escape_string($conn, trim($_POST['social_tiktok'] ?? ''));
    $social_telegram = mysqli_real_escape_string($conn, trim($_POST['social_telegram'] ?? ''));
    $social_snapchat = mysqli_real_escape_string($conn, trim($_POST['social_snapchat'] ?? ''));

    // Check if record exists
    $check = mysqli_query($conn, "SELECT * FROM tbl_social WHERE social_id = 1");

    if (mysqli_num_rows($check) > 0) {
        // Update existing record
        $sql = "UPDATE tbl_social SET
                social_facebook = '$social_facebook',
                social_twitter = '$social_twitter',
                social_linkedin = '$social_linkedin',
                social_instagram = '$social_instagram',
                social_youtube = '$social_youtube',
                social_whatsapp = '$social_whatsapp',
                social_pinterest = '$social_pinterest',
                social_tiktok = '$social_tiktok',
                social_telegram = '$social_telegram',
                social_snapchat = '$social_snapchat'
                WHERE social_id = 1";
    } else {
        // Insert new record
        $sql = "INSERT INTO tbl_social (social_id, social_facebook, social_twitter, social_linkedin, social_instagram,
                social_youtube, social_whatsapp, social_pinterest, social_tiktok, social_telegram, social_snapchat)
                VALUES (1, '$social_facebook', '$social_twitter', '$social_linkedin', '$social_instagram',
                '$social_youtube', '$social_whatsapp', '$social_pinterest', '$social_tiktok', '$social_telegram', '$social_snapchat')";
    }

    if (mysqli_query($conn, $sql)) {
        $message = 'Social links updated successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error updating social links: ' . mysqli_error($conn);
        $message_type = 'error';
    }
}

// Fetch current social links
$result = mysqli_query($conn, "SELECT * FROM tbl_social WHERE social_id = 1");
$social = mysqli_fetch_assoc($result);

// If no record exists, create empty array
if (!$social) {
    $social = [
        'social_facebook' => '',
        'social_twitter' => '',
        'social_linkedin' => '',
        'social_instagram' => '',
        'social_youtube' => '',
        'social_whatsapp' => '',
        'social_pinterest' => '',
        'social_tiktok' => '',
        'social_telegram' => '',
        'social_snapchat' => ''
    ];
}
?>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>

      <div class="right_col" role="main" style="background: #f8fafc; min-height:100vh;">

        <!-- Page Header -->
        <div style="padding: 25px 30px; background: white; margin-bottom: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
          <h2 style="margin: 0; font-size: 1.8rem; font-weight: 800; color: #1a1a1a;">
            <i class="fa fa-share-alt" style="color: #667eea; margin-right: 10px;"></i>
            Social Media Links
          </h2>
          <p style="margin: 8px 0 0 0; color: #7f8c8d; font-size: 1rem;">
            Manage all social media links displayed on your website
          </p>
        </div>

        <!-- Success/Error Message -->
        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?>"
             style="margin: 0 30px 25px 30px; padding: 15px 20px; border-radius: 10px; animation: slideInDown 0.3s;">
          <i class="fa fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
          <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <!-- Validation Error Message -->
        <div id="validationError" style="display: none; margin: 0 30px 25px 30px; padding: 15px 20px; border-radius: 10px; background: #fee; border-left: 4px solid #dc3545; color: #721c24;">
          <i class="fa fa-exclamation-triangle"></i>
          <span id="validationErrorText"></span>
        </div>

        <!-- Form Container -->
        <div style="padding: 0 30px 30px 30px;">
          <div style="border-radius: 16px; background: white; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 1200px; margin: 0 auto;">

            <form method="post" action="" id="socialForm">

              <!-- Social Media Grid -->
              <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; margin-bottom: 30px;">

                <!-- Facebook -->
                <div class="social-field">
                  <label>
                    <i class="fab fa-facebook" style="color: #1877F2;"></i>
                    Facebook
                  </label>
                  <input type="text" name="social_facebook" class="form-control-social"
                         value="<?php echo htmlspecialchars($social['social_facebook'] ?? ''); ?>"
                         placeholder="https://facebook.com/yourcompany">
                  <small>Full Facebook page URL</small>
                </div>

                <!-- Twitter / X -->
                <div class="social-field">
                  <label>
                    <i class="fab fa-twitter" style="color: #1DA1F2;"></i>
                    Twitter / X
                  </label>
                  <input type="text" name="social_twitter" class="form-control-social"
                         value="<?php echo htmlspecialchars($social['social_twitter'] ?? ''); ?>"
                         placeholder="https://twitter.com/yourcompany">
                  <small>Full Twitter/X profile URL</small>
                </div>

                <!-- LinkedIn -->
                <div class="social-field">
                  <label>
                    <i class="fab fa-linkedin" style="color: #0077B5;"></i>
                    LinkedIn
                  </label>
                  <input type="text" name="social_linkedin" class="form-control-social"
                         value="<?php echo htmlspecialchars($social['social_linkedin'] ?? ''); ?>"
                         placeholder="https://linkedin.com/company/yourcompany">
                  <small>Full LinkedIn company page URL</small>
                </div>

                <!-- Instagram -->
                <div class="social-field">
                  <label>
                    <i class="fab fa-instagram" style="color: #E1306C;"></i>
                    Instagram
                  </label>
                  <input type="text" name="social_instagram" class="form-control-social"
                         value="<?php echo htmlspecialchars($social['social_instagram'] ?? ''); ?>"
                         placeholder="https://instagram.com/yourcompany">
                  <small>Full Instagram profile URL</small>
                </div>

                <!-- YouTube -->
                <div class="social-field">
                  <label>
                    <i class="fab fa-youtube" style="color: #FF0000;"></i>
                    YouTube
                  </label>
                  <input type="text" name="social_youtube" class="form-control-social"
                         value="<?php echo htmlspecialchars($social['social_youtube'] ?? ''); ?>"
                         placeholder="https://youtube.com/@yourcompany">
                  <small>Full YouTube channel URL</small>
                </div>

                <!-- WhatsApp -->
                <div class="social-field">
                  <label>
                    <i class="fab fa-whatsapp" style="color: #25D366;"></i>
                    WhatsApp
                  </label>
                  <input type="text" name="social_whatsapp" class="form-control-social"
                         value="<?php echo htmlspecialchars($social['social_whatsapp'] ?? ''); ?>"
                         placeholder="https://wa.me/1234567890">
                  <small>WhatsApp business link (wa.me format)</small>
                </div>

                <!-- Pinterest -->
                <div class="social-field">
                  <label>
                    <i class="fab fa-pinterest" style="color: #E60023;"></i>
                    Pinterest
                  </label>
                  <input type="text" name="social_pinterest" class="form-control-social"
                         value="<?php echo htmlspecialchars($social['social_pinterest'] ?? ''); ?>"
                         placeholder="https://pinterest.com/yourcompany">
                  <small>Full Pinterest profile URL</small>
                </div>

                <!-- TikTok -->
                <div class="social-field">
                  <label>
                    <i class="fab fa-tiktok" style="color: #000000;"></i>
                    TikTok
                  </label>
                  <input type="text" name="social_tiktok" class="form-control-social"
                         value="<?php echo htmlspecialchars($social['social_tiktok'] ?? ''); ?>"
                         placeholder="https://tiktok.com/@yourcompany">
                  <small>Full TikTok profile URL</small>
                </div>

                <!-- Telegram -->
                <div class="social-field">
                  <label>
                    <i class="fab fa-telegram" style="color: #0088cc;"></i>
                    Telegram
                  </label>
                  <input type="text" name="social_telegram" class="form-control-social"
                         value="<?php echo htmlspecialchars($social['social_telegram'] ?? ''); ?>"
                         placeholder="https://t.me/yourcompany">
                  <small>Full Telegram channel URL</small>
                </div>

                <!-- Snapchat -->
                <div class="social-field">
                  <label>
                    <i class="fab fa-snapchat" style="color: #FFFC00;"></i>
                    Snapchat
                  </label>
                  <input type="text" name="social_snapchat" class="form-control-social"
                         value="<?php echo htmlspecialchars($social['social_snapchat'] ?? ''); ?>"
                         placeholder="https://snapchat.com/add/yourcompany">
                  <small>Full Snapchat profile URL</small>
                </div>

              </div>

              <!-- Preview Section -->
              <div style="background: #f8f9fa; padding: 25px; border-radius: 12px; margin-bottom: 30px;">
                <h4 style="margin: 0 0 20px 0; font-size: 1.2rem; font-weight: 700; color: #2c3e50;">
                  <i class="fa fa-eye" style="color: #667eea;"></i> Live Preview
                </h4>
                <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                  <?php if (!empty($social['social_facebook'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_facebook']); ?>" target="_blank" class="social-icon" style="background: #1877F2;">
                    <i class="fab fa-facebook-f"></i>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($social['social_twitter'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_twitter']); ?>" target="_blank" class="social-icon" style="background: #1DA1F2;">
                    <i class="fab fa-twitter"></i>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($social['social_linkedin'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_linkedin']); ?>" target="_blank" class="social-icon" style="background: #0077B5;">
                    <i class="fab fa-linkedin-in"></i>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($social['social_instagram'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_instagram']); ?>" target="_blank" class="social-icon" style="background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);">
                    <i class="fab fa-instagram"></i>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($social['social_youtube'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_youtube']); ?>" target="_blank" class="social-icon" style="background: #FF0000;">
                    <i class="fab fa-youtube"></i>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($social['social_whatsapp'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_whatsapp']); ?>" target="_blank" class="social-icon" style="background: #25D366;">
                    <i class="fab fa-whatsapp"></i>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($social['social_pinterest'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_pinterest']); ?>" target="_blank" class="social-icon" style="background: #E60023;">
                    <i class="fab fa-pinterest-p"></i>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($social['social_tiktok'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_tiktok']); ?>" target="_blank" class="social-icon" style="background: #000000;">
                    <i class="fab fa-tiktok"></i>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($social['social_telegram'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_telegram']); ?>" target="_blank" class="social-icon" style="background: #0088cc;">
                    <i class="fab fa-telegram-plane"></i>
                  </a>
                  <?php endif; ?>

                  <?php if (!empty($social['social_snapchat'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_snapchat']); ?>" target="_blank" class="social-icon" style="background: #FFFC00; color: #000;">
                    <i class="fab fa-snapchat-ghost"></i>
                  </a>
                  <?php endif; ?>

                  <?php if (empty($social['social_facebook']) && empty($social['social_twitter']) && empty($social['social_linkedin']) && empty($social['social_instagram']) && empty($social['social_youtube']) && empty($social['social_whatsapp']) && empty($social['social_pinterest']) && empty($social['social_tiktok']) && empty($social['social_telegram']) && empty($social['social_snapchat'])): ?>
                  <p style="color: #7f8c8d; margin: 0;">No social media links added yet. Add links above to see the preview.</p>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Action Buttons -->
              <div style="display: flex; gap: 15px; justify-content: center; padding-top: 20px; border-top: 2px solid #e6e9ed;">
                <button type="submit" name="update_social" class="btn btn-success btn-modern">
                  <i class="fa fa-save"></i> Update Social Links
                </button>
                <a href="index.php" class="btn btn-secondary btn-modern">
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
/* Form Styling */
.social-field {
  display: flex;
  flex-direction: column;
}

.social-field label {
  display: block;
  font-weight: 600;
  color: #333;
  margin-bottom: 10px;
  font-size: 1.05rem;
}

.social-field label i {
  margin-right: 8px;
  font-size: 1.2rem;
}

.form-control-social {
  width: 100%;
  padding: 12px 15px;
  font-size: 1rem;
  border: 2px solid #e1e8ed;
  border-radius: 10px;
  transition: all 0.3s;
  font-family: inherit;
}

.form-control-social:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.form-control-social.error {
  border-color: #e74c3c;
  background: #fff5f5;
}

.social-field small {
  color: #7f8c8d;
  font-size: 0.85rem;
  margin-top: 6px;
}

/* Social Icon Preview */
.social-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.4rem;
  text-decoration: none;
  transition: all 0.3s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.social-icon:hover {
  transform: scale(1.15) translateY(-3px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

/* Button Styling */
.btn-modern {
  padding: 14px 40px;
  font-size: 1.1rem;
  font-weight: 700;
  border-radius: 10px;
  transition: all 0.3s;
  border: none;
}

.btn-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

/* Animations */
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

  .btn-modern {
    padding: 12px 25px;
    font-size: 1rem;
    width: 100%;
  }

  div[style*="display: flex"][style*="justify-content: center"] {
    flex-direction: column;
  }

  h2 {
    font-size: 1.4rem !important;
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
      setTimeout(() => alert.style.display = 'none', 500);
    }, 4000);
  });
});

// Form validation
document.getElementById('socialForm').addEventListener('submit', function(e) {
  const inputs = this.querySelectorAll('.form-control-social');
  let hasError = false;
  let errorFields = [];

  // Clear previous errors
  inputs.forEach(input => {
    input.classList.remove('error');
  });

  // Validate each field
  inputs.forEach(function(input) {
    const value = input.value.trim();

    // Skip empty fields (they're optional)
    if (!value) return;

    // Check if URL starts with http:// or https://
    if (input.type === 'url' && !value.startsWith('http://') && !value.startsWith('https://')) {
      hasError = true;
      input.classList.add('error');
      const fieldName = input.closest('.social-field').querySelector('label').textContent.trim();
      errorFields.push(fieldName);
    }
  });

  // Show error message if validation fails
  if (hasError) {
    e.preventDefault();
    const errorDiv = document.getElementById('validationError');
    const errorText = document.getElementById('validationErrorText');
    errorText.textContent = 'Please enter valid URLs starting with http:// or https:// for: ' + errorFields.join(', ');
    errorDiv.style.display = 'block';
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Auto-hide error after 5 seconds
    setTimeout(() => {
      errorDiv.style.opacity = '0';
      errorDiv.style.transition = 'opacity 0.5s';
      setTimeout(() => {
        errorDiv.style.display = 'none';
        errorDiv.style.opacity = '1';
      }, 500);
    }, 5000);

    return false;
  }

  return true;
});

// Clear error styling on input
document.querySelectorAll('.form-control-social').forEach(function(field) {
  field.addEventListener('input', function() {
    this.classList.remove('error');
    document.getElementById('validationError').style.display = 'none';
  });
});
</script>
