<?php
/**
 * Team Management - Modern CRUD System
 * Manage team members displayed on website
 */

require 'check_auth.php';
require 'top_header.php';

// Handle CRUD operations
$message = '';
$message_type = '';

// Delete operation
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $team_id = intval($_GET['id']);
    $delete_sql = "DELETE FROM tbl_team WHERE team_id = $team_id";
    if (mysqli_query($conn, $delete_sql)) {
        $message = 'Team member deleted successfully';
        $message_type = 'success';
    } else {
        $message = 'Error deleting team member';
        $message_type = 'error';
    }
}

// Add operation
if (isset($_POST['save_team'])) {
    $team_title = mysqli_real_escape_string($conn, trim($_POST['team_title']));
    $team_desg = mysqli_real_escape_string($conn, trim($_POST['team_desg']));
    $team_srt_desc = mysqli_real_escape_string($conn, trim($_POST['team_srt_desc']));
    $team_type = isset($_POST['team_type']) ? mysqli_real_escape_string($conn, $_POST['team_type']) : '0';

    // Handle image upload
    $image_name = 'noimage.jpg';
    if (!empty($_FILES['team_image']['name'])) {
        $image_name = time() . '_' . $_FILES['team_image']['name'];
        $temp_name = $_FILES['team_image']['tmp_name'];
        $dir = "post_img/";
        $uploadimage = $dir . $image_name;
        move_uploaded_file($temp_name, $uploadimage);
    }

    $insert_sql = "INSERT INTO tbl_team (team_title, team_desg, team_srt_desc, team_type, team_image, alise)
                   VALUES ('$team_title', '$team_desg', '$team_srt_desc', '$team_type', '$image_name', '" . strtolower(str_replace(' ', '-', $team_title)) . "')";

    if (mysqli_query($conn, $insert_sql)) {
        $message = 'Team member added successfully';
        $message_type = 'success';
    } else {
        $message = 'Error adding team member';
        $message_type = 'error';
    }
}

// Update operation
if (isset($_POST['update_team'])) {
    $team_id = intval($_POST['team_id']);
    $team_title = mysqli_real_escape_string($conn, trim($_POST['team_title']));
    $team_desg = mysqli_real_escape_string($conn, trim($_POST['team_desg']));
    $team_srt_desc = mysqli_real_escape_string($conn, trim($_POST['team_srt_desc']));
    $team_type = isset($_POST['team_type']) ? mysqli_real_escape_string($conn, $_POST['team_type']) : '0';

    $update_sql = "UPDATE tbl_team SET
                   team_title = '$team_title',
                   team_desg = '$team_desg',
                   team_srt_desc = '$team_srt_desc',
                   team_type = '$team_type',
                   alise = '" . strtolower(str_replace(' ', '-', $team_title)) . "'
                   WHERE team_id = $team_id";

    if (mysqli_query($conn, $update_sql)) {
        // Handle image upload if new image is provided
        if (!empty($_FILES['team_image']['name'])) {
            $image_name = time() . '_' . $_FILES['team_image']['name'];
            $temp_name = $_FILES['team_image']['tmp_name'];
            $dir = "post_img/";
            $uploadimage = $dir . $image_name;
            move_uploaded_file($temp_name, $uploadimage);

            $image_update_sql = "UPDATE tbl_team SET team_image = '$image_name' WHERE team_id = $team_id";
            mysqli_query($conn, $image_update_sql);
        }

        $message = 'Team member updated successfully';
        $message_type = 'success';
    } else {
        $message = 'Error updating team member';
        $message_type = 'error';
    }
}

// Fetch all team members
$sql = "SELECT * FROM tbl_team ORDER BY team_type ASC, team_id ASC";
$result = mysqli_query($conn, $sql);

