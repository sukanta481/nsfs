<?php
require 'check_auth.php';
requirePermission('user_delete');
require 'conn.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id == 0) {
    header("Location: users.php?error=Invalid user ID");
    exit;
}

// Check if user exists
$user_query = "SELECT * FROM tbl_users WHERE user_id = $user_id";
$user_result = mysqli_query($conn, $user_query);

if (mysqli_num_rows($user_result) == 0) {
    header("Location: users.php?error=User not found");
    exit;
}

$user = mysqli_fetch_assoc($user_result);

// Prevent self-deletion
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
    header("Location: users.php?error=You cannot delete your own account");
    exit;
}

// Prevent deletion of Super Admin if it's the only one
$role_query = "SELECT role_name FROM tbl_roles WHERE role_id = " . $user['role_id'];
$role_result = mysqli_query($conn, $role_query);
$role = mysqli_fetch_assoc($role_result);

if ($role['role_name'] == 'Super Admin') {
    // Check if there are other Super Admins
    $super_admin_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM tbl_users u 
                                               JOIN tbl_roles r ON u.role_id = r.role_id 
                                               WHERE r.role_name = 'Super Admin' AND u.active_status = 1");
    $count_result = mysqli_fetch_assoc($super_admin_count);
    
    if ($count_result['count'] <= 1) {
        header("Location: users.php?error=Cannot delete the only active Super Admin");
        exit;
    }
}

// Delete user
$delete_query = "DELETE FROM tbl_users WHERE user_id = $user_id";

if (mysqli_query($conn, $delete_query)) {
    header("Location: users.php?success=User '" . htmlspecialchars($user['username']) . "' deleted successfully");
    exit;
} else {
    header("Location: users.php?error=Error deleting user: " . mysqli_error($conn));
    exit;
}
?>
