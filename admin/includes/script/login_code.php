<?php
$message = $message ?? '';

// === DEBUG: See POST and SESSION each request ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre style='color:blue;'>DEBUG POST:\n";
    print_r($_POST);
    echo "</pre>";
    echo "<pre style='color:blue;'>DEBUG SESSION (before login):\n";
    print_r($_SESSION);
    echo "</pre>";
}

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
        // 3. Build and run the query (no TABLE_PREFIX unless you have it defined)
        $username = mysqli_real_escape_string($conn, $user);
        $password_hash = md5($pw); // For security, consider using password_hash in the future
        $query = "SELECT * FROM tbl_administrator WHERE adminname='" . $username . "' AND adminpassword='" . $password_hash . "' LIMIT 1";

        // DEBUG: Show query
        echo "<pre style='color:green;'>DEBUG SQL: $query</pre>";

        $result_resource = g_db_query($query);
        $result = g_db_fetch_array($result_resource);

        // DEBUG: Show DB result
        echo "<pre style='color:purple;'>DEBUG DB Result:\n";
        print_r($result);
        echo "</pre>";

        if ($result && isset($result['id']) && $result['id'] != '') {
            // 4. Set session!
            $_SESSION['admin'] = [
                'id' => $result['id'],
                'adminname' => $result['adminname'],
                'admin_email' => $result['admin_email']
            ];

            // DEBUG: Show session after setting
            echo "<pre style='color:darkorange;'>DEBUG SESSION (after login):\n";
            print_r($_SESSION);
            echo "</pre>";

            // 5. Handle redirect (uncomment when debugging is done)
            /*
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
            */
        } else {
            $message = "Username or Password is Invalid.";
        }
    }
}
?>
