<?php
/**
 * Authentication Check
 * Include this at the top of protected pages
 * Works with both old (tbl_administrator) and new (tbl_users) systems
 */

if (session_status() === PHP_SESSION_NONE) {
    session_name('pro'); // Use same session name as main application
    session_start();
}

// Check if user is logged in (old or new system)
// Old system uses $_SESSION['admin_id'], new system uses $_SESSION['user_id']
$is_logged_in = (isset($_SESSION['admin_id']) || isset($_SESSION['user_id']));

if (!$is_logged_in) {
    header('Location: login.php');
    exit;
}

// If old system user, set up basic session for compatibility
if (isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = $_SESSION['admin_id'];
    $_SESSION['username'] = $_SESSION['adminname'] ?? 'admin';
    $_SESSION['full_name'] = $_SESSION['adminname'] ?? 'Administrator';
    $_SESSION['role_id'] = 1; // Super Admin role
    $_SESSION['role_name'] = 'Super Admin';
    $_SESSION['is_legacy_admin'] = true;
}

// Function to check if user has specific permission
function hasPermission($permission_key) {
    // Legacy admin has all permissions
    if (isset($_SESSION['is_legacy_admin']) && $_SESSION['is_legacy_admin']) {
        return true;
    }
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
        return false;
    }
    
    global $conn;
    if (!isset($conn)) {
        require_once 'conn.php';
    }
    // Ensure $conn is a valid mysqli connection
    if (!isset($conn) || !($conn instanceof mysqli)) {
        echo "<b>Database connection error.</b> Please contact the administrator.";
        exit();
    }
    
    // Check if permission tables exist - if not, grant access (tables not set up yet)
    $tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_permissions'");
    if (!$tables_check || mysqli_num_rows($tables_check) == 0) {
        // Tables don't exist yet, grant access to logged-in users
        return true;
    }
    
    // Check if user's role has the permission
    $query = "SELECT COUNT(*) as has_perm FROM tbl_role_permissions rp
              JOIN tbl_permissions p ON rp.permission_id = p.permission_id
              WHERE rp.role_id = {$_SESSION['role_id']} AND p.permission_key = '$permission_key'";
    
    $result = mysqli_query($conn, $query);
    if (!$result) {
        // If query fails (tables might not exist), grant access
        return true;
    }
    $row = mysqli_fetch_assoc($result);
    
    return $row['has_perm'] > 0;
}

// Function to get all user permissions
function getUserPermissions() {
    // Legacy admin has all permissions
    if (isset($_SESSION['is_legacy_admin']) && $_SESSION['is_legacy_admin']) {
        return ['*']; // Wildcard for all permissions
    }
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
        return [];
    }
    
    if (!isset($_SESSION['permissions'])) {
        global $conn;
        if (!isset($conn)) {
            require_once 'conn.php';
        }
        // Ensure $conn is a valid mysqli connection
        if (!isset($conn) || !($conn instanceof mysqli)) {
            echo "<b>Database connection error.</b> Please contact the administrator.";
            exit();
        }
        
        // Check if permission tables exist
        $tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_permissions'");
        if (!$tables_check || mysqli_num_rows($tables_check) == 0) {
            // Tables don't exist yet, return wildcard
            $_SESSION['permissions'] = ['*'];
            return ['*'];
        }
        
        $query = "SELECT p.permission_key FROM tbl_role_permissions rp
                  JOIN tbl_permissions p ON rp.permission_id = p.permission_id
                  WHERE rp.role_id = {$_SESSION['role_id']}";
        
        $result = mysqli_query($conn, $query);
        if (!$result) {
            // Query failed, return wildcard
            $_SESSION['permissions'] = ['*'];
            return ['*'];
        }
        
        $permissions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $permissions[] = $row['permission_key'];
        }
        
        $_SESSION['permissions'] = $permissions;
    }
    
    return $_SESSION['permissions'];
}

