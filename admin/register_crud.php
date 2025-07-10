<?php
require 'conn.php';

// Get lists for dropdowns (for modal, used via JS as well)
$companies = mysqli_query($conn, "SELECT * FROM tbl_company ORDER BY company_title ASC");
$cars = mysqli_query($conn, "SELECT * FROM tbl_car ORDER BY car_number ASC");
$drivers = mysqli_query($conn, "SELECT * FROM tbl_driver ORDER BY driver_name ASC");
$helpers = mysqli_query($conn, "SELECT * FROM tbl_helper ORDER BY helper_name ASC");
?>

<div class="x_panel">
  <div class="x_title">
    <h2>Shipping Register Management</h2>
    <div class="clearfix"></div>
  </div>
  <div class="x_content">
    <button class="btn btn-primary mb-3" id="addNewBtn">+ Add New Entry</button>
    <table class="table table-bordered" id="registerTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Doc No</th>
          <th>Company</th>
          <th>Consignee</th>
          <th>Car</th>
          <th>Driver</th>
          <th>Helper</th>
          <th>Box</th>
          <th>Weight</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <!-- Table rows will be loaded via AJAX -->
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" id="registerForm">
      <div class="modal-header">
        <h5 class="modal-title">Shipping Entry</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <!-- Modal form fields START -->
        <input type="hidden" name="shipping_details_id" id="shipping_details_id">
        <div class="form-row">
          <div class="form-group col-md-4">
            <label>Doc No *</label>
            <input type="text" name="doc_no" id="doc_no" class="form-control" required>
          </div>
          <div class="form-group col-md-4">
            <label>Consignor Company *</label>
            <select name="company_id" id="company_id" class="form-control" required>
              <option value="">Select</option>
              <?php mysqli_data_seek($companies,0); while($company=mysqli_fetch_assoc($companies)){ ?>
              <option value="<?= $company['company_id'] ?>"><?= htmlspecialchars($company['company_title']) ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label>Consignee Name *</label>
            <input type="text" name="client_name" id="client_name" class="form-control" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Rented Car *</label><br>
            <input type="radio" name="rented_car" value="1" id="rentedCarYes"> Yes
            <input type="radio" name="rented_car" value="0" id="rentedCarNo" checked> No
          </div>
          <div class="form-group col-md-3">
            <label>Car *</label>
            <select name="car_id" id="car_id" class="form-control">
              <option value="">Select</option>
              <?php mysqli_data_seek($cars,0); while($car=mysqli_fetch_assoc($cars)){ ?>
                <option value="<?= $car['car_id'] ?>"><?= htmlspecialchars($car['car_number']) ?></option>
              <?php } ?>
            </select>
            <input type="text" name="car_number" id="car_number" class="form-control mt-1" style="display:none;" placeholder="Car Number (Rented)">
          </div>
          <div class="form-group col-md-3">
            <label>Driver *</label>
            <select name="driver_id" id="driver_id" class="form-control">
              <option value="">Select</option>
              <?php mysqli_data_seek($drivers,0); while($driver=mysqli_fetch_assoc($drivers)){ ?>
                <option value="<?= $driver['driver_id'] ?>"><?= htmlspecialchars($driver['driver_name']) ?></option>
              <?php } ?>
            </select>
            <input type="text" name="driver_name" id="driver_name" class="form-control mt-1" style="display:none;" placeholder="Driver Name (Rented)">
          </div>
          <div class="form-group col-md-3">
            <label>Driver Number *</label>
            <input type="text" name="driver_number" id="driver_number" class="form-control">
            <input type="text" name="driver_number_rent" id="driver_number_rent" class="form-control mt-1" style="display:none;" placeholder="Driver Number (Rented)">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-3">
            <label>Helper *</label>
            <select name="helper_id" id="helper_id" class="form-control">
              <option value="">Select</option>
              <?php mysqli_data_seek($helpers,0); while($helper=mysqli_fetch_assoc($helpers)){ ?>
                <option value="<?= $helper['helper_id'] ?>"><?= htmlspecialchars($helper['helper_name']) ?></option>
              <?php } ?>
            </select>
            <input type="text" name="helper_name" id="helper_name" class="form-control mt-1" style="display:none;" placeholder="Helper Name (Rented)">
          </div>
          <div class="form-group col-md-3">
            <label>Helper Number *</label>
            <input type="text" name="helper_number" id="helper_number" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>Box *</label>
            <input type="number" name="box" id="box" class="form-control" min="1" required>
          </div>
          <div class="form-group col-md-2">
            <label>Weight (kg) *</label>
            <input type="number" name="weight" id="weight" class="form-control" step="0.01" min="0" required>
          </div>
          <div class="form-group col-md-2">
            <label>To Pay *</label>
            <select name="pay_to" id="pay_to" class="form-control">
              <option value="1">Yes</option>
              <option value="0" selected>No</option>
            </select>
          </div>
        </div>
        <!-- Add more fields as needed -->
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function loadTable() {
  $.get('ajax_register_crud.php', {action: 'list'}, function(res){
    $('#registerTable tbody').html(res);
  });
}

$(function(){
  loadTable();

  // Show Add modal
  $('#addNewBtn').click(function(){
    $('#registerForm')[0].reset();
    $('#registerForm input[type="hidden"]').val('');
    $('#registerModal').modal('show');
    $('.form-control').show();
    $('#car_number,#driver_name,#driver_number_rent,#helper_name').hide();
    $('#car_id,#driver_id,#helper_id,#driver_number').show();
    $('#rentedCarNo').prop('checked', true);
  });

  // Rented car logic
  $('input[name="rented_car"]').change(function(){
    if ($(this).val() == '1') {
      $('#car_id,#driver_id,#helper_id,#driver_number').hide();
      $('#car_number,#driver_name,#driver_number_rent,#helper_name').show();
    } else {
      $('#car_id,#driver_id,#helper_id,#driver_number').show();
      $('#car_number,#driver_name,#driver_number_rent,#helper_name').hide();
    }
  });

  // Open Edit modal
  $(document).on('click', '.editBtn', function(){
    var id = $(this).data('id');
    $.post('ajax_register_crud.php', {action:'fetch', id:id}, function(data){
      var d = JSON.parse(data);
      $('#shipping_details_id').val(d.shipping_details_id);
      $('#doc_no').val(d.doc_no);
      $('#company_id').val(d.company_id);
      $('#client_name').val(d.client_name);
      $('#box').val(d.box);
      $('#weight').val(d.weight);
      $('#pay_to').val(d.pay_to);
      $('#rentedCarYes').prop('checked', d.rented_car == '1');
      $('#rentedCarNo').prop('checked', d.rented_car == '0');
      if(d.rented_car == '1'){
        $('#car_number').val(d.car_number);
        $('#driver_name').val(d.driver_name);
        $('#driver_number_rent').val(d.driver_number);
        $('#helper_name').val(d.helper_name);
        $('#helper_number').val(d.helper_number);
        $('#car_id,#driver_id,#helper_id,#driver_number').hide();
        $('#car_number,#driver_name,#driver_number_rent,#helper_name').show();
      } else {
        $('#car_id').val(d.car_id);
        $('#driver_id').val(d.driver_id);
        $('#helper_id').val(d.helper_id);
        $('#driver_number').val(d.driver_number);
        $('#helper_number').val(d.helper_number);
        $('#car_id,#driver_id,#helper_id,#driver_number').show();
        $('#car_number,#driver_name,#driver_number_rent,#helper_name').hide();
      }
      $('#registerModal').modal('show');
    });
  });

  // Submit Add/Edit
  $('#registerForm').submit(function(e){
    e.preventDefault();
    $.post('ajax_register_crud.php', $(this).serialize()+'&action=save', function(res){
      if(res == 'success'){
        $('#registerModal').modal('hide');
        loadTable();
      }else{
        alert('Failed to save!');
      }
    });
  });

  // Delete
  $(document).on('click', '.deleteBtn', function(){
    if(!confirm('Delete this entry?')) return;
    var id = $(this).data('id');
    $.post('ajax_register_crud.php', {action:'delete', id:id}, function(res){
      if(res == 'success'){
        loadTable();
      }else{
        alert('Failed to delete!');
      }
    });
  });
});
</script>
