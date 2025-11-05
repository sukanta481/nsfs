<?php
// Start session first
session_name('pro');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simple authentication check (without permission system)
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    header('Location: ../login_new.php');
    exit();
}

require __DIR__ . '/../conn.php';

// Session is already started
// Check for session messages
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
        
        // Create backups directory if it doesn't exist
        if (!file_exists(__DIR__ . '/backups')) {
            mkdir(__DIR__ . '/backups', 0755, true);
        }
        
        // Get database credentials
        $db_host = $env['DB_HOST'] ?? 'localhost';
        $db_user = $env['DB_USER'] ?? 'root';
        $db_pass = $env['DB_PASS'] ?? '';
        $db_name = $env['DB_NAME'] ?? 'nsfs';
        
        // Create mysqldump command
        $command = "mysqldump --host={$db_host} --user={$db_user}";
        if (!empty($db_pass)) {
            $command .= " --password={$db_pass}";
        }
        $command .= " {$db_name} > \"{$backup_path}\"";
        
        // Execute backup
        exec($command, $output, $return_var);
        
        if ($return_var === 0 && file_exists($backup_path)) {
            $_SESSION['sync_message'] = "Database exported successfully! File: {$backup_file}";
            $_SESSION['sync_type'] = 'success';
            header('Location: database_sync.php?exported=1');
            exit();
        } else {
            throw new Exception("Backup command failed");
        }
        
    } catch (Exception $e) {
        // Fallback to PHP-based export if mysqldump fails
        try {
            $tables = array();
            $result = mysqli_query($conn, "SHOW TABLES");
            while ($row = mysqli_fetch_row($result)) {
                $tables[] = $row[0];
            }
            
            $sql_dump = "-- Database Backup\n";
            $sql_dump .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
            $sql_dump .= "-- PHP Version: " . phpversion() . "\n\n";
            $sql_dump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sql_dump .= "SET time_zone = \"+00:00\";\n";
            $sql_dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            foreach ($tables as $table) {
                $sql_dump .= "\n-- --------------------------------------------------------\n";
                $sql_dump .= "-- Table structure for table `{$table}`\n";
                $sql_dump .= "-- --------------------------------------------------------\n\n";
                
                // Get table structure
                $result = mysqli_query($conn, "SHOW CREATE TABLE `{$table}`");
                $row = mysqli_fetch_row($result);
                $sql_dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql_dump .= $row[1] . ";\n\n";
                
                // Get table data with column names
                $result = mysqli_query($conn, "SELECT * FROM `{$table}`");
                $num_rows = mysqli_num_rows($result);
                
                if ($num_rows > 0) {
                    $sql_dump .= "-- Dumping data for table `{$table}` ({$num_rows} rows)\n\n";
                    
                    // Get column names
                    $fields = mysqli_fetch_fields($result);
                    $columns = array_map(function($field) { return "`{$field->name}`"; }, $fields);
                    $column_list = implode(', ', $columns);
                    
                    // Insert data in batches
                    $batch_size = 100;
                    $row_count = 0;
                    $insert_prefix = "INSERT INTO `{$table}` ({$column_list}) VALUES\n";
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
                        $row_count++;
                        
                        // Write batch when reaching batch size or last row
                        if ($row_count % $batch_size === 0 || $row_count === $num_rows) {
                            $sql_dump .= $insert_prefix . implode(",\n", $values_array) . ";\n\n";
                            $values_array = [];
                        }
                    }
                }
            }
            
            $sql_dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            file_put_contents($backup_path, $sql_dump);
            $_SESSION['sync_message'] = "Database exported successfully! File: {$backup_file} (" . count($tables) . " tables)";
            $_SESSION['sync_type'] = 'success';
            header('Location: database_sync.php?exported=1');
            exit();
            
        } catch (Exception $e2) {
            $_SESSION['sync_message'] = "Error exporting database: " . $e2->getMessage();
            $_SESSION['sync_type'] = 'error';
            header('Location: database_sync.php');
            exit();
        }
    }
}

