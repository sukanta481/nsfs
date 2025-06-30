<?php
// Load environment variables from .env file
$envPath = __DIR__ . '/.env'; // Adjust the path if needed
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    if ($env !== false) {
        foreach ($env as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}

// Now you can use DB_HOST, DB_USER, DB_PASS, DB_NAME, etc. as constants everywhere.
?>
