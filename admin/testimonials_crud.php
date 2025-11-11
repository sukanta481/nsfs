<?php
/**
 * Testimonials Management - Modern CRUD System
 * Manage customer testimonials displayed on website
 */

require 'check_auth.php';
require 'top_header.php';

// Handle CRUD operations
$message = '';
$message_type = '';

// Delete operation
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $testimonial_id = intval($_GET['id']);
    $delete_sql = "DELETE FROM tbl_testimonial WHERE testimonial_id = $testimonial_id";
    if (mysqli_query($conn, $delete_sql)) {
        $message = 'Testimonial deleted successfully';
        $message_type = 'success';
    } else {
        $message = 'Error deleting testimonial';
        $message_type = 'error';
    }
}

// Add operation
if (isset($_POST['save_testimonial'])) {
    $testimonial_title = mysqli_real_escape_string($conn, trim($_POST['testimonial_title']));
    $testimonial_position = mysqli_real_escape_string($conn, trim($_POST['testimonial_position']));
    $testimonial_rate = intval($_POST['testimonial_rate']);
    $testimonial_desc = mysqli_real_escape_string($conn, trim($_POST['testimonial_desc']));

    // Handle image upload
    $image_name = 'noimage.jpg';
    if (!empty($_FILES['testimonial_image']['name'])) {
        $image_name = time() . '_' . $_FILES['testimonial_image']['name'];
        $temp_name = $_FILES['testimonial_image']['tmp_name'];
        $dir = "post_img/";
        $uploadimage = $dir . $image_name;
        move_uploaded_file($temp_name, $uploadimage);
    }

    $insert_sql = "INSERT INTO tbl_testimonial (testimonial_name, testimonial_position, testimonial_rate, testimonial_desc, testimonial_image, alise)
                   VALUES ('$testimonial_title', '$testimonial_position', '$testimonial_rate', '$testimonial_desc', '$image_name', '" . strtolower(str_replace(' ', '-', $testimonial_title)) . "')";

    if (mysqli_query($conn, $insert_sql)) {
        $message = 'Testimonial added successfully';
        $message_type = 'success';
    } else {
        $message = 'Error adding testimonial';
        $message_type = 'error';
    }
}

// Update operation
if (isset($_POST['update_testimonial'])) {
    $testimonial_id = intval($_POST['testimonial_id']);
    $testimonial_title = mysqli_real_escape_string($conn, trim($_POST['testimonial_title']));
    $testimonial_position = mysqli_real_escape_string($conn, trim($_POST['testimonial_position']));
    $testimonial_rate = intval($_POST['testimonial_rate']);
    $testimonial_desc = mysqli_real_escape_string($conn, trim($_POST['testimonial_desc']));

    $update_sql = "UPDATE tbl_testimonial SET
                   testimonial_name = '$testimonial_title',
                   testimonial_position = '$testimonial_position',
                   testimonial_rate = '$testimonial_rate',
                   testimonial_desc = '$testimonial_desc',
                   alise = '" . strtolower(str_replace(' ', '-', $testimonial_title)) . "'
                   WHERE testimonial_id = $testimonial_id";

    if (mysqli_query($conn, $update_sql)) {
        // Handle image upload if new image is provided
        if (!empty($_FILES['testimonial_image']['name'])) {
            $image_name = time() . '_' . $_FILES['testimonial_image']['name'];
            $temp_name = $_FILES['testimonial_image']['tmp_name'];
            $dir = "post_img/";
            $uploadimage = $dir . $image_name;
            move_uploaded_file($temp_name, $uploadimage);

            $image_update_sql = "UPDATE tbl_testimonial SET testimonial_image = '$image_name' WHERE testimonial_id = $testimonial_id";
            mysqli_query($conn, $image_update_sql);
        }

        $message = 'Testimonial updated successfully';
        $message_type = 'success';
    } else {
        $message = 'Error updating testimonial';
        $message_type = 'error';
    }
}

// Fetch all testimonials
$sql = "SELECT * FROM tbl_testimonial ORDER BY testimonial_id DESC";
$result = mysqli_query($conn, $sql);

