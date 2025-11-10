<?php
require 'top_header.php';
require 'conn.php';

// Handle Add/Edit via AJAX or POST
$msg = '';
if (isset($_POST['save_consignor'])) {
    $company_title = trim($_POST['company_title']);
    $alise = trim($_POST['alise']);
    $company_address = trim($_POST['company_address']);
    $company_phone = trim($_POST['company_phone']);
    $company_email = trim($_POST['company_email'] ?? '');
    $id = $_POST['company_id'] ?? '';

    if ($id) {
        $stmt = $conn->prepare("UPDATE tbl_company SET company_title=?, alise=?, company_address=?, company_phone=?, company_email=? WHERE company_id=?");
        $stmt->bind_param("sssssi", $company_title, $alise, $company_address, $company_phone, $company_email, $id);
        $stmt->execute();
        $msg = "Consignor updated successfully!";
    } else {
        // For new entries, set default image
        $stmt = $conn->prepare("INSERT INTO tbl_company (company_title, alise, company_address, company_phone, company_email, company_image) VALUES (?, ?, ?, ?, ?, 'default.png')");
        $stmt->bind_param("sssss", $company_title, $alise, $company_address, $company_phone, $company_email);
        $stmt->execute();
        $msg = "Consignor added successfully!";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delid = intval($_GET['delete']);
    $conn->query("DELETE FROM tbl_company WHERE company_id=$delid LIMIT 1");
    $msg = "Consignor deleted!";
    header("Location: consignors.php?msg=deleted");
    exit;
}

// Get all consignors for listing
$res = $conn->query("SELECT * FROM tbl_company ORDER BY company_id DESC");

?>
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php'; ?>
      <?php require 'header_banner.php'; ?>
      <div class="right_col" role="main" style="background: #f8fafc; min-height:100vh;">
        <div class="x_panel">
          <div class="x_title">
            <h2><i class="fa fa-building"></i> All Consignors</h2>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">

            <?php if($msg): ?>
              <div class="alert alert-success" style="margin-bottom:20px;">
                <i class="fa fa-check-circle"></i> <?= $msg ?>
              </div>
              <script>
              setTimeout(function() {
                $(".alert-success").fadeOut(1000);
              }, 2500);
              </script>
            <?php endif; ?>

            <!-- Add New Consignor Button -->
            <div style="margin-bottom:20px;">
              <button class="btn btn-success" data-toggle="modal" data-target="#consignorModal" onclick="resetForm()">
                <i class="fa fa-plus-circle"></i> Add New Consignor
              </button>
            </div>

            <div style="overflow-x:auto;">
              <table class="table table-bordered table-striped" id="consignorTable" style="background:#fff;">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Company Name</th>
                    <th>Alias</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th width="135">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i=1; while($row = $res->fetch_assoc()): ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <td><strong><?= htmlspecialchars($row['company_title']) ?></strong></td>
                      <td><?= htmlspecialchars($row['alise']) ?></td>
                      <td><?= htmlspecialchars($row['company_address']) ?></td>
                      <td><?= htmlspecialchars($row['company_phone']) ?></td>
                      <td><?= htmlspecialchars($row['company_email'] ?? '') ?></td>
                      <td>
                        <button class="btn btn-info btn-xs" onclick="editConsignor(<?= $row['company_id'] ?>, '<?= htmlspecialchars($row['company_title'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['alise'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['company_address'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['company_phone'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['company_email'] ?? '', ENT_QUOTES) ?>')">
                          <i class="fa fa-edit"></i> Edit
                        </button>
                        <a href="consignors.php?delete=<?= $row['company_id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete this consignor?');">
                          <i class="fa fa-trash"></i> Delete
                        </a>
                      </td>
                    </tr>
                  <?php endwhile ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

<!-- Edit/Add Consignor Modal -->
<div class="modal fade" id="consignorModal" tabindex="-1" role="dialog" aria-labelledby="consignorModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="consignorModalLabel">
          <i class="fa fa-building"></i> <span id="modalTitle">Add New Consignor</span>
        </h4>
      </div>
      <form method="post" id="consignorForm" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" name="company_id" id="company_id">

          <div class="form-group">
            <label>Company Name <span style="color:red">*</span></label>
            <input type="text" name="company_title" id="company_title" required class="form-control" placeholder="Enter company name">
          </div>

          <div class="form-group">
            <label>Alias <span style="color:red">*</span></label>
            <input type="text" name="alise" id="alise" required class="form-control" placeholder="Enter alias/short name">
          </div>

          <div class="form-group">
            <label>Address <span style="color:red">*</span></label>
            <textarea name="company_address" id="company_address" required class="form-control" placeholder="Enter company address" rows="2"></textarea>
          </div>

          <div class="form-group">
            <label>Phone <span style="color:red">*</span></label>
            <input type="text" name="company_phone" id="company_phone" required class="form-control" placeholder="Enter phone number">
          </div>

          <div class="form-group">
            <label>Email</label>
            <input type="email" name="company_email" id="company_email" class="form-control" placeholder="Enter email address (optional)">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <i class="fa fa-times"></i> Cancel
          </button>
          <button type="submit" name="save_consignor" class="btn btn-success">
            <i class="fa fa-save"></i> <span id="submitBtn">Save Consignor</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetForm() {
  document.getElementById('consignorForm').reset();
  document.getElementById('company_id').value = '';
  document.getElementById('modalTitle').textContent = 'Add New Consignor';
  document.getElementById('submitBtn').textContent = 'Save Consignor';
}

function editConsignor(id, title, alise, address, phone, email) {
  document.getElementById('company_id').value = id;
  document.getElementById('company_title').value = title;
  document.getElementById('alise').value = alise;
  document.getElementById('company_address').value = address;
  document.getElementById('company_phone').value = phone;
  document.getElementById('company_email').value = email;
  document.getElementById('modalTitle').textContent = 'Edit Consignor';
  document.getElementById('submitBtn').textContent = 'Update Consignor';
  $('#consignorModal').modal('show');
}

// Initialize DataTable for better UX
$(document).ready(function() {
  if ($.fn.DataTable) {
    $('#consignorTable').DataTable({
      "pageLength": 25,
      "order": [[0, "desc"]],
      "language": {
        "search": "Search consignors:",
        "lengthMenu": "Show _MENU_ consignors per page",
        "info": "Showing _START_ to _END_ of _TOTAL_ consignors",
        "infoEmpty": "No consignors available",
        "zeroRecords": "No matching consignors found"
      }
    });
  }
});
</script>

   <?php require 'footer.php'; ?>
    </div>
  </div>
</body>

<style>
/* Modern styling matching office page */
.x_panel {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 8px 32px 0 rgba(78,110,255,0.07);
  padding: 30px 22px 24px 22px;
}

.x_title {
  border-bottom: 1.5px solid #e6e9ed;
  margin-bottom: 25px;
  padding-bottom: 5px;
}

.x_title h2 {
  margin: 0;
  font-size: 1.6rem;
  font-weight: 800;
  letter-spacing: -.5px;
  color: #222;
}

.x_content {
  padding: 0;
}

.table {
  font-size: 1.15rem;
}

.table th {
  font-size: 1.2rem;
  font-weight: 700;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.table td {
  font-size: 1.1rem;
  padding: 12px 8px;
  vertical-align: middle;
}

.btn-xs {
  padding: 5px 12px;
  font-size: 1rem;
}

.modal-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 5px 5px 0 0;
}

.modal-header .close {
  color: white;
  opacity: 0.8;
  font-size: 2rem;
}

.modal-header .close:hover {
  opacity: 1;
}

.modal-title {
  font-weight: 700;
  font-size: 1.4rem;
}

.modal-body label {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 5px;
}

.modal-body .form-control {
  font-size: 1.1rem;
  padding: 10px;
}

.btn {
  font-size: 1.05rem;
  padding: 8px 16px;
}

.alert {
  font-size: 1.1rem;
  padding: 15px 20px;
  border-radius: 8px;
}

/* DataTable styling */
.dataTables_wrapper .dataTables_filter input {
  border: 2px solid #e0e0e0;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 1.05rem;
}

.dataTables_wrapper .dataTables_length select {
  border: 2px solid #e0e0e0;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 1.05rem;
}

/* Responsive Design */
@media (max-width: 900px) {
  .right_col {
    padding: 6px 2px 40px 2px;
  }

  table {
    font-size: 1rem;
  }

  .x_panel {
    padding: 12px 4px;
  }

  .x_title h2 {
    font-size: 1.4rem;
  }
}

@media (max-width: 768px) {
  table {
    font-size: 0.95rem;
  }

  table th,
  table td {
    padding: 8px !important;
  }

  .btn {
    font-size: 1rem;
    padding: 6px 12px;
  }

  .modal-title {
    font-size: 1.2rem;
  }

  .modal-body label {
    font-size: 1rem;
  }

  .modal-body .form-control {
    font-size: 1rem;
  }

  .x_title h2 {
    font-size: 1.3rem;
  }
}

@media (max-width: 576px) {
  table {
    font-size: 0.85rem;
  }

  table th,
  table td {
    padding: 6px !important;
  }

  .btn {
    font-size: 0.9rem;
    padding: 5px 10px;
    min-width: 70px;
  }

  .btn-xs {
    padding: 4px 8px;
    font-size: 0.85rem;
  }

  .modal-dialog {
    margin: 10px !important;
    max-width: calc(100% - 20px) !important;
  }

  .modal-title {
    font-size: 1.1rem;
  }

  .modal-body label {
    font-size: 0.95rem;
  }

  .modal-body .form-control {
    font-size: 0.95rem;
    padding: 8px;
  }

  .modal-header .close {
    font-size: 1.7rem;
  }

  .x_title h2 {
    font-size: 1.15rem;
  }

  .alert {
    font-size: 1rem;
    padding: 12px 15px;
  }
}

@media (max-width: 400px) {
  .x_panel {
    padding: 10px 6px;
  }

  table {
    font-size: 0.8rem;
  }

  .btn {
    font-size: 0.85rem;
    padding: 4px 8px;
  }

  .x_title h2 {
    font-size: 1.05rem;
  }
}
</style>
