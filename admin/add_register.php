<?php
require 'conn.php';
// Prepare company/car/driver/helper lists for selects
$companies = mysqli_query($conn, "SELECT * FROM tbl_company ORDER BY company_title ASC");
$cars = mysqli_query($conn, "SELECT * FROM tbl_car ORDER BY car_number ASC");
$drivers = mysqli_query($conn, "SELECT * FROM tbl_driver ORDER BY driver_name ASC");
$helpers = mysqli_query($conn, "SELECT * FROM tbl_helper ORDER BY helper_name ASC");

// Notification message after registration
$show_success = isset($_GET['msg']) && $_GET['msg'] === 'success';
$show_error   = isset($_GET['msg']) && $_GET['msg'] === 'error';
?>

<script type="text/javascript">
function listregister() {
    location.href = "register.php?type=list_register&lp=cu";
}
function add_new_client() {
    ctr = $("#ctr").val();
    ctr = parseInt(ctr) + 1;
    var Ajax = $.ajax({
        type: "POST",
        url: "ajax_add_new_client.php",
        dataType: 'html',
        data: "q=" + ctr,
        success: function (html) {
            $("#ctr").val(ctr)
            $("#add_new_sec").append(html);
        }
    });
}
function change_rented_car(val) {
    if (val == "1") {
        $("#personal_car_sec").hide();
        $("#rented_car_sec").show();
    } else {
        $("#personal_car_sec").show();
        $("#rented_car_sec").hide();
    }
}
function get_driver_phone_no(v) {
    $.ajax({
        type: "POST",
        url: "ajax_get_driver_phone_no.php",
        dataType: 'text',
        data: "q=" + v,
        success: function (phone) {
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
        success: function (phone) {
            $("#helper_number").val(phone.trim());
        }
    });
}
</script>

<?php if ($show_success): ?>
  <div class="alert alert-success" id="notify_save" style="margin-bottom:20px;">
    New shipping entry registered successfully!
  </div>
  <script>
    setTimeout(function() {
      document.getElementById("notify_save").style.display = 'none';
    }, 5000);
  </script>
<?php elseif ($show_error): ?>
  <div class="alert alert-danger" id="notify_error" style="margin-bottom:20px;">
    Failed to save. Please try again.
  </div>
  <script>
    setTimeout(function() {
      document.getElementById("notify_error").style.display = 'none';
    }, 5000);
  </script>
<?php endif; ?>

<div class="x_panel">
  <div class="x_title">
    <h2>Add New Shipping Entry</h2>
    <div class="clearfix"></div>
  </div>
  <div class="x_content">
    <form id="add_register_form" action="includes/script/register_code.php" method="post" autocomplete="off" class="form-horizontal form-label-left">
      <!-- Rented Car selection -->
      <div class="item form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Rented Car <span class="required">*</span></label>
        <div class="col-md-6 col-sm-6 col-xs-12">
          <input type="radio" name="rented_car" value="1" onclick="change_rented_car('1')"> Yes
          <input type="radio" name="rented_car" value="0" checked onclick="change_rented_car('0')"> No
        </div>
      </div>
      <!-- Personal Car Section (Dropdowns) -->
      <div id="personal_car_sec">
        <div class="item form-group">
          <label class="control-label col-md-3 col-sm-3 col-xs-12">Car No <span class="required">*</span></label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <select name="car_id" id="car_id" class="form-control">
              <option value="">Select</option>
              <?php while ($car = mysqli_fetch_assoc($cars)) { ?>
                <option value="<?= $car['car_id']; ?>"><?= $car['car_number']; ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="item form-group">
          <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Name <span class="required">*</span></label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <select name="driver_id" id="driver_id" class="form-control" onchange="get_driver_phone_no(this.value);">
              <option value="">Select</option>
              <?php mysqli_data_seek($drivers, 0); while ($driver = mysqli_fetch_assoc($drivers)) { ?>
                <option value="<?= $driver['driver_id']; ?>"><?= $driver['driver_name']; ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="item form-group" id="driver_number_sec">
          <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Number <span class="required">*</span></label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="text" name="driver_number" id="driver_number" class="form-control"/>
          </div>
        </div>
        <div class="item form-group">
          <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Name <span class="required">*</span></label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <select name="helper_id" id="helper_id" class="form-control" onchange="get_helper_phone_no(this.value)">
              <option value="">Select</option>
              <?php mysqli_data_seek($helpers, 0); while ($helper = mysqli_fetch_assoc($helpers)) { ?>
                <option value="<?= $helper['helper_id']; ?>"><?= $helper['helper_name']; ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="item form-group" id="helper_number_sec">
          <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Number <span class="required">*</span></label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="text" name="helper_number" id="helper_number" class="form-control"/>
          </div>
        </div>
      </div>
      <!-- Rented Car Section (Manual) -->
      <div id="rented_car_sec" style="display:none;">
        <div class="item form-group">
          <label class="control-label col-md-3 col-sm-3 col-xs-12">Car No <span class="required">*</span></label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="text" name="car_number" class="form-control"/>
          </div>
        </div>
        <div class="item form-group">
          <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Name <span class="required">*</span></label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="text" name="driver_name" class="form-control"/>
          </div>
        </div>
        <div class="item form-group">
          <label class="control-label col-md-3 col-sm-3 col-xs-12">Driver Number <span class="required">*</span></label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="text" name="driver_number_rent" class="form-control"/>
          </div>
        </div>
        <div class="item form-group">
          <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Name <span class="required">*</span></label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="text" name="helper_name" class="form-control"/>
          </div>
        </div>
        <div class="item form-group">
          <label class="control-label col-md-3 col-sm-3 col-xs-12">Helper Number <span class="required">*</span></label>
          <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="text" name="helper_number" class="form-control"/>
          </div>
        </div>
      </div>
      <!-- Other trip fields -->
      <div class="item form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Pickup Time</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
          <input type="time" name="car_in_time" id="car_in_time" class="form-control">
        </div>
      </div>
      <div class="item form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Docket No <span class="required">*</span></label>
        <div class="col-md-6 col-sm-6 col-xs-12">
          <input type="text" name="doc_no" class="form-control" required>
        </div>
      </div>
      <!-- Consignor company -->
      <div class="item form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignor Company <span class="required">*</span></label>
        <div class="col-md-6 col-sm-6 col-xs-12">
          <select name="company_id" id="company_id" class="form-control" required>
            <option value="">Select</option>
            <?php mysqli_data_seek($companies, 0); while ($company = mysqli_fetch_assoc($companies)) { ?>
              <option value="<?= $company['company_id']; ?>"><?= $company['company_title']; ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <!-- To Pay -->
      <div class="item form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">To Pay <span class="required">*</span></label>
        <div class="col-md-6 col-sm-6 col-xs-12">
          <select name="pay_to" id="pay_to" class="form-control" required>
            <option value="1">Yes</option>
            <option value="0" selected>No</option>
          </select>
        </div>
      </div>
      <div class="item form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignee Name <span class="required">*</span></label>
        <div class="col-md-6 col-sm-6 col-xs-12">
          <input type="text" name="client_name" id="client_name" required class="form-control">
        </div>
      </div>
      <div class="item form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignee Phone <span class="required">*</span></label>
        <div class="col-md-6 col-sm-6 col-xs-12">
          <input type="tel" name="client_phone" id="client_phone" required class="form-control">
        </div>
      </div>
      <div class="item form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Consignee Address <span class="required">*</span></label>
        <div class="col-md-6 col-sm-6 col-xs-12">
          <input type="text" name="client_address" id="client_address" required class="form-control">
        </div>
      </div>
      <div class="item form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Box <span class="required">*</span></label>
        <div class="col-md-6 col-sm-6 col-xs-12">
          <input type="number" name="box" required value="0" class="form-control" min="1">
        </div>
      </div>
      <div class="item form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Weight (kg) <span class="required">*</span></label>
        <div class="col-md-6 col-sm-6 col-xs-12">
          <input type="number" name="weight" required value="0" class="form-control" min="0" step="0.01">
        </div>
      </div>
      <input type="hidden" name="status" value="Pickup">
      <div class="form-group">
        <div class="col-md-6 col-md-offset-3">
          <button type="submit" name="save_register" class="btn btn-success">Save</button>
          <button type="button" class="btn btn-secondary" onclick="listregister();">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>