// Get edit data if editing
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $edit_sql = "SELECT * FROM tbl_team WHERE team_id = $edit_id";
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
            <i class="fa fa-users" style="color: #667eea; margin-right: 10px;"></i>
            Team Management
          </h2>
          <p style="margin: 8px 0 0 0; color: #7f8c8d; font-size: 1rem;">
            Manage team members displayed on the website
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
                <?php echo $edit_data ? 'Edit' : 'Add New'; ?> Team Member
              </h2>
            </div>

            <form method="post" action="" id="teamForm" autocomplete="off" enctype="multipart/form-data">
              <?php if ($edit_data): ?>
              <input type="hidden" name="team_id" value="<?php echo $edit_data['team_id']; ?>">
              <?php endif; ?>

              <!-- Member Image -->
              <?php if ($edit_data && !empty($edit_data['team_image']) && $edit_data['team_image'] != 'noimage.jpg'): ?>
              <div style="margin-bottom: 20px; text-align: center;">
                <img src="post_img/<?php echo htmlspecialchars($edit_data['team_image']); ?>"
                     style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #667eea; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"
                     alt="Current Image" id="current_image">
              </div>
              <?php endif; ?>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                  <i class="fa fa-image" style="color: #667eea;"></i>
                  Member Photo
                </label>
                <input type="file"
                       name="team_image"
                       id="team_image"
                       class="form-control"
                       accept="image/*"
                       style="padding: 10px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s;"
                       onchange="previewImage(this)">
                <small style="color: #7f8c8d; font-size: 0.9rem;">Recommended: Square image, min 300x300px</small>
                <div id="image_preview" style="margin-top: 10px; display: none;">
                  <img id="preview_img" src="" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid #667eea;">
                </div>
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                  <i class="fa fa-user" style="color: #667eea;"></i>
                  Member Name <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text"
                       name="team_title"
                       id="team_title"
                       class="form-control"
                       placeholder="Enter member name (e.g., John Doe)"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['team_title']) : ''; ?>"
                       required
                       style="padding: 12px 15px; font-size: 1.05rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s;">
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                  <i class="fa fa-briefcase" style="color: #667eea;"></i>
                  Designation / Role <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text"
                       name="team_desg"
                       id="team_desg"
                       class="form-control"
                       placeholder="Enter designation (e.g., CEO, Manager)"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['team_desg']) : ''; ?>"
                       required
                       style="padding: 12px 15px; font-size: 1.05rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s;">
              </div>

              <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                  <i class="fa fa-align-left" style="color: #667eea;"></i>
                  Short Description
                </label>
                <textarea name="team_srt_desc"
                          id="team_srt_desc"
                          class="form-control"
                          rows="3"
                          placeholder="Brief description about the team member (optional)"
                          style="padding: 12px 15px; font-size: 1rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s; resize: vertical;"><?php echo $edit_data ? htmlspecialchars($edit_data['team_srt_desc']) : ''; ?></textarea>
              </div>

              <input type="hidden" name="team_type" value="0">

              <div style="display: flex; gap: 12px; margin-top: 25px;">
                <button type="submit"
                        name="<?php echo $edit_data ? 'update_team' : 'save_team'; ?>"
                        class="btn btn-<?php echo $edit_data ? 'warning' : 'success'; ?>"
                        style="flex: 1; padding: 12px 25px; font-size: 1.05rem; font-weight: 700; border-radius: 10px; transition: all 0.3s;">
                  <i class="fa fa-<?php echo $edit_data ? 'save' : 'plus'; ?>"></i>
                  <?php echo $edit_data ? 'Update' : 'Add'; ?> Member
                </button>

                <?php if ($edit_data): ?>
                <a href="team_crud.php"
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
                All Team Members (<?php echo mysqli_num_rows($result); ?>)
              </h2>
            </div>

            <div class="x_content">
              <?php if (mysqli_num_rows($result) == 0): ?>
              <div style="text-align: center; padding: 50px 20px; color: #95a5a6;">
                <i class="fa fa-users" style="font-size: 4rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <p style="font-size: 1.2rem; margin: 0;">No team members found. Add one to get started!</p>
              </div>
              <?php else: ?>

              <div class="table-responsive">
                <table class="table table-hover" style="margin-bottom: 0;">
                  <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <tr>
                      <th style="padding: 15px; font-weight: 700; border: none;">#</th>
                      <th style="padding: 15px; font-weight: 700; border: none;">Photo</th>
                      <th style="padding: 15px; font-weight: 700; border: none;">Name</th>
                      <th style="padding: 15px; font-weight: 700; border: none;">Designation</th>
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
                      <td style="padding: 15px;">
                        <?php if (!empty($row['team_image']) && $row['team_image'] != 'noimage.jpg'): ?>
                        <img src="post_img/<?php echo htmlspecialchars($row['team_image']); ?>"
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%; border: 2px solid #667eea;"
                             alt="<?php echo htmlspecialchars($row['team_title']); ?>">
                        <?php else: ?>
                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                          <?php echo strtoupper(substr($row['team_title'], 0, 2)); ?>
                        </div>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 15px; font-size: 1.05rem; color: #2c3e50; font-weight: 600;">
                        <?php echo htmlspecialchars($row['team_title']); ?>
                        <?php if (!empty($row['team_srt_desc'])): ?>
                        <br><small style="color: #7f8c8d; font-weight: 400;"><?php echo htmlspecialchars(substr($row['team_srt_desc'], 0, 60)) . (strlen($row['team_srt_desc']) > 60 ? '...' : ''); ?></small>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 15px; color: #7f8c8d;">
                        <i class="fa fa-briefcase" style="color: #95a5a6; margin-right: 5px;"></i>
                        <?php echo htmlspecialchars($row['team_desg']); ?>
                      </td>
                      <td style="padding: 15px; text-align: right;">
                        <div style="display: inline-flex; gap: 8px;">
                          <a href="team_crud.php?action=edit&id=<?php echo $row['team_id']; ?>"
                             class="btn btn-sm btn-warning"
                             style="padding: 8px 15px; border-radius: 8px; font-weight: 600; transition: all 0.2s;"
                             title="Edit">
                            <i class="fa fa-edit"></i> Edit
                          </a>
                          <a href="#"
                             onclick="confirmDelete(<?php echo $row['team_id']; ?>, '<?php echo addslashes($row['team_title']); ?>')"
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

