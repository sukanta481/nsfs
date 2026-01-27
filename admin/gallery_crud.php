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
            <?php if ($edit_data): ?>
            <!-- Edit Single Image Mode -->
            <div class="x_title" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e6e9ed;">
              <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: #222;">
                <i class="fa fa-edit" style="color: #f39c12;"></i>
                Edit Gallery Image
              </h2>
            </div>

            <form method="post" action="" enctype="multipart/form-data">
              <input type="hidden" name="gallery_id" value="<?php echo $edit_data['gallery_id']; ?>">

              <?php if (!empty($edit_data['gallery_image']) && $edit_data['gallery_image'] != 'noimage.jpg'): ?>
              <div style="margin-bottom: 20px; text-align: center;">
                <img src="post_img/<?php echo htmlspecialchars($edit_data['gallery_image']); ?>"
                     style="width: 200px; height: 150px; object-fit: cover; border-radius: 10px; border: 3px solid #667eea;">
              </div>
              <?php endif; ?>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-image" style="color: #667eea;"></i> Replace Image
                </label>
                <input type="file" name="gallery_image" class="form-control" accept="image/*"
                       style="padding: 10px; border: 2px solid #e1e8ed; border-radius: 10px;">
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-tag" style="color: #667eea;"></i> Image Title <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text" name="gallery_name" required class="form-control"
                       value="<?php echo htmlspecialchars($edit_data['gallery_title']); ?>"
                       style="padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px;">
              </div>

              <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-folder" style="color: #667eea;"></i> Category ID
                </label>
                <input type="text" name="gallery_category" class="form-control"
                       value="<?php echo htmlspecialchars($edit_data['gallery_category_id']); ?>"
                       style="padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px;">
              </div>

              <div style="display: flex; gap: 12px;">
                <button type="submit" name="update_gallery" class="btn btn-warning"
                        style="flex: 1; padding: 12px 25px; font-weight: 700; border-radius: 10px;">
                  <i class="fa fa-save"></i> Update Image
                </button>
                <a href="gallery_crud.php" class="btn btn-secondary" style="padding: 12px 25px; font-weight: 700; border-radius: 10px;">
                  <i class="fa fa-times"></i> Cancel
                </a>
              </div>
            </form>

            <?php else: ?>
            <!-- Multi-Upload Mode -->
            <div class="x_title" style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #e6e9ed;">
              <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; color: #222;">
                <i class="fa fa-cloud-upload-alt" style="color: #4caf50;"></i>
                Upload Gallery Images
              </h2>
            </div>

            <form id="multiUploadForm" enctype="multipart/form-data">
              <!-- Drag & Drop Zone -->
              <div id="dropZone" style="border: 3px dashed #667eea; border-radius: 16px; padding: 40px 20px; text-align: center; background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%); cursor: pointer; transition: all 0.3s; margin-bottom: 20px;">
                <i class="fa fa-cloud-upload-alt" style="font-size: 3rem; color: #667eea; margin-bottom: 15px;"></i>
                <p style="margin: 0; font-size: 1.1rem; font-weight: 600; color: #333;">Drag & Drop Images Here</p>
                <p style="margin: 8px 0 15px 0; color: #7f8c8d; font-size: 0.9rem;">or click to browse files</p>
                <span style="display: inline-block; padding: 8px 20px; background: #667eea; color: white; border-radius: 25px; font-size: 0.9rem; font-weight: 600;">
                  <i class="fa fa-folder-open"></i> Select Multiple Images
                </span>
                <input type="file" id="galleryImages" name="gallery_images[]" multiple accept="image/*" 
                       style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
              </div>

              <!-- Preview Container -->
              <div id="previewContainer" style="display: none; margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-images" style="color: #667eea;"></i>
                  Selected Images (<span id="fileCount">0</span>)
                </label>
                <div id="imagePreviewGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; max-height: 200px; overflow-y: auto; padding: 10px; background: #f8fafc; border-radius: 10px;"></div>
                <button type="button" id="clearSelection" style="margin-top: 10px; padding: 6px 15px; background: #e74c3c; color: white; border: none; border-radius: 6px; font-size: 0.85rem; cursor: pointer;">
                  <i class="fa fa-times"></i> Clear All
                </button>
              </div>

              <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px;">
                  <i class="fa fa-folder" style="color: #667eea;"></i>
                  Category ID <span style="color: #7f8c8d; font-size: 0.85rem;">(Optional)</span>
                </label>
                <input type="number" name="gallery_category" id="galleryCategory" class="form-control" value="0"
                       placeholder="Enter category ID"
                       style="padding: 12px 15px; border: 2px solid #e1e8ed; border-radius: 10px;">
              </div>

              <button type="submit" id="uploadBtn" class="btn btn-success" disabled
                      style="width: 100%; padding: 14px 25px; font-weight: 700; border-radius: 10px; font-size: 1rem;">
                <i class="fa fa-upload"></i> Upload Images
              </button>
            </form>
            <?php endif; ?>
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
@keyframes slideInRight { from { opacity: 0; transform: translate3d(100%, 0, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
@keyframes slideOutRight { from { opacity: 1; transform: translate3d(0, 0, 0); } to { opacity: 0; transform: translate3d(100%, 0, 0); } }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
@media (max-width: 1024px) { div[style*="grid-template-columns: 1fr 2fr"] { grid-template-columns: 1fr !important; } }
@media (max-width: 768px) { .x_panel { padding: 20px 15px !important; } }

#dropZone.dragover {
  border-color: #4caf50 !important;
  background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%) !important;
  transform: scale(1.02);
}
#dropZone { position: relative; }

/* Upload Progress Notification */
.upload-notification {
  position: fixed;
  top: 20px;
  right: 20px;
  width: 380px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
  z-index: 10000;
  overflow: hidden;
  animation: slideInRight 0.4s ease;
}
.upload-notification.hiding {
  animation: slideOutRight 0.4s ease forwards;
}
.upload-notification-header {
  padding: 15px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.upload-notification-header h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
}
.upload-notification-header .close-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s;
}
.upload-notification-header .close-btn:hover {
  background: rgba(255,255,255,0.3);
}
.upload-notification-body {
  padding: 20px;
}
.upload-progress-bar {
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 12px;
}
.upload-progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 4px;
  transition: width 0.3s ease;
  width: 0%;
}
.upload-progress-bar-fill.uploading {
  animation: pulse 1s infinite;
}
.upload-status {
  font-size: 0.9rem;
  color: #666;
  margin-bottom: 15px;
}
.upload-file-list {
  max-height: 150px;
  overflow-y: auto;
  font-size: 0.85rem;
}
.upload-file-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 6px;
}
.upload-file-item i {
  font-size: 1rem;
}
.upload-file-item.success i { color: #4caf50; }
.upload-file-item.error i { color: #e74c3c; }
.upload-file-item.pending i { color: #f39c12; animation: pulse 1s infinite; }
.upload-file-item span { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>

<!-- Upload Progress Notification (Hidden by default) -->
<div id="uploadNotification" class="upload-notification" style="display: none;">
  <div class="upload-notification-header">
    <h4><i class="fa fa-cloud-upload-alt"></i> <span id="notificationTitle">Uploading Images</span></h4>
    <button class="close-btn" onclick="closeNotification()"><i class="fa fa-times"></i></button>
  </div>
  <div class="upload-notification-body">
    <div class="upload-progress-bar">
      <div id="progressBarFill" class="upload-progress-bar-fill"></div>
    </div>
    <div id="uploadStatus" class="upload-status">Preparing upload...</div>
    <div id="uploadFileList" class="upload-file-list"></div>
  </div>
</div>

<script>
function confirmDelete(id, name) {
  if (confirm('Delete "' + name + '"?\n\nThis cannot be undone.')) {
    window.location.href = 'gallery_crud.php?action=delete&id=' + id;
  }
}

// Auto-hide success alerts
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.alert-success').forEach(function(alert) {
    setTimeout(function() {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.5s';
      setTimeout(() => alert.style.display = 'none', 500);
    }, 3000);
  });

  // Multi-upload functionality
  const dropZone = document.getElementById('dropZone');
  const fileInput = document.getElementById('galleryImages');
  const previewContainer = document.getElementById('previewContainer');
  const previewGrid = document.getElementById('imagePreviewGrid');
  const fileCount = document.getElementById('fileCount');
  const uploadBtn = document.getElementById('uploadBtn');
  const clearBtn = document.getElementById('clearSelection');
  const uploadForm = document.getElementById('multiUploadForm');

  if (!dropZone) return; // Exit if in edit mode

  let selectedFiles = [];

  // Drag & Drop handlers
  ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
  });

  function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  ['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'));
  });

  ['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'));
  });

  dropZone.addEventListener('drop', function(e) {
    const files = e.dataTransfer.files;
    handleFiles(files);
  });

  fileInput.addEventListener('change', function() {
    handleFiles(this.files);
  });

  function handleFiles(files) {
    const imageFiles = Array.from(files).filter(f => f.type.startsWith('image/'));
    if (imageFiles.length === 0) {
      alert('Please select image files only.');
      return;
    }
    selectedFiles = imageFiles;
    updatePreview();
  }

  function updatePreview() {
    if (selectedFiles.length === 0) {
      previewContainer.style.display = 'none';
      uploadBtn.disabled = true;
      return;
    }

    previewContainer.style.display = 'block';
    uploadBtn.disabled = false;
    fileCount.textContent = selectedFiles.length;
    previewGrid.innerHTML = '';

    selectedFiles.forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = function(e) {
        const div = document.createElement('div');
        div.style.cssText = 'position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden;';
        div.innerHTML = `
          <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
          <button type="button" onclick="removeFile(${index})" style="position: absolute; top: 2px; right: 2px; width: 20px; height: 20px; background: rgba(231,76,60,0.9); color: white; border: none; border-radius: 50%; font-size: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-times"></i>
          </button>
        `;
        previewGrid.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
  }

  window.removeFile = function(index) {
    selectedFiles.splice(index, 1);
    updatePreview();
  };

  clearBtn.addEventListener('click', function() {
    selectedFiles = [];
    fileInput.value = '';
    updatePreview();
  });

  // Form submission with AJAX
  uploadForm.addEventListener('submit', function(e) {
    e.preventDefault();
    if (selectedFiles.length === 0) return;

    const formData = new FormData();
    selectedFiles.forEach(file => {
      formData.append('gallery_images[]', file);
    });
    formData.append('gallery_category', document.getElementById('galleryCategory').value);

    // Show notification
    showUploadNotification(selectedFiles);

    // Disable button
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';

    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
      if (e.lengthComputable) {
        const percent = Math.round((e.loaded / e.total) * 100);
        updateProgress(percent);
      }
    });

    xhr.addEventListener('load', function() {
      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText);
          showUploadComplete(response);
          
          // Reload page after 2 seconds to show new images
          setTimeout(() => {
            window.location.reload();
          }, 2000);
        } catch (err) {
          showUploadError('Invalid server response');
        }
      } else {
        showUploadError('Upload failed. Please try again.');
      }
    });

    xhr.addEventListener('error', function() {
      showUploadError('Network error. Please check your connection.');
    });

    xhr.open('POST', 'ajax_gallery_upload.php', true);
    xhr.send(formData);
  });
});

