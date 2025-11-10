<?php
require 'check_auth.php';
requirePermission('manifest_view');
require 'top_header.php';
?>
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      <!-- page content -->
      <div class="right_col" role="main">
        <?php
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'list_manifest';

        switch($type) {
          case 'create_manifest':
            include('manifest_new_entry.php');
            break;

          case 'view_manifest':
            include('manifest_view.php');
            break;

          case 'list_manifest':
          default:
            include('list_manifest.php');
            break;
        }
        ?>
      </div>
    </div>
  </div>
  <?php require 'footer.php';?>
</body>
