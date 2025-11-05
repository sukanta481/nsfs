<?php
/**
 * Authentication Check
 * Include this at the top of protected pages
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Function to check if user has specific permission
function hasPermission($permission_key) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
        return false;
    }
    
    require_once 'conn.php';
    
    // Check if user's role has the permission
    $query = "SELECT COUNT(*) as has_perm FROM tbl_role_permissions rp
              JOIN tbl_permissions p ON rp.permission_id = p.permission_id
              WHERE rp.role_id = {$_SESSION['role_id']} AND p.permission_key = '$permission_key'";
    
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['has_perm'] > 0;
}

// Function to get all user permissions
function getUserPermissions() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
        return [];
    }
    
    if (!isset($_SESSION['permissions'])) {
        require_once 'conn.php';
        
        $query = "SELECT p.permission_key FROM tbl_role_permissions rp
                  JOIN tbl_permissions p ON rp.permission_id = p.permission_id
                  WHERE rp.role_id = {$_SESSION['role_id']}";
        
        $result = mysqli_query($conn, $query);
        $permissions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $permissions[] = $row['permission_key'];
        }
        
        $_SESSION['permissions'] = $permissions;
    }
    
    return $_SESSION['permissions'];
}

// Function to require permission (redirect if not authorized)
function requirePermission($permission_key) {
    if (!hasPermission($permission_key)) {
        header('HTTP/1.1 403 Forbidden');
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Access Denied</title>
            <style>
                body { font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f5f5f5; }
                .error-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; max-width: 500px; }
                .error-box i { font-size: 60px; color: #e74c3c; }
                h1 { color: #2c3e50; margin: 20px 0 10px; }
                p { color: #7f8c8d; margin-bottom: 20px; }
                a { display: inline-block; padding: 12px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
            </style>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        </head>
        <body>
            <div class='error-box'>
                <i class='fas fa-lock'></i>
                <h1>Access Denied</h1>
                <p>You don't have permission to access this resource.</p>
                <p><small>Required permission: <code>$permission_key</code></small></p>
                <a href='index.php'><i class='fas fa-home'></i> Go to Dashboard</a>
            </div>
        </body>
        </html>";
        exit;
    }
}

// Function to check if user is Super Admin
function isSuperAdmin() {
    return isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Super Admin';
}
?>
