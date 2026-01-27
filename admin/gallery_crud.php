<?php
/**
 * Gallery Management - Modern CRUD System
 */

require 'check_auth.php';
require 'top_header.php';

$message = '';
$message_type = '';

// Delete operation
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $gallery_id = intval($_GET['id']);
    if (mysqli_query($conn, "DELETE FROM tbl_gallery WHERE gallery_id = $gallery_id")) {
        $message = 'Gallery item deleted successfully';
        $message_type = 'success';
    }
}

// Add operation
if (isset($_POST['save_gallery'])) {
    $gallery_title = mysqli_real_escape_string($conn, trim($_POST['gallery_name']));
    $gallery_category = mysqli_real_escape_string($conn, trim($_POST['gallery_category']));

    $image_name = 'noimage.jpg';
    if (!empty($_FILES['gallery_image']['name'])) {
        $image_name = time() . '_' . $_FILES['gallery_image']['name'];
        $upload_result = move_uploaded_file($_FILES['gallery_image']['tmp_name'], "post_img/" . $image_name);
        if (!$upload_result) {
            $message = 'Failed to upload image. Check folder permissions.';
            $message_type = 'error';
        }
    }

    if (empty($message)) {
        $sql = "INSERT INTO tbl_gallery (gallery_title, gallery_category_id, gallery_image, alise, status)
                VALUES ('$gallery_title', '" . intval($gallery_category) . "', '$image_name', '" . strtolower(str_replace(' ', '-', $gallery_title)) . "', 1)";

        if (mysqli_query($conn, $sql)) {
            $message = 'Gallery item added successfully';
            $message_type = 'success';
        } else {
            $message = 'Database error: ' . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// Update operation
if (isset($_POST['update_gallery'])) {
    $gallery_id = intval($_POST['gallery_id']);
    $gallery_title = mysqli_real_escape_string($conn, trim($_POST['gallery_name']));
    $gallery_category = mysqli_real_escape_string($conn, trim($_POST['gallery_category']));

    $sql = "UPDATE tbl_gallery SET gallery_title = '$gallery_title', gallery_category_id = '" . intval($gallery_category) . "' WHERE gallery_id = $gallery_id";

    if (mysqli_query($conn, $sql)) {
        if (!empty($_FILES['gallery_image']['name'])) {
            $image_name = time() . '_' . $_FILES['gallery_image']['name'];
            $upload_result = move_uploaded_file($_FILES['gallery_image']['tmp_name'], "post_img/" . $image_name);
            if ($upload_result) {
                mysqli_query($conn, "UPDATE tbl_gallery SET gallery_image = '$image_name' WHERE gallery_id = $gallery_id");
            }
        }
        $message = 'Gallery item updated successfully';
        $message_type = 'success';
    } else {
        $message = 'Database error: ' . mysqli_error($conn);
        $message_type = 'error';
    }
}

$result = mysqli_query($conn, "SELECT * FROM tbl_gallery ORDER BY gallery_id DESC");

$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_result = mysqli_query($conn, "SELECT * FROM tbl_gallery WHERE gallery_id = " . intval($_GET['id']));
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
            <i class="fa fa-images" style="color: #667eea; margin-right: 10px;"></i>
            Gallery Management
          </h2>
          <p style="margin: 8px 0 0 0; color: #7f8c8d; font-size: 1rem;">
            Manage gallery images displayed on the website
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
                <?php echo $edit_data ? 'Edit' : 'Add New'; ?> Gallery Image
              </h2>
            </div>

            <form method="post" action="" enctype="multipart/form-data">
              <?php if ($edit_data): ?>
              <input type="hidden" name="gallery_id" value="<?php echo $edit_data['gallery_id']; ?>">
              <?php endif; ?>

              <?php if ($edit_data && !empty($edit_data['gallery_image']) && $edit_data['gallery_image'] != 'noimage.jpg'): ?>
              <div style="margin-bottom: 20px; text-align: center;">
                <img src="post_img/<?php echo htmlspecialchars($edit_data['gallery_image']); ?>"
                     style="width: 200px; height: 150px; object-fit: cover; border-radius: 10px; border: 3px solid #667eea;">
              </div>
              <?php endif; ?>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-image" style="color: #667eea;"></i>
                  Gallery Image <?php echo !$edit_data ? '<span style="color: #e74c3c;">*</span>' : ''; ?>
                </label>
                <input type="file" name="gallery_image" class="form-control" accept="image/*"
                       <?php echo !$edit_data ? 'required' : ''; ?>
                       style="padding: 10px; border: 2px solid #e1e8ed; border-radius: 10px;">
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-tag" style="color: #667eea;"></i>
                  Image Title <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text" name="gallery_name" required class="form-control"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['gallery_title']) : ''; ?>"
                       placeholder="Enter image title"
                       style="padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px;">
              </div>

              <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-folder" style="color: #667eea;"></i>
                  Category
                </label>
                <input type="text" name="gallery_category" class="form-control"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['gallery_category_id']) : ''; ?>"
                       placeholder="e.g., 1, 2, 3 (Category ID)"
                       style="padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px;">
              </div>

              <div style="display: flex; gap: 12px;">
                <button type="submit" name="<?php echo $edit_data ? 'update_gallery' : 'save_gallery'; ?>"
                        class="btn btn-<?php echo $edit_data ? 'warning' : 'success'; ?>"
                        style="flex: 1; padding: 12px 25px; font-weight: 700; border-radius: 10px;">
                  <i class="fa fa-<?php echo $edit_data ? 'save' : 'plus'; ?>"></i>
                  <?php echo $edit_data ? 'Update' : 'Add'; ?> Image
                </button>
                <?php if ($edit_data): ?>
                <a href="gallery_crud.php" class="btn btn-secondary" style="padding: 12px 25px; font-weight: 700; border-radius: 10px;">
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
                Gallery Images (<?php echo mysqli_num_rows($result); ?>)
              </h2>
            </div>

            <?php if (mysqli_num_rows($result) == 0): ?>
            <div style="text-align: center; padding: 50px 20px; color: #95a5a6;">
              <i class="fa fa-images" style="font-size: 4rem; margin-bottom: 15px; opacity: 0.5;"></i>
              <p style="font-size: 1.2rem; margin: 0;">No images found. Add one to get started!</p>
            </div>
            <?php else: ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <?php if (!empty($row['gallery_image']) && $row['gallery_image'] != 'noimage.jpg'): ?>
                <img src="post_img/<?php echo htmlspecialchars($row['gallery_image']); ?>"
                     style="width: 100%; height: 150px; object-fit: cover;">
                <?php else: ?>
                <div style="width: 100%; height: 150px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                  <i class="fa fa-image"></i>
                </div>
                <?php endif; ?>
                <div style="padding: 15px;">
                  <h4 style="margin: 0 0 5px 0; font-size: 1rem; font-weight: 600; color: #2c3e50;">
                    <?php echo htmlspecialchars($row['gallery_title'] ?? ''); ?>
                  </h4>
                  <?php if (!empty($row['gallery_category_id'])): ?>
                  <p style="margin: 0 0 10px 0; font-size: 0.85rem; color: #7f8c8d;">
                    <i class="fa fa-folder" style="margin-right: 5px;"></i>Category <?php echo htmlspecialchars($row['gallery_category_id']); ?>
                  </p>
                  <?php endif; ?>
                  <div style="display: flex; gap: 8px; margin-top: 10px;">
                    <a href="gallery_crud.php?action=edit&id=<?php echo $row['gallery_id']; ?>"
                       class="btn btn-sm btn-warning" style="flex: 1; padding: 6px; font-size: 0.85rem; border-radius: 6px;">
                      <i class="fa fa-edit"></i> Edit
                    </a>
                    <a href="#" onclick="confirmDelete(<?php echo $row['gallery_id']; ?>, '<?php echo addslashes($row['gallery_title'] ?? ''); ?>')"
                       class="btn btn-sm btn-danger" style="flex: 1; padding: 6px; font-size: 0.85rem; border-radius: 6px;">
                      <i class="fa fa-trash"></i> Delete
                    </a>
                  </div>
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
    window.location.href = 'gallery_crud.php?action=delete&id=' + id;
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
