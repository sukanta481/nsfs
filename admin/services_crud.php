<?php
/**
 * Services Management - Modern CRUD System
 * Manage services displayed on website
 */

require 'check_auth.php';
require 'top_header.php';

$message = '';
$message_type = '';

// Delete operation
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $service_id = intval($_GET['id']);
    $delete_sql = "DELETE FROM tbl_service WHERE service_id = $service_id";
    if (mysqli_query($conn, $delete_sql)) {
        $message = 'Service deleted successfully';
        $message_type = 'success';
    } else {
        $message = 'Error deleting service';
        $message_type = 'error';
    }
}

// Add operation
if (isset($_POST['save_service'])) {
    $service_title = mysqli_real_escape_string($conn, trim($_POST['service_title']));
    $service_srt_desc = mysqli_real_escape_string($conn, trim($_POST['service_srt_desc']));
    $service_desc = mysqli_real_escape_string($conn, trim($_POST['service_desc']));
    $service_link = mysqli_real_escape_string($conn, trim($_POST['service_link']));

    // Handle main image upload
    $image_name = 'noimage.jpg';
    if (!empty($_FILES['service_image']['name'])) {
        $image_name = time() . '_' . $_FILES['service_image']['name'];
        move_uploaded_file($_FILES['service_image']['tmp_name'], "post_img/" . $image_name);
    }

    // Handle small image upload
    $small_image_name = 'noimage.jpg';
    if (!empty($_FILES['service_small_image']['name'])) {
        $small_image_name = time() . '_small_' . $_FILES['service_small_image']['name'];
        move_uploaded_file($_FILES['service_small_image']['tmp_name'], "post_img/" . $small_image_name);
    }

    $insert_sql = "INSERT INTO tbl_service (service_title, service_srt_desc, service_desc, service_link, service_image, service_small_image, alise)
                   VALUES ('$service_title', '$service_srt_desc', '$service_desc', '$service_link', '$image_name', '$small_image_name', '" . strtolower(str_replace(' ', '-', $service_title)) . "')";

    if (mysqli_query($conn, $insert_sql)) {
        $message = 'Service added successfully';
        $message_type = 'success';
    } else {
        $message = 'Error adding service';
        $message_type = 'error';
    }
}

// Update operation
if (isset($_POST['update_service'])) {
    $service_id = intval($_POST['service_id']);
    $service_title = mysqli_real_escape_string($conn, trim($_POST['service_title']));
    $service_srt_desc = mysqli_real_escape_string($conn, trim($_POST['service_srt_desc']));
    $service_desc = mysqli_real_escape_string($conn, trim($_POST['service_desc']));
    $service_link = mysqli_real_escape_string($conn, trim($_POST['service_link']));

    $update_sql = "UPDATE tbl_service SET
                   service_title = '$service_title',
                   service_srt_desc = '$service_srt_desc',
                   service_desc = '$service_desc',
                   service_link = '$service_link',
                   alise = '" . strtolower(str_replace(' ', '-', $service_title)) . "'
                   WHERE service_id = $service_id";

    if (mysqli_query($conn, $update_sql)) {
        // Handle main image
        if (!empty($_FILES['service_image']['name'])) {
            $image_name = time() . '_' . $_FILES['service_image']['name'];
            move_uploaded_file($_FILES['service_image']['tmp_name'], "post_img/" . $image_name);
            mysqli_query($conn, "UPDATE tbl_service SET service_image = '$image_name' WHERE service_id = $service_id");
        }
        // Handle small image
        if (!empty($_FILES['service_small_image']['name'])) {
            $small_image_name = time() . '_small_' . $_FILES['service_small_image']['name'];
            move_uploaded_file($_FILES['service_small_image']['tmp_name'], "post_img/" . $small_image_name);
            mysqli_query($conn, "UPDATE tbl_service SET service_small_image = '$small_image_name' WHERE service_id = $service_id");
        }

        $message = 'Service updated successfully';
        $message_type = 'success';
    } else {
        $message = 'Error updating service';
        $message_type = 'error';
    }
}

$sql = "SELECT * FROM tbl_service ORDER BY service_id ASC";
$result = mysqli_query($conn, $sql);

