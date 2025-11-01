<?php
require 'top_header.php';
?>
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      <!-- page content -->
      <div class="right_col" role="main" style="background: #f8fafc; min-height:100vh;">
        <div class="dashboard-header-row" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
          <div>
            <h2 class="dashboard-title" style="margin:0;font-weight:800;letter-spacing:-.5px;color:#222;font-size:2.13rem;">Manifest</h2>
            <div class="dashboard-subtitle" style="color:#7f8ba5;font-size:1.12rem;font-weight:500;margin-top:4px;">
              Create, search and print manifests for office locations.
            </div>
          </div>
        </div>

        <div class="manifest-controls" style="display:flex;gap:18px;align-items:center;margin-bottom:32px;flex-wrap:wrap;">
          <div>
            <label for="manifest_location" style="font-weight:600;color:#222;margin-right:8px;">Select Location:</label>
            <select id="manifest_location" class="form-control" style="display:inline-block;width:210px;">
              <option value="">-- Select Branch/Office --</option>
              <?php
              $locations = mysqli_query($conn, "SELECT office_id, office_name FROM tbl_offices ORDER BY office_name ASC");
              while($loc = mysqli_fetch_assoc($locations)) {
                echo '<option value="'.$loc['office_id'].'">'.htmlspecialchars($loc['office_name']).'</option>';
              }
              ?>
            </select>
          </div>
          <div id="manifest_id_container" style="display:none;">
            <label for="manifest_id_select" style="font-weight:600;color:#222;margin-right:8px;">Select Manifest:</label>
            <select id="manifest_id_select" class="form-control" style="display:inline-block;width:350px;">
              <option value="">-- Select Manifest --</option>
            </select>
          </div>
          <button type="button" class="btn btn-primary" id="btn_manifest_new" style="font-weight:600;padding:8px 22px;">
            <i class="fa fa-plus"></i> New Entry
          </button>
        </div>

        <!-- Manifest content area -->
        <div id="manifest_content"></div>

      </div>
    </div>
  </div>
  <?php require 'footer.php';?>

  <style>
    .dashboard-header-row { margin-bottom: 18px;}
    .dashboard-title { font-weight: 800; letter-spacing: -.5px; color: #222; font-size: 2.13rem; margin-bottom: 0;}
    @media (max-width: 900px) {
      .dashboard-title { font-size: 1.23rem;}
      .manifest-controls { flex-direction:column;align-items:stretch;gap:11px;}
    }
    @media (max-width:600px) {
      .dashboard-title { font-size: 1.08rem;}
      .manifest-controls select, .manifest-controls button { width:100%; margin-bottom:5px;}
    }
  </style>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $(function() {
      var selectedOfficeId = null;

      // When office is selected, load manifest IDs automatically
      $('#manifest_location').on('change', function() {
        selectedOfficeId = $(this).val();
        $('#manifest_content').html('');
        
        if (selectedOfficeId) {
          // Show loading in manifest dropdown
          $('#manifest_id_select').html('<option value="">Loading...</option>');
          $('#manifest_id_container').show();
          $('#btn_manifest_new').prop('disabled', false);
          
          // Load manifests for this office
          $.get('manifest_get_list.php', {office_id: selectedOfficeId}, function(data) {
            if (data.success && data.manifests.length > 0) {
              var options = '<option value="">-- Select Manifest --</option>';
              $.each(data.manifests, function(i, m) {
                options += '<option value="' + m.manifest_id + '">' + 
                          m.manifest_no + ' - ' + m.date + ' (Net: ₹' + m.net_total + ')' +
                          '</option>';
              });
              $('#manifest_id_select').html(options);
            } else {
              $('#manifest_id_select').html('<option value="">No manifests found</option>');
            }
          }, 'json').fail(function() {
            $('#manifest_id_select').html('<option value="">Error loading manifests</option>');
          });
        } else {
          $('#manifest_id_container').hide();
          $('#manifest_id_select').html('<option value="">-- Select Manifest --</option>');
          $('#btn_manifest_new').prop('disabled', true);
        }
      });

      // When manifest is selected, automatically load and display it
      $('#manifest_id_select').on('change', function() {
        var manifestId = $(this).val();
        
        if (manifestId) {
          // Disable New Entry button when viewing a manifest
          $('#btn_manifest_new').prop('disabled', true);
          
          // Load manifest details
          $('#manifest_content').html('<div style="padding:48px;text-align:center;font-size:1.1rem;color:#666;">Loading manifest details…</div>');
          $.get('manifest_view.php', {manifest_id: manifestId}, function(html) {
            $('#manifest_content').html(html);
          }).fail(function() {
            $('#manifest_content').html('<div class="alert alert-danger">Error loading manifest details.</div>');
          });
        } else {
          // Enable New Entry button when no manifest selected
          $('#btn_manifest_new').prop('disabled', false);
          $('#manifest_content').html('');
        }
      });

      // New Entry button - only enabled when office selected and no manifest selected
      $('#btn_manifest_new').click(function() {
        if (!selectedOfficeId) {
          alert('Please select a location/branch first.');
          return;
        }
        $('#manifest_content').html('<div style="padding:48px;text-align:center;font-size:1.1rem;color:#666;">Loading…</div>');
        $.get('manifest_new_entry.php', {office_id: selectedOfficeId}, function(html) {
          $('#manifest_content').html(html);
        });
      });

      // Initially disable New Entry button
      $('#btn_manifest_new').prop('disabled', true);
    });
  </script>
</body>