// Function to require permission (redirect if not authorized)
function requirePermission($permission_key, ...$fallback_permissions) {
    // Legacy admin bypasses permission check
    if (isset($_SESSION['is_legacy_admin']) && $_SESSION['is_legacy_admin']) {
        return;
    }
    
    // Check if permission tables exist - if not, allow access
    global $conn;
    if (!isset($conn)) {
        require_once 'conn.php';
    }
    // Ensure $conn is a valid mysqli connection
    if (!isset($conn) || !($conn instanceof mysqli)) {
        echo "<b>Database connection error.</b> Please contact the administrator.";
        exit();
    }
    
    $tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_permissions'");
    if (!$tables_check || mysqli_num_rows($tables_check) == 0) {
        // Tables don't exist yet, allow access for logged-in users
        return;
    }
    
    // Check primary permission or any fallback permissions
    $has_access = hasPermission($permission_key);
    if (!$has_access && !empty($fallback_permissions)) {
        foreach ($fallback_permissions as $fallback) {
            if (hasPermission($fallback)) {
                $has_access = true;
                break;
            }
        }
    }
    
    if (!$has_access) {
        header('HTTP/1.1 403 Forbidden');
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Access Denied</title>
            <style>
                body { font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f5f5f5; }
                .error-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; max-width: 500px; }
                .error-box i { font-size: 60px; color: #e74c3c; }
                h1 { color: #2c3e50; margin: 20px 0 10px; }
                p { color: #7f8c8d; margin-bottom: 20px; }
                a { display: inline-block; padding: 12px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
            </style>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        </head>
        <body>
            <div class='error-box'>
                <i class='fas fa-lock'></i>
                <h1>Access Denied</h1>
                <p>You don't have permission to access this resource.</p>
                <p><small>Required permission: <code>$permission_key</code></small></p>
                <a href='index.php'><i class='fas fa-home'></i> Go to Dashboard</a>
            </div>
        </body>
        </html>";
        exit;
    }
}

// Function to check if user is Super Admin
function isSuperAdmin() {
    // Legacy admin is super admin
    if (isset($_SESSION['is_legacy_admin']) && $_SESSION['is_legacy_admin']) {
        return true;
    }
    return isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Super Admin';
}

/**
 * Get office filter for SQL queries
 * Returns WHERE clause fragment to filter by user's office
 * 
 * @param string $table_alias - Table alias (e.g., 'dd' for docket_details)
 * @param string $column - Column name (default: 'office_id')
 * @return string - SQL WHERE fragment (e.g., " AND dd.office_id = 5")
 */
function getOfficeFilter($table_alias = 'dd', $column = 'office_id') {
    // Super admin or legacy admin sees all
    if (isSuperAdmin()) {
        return '';
    }
    
    // User with 'Access All Offices' permission sees all
    if (isset($_SESSION['can_access_all_offices']) && $_SESSION['can_access_all_offices'] == 1) {
        return '';
    }
    
    // Check for 'office_view_all' permission
    if (hasPermission('office_view_all')) {
        return '';
    }
    
    // If NO office is assigned (NULL), user can see ALL dockets (head office access)
    if (!isset($_SESSION['office_id']) || empty($_SESSION['office_id']) || $_SESSION['office_id'] === null) {
        return ''; // No filter = see all
    }
    
    // Restrict to user's assigned office
    $office_id = intval($_SESSION['office_id']);
    return " AND {$table_alias}.{$column} = {$office_id}";
}

/**
 * Get office filter for branch_office column (string matching)
 * 
 * @param string $table_alias - Table alias
 * @return string - SQL WHERE fragment
 */
function getOfficeNameFilter($table_alias = 'dd') {
    global $conn;
    
    // Super admin sees all
    if (isSuperAdmin()) {
        return '';
    }
    
    // User with access all offices sees all
    if (isset($_SESSION['can_access_all_offices']) && $_SESSION['can_access_all_offices'] == 1) {
        return '';
    }
    
    // Check for 'office_view_all' permission
    if (hasPermission('office_view_all')) {
        return '';
    }
    
    // If NO office is assigned (NULL), user can see ALL dockets (head office access)
    if (!isset($_SESSION['office_id']) || empty($_SESSION['office_id']) || $_SESSION['office_id'] === null) {
        return ''; // No filter = see all
    }
    
    // Get office name from session or database
    if (isset($_SESSION['office_name']) && !empty($_SESSION['office_name'])) {
        $office_name = mysqli_real_escape_string($conn, $_SESSION['office_name']);
        return " AND {$table_alias}.branch_office = '{$office_name}'";
    }
    
    // If we have office_id, get the name
    if (!isset($conn)) {
        require_once 'conn.php';
    }
    $office_id = intval($_SESSION['office_id']);
    $q = mysqli_query($conn, "SELECT office_name FROM tbl_offices WHERE office_id = $office_id");
    if ($q && $row = mysqli_fetch_assoc($q)) {
        $_SESSION['office_name'] = $row['office_name'];
        $office_name = mysqli_real_escape_string($conn, $row['office_name']);
        return " AND {$table_alias}.branch_office = '{$office_name}'";
    }
    
    return ''; // Fallback to no filter
}

/**
 * Check if user can update to a specific status
 * 
 * @param string $status_name - The status to check
 * @return bool
 */
function canUpdateToStatus($status_name) {
    // Super admin can update to any status
    if (isSuperAdmin()) {
        return true;
    }
    
    // Check general status update permission first
    if (!hasPermission('docket_status_update')) {
        return false;
    }
    
    global $conn;
    if (!isset($conn)) {
        require_once 'conn.php';
    }
    
    // Check if status permissions table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_user_status_permissions'");
    if (!$table_check || mysqli_num_rows($table_check) == 0) {
        // Table doesn't exist - allow all statuses for users with docket_status_update permission
        return true;
    }
    
    // Check if user has any status restrictions defined
    $user_id = intval($_SESSION['user_id']);
    $check_query = "SELECT COUNT(*) as cnt FROM tbl_user_status_permissions WHERE user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['cnt'] == 0) {
        // No specific restrictions - allow all statuses
        return true;
    }
    
    // User has specific status permissions - check if this status is allowed
    // Join with tbl_status_hierarchy to match by status_name
    $status_escaped = mysqli_real_escape_string($conn, $status_name);
    $perm_query = "SELECT usp.can_update 
                   FROM tbl_user_status_permissions usp
                   JOIN tbl_status_hierarchy sh ON usp.status_id = sh.status_id
                   WHERE usp.user_id = $user_id AND sh.status_name = '$status_escaped'";
    $perm_result = mysqli_query($conn, $perm_query);
    
    if ($perm_result && $row = mysqli_fetch_assoc($perm_result)) {
        return $row['can_update'] == 1;
    }
    
    return false; // Status not in user's allowed list
}

/**
 * Get list of statuses user can update to
 * 
 * @return array - List of status names
 */
function getAllowedStatuses() {
    global $conn;
    if (!isset($conn)) {
        require_once 'conn.php';
    }
    
    // Super admin can update to all statuses
    if (isSuperAdmin()) {
        $q = mysqli_query($conn, "SELECT status_name FROM tbl_status_hierarchy ORDER BY status_order");
        $statuses = [];
        while ($row = mysqli_fetch_assoc($q)) {
            $statuses[] = $row['status_name'];
        }
        return $statuses;
    }
    
    // Check if user has status update permission at all
    if (!hasPermission('docket_status_update')) {
        return [];
    }
    
    // Check if status permissions table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_user_status_permissions'");
    
    if ($table_check && mysqli_num_rows($table_check) > 0) {
        // Check if user has specific status restrictions
        $user_id = intval($_SESSION['user_id']);
        $check_query = "SELECT COUNT(*) as cnt FROM tbl_user_status_permissions WHERE user_id = $user_id";
        $check_result = mysqli_query($conn, $check_query);
        $check_row = mysqli_fetch_assoc($check_result);
        
        if ($check_row['cnt'] > 0) {
            // User has specific restrictions - only return allowed statuses
            $perm_query = "SELECT sh.status_name 
                           FROM tbl_user_status_permissions usp
                           JOIN tbl_status_hierarchy sh ON usp.status_id = sh.status_id
                           WHERE usp.user_id = $user_id AND usp.can_update = 1
                           ORDER BY sh.status_order";
            $result = mysqli_query($conn, $perm_query);
            
            $statuses = [];
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $statuses[] = $row['status_name'];
                }
            }
            return $statuses;
        }
    }
    
    // No specific restrictions - return all statuses
    $q = mysqli_query($conn, "SELECT status_name FROM tbl_status_hierarchy ORDER BY status_order");
    $statuses = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $statuses[] = $row['status_name'];
    }
    return $statuses;
}

