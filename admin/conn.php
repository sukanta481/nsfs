<?php
global $conn;
// Parse .env file if exists

// --- Enhanced error logging and debug output ---
$debug = (isset($_GET['debug']) && $_GET['debug'] === '1');
$log_file = __DIR__ . '/debug_conn.log';

$env_path = __DIR__ . '/../.env';
if (!file_exists($env_path)) {
    $env_path = __DIR__ . '/.env';
}

$env = [];
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $env[trim($name)] = trim($value);
        }
    }
    $db_host = $env['DB_HOST'] ?? '';
    $db_user = $env['DB_USER'] ?? '';
    $db_pass = $env['DB_PASS'] ?? '';
    $db_name = $env['DB_NAME'] ?? '';
    
    if (empty($db_host) || empty($db_user) || empty($db_name)) {
        $msg = "[conn.php] .env file is missing required DB credentials (DB_HOST, DB_USER, DB_NAME).";
        error_log($msg, 3, $log_file);
        if ($debug) {
            die("<html><body><h1>Database Configuration Error</h1><p>$msg</p><p>Check your .env file at: $env_path</p></body></html>");
        }
        die("Database connection error: Please contact the administrator.");
    }
} else {
    $msg = "[conn.php] .env file not found at $env_path. Using fallback defaults.";
    error_log($msg, 3, $log_file);
    
    // Fallback defaults
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'nsfs';
    
    if ($debug) {
        echo "<div style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 10px;'>";
        echo "<b>WARNING:</b> $msg<br>";
        echo "Using fallback: host=$db_host, user=$db_user, db=$db_name";
        echo "</div>";
    }
}

// Attempt connection
$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (mysqli_connect_errno()) {
    $err = "[conn.php] Failed to connect to MySQL: " . mysqli_connect_error();
    error_log($err, 3, $log_file);
    error_log("[conn.php] Connection details - Host: $db_host, User: $db_user, DB: $db_name", 3, $log_file);
    
    if ($debug) {
        die("<html><body style='font-family: Arial, sans-serif; padding: 20px;'>
            <h1 style='color: #dc3545;'>Database Connection Failed</h1>
            <div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0;'>
                <strong>Error:</strong> " . mysqli_connect_error() . "<br><br>
                <strong>Host:</strong> $db_host<br>
                <strong>User:</strong> $db_user<br>
                <strong>Database:</strong> $db_name<br>
            </div>
            <h3>Troubleshooting Steps:</h3>
            <ol>
                <li>Check if MySQL/MariaDB service is running</li>
                <li>Verify credentials in .env file: $env_path</li>
                <li>Check if database '$db_name' exists</li>
                <li>Verify user '$db_user' has access to database</li>
                <li>Check debug_conn.log for details</li>
            </ol>
            </body></html>");
    } else {
        die("Database connection error: Please contact the administrator.");
    }
}

// Set charset to UTF-8 to prevent encoding issues
mysqli_set_charset($conn, "utf8mb4");

// Log successful connection in debug mode - BUT DON'T EXIT!
if ($debug) {
    $success_msg = "[conn.php] DB connection successful to $db_host/$db_name at " . date('Y-m-d H:i:s');
    error_log($success_msg . "\n", 3, $log_file);
    echo "<div style='background: #d4edda; padding: 10px; border-left: 4px solid #28a745; margin: 10px;'>";
    echo "<b>SUCCESS:</b> Database connected successfully to $db_host/$db_name";
    echo "</div>";
    // IMPORTANT: DO NOT exit() here! Let the script continue so $conn is available
}

// Ensure $conn is set and valid before returning
if (!isset($conn) || !($conn instanceof mysqli)) {
    $err = "[conn.php] Critical error: \$conn variable is not a valid mysqli object after connection attempt";
    error_log($err, 3, $log_file);
    if ($debug) {
        die("<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px;'>$err</div>");
    }
    die("Database connection error: Please contact the administrator.");
}
?>