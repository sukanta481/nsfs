<?php
/**
 * Create New Super Admin User
 * Username: sukanta481
 * Password: Sukanta@0050
 */

require 'conn.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Create Super Admin User</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .credentials { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; border: 2px solid #e9ecef; }
        .credentials h3 { margin-top: 0; color: #495057; }
        .credentials code { background: #e9ecef; padding: 5px 10px; border-radius: 3px; font-size: 16px; color: #c7254e; }
        .btn { display: inline-block; padding: 12px 30px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn:hover { background: #45a049; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔐 Create Super Admin User</h1>";

// First, check if user management tables exist
$tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_users'");
if (mysqli_num_rows($tables_check) == 0) {
    echo "<div class='error'><strong>Error:</strong> User management tables don't exist yet!<br>
          Please run <a href='setup_user_management.php'>setup_user_management.php</a> first.</div>";
    echo "</div></body></html>";
    exit;
}

// Check if Super Admin role exists
$role_check = mysqli_query($conn, "SELECT role_id FROM tbl_roles WHERE role_name='Super Admin' LIMIT 1");
if (mysqli_num_rows($role_check) == 0) {
    echo "<div class='error'><strong>Error:</strong> Super Admin role doesn't exist!<br>
          Please run <a href='setup_user_management.php'>setup_user_management.php</a> first.</div>";
    echo "</div></body></html>";
    exit;
}
$super_admin_role = mysqli_fetch_assoc($role_check);
$role_id = $super_admin_role['role_id'];

// Check if username already exists
$username = 'sukanta481';
$check_existing = mysqli_query($conn, "SELECT user_id FROM tbl_users WHERE username='$username'");

if (mysqli_num_rows($check_existing) > 0) {
    echo "<div class='info'><strong>Note:</strong> User '$username' already exists!</div>";
    
    // Update the password
    $password = 'Sukanta@0050';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $update_sql = "UPDATE tbl_users SET 
                   password = '$hashed_password',
                   full_name = 'Sukanta Maity',
                   email = 'sukanta481@nsfs.com',
                   role_id = $role_id,
                   active_status = 1,
                   updated_at = NOW()
                   WHERE username = '$username'";
    
    if (mysqli_query($conn, $update_sql)) {
        echo "<div class='success'><strong>✓ Password Updated Successfully!</strong><br>
              The password for user '$username' has been updated.</div>";
    } else {
        echo "<div class='error'><strong>Error:</strong> " . mysqli_error($conn) . "</div>";
    }
} else {
    // Create new user
    $password = 'Sukanta@0050';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $insert_sql = "INSERT INTO tbl_users 
                   (username, email, password, full_name, role_id, active_status, created_at) 
                   VALUES 
                   ('$username', 'sukanta481@nsfs.com', '$hashed_password', 'Sukanta Maity', $role_id, 1, NOW())";
    
    if (mysqli_query($conn, $insert_sql)) {
        $user_id = mysqli_insert_id($conn);
        echo "<div class='success'><strong>✓ Super Admin User Created Successfully!</strong><br>
              User ID: $user_id</div>";
    } else {
        echo "<div class='error'><strong>Error:</strong> " . mysqli_error($conn) . "</div>";
    }
}

// Display credentials
echo "<div class='credentials'>
        <h3>🔑 Login Credentials</h3>
        <p><strong>Username:</strong> <code>sukanta481</code></p>
        <p><strong>Password:</strong> <code>Sukanta@0050</code></p>
        <p><strong>Role:</strong> Super Admin (Full Access)</p>
        <p style='margin-top: 20px; padding-top: 15px; border-top: 1px solid #dee2e6;'>
            <strong>⚠️ Important:</strong> Please change your password after first login for security.
        </p>
      </div>";

// Check how many Super Admin users exist
$admin_count = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tbl_users WHERE role_id = $role_id");
$count = mysqli_fetch_assoc($admin_count)['cnt'];

echo "<div class='info'>
        <strong>System Status:</strong><br>
        • Total Super Admin users: <strong>$count</strong><br>
        • Role ID: <strong>$role_id</strong><br>
        • User Management Tables: <strong>✓ Ready</strong>
      </div>";

echo "<h3>Next Steps:</h3>
      <ol>
        <li>Logout from your current session (if logged in)</li>
        <li>Go to the login page</li>
        <li>Login with the credentials above</li>
        <li>Access User Management from the header or sidebar</li>
      </ol>";

echo "<a href='logout.php' class='btn'>Logout & Go to Login</a> ";
echo "<a href='users.php' class='btn' style='background:#007bff;'>Go to User Management</a>";

echo "</div></body></html>";
?>
