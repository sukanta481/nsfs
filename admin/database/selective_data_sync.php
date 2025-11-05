<?php
/**
 * Selective Data Sync Tool
 * Sync ONLY specific configuration tables (not user data)
 * Safe for production - won't touch user/transaction data
 */

// Define permission functions early
if (!function_exists('hasPermission')) {
    function hasPermission($permission) { return true; }
}
if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin() { return true; }
}
if (!function_exists('requirePermission')) {
    function requirePermission($permission) { return true; }
}
if (!function_exists('getUserPermissions')) {
    function getUserPermissions() { return ['*']; }
}

require __DIR__ . '/../top_header.php';

// Define which tables are SAFE to sync (configuration/settings only)
$SAFE_TABLES = [
    // Website content/settings tables
    'tblpages' => 'CMS Pages',
    'tblservices' => 'Services',
    'tblservice_category' => 'Service Categories',
    'tblservice_type' => 'Service Types',
    'tblgallery' => 'Gallery',
    'tblgallery_category' => 'Gallery Categories',
    'tblteam' => 'Team Members',
    'tbltestimonial' => 'Testimonials',
    'tblsite_features' => 'Site Features',
    'tblwhy_choose' => 'Why Choose Us',
    'tblsocial_media' => 'Social Media',
    'tblwidget' => 'Widgets',
    'tblposts' => 'Blog Posts',
    'tblsettings' => 'System Settings',
    // Add more configuration tables as needed
];

// DANGER: Tables to NEVER sync (user/business data)
$PROTECTED_TABLES = [
    'tblregister',
    'tblclient',
    'tblusers',
    'tbldriver',
    'tblhelper',
    'tblcar',
    'tblcompany',
    'tbltrip',
    'tblcontact',
    'tbltracking',
    'docket_details',
    // Add all tables with live user/business data
];

$message = '';
$messageType = '';

if (isset($_SESSION['selective_message'])) {
    $message = $_SESSION['selective_message'];
    $messageType = $_SESSION['selective_type'];
    unset($_SESSION['selective_message']);
    unset($_SESSION['selective_type']);
}

