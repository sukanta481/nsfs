<?php
/**
 * Debug Bardhaman User Access
 * Check why Bardhaman office sees 0 dockets
 */

session_name('pro');
session_start();
require 'conn.php';
require 'check_auth.php';

echo "<h2>Debug: Why Bardhaman Sees 0 Dockets</h2>";
echo "<hr>";

// 1. Current User Info
echo "<h3>1. Current Logged-in User:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Value</th></tr>";
echo "<tr><td>User ID</td><td>" . ($_SESSION['user_id'] ?? 'NULL') . "</td></tr>";
echo "<tr><td>Username</td><td>" . ($_SESSION['username'] ?? 'NULL') . "</td></tr>";
echo "<tr><td>Office ID (Session)</td><td><strong>" . ($_SESSION['office_id'] ?? 'NULL') . "</strong></td></tr>";
echo "<tr><td>Office Name (Session)</td><td>" . ($_SESSION['office_name'] ?? 'NULL') . "</td></tr>";
echo "<tr><td>Can Access All Offices</td><td>" . ($_SESSION['can_access_all_offices'] ?? 'NULL') . "</td></tr>";
echo "<tr><td>Is Super Admin</td><td>" . (isSuperAdmin() ? 'YES' : 'NO') . "</td></tr>";
echo "<tr><td>Has docket_view_all</td><td>" . (hasPermission('docket_view_all') ? 'YES' : 'NO') . "</td></tr>";
echo "</table>";

// 2. Get user's actual office_id from database
$user_id = intval($_SESSION['user_id'] ?? 0);
if ($user_id > 0) {
    $user_query = "SELECT u.*, o.office_name 
                   FROM tbl_users u 
                   LEFT JOIN tbl_offices o ON u.office_id = o.office_id 
                   WHERE u.user_id = $user_id";
    $user_result = mysqli_query($conn, $user_query);
    $user = mysqli_fetch_assoc($user_result);
    
    echo "<h3>2. User Record in Database:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><td>User ID</td><td>" . $user['user_id'] . "</td></tr>";
    echo "<tr><td>Username</td><td>" . htmlspecialchars($user['username']) . "</td></tr>";
    echo "<tr><td>Office ID (Database)</td><td><strong style='color:blue;'>" . ($user['office_id'] ?? 'NULL') . "</strong></td></tr>";
    echo "<tr><td>Office Name</td><td>" . htmlspecialchars($user['office_name'] ?? 'NULL') . "</td></tr>";
    echo "<tr><td>Role ID</td><td>" . ($user['role_id'] ?? 'NULL') . "</td></tr>";
    echo "</table>";
    
    $db_office_id = $user['office_id'] ?? null;
    $session_office_id = $_SESSION['office_id'] ?? null;
    
    if ($db_office_id != $session_office_id) {
        echo "<div style='background: #ffcdd2; padding: 15px; border-left: 4px solid #f44336; margin: 10px 0;'>";
        echo "<h4>⚠️ MISMATCH!</h4>";
        echo "<p>Session office_id ($session_office_id) doesn't match Database office_id ($db_office_id)</p>";
        echo "<p><strong>Solution:</strong> User needs to logout and login again.</p>";
        echo "</div>";
    }
}

// 3. Check filters being applied
echo "<h3>3. Filters Being Applied:</h3>";
$officeFilter = getOfficeFilter('dd');
$creatorFilter = getCreatorFilter('dd');
echo "<pre>";
echo "Office Filter: " . ($officeFilter ?: '(none - sees all offices)') . "\n";
echo "Creator Filter: " . ($creatorFilter ?: '(none - sees all creators)') . "\n";
echo "Combined: " . ($officeFilter . $creatorFilter ?: '(no filter)') . "\n";
echo "</pre>";

// 4. Check Bardhaman office details
echo "<h3>4. Bardhaman Office Details:</h3>";
$bardhaman_query = "SELECT * FROM tbl_offices WHERE office_name LIKE '%bardhaman%'";
$bardhaman_result = mysqli_query($conn, $bardhaman_query);
while ($office = mysqli_fetch_assoc($bardhaman_result)) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><td>Office ID</td><td><strong>" . $office['office_id'] . "</strong></td></tr>";
    echo "<tr><td>Office Name</td><td>" . htmlspecialchars($office['office_name']) . "</td></tr>";
    echo "<tr><td>Office Address</td><td>" . htmlspecialchars($office['office_address']) . "</td></tr>";
    echo "</table>";
}

