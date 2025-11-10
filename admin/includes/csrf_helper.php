<?php
/**
 * CSRF Protection Helper
 * Provides functions to generate and validate CSRF tokens
 */

/**
 * Generate a CSRF token and store it in the session
 * @param string $form_name Optional form name for multiple forms on same page
 * @return string The generated token
 */
function csrf_generate_token($form_name = 'default') {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Generate a random token
    $token = bin2hex(random_bytes(32));

    // Store token in session with timestamp
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }

    $_SESSION['csrf_tokens'][$form_name] = [
        'token' => $token,
        'time' => time()
    ];

    return $token;
}

/**
 * Get the current CSRF token for a form
 * @param string $form_name Optional form name
 * @return string|null The token or null if not found
 */
function csrf_get_token($form_name = 'default') {
    if (!isset($_SESSION['csrf_tokens'][$form_name])) {
        return csrf_generate_token($form_name);
    }

    return $_SESSION['csrf_tokens'][$form_name]['token'];
}

/**
 * Validate a CSRF token
 * @param string $token The token to validate
 * @param string $form_name Optional form name
 * @param int $expire_time Token expiration time in seconds (default: 3600 = 1 hour)
 * @return bool True if valid, false otherwise
 */
function csrf_validate_token($token, $form_name = 'default', $expire_time = 3600) {
    // Check if token exists in session
    if (!isset($_SESSION['csrf_tokens'][$form_name])) {
        return false;
    }

    $stored_token = $_SESSION['csrf_tokens'][$form_name]['token'];
    $token_time = $_SESSION['csrf_tokens'][$form_name]['time'];

    // Check if token has expired
    if (time() - $token_time > $expire_time) {
        unset($_SESSION['csrf_tokens'][$form_name]);
        return false;
    }

    // Use hash_equals to prevent timing attacks
    if (!hash_equals($stored_token, $token)) {
        return false;
    }

    // Token is valid - optionally regenerate for one-time use
    // Uncomment the line below if you want one-time tokens
    // unset($_SESSION['csrf_tokens'][$form_name]);

    return true;
}

/**
 * Generate a hidden input field with CSRF token
 * @param string $form_name Optional form name
 * @return string HTML input field
 */
function csrf_token_field($form_name = 'default') {
    $token = csrf_get_token($form_name);
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify CSRF token from POST request
 * @param string $form_name Optional form name
 * @return bool True if valid, false otherwise
 */
function csrf_verify_request($form_name = 'default') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true; // Only validate POST requests
    }

    $token = $_POST['csrf_token'] ?? '';

    if (empty($token)) {
        return false;
    }

    return csrf_validate_token($token, $form_name);
}

/**
 * Clean up expired tokens from session
 * @param int $expire_time Token expiration time in seconds
 */
function csrf_cleanup_expired_tokens($expire_time = 3600) {
    if (!isset($_SESSION['csrf_tokens'])) {
        return;
    }

    $current_time = time();

    foreach ($_SESSION['csrf_tokens'] as $form_name => $data) {
        if ($current_time - $data['time'] > $expire_time) {
            unset($_SESSION['csrf_tokens'][$form_name]);
        }
    }
}

/**
 * Display CSRF error and exit
 */
function csrf_error_exit() {
    http_response_code(403);
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Security Error</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f5f5f5;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }
            .error-container {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                max-width: 500px;
                text-align: center;
            }
            .error-icon {
                font-size: 60px;
                color: #e74c3c;
                margin-bottom: 20px;
            }
            h1 {
                color: #2c3e50;
                margin-bottom: 10px;
            }
            p {
                color: #7f8c8d;
                line-height: 1.6;
            }
            .btn {
                display: inline-block;
                margin-top: 20px;
                padding: 12px 30px;
                background: #3498db;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #2980b9;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">⚠️</div>
            <h1>Security Token Error</h1>
            <p>Your security token is invalid or has expired. This could be due to:</p>
            <ul style="text-align: left; color: #7f8c8d;">
                <li>Your session expired (inactive for too long)</li>
                <li>You submitted the form multiple times</li>
                <li>You opened the form in multiple tabs</li>
            </ul>
            <p>Please go back and try again.</p>
            <a href="javascript:history.back()" class="btn">← Go Back</a>
        </div>
    </body>
    </html>
    ');
}

// Auto-cleanup expired tokens on every request (optional)
csrf_cleanup_expired_tokens();
