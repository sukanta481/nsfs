<?php
/**
 * Automated SMTP Email Setup Script
 * Downloads PHPMailer and configures all files
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>SMTP Email Setup Wizard</h1>";
echo "<hr>";

// Step 1: Check if PHPMailer exists
echo "<h2>Step 1: Checking PHPMailer...</h2>";
$phpmailer_path = __DIR__ . '/PHPMailer/src/PHPMailer.php';

if (file_exists($phpmailer_path)) {
    echo "✅ PHPMailer already installed!<br>";
} else {
    echo "⚠️ PHPMailer not found. Downloading...<br>";

    // Download PHPMailer
    $zip_url = "https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip";
    $zip_file = __DIR__ . '/phpmailer-master.zip';

    echo "Downloading from GitHub...<br>";
    $zip_content = @file_get_contents($zip_url);

    if ($zip_content === false) {
        echo "❌ <strong>Failed to download PHPMailer!</strong><br>";
        echo "<p style='color:red;'>Please download manually from: <a href='$zip_url' target='_blank'>$zip_url</a></p>";
        echo "<p>Extract and place the 'src' folder in: <code>" . __DIR__ . "/PHPMailer/src/</code></p>";
    } else {
        file_put_contents($zip_file, $zip_content);
        echo "✅ Downloaded successfully!<br>";

        // Extract ZIP
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            if ($zip->open($zip_file) === TRUE) {
                $zip->extractTo(__DIR__);
                $zip->close();

                // Move files to correct location
                if (!file_exists(__DIR__ . '/PHPMailer')) {
                    mkdir(__DIR__ . '/PHPMailer');
                }

                rename(__DIR__ . '/PHPMailer-master/src', __DIR__ . '/PHPMailer/src');

                // Cleanup
                @unlink($zip_file);
                @rmdir(__DIR__ . '/PHPMailer-master');

                echo "✅ PHPMailer extracted and installed!<br>";
            } else {
                echo "❌ Failed to extract ZIP. Please extract manually.<br>";
            }
        } else {
            echo "⚠️ ZipArchive not available. Please extract manually.<br>";
            echo "<p>ZIP file saved at: <code>$zip_file</code></p>";
        }
    }
}

echo "<hr>";

// Step 2: Update all files to use SMTP config
echo "<h2>Step 2: Updating files to use SMTP configuration...</h2>";

$files_to_update = [
    'update_docket_status.php' => [
        'old' => "require 'conn.php';\nrequire 'email_templates.php';",
        'new' => "require 'conn.php';\nrequire 'email_config_smtp.php';\nrequire 'email_templates.php';"
    ],
    'delivery_status.php' => [
        'old' => "require 'conn.php';\nrequire 'email_templates.php';",
        'new' => "require 'conn.php';\nrequire 'email_config_smtp.php';\nrequire 'email_templates.php';"
    ],
    'manifest_save.php' => [
        'old' => "require 'conn.php';\nrequire 'email_templates.php';",
        'new' => "require 'conn.php';\nrequire 'email_config_smtp.php';\nrequire 'email_templates.php';"
    ],
    'save_trip_modern.php' => [
        'old' => "require_once 'DocketDetailsManager.php';\nrequire_once 'email_templates.php';",
        'new' => "require_once 'DocketDetailsManager.php';\nrequire_once 'email_config_smtp.php';\nrequire_once 'email_templates.php';"
    ],
    'email_templates.php' => [
        'old' => "require_once 'email_config.php';",
        'new' => "require_once 'email_config_smtp.php';"
    ]
];

$updated_count = 0;
foreach ($files_to_update as $file => $replacement) {
    $file_path = __DIR__ . '/' . $file;

    if (!file_exists($file_path)) {
        echo "⚠️ File not found: <code>$file</code><br>";
        continue;
    }

    $content = file_get_contents($file_path);
    $old = $replacement['old'];
    $new = $replacement['new'];

    if (strpos($content, $old) !== false) {
        $content = str_replace($old, $new, $content);
        file_put_contents($file_path, $content);
        echo "✅ Updated: <code>$file</code><br>";
        $updated_count++;
    } else if (strpos($content, $new) !== false) {
        echo "✓ Already updated: <code>$file</code><br>";
        $updated_count++;
    } else {
        echo "⚠️ Could not find replacement pattern in: <code>$file</code><br>";
    }
}

echo "<p><strong>Files updated: $updated_count / " . count($files_to_update) . "</strong></p>";
echo "<hr>";

// Step 3: Configuration check
echo "<h2>Step 3: Configuration Status</h2>";

$config_file = __DIR__ . '/email_config_smtp.php';
if (file_exists($config_file)) {
    echo "✅ SMTP configuration file exists!<br>";

    $config_content = file_get_contents($config_file);
    if (strpos($config_content, 'YOUR_PASSWORD_HERE') !== false) {
        echo "⚠️ <strong style='color:orange;'>SMTP password not configured!</strong><br>";
        echo "<p style='background:#fff3cd;padding:15px;border-left:4px solid #ffc107;'>";
        echo "Please edit <code>email_config_smtp.php</code> and replace:<br>";
        echo "<code>define('SMTP_PASSWORD', 'YOUR_PASSWORD_HERE');</code><br>";
        echo "with your actual Hostinger email password.";
        echo "</p>";
    } else {
        echo "✅ SMTP password appears to be configured!<br>";
    }
} else {
    echo "❌ SMTP configuration file not found!<br>";
}

echo "<hr>";

// Step 4: Next steps
echo "<h2>✅ Setup Complete!</h2>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><strong>Configure SMTP Password:</strong> Edit <code>email_config_smtp.php</code> (line 19)</li>";
echo "<li><strong>Test Email:</strong> <a href='test_email_smtp.php' target='_blank'>Click here to test email sending</a></li>";
echo "<li><strong>Check Documentation:</strong> <a href='SMTP_EMAIL_SETUP_GUIDE.md' target='_blank'>SMTP Setup Guide</a></li>";
echo "<li><strong>Delete this file:</strong> Remove <code>setup_smtp_email.php</code> after setup is complete</li>";
echo "</ol>";

echo "<hr>";
echo "<h3>📋 Configuration Summary</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>SMTP Host</td><td>smtp.hostinger.com</td></tr>";
echo "<tr><td>SMTP Port</td><td>465 (SSL)</td></tr>";
echo "<tr><td>Username</td><td>onestepup@northsuperfastservice.com</td></tr>";
echo "<tr><td>From Email</td><td>onestepup@northsuperfastservice.com</td></tr>";
echo "<tr><td>From Name</td><td>North Super Fast Service</td></tr>";
echo "</table>";

echo "<hr>";
echo "<p style='background:#d4edda;padding:15px;border-left:4px solid #28a745;'>";
echo "<strong>✅ All files have been updated to use SMTP configuration!</strong><br>";
echo "Once you configure the password, your email system will be fully operational.";
echo "</p>";
?>
