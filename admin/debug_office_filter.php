<?php
session_name('pro');
session_start();
require 'conn.php';
require 'check_auth.php';

echo "<h2>Office Filter Debug Information</h2>";
echo "<hr>";

echo "<h3>1. Session Variables:</h3>";
echo "<pre>";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
echo "role_id: " . ($_SESSION['role_id'] ?? 'NOT SET') . "\n";
echo "role_name: " . ($_SESSION['role_name'] ?? 'NOT SET') . "\n";
echo "office_id: " . (isset($_SESSION['office_id']) ? ($_SESSION['office_id'] ?? 'NULL') : 'NOT SET') . "\n";
echo "office_name: " . ($_SESSION['office_name'] ?? 'NOT SET / NULL') . "\n";
echo "can_access_all_offices: " . ($_SESSION['can_access_all_offices'] ?? 'NOT SET') . "\n";
echo "is_legacy_admin: " . (isset($_SESSION['is_legacy_admin']) ? ($_SESSION['is_legacy_admin'] ? 'YES' : 'NO') : 'NOT SET') . "\n";
echo "</pre>";

echo "<h3>2. User Role Checks:</h3>";
echo "<pre>";
echo "isSuperAdmin(): " . (isSuperAdmin() ? 'YES' : 'NO') . "\n";
echo "hasPermission('office_view_all'): " . (hasPermission('office_view_all') ? 'YES' : 'NO') . "\n";
echo "</pre>";

echo "<h3>3. Office Filter Output:</h3>";
echo "<pre>";
$officeFilter = getOfficeFilter('dd');
echo "getOfficeFilter('dd'): '" . htmlspecialchars($officeFilter) . "'\n";
if (empty($officeFilter)) {
    echo "\n⚠️ FILTER IS EMPTY - User will see ALL dockets\n";
    echo "Reason: User is either Super Admin, has office_view_all permission, can_access_all_offices flag is set, or office_id is NULL\n";
} else {
    echo "\n✓ FILTER IS ACTIVE - User will only see dockets from their office\n";
}
echo "</pre>";

echo "<h3>4. User Office Information:</h3>";
echo "<pre>";
$userOffice = getUserOffice();
if ($userOffice) {
    echo "Office ID: " . $userOffice['office_id'] . "\n";
    echo "Office Name: " . $userOffice['office_name'] . "\n";
    echo "Office Address: " . ($userOffice['office_address'] ?? 'N/A') . "\n";
    echo "Office Phone: " . ($userOffice['office_phone'] ?? 'N/A') . "\n";
} else {
    echo "No office assigned to this user\n";
}
echo "</pre>";

echo "<h3>5. Test Query - Total Dockets Count:</h3>";
echo "<pre>";
$officeFilterClause = !empty($officeFilter) ? "WHERE 1=1 $officeFilter" : "";
$sql = "SELECT COUNT(*) as total FROM docket_details dd $officeFilterClause";
echo "SQL: " . $sql . "\n\n";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Total Dockets Visible to This User: " . $row['total'] . "\n";
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
echo "</pre>";

echo "<h3>6. All Dockets Count (No Filter):</h3>";
echo "<pre>";
$sql_all = "SELECT COUNT(*) as total FROM docket_details";
$result_all = mysqli_query($conn, $sql_all);
if ($result_all) {
    $row_all = mysqli_fetch_assoc($result_all);
    echo "Total Dockets in Database: " . $row_all['total'] . "\n";
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
echo "</pre>";

echo "<h3>7. Dockets by Office:</h3>";
echo "<pre>";
$sql_by_office = "SELECT dd.office_id, o.office_name, COUNT(*) as count
                  FROM docket_details dd
                  LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
                  GROUP BY dd.office_id
                  ORDER BY count DESC";
$result_by_office = mysqli_query($conn, $sql_by_office);
if ($result_by_office) {
    echo "Office ID | Office Name           | Docket Count\n";
    echo "--------- | --------------------- | ------------\n";
    while ($row = mysqli_fetch_assoc($result_by_office)) {
        printf("%-9s | %-21s | %d\n",
            $row['office_id'] ?? 'NULL',
            $row['office_name'] ?? 'No Office',
            $row['count']
        );
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
echo "</pre>";

echo "<h3>8. User's Permissions:</h3>";
echo "<pre>";
if (isset($_SESSION['permissions']) && is_array($_SESSION['permissions'])) {
    echo "Permissions: " . implode(", ", $_SESSION['permissions']) . "\n";
} else {
    echo "No permissions array in session\n";
}
echo "</pre>";

echo "<hr>";
echo "<p><a href='index.php'>← Back to Dashboard</a></p>";
?>
