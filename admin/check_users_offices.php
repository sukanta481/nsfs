<?php
session_name('pro');
session_start();
require 'conn.php';
require 'check_auth.php';

// Only allow Super Admin to view this
if (!isSuperAdmin()) {
    die("Access denied. Only Super Admin can view this page.");
}

echo "<h2>User-Office Assignment Check</h2>";
echo "<p>This page shows which users are assigned to which offices and whether filtering will work for them.</p>";
echo "<hr>";

$sql = "SELECT
    u.user_id,
    u.username,
    r.role_name,
    u.office_id,
    o.office_name,
    u.can_access_all_offices,
    o.office_address,
    (SELECT COUNT(*) FROM docket_details dd WHERE dd.office_id = u.office_id) as docket_count
FROM tbl_users u
LEFT JOIN tbl_roles r ON u.role_id = r.role_id
LEFT JOIN tbl_offices o ON u.office_id = o.office_id
ORDER BY r.role_name, u.username";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error: " . mysqli_error($conn));
}

echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
echo "<thead style='background: #f0f0f0;'>";
echo "<tr>";
echo "<th>User ID</th>";
echo "<th>Username</th>";
echo "<th>Role</th>";
echo "<th>Office ID</th>";
echo "<th>Office Name</th>";
echo "<th>Can Access All</th>";
echo "<th>Dockets in Office</th>";
echo "<th>Filtering Status</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

while ($row = mysqli_fetch_assoc($result)) {
    $filterStatus = "";
    $statusColor = "";

    // Determine filtering status
    if ($row['role_name'] == 'Super Admin') {
        $filterStatus = "NO FILTER (Super Admin)";
        $statusColor = "background: #d4edda;";
    } elseif ($row['can_access_all_offices'] == 1) {
        $filterStatus = "NO FILTER (All Offices Access)";
        $statusColor = "background: #d4edda;";
    } elseif (empty($row['office_id'])) {
        $filterStatus = "NO FILTER (No Office Assigned)";
        $statusColor = "background: #fff3cd;";
    } else {
        $filterStatus = "FILTERED (Office ID: " . $row['office_id'] . ")";
        $statusColor = "background: #cce5ff;";
    }

    echo "<tr>";
    echo "<td>" . $row['user_id'] . "</td>";
    echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($row['role_name'] ?? 'N/A') . "</td>";
    echo "<td>" . ($row['office_id'] ?? '<span style="color: red;">NULL</span>') . "</td>";
    echo "<td>" . htmlspecialchars($row['office_name'] ?? 'Not Assigned') . "</td>";
    echo "<td>" . ($row['can_access_all_offices'] ? 'YES' : 'NO') . "</td>";
    echo "<td>" . $row['docket_count'] . "</td>";
    echo "<td style='$statusColor'><strong>" . $filterStatus . "</strong></td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";

echo "<hr>";
echo "<h3>Summary:</h3>";
echo "<ul>";
echo "<li><strong>Super Admin</strong> - Sees ALL dockets (no filter)</li>";
echo "<li><strong>Can Access All Offices = YES</strong> - Sees ALL dockets (no filter)</li>";
echo "<li><strong>Office ID = NULL</strong> - Sees ALL dockets (treated as head office)</li>";
echo "<li><strong>Office ID = Specific Number</strong> - ONLY sees dockets from that office (FILTERED)</li>";
echo "</ul>";

echo "<h3>How to Fix Users Who Should Be Filtered:</h3>";
echo "<ol>";
echo "<li>Identify which users should only see dockets from their specific branch/office</li>";
echo "<li>Update their <code>office_id</code> field in the <code>tbl_users</code> table to match the office ID from <code>tbl_offices</code></li>";
echo "<li>Set <code>can_access_all_offices</code> to <code>0</code> (or NULL) for those users</li>";
echo "<li>Example SQL: <code>UPDATE tbl_users SET office_id = 1, can_access_all_offices = 0 WHERE user_id = 5;</code></li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='index.php'>← Back to Dashboard</a></p>";
?>
