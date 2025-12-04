<?php
/**
 * Enhanced User Management - Add New User
 * Features:
 * - Branch/Office assignment
 * - Role selection with permission preview
 * - Granular permission overrides
 * - Status update permissions
 * - Professional UI with accordion sections
 */

// Debug mode
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// Safe require helper
function require_file_or_die($path) {
    $candidates = [__DIR__ . '/' . $path, __DIR__ . '/../' . $path, $path];
    foreach ($candidates as $c) {
        if (file_exists($c)) {
            require $c;
            return;
        }
    }
    header('HTTP/1.1 500 Internal Server Error');
    exit("Missing required file: $path");
}

require_file_or_die('conn.php');
require_file_or_die('check_auth.php');
require_file_or_die('includes/csrf_helper.php');

requirePermission('user_create');

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    // CSRF Protection
    if (!csrf_verify_request('add_user')) {
        csrf_error_exit();
    }
    
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $role_id = intval($_POST['role_id']);
    $staff_id = !empty($_POST['staff_id']) ? intval($_POST['staff_id']) : NULL;
    $office_id = !empty($_POST['office_id']) ? intval($_POST['office_id']) : NULL;
    $can_access_all_offices = isset($_POST['can_access_all_offices']) ? 1 : 0;
    $active_status = isset($_POST['active_status']) ? 1 : 0;
    
    // Get selected permissions and status permissions
    $selected_permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    $selected_status_permissions = isset($_POST['status_permissions']) ? $_POST['status_permissions'] : [];

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($role_id)) {
        $error = "Please fill in all required fields";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = "Username must be between 3 and 50 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username can only contain letters, numbers, and underscores";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!$can_access_all_offices && empty($office_id)) {
        $error = "Please select an office/branch or enable 'Access All Offices'";
    } else {
        // Check duplicates
        $check_username = mysqli_query($conn, "SELECT user_id FROM tbl_users WHERE username='$username'");
        $check_email = mysqli_query($conn, "SELECT user_id FROM tbl_users WHERE email='$email'");
        
        if (mysqli_num_rows($check_username) > 0) {
            $error = "Username already exists";
        } elseif (mysqli_num_rows($check_email) > 0) {
            $error = "Email already exists";
        } else {
            mysqli_begin_transaction($conn);
            
            try {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $staff_id_value = $staff_id ? $staff_id : 'NULL';
                $office_id_value = $office_id ? $office_id : 'NULL';
                
                $insert_query = "INSERT INTO tbl_users 
                    (username, email, password, full_name, role_id, staff_id, office_id, can_access_all_offices, active_status, created_at) 
                    VALUES 
                    ('$username', '$email', '$hashed_password', '$full_name', $role_id, $staff_id_value, $office_id_value, $can_access_all_offices, $active_status, NOW())";
                
                if (!mysqli_query($conn, $insert_query)) {
                    throw new Exception("Error creating user: " . mysqli_error($conn));
                }
                
                $new_user_id = mysqli_insert_id($conn);
                
                // Insert user-specific permission overrides (if any selected)
                if (!empty($selected_permissions)) {
                    // Check if tbl_user_permissions exists
                    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_user_permissions'");
                    if (mysqli_num_rows($table_check) > 0) {
                        foreach ($selected_permissions as $perm_id) {
                            $perm_id = intval($perm_id);
                            $perm_insert = "INSERT IGNORE INTO tbl_user_permissions (user_id, permission_id, granted) VALUES ($new_user_id, $perm_id, 1)";
                            mysqli_query($conn, $perm_insert);
                        }
                    }
                }
                
                // Insert status update permissions
                if (!empty($selected_status_permissions)) {
                    // Check if tbl_user_status_permissions exists
                    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_user_status_permissions'");
                    if (mysqli_num_rows($table_check) > 0) {
                        foreach ($selected_status_permissions as $status_name) {
                            $status_name = mysqli_real_escape_string($conn, $status_name);
                            $status_insert = "INSERT IGNORE INTO tbl_user_status_permissions (user_id, status_name, can_update_to) VALUES ($new_user_id, '$status_name', 1)";
                            mysqli_query($conn, $status_insert);
                        }
                    }
                }
                
                // Log user creation
                $log_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_user_access_log'");
                if (mysqli_num_rows($log_table_check) > 0) {
                    $creator_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                    $log_query = "INSERT INTO tbl_user_access_log (user_id, action_type, module, record_id, details, ip_address) 
                                  VALUES ($creator_id, 'create', 'user', '$new_user_id', '{\"created_user\":\"$username\"}', '$ip')";
                    mysqli_query($conn, $log_query);
                }
                
                mysqli_commit($conn);
                
                $_SESSION['success_msg'] = "User '$username' created successfully!";
                header("Location: users.php?success=1");
                exit;
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = $e->getMessage();
            }
        }
    }
}

