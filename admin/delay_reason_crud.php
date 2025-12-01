<?php
/**
 * Delay Reasons Management - Modern CRUD System
 * Combined List, Add, Edit, Delete operations with modern UI/UX
 */

require 'check_auth.php';
require 'top_header.php';

// Handle CRUD operations
$message = '';
$message_type = '';

// Delete operation
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $reason_id = intval($_GET['id']);
    $delete_sql = "DELETE FROM tbl_delay_reasons WHERE reason_id = $reason_id";
    if (mysqli_query($conn, $delete_sql)) {
        $message = 'Delay reason deleted successfully';
        $message_type = 'success';
    } else {
        $message = 'Error deleting delay reason';
        $message_type = 'error';
    }
}

// Add operation
if (isset($_POST['save_delay_reason'])) {
    $reason_text = mysqli_real_escape_string($conn, trim($_POST['reason_text']));
    $reason_category = mysqli_real_escape_string($conn, trim($_POST['reason_category']));

    // Check for duplicate
    $check_sql = "SELECT * FROM tbl_delay_reasons WHERE reason_text = '$reason_text' AND reason_category = '$reason_category'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $message = 'A delay reason with this name already exists in this category';
        $message_type = 'error';
    } else {
        $insert_sql = "INSERT INTO tbl_delay_reasons (reason_text, reason_category, is_active) VALUES ('$reason_text', '$reason_category', 1)";
        if (mysqli_query($conn, $insert_sql)) {
            $message = 'Delay reason added successfully';
            $message_type = 'success';
        } else {
            $message = 'Error adding delay reason: ' . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// Update operation
if (isset($_POST['update_delay_reason'])) {
    $reason_id = intval($_POST['reason_id']);
    $reason_text = mysqli_real_escape_string($conn, trim($_POST['reason_text']));
    $reason_category = mysqli_real_escape_string($conn, trim($_POST['reason_category']));

    // Check for duplicate (excluding current record)
    $check_sql = "SELECT * FROM tbl_delay_reasons WHERE reason_text = '$reason_text' AND reason_category = '$reason_category' AND reason_id != $reason_id";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $message = 'A delay reason with this name already exists in this category';
        $message_type = 'error';
    } else {
        $update_sql = "UPDATE tbl_delay_reasons SET reason_text = '$reason_text', reason_category = '$reason_category' WHERE reason_id = $reason_id";
        if (mysqli_query($conn, $update_sql)) {
            $message = 'Delay reason updated successfully';
            $message_type = 'success';
        } else {
            $message = 'Error updating delay reason: ' . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// Fetch all delay reasons
$sql = "SELECT * FROM tbl_delay_reasons ORDER BY reason_category, reason_text ASC";
$result = mysqli_query($conn, $sql);

// Get edit data if editing
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $edit_sql = "SELECT * FROM tbl_delay_reasons WHERE reason_id = $edit_id";
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
            <i class="fa fa-clock-o" style="color: #667eea; margin-right: 10px;"></i>
            Delay Reasons Management
          </h2>
          <p style="margin: 8px 0 0 0; color: #7f8c8d; font-size: 1rem;">
            Manage delay reasons for shipment tracking
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
                <?php echo $edit_data ? 'Edit' : 'Add New'; ?> Delay Reason
              </h2>
            </div>

            <form method="post" action="" id="delayReasonForm" autocomplete="off">
              <?php if ($edit_data): ?>
              <input type="hidden" name="reason_id" value="<?php echo $edit_data['reason_id']; ?>">
              <?php endif; ?>

              <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                  <i class="fa fa-folder" style="color: #667eea;"></i>
                  Category <span style="color: #e74c3c;">*</span>
                </label>
                <select name="reason_category"
                        id="reason_category"
                        class="form-control"
                        required
                        style="padding: 12px 15px; font-size: 1.05rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s;">
                  <option value="">-- Select Category --</option>
                  <option value="Traffic" <?php echo ($edit_data && $edit_data['reason_category'] == 'Traffic') ? 'selected' : ''; ?>>Traffic</option>
                  <option value="Weather" <?php echo ($edit_data && $edit_data['reason_category'] == 'Weather') ? 'selected' : ''; ?>>Weather</option>
                  <option value="Vehicle" <?php echo ($edit_data && $edit_data['reason_category'] == 'Vehicle') ? 'selected' : ''; ?>>Vehicle</option>
                  <option value="Customer" <?php echo ($edit_data && $edit_data['reason_category'] == 'Customer') ? 'selected' : ''; ?>>Customer</option>
                  <option value="Other" <?php echo ($edit_data && $edit_data['reason_category'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
              </div>

              <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; color: #333; margin-bottom: 10px; font-size: 1.05rem;">
                  <i class="fa fa-tag" style="color: #667eea;"></i>
                  Delay Reason Name <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text"
                       name="reason_text"
                       id="reason_text"
                       class="form-control"
                       placeholder="Enter delay reason (e.g., Traffic congestion, Heavy rain)"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['reason_text']) : ''; ?>"
                       required
                       style="padding: 12px 15px; font-size: 1.05rem; border: 2px solid #e1e8ed; border-radius: 10px; transition: all 0.3s;">
              </div>

              <div style="display: flex; gap: 12px; margin-top: 25px;">
                <button type="submit"
                        name="<?php echo $edit_data ? 'update_delay_reason' : 'save_delay_reason'; ?>"
                        class="btn btn-<?php echo $edit_data ? 'warning' : 'success'; ?>"
                        style="flex: 1; padding: 12px 25px; font-size: 1.05rem; font-weight: 700; border-radius: 10px; transition: all 0.3s;">
                  <i class="fa fa-<?php echo $edit_data ? 'save' : 'plus'; ?>"></i>
                  <?php echo $edit_data ? 'Update' : 'Add'; ?> Reason
                </button>

                <?php if ($edit_data): ?>
                <a href="delay_reason_crud.php"
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
                All Delay Reasons (<?php echo mysqli_num_rows($result); ?>)
              </h2>
            </div>

            <div class="x_content">
              <?php if (mysqli_num_rows($result) == 0): ?>
              <div style="text-align: center; padding: 50px 20px; color: #95a5a6;">
                <i class="fa fa-inbox" style="font-size: 4rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <p style="font-size: 1.2rem; margin: 0;">No delay reasons found. Add one to get started!</p>
              </div>
              <?php else: ?>

              <div class="table-responsive">
                <table class="table table-hover" style="margin-bottom: 0;">
                  <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <tr>
                      <th style="padding: 15px; font-weight: 700; border: none;">#</th>
                      <th style="padding: 15px; font-weight: 700; border: none;">Category</th>
                      <th style="padding: 15px; font-weight: 700; border: none;">Delay Reason Name</th>
                      <th style="padding: 15px; font-weight: 700; border: none; text-align: right;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $count = 1;
                    mysqli_data_seek($result, 0);
                    while ($row = mysqli_fetch_assoc($result)):
                      // Set category color and icon
                      $category_colors = [
                        'Traffic' => ['color' => '#e67e22', 'icon' => 'fa-car'],
                        'Weather' => ['color' => '#3498db', 'icon' => 'fa-cloud'],
                        'Vehicle' => ['color' => '#e74c3c', 'icon' => 'fa-truck'],
                        'Customer' => ['color' => '#9b59b6', 'icon' => 'fa-user'],
                        'Other' => ['color' => '#95a5a6', 'icon' => 'fa-ellipsis-h']
                      ];
                      $cat_info = $category_colors[$row['reason_category']] ?? $category_colors['Other'];
                    ?>
                    <tr style="transition: all 0.2s; border-bottom: 1px solid #ecf0f1;">
                      <td style="padding: 15px; font-weight: 600; color: #7f8c8d;">
                        <?php echo $count++; ?>
                      </td>
                      <td style="padding: 15px;">
                        <span style="background: <?php echo $cat_info['color']; ?>; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                          <i class="fa <?php echo $cat_info['icon']; ?>"></i>
                          <?php echo htmlspecialchars($row['reason_category']); ?>
                        </span>
                      </td>
                      <td style="padding: 15px; font-size: 1.05rem; color: #2c3e50;">
                        <i class="fa fa-clock-o" style="color: #95a5a6; margin-right: 8px;"></i>
                        <?php echo htmlspecialchars($row['reason_text']); ?>
                      </td>
                      <td style="padding: 15px; text-align: right;">
                        <div style="display: inline-flex; gap: 8px;">
                          <a href="delay_reason_crud.php?action=edit&id=<?php echo $row['reason_id']; ?>"
                             class="btn btn-sm btn-warning"
                             style="padding: 8px 15px; border-radius: 8px; font-weight: 600; transition: all 0.2s;"
                             title="Edit">
                            <i class="fa fa-edit"></i> Edit
                          </a>
                          <a href="#"
                             onclick="confirmDelete(<?php echo $row['reason_id']; ?>, '<?php echo addslashes($row['reason_text']); ?>')"
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
}
</style>

<script>
function confirmDelete(id, name) {
  if (confirm('Are you sure you want to delete "' + name + '"?\n\nThis action cannot be undone.')) {
    window.location.href = 'delay_reason_crud.php?action=delete&id=' + id;
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

  // Focus on category dropdown
  const categorySelect = document.getElementById('reason_category');
  if (categorySelect && !categorySelect.value) {
    categorySelect.focus();
  } else {
    const textInput = document.getElementById('reason_text');
    if (textInput) {
      textInput.focus();
    }
  }
});

// Form validation
document.getElementById('delayReasonForm').addEventListener('submit', function(e) {
  const categorySelect = document.getElementById('reason_category');
  const textInput = document.getElementById('reason_text');
  const category = categorySelect.value;
  const text = textInput.value.trim();

  if (!category) {
    e.preventDefault();
    alert('Please select a category');
    categorySelect.focus();
    return false;
  }

  if (text.length < 3) {
    e.preventDefault();
    alert('Delay reason name must be at least 3 characters long');
    textInput.focus();
    return false;
  }

  return true;
});
</script>