/**
 * Check if user can access a specific docket
 * 
 * @param int $docket_id - Docket ID
 * @return bool
 */
function canAccessDocket($docket_id) {
    // Super admin can access all
    if (isSuperAdmin()) {
        return true;
    }
    
    // Users with all office access can access all
    if (isset($_SESSION['can_access_all_offices']) && $_SESSION['can_access_all_offices'] == 1) {
        return true;
    }
    
    global $conn;
    if (!isset($conn)) {
        require_once 'conn.php';
    }
    
    $docket_id = intval($docket_id);
    $office_filter = getOfficeFilter('dd');
    
    $query = "SELECT docket_id FROM docket_details dd WHERE docket_id = $docket_id $office_filter";
    $result = mysqli_query($conn, $query);
    
    return $result && mysqli_num_rows($result) > 0;
}

/**
 * Get user's office info
 * 
 * @return array|null - Office info or null if not assigned
 */
function getUserOffice() {
    if (isset($_SESSION['office_id']) && !empty($_SESSION['office_id'])) {
        global $conn;
        if (!isset($conn)) {
            require_once 'conn.php';
        }
        
        $office_id = intval($_SESSION['office_id']);
        $q = mysqli_query($conn, "SELECT * FROM tbl_offices WHERE office_id = $office_id");
        
        if ($q && $row = mysqli_fetch_assoc($q)) {
            return $row;
        }
    }
    return null;
}