// Fetch data for dropdowns
$roles_query = "SELECT role_id, role_name, role_description FROM tbl_roles ORDER BY role_name";
$roles_result = mysqli_query($conn, $roles_query);

$staff_query = "SELECT s.staff_id, s.staff_name, s.staff_role, o.office_name 
                FROM tbl_staff s 
                LEFT JOIN tbl_offices o ON s.office_id = o.office_id 
                WHERE s.active_status = 1 
                ORDER BY s.staff_name";
$staff_result = mysqli_query($conn, $staff_query);

$offices_query = "SELECT office_id, office_name, office_address FROM tbl_offices ORDER BY office_name";
$offices_result = mysqli_query($conn, $offices_query);

// Fetch all permissions grouped by module
$permissions_query = "SELECT p.*, pg.group_name, pg.display_order 
                      FROM tbl_permissions p
                      LEFT JOIN tbl_permission_groups pg ON p.module_name = pg.module_name
                      ORDER BY COALESCE(pg.display_order, 999), p.module_name, p.permission_name";
$permissions_result = mysqli_query($conn, $permissions_query);

$permissions_by_module = [];
if ($permissions_result) {
    while ($perm = mysqli_fetch_assoc($permissions_result)) {
        $module = $perm['module_name'] ?: 'Other';
        if (!isset($permissions_by_module[$module])) {
            $permissions_by_module[$module] = [];
        }
        $permissions_by_module[$module][] = $perm;
    }
}

// Fetch status hierarchy for status permissions
$status_query = "SELECT status_name, status_order, is_final FROM tbl_status_hierarchy ORDER BY status_order";
$status_result = mysqli_query($conn, $status_query);
$statuses = [];
if ($status_result) {
    while ($st = mysqli_fetch_assoc($status_result)) {
        $statuses[] = $st;
    }
}

// Fetch role permissions for preview
$role_permissions = [];
$rp_query = "SELECT rp.role_id, GROUP_CONCAT(p.permission_key) as perms 
             FROM tbl_role_permissions rp 
             JOIN tbl_permissions p ON rp.permission_id = p.permission_id 
             GROUP BY rp.role_id";
$rp_result = mysqli_query($conn, $rp_query);
if ($rp_result) {
    while ($rp = mysqli_fetch_assoc($rp_result)) {
        $role_permissions[$rp['role_id']] = explode(',', $rp['perms']);
    }
}

require_file_or_die('top_header.php');
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
.user-form-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    padding: 0;
    margin: 20px;
    overflow: hidden;
}

.form-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 30px;
}

.form-header h2 {
    margin: 0;
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-header p {
    margin: 8px 0 0 0;
    opacity: 0.9;
    font-size: 14px;
}

.form-body {
    padding: 30px;
}

/* Section Styling */
.form-section {
    margin-bottom: 25px;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
}

.section-header {
    background: #f8f9fa;
    padding: 15px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
    transition: background 0.3s;
}

.section-header:hover {
    background: #e9ecef;
}

.section-header h3 {
    margin: 0;
    font-size: 16px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header .toggle-icon {
    transition: transform 0.3s;
}

.section-header.collapsed .toggle-icon {
    transform: rotate(-90deg);
}

.section-content {
    padding: 20px;
    display: block;
}

.section-content.collapsed {
    display: none;
}

/* Form Grid */
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
    font-size: 14px;
}

.form-group label .required {
    color: #e74c3c;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e1e1;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.form-control.with-icon {
    padding-left: 42px;
}

.input-wrapper {
    position: relative;
}

.input-wrapper > i:first-child {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 14px;
}

.toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
    cursor: pointer;
}

