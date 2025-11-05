<?php
/**
 * Display All Super Admin Credentials
 * This page shows all admin accounts in the system
 */
require 'conn.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Credentials - NSFS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            color: white;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 40px;
        }
        .section h2 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 10px;
            overflow: hidden;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #e1e1e1;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        .badge-primary {
            background: #cce5ff;
            color: #004085;
        }
        .credential-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid #667eea;
        }
        .credential-box h3 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .cred-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .cred-item:last-child {
            border-bottom: none;
        }
        .cred-label {
            font-weight: 600;
            color: #495057;
        }
        .cred-value {
            color: #c7254e;
            background: #f9f2f4;
            padding: 5px 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin: 10px 5px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102,126,234,0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }
        .icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-key"></i> Admin Credentials</h1>
            <p>Complete overview of all admin accounts in the system</p>
        </div>
        
        <div class="content">
            
            <!-- NEW USER MANAGEMENT SYSTEM -->
            <div class="section">
                <h2><i class="fas fa-users-cog icon"></i>New User Management System (tbl_users)</h2>
                
                <?php
                // Check if table exists
                $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_users'");
                if (mysqli_num_rows($check_table) > 0) {
                    $query = "SELECT u.*, r.role_name 
                              FROM tbl_users u 
                              LEFT JOIN tbl_roles r ON u.role_id = r.role_id 
                              ORDER BY u.user_id";
                    $result = mysqli_query($conn, $query);
                    
                    if (mysqli_num_rows($result) > 0) {
                        echo "<table>";
                        echo "<tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                              </tr>";
                        
                        while ($user = mysqli_fetch_assoc($result)) {
                            $status_badge = $user['active_status'] == 1 ? 
                                '<span class="badge badge-success">Active</span>' : 
                                '<span class="badge badge-danger">Inactive</span>';
                            
                            echo "<tr>";
                            echo "<td>" . $user['user_id'] . "</td>";
                            echo "<td><strong>" . htmlspecialchars($user['username']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                            echo "<td><span class='badge badge-primary'>" . htmlspecialchars($user['role_name']) . "</span></td>";
                            echo "<td>" . $status_badge . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($user['created_at'])) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                        
                        // Show detailed credentials for each user
                        mysqli_data_seek($result, 0); // Reset pointer
                        while ($user = mysqli_fetch_assoc($result)) {
                            echo "<div class='credential-box'>";
                            echo "<h3><i class='fas fa-user-shield'></i> " . htmlspecialchars($user['full_name']) . "</h3>";
                            echo "<div class='cred-item'>";
                            echo "<span class='cred-label'>Username:</span>";
                            echo "<span class='cred-value'>" . htmlspecialchars($user['username']) . "</span>";
                            echo "</div>";
                            echo "<div class='cred-item'>";
                            echo "<span class='cred-label'>Password:</span>";
                            echo "<span class='cred-value'>(Securely hashed - use password_verify)</span>";
                            echo "</div>";
                            echo "<div class='cred-item'>";
                            echo "<span class='cred-label'>Role:</span>";
                            echo "<span class='cred-value'>" . htmlspecialchars($user['role_name']) . "</span>";
                            echo "</div>";
                            echo "<div class='cred-item'>";
                            echo "<span class='cred-label'>Login URL:</span>";
                            echo "<span class='cred-value'>login_new.php</span>";
                            echo "</div>";
                            echo "</div>";
                        }
                        
                        echo "<div class='alert alert-info'>";
                        echo "<strong><i class='fas fa-info-circle'></i> Password Information:</strong><br>";
                        echo "• Passwords are hashed using PHP's password_hash() function<br>";
                        echo "• You created a user with username: <strong>sukanta481</strong> and password: <strong>Sukanta@0050</strong><br>";
                        echo "• Use <code>login_new.php</code> to login with these credentials";
                        echo "</div>";
                        
                    } else {
                        echo "<div class='alert alert-warning'>";
                        echo "<i class='fas fa-exclamation-triangle'></i> No users found in the new system.<br>";
                        echo "Run <a href='create_super_admin.php'>create_super_admin.php</a> to create your Super Admin account.";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='alert alert-warning'>";
                    echo "<i class='fas fa-exclamation-triangle'></i> User management tables not found.<br>";
                    echo "Run <a href='setup_user_management.php'>setup_user_management.php</a> first.";
                    echo "</div>";
                }
                ?>
            </div>
            
            <!-- OLD/LEGACY SYSTEM -->
            <div class="section">
                <h2><i class="fas fa-user-lock icon"></i>Legacy System (tbl_administrator)</h2>
                
                <?php
                $check_legacy = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_administrator'");
                if (mysqli_num_rows($check_legacy) > 0) {
                    $legacy_query = "SELECT * FROM tbl_administrator ORDER BY admin_id";
                    $legacy_result = mysqli_query($conn, $legacy_query);
                    
                    if (mysqli_num_rows($legacy_result) > 0) {
                        echo "<table>";
                        echo "<tr>
                                <th>ID</th>
                                <th>Admin Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                              </tr>";
                        
                        while ($admin = mysqli_fetch_assoc($legacy_result)) {
                            $status_badge = $admin['status'] == 'Active' ? 
                                '<span class="badge badge-success">Active</span>' : 
                                '<span class="badge badge-danger">Inactive</span>';
                            
                            echo "<tr>";
                            echo "<td>" . $admin['admin_id'] . "</td>";
                            echo "<td><strong>" . htmlspecialchars($admin['adminname']) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($admin['phone']) . "</td>";
                            echo "<td>" . $status_badge . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                        
                        // Show credentials
                        mysqli_data_seek($legacy_result, 0);
                        while ($admin = mysqli_fetch_assoc($legacy_result)) {
                            echo "<div class='credential-box'>";
                            echo "<h3><i class='fas fa-user'></i> " . htmlspecialchars($admin['adminname']) . " (Legacy)</h3>";
                            echo "<div class='cred-item'>";
                            echo "<span class='cred-label'>Admin Name:</span>";
                            echo "<span class='cred-value'>" . htmlspecialchars($admin['adminname']) . "</span>";
                            echo "</div>";
                            echo "<div class='cred-item'>";
                            echo "<span class='cred-label'>Password:</span>";
                            echo "<span class='cred-value'>(MD5 hashed)</span>";
                            echo "</div>";
                            echo "<div class='cred-item'>";
                            echo "<span class='cred-label'>Login URL:</span>";
                            echo "<span class='cred-value'>login.php</span>";
                            echo "</div>";
                            echo "</div>";
                        }
                        
                        echo "<div class='alert alert-warning'>";
                        echo "<strong><i class='fas fa-exclamation-triangle'></i> Legacy System Notice:</strong><br>";
                        echo "• This is the old login system using MD5 hashing<br>";
                        echo "• Passwords are stored with weak MD5 encryption<br>";
                        echo "• Consider migrating to the new system for better security<br>";
                        echo "• Use <code>login.php</code> to login with legacy accounts";
                        echo "</div>";
                        
                    } else {
                        echo "<p>No legacy administrators found.</p>";
                    }
                } else {
                    echo "<p>Legacy administrator table not found.</p>";
                }
                ?>
            </div>
            
            <!-- RECOMMENDED CREDENTIALS -->
            <div class="section">
                <h2><i class="fas fa-star icon"></i>Recommended Login Credentials</h2>
                
                <div class="credential-box" style="border-left-color: #28a745;">
                    <h3><i class="fas fa-check-circle" style="color: #28a745;"></i> New System (Recommended)</h3>
                    <div class="cred-item">
                        <span class="cred-label">Username:</span>
                        <span class="cred-value">sukanta481</span>
                    </div>
                    <div class="cred-item">
                        <span class="cred-label">Password:</span>
                        <span class="cred-value">Sukanta@0050</span>
                    </div>
                    <div class="cred-item">
                        <span class="cred-label">Role:</span>
                        <span class="cred-value">Super Admin</span>
                    </div>
                    <div class="cred-item">
                        <span class="cred-label">Login Page:</span>
                        <span class="cred-value">login_new.php</span>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <strong><i class="fas fa-lightbulb"></i> Important Notes:</strong><br>
                    • The system now redirects to <code>login_new.php</code> by default<br>
                    • Your Super Admin account has full access to all features<br>
                    • You can still use legacy accounts with <code>login.php</code> if needed<br>
                    • After login, access User Management from the header or sidebar
                </div>
            </div>
            
            <!-- ACTION BUTTONS -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="login_new.php" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Login to New System
                </a>
                <a href="login.php" class="btn btn-secondary">
                    <i class="fas fa-history"></i> Login to Legacy System
                </a>
                <a href="create_super_admin.php" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i> Create/Reset Super Admin
                </a>
            </div>
            
        </div>
    </div>
</body>
</html>
