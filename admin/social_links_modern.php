<?php
/**
 * Social Links Management - Modern Interface
 */

require 'check_auth.php';
require 'top_header.php';

$message = '';
$message_type = '';

// Update operation
if (isset($_POST['update_social'])) {
    $social_twitter = mysqli_real_escape_string($conn, trim($_POST['social_twitter']));
    $social_linkedin = mysqli_real_escape_string($conn, trim($_POST['social_linkedin']));
    $social_instagram = mysqli_real_escape_string($conn, trim($_POST['social_instagram']));

    $sql = "UPDATE tbl_social SET
            social_twitter = '$social_twitter',
            social_linkedin = '$social_linkedin',
            social_instagram = '$social_instagram'
            WHERE social_id = 1";

    if (mysqli_query($conn, $sql)) {
        $message = 'Social links updated successfully';
        $message_type = 'success';
    } else {
        $message = 'Error updating social links';
        $message_type = 'error';
    }
}

// Fetch current social links
$result = mysqli_query($conn, "SELECT * FROM tbl_social WHERE social_id = 1");
$social = mysqli_fetch_assoc($result);
?>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>

      <div class="right_col" role="main" style="background: #f8fafc; min-height:100vh;">

        <div style="padding: 20px 30px; background: white; margin-bottom: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
          <h2 style="margin: 0; font-size: 1.8rem; font-weight: 800; color: #1a1a1a;">
            <i class="fa fa-share-alt" style="color: #667eea; margin-right: 10px;"></i>
            Social Media Links
          </h2>
          <p style="margin: 8px 0 0 0; color: #7f8c8d; font-size: 1rem;">
            Manage social media links displayed on the website
          </p>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?>" style="margin: 0 30px 25px 30px; padding: 15px 20px; border-radius: 10px; animation: slideInDown 0.3s;">
          <i class="fa fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
          <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div style="padding: 0 30px 30px 30px;">
          <div class="x_panel" style="border-radius: 16px; background: white; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); max-width: 900px; margin: 0 auto;">

            <form method="post" action="" id="socialForm">

              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-bottom: 30px;">

                <div class="form-group">
                  <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                    <i class="fab fa-twitter" style="color: #1DA1F2; margin-right: 8px;"></i>
                    Twitter URL
                  </label>
                  <input type="url" name="social_twitter" class="form-control"
                         value="<?php echo htmlspecialchars($social['social_twitter']); ?>"
                         placeholder="https://twitter.com/yourcompany"
                         style="padding: 12px 15px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s;">
                  <small style="color: #7f8c8d; font-size: 0.9rem;">Full Twitter profile URL</small>
                </div>

                <div class="form-group">
                  <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                    <i class="fab fa-linkedin" style="color: #0077B5; margin-right: 8px;"></i>
                    LinkedIn URL
                  </label>
                  <input type="url" name="social_linkedin" class="form-control"
                         value="<?php echo htmlspecialchars($social['social_linkedin']); ?>"
                         placeholder="https://linkedin.com/company/yourcompany"
                         style="padding: 12px 15px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s;">
                  <small style="color: #7f8c8d; font-size: 0.9rem;">Full LinkedIn profile URL</small>
                </div>

                <div class="form-group">
                  <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                    <i class="fab fa-instagram" style="color: #E1306C; margin-right: 8px;"></i>
                    Instagram URL
                  </label>
                  <input type="url" name="social_instagram" class="form-control"
                         value="<?php echo htmlspecialchars($social['social_instagram']); ?>"
                         placeholder="https://instagram.com/yourcompany"
                         style="padding: 12px 15px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s;">
                  <small style="color: #7f8c8d; font-size: 0.9rem;">Full Instagram profile URL</small>
                </div>

              </div>

              <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
                <h4 style="margin: 0 0 15px 0; font-size: 1.1rem; font-weight: 600; color: #2c3e50;">
                  <i class="fa fa-eye" style="color: #667eea;"></i> Preview
                </h4>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                  <?php if (!empty($social['social_twitter'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_twitter']); ?>" target="_blank"
                     style="width: 50px; height: 50px; background: #1DA1F2; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; text-decoration: none; transition: transform 0.3s;"
                     onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fab fa-twitter"></i>
                  </a>
                  <?php endif; ?>
                  <?php if (!empty($social['social_linkedin'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_linkedin']); ?>" target="_blank"
                     style="width: 50px; height: 50px; background: #0077B5; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; text-decoration: none; transition: transform 0.3s;"
                     onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fab fa-linkedin"></i>
                  </a>
                  <?php endif; ?>
                  <?php if (!empty($social['social_instagram'])): ?>
                  <a href="<?php echo htmlspecialchars($social['social_instagram']); ?>" target="_blank"
                     style="width: 50px; height: 50px; background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; text-decoration: none; transition: transform 0.3s;"
                     onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fab fa-instagram"></i>
                  </a>
                  <?php endif; ?>
                </div>
              </div>

              <div style="display: flex; gap: 15px; justify-content: center; padding-top: 20px; border-top: 2px solid #e6e9ed;">
                <button type="submit" name="update_social" class="btn btn-success"
                        style="padding: 14px 40px; font-size: 1.1rem; font-weight: 700; border-radius: 10px; transition: all 0.3s;">
                  <i class="fa fa-save"></i> Update Social Links
                </button>
                <a href="index.php" class="btn btn-secondary"
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

@media (max-width: 768px) {
  .right_col {
    padding: 15px 10px !important;
  }

  .x_panel {
    padding: 20px 15px !important;
  }

  .btn {
    padding: 12px 25px !important;
    font-size: 1rem !important;
    width: 100%;
  }

  div[style*="display: flex"] {
    flex-direction: column;
  }

  h2 {
    font-size: 1.4rem !important;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert-success');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.5s';
      setTimeout(() => alert.style.display = 'none', 500);
    }, 3000);
  });
});

// URL validation
document.getElementById('socialForm').addEventListener('submit', function(e) {
  const inputs = this.querySelectorAll('input[type="url"]');
  let isValid = true;

  inputs.forEach(function(input) {
    if (input.value && !input.value.startsWith('http')) {
      isValid = false;
      input.style.borderColor = '#e74c3c';
    } else {
      input.style.borderColor = '#e1e8ed';
    }
  });

  if (!isValid) {
    e.preventDefault();
    alert('Please enter valid URLs starting with http:// or https://');
    return false;
  }

  return true;
});

document.querySelectorAll('.form-control').forEach(function(field) {
  field.addEventListener('input', function() {
    this.style.borderColor = '#e1e8ed';
  });
});
</script>