/* Permission Grid */
.permission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 10px;
}

.permission-item {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
    transition: all 0.2s;
}

.permission-item:hover {
    background: #e7f3ff;
    border-color: #667eea;
}

.permission-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 10px;
    cursor: pointer;
}

.permission-item label {
    margin: 0;
    font-size: 13px;
    cursor: pointer;
    flex: 1;
}

.permission-item .perm-desc {
    font-size: 11px;
    color: #666;
    display: block;
}

/* Module Header */
.module-header {
    background: #667eea;
    color: white;
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 10px;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.module-header .select-all {
    font-size: 12px;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    padding: 4px 10px;
    border-radius: 4px;
    cursor: pointer;
}

.module-header .select-all:hover {
    background: rgba(255,255,255,0.3);
}

.permission-module {
    margin-bottom: 20px;
}

/* Status Permission Grid */
.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 10px;
}

.status-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px solid #e0e0e0;
    transition: all 0.2s;
}

.status-item:hover {
    border-color: #667eea;
}

.status-item.final {
    background: #fff3cd;
    border-color: #ffc107;
}

.status-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 10px;
}

.status-item label {
    margin: 0;
    font-size: 14px;
    font-weight: 500;
}

.status-badge {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 3px;
    margin-left: 5px;
}

.status-badge.final {
    background: #dc3545;
    color: white;
}

