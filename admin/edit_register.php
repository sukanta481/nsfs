<?php
require 'top_header.php';
require 'conn.php';

$shipping_details_id = intval($_REQUEST['shipping_details_id'] ?? 0);

// Fetch existing shipping entry
$sql = "SELECT * FROM tbl_shipping_details WHERE shipping_details_id = $shipping_details_id";
$res = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    echo '<div class="alert alert-danger">Shipping entry not found. <a href="register.php?type=list_register&lp=cu">Back to List</a></div>';
    exit;
}

// Prepare dropdowns
$companies = mysqli_query($conn, "SELECT * FROM tbl_company ORDER BY company_title ASC");
$cars      = mysqli_query($conn, "SELECT * FROM tbl_car ORDER BY car_number ASC");
$drivers   = mysqli_query($conn, "SELECT * FROM tbl_driver ORDER BY driver_name ASC");
$helpers   = mysqli_query($conn, "SELECT * FROM tbl_helper ORDER BY helper_name ASC");
?>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      <div class="right_col" role="main">
        <div class="x_panel">
          <div class="x_title">
            <h2>Edit Shipping Entry</h2>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">
            <form id="edit_register_form" action="includes/script/register_code.php" method="post" class="form-horizontal form-label-left" autocomplete="off">

              <!-- Rented Car selection -->
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Rented Car <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="radio" name="rented_car" value="1" <?php if($data['rented_car']==1){echo "checked";}?> onclick="change_rented_car('1');"> Yes
                  <input type="radio" name="rented_car" value="0" <?php if($data['rented_car']==0){echo "checked";}?> onclick="change_rented_car('0');"> No
                </div>
              </div>
              
              <!-- Personal Car Section (Dropdowns) -->
              <div id="personal_car_sec" <?php if($data['rented_car']!=1){?>style="display:block"<?php }else{?>style="display:none"<?php } ?>>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Car No <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <select name="car_id" id="car_id" class="form-control">
                      <option value="">Select</option>
                      <?php
                        mysqli_data_seek($cars, 0); // reset pointer
                        while ($car = mysqli_fetch_assoc($cars)) {
                          $selected = ($car['car_id'] == $data['car_id']) ? 'selected' : '';
                          echo "<option value='{$car['car_id']}' $selected>{$car['car_number']}</option>";
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Name <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <select name="driver_id" id="driver_id" class="form-control" onchange="get_driver_phone_no(this.value);">
                      <option value="">Select</option>
                      <?php
                        mysqli_data_seek($drivers, 0);
                        while ($driver = mysqli_fetch_assoc($drivers)) {
                          $selected = ($driver['driver_id'] == $data['driver_id']) ? 'selected' : '';
                          echo "<option value='{$driver['driver_id']}' $selected>{$driver['driver_name']}</option>";
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="item form-group" id="driver_number_sec">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Number <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="driver_number" id="driver_number" value="<?= htmlspecialchars($data['driver_number']); ?>" class="form-control"/>
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Name <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <select name="helper_id" id="helper_id" class="form-control" onchange="get_helper_phone_no(this.value);">
                      <option value="">Select</option>
                      <?php
                        mysqli_data_seek($helpers, 0);
                        while ($helper = mysqli_fetch_assoc($helpers)) {
                          $selected = ($helper['helper_id'] == $data['helper_id']) ? 'selected' : '';
                          echo "<option value='{$helper['helper_id']}' $selected>{$helper['helper_name']}</option>";
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="item form-group" id="helper_number_sec">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Number <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="helper_number" id="helper_number" value="<?= htmlspecialchars($data['helper_number']); ?>" class="form-control"/>
                  </div>
                </div>
              </div>

              <!-- Rented Car Section (Manual) -->
              <div id="rented_car_sec" <?php if($data['rented_car']==1){?>style="display:block"<?php }else{?>style="display:none"<?php } ?>>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Car No <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="car_number" value="<?= htmlspecialchars($data['car_number']); ?>" class="form-control"/>
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Name <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="driver_name" value="<?= htmlspecialchars($data['driver_name']); ?>" class="form-control"/>
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Number <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="driver_number_rent" value="<?= htmlspecialchars($data['driver_number']); ?>" class="form-control"/>
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Name <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="helper_name" value="<?= htmlspecialchars($data['helper_name']); ?>" class="form-control"/>
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Number <span class="required">*</span></label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="helper_number" value="<?= htmlspecialchars($data['helper_number']); ?>" class="form-control"/>
                  </div>
                </div>
              </div>

              <!-- Pickup Time -->
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Pickup Time</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="time" name="car_in_time" id="car_in_time" value="<?= htmlspecialchars($data['car_in_time']); ?>" class="form-control">
                </div>
              </div>

              <!-- Docket No -->
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Docket No <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" name="doc_no" value="<?= htmlspecialchars($data['doc_no']); ?>" class="form-control" required>
                </div>
              </div>

              <!-- Consignor company -->
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignor Company <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <select name="company_id" id="company_id" class="form-control" required>
                    <option value="">Select</option>
                    <?php
                      mysqli_data_seek($companies, 0);
                      while ($company = mysqli_fetch_assoc($companies)) {
                        $selected = ($company['company_id'] == $data['company_id']) ? 'selected' : '';
                        echo "<option value='{$company['company_id']}' $selected>{$company['company_title']}</option>";
                      }
                    ?>
                  </select>
                </div>
              </div>

              <!-- To Pay -->
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">To Pay <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <select name="pay_to" id="pay_to" class="form-control" required>
                    <option value="1" <?php if($data['pay_to']==1){echo "selected";}?>>Yes</option>
                    <option value="0" <?php if($data['pay_to']==0){echo "selected";}?>>No</option>
                  </select>
                </div>
              </div>

              <!-- Consignee Info -->
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignee Name <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" name="client_name" id="client_name" value="<?= htmlspecialchars($data['client_name']); ?>" required class="form-control">
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignee Phone <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="tel" name="client_phone" id="client_phone" value="<?= htmlspecialchars($data['client_phone']); ?>" required class="form-control">
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignee Address <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" name="client_address" id="client_address" value="<?= htmlspecialchars($data['client_address']); ?>" required class="form-control">
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Box <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="number" name="box" required value="<?= htmlspecialchars($data['box']); ?>" class="form-control" min="1">
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Weight (kg) <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="number" name="weight" required value="<?= htmlspecialchars($data['weight']); ?>" class="form-control" min="0" step="0.01">
                </div>
              </div>
              <input type="hidden" name="shipping_details_id" value="<?= $data['shipping_details_id']; ?>">
              <input type="hidden" name="status" value="<?= htmlspecialchars($data['status']); ?>">
              <div class="form-group">
                <div class="col-md-6 col-md-offset-3">
                  <button type="submit" name="edit_register" class="btn btn-success">Update</button>
                  <button type="button" class="btn btn-secondary" onclick="listregister();">Cancel</button>
                </div>
              </div>
            </form>
            <script>
              function change_rented_car(val) {
                if (val == "1") {
                  $("#personal_car_sec").hide();
                  $("#rented_car_sec").show();
                } else {
                  $("#personal_car_sec").show();
                  $("#rented_car_sec").hide();
                }
              }
              function listregister() {
                location.href = "register.php?type=list_register&lp=cu";
              }
              function get_driver_phone_no(v) {
                $.ajax({
                  type: "POST",
                  url: "ajax_get_driver_phone_no.php",
                  dataType: 'text',
                  data: "q=" + v,
                  success: function(phone) {
                    $("#driver_number").val(phone.trim());
                  }
                });
              }
              function get_helper_phone_no(v) {
                $.ajax({
                  type: "POST",
                  url: "ajax_get_helper_phone_no.php",
                  dataType: 'text',
                  data: "q=" + v,
                  success: function(phone) {
                    $("#helper_number").val(phone.trim());
                  }
                });
              }
            </script>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php require 'footer.php';?>
</body>
</html>