@media (max-width: 400px) {
  .btn span {
    display: none;
  }
}
</style>

<script>
function confirmDelete(id, name) {
  if (confirm('Are you sure you want to delete "' + name + '"?\n\nThis action cannot be undone.')) {
    window.location.href = 'team_crud.php?action=delete&id=' + id;
  }
}

// Image preview function
function previewImage(input) {
  const preview = document.getElementById('image_preview');
  const previewImg = document.getElementById('preview_img');
  const currentImage = document.getElementById('current_image');

  if (input.files && input.files[0]) {
    const reader = new FileReader();

    reader.onload = function(e) {
      previewImg.src = e.target.result;
      preview.style.display = 'block';
      if (currentImage) {
        currentImage.style.opacity = '0.3';
      }
    }

    reader.readAsDataURL(input.files[0]);
  }
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

  // Focus on name input field
  const nameInput = document.getElementById('team_title');
  if (nameInput && !nameInput.value) {
    nameInput.focus();
  }
});

// Form validation
document.getElementById('teamForm').addEventListener('submit', function(e) {
  const nameInput = document.getElementById('team_title');
  const desgInput = document.getElementById('team_desg');

  if (nameInput.value.trim().length < 2) {
    e.preventDefault();
    alert('Member name must be at least 2 characters long');
    nameInput.focus();
    return false;
  }

  if (desgInput.value.trim().length < 2) {
    e.preventDefault();
    alert('Designation must be at least 2 characters long');
    desgInput.focus();
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