// Notification functions
function showUploadNotification(files) {
  const notification = document.getElementById('uploadNotification');
  const fileList = document.getElementById('uploadFileList');
  const progressFill = document.getElementById('progressBarFill');
  const status = document.getElementById('uploadStatus');
  const title = document.getElementById('notificationTitle');

  title.textContent = 'Uploading ' + files.length + ' Image' + (files.length > 1 ? 's' : '');
  status.textContent = 'Starting upload...';
  progressFill.style.width = '0%';
  progressFill.classList.add('uploading');

  fileList.innerHTML = '';
  files.forEach(file => {
    fileList.innerHTML += `
      <div class="upload-file-item pending">
        <i class="fa fa-circle-notch fa-spin"></i>
        <span>${file.name}</span>
      </div>
    `;
  });

  notification.style.display = 'block';
  notification.classList.remove('hiding');
}

function updateProgress(percent) {
  const progressFill = document.getElementById('progressBarFill');
  const status = document.getElementById('uploadStatus');
  
  progressFill.style.width = percent + '%';
  status.textContent = 'Uploading... ' + percent + '%';
}

function showUploadComplete(response) {
  const progressFill = document.getElementById('progressBarFill');
  const status = document.getElementById('uploadStatus');
  const fileList = document.getElementById('uploadFileList');
  const title = document.getElementById('notificationTitle');

  progressFill.style.width = '100%';
  progressFill.classList.remove('uploading');
  progressFill.style.background = response.success ? 'linear-gradient(90deg, #4caf50, #81c784)' : 'linear-gradient(90deg, #e74c3c, #ef5350)';
  
  title.innerHTML = response.success 
    ? '<i class="fa fa-check-circle"></i> Upload Complete' 
    : '<i class="fa fa-exclamation-circle"></i> Upload Issues';
  
  status.textContent = response.message;

  fileList.innerHTML = '';
  response.files.forEach(file => {
    const isSuccess = file.success;
    fileList.innerHTML += `
      <div class="upload-file-item ${isSuccess ? 'success' : 'error'}">
        <i class="fa fa-${isSuccess ? 'check-circle' : 'times-circle'}"></i>
        <span>${file.name}</span>
        ${!isSuccess ? '<small style="color: #e74c3c;">' + (file.error || 'Failed') + '</small>' : ''}
      </div>
    `;
  });
}

function showUploadError(message) {
  const progressFill = document.getElementById('progressBarFill');
  const status = document.getElementById('uploadStatus');
  const title = document.getElementById('notificationTitle');

  progressFill.classList.remove('uploading');
  progressFill.style.background = '#e74c3c';
  progressFill.style.width = '100%';
  
  title.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Upload Failed';
  status.textContent = message;

  // Reset button
  const uploadBtn = document.getElementById('uploadBtn');
  uploadBtn.disabled = false;
  uploadBtn.innerHTML = '<i class="fa fa-upload"></i> Upload Images';
}

function closeNotification() {
  const notification = document.getElementById('uploadNotification');
  notification.classList.add('hiding');
  setTimeout(() => {
    notification.style.display = 'none';
    notification.classList.remove('hiding');
  }, 400);
}
</script>
