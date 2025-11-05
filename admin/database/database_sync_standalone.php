<?php
/**
 * Standalone Database Sync - No Dependencies Version
 * Use this if the main database_sync.php fails due to missing includes
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_name('pro');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simple authentication check
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    die("Access denied. Please <a href='../login_new.php'>login</a> first.");
}

// Load database connection
$conn_path = __DIR__ . '/../conn.php';
if (!file_exists($conn_path)) {
    die("Error: conn.php not found at: $conn_path");
}
require $conn_path;

if (!isset($conn)) {
    die("Error: Database connection not established");
}

// Session messages
$message = '';
$messageType = '';

if (isset($_SESSION['sync_message'])) {
    $message = $_SESSION['sync_message'];
    $messageType = $_SESSION['sync_type'];
    unset($_SESSION['sync_message']);
    unset($_SESSION['sync_type']);
}

// Handle database export
if (isset($_POST['export_db'])) {
    try {
        $backup_file = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backup_path = __DIR__ . '/backups/' . $backup_file;
        
        // Create backups directory
        if (!file_exists(__DIR__ . '/backups')) {
            mkdir(__DIR__ . '/backups', 0755, true);
        }
        
        // PHP-based export (more reliable across servers)
        $tables = array();
        $result = mysqli_query($conn, "SHOW TABLES");
        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }
        
        $sql_dump = "-- Database Backup\n";
        $sql_dump .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        $sql_dump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql_dump .= "SET time_zone = \"+00:00\";\n";
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            $sql_dump .= "\n-- Table: {$table}\n";
            
            // Get table structure
            $result = mysqli_query($conn, "SHOW CREATE TABLE `{$table}`");
            $row = mysqli_fetch_row($result);
            $sql_dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql_dump .= $row[1] . ";\n\n";
            
            // Get table data
            $result = mysqli_query($conn, "SELECT * FROM `{$table}`");
            $num_rows = mysqli_num_rows($result);
            
            if ($num_rows > 0) {
                $fields = mysqli_fetch_fields($result);
                $columns = array_map(function($field) { return "`{$field->name}`"; }, $fields);
                $column_list = implode(', ', $columns);
                
                $values_array = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $values = array();
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } elseif (is_numeric($value)) {
                            $values[] = $value;
                        } else {
                            $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
                        }
                    }
                    $values_array[] = '(' . implode(', ', $values) . ')';
                }
                
                $sql_dump .= "INSERT INTO `{$table}` ({$column_list}) VALUES\n";
                $sql_dump .= implode(",\n", $values_array) . ";\n\n";
            }
        }
        
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        file_put_contents($backup_path, $sql_dump);
        $_SESSION['sync_message'] = "Database exported successfully! File: {$backup_file}";
        $_SESSION['sync_type'] = 'success';
        header('Location: database_sync_standalone.php?exported=1');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['sync_message'] = "Error: " . $e->getMessage();
        $_SESSION['sync_type'] = 'error';
        header('Location: database_sync_standalone.php');
        exit();
    }
}

// Handle database import
if (isset($_POST['import_db']) && isset($_FILES['sql_file'])) {
    try {
        $file = $_FILES['sql_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error");
        }
        
        if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'sql') {
            throw new Exception("Please upload a .sql file");
        }
        
        $sql_content = file_get_contents($file['tmp_name']);
        
        // Disable foreign key checks
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");
        
        // Parse and execute queries
        $queries = [];
        $current_query = '';
        $lines = explode("\n", $sql_content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || substr($line, 0, 2) === '--') continue;
            
            $current_query .= $line . ' ';
            
            if (substr(rtrim($line), -1) === ';') {
                $queries[] = trim($current_query);
                $current_query = '';
            }
        }
        
        $success_count = 0;
        $error_count = 0;
        
        foreach ($queries as $query) {
            if (!empty($query)) {
                if (mysqli_query($conn, $query)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
        
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");
        
        $_SESSION['sync_message'] = "Import complete! Success: {$success_count}, Errors: {$error_count}";
        $_SESSION['sync_type'] = $error_count > 0 ? 'warning' : 'success';
        header('Location: database_sync_standalone.php?imported=1');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['sync_message'] = "Error: " . $e->getMessage();
        $_SESSION['sync_type'] = 'error';
        header('Location: database_sync_standalone.php');
        exit();
    }
}

// Get backup files
$backups = array();
if (file_exists(__DIR__ . '/backups')) {
    $files = scandir(__DIR__ . '/backups', SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
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

// Handle delete
if (isset($_GET['delete'])) {
    $filename = basename($_GET['delete']);
    $filepath = __DIR__ . '/backups/' . $filename;
    
    if (file_exists($filepath)) {
        unlink($filepath);
        $_SESSION['sync_message'] = "Backup deleted successfully!";
        $_SESSION['sync_type'] = 'success';
        header('Location: database_sync_standalone.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Sync (Standalone) | NSFS Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header p {
            color: #7f8c8d;
            margin-top: 5px;
        }
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
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
        }
        .export-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .import-header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .card-body {
            padding: 20px;
        }
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
        .btn-export { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .btn-import { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); margin-top: 10px; }
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background: #667eea;
            color: white;
        }
        tr:nth-child(even) { background: #f8f9fa; }
        .btn-small {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 0.9em;
            display: inline-block;
            margin-right: 5px;
        }
        .btn-download { background: #28a745; }
        .btn-delete { background: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fa fa-database"></i> Database Sync Tool (Standalone)</h1>
            <p>Export and import your database - No dependencies version</p>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>">
            <i class="fa fa-info-circle"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <div class="grid">
            <div class="card">
                <div class="card-header export-header">
                    <i class="fa fa-upload"></i> Export Database
                </div>
                <div class="card-body">
                    <p style="margin-bottom: 15px;">Export your current database to a SQL file.</p>
                    <form method="post">
                        <button type="submit" name="export_db" class="btn btn-export">
                            <i class="fa fa-download"></i> Export Database
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header import-header">
                    <i class="fa fa-download"></i> Import Database
                </div>
                <div class="card-body">
                    <p style="margin-bottom: 15px;">Import a SQL file to your database.</p>
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="sql_file" accept=".sql" required>
                        <button type="submit" name="import_db" class="btn btn-import" onclick="return confirm('This will replace existing data! Continue?')">
                            <i class="fa fa-upload"></i> Import Database
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php if (!empty($backups)): ?>
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin-bottom: 15px;"><i class="fa fa-archive"></i> Available Backups</h2>
            <table>
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Size</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td><?= htmlspecialchars($backup['name']) ?></td>
                        <td><?= number_format($backup['size'] / 1024, 2) ?> KB</td>
                        <td><?= htmlspecialchars($backup['date']) ?></td>
                        <td>
                            <a href="?download=<?= urlencode($backup['name']) ?>" class="btn-small btn-download">
                                <i class="fa fa-download"></i> Download
                            </a>
                            <a href="?delete=<?= urlencode($backup['name']) ?>" class="btn-small btn-delete" onclick="return confirm('Delete this backup?')">
                                <i class="fa fa-trash"></i> Delete
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
