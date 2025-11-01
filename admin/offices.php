<?php
require 'top_header.php';
require 'conn.php';

// Handle Add/Edit via AJAX or POST
$msg = '';
if (isset($_POST['save_office'])) {
    $name = trim($_POST['office_name']);
    $address = trim($_POST['office_address']);
    $phone = trim($_POST['office_phone']);
    $person_name = trim($_POST['office_person_name'] ?? '');
    $id = $_POST['office_id'] ?? '';

    // Check if column exists, if not add it
    $check_col = mysqli_query($conn, "SHOW COLUMNS FROM tbl_offices LIKE 'office_person_name'");
    if (mysqli_num_rows($check_col) == 0) {
        mysqli_query($conn, "ALTER TABLE tbl_offices ADD COLUMN office_person_name VARCHAR(255) DEFAULT NULL AFTER office_phone");
    }

    if ($id) {
        $stmt = $conn->prepare("UPDATE tbl_offices SET office_name=?, office_address=?, office_phone=?, office_person_name=? WHERE office_id=?");
        $stmt->bind_param("ssssi", $name, $address, $phone, $person_name, $id);
        $stmt->execute();
        $msg = "Office updated successfully!";
    } else {
        $stmt = $conn->prepare("INSERT INTO tbl_offices (office_name, office_address, office_phone, office_person_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $address, $phone, $person_name);
        $stmt->execute();
        $msg = "Office added successfully!";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delid = intval($_GET['delete']);
    $conn->query("DELETE FROM tbl_offices WHERE office_id=$delid LIMIT 1");
    $msg = "Office deleted!";
    header("Location: offices.php?msg=deleted");
    exit;
}

// Get all offices for listing
$res = $conn->query("SELECT * FROM tbl_offices ORDER BY office_id DESC");

?>
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php'; ?>
      <?php require 'header_banner.php'; ?>
      <div class="right_col" role="main" style="background: #f8fafc; min-height:100vh;">
        <div class="x_panel">
          <div class="x_title">
      <h2>Offices</h2>
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

      <!-- Add New Office Button -->
      <div style="margin-bottom:20px;">
        <button class="btn btn-success" data-toggle="modal" data-target="#officeModal" onclick="resetForm()">
          <i class="fa fa-plus-circle"></i> Add New Office
        </button>
      </div>

      <div style="overflow-x:auto;">
        <table class="table table-bordered table-striped" style="background:#fff;">
          <thead>
            <tr>
              <th>#</th>
              <th>Office Name</th>
              <th>Person Name</th>
              <th>Address</th>
              <th>Phone</th>
              <th width="135"></th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; while($row = $res->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['office_name']) ?></td>
                <td><?= htmlspecialchars($row['office_person_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['office_address']) ?></td>
                <td><?= htmlspecialchars($row['office_phone']) ?></td>
                <td>
                  <button class="btn btn-info btn-xs" onclick="editOffice(<?= $row['office_id'] ?>, '<?= htmlspecialchars($row['office_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['office_person_name'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($row['office_address'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['office_phone'], ENT_QUOTES) ?>')">
                    <i class="fa fa-edit"></i> Edit
                  </button>
                  <a href="offices.php?delete=<?= $row['office_id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete this office?');">
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

<!-- Edit/Add Office Modal -->
<div class="modal fade" id="officeModal" tabindex="-1" role="dialog" aria-labelledby="officeModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="officeModalLabel">
          <i class="fa fa-building"></i> <span id="modalTitle">Add New Office</span>
        </h4>
      </div>
      <form method="post" id="officeForm" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" name="office_id" id="office_id">
          
          <div class="form-group">
            <label>Office Name <span style="color:red">*</span></label>
            <input type="text" name="office_name" id="office_name" required class="form-control" placeholder="Enter office name">
          </div>
          
          <div class="form-group">
            <label>Person Name</label>
            <input type="text" name="office_person_name" id="office_person_name" class="form-control" placeholder="e.g., MANIK DA">
          </div>
          
          <div class="form-group">
            <label>Address <span style="color:red">*</span></label>
            <input type="text" name="office_address" id="office_address" required class="form-control" placeholder="Enter office address">
          </div>
          
          <div class="form-group">
            <label>Phone <span style="color:red">*</span></label>
            <input type="text" name="office_phone" id="office_phone" required class="form-control" placeholder="Enter phone number">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <i class="fa fa-times"></i> Cancel
          </button>
          <button type="submit" name="save_office" class="btn btn-success">
            <i class="fa fa-save"></i> <span id="submitBtn">Save Office</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetForm() {
  document.getElementById('officeForm').reset();
  document.getElementById('office_id').value = '';
  document.getElementById('modalTitle').textContent = 'Add New Office';
  document.getElementById('submitBtn').textContent = 'Save Office';
}

function editOffice(id, name, person, address, phone) {
  document.getElementById('office_id').value = id;
  document.getElementById('office_name').value = name;
  document.getElementById('office_person_name').value = person;
  document.getElementById('office_address').value = address;
  document.getElementById('office_phone').value = phone;
  document.getElementById('modalTitle').textContent = 'Edit Office';
  document.getElementById('submitBtn').textContent = 'Update Office';
  $('#officeModal').modal('show');
}
</script>

   <?php require 'footer.php'; ?>
    </div>
  </div>
</body>

<style>
/* Match main panel padding/style */
.dashboard-title {font-weight:800;letter-spacing:-.5px;color:#222;}
.table {font-size:1.15rem;}
.table th {font-size:1.2rem; font-weight:700;}
.table td {font-size:1.1rem; padding:12px 8px;}
.btn-xs {padding:5px 12px;font-size:1rem;}
.x_panel {background: #fff; border-radius: 16px; box-shadow: 0 8px 32px 0 rgba(78,110,255,0.07); padding: 30px 22px 24px 22px;}
.x_title {border-bottom: 1.5px solid #e6e9ed; margin-bottom: 25px; padding-bottom: 5px;}
.x_title h2 {margin: 0; font-size: 1.6rem; font-weight: 800; letter-spacing: -.5px;}
.x_content {padding: 0;}
.modal-header {background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 5px 5px 0 0;}
.modal-header .close {color: white; opacity: 0.8; font-size:2rem;}
.modal-header .close:hover {opacity: 1;}
.modal-title {font-weight: 700; font-size:1.4rem;}
.modal-body label {font-size:1.1rem; font-weight:600;}
.modal-body .form-control {font-size:1.1rem; padding:10px;}
.btn {font-size:1.05rem; padding:8px 16px;}
@media (max-width:900px){
    .dashboard-title { font-size:1.19rem; }
    .right_col { padding:6px 2px 40px 2px; }
    table { font-size: 1rem; }
    .x_panel {padding:12px 4px;}
}
</style>
