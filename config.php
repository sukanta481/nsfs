<?php
// Load database configuration from environment variables

foreach (['DB_SERVER', 'DB_SERVER_USERNAME', 'DB_SERVER_PASSWORD', 'DB_DATABASE'] as $var) {
    $value = getenv($var);
    if ($value === false) {
        $value = '';
    }
    if (!defined($var)) {
        define($var, $value);
    }
}
