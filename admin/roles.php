<?php
require 'check_auth.php';
requirePermission('role_manage');
require 'conn.php';

// Check for success/error messages
$success_message = '';
$error_message = '';
if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}

// Handle delete role
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['role_id'])) {
    $role_id = intval($_GET['role_id']);
    
    // Check if role has users
    $check_users = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_users WHERE role_id = $role_id");
    $user_count = mysqli_fetch_assoc($check_users)['cnt'];
    
    if ($user_count > 0) {
        header("Location: roles.php?error=Cannot delete role with assigned users. Please reassign users first.");
        exit;
    }
    
    // Delete role permissions first
    mysqli_query($conn, "DELETE FROM tbl_role_permissions WHERE role_id = $role_id");
    
    // Delete role
    if (mysqli_query($conn, "DELETE FROM tbl_roles WHERE role_id = $role_id")) {
        header("Location: roles.php?success=Role deleted successfully");
        exit;
    } else {
        header("Location: roles.php?error=Error deleting role");
        exit;
    }
}

// Get all roles with user count and permission count
$roles_query = "SELECT r.*, 
                COUNT(DISTINCT u.user_id) as user_count,
                COUNT(DISTINCT rp.permission_id) as permission_count
                FROM tbl_roles r
                LEFT JOIN tbl_users u ON r.role_id = u.role_id
                LEFT JOIN tbl_role_permissions rp ON r.role_id = rp.role_id
                GROUP BY r.role_id
                ORDER BY r.created_at DESC";
$roles_result = mysqli_query($conn, $roles_query);

require 'top_header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
.roles-container {
    background: #f8fafc;
    min-height: 100vh;
    padding: 20px;
}

.page-header {
    background: white;
    padding: 25px 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

.page-header h2 {
    margin: 0;
    font-weight: 800;
    color: #2c3e50;
    font-size: 28px;
}

.page-header p {
    margin: 8px 0 0 0;
    color: #7f8c8d;
    font-size: 14px;
}

.header-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.role-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s;
    border-left: 4px solid #667eea;
}

.role-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.role-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
}

.role-name {
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 5px 0;
}

.role-description {
    color: #7f8c8d;
    font-size: 14px;
    margin: 5px 0 15px 0;
    line-height: 1.5;
}

.role-stats {
    display: flex;
    gap: 20px;
    padding: 15px 0;
    border-top: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
    margin: 15px 0;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stat-item i {
    color: #667eea;
    font-size: 18px;
}

.stat-value {
    font-weight: 700;
    font-size: 18px;
    color: #2c3e50;
}

.stat-label {
    font-size: 12px;
    color: #7f8c8d;
}

.role-actions {
    display: flex;
    gap: 8px;
    margin-top: 15px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102,126,234,0.4);
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
}

.btn-warning {
    background: #ffc107;
    color: #333;
}

.btn-warning:hover {
    background: #e0a800;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.alert .close {
    margin-left: auto;
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: inherit;
    opacity: 0.5;
}

.alert .close:hover {
    opacity: 1;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.empty-state i {
    font-size: 64px;
    color: #e9ecef;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.empty-state p {
    color: #7f8c8d;
    margin-bottom: 20px;
}

.role-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-super {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.badge-admin {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-default {
    background: #e9ecef;
    color: #495057;
}
</style>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <div class="right_col" role="main">
        <div class="roles-container">
          
          <?php if (!empty($success_message)): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success_message; ?></span>
            <button class="close" onclick="this.parentElement.remove()">×</button>
          </div>
          <?php endif; ?>

          <?php if (!empty($error_message)): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error_message; ?></span>
            <button class="close" onclick="this.parentElement.remove()">×</button>
          </div>
          <?php endif; ?>

          <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
              <div>
                <h2><i class="fas fa-shield-alt"></i> Roles & Permissions</h2>
                <p>Manage user roles and their associated permissions</p>
              </div>
              <div class="header-actions">
                <a href="add_role.php" class="btn btn-success">
                  <i class="fas fa-plus-circle"></i> Add New Role
                </a>
                <a href="users.php" class="btn btn-primary">
                  <i class="fas fa-users"></i> View Users
                </a>
              </div>
            </div>
          </div>

          <?php if (mysqli_num_rows($roles_result) > 0): ?>
            <div class="roles-grid">
              <?php while ($role = mysqli_fetch_assoc($roles_result)): ?>
                <div class="role-card">
                  <div class="role-card-header">
                    <div style="flex: 1;">
                      <h3 class="role-name">
                        <i class="fas fa-user-shield" style="color: #667eea;"></i>
                        <?php echo htmlspecialchars($role['role_name']); ?>
                      </h3>
                      <?php if ($role['role_name'] == 'Super Admin'): ?>
                        <span class="role-badge badge-super">System Role</span>
                      <?php elseif (strpos(strtolower($role['role_name']), 'admin') !== false): ?>
                        <span class="role-badge badge-admin">Admin</span>
                      <?php else: ?>
                        <span class="role-badge badge-default">Custom</span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <p class="role-description">
                    <?php echo !empty($role['description']) ? htmlspecialchars($role['description']) : 'No description provided'; ?>
                  </p>

                  <div class="role-stats">
                    <div class="stat-item">
                      <i class="fas fa-users"></i>
                      <div>
                        <div class="stat-value"><?php echo $role['user_count']; ?></div>
                        <div class="stat-label">Users</div>
                      </div>
                    </div>
                    <div class="stat-item">
                      <i class="fas fa-key"></i>
                      <div>
                        <div class="stat-value"><?php echo $role['permission_count']; ?></div>
                        <div class="stat-label">Permissions</div>
                      </div>
                    </div>
                  </div>

                  <div class="role-actions">
                    <a href="view_role.php?role_id=<?php echo $role['role_id']; ?>" class="btn btn-info btn-sm" title="View Details">
                      <i class="fas fa-eye"></i> View
                    </a>
                    <a href="edit_role.php?role_id=<?php echo $role['role_id']; ?>" class="btn btn-warning btn-sm" title="Edit Role">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <?php if ($role['role_name'] != 'Super Admin'): ?>
                    <a href="roles.php?action=delete&role_id=<?php echo $role['role_id']; ?>" 
                       class="btn btn-danger btn-sm" 
                       onclick="return confirm('Are you sure you want to delete this role? This action cannot be undone.')"
                       title="Delete Role">
                      <i class="fas fa-trash"></i> Delete
                    </a>
                    <?php endif; ?>
                  </div>

                  <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef; font-size: 12px; color: #7f8c8d;">
                    <i class="far fa-clock"></i> Created: <?php echo date('M d, Y', strtotime($role['created_at'])); ?>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <i class="fas fa-shield-alt"></i>
              <h3>No Roles Found</h3>
              <p>Get started by creating your first role with custom permissions</p>
              <a href="add_role.php" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Create First Role
              </a>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <?php require 'footer.php'; ?>
    </div>
  </div>

<script>
// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>

</body>
</html>