/**
 * Log user action for audit
 * 
 * @param string $action_type - 'login', 'logout', 'view', 'create', 'update', 'delete'
 * @param string $module - 'docket', 'manifest', 'user', etc.
 * @param string $record_id - ID of the record
 * @param array $details - Additional details (will be JSON encoded)
 */
function logUserAction($action_type, $module = null, $record_id = null, $details = []) {
    global $conn;
    if (!isset($conn)) {
        require_once 'conn.php';
    }
    
    // Check if log table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_user_access_log'");
    if (!$table_check || mysqli_num_rows($table_check) == 0) {
        return false;
    }
    
    $user_id = intval($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0);
    $action_type = mysqli_real_escape_string($conn, $action_type);
    $module = $module ? mysqli_real_escape_string($conn, $module) : 'NULL';
    $record_id = $record_id ? mysqli_real_escape_string($conn, $record_id) : 'NULL';
    $details_json = mysqli_real_escape_string($conn, json_encode($details));
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT'] ?? '');
    
    $module_val = $module !== 'NULL' ? "'$module'" : 'NULL';
    $record_val = $record_id !== 'NULL' ? "'$record_id'" : 'NULL';
    
    $query = "INSERT INTO tbl_user_access_log 
              (user_id, action_type, module, record_id, details, ip_address, user_agent) 
              VALUES ($user_id, '$action_type', $module_val, $record_val, '$details_json', '$ip', '$user_agent')";
    
    return mysqli_query($conn, $query);
}

/**
 * Get creator filter for SQL queries
 * Returns WHERE clause fragment to filter dockets by creator for limited users
 * 
 * @param string $table_alias - Table alias (e.g., 'dd' for docket_details)
 * @return string - SQL WHERE fragment (e.g., " AND dd.created_by = 5")
 */
function getCreatorFilter($table_alias = 'dd') {
    // Super admin sees all
    if (isSuperAdmin()) {
        return '';
    }
    
    // Users with docket_view_all permission see all dockets (no filter)
    if (hasPermission('docket_view_all')) {
        return '';
    }
    
    // All other users (including those with edit/delete) see only their own dockets
    // This applies to users with special_docket_create, docket_edit, etc.
    $user_id = intval($_SESSION['user_id'] ?? 0);
    if ($user_id > 0) {
        return " AND {$table_alias}.created_by = {$user_id}";
    }
    
    return '';
}

/**
 * Check if user can view a specific docket
 * @param int $docket_id - Docket ID
 * @param int $created_by - Creator user ID from docket
 * @return bool
 */
function canViewDocket($docket_id, $created_by = null) {
    // Super admin can view all
    if (isSuperAdmin()) {
        return true;
    }
    
    // Users with edit/delete/status update permissions can view all
    if (hasPermission('docket_edit') || hasPermission('docket_delete') || hasPermission('docket_status_update')) {
        return true;
    }
    
    // Special docket creators can only view their own
    if (hasPermission('special_docket_create') && !hasPermission('docket_create')) {
        $user_id = intval($_SESSION['user_id'] ?? 0);
        return ($created_by && $user_id == $created_by);
    }
    
    return true; // Default: allow viewing
}
?>