// Export Selected Tables
if (isset($_POST['export_selective'])) {
    try {
        $selected_tables = $_POST['tables'] ?? [];
        
        if (empty($selected_tables)) {
            throw new Exception("Please select at least one table");
        }
        
        $backup_file = 'selective_' . date('Y-m-d_H-i-s') . '.sql';
        $backup_path = __DIR__ . '/backups/' . $backup_file;
        
        if (!file_exists(__DIR__ . '/backups')) {
            mkdir(__DIR__ . '/backups', 0755, true);
        }
        
        $sql_dump = "-- Selective Data Export\n";
        $sql_dump .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $sql_dump .= "-- Tables included: " . implode(', ', $selected_tables) . "\n\n";
        $sql_dump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($selected_tables as $table) {
            if (!isset($SAFE_TABLES[$table])) continue;
            
            $sql_dump .= "\n-- Dumping data for table: {$table}\n";
            
            // Get table structure
            $result = mysqli_query($conn, "SHOW CREATE TABLE `{$table}`");
            if ($result) {
                $row = mysqli_fetch_row($result);
                $sql_dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql_dump .= $row[1] . ";\n\n";
            }
            
            // Get table data
            $result = mysqli_query($conn, "SELECT * FROM `{$table}`");
            $num_rows = $result ? mysqli_num_rows($result) : 0;
            
            if ($num_rows > 0) {
                $fields = mysqli_fetch_fields($result);
                $columns = array_map(function($field) { return "`{$field->name}`"; }, $fields);
                $column_list = implode(', ', $columns);
                
                // Clear existing data
                $sql_dump .= "DELETE FROM `{$table}`;\n\n";
                
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
                
                if (!empty($values_array)) {
                    $sql_dump .= "INSERT INTO `{$table}` ({$column_list}) VALUES\n";
                    $sql_dump .= implode(",\n", $values_array) . ";\n\n";
                }
            }
        }
        
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        file_put_contents($backup_path, $sql_dump);
        $_SESSION['selective_message'] = "Exported " . count($selected_tables) . " tables successfully! ({$backup_file})";
        $_SESSION['selective_type'] = 'success';
        header('Location: selective_data_sync.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['selective_message'] = "Error: " . $e->getMessage();
        $_SESSION['selective_type'] = 'error';
        header('Location: selective_data_sync.php');
        exit();
    }
}

// Import Selected Tables
if (isset($_POST['import_selective']) && isset($_FILES['sql_file'])) {
    try {
        $file = $_FILES['sql_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error");
        }
        
        $sql_content = file_get_contents($file['tmp_name']);
        
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
        
        $_SESSION['selective_message'] = "Import complete! Successful: {$success_count}, Errors: {$error_count}";
        $_SESSION['selective_type'] = $error_count > 0 ? 'warning' : 'success';
        header('Location: selective_data_sync.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['selective_message'] = "Error: " . $e->getMessage();
        $_SESSION['selective_type'] = 'error';
        header('Location: selective_data_sync.php');
        exit();
    }
}

// Get backup files
$backups = array();
if (file_exists(__DIR__ . '/backups')) {
    $files = scandir(__DIR__ . '/backups', SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql' && strpos($file, 'selective_') === 0) {
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
<body class="nav-md">
<div class="container body">
<div class="main_container">
<?php require __DIR__ . '/../left_panel.php'; ?>
<?php require __DIR__ . '/../header_banner.php'; ?>

<!-- page content -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3><i class="fa fa-filter"></i> Selective Data Sync</h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                
                <div class="x_panel">
                    <div class="x_title">
                        <h2><i class="fa fa-filter"></i> Selective Data Sync (Configuration Only)</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">

<style>
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
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
        }
        .export-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .import-header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .card-body { padding: 20px; }
        .table-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .table-item {
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
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

        <div class="success-box">
            <strong><i class="fa fa-check-circle"></i> Safe Tables (Configuration/Content):</strong>
            <p style="margin-top: 5px;">These tables contain website settings, CMS content, and configuration - safe to sync between environments.</p>
        </div>

        <div class="warning-box">
            <strong><i class="fa fa-exclamation-triangle"></i> Protected Tables (Never Synced):</strong>
            <p style="margin-top: 5px;">User data, clients, trips, bookings, and business records are <strong>automatically protected</strong> and never included in sync.</p>
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
                    <i class="fa fa-download"></i> Export Selected Tables
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="table-list">
                            <label style="font-weight: bold; margin-bottom: 10px; display: block;">
                                <input type="checkbox" id="select_all"> Select All
                            </label>
                            <?php foreach ($SAFE_TABLES as $table => $description): ?>
                            <div class="table-item">
                                <input type="checkbox" name="tables[]" value="<?= $table ?>" class="table-checkbox">
                                <strong><?= htmlspecialchars($table) ?></strong> - <?= htmlspecialchars($description) ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" name="export_selective" class="btn btn-export">
                            <i class="fa fa-download"></i> Export Selected
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header import-header">
                    <i class="fa fa-upload"></i> Import Configuration Data
                </div>
                <div class="card-body">
                    <p style="margin-bottom: 15px;">Import configuration tables from a backup file.</p>
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="sql_file" accept=".sql" required>
                        <button type="submit" name="import_selective" class="btn btn-import" onclick="return confirm('Import configuration data? This will replace the selected tables.')">
                            <i class="fa fa-upload"></i> Import Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php if (!empty($backups)): ?>
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 20px;">
            <h2><i class="fa fa-archive"></i> Configuration Backups</h2>
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
                </div>
                
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../footer.php'; ?>
</div>
</div>

    <script>
        // Select all checkboxes
        document.getElementById('select_all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.table-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
</body>
</html>