/* Office Selection */
.office-card {
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.office-card:hover {
    border-color: #667eea;
    background: #f8f9ff;
}

.office-card.selected {
    border-color: #667eea;
    background: #e7f3ff;
}

.office-card input[type="radio"] {
    display: none;
}

.office-card .office-name {
    font-weight: 600;
    font-size: 15px;
    color: #333;
}

.office-card .office-address {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

/* Checkbox Toggle */
.toggle-switch {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    background: #f0f7ff;
    border-radius: 8px;
    margin-bottom: 15px;
}

.toggle-switch input[type="checkbox"] {
    width: 20px;
    height: 20px;
}

.toggle-switch label {
    margin: 0;
    font-weight: 500;
}

.toggle-switch .hint {
    font-size: 12px;
    color: #666;
}

/* Buttons */
.btn-group {
    display: flex;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

.btn {
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102,126,234,0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

/* Alert */
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

.alert-info {
    background: #e7f3ff;
    color: #0c5460;
    border-left: 4px solid #2196F3;
}

/* Role Preview */
.role-preview {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
    display: none;
}

.role-preview.active {
    display: block;
}

.role-preview h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #333;
}

.role-preview .perm-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.role-preview .perm-tag {
    background: #667eea;
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
}

/* Password Strength */
.password-strength {
    margin-top: 5px;
}

.strength-bar {
    height: 4px;
    border-radius: 2px;
    background: #e1e1e1;
    overflow: hidden;
}

.strength-bar-fill {
    height: 100%;
    transition: all 0.3s;
    width: 0%;
}

.strength-weak { background: #dc3545; width: 33%; }
.strength-medium { background: #ffc107; width: 66%; }
.strength-strong { background: #28a745; width: 100%; }

/* Quick Action Buttons */
.quick-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.quick-btn {
    padding: 8px 15px;
    border: 1px solid #667eea;
    background: white;
    color: #667eea;
    border-radius: 5px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.quick-btn:hover {
    background: #667eea;
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .form-body {
        padding: 15px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .permission-grid {
        grid-template-columns: 1fr;
    }
    
    .status-grid {
        grid-template-columns: 1fr;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<body class="nav-md">
<div class="container body">
<div class="main_container">
    <?php require_file_or_die('left_panel.php'); ?>
    <?php require_file_or_die('header_banner.php'); ?>
    
    <div class="right_col" role="main">
        <div class="user-form-container">
            
            <div class="form-header">
                <h2><i class="fas fa-user-plus"></i> Add New User</h2>
                <p>Create user account with role, office assignment, and granular permissions</p>
            </div>
            
            <div class="form-body">
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="userForm">
                    <?php echo csrf_token_field('add_user'); ?>
                    
                    <!-- Section 1: Basic Information -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-user"></i> Basic Information</h3>
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="section-content">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="username">
                                        Username <span class="required">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-user"></i>
                                        <input type="text" name="username" id="username" class="form-control with-icon" 
                                               placeholder="Enter username (letters, numbers, underscore)" required
                                               pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="50"
                                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">
                                        Email <span class="required">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" name="email" id="email" class="form-control with-icon" 
                                               placeholder="Enter email address" required
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name">
                                    Full Name <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <i class="fas fa-id-card"></i>
                                    <input type="text" name="full_name" id="full_name" class="form-control with-icon" 
                                           placeholder="Enter full name" required
                                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password">
                                        Password <span class="required">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" name="password" id="password" class="form-control with-icon" 
                                               placeholder="Min 6 characters" required minlength="6">
                                        <span class="toggle-password" onclick="togglePassword('password', this)">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                    <div class="password-strength">
                                        <div class="strength-bar">
                                            <div class="strength-bar-fill" id="strengthBar"></div>
                                        </div>
                                        <span id="strengthText" style="font-size:12px;"></span>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">
                                        Confirm Password <span class="required">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control with-icon" 
                                               placeholder="Re-enter password" required minlength="6">
                                        <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="staff_id">
                                    Link to Staff Member (Optional)
                                </label>
                                <div class="input-wrapper">
                                    <i class="fas fa-users"></i>
                                    <select name="staff_id" id="staff_id" class="form-control with-icon">
                                        <option value="">-- Not linked to any staff --</option>
                                        <?php if ($staff_result && mysqli_num_rows($staff_result) > 0): 
                                            mysqli_data_seek($staff_result, 0);
                                            while ($staff = mysqli_fetch_assoc($staff_result)): ?>
                                        <option value="<?php echo $staff['staff_id']; ?>">
                                            <?php echo htmlspecialchars($staff['staff_name'] . ' (' . $staff['staff_role'] . ')'); ?>
                                            <?php if ($staff['office_name']): ?> - <?php echo htmlspecialchars($staff['office_name']); ?><?php endif; ?>
                                        </option>
                                        <?php endwhile; endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="toggle-switch">
                                <input type="checkbox" name="active_status" id="active_status" value="1" checked>
                                <div>
                                    <label for="active_status">Active Account</label>
                                    <div class="hint">User can login when active</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 2: Role & Office Assignment -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-building"></i> Role & Office Assignment</h3>
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="section-content">
                            
                            <div class="form-group">
                                <label for="role_id">
                                    User Role <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <i class="fas fa-user-tag"></i>
                                    <select name="role_id" id="role_id" class="form-control with-icon" required onchange="showRolePreview(this.value)">
                                        <option value="">-- Select Role --</option>
                                        <?php if ($roles_result && mysqli_num_rows($roles_result) > 0): 
                                            mysqli_data_seek($roles_result, 0);
                                            while ($role = mysqli_fetch_assoc($roles_result)): ?>
                                        <option value="<?php echo $role['role_id']; ?>" 
                                                data-desc="<?php echo htmlspecialchars($role['role_description'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($role['role_name']); ?>
                                        </option>
                                        <?php endwhile; endif; ?>
                                    </select>
                                </div>
                                <div class="role-preview" id="rolePreview">
                                    <h4>Role Permissions:</h4>
                                    <div class="perm-tags" id="rolePermTags"></div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info" style="margin-top:15px;">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    <strong>Office Assignment:</strong> Assign user to a specific branch/office. They will only see dockets and data related to their assigned office.
                                </div>
                            </div>
                            
                            <div class="toggle-switch">
                                <input type="checkbox" name="can_access_all_offices" id="can_access_all_offices" value="1" onchange="toggleOfficeSelection()">
                                <div>
                                    <label for="can_access_all_offices">Access All Offices</label>
                                    <div class="hint">Enable to allow access to dockets from all offices (for admins/managers)</div>
                                </div>
                            </div>
                            
                            <div id="officeSelectionArea">
                                <label style="font-weight:600; margin-bottom:10px; display:block;">
                                    Select Office/Branch <span class="required">*</span>
                                </label>
                                <div id="officeList">
                                    <?php if ($offices_result && mysqli_num_rows($offices_result) > 0): 
                                        mysqli_data_seek($offices_result, 0);
                                        while ($office = mysqli_fetch_assoc($offices_result)): ?>
                                    <label class="office-card" onclick="selectOffice(<?php echo $office['office_id']; ?>)">
                                        <input type="radio" name="office_id" value="<?php echo $office['office_id']; ?>">
                                        <div class="office-name">
                                            <i class="fas fa-building"></i> 
                                            <?php echo htmlspecialchars($office['office_name']); ?>
                                        </div>
                                        <?php if ($office['office_address']): ?>
                                        <div class="office-address">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?php echo htmlspecialchars($office['office_address']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </label>
                                    <?php endwhile; else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        No offices found. <a href="offices.php">Add offices first</a>.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- Section 3: Status Update Permissions -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-exchange-alt"></i> Status Update Permissions</h3>
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="section-content">
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    Select which statuses this user can update dockets to. Leave all unchecked to use role-based permissions.
                                </div>
                            </div>
                            
                            <div class="quick-actions">
                                <button type="button" class="quick-btn" onclick="selectAllStatuses()">
                                    <i class="fas fa-check-double"></i> Select All
                                </button>
                                <button type="button" class="quick-btn" onclick="deselectAllStatuses()">
                                    <i class="fas fa-times"></i> Deselect All
                                </button>
                                <button type="button" class="quick-btn" onclick="selectDeliveryStatuses()">
                                    <i class="fas fa-truck"></i> Delivery Only
                                </button>
                            </div>
                            
                            <div class="status-grid">
                                <?php foreach ($statuses as $status): ?>
                                <div class="status-item <?php echo $status['is_final'] ? 'final' : ''; ?>">
                                    <input type="checkbox" name="status_permissions[]" 
                                           id="status_<?php echo md5($status['status_name']); ?>"
                                           value="<?php echo htmlspecialchars($status['status_name']); ?>"
                                           class="status-checkbox">
                                    <label for="status_<?php echo md5($status['status_name']); ?>">
                                        <?php echo htmlspecialchars($status['status_name']); ?>
                                        <?php if ($status['is_final']): ?>
                                        <span class="status-badge final">Final</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- Section 4: Additional Permissions (Optional Overrides) -->
                    <div class="form-section">
                        <div class="section-header collapsed" onclick="toggleSection(this)">
                            <h3><i class="fas fa-key"></i> Additional Permissions (Optional)</h3>
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="section-content collapsed">
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    <strong>Optional:</strong> These permissions are in addition to the role's permissions. 
                                    Use this to grant extra permissions beyond the selected role.
                                </div>
                            </div>
                            
                            <?php foreach ($permissions_by_module as $module => $perms): ?>
                            <div class="permission-module">
                                <div class="module-header">
                                    <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($module); ?></span>
                                    <button type="button" class="select-all" onclick="toggleModulePermissions('<?php echo htmlspecialchars($module); ?>')">
                                        Toggle All
                                    </button>
                                </div>
                                <div class="permission-grid" data-module="<?php echo htmlspecialchars($module); ?>">
                                    <?php foreach ($perms as $perm): ?>
                                    <div class="permission-item">
                                        <input type="checkbox" name="permissions[]" 
                                               id="perm_<?php echo $perm['permission_id']; ?>"
                                               value="<?php echo $perm['permission_id']; ?>"
                                               data-key="<?php echo htmlspecialchars($perm['permission_key']); ?>"
                                               class="perm-checkbox">
                                        <label for="perm_<?php echo $perm['permission_id']; ?>">
                                            <?php echo htmlspecialchars($perm['permission_name']); ?>
                                            <?php if ($perm['permission_description']): ?>
                                            <span class="perm-desc"><?php echo htmlspecialchars($perm['permission_description']); ?></span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="btn-group">
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create User
                        </button>
                        <a href="users.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                    
                </form>
                
            </div>
        </div>
    </div>
    
    <?php require_file_or_die('footer.php'); ?>
</div>
</div>

<script>
// Role permissions data from PHP
const rolePermissions = <?php echo json_encode($role_permissions); ?>;

// Toggle section accordion
function toggleSection(header) {
    header.classList.toggle('collapsed');
    const content = header.nextElementSibling;
    content.classList.toggle('collapsed');
}

// Toggle password visibility
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);
    const i = icon.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        i.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        i.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const bar = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    bar.className = 'strength-bar-fill';
    if (strength <= 2) {
        bar.classList.add('strength-weak');
        text.textContent = 'Weak';
        text.style.color = '#dc3545';
    } else if (strength <= 4) {
        bar.classList.add('strength-medium');
        text.textContent = 'Medium';
        text.style.color = '#ffc107';
    } else {
        bar.classList.add('strength-strong');
        text.textContent = 'Strong';
        text.style.color = '#28a745';
    }
});

// Show role permissions preview
function showRolePreview(roleId) {
    const preview = document.getElementById('rolePreview');
    const tagsContainer = document.getElementById('rolePermTags');
    
    if (roleId && rolePermissions[roleId]) {
        tagsContainer.innerHTML = '';
        rolePermissions[roleId].forEach(perm => {
            const tag = document.createElement('span');
            tag.className = 'perm-tag';
            tag.textContent = perm.replace(/_/g, ' ');
            tagsContainer.appendChild(tag);
        });
        preview.classList.add('active');
    } else {
        preview.classList.remove('active');
    }
}

// Toggle office selection visibility
function toggleOfficeSelection() {
    const checkbox = document.getElementById('can_access_all_offices');
    const officeArea = document.getElementById('officeSelectionArea');
    
    if (checkbox.checked) {
        officeArea.style.display = 'none';
        // Clear selection
        document.querySelectorAll('input[name="office_id"]').forEach(r => r.checked = false);
        document.querySelectorAll('.office-card').forEach(c => c.classList.remove('selected'));
    } else {
        officeArea.style.display = 'block';
    }
}

// Select office card
function selectOffice(officeId) {
    document.querySelectorAll('.office-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    const radio = document.querySelector(`input[name="office_id"][value="${officeId}"]`);
    if (radio) {
        radio.checked = true;
        radio.closest('.office-card').classList.add('selected');
    }
}

// Toggle module permissions
function toggleModulePermissions(module) {
    const grid = document.querySelector(`.permission-grid[data-module="${module}"]`);
    const checkboxes = grid.querySelectorAll('.perm-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
    });
}

// Status permission helpers
function selectAllStatuses() {
    document.querySelectorAll('.status-checkbox').forEach(cb => cb.checked = true);
}

function deselectAllStatuses() {
    document.querySelectorAll('.status-checkbox').forEach(cb => cb.checked = false);
}

function selectDeliveryStatuses() {
    deselectAllStatuses();
    const deliveryStatuses = ['Out for Delivery', 'Delivered', 'Delayed', 'Failed Delivery'];
    document.querySelectorAll('.status-checkbox').forEach(cb => {
        if (deliveryStatuses.includes(cb.value)) {
            cb.checked = true;
        }
    });
}

// Form validation
document.getElementById('userForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const canAccessAll = document.getElementById('can_access_all_offices').checked;
    const officeSelected = document.querySelector('input[name="office_id"]:checked');
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match');
        return false;
    }
    
    if (!canAccessAll && !officeSelected) {
        e.preventDefault();
        alert('Please select an office or enable "Access All Offices"');
        return false;
    }
    
    return true;
});
</script>

</body>
</html>
