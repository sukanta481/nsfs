<?php
/**
 * Site Features Management - Modern CRUD System
 */

require 'check_auth.php';
require 'top_header.php';

$message = '';
$message_type = '';

// Delete operation
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $feature_id = intval($_GET['id']);
    if (mysqli_query($conn, "DELETE FROM tbl_site_feature WHERE feature_id = $feature_id")) {
        $message = 'Site feature deleted successfully';
        $message_type = 'success';
    }
}

// Add operation
if (isset($_POST['save_feature'])) {
    $feature_title = mysqli_real_escape_string($conn, trim($_POST['feature_title']));
    $feature_desc = mysqli_real_escape_string($conn, trim($_POST['feature_desc']));

    $image_name = 'noimage.jpg';
    if (!empty($_FILES['feature_image']['name'])) {
        $image_name = time() . '_' . $_FILES['feature_image']['name'];
        move_uploaded_file($_FILES['feature_image']['tmp_name'], "post_img/" . $image_name);
    }

    $sql = "INSERT INTO tbl_site_feature (feature_title, feature_desc, feature_image, alise)
            VALUES ('$feature_title', '$feature_desc', '$image_name', '" . strtolower(str_replace(' ', '-', $feature_title)) . "')";

    if (mysqli_query($conn, $sql)) {
        $message = 'Site feature added successfully';
        $message_type = 'success';
    }
}

// Update operation
if (isset($_POST['update_feature'])) {
    $feature_id = intval($_POST['feature_id']);
    $feature_title = mysqli_real_escape_string($conn, trim($_POST['feature_title']));
    $feature_desc = mysqli_real_escape_string($conn, trim($_POST['feature_desc']));

    $sql = "UPDATE tbl_site_feature SET feature_title = '$feature_title', feature_desc = '$feature_desc' WHERE feature_id = $feature_id";

    if (mysqli_query($conn, $sql)) {
        if (!empty($_FILES['feature_image']['name'])) {
            $image_name = time() . '_' . $_FILES['feature_image']['name'];
            move_uploaded_file($_FILES['feature_image']['tmp_name'], "post_img/" . $image_name);
            mysqli_query($conn, "UPDATE tbl_site_feature SET feature_image = '$image_name' WHERE feature_id = $feature_id");
        }
        $message = 'Site feature updated successfully';
        $message_type = 'success';
    }
}

$result = mysqli_query($conn, "SELECT * FROM tbl_site_feature ORDER BY feature_id ASC");