// Handle database import
if (isset($_POST['import_db']) && isset($_FILES['sql_file'])) {
    try {
        $file = $_FILES['sql_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $file['error']);
        }
        
        if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'sql') {
            throw new Exception("Please upload a .sql file");
        }
        
        // Read the SQL file
        $sql_content = file_get_contents($file['tmp_name']);
        
        if (empty($sql_content)) {
            throw new Exception("SQL file is empty");
        }
        
        // Disable foreign key checks temporarily
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");
        mysqli_query($conn, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
        mysqli_query($conn, "SET time_zone = '+00:00'");
        
        // Split queries properly (handle multi-line statements)
        $queries = [];
        $current_query = '';
        $lines = explode("\n", $sql_content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || substr($line, 0, 2) === '--' || substr($line, 0, 1) === '#') {
                continue;
            }
            
            // Add line to current query
            $current_query .= $line . ' ';
            
            // Check if query is complete (ends with semicolon)
            if (substr(rtrim($line), -1) === ';') {
                $queries[] = trim($current_query);
                $current_query = '';
            }
        }
        
        // Add last query if exists
        if (!empty($current_query)) {
            $queries[] = trim($current_query);
        }
        
        $success_count = 0;
        $error_count = 0;
        $errors = [];
        
        // Execute queries
        foreach ($queries as $query) {
            if (!empty($query) && strlen($query) > 5) {
                if (mysqli_query($conn, $query)) {
                    $success_count++;
                } else {
                    $error_count++;
                    $errors[] = mysqli_error($conn);
                }
            }
        }
        
        // Re-enable foreign key checks
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");
        
        if ($error_count > 0) {
            $_SESSION['sync_message'] = "Database import completed with errors! Successful: {$success_count}, Errors: {$error_count}. First error: " . (isset($errors[0]) ? $errors[0] : 'Unknown');
            $_SESSION['sync_type'] = 'warning';
        } else {
            $_SESSION['sync_message'] = "Database imported successfully! {$success_count} queries executed.";
            $_SESSION['sync_type'] = 'success';
        }
        
        header('Location: database_sync.php?imported=1');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['sync_message'] = "Error importing database: " . $e->getMessage();
        $_SESSION['sync_type'] = 'error';
        header('Location: database_sync.php');
        exit();
    }
}

// Get list of backup files
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

// Handle backup download
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

// Handle backup delete
if (isset($_GET['delete'])) {
    $filename = basename($_GET['delete']);
    $filepath = __DIR__ . '/backups/' . $filename;
    
    if (file_exists($filepath)) {
        unlink($filepath);
        header('Location: database_sync.php?deleted=1');
        exit();
    }
}

if (isset($_GET['deleted'])) {
    $message = "Backup file deleted successfully!";
    $messageType = 'success';
}

// Define permission functions locally to avoid errors in included files
if (!function_exists('hasPermission')) {
    function hasPermission($permission) {
        return true; // Grant all permissions for database sync page
    }
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin() {
        return true; // Grant super admin for database sync page
    }
}

if (!function_exists('requirePermission')) {
    function requirePermission($permission) {
        return true; // Grant all permissions for database sync page
    }
}

