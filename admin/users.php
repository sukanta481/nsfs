<?php
require 'check_auth.php';
requirePermission('user_view');
require 'conn.php';

// Check for success message
$success_message = '';
if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}

// Check for error message
$error_message = '';
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}

// Get all users with their roles and office info
$users_query = "SELECT u.*, r.role_name, s.staff_name, o.office_name 
                FROM tbl_users u 
                LEFT JOIN tbl_roles r ON u.role_id = r.role_id 
                LEFT JOIN tbl_staff s ON u.staff_id = s.staff_id 
                LEFT JOIN tbl_offices o ON u.office_id = o.office_id
                ORDER BY u.created_at DESC";
$users_result = mysqli_query($conn, $users_query);

// Get roles for filters
$roles_query = "SELECT * FROM tbl_roles ORDER BY role_name ASC";
$roles_result = mysqli_query($conn, $roles_query);

require 'top_header.php';
?>
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <!-- page content -->
      <div class="right_col" role="main" style="background: #f8fafc; min-height:100vh;">
        
        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin: 20px 0; border-radius: 10px; border-left: 4px solid #28a745;">
          <i class="fa fa-check-circle"></i> <strong>Success!</strong> <?php echo $success_message; ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 20px 0; border-radius: 10px; border-left: 4px solid #dc3545;">
          <i class="fa fa-exclamation-circle"></i> <strong>Error!</strong> <?php echo $error_message; ?>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <?php endif; ?>
        
        <!-- Header -->
        <div style="background: white; padding: 25px 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 25px;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
              <h2 style="margin: 0; font-weight: 800; color: #2c3e50; font-size: 28px;">
                <i class="fa fa-users" style="color: #667eea;"></i> User Management
              </h2>
              <p style="margin: 8px 0 0 0; color: #7f8c8d; font-size: 14px;">
                Manage users, roles, and permissions for the admin panel
              </p>
            </div>
            <div style="display: flex; gap: 10px;">
              <?php if (hasPermission('user_create')): ?>
              <a href="add_user.php" class="btn btn-success" style="padding: 12px 25px; font-weight: 600; font-size: 15px;">
                <i class="fa fa-plus-circle"></i> Add New User
              </a>
              <?php endif; ?>
              <?php if (hasPermission('role_manage')): ?>
              <a href="roles.php" class="btn btn-primary" style="padding: 12px 25px; font-weight: 600; font-size: 15px;">
                <i class="fa fa-shield-alt"></i> Manage Roles
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
          <?php
          $total_users = mysqli_num_rows($users_result);
          mysqli_data_seek($users_result, 0);
          
          $active_users = 0;
          $inactive_users = 0;
          while($u = mysqli_fetch_assoc($users_result)) {
            if ($u['active_status'] == 1) $active_users++;
            else $inactive_users++;
          }
          mysqli_data_seek($users_result, 0);
          
          $total_roles_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_roles");
          $total_roles = mysqli_fetch_assoc($total_roles_result)['cnt'];
          ?>
          
          <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 12px; color: white; box-shadow: 0 4px 15px rgba(102,126,234,0.3);">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Total Users</div>
            <div style="font-size: 36px; font-weight: 800;"><?= $total_users ?></div>
          </div>
          
          <div style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); padding: 25px; border-radius: 12px; color: white; box-shadow: 0 4px 15px rgba(76,175,80,0.3);">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Active Users</div>
            <div style="font-size: 36px; font-weight: 800;"><?= $active_users ?></div>
          </div>
          
          <div style="background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); padding: 25px; border-radius: 12px; color: white; box-shadow: 0 4px 15px rgba(255,152,0,0.3);">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Inactive Users</div>
            <div style="font-size: 36px; font-weight: 800;"><?= $inactive_users ?></div>
          </div>
          
          <div style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); padding: 25px; border-radius: 12px; color: white; box-shadow: 0 4px 15px rgba(33,150,243,0.3);">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Total Roles</div>
            <div style="font-size: 36px; font-weight: 800;"><?= $total_roles ?></div>
          </div>
        </div>

        <!-- Users Table -->
        <div style="background: white; padding: 0; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
          <div style="padding: 20px 25px; border-bottom: 2px solid #f0f0f0;">
            <h3 style="margin: 0; font-weight: 700; color: #2c3e50;">
              <i class="fa fa-list"></i> All Users
            </h3>
          </div>
          
          <div style="padding: 25px;">
            <table class="table table-striped table-hover" style="margin: 0;">
              <thead style="background: #f8f9fa;">
                <tr>
                  <th style="font-weight: 700; padding: 15px;">ID</th>
                  <th style="font-weight: 700; padding: 15px;">Username</th>
                  <th style="font-weight: 700; padding: 15px;">Full Name</th>
                  <th style="font-weight: 700; padding: 15px;">Email</th>
                  <th style="font-weight: 700; padding: 15px;">Role</th>
                  <th style="font-weight: 700; padding: 15px;">Office/Branch</th>
                  <th style="font-weight: 700; padding: 15px;">Linked Staff</th>
                  <th style="font-weight: 700; padding: 15px;">Status</th>
                  <th style="font-weight: 700; padding: 15px;">Last Login</th>
                  <th style="font-weight: 700; padding: 15px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                <tr>
                  <td style="padding: 15px; font-weight: 600;"><?= $user['user_id'] ?></td>
                  <td style="padding: 15px;">
                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                  </td>
                  <td style="padding: 15px;"><?= htmlspecialchars($user['full_name']) ?></td>
                  <td style="padding: 15px;">
                    <i class="fa fa-envelope" style="color: #95a5a6;"></i> 
                    <?= htmlspecialchars($user['email']) ?>
                  </td>
                  <td style="padding: 15px;">
                    <span style="background: #e3f2fd; color: #1976d2; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                      <?= htmlspecialchars($user['role_name'] ?? 'No Role') ?>
                    </span>
                  </td>
                  <td style="padding: 15px;">
                    <?php if (!empty($user['can_access_all_offices']) && $user['can_access_all_offices'] == 1): ?>
                      <span style="background: #fff3e0; color: #e65100; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        <i class="fa fa-globe"></i> All Offices
                      </span>
                    <?php elseif ($user['office_name']): ?>
                      <span style="background: #e8f5e9; color: #2e7d32; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        <i class="fa fa-building"></i> <?= htmlspecialchars($user['office_name']) ?>
                      </span>
                    <?php else: ?>
                      <span style="color: #95a5a6;">-</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 15px;">
                    <?= $user['staff_name'] ? htmlspecialchars($user['staff_name']) : '<span style="color: #95a5a6;">-</span>' ?>
                  </td>
                  <td style="padding: 15px;">
                    <?php if ($user['active_status'] == 1): ?>
                      <span style="background: #d4edda; color: #155724; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                        <i class="fa fa-check-circle"></i> Active
                      </span>
                    <?php else: ?>
                      <span style="background: #f8d7da; color: #721c24; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600;">
                        <i class="fa fa-times-circle"></i> Inactive
                      </span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 15px;">
                    <?php if ($user['last_login']): ?>
                      <small><?= date('M d, Y H:i', strtotime($user['last_login'])) ?></small>
                    <?php else: ?>
                      <span style="color: #95a5a6;">Never</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 15px;">
                    <div style="display: flex; gap: 8px;">
                      <?php if (hasPermission('user_edit')): ?>
                      <a href="edit_user.php?id=<?= $user['user_id'] ?>" 
                         class="btn btn-sm btn-primary" 
                         title="Edit User"
                         style="padding: 6px 12px;">
                        <i class="fa fa-edit"></i>
                      </a>
                      <?php endif; ?>
                      
                      <?php if (hasPermission('user_delete') && $user['user_id'] != $_SESSION['user_id']): ?>
                      <a href="delete_user.php?user_id=<?= $user['user_id'] ?>" 
                         class="btn btn-sm btn-danger" 
                         title="Delete User"
                         onclick="return confirm('Are you sure you want to delete user \'<?= htmlspecialchars($user['username']) ?>\'? This action cannot be undone.')"
                         style="padding: 6px 12px;">
                        <i class="fa fa-trash"></i>
                      </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
      <!-- /page content -->
    </div>
  </div>
  <?php require 'footer.php';?>
</body>
</html>
