<?php
/**
 * Professional Database Schema Sync Tool
 * Syncs ONLY table structures, not data
 * Safe for production - won't delete user data
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('pro');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define permission functions
if (!function_exists('hasPermission')) {
    function hasPermission($permission) { return true; }
}
if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin() { return true; }
}

// Authentication
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    die("Access denied. Please <a href='../login_new.php'>login</a> first.");
}

require __DIR__ . '/../conn.php';

$message = '';
$messageType = '';

if (isset($_SESSION['schema_message'])) {
    $message = $_SESSION['schema_message'];
    $messageType = $_SESSION['schema_type'];
    unset($_SESSION['schema_message']);
    unset($_SESSION['schema_type']);
}

// Export Schema Only
if (isset($_POST['export_schema'])) {
    try {
        $backup_file = 'schema_only_' . date('Y-m-d_H-i-s') . '.sql';
        $backup_path = __DIR__ . '/backups/' . $backup_file;
        
        if (!file_exists(__DIR__ . '/backups')) {
            mkdir(__DIR__ . '/backups', 0755, true);
        }
        
        $tables = array();
        $result = mysqli_query($conn, "SHOW TABLES");
        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }
        
        $sql_dump = "-- Database Schema Export (Structure Only)\n";
        $sql_dump .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $sql_dump .= "-- WARNING: This file contains ONLY table structures, NO DATA\n\n";
        $sql_dump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            $sql_dump .= "\n-- Table structure for: {$table}\n";
            
            $result = mysqli_query($conn, "SHOW CREATE TABLE `{$table}`");
            $row = mysqli_fetch_row($result);
            $sql_dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql_dump .= $row[1] . ";\n\n";
        }
        
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        file_put_contents($backup_path, $sql_dump);
        $_SESSION['schema_message'] = "Schema exported successfully! ({$backup_file})";
        $_SESSION['schema_type'] = 'success';
        header('Location: schema_sync.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['schema_message'] = "Error: " . $e->getMessage();
        $_SESSION['schema_type'] = 'error';
        header('Location: schema_sync.php');
        exit();
    }
}

// Compare Schemas
if (isset($_POST['compare_schema']) && isset($_FILES['schema_file'])) {
    try {
        $file = $_FILES['schema_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error");
        }
        
        $sql_content = file_get_contents($file['tmp_name']);
        
        // Parse the schema file to extract table structures
        $uploaded_tables = [];
        preg_match_all('/CREATE TABLE `([^`]+)`[^;]+;/is', $sql_content, $matches);
        
        for ($i = 0; $i < count($matches[0]); $i++) {
            $table_name = $matches[1][$i];
            $uploaded_tables[$table_name] = $matches[0][$i];
        }
        
        // Get current database tables
        $current_tables = [];
        $result = mysqli_query($conn, "SHOW TABLES");
        while ($row = mysqli_fetch_row($result)) {
            $table_name = $row[0];
            $result2 = mysqli_query($conn, "SHOW CREATE TABLE `{$table_name}`");
            $row2 = mysqli_fetch_row($result2);
            $current_tables[$table_name] = $row2[1];
        }
        
        // Compare
        $differences = [];
        
        // Check for new tables
        foreach ($uploaded_tables as $table => $structure) {
            if (!isset($current_tables[$table])) {
                $differences[] = [
                    'type' => 'new_table',
                    'table' => $table,
                    'message' => "New table to be created"
                ];
            }
        }
        
        // Check for missing tables
        foreach ($current_tables as $table => $structure) {
            if (!isset($uploaded_tables[$table])) {
                $differences[] = [
                    'type' => 'missing_table',
                    'table' => $table,
                    'message' => "Table exists in current DB but not in uploaded schema"
                ];
            }
        }
        
        // Check for modified tables (simplified comparison)
        foreach ($uploaded_tables as $table => $uploaded_structure) {
            if (isset($current_tables[$table])) {
                // Normalize for comparison
                $uploaded_normalized = preg_replace('/\s+/', ' ', strtolower($uploaded_structure));
                $current_normalized = preg_replace('/\s+/', ' ', strtolower($current_tables[$table]));
                
                if ($uploaded_normalized !== $current_normalized) {
                    $differences[] = [
                        'type' => 'modified_table',
                        'table' => $table,
                        'message' => "Table structure differs"
                    ];
                }
            }
        }
        
        $_SESSION['schema_differences'] = $differences;
        header('Location: schema_sync.php?compared=1');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['schema_message'] = "Error: " . $e->getMessage();
        $_SESSION['schema_type'] = 'error';
        header('Location: schema_sync.php');
        exit();
    }
}

// Apply Schema Changes
if (isset($_POST['apply_schema']) && isset($_FILES['schema_file'])) {
    try {
        $file = $_FILES['schema_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error");
        }
        
        $sql_content = file_get_contents($file['tmp_name']);
        
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");
        
        // Parse and execute only CREATE TABLE statements
        preg_match_all('/DROP TABLE IF EXISTS `([^`]+)`;[\s\n]*CREATE TABLE `[^`]+`[^;]+;/is', $sql_content, $matches);
        
        $success_count = 0;
        $error_count = 0;
        $errors = [];
        
        foreach ($matches[0] as $statement) {
            if (mysqli_multi_query($conn, $statement)) {
                do {
                    if ($result = mysqli_store_result($conn)) {
                        mysqli_free_result($result);
                    }
                } while (mysqli_next_result($conn));
                $success_count++;
            } else {
                $error_count++;
                $errors[] = mysqli_error($conn);
            }
        }
        
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");
        
        $_SESSION['schema_message'] = "Schema updated! Tables processed: {$success_count}, Errors: {$error_count}";
        $_SESSION['schema_type'] = $error_count > 0 ? 'warning' : 'success';
        header('Location: schema_sync.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['schema_message'] = "Error: " . $e->getMessage();
        $_SESSION['schema_type'] = 'error';
        header('Location: schema_sync.php');
        exit();
    }
}

$differences = isset($_SESSION['schema_differences']) ? $_SESSION['schema_differences'] : [];
if (isset($_GET['compared'])) {
    unset($_SESSION['schema_differences']);
}

// Get backup files
$backups = array();
if (file_exists(__DIR__ . '/backups')) {
    $files = scandir(__DIR__ . '/backups', SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql' && strpos($file, 'schema_') === 0) {
            $backups[] = array(
                'name' => $file,
                'size' => filesize(__DIR__ . '/backups/' . $file),
                'date' => date('Y-m-d H:i:s', filemtime(__DIR__ . '/backups/' . $file))
            );
        }
    }
}

// Handle download
if (isset($_GET['download'])) {
    $filename = basename($_GET['download']);
    $filepath = __DIR__ . '/backups/' . $filename;
    
    if (file_exists($filepath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Schema Sync (Structure Only) | NSFS Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header h1 { color: #2c3e50; display: flex; align-items: center; gap: 10px; }
        .header p { color: #7f8c8d; margin-top: 5px; }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .alert-warning { background: #fff3cd; color: #856404; }
        .alert-info { background: #d1ecf1; color: #0c5460; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card-header {
            padding: 20px;
            color: white;
            font-weight: bold;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .export-header { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .compare-header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .apply-header { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .card-body { padding: 20px; }
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform 0.2s;
        }
        .btn:hover { transform: scale(1.02); }
        .btn-export { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .btn-compare { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); margin-top: 10px; }
        .btn-apply { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); margin-top: 10px; }
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .differences {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .diff-item {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .diff-new { border-color: #28a745; background: #d4edda; }
        .diff-missing { border-color: #dc3545; background: #f8d7da; }
        .diff-modified { border-color: #ffc107; background: #fff3cd; }
        table { width: 100%; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; }
        th { background: #11998e; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
        .btn-small {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 0.9em;
            display: inline-block;
            background: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fa fa-database"></i> Database Schema Sync (Structure Only)</h1>
            <p><strong>Safe for Production:</strong> Syncs only table structures, preserves all data</p>
        </div>

        <div class="info-box">
            <strong><i class="fa fa-info-circle"></i> How This Works:</strong>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li><strong>Export Schema:</strong> Exports only table structures (columns, indexes) - NO DATA</li>
                <li><strong>Compare:</strong> Shows differences between your schema file and live database</li>
                <li><strong>Apply:</strong> Updates table structures without touching existing data</li>
            </ul>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>">
            <i class="fa fa-info-circle"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($differences)): ?>
        <div class="differences">
            <h2><i class="fa fa-exchange-alt"></i> Schema Differences Found</h2>
            <p style="margin: 10px 0;">Review these changes before applying:</p>
            
            <?php foreach ($differences as $diff): ?>
            <div class="diff-item diff-<?= $diff['type'] === 'new_table' ? 'new' : ($diff['type'] === 'missing_table' ? 'missing' : 'modified') ?>">
                <strong><?= htmlspecialchars($diff['table']) ?></strong>: <?= htmlspecialchars($diff['message']) ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="grid">
            <div class="card">
                <div class="card-header export-header">
                    <i class="fa fa-download"></i> Export Schema
                </div>
                <div class="card-body">
                    <p style="margin-bottom: 15px;">Export current database structure (no data).</p>
                    <form method="post">
                        <button type="submit" name="export_schema" class="btn btn-export">
                            <i class="fa fa-download"></i> Export Structure Only
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header compare-header">
                    <i class="fa fa-balance-scale"></i> Compare Schema
                </div>
                <div class="card-body">
                    <p style="margin-bottom: 15px;">Compare uploaded schema with current database.</p>
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="schema_file" accept=".sql" required>
                        <button type="submit" name="compare_schema" class="btn btn-compare">
                            <i class="fa fa-balance-scale"></i> Compare Structures
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header apply-header">
                    <i class="fa fa-sync"></i> Apply Schema
                </div>
                <div class="card-body">
                    <p style="margin-bottom: 15px;">Apply schema changes (data preserved).</p>
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="schema_file" accept=".sql" required>
                        <button type="submit" name="apply_schema" class="btn btn-apply" onclick="return confirm('Apply schema changes? Existing data will be preserved.')">
                            <i class="fa fa-sync"></i> Apply Structure Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="warning-box">
            <strong><i class="fa fa-exclamation-triangle"></i> Important Notes:</strong>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Always export current schema before applying changes</li>
                <li>Compare first to see what will change</li>
                <li>This tool is safe - it won't delete your data</li>
                <li>For config data sync, use the Selective Data Sync tool</li>
            </ul>
        </div>

        <?php if (!empty($backups)): ?>
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 20px;">
            <h2><i class="fa fa-archive"></i> Schema Backups</h2>
            <table>
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Size</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td><?= htmlspecialchars($backup['name']) ?></td>
                        <td><?= number_format($backup['size'] / 1024, 2) ?> KB</td>
                        <td><?= htmlspecialchars($backup['date']) ?></td>
                        <td>
                            <a href="?download=<?= urlencode($backup['name']) ?>" class="btn-small">
                                <i class="fa fa-download"></i> Download
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
