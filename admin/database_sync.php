<?php
require 'conn.php';

// Session is already started in top_header.php
// No need to start it again

$message = '';
$messageType = '';

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
            $message = "Database exported successfully! File: {$backup_file}";
            $messageType = 'success';
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
            $sql_dump .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
            
            foreach ($tables as $table) {
                // Get table structure
                $result = mysqli_query($conn, "SHOW CREATE TABLE `{$table}`");
                $row = mysqli_fetch_row($result);
                $sql_dump .= "\n\n-- Table structure for `{$table}`\n";
                $sql_dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql_dump .= $row[1] . ";\n";
                
                // Get table data
                $result = mysqli_query($conn, "SELECT * FROM `{$table}`");
                if (mysqli_num_rows($result) > 0) {
                    $sql_dump .= "\n-- Dumping data for table `{$table}`\n";
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sql_dump .= "INSERT INTO `{$table}` VALUES (";
                        $values = array();
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
                            }
                        }
                        $sql_dump .= implode(', ', $values) . ");\n";
                    }
                }
            }
            
            file_put_contents($backup_path, $sql_dump);
            $message = "Database exported successfully (PHP method)! File: {$backup_file}";
            $messageType = 'success';
            
        } catch (Exception $e2) {
            $message = "Error exporting database: " . $e2->getMessage();
            $messageType = 'error';
        }
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
        
        // Split SQL into individual queries
        $queries = array_filter(array_map('trim', explode(';', $sql_content)));
        
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
        
        $message = "Database import completed! Successful: {$success_count}, Errors: {$error_count}";
        $messageType = $error_count > 0 ? 'warning' : 'success';
        
    } catch (Exception $e) {
        $message = "Error importing database: " . $e->getMessage();
        $messageType = 'error';
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

require 'top_header.php';
?>
<body class="nav-md">
<div class="container body">
<div class="main_container">
<?php require 'left_panel.php'; ?>
<?php require 'header_banner.php'; ?>

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

<?php require 'footer.php'; ?>
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
</script>

</body>
</html>