$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $edit_result = mysqli_query($conn, "SELECT * FROM tbl_service WHERE service_id = $edit_id");
    $edit_data = mysqli_fetch_assoc($edit_result);
}
?>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>

      <div class="right_col" role="main" style="background: #f8fafc; min-height:100vh;">

        <div style="padding: 20px 30px; background: white; margin-bottom: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
          <h2 style="margin: 0; font-size: 1.8rem; font-weight: 800; color: #1a1a1a;">
            <i class="fa fa-cogs" style="color: #667eea; margin-right: 10px;"></i>
            Services Management
          </h2>
          <p style="margin: 8px 0 0 0; color: #7f8c8d; font-size: 1rem;">
            Manage services displayed on the website
          </p>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?>" style="margin: 0 30px 25px 30px; padding: 15px 20px; border-radius: 10px; animation: slideInDown 0.3s;">
          <i class="fa fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
          <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px; padding: 0 30px 30px 30px;">

          <div class="x_panel" style="border-radius: 16px; background: white; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); height: fit-content;">
            <div class="x_title" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e6e9ed;">
              <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: #222;">
                <i class="fa fa-<?php echo $edit_data ? 'edit' : 'plus-circle'; ?>" style="color: <?php echo $edit_data ? '#f39c12' : '#4caf50'; ?>;"></i>
                <?php echo $edit_data ? 'Edit' : 'Add New'; ?> Service
              </h2>
            </div>

            <form method="post" action="" id="serviceForm" enctype="multipart/form-data">
              <?php if ($edit_data): ?>
              <input type="hidden" name="service_id" value="<?php echo $edit_data['service_id']; ?>">
              <?php endif; ?>

              <?php if ($edit_data && !empty($edit_data['service_image']) && $edit_data['service_image'] != 'noimage.jpg'): ?>
              <div style="margin-bottom: 20px; text-align: center;">
                <img src="post_img/<?php echo htmlspecialchars($edit_data['service_image']); ?>"
                     style="width: 150px; height: 100px; object-fit: cover; border-radius: 10px; border: 3px solid #667eea;">
              </div>
              <?php endif; ?>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-heading" style="color: #667eea;"></i>
                  Service Title <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text" name="service_title" required class="form-control"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['service_title']) : ''; ?>"
                       placeholder="Enter service title"
                       style="padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px;">
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-image" style="color: #667eea;"></i>
                  Main Service Image <?php echo !$edit_data ? '<span style="color: #e74c3c;">*</span>' : ''; ?>
                </label>
                <input type="file" name="service_image" class="form-control" accept="image/*"
                       <?php echo !$edit_data ? 'required' : ''; ?>
                       style="padding: 10px; border: 2px solid #e1e8ed; border-radius: 10px;">
                <small style="color: #7f8c8d;">Recommended: 800x600px</small>
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-image" style="color: #667eea;"></i>
                  Icon/Small Image
                </label>
                <input type="file" name="service_small_image" class="form-control" accept="image/*"
                       style="padding: 10px; border: 2px solid #e1e8ed; border-radius: 10px;">
                <small style="color: #7f8c8d;">Recommended: 100x100px icon</small>
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-align-left" style="color: #667eea;"></i>
                  Short Description
                </label>
                <textarea name="service_srt_desc" rows="3" class="form-control"
                          placeholder="Brief description (shown on cards)"
                          style="padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px; resize: vertical;"><?php echo $edit_data ? htmlspecialchars($edit_data['service_srt_desc']) : ''; ?></textarea>
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-file-text" style="color: #667eea;"></i>
                  Full Description
                </label>
                <textarea name="service_desc" rows="5" class="form-control"
                          placeholder="Detailed description of the service"
                          style="padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px; resize: vertical;"><?php echo $edit_data ? htmlspecialchars($edit_data['service_desc']) : ''; ?></textarea>
              </div>

              <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-link" style="color: #667eea;"></i>
                  Service Page Link
                </label>
                <input type="text" name="service_link" class="form-control"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['service_link']) : ''; ?>"
                       placeholder="e.g., /services/express-delivery"
                       style="padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px;">
              </div>

              <div style="display: flex; gap: 12px;">
                <button type="submit" name="<?php echo $edit_data ? 'update_service' : 'save_service'; ?>"
                        class="btn btn-<?php echo $edit_data ? 'warning' : 'success'; ?>"
                        style="flex: 1; padding: 12px 25px; font-weight: 700; border-radius: 10px;">
                  <i class="fa fa-<?php echo $edit_data ? 'save' : 'plus'; ?>"></i>
                  <?php echo $edit_data ? 'Update' : 'Add'; ?> Service
                </button>
                <?php if ($edit_data): ?>
                <a href="services_crud.php" class="btn btn-secondary"
                   style="padding: 12px 25px; font-weight: 700; border-radius: 10px;">
                  <i class="fa fa-times"></i> Cancel
                </a>
                <?php endif; ?>
              </div>
            </form>
          </div>

          <div class="x_panel" style="border-radius: 16px; background: white; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <div class="x_title" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e6e9ed;">
              <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: #222;">
                <i class="fa fa-list" style="color: #3498db;"></i>
                All Services (<?php echo mysqli_num_rows($result); ?>)
              </h2>
            </div>

            <div class="x_content">
              <?php if (mysqli_num_rows($result) == 0): ?>
              <div style="text-align: center; padding: 50px 20px; color: #95a5a6;">
                <i class="fa fa-cogs" style="font-size: 4rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <p style="font-size: 1.2rem; margin: 0;">No services found. Add one to get started!</p>
              </div>
              <?php else: ?>

              <div class="table-responsive">
                <table class="table table-hover">
                  <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <tr>
                      <th style="padding: 15px; font-weight: 700; border: none;">#</th>
                      <th style="padding: 15px; font-weight: 700; border: none;">Image</th>
                      <th style="padding: 15px; font-weight: 700; border: none;">Title</th>
                      <th style="padding: 15px; font-weight: 700; border: none; text-align: right;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $count = 1;
                    mysqli_data_seek($result, 0);
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                      <td style="padding: 15px; font-weight: 600; color: #7f8c8d;"><?php echo $count++; ?></td>
                      <td style="padding: 15px;">
                        <?php if (!empty($row['service_image']) && $row['service_image'] != 'noimage.jpg'): ?>
                        <img src="post_img/<?php echo htmlspecialchars($row['service_image']); ?>"
                             style="width: 60px; height: 45px; object-fit: cover; border-radius: 8px; border: 2px solid #667eea;">
                        <?php else: ?>
                        <div style="width: 60px; height: 45px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                          <i class="fa fa-cogs"></i>
                        </div>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 15px; font-weight: 600;">
                        <?php echo htmlspecialchars($row['service_title']); ?>
                        <?php if (!empty($row['service_srt_desc'])): ?>
                        <br><small style="color: #7f8c8d; font-weight: 400;"><?php echo htmlspecialchars(substr($row['service_srt_desc'], 0, 50)) . (strlen($row['service_srt_desc']) > 50 ? '...' : ''); ?></small>
                        <?php endif; ?>
                      </td>
                      <td style="padding: 15px; text-align: right;">
                        <a href="services_crud.php?action=edit&id=<?php echo $row['service_id']; ?>"
                           class="btn btn-sm btn-warning" style="padding: 8px 15px; border-radius: 8px; font-weight: 600;">
                          <i class="fa fa-edit"></i> Edit
                        </a>
                        <a href="#" onclick="confirmDelete(<?php echo $row['service_id']; ?>, '<?php echo addslashes($row['service_title']); ?>')"
                           class="btn btn-sm btn-danger" style="padding: 8px 15px; border-radius: 8px; font-weight: 600;">
                          <i class="fa fa-trash"></i> Delete
                        </a>
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
.form-control:focus { border-color: #667eea !important; box-shadow: 0 0 0 3px rgba(102,126,234,0.1) !important; }
.btn { transition: all 0.3s ease; border: none; }
.btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
.table-hover tbody tr:hover { background: #f8f9fa; }
@keyframes slideInDown { from { opacity: 0; transform: translate3d(0, -20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
@media (max-width: 1024px) { div[style*="grid-template-columns: 1fr 2fr"] { grid-template-columns: 1fr !important; } }
@media (max-width: 768px) {
  .right_col { padding: 15px 10px !important; }
  .x_panel { padding: 20px 15px !important; }
  h2 { font-size: 1.3rem !important; }
}
</style>

<script>
function confirmDelete(id, name) {
  if (confirm('Delete "' + name + '"?\n\nThis cannot be undone.')) {
    window.location.href = 'services_crud.php?action=delete&id=' + id;
  }
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.alert-success').forEach(function(alert) {
    setTimeout(function() {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.5s';
      setTimeout(() => alert.style.display = 'none', 500);
    }, 3000);
  });
});
</script>
