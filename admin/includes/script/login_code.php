<?php
date_default_timezone_set('Asia/Kolkata');
$message = $message ?? '';

if (isset($_POST['Login']) && $_POST['Login'] == 'Login') {
    // 1. Get and validate input
    $user = trim($_POST['user'] ?? '');
    $pw   = trim($_POST['password'] ?? '');
    $error = false;
    $message = '';

    // 2. Simple validation
    if (empty($user)) {
        $message = 'Invalid Username';
        $error = true;
    }
    if (empty($pw)) {
        $message = 'Invalid Password';
        $error = true;
    }

    if (!$error) {
        // 3. Query user from tbl_administrator
        $username = mysqli_real_escape_string($conn, $user);
        $query = "SELECT * FROM tbl_administrator WHERE adminname='" . $username . "' LIMIT 1";

        $result_resource = g_db_query($query);
        $result = g_db_fetch_array($result_resource);

        if ($result && isset($result['id']) && $result['id'] != '') {
            $password_valid = false;
            $stored_password = $result['adminpassword'];

            // Check if password is bcrypt (starts with $2y$ or $2a$) or MD5 (32 hex chars)
            if (substr($stored_password, 0, 4) === '$2y$' || substr($stored_password, 0, 4) === '$2a$') {
                // Bcrypt password - use password_verify
                if (password_verify($pw, $stored_password)) {
                    $password_valid = true;
                }
            } else if (strlen($stored_password) == 32 && ctype_xdigit($stored_password)) {
                // MD5 password - verify with MD5 then upgrade to bcrypt
                $md5_hash = md5($pw);
                if ($md5_hash === $stored_password) {
                    $password_valid = true;

                    // Upgrade to bcrypt immediately
                    $new_bcrypt_hash = password_hash($pw, PASSWORD_DEFAULT);
                    $update_query = "UPDATE tbl_administrator SET adminpassword='" . mysqli_real_escape_string($conn, $new_bcrypt_hash) . "' WHERE id=" . $result['id'];
                    g_db_query($update_query);

                    // Also update in tbl_users if exists
                    $check_user = mysqli_query($conn, "SELECT user_id FROM tbl_users WHERE username='" . $username . "' LIMIT 1");
                    if ($check_user && mysqli_num_rows($check_user) > 0) {
                        $user_row = mysqli_fetch_assoc($check_user);
                        mysqli_query($conn, "UPDATE tbl_users SET password='" . mysqli_real_escape_string($conn, $new_bcrypt_hash) . "' WHERE user_id=" . $user_row['user_id']);
                    }
                }
            }

            if ($password_valid) {
                // 4. Set session!
                $_SESSION['admin'] = [
                    'id' => $result['id'],
                    'adminname' => $result['adminname'],
                    'admin_email' => $result['admin_email']
                ];

                // 5. Handle redirect
                if (isset($_SESSION['redirect_origin'])) {
                    $redirect_origin = $_SESSION['redirect_origin'];
                    $page = $redirect_origin['page'] ?? 'index.php';
                    unset($_SESSION['redirect_origin']);
                    header("Location: $page");
                    exit;
                } else {
                    header("Location: index.php");
                    exit;
                }
            } else {
                $message = "Username or Password is Invalid.";
            }
        } else {
            $message = "Username or Password is Invalid.";
        }
    }
}
?>
