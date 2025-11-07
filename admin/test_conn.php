<?php
// Simple DB connectivity tester for live servers.
// Visit: /admin/test_conn.php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Try to include the project's conn.php if available
$included = false;
$candidates = [__DIR__ . '/conn.php', __DIR__ . '/../conn.php', __DIR__ . '/../../conn.php'];
foreach ($candidates as $c) {
    if (file_exists($c)) {
        require $c;
        $included = true;
        break;
    }
}

header('Content-Type: text/plain; charset=utf-8');
if (!$included) {
    echo "conn.php not found in expected locations:\n";
    foreach ($candidates as $c) echo " - $c\n";
    exit(1);
}

if (!isset($conn)) {
    echo "conn.php was included but did not expose \$conn variable.\n";
    exit(1);
}

if (mysqli_connect_errno()) {
    echo "MySQL connect error: " . mysqli_connect_error() . "\n";
    exit(1);
}

// Ping
if (mysqli_ping($conn)) {
    echo "DB connected OK. Server info: " . mysqli_get_server_info($conn) . "\n";
    // show selected DB user/host
    $res = mysqli_query($conn, "SELECT DATABASE() AS db");
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        echo "Current database: " . ($row['db'] ?? '(none)') . "\n";
    }
} else {
    echo "mysqli_ping failed: " . mysqli_error($conn) . "\n";
}

?>