// Get edit data if editing
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $edit_sql = "SELECT * FROM tbl_testimonial WHERE testimonial_id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_sql);
    $edit_data = mysqli_fetch_assoc($edit_result);
}
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
            <i class="fa fa-comments" style="color: #667eea; margin-right: 10px;"></i>
            Testimonials Management
          </h2>
          <p style="margin: 8px 0 0 0; color: #7f8c8d; font-size: 1rem;">
            Manage customer testimonials and reviews displayed on the website
          </p>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?>" style="margin: 0 30px 25px 30px; padding: 15px 20px; border-radius: 10px; font-size: 1.05rem; animation: slideInDown 0.3s;">
          <i class="fa fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
          <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px; padding: 0 30px 30px 30px;">

          <!-- Add/Edit Form Panel -->
          <div class="x_panel" style="border-radius: 16px; background: white; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); height: fit-content;">
            <div class="x_title" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e6e9ed;">
              <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: #222;">
                <i class="fa fa-<?php echo $edit_data ? 'edit' : 'plus-circle'; ?>" style="color: <?php echo $edit_data ? '#f39c12' : '#4caf50'; ?>;"></i>
                <?php echo $edit_data ? 'Edit' : 'Add New'; ?> Testimonial
              </h2>
            </div>

            <form method="post" action="" id="testimonialForm" autocomplete="off" enctype="multipart/form-data">
              <?php if ($edit_data): ?>
              <input type="hidden" name="testimonial_id" value="<?php echo $edit_data['testimonial_id']; ?>">
              <?php endif; ?>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                  <i class="fa fa-user" style="color: #667eea;"></i>
                  Customer Name <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text"
                       name="testimonial_title"
                       id="testimonial_title"
                       class="form-control"
                       placeholder="Enter customer name"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['testimonial_name']) : ''; ?>"
                       required
                       style="padding: 12px 15px; font-size: 1.05rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s;">
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                  <i class="fa fa-map-marker" style="color: #667eea;"></i>
                  Location / Company <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text"
                       name="testimonial_position"
                       id="testimonial_position"
                       class="form-control"
                       placeholder="e.g., New York, USA or ABC Company"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['testimonial_position']) : ''; ?>"
                       required
                       style="padding: 12px 15px; font-size: 1.05rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s;">
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                  <i class="fa fa-star" style="color: #667eea;"></i>
                  Rating <span style="color: #e74c3c;">*</span>
                </label>
                <div style="display: flex; gap: 10px; align-items: center;">
                  <input type="number"
                         name="testimonial_rate"
                         id="testimonial_rate"
                         class="form-control"
                         min="1"
                         max="5"
                         value="<?php echo $edit_data ? htmlspecialchars($edit_data['testimonial_rate']) : '5'; ?>"
                         required
                         style="padding: 12px 15px; font-size: 1.05rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s; width: 100px;">
                  <div id="star_preview" style="font-size: 1.5rem; color: #f39c12;"></div>
                </div>
                <small style="color: #7f8c8d; font-size: 0.9rem;">Rating from 1 to 5 stars</small>
              </div>

              <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                  <i class="fa fa-quote-left" style="color: #667eea;"></i>
                  Testimonial Text <span style="color: #e74c3c;">*</span>
                </label>
                <textarea name="testimonial_desc"
                          id="testimonial_desc"
                          class="form-control"
                          rows="5"
                          placeholder="Enter customer's testimonial or review..."
                          required
                          style="padding: 12px 15px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s; resize: vertical;"><?php echo $edit_data ? htmlspecialchars($edit_data['testimonial_desc']) : ''; ?></textarea>
                <small style="color: #7f8c8d; font-size: 0.9rem;">Write a meaningful testimonial from the customer</small>
              </div>

              <div style="display: flex; gap: 12px; margin-top: 25px;">
                <button type="submit"
                        name="<?php echo $edit_data ? 'update_testimonial' : 'save_testimonial'; ?>"
                        class="btn btn-<?php echo $edit_data ? 'warning' : 'success'; ?>"
                        style="flex: 1; padding: 12px 25px; font-size: 1.05rem; font-weight: 700; border-radius: 10px; transition: all 0.3s;">
                  <i class="fa fa-<?php echo $edit_data ? 'save' : 'plus'; ?>"></i>
                  <?php echo $edit_data ? 'Update' : 'Add'; ?> Testimonial
                </button>

                <?php if ($edit_data): ?>
                <a href="testimonials_crud.php"
                   class="btn btn-secondary"
                   style="padding: 12px 25px; font-size: 1.05rem; font-weight: 700; border-radius: 10px; transition: all 0.3s;">
                  <i class="fa fa-times"></i> Cancel
                </a>
                <?php endif; ?>
              </div>
            </form>
          </div>

          <!-- List Panel -->
          <div class="x_panel" style="border-radius: 16px; background: white; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <div class="x_title" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e6e9ed;">
              <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: #222;">
                <i class="fa fa-list" style="color: #3498db;"></i>
                All Testimonials (<?php echo mysqli_num_rows($result); ?>)
              </h2>
            </div>

            <div class="x_content">
              <?php if (mysqli_num_rows($result) == 0): ?>
              <div style="text-align: center; padding: 50px 20px; color: #95a5a6;">
                <i class="fa fa-comments-o" style="font-size: 4rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <p style="font-size: 1.2rem; margin: 0;">No testimonials found. Add one to get started!</p>
              </div>
              <?php else: ?>

              <div class="table-responsive">
                <table class="table table-hover" style="margin-bottom: 0;">
                  <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <tr>
                      <th style="padding: 15px; font-weight: 700; border: none;">#</th>
                      <th style="padding: 15px; font-weight: 700; border: none;">Customer</th>
                      <th style="padding: 15px; font-weight: 700; border: none;">Location</th>
                      <th style="padding: 15px; font-weight: 700; border: none;">Rating</th>
                      <th style="padding: 15px; font-weight: 700; border: none; text-align: right;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $count = 1;
                    mysqli_data_seek($result, 0);
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr style="transition: all 0.2s; border-bottom: 1px solid #ecf0f1;">
                      <td style="padding: 15px; font-weight: 600; color: #7f8c8d;">
                        <?php echo $count++; ?>
                      </td>
                      <td style="padding: 15px; font-size: 1.05rem; color: #2c3e50; font-weight: 600;">
                        <?php echo htmlspecialchars($row['testimonial_name']); ?>
                        <?php if (!empty($row['testimonial_desc'])): ?>
                        <br><small style="color: #7f8c8d; font-weight: 400; font-style: italic;">"<?php echo htmlspecialchars(substr($row['testimonial_desc'], 0, 60)) . (strlen($row['testimonial_desc']) > 60 ? '...' : ''); ?>"</small>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 15px; color: #7f8c8d;">
                        <i class="fa fa-map-marker" style="color: #95a5a6; margin-right: 5px;"></i>
                        <?php echo htmlspecialchars($row['testimonial_position']); ?>
                      </td>
                      <td style="padding: 15px;">
                        <?php
                        $rating = intval($row['testimonial_rate']);
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $rating) {
                                echo '<i class="fa fa-star" style="color: #f39c12;"></i>';
                            } else {
                                echo '<i class="fa fa-star-o" style="color: #ddd;"></i>';
                            }
                        }
                        ?>
                      </td>
                      <td style="padding: 15px; text-align: right;">
                        <div style="display: inline-flex; gap: 8px;">
                          <a href="testimonials_crud.php?action=edit&id=<?php echo $row['testimonial_id']; ?>"
                             class="btn btn-sm btn-warning"
                             style="padding: 8px 15px; border-radius: 8px; font-weight: 600; transition: all 0.2s;"
                             title="Edit">
                            <i class="fa fa-edit"></i> Edit
                          </a>
                          <a href="#"
                             onclick="confirmDelete(<?php echo $row['testimonial_id']; ?>, '<?php echo addslashes($row['testimonial_name']); ?>')"
                             class="btn btn-sm btn-danger"
                             style="padding: 8px 15px; border-radius: 8px; font-weight: 600; transition: all 0.2s;"
                             title="Delete">
                            <i class="fa fa-trash"></i> Delete
                          </a>
                        </div>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
              <?php endif; ?>
            </div>
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
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.table-hover tbody tr:hover {
  background: #f8f9fa;
  transform: scale(1.01);
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
@media (max-width: 1024px) {
  div[style*="grid-template-columns: 1fr 2fr"] {
    grid-template-columns: 1fr !important;
  }
}

@media (max-width: 768px) {
  .right_col {
    padding: 15px 10px !important;
  }

  .x_panel {
    padding: 20px 15px !important;
    margin: 10px !important;
  }

  .btn {
    padding: 10px 15px !important;
    font-size: 0.95rem !important;
  }

  .table-responsive {
    overflow-x: auto;
  }

  h2 {
    font-size: 1.3rem !important;
  }

  table thead {
    font-size: 0.85rem;
  }

  table tbody {
    font-size: 0.9rem;
  }
}
</style>

<script>
function confirmDelete(id, name) {
  if (confirm('Are you sure you want to delete testimonial from "' + name + '"?\n\nThis action cannot be undone.')) {
    window.location.href = 'testimonials_crud.php?action=delete&id=' + id;
  }
}

// Star rating preview
function updateStarPreview() {
  const rating = parseInt(document.getElementById('testimonial_rate').value) || 0;
  const preview = document.getElementById('star_preview');
  let stars = '';
  for (let i = 1; i <= 5; i++) {
    if (i <= rating) {
      stars += '<i class="fa fa-star"></i>';
    } else {
      stars += '<i class="fa fa-star-o" style="color: #ddd;"></i>';
    }
  }
  preview.innerHTML = stars;
}

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

  // Update star preview on load and change
  updateStarPreview();
  document.getElementById('testimonial_rate').addEventListener('input', updateStarPreview);

  // Focus on name input field
  const nameInput = document.getElementById('testimonial_title');
  if (nameInput && !nameInput.value) {
    nameInput.focus();
  }
});

// Form validation
document.getElementById('testimonialForm').addEventListener('submit', function(e) {
  const nameInput = document.getElementById('testimonial_title');
  const locationInput = document.getElementById('testimonial_position');
  const descInput = document.getElementById('testimonial_desc');
  const ratingInput = document.getElementById('testimonial_rate');

  if (nameInput.value.trim().length < 2) {
    e.preventDefault();
    alert('Customer name must be at least 2 characters long');
    nameInput.focus();
    return false;
  }

  if (locationInput.value.trim().length < 2) {
    e.preventDefault();
    alert('Location must be at least 2 characters long');
    locationInput.focus();
    return false;
  }

  const rating = parseInt(ratingInput.value);
  if (rating < 1 || rating > 5) {
    e.preventDefault();
    alert('Rating must be between 1 and 5');
    ratingInput.focus();
    return false;
  }

  if (descInput.value.trim().length < 10) {
    e.preventDefault();
    alert('Testimonial text must be at least 10 characters long');
    descInput.focus();
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
