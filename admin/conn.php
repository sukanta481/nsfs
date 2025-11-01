<?php
// Parse .env file if exists
$env_path = __DIR__ . '/../.env'; // Adjust path if needed (this goes one level up from /admin)
if (!file_exists($env_path)) {
    $env_path = __DIR__ . '/.env'; // Fallback: same folder
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
    $db_host = $env['DB_HOST'] ?? 'localhost';
    $db_user = $env['DB_USER'] ?? 'root';
    $db_pass = $env['DB_PASS'] ?? '';
    $db_name = $env['DB_NAME'] ?? '';
} else {
    // Fallback if no .env
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'nsfs';
}

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}
?>