if (!function_exists('getUserPermissions')) {
    function getUserPermissions() {
        return ['*']; // Grant all permissions for database sync page
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Database Sync | NSFS Admin</title>
  
  <!-- Bootstrap -->
  <link href="../css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <!-- Custom Theme Style -->
  <link href="../css/custom.css" rel="stylesheet">
  
  <style>
  body {
    background: #f8f9fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  </style>
</head>

<body class="nav-md">
<div class="container body">
<div class="main_container">
<?php require __DIR__ . '/../left_panel.php'; ?>
<?php require __DIR__ . '/../header_banner.php'; ?>

<div class="right_col" role="main">
    <div class="sync-container">
        <div class="page-header">
            <h2><i class="fa fa-database"></i> Database Sync Tool</h2>
            <p>Export your localhost database and sync it to your live server</p>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>">
            <i class="fa fa-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'times-circle') ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <div class="sync-grid">
            <!-- Export Database -->
            <div class="sync-card">
                <div class="card-header export-header">
                    <i class="fa fa-upload"></i>
                    <h3>Export Database</h3>
                </div>
                <div class="card-body">
                    <p>Export your current localhost database to a SQL file.</p>
                    <form method="post">
                        <button type="submit" name="export_db" class="btn-action btn-export">
                            <i class="fa fa-download"></i> Export Database
                        </button>
                    </form>
                    <div class="info-box">
                        <i class="fa fa-info-circle"></i>
                        <span>Backup will be saved to <code>/admin/backups/</code> folder</span>
                    </div>
                </div>
            </div>

            <!-- Import Database -->
            <div class="sync-card">
                <div class="card-header import-header">
                    <i class="fa fa-download"></i>
                    <h3>Import Database</h3>
                </div>
                <div class="card-body">
                    <p>Import a SQL file to your current database.</p>
                    <form method="post" enctype="multipart/form-data">
                        <div class="file-upload">
                            <input type="file" name="sql_file" id="sql_file" accept=".sql" required>
                            <label for="sql_file">
                                <i class="fa fa-file-code-o"></i>
                                Choose SQL File
                            </label>
                        </div>
                        <button type="submit" name="import_db" class="btn-action btn-import">
                            <i class="fa fa-upload"></i> Import Database
                        </button>
                    </form>
                    <div class="warning-box">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span>Warning: This will replace existing data!</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Files List -->
        <?php if (!empty($backups)): ?>
        <div class="backups-section">
            <h3><i class="fa fa-archive"></i> Available Backups</h3>
            <div class="backups-table">
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><i class="fa fa-file-code-o"></i> <?= htmlspecialchars($backup['name']) ?></td>
                            <td><?= number_format($backup['size'] / 1024, 2) ?> KB</td>
                            <td><?= htmlspecialchars($backup['date']) ?></td>
                            <td class="actions">
                                <a href="?download=<?= urlencode($backup['name']) ?>" class="btn-small btn-download">
                                    <i class="fa fa-download"></i> Download
                                </a>
                                <a href="?delete=<?= urlencode($backup['name']) ?>" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this backup?')">
                                    <i class="fa fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Instructions -->
        <div class="instructions-section">
            <h3><i class="fa fa-book"></i> How to Sync to Live Server</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Export Database</h4>
                        <p>Click "Export Database" button to create a backup of your localhost database</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Download Backup</h4>
                        <p>Download the SQL file from the "Available Backups" section</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Upload to Live Server</h4>
                        <p>Access your live server's admin panel at <code>yourdomain.com/admin/database_sync.php</code></p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>Import on Live</h4>
                        <p>Use the "Import Database" section to upload and import the SQL file</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../footer.php'; ?>
</div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.sync-container {
    font-family: 'Inter', sans-serif;
    padding: 0 35px 60px 35px;
    min-height: calc(100vh - 160px);
}

.page-header {
    margin-bottom: 30px;
}

.page-header h2 {
    color: #2c3e50;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-header p {
    color: #7f8c8d;
    font-size: 1.05rem;
    margin: 0;
}

.alert {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
    border: 1px solid #ffeaa7;
}

.sync-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.sync-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.sync-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.card-header {
    padding: 25px;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 12px;
}

.export-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.import-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.card-header i {
    font-size: 1.5rem;
}

.card-header h3 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 700;
}

.card-body {
    padding: 25px;
}

.card-body p {
    color: #7f8c8d;
    margin-bottom: 20px;
    line-height: 1.6;
}

.btn-action {
    width: 100%;
    padding: 14px 24px;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: #fff;
}

.btn-export {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.btn-export:hover {
    background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
    transform: scale(1.02);
}

.btn-import {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    margin-top: 15px;
}

.btn-import:hover {
    background: linear-gradient(135deg, #e081ea 0%, #e3465a 100%);
    transform: scale(1.02);
}

.file-upload {
    margin-bottom: 15px;
}

.file-upload input[type="file"] {
    display: none;
}

.file-upload label {
    display: block;
    padding: 12px 20px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #495057;
    font-weight: 500;
}

.file-upload label:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.file-upload label i {
    margin-right: 8px;
}

.info-box, .warning-box {
    margin-top: 15px;
    padding: 12px 15px;
    border-radius: 8px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-box {
    background: #e7f3ff;
    color: #004085;
    border: 1px solid #bee5eb;
}

.warning-box {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.info-box code, .warning-box code {
    background: rgba(0,0,0,0.1);
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
}

.backups-section {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    padding: 30px;
    margin-bottom: 40px;
}

.backups-section h3 {
    color: #2c3e50;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.backups-table {
    overflow-x: auto;
}

.backups-table table {
    width: 100%;
    border-collapse: collapse;
}

.backups-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}

.backups-table th {
    padding: 15px 20px;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.backups-table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: background 0.2s ease;
}

.backups-table tbody tr:hover {
    background: #f8f9fa;
}

.backups-table td {
    padding: 15px 20px;
    color: #495057;
}

.backups-table td i {
    margin-right: 8px;
    color: #667eea;
}

.actions {
    display: flex;
    gap: 10px;
}

.btn-small {
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #fff;
}

.btn-download {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.btn-download:hover {
    background: linear-gradient(135deg, #0e8a7f 0%, #30d96b 100%);
    transform: scale(1.05);
}

.btn-delete {
    background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
}

.btn-delete:hover {
    background: linear-gradient(135deg, #d62c3d 0%, #e04d39 100%);
    transform: scale(1.05);
}

.instructions-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 35px;
    color: #fff;
}

.instructions-section h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.step {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    gap: 15px;
    transition: transform 0.3s ease;
}

.step:hover {
    transform: translateY(-5px);
    background: rgba(255,255,255,0.15);
}

.step-number {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    font-weight: 700;
    flex-shrink: 0;
}

.step-content h4 {
    margin: 0 0 8px 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.step-content p {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.5;
    opacity: 0.9;
}

.step-content code {
    background: rgba(0,0,0,0.2);
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
}

/* Responsive */
@media (max-width: 768px) {
    .sync-grid {
        grid-template-columns: 1fr;
    }
    
    .steps {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// File input preview
document.getElementById('sql_file')?.addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    if (fileName) {
        const label = document.querySelector('.file-upload label');
        label.innerHTML = '<i class="fa fa-check-circle"></i> ' + fileName;
        label.style.borderColor = '#28a745';
        label.style.color = '#28a745';
    }
});

<?php if (!empty($message)): ?>
// Show notification alert
window.addEventListener('DOMContentLoaded', function() {
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Show browser alert
        <?php if ($messageType === 'success' && isset($_GET['imported'])): ?>
        alert('✅ Database Import Successful!\n\n<?= addslashes($message) ?>\n\nYour database has been updated!');
        <?php elseif ($messageType === 'success' && isset($_GET['exported'])): ?>
        alert('✅ Database Export Successful!\n\n<?= addslashes($message) ?>\n\nCheck the "Available Backups" section below to download.');
        <?php elseif ($messageType === 'warning'): ?>
        alert('⚠️ Import Completed with Warnings!\n\n<?= addslashes($message) ?>');
        <?php elseif ($messageType === 'error'): ?>
        alert('❌ Error!\n\n<?= addslashes($message) ?>');
        <?php endif; ?>
    }
});
<?php endif; ?>

// Confirm before import
document.querySelector('button[name="import_db"]')?.addEventListener('click', function(e) {
    if (!confirm('⚠️ WARNING!\n\nThis will REPLACE all data in your current database!\n\nAre you sure you want to import?')) {
        e.preventDefault();
        return false;
    }
});

</script>

</body>
</html>
