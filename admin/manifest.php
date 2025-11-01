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
        <div class="dashboard-header-row" style="margin-bottom:24px;">
          <h2 class="dashboard-title" style="margin:0;font-weight:800;color:#222;font-size:2rem;">📋 Manifest Management</h2>
          <div class="dashboard-subtitle" style="color:#7f8ba5;font-size:1rem;margin-top:6px;">
            Select an office to create new manifest or view existing ones
          </div>
        </div>

        <!-- Simple Control Panel -->
        <div class="manifest-control-panel" style="background:white;padding:24px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:24px;">
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;align-items:end;">
            
            <!-- Step 1: Select Office -->
            <div>
              <label style="display:block;font-weight:600;color:#444;margin-bottom:8px;font-size:1.05rem;">
                <span style="background:#007bff;color:white;border-radius:50%;padding:4px 10px;font-size:1rem;margin-right:6px;">1</span>
                Select Office
              </label>
              <select id="manifest_location" class="form-control" style="height:48px;font-size:1.15rem;">
                <option value="">Choose office...</option>
                <?php
                $locations = mysqli_query($conn, "SELECT office_id, office_name FROM tbl_offices ORDER BY office_name ASC");
                while($loc = mysqli_fetch_assoc($locations)) {
                  echo '<option value="'.$loc['office_id'].'">'.htmlspecialchars($loc['office_name']).'</option>';
                }
                ?>
              </select>
            </div>

            <!-- Step 2: View or Create -->
            <div id="action_container" style="display:none;">
              <label style="display:block;font-weight:600;color:#444;margin-bottom:8px;font-size:1.05rem;">
                <span style="background:#28a745;color:white;border-radius:50%;padding:4px 10px;font-size:1rem;margin-right:6px;">2</span>
                What would you like to do?
              </label>
              <div style="display:flex;gap:10px;">
                <button type="button" class="btn btn-success" id="btn_create_new" style="flex:1;height:48px;font-weight:600;font-size:1.1rem;">
                  <i class="fa fa-plus-circle"></i> Create New
                </button>
                <button type="button" class="btn btn-primary" id="btn_view_existing" style="flex:1;height:48px;font-weight:600;font-size:1.1rem;">
                  <i class="fa fa-search"></i> View Existing
                </button>
              </div>
            </div>

            <!-- Step 3: Select Manifest (shown only when viewing) -->
            <div id="manifest_selector" style="display:none;">
              <label style="display:block;font-weight:600;color:#444;margin-bottom:8px;font-size:1.05rem;">
                <span style="background:#ffc107;color:#333;border-radius:50%;padding:4px 10px;font-size:1rem;margin-right:6px;">3</span>
                Select Manifest
              </label>
              <select id="manifest_id_select" class="form-control" style="height:48px;font-size:1.15rem;">
                <option value="">Choose manifest...</option>
              </select>
            </div>

          </div>

          <!-- Quick Stats -->
          <div id="quick_stats" style="display:none;margin-top:20px;padding-top:20px;border-top:2px dashed #e0e0e0;">
            <div style="display:flex;gap:20px;flex-wrap:wrap;">
              <div style="flex:1;min-width:150px;padding:16px;background:#f0f9ff;border-radius:8px;">
                <div style="font-size:1rem;color:#666;margin-bottom:6px;font-weight:600;">Total Manifests</div>
                <div style="font-size:1.8rem;font-weight:700;color:#007bff;" id="stat_total">0</div>
              </div>
              <div style="flex:1;min-width:150px;padding:16px;background:#f0fdf4;border-radius:8px;">
                <div style="font-size:1rem;color:#666;margin-bottom:6px;font-weight:600;">Latest Manifest</div>
                <div style="font-size:1.2rem;font-weight:600;color:#16a34a;" id="stat_latest">-</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content Area -->
        <div id="manifest_content"></div>

      </div>
    </div>
  </div>
  <?php require 'footer.php';?>

  <style>
    .dashboard-title { font-weight: 800; color: #222; font-size: 2rem; margin-bottom: 0;}
    .form-control {
      font-size: 1.15rem;
      padding: 10px 12px;
      height: auto;
    }
    .form-control:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    label {
      font-size: 1.1rem;
      font-weight: 600;
    }
    .btn {
      transition: all 0.2s ease;
      font-size: 1.1rem;
      padding: 10px 18px;
    }
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    #quick_stats {
      font-size: 1.05rem;
    }
    #quick_stats > div > div {
      font-size: 1rem;
    }
    #stat_total, #stat_latest {
      font-size: 1.6rem !important;
    }
    @media (max-width: 768px) {
      .dashboard-title { font-size: 1.5rem;}
      .manifest-control-panel {
        padding: 16px !important;
      }
      .manifest-control-panel > div {
        grid-template-columns: 1fr !important;
      }
      .form-control {
        font-size: 1rem;
      }
      .btn {
        font-size: 1rem;
        padding: 8px 14px;
      }
    }
    
    @media (max-width: 576px) {
      .dashboard-title { font-size: 1.3rem; }
      .dashboard-subtitle { font-size: 0.9rem; }
      .manifest-control-panel { padding: 12px !important; }
      .form-control { font-size: 0.95rem; height: 42px !important; }
      .btn { font-size: 0.95rem; padding: 8px 12px; height: 42px !important; }
      label { font-size: 1rem !important; }
      #quick_stats > div > div { font-size: 0.9rem !important; }
      #stat_total, #stat_latest { font-size: 1.4rem !important; }
    }
  </style>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $(function() {
      var selectedOfficeId = null;
      var currentMode = null; // 'create' or 'view'

      // Step 1: Office Selection
      $('#manifest_location').on('change', function() {
        selectedOfficeId = $(this).val();
        $('#manifest_content').html('');
        $('#manifest_selector').hide();
        
        if (selectedOfficeId) {
          // Show action buttons
          $('#action_container').fadeIn(300);
          
          // Load manifests in background
          loadManifestsList();
        } else {
          $('#action_container').hide();
          $('#quick_stats').hide();
        }
      });

      // Step 2a: Create New Manifest
      $('#btn_create_new').on('click', function() {
        currentMode = 'create';
        $('#manifest_selector').hide();
        $('#manifest_content').html('<div style="padding:40px;text-align:center;"><i class="fa fa-spinner fa-spin fa-2x"></i><br><span style="margin-top:12px;display:inline-block;">Loading form...</span></div>');
        
        $.get('manifest_new_entry.php', {office_id: selectedOfficeId}, function(html) {
          $('#manifest_content').html(html);
          // Smooth scroll to content
          $('html, body').animate({
            scrollTop: $('#manifest_content').offset().top - 20
          }, 400);
        }).fail(function() {
          $('#manifest_content').html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error loading form. Please try again.</div>');
        });
      });

      // Step 2b: View Existing Manifests
      $('#btn_view_existing').on('click', function() {
        currentMode = 'view';
        $('#manifest_content').html('');
        $('#manifest_selector').fadeIn(300);
        
        // Smooth scroll to selector
        $('html, body').animate({
          scrollTop: $('#manifest_selector').offset().top - 100
        }, 400);
      });

      // Step 3: Manifest Selection
      $('#manifest_id_select').on('change', function() {
        var manifestId = $(this).val();
        
        if (manifestId) {
          $('#manifest_content').html('<div style="padding:40px;text-align:center;"><i class="fa fa-spinner fa-spin fa-2x"></i><br><span style="margin-top:12px;display:inline-block;">Loading manifest...</span></div>');
          
          $.get('manifest_view.php', {manifest_id: manifestId}, function(html) {
            $('#manifest_content').html(html);
            // Smooth scroll to content
            $('html, body').animate({
              scrollTop: $('#manifest_content').offset().top - 20
            }, 400);
          }).fail(function() {
            $('#manifest_content').html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error loading manifest. Please try again.</div>');
          });
        } else {
          $('#manifest_content').html('');
        }
      });

      // Load manifests list for selected office
      function loadManifestsList() {
        $.get('manifest_get_list.php', {office_id: selectedOfficeId}, function(data) {
          if (data.success) {
            // Update stats
            $('#stat_total').text(data.count);
            if (data.count > 0) {
              $('#stat_latest').text(data.manifests[0].manifest_no);
              $('#quick_stats').fadeIn(300);
              
              // Populate manifest selector
              var options = '<option value="">Choose manifest...</option>';
              $.each(data.manifests, function(i, m) {
                options += '<option value="' + m.manifest_id + '">' + 
                          m.manifest_no + ' - ' + m.date + ' (₹' + m.net_total + ')' +
                          '</option>';
              });
              $('#manifest_id_select').html(options);
            } else {
              $('#stat_latest').text('No manifests yet');
              $('#quick_stats').fadeIn(300);
              $('#manifest_id_select').html('<option value="">No manifests found</option>');
            }
          }
        }, 'json').fail(function() {
          console.error('Failed to load manifests list');
        });
      }
    });
  </script>
</body>
