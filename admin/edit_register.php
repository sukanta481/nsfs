<?php
require 'top_header.php';
require 'conn.php';
$shipping_details_id = intval($_REQUEST['shipping_details_id'] ?? 0);

$sql = "SELECT * FROM tbl_shipping_details WHERE shipping_details_id = $shipping_details_id";
$res = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    echo '<div class="alert alert-danger">Shipping entry not found. <a href="register.php?type=list_register&lp=cu">Back to List</a></div>';
    exit;
}
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
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Rented Car :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="radio" name="rented_car" value="1" <?php if($data['rented_car']==1){ echo "checked";}?> onclick="change_rented_car('1');"> Yes
                  <input type="radio" name="rented_car" value="0" <?php if($data['rented_car']==0){ echo "checked";}?> onclick="change_rented_car('0');"> No
                </div>
              </div>
              <script>
                function change_rented_car(v) {
                  if (v == "1") {
                    $("#personal_car_sec").hide();
                    $("#rented_car_sec").show();
                  } else {
                    $("#personal_car_sec").show();
                    $("#rented_car_sec").hide();
                  }
                }
              </script>
              <!-- Rented Car Section -->
              <div id="rented_car_sec" <?php if($data['rented_car']==1){?>style="display:block"<?php }else{?>style="display:none"<?php } ?>>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Car No :</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="car_number" value="<?= htmlspecialchars($data['car_number']); ?>" class="form-control col-md-7 col-xs-12" />
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Name :</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="driver_name" value="<?= htmlspecialchars($data['driver_name']); ?>" class="form-control col-md-7 col-xs-12" />
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Number :</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="driver_number_rent" value="<?= htmlspecialchars($data['driver_number']); ?>" class="form-control col-md-7 col-xs-12" />
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Name :</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="helper_name" value="<?= htmlspecialchars($data['helper_name']); ?>" class="form-control col-md-7 col-xs-12" />
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Number :</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" name="helper_number" value="<?= htmlspecialchars($data['helper_number']); ?>" class="form-control col-md-7 col-xs-12" />
                  </div>
                </div>
              </div>
              <!-- Personal Car Section -->
              <div id="personal_car_sec" <?php if($data['rented_car']==0){?>style="display:block"<?php }else{?>style="display:none"<?php } ?>>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Car No :</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <select name="car_id" class="form-control col-md-7 col-xs-12">
                      <option value="">Select</option>
                      <?php
                      $cars = mysqli_query($conn, "SELECT * FROM tbl_car ORDER BY car_number ASC");
                      while ($car = mysqli_fetch_assoc($cars)) {
                        $selected = $car['car_id'] == $data['car_id'] ? "selected" : "";
                        echo "<option value='{$car['car_id']}' $selected>{$car['car_number']}</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Name :</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <select name="driver_id" class="form-control col-md-7 col-xs-12" onchange="get_driver_phone_no(this.value);">
                      <option value="">Select</option>
                      <?php
                      $drivers = mysqli_query($conn, "SELECT * FROM tbl_driver ORDER BY driver_name ASC");
                      while ($driver = mysqli_fetch_assoc($drivers)) {
                        $selected = $driver['driver_id'] == $data['driver_id'] ? "selected" : "";
                        echo "<option value='{$driver['driver_id']}' $selected>{$driver['driver_name']}</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <script>
                  function get_driver_phone_no(v) {
                    $.ajax({
                      type: "POST",
                      url: "ajax_get_driver_phone_no.php",
                      dataType: 'html',
                      data: "q=" + v,
                      success: function(html) {
                        $("#driver_number_sec").html(html);
                      }
                    });
                  }
                </script>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Number :</label>
                  <div class="col-md-6 col-sm-6 col-xs-12" id="driver_number_sec">
                    <input type="text" name="driver_number" value="<?= htmlspecialchars($data['driver_number']); ?>" class="form-control col-md-7 col-xs-12" />
                  </div>
                </div>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Name :</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <select name="helper_id" class="form-control col-md-7 col-xs-12" onchange="get_helper_phone_no(this.value);">
                      <option value="">Select</option>
                      <?php
                      $helpers = mysqli_query($conn, "SELECT * FROM tbl_helper ORDER BY helper_name ASC");
                      while ($helper = mysqli_fetch_assoc($helpers)) {
                        $selected = $helper['helper_id'] == $data['helper_id'] ? "selected" : "";
                        echo "<option value='{$helper['helper_id']}' $selected>{$helper['helper_name']}</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <script>
                  function get_helper_phone_no(v) {
                    $.ajax({
                      type: "POST",
                      url: "ajax_get_helper_phone_no.php",
                      dataType: 'html',
                      data: "q=" + v,
                      success: function(html) {
                        $("#helper_number_sec").html(html);
                      }
                    });
                  }
                </script>
                <div class="item form-group">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Number :</label>
                  <div class="col-md-6 col-sm-6 col-xs-12" id="helper_number_sec">
                    <input type="text" name="helper_number" value="<?= htmlspecialchars($data['helper_number']); ?>" class="form-control col-md-7 col-xs-12" />
                  </div>
                </div>
              </div>
              <!-- Other Details -->
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Car Oil Amount :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="number" name="car_oil_amount" value="<?= htmlspecialchars($data['car_oil_amount']); ?>" class="form-control col-md-7 col-xs-12" min="0">
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">IN Time :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="time" name="car_in_time" value="<?= htmlspecialchars($data['car_in_time']); ?>" class="form-control col-md-7 col-xs-12">
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Out Time :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="time" name="car_out_time" value="<?= htmlspecialchars($data['car_out_time']); ?>" class="form-control col-md-7 col-xs-12">
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Doc No :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" name="doc_no" value="<?= htmlspecialchars($data['doc_no']); ?>" class="form-control col-md-7 col-xs-12" required>
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Doc Type :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <select name="doc_type" class="form-control col-md-7 col-xs-12" required>
                    <option value="">Select</option>
                    <option value="DRS" <?php if($data['doc_type']=="DRS"){echo "selected";}?>>DRS</option>
                    <option value="NON-DRS" <?php if($data['doc_type']=="NON-DRS"){echo "selected";}?>>NON-DRS</option>
                  </select>
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Branch Office :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <select name="branch_office" class="form-control col-md-7 col-xs-12">
                    <option value="">Select Branch</option>
                    <option value="slg" <?php if($data['branch_office']=="slg"){echo "selected";}?>>Siliguri</option>
                    <option value="bdn" <?php if($data['branch_office']=="bdn"){echo "selected";}?>>Burdwan</option>
                    <option value="drj" <?php if($data['branch_office']=="drj"){echo "selected";}?>>Darjeeling</option>
                  </select>
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignor Company :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <select name="company_id" class="form-control col-md-7 col-xs-12" required>
                    <option value="">Select</option>
                    <?php
                    $companies = mysqli_query($conn, "SELECT * FROM tbl_company ORDER BY company_title ASC");
                    while ($company = mysqli_fetch_assoc($companies)) {
                      $selected = $company['company_id'] == $data['company_id'] ? "selected" : "";
                      echo "<option value='{$company['company_id']}' $selected>{$company['company_title']}</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignee Name :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" name="client_name" value="<?= htmlspecialchars($data['client_name']); ?>" class="form-control col-md-7 col-xs-12" required>
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignee Phone :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" name="client_phone" value="<?= htmlspecialchars($data['client_phone']); ?>" class="form-control col-md-7 col-xs-12" required>
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignee Email :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="email" name="client_email" value="<?= htmlspecialchars($data['client_email']); ?>" class="form-control col-md-7 col-xs-12">
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignee Address :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="text" name="client_address" value="<?= htmlspecialchars($data['client_address']); ?>" class="form-control col-md-7 col-xs-12" required>
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Box (unit) :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="number" name="box" value="<?= htmlspecialchars($data['box']); ?>" class="form-control col-md-7 col-xs-12" min="0">
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Weight (kg) :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="number" name="weight" value="<?= htmlspecialchars($data['weight']); ?>" class="form-control col-md-7 col-xs-12" min="0" step="0.01">
                </div>
              </div>
              <div class="item form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Pay To :</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <select name="pay_to" class="form-control col-md-7 col-xs-12">
                    <option value="1" <?php if($data['pay_to']==1){echo "selected";}?>>Yes</option>
                    <option value="0" <?php if($data['pay_to']==0){echo "selected";}?>>No</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <div class="col-md-6 col-md-offset-3">
                  <input type="hidden" name="shipping_details_id" value="<?= $data['shipping_details_id']; ?>">
                  <button type="submit" name="edit_register" class="btn btn-success">Update</button>
                  <button type="button" class="btn btn-secondary" onclick="listregister();">Cancel</button>
                </div>
              </div>
            </form>
            <script>
              function listregister() {
                location.href = "register.php?type=list_register&lp=cu";
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
