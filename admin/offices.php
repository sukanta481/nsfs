<?php
require 'top_header.php';
require 'conn.php';

// Handle Add/Edit
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
}

// Handle Edit load
$edit = null;
if (isset($_GET['edit'])) {
    $editid = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM tbl_offices WHERE office_id=$editid");
    $edit = $res->fetch_assoc();
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
          <?= $msg ?>
        </div>
        <script>
        setTimeout(function() {
          $(".alert-success").fadeOut(1000);
        }, 2500);
        </script>
      <?php endif; ?>

      <form method="post" autocomplete="off" style="display:flex;gap:15px;margin-bottom:18px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="office_id" value="<?= $edit['office_id'] ?? '' ?>">
        <div>
          <label>Office Name <span style="color:red">*</span></label>
          <input type="text" name="office_name" required class="form-control" value="<?= htmlspecialchars($edit['office_name'] ?? '') ?>" style="min-width:160px;">
        </div>
        <div>
          <label>Person Name</label>
          <input type="text" name="office_person_name" class="form-control" value="<?= htmlspecialchars($edit['office_person_name'] ?? '') ?>" style="min-width:160px;" placeholder="e.g., MANIK DA">
        </div>
        <div>
          <label>Address <span style="color:red">*</span></label>
          <input type="text" name="office_address" required class="form-control" value="<?= htmlspecialchars($edit['office_address'] ?? '') ?>" style="min-width:220px;">
        </div>
        <div>
          <label>Phone <span style="color:red">*</span></label>
          <input type="text" name="office_phone" required class="form-control" value="<?= htmlspecialchars($edit['office_phone'] ?? '') ?>" style="min-width:110px;">
        </div>
        <div>
          <button class="btn btn-success" name="save_office" style="margin-bottom:2px;">
            <?= $edit ? 'Update' : 'Add' ?>
          </button>
          <?php if($edit): ?>
            <a href="offices.php" class="btn btn-secondary" style="margin-left:5px;">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
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
                  <a href="offices.php?edit=<?= $row['office_id'] ?>" class="btn btn-info btn-xs">Edit</a>
                  <a href="offices.php?delete=<?= $row['office_id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Delete this office?');">Delete</a>
                </td>
              </tr>
            <?php endwhile ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
   <?php require 'footer.php'; ?>
    </div>
  </div>
</body>

<style>
/* Match main panel padding/style */
.dashboard-title {font-weight:800;letter-spacing:-.5px;color:#222;}
.table {font-size:1rem;}
.btn-xs {padding:3px 10px;font-size:.95em;}
.x_panel {background: #fff; border-radius: 16px; box-shadow: 0 8px 32px 0 rgba(78,110,255,0.07); padding: 30px 22px 24px 22px;}
.x_title {border-bottom: 1.5px solid #e6e9ed; margin-bottom: 25px; padding-bottom: 5px;}
.x_title h2 {margin: 0; font-size: 1.35rem; font-weight: 800; letter-spacing: -.5px;}
.x_content {padding: 0;}
@media (max-width:900px){
    .dashboard-title { font-size:1.19rem; }
    .right_col { padding:6px 2px 40px 2px; }
    table { font-size: .95rem; }
    .x_panel {padding:12px 4px;}
}
</style>