// 5. Check dockets with office_id = 2 (Bardhaman)
echo "<h3>5. Dockets with office_id = 2 (Bardhaman):</h3>";
$dockets_query = "SELECT doc_no, office_id, status, created_by, manifest_id FROM docket_details WHERE office_id = 2 ORDER BY docket_id DESC LIMIT 20";
$dockets_result = mysqli_query($conn, $dockets_query);
$count = mysqli_num_rows($dockets_result);
echo "<p><strong>Total dockets with office_id = 2:</strong> $count</p>";

if ($count > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Doc No</th><th>Office ID</th><th>Status</th><th>Manifest ID</th><th>Created By</th></tr>";
    while ($row = mysqli_fetch_assoc($dockets_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['doc_no']) . "</td>";
        echo "<td>" . $row['office_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . ($row['manifest_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['created_by'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background: #ffcdd2; padding: 15px; border-left: 4px solid #f44336;'>";
    echo "<h4>❌ NO DOCKETS FOUND with office_id = 2!</h4>";
    echo "<p>This is why Bardhaman sees 0 dockets.</p>";
    echo "</div>";
}

// 6. Check the manifest dockets
echo "<h3>6. Manifest #2 (Bardhaman) Dockets Check:</h3>";
$manifest_check = "SELECT m.manifest_id, m.office_id as manifest_office, md.doc_no, 
                   dd.office_id as docket_office, dd.docket_id
                   FROM tbl_manifest m
                   LEFT JOIN tbl_manifest_details md ON m.manifest_id = md.manifest_id
                   LEFT JOIN docket_details dd ON md.doc_no = dd.doc_no
                   WHERE m.manifest_id = 2";
$manifest_result = mysqli_query($conn, $manifest_check);
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Manifest ID</th><th>Manifest Office</th><th>Doc No</th><th>Docket Office ID</th><th>Docket ID</th></tr>";
while ($row = mysqli_fetch_assoc($manifest_result)) {
    $style = ($row['docket_office'] != $row['manifest_office']) ? 'background-color: #ffcdd2;' : 'background-color: #c8e6c9;';
    echo "<tr style='$style'>";
    echo "<td>" . $row['manifest_id'] . "</td>";
    echo "<td>" . $row['manifest_office'] . "</td>";
    echo "<td>" . htmlspecialchars($row['doc_no'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['docket_office'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['docket_id'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 7. Test query that dashboard uses
echo "<h3>7. Test Dashboard Query:</h3>";
$combinedFilter = $officeFilter . $creatorFilter;
$whereClause = !empty($combinedFilter) ? "WHERE 1=1 $combinedFilter" : "";
$test_query = "SELECT COUNT(*) as c FROM docket_details dd $whereClause";
echo "<pre>Query: " . htmlspecialchars($test_query) . "</pre>";
$test_result = mysqli_query($conn, $test_query);
$test_row = mysqli_fetch_assoc($test_result);
echo "<p><strong>Result:</strong> " . $test_row['c'] . " dockets</p>";

// 8. Recommendation
echo "<hr>";
echo "<h3>8. Diagnosis & Solution:</h3>";

$session_office = $_SESSION['office_id'] ?? null;
if (empty($session_office)) {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ff9800;'>";
    echo "<h4>⚠️ User has no office_id in session!</h4>";
    echo "<p>This means the user can see ALL dockets (no office filter).</p>";
    echo "<p>But if there are 0 dockets showing, it might be a CREATOR filter issue.</p>";
    echo "</div>";
}

if (!empty($creatorFilter)) {
    echo "<div style='background: #ffcdd2; padding: 15px; border-left: 4px solid #f44336;'>";
    echo "<h4>⚠️ Creator Filter is Active!</h4>";
    echo "<p>This user can only see dockets THEY created (created_by = $user_id).</p>";
    echo "<p><strong>This is likely the problem!</strong></p>";
    echo "<p><strong>Solution:</strong> Give this user the <code>docket_view_all</code> permission so they can see all office dockets, not just their own.</p>";
    echo "</div>";
}

mysqli_close($conn);
?>