$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_result = mysqli_query($conn, "SELECT * FROM tbl_site_feature WHERE feature_id = " . intval($_GET['id']));
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
            <i class="fa fa-star" style="color: #667eea; margin-right: 10px;"></i>
            Site Features Management
          </h2>
          <p style="margin: 8px 0 0 0; color: #7f8c8d; font-size: 1rem;">
            Manage key features displayed on homepage
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
                <?php echo $edit_data ? 'Edit' : 'Add New'; ?> Feature
              </h2>
            </div>

            <form method="post" action="" enctype="multipart/form-data">
              <?php if ($edit_data): ?>
              <input type="hidden" name="feature_id" value="<?php echo $edit_data['feature_id']; ?>">
              <?php endif; ?>

              <?php if ($edit_data && !empty($edit_data['feature_image']) && $edit_data['feature_image'] != 'noimage.jpg'): ?>
              <div style="margin-bottom: 20px; text-align: center;">
                <img src="post_img/<?php echo htmlspecialchars($edit_data['feature_image']); ?>"
                     style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 3px solid #667eea;">
              </div>
              <?php endif; ?>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-image" style="color: #667eea;"></i>
                  Feature Icon <?php echo !$edit_data ? '<span style="color: #e74c3c;">*</span>' : ''; ?>
                </label>
                <input type="file" name="feature_image" class="form-control" accept="image/*"
                       <?php echo !$edit_data ? 'required' : ''; ?>
                       style="padding: 10px; border: 2px solid #e1e8ed; border-radius: 10px;">
                <small style="color: #7f8c8d;">Recommended: Square icon, 100x100px</small>
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-heading" style="color: #667eea;"></i>
                  Feature Title <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text" name="feature_title" required class="form-control"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['feature_title']) : ''; ?>"
                       placeholder="e.g., Fast Delivery, 24/7 Support"
                       style="padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px;">
              </div>

              <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-align-left" style="color: #667eea;"></i>
                  Feature Description
                </label>
                <textarea name="feature_desc" rows="4" class="form-control"
                          placeholder="Brief description of the feature"
                          style="padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px; resize: vertical;"><?php echo $edit_data ? htmlspecialchars($edit_data['feature_desc']) : ''; ?></textarea>
              </div>

              <div style="display: flex; gap: 12px;">
                <button type="submit" name="<?php echo $edit_data ? 'update_feature' : 'save_feature'; ?>"
                        class="btn btn-<?php echo $edit_data ? 'warning' : 'success'; ?>"
                        style="flex: 1; padding: 12px 25px; font-weight: 700; border-radius: 10px;">
                  <i class="fa fa-<?php echo $edit_data ? 'save' : 'plus'; ?>"></i>
                  <?php echo $edit_data ? 'Update' : 'Add'; ?> Feature
                </button>
                <?php if ($edit_data): ?>
                <a href="site_features_crud.php" class="btn btn-secondary" style="padding: 12px 25px; font-weight: 700; border-radius: 10px;">
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
                Site Features (<?php echo mysqli_num_rows($result); ?>)
              </h2>
            </div>

            <?php if (mysqli_num_rows($result) == 0): ?>
            <div style="text-align: center; padding: 50px 20px; color: #95a5a6;">
              <i class="fa fa-star" style="font-size: 4rem; margin-bottom: 15px; opacity: 0.5;"></i>
              <p style="font-size: 1.2rem; margin: 0;">No features found. Add one to get started!</p>
            </div>
            <?php else: ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s; text-align: center;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <?php if (!empty($row['feature_image']) && $row['feature_image'] != 'noimage.jpg'): ?>
                <img src="post_img/<?php echo htmlspecialchars($row['feature_image']); ?>"
                     style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 15px;">
                <?php else: ?>
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; margin: 0 auto 15px;">
                  <i class="fa fa-star"></i>
                </div>
                <?php endif; ?>
                <h4 style="margin: 0 0 10px 0; font-size: 1.1rem; font-weight: 600; color: #2c3e50;">
                  <?php echo htmlspecialchars($row['feature_title']); ?>
                </h4>
                <?php if (!empty($row['feature_desc'])): ?>
                <p style="margin: 0 0 15px 0; font-size: 0.9rem; color: #7f8c8d;">
                  <?php echo htmlspecialchars(substr($row['feature_desc'], 0, 60)) . (strlen($row['feature_desc']) > 60 ? '...' : ''); ?>
                </p>
                <?php endif; ?>
                <div style="display: flex; gap: 8px; margin-top: 15px;">
                  <a href="site_features_crud.php?action=edit&id=<?php echo $row['feature_id']; ?>"
                     class="btn btn-sm btn-warning" style="flex: 1; padding: 8px; font-size: 0.9rem; border-radius: 6px;">
                    <i class="fa fa-edit"></i> Edit
                  </a>
                  <a href="#" onclick="confirmDelete(<?php echo $row['feature_id']; ?>, '<?php echo addslashes($row['feature_title']); ?>')"
                     class="btn btn-sm btn-danger" style="flex: 1; padding: 8px; font-size: 0.9rem; border-radius: 6px;">
                    <i class="fa fa-trash"></i>
                  </a>
                </div>
              </div>
              <?php endwhile; ?>
            </div>
            <?php endif; ?>
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
@keyframes slideInDown { from { opacity: 0; transform: translate3d(0, -20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
@media (max-width: 1024px) { div[style*="grid-template-columns: 1fr 2fr"] { grid-template-columns: 1fr !important; } }
@media (max-width: 768px) { .x_panel { padding: 20px 15px !important; } }
</style>

<script>
function confirmDelete(id, name) {
  if (confirm('Delete "' + name + '"?\n\nThis cannot be undone.')) {
    window.location.href = 'site_features_crud.php?action=delete&id=' + id;
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
