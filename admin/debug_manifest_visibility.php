<?php
/**
 * Debug script to check manifest docket visibility
 * Helps diagnose why Bardhaman office can't see manifested dockets
 */

session_name('pro');
session_start();
require 'conn.php';
require 'check_auth.php';

echo "<h2>Manifest Docket Visibility Debug</h2>";
echo "<hr>";

// Current user info
echo "<h3>1. Current User Information:</h3>";
echo "<pre>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "\n";
echo "Username: " . ($_SESSION['username'] ?? 'Not set') . "\n";
echo "Office ID: " . ($_SESSION['office_id'] ?? 'NULL/Not set') . "\n";
echo "Can Access All Offices: " . (isset($_SESSION['can_access_all_offices']) ? $_SESSION['can_access_all_offices'] : 'Not set') . "\n";
echo "Is Super Admin: " . (isSuperAdmin() ? 'YES' : 'NO') . "\n";
echo "Has docket_view_all: " . (hasPermission('docket_view_all') ? 'YES' : 'NO') . "\n";
echo "Has office_view_all: " . (hasPermission('office_view_all') ? 'YES' : 'NO') . "\n";
echo "</pre>";

// Get office filter
echo "<h3>2. Office Filter Being Applied:</h3>";
echo "<pre>";
$officeFilter = getOfficeFilter('dd');
if (empty($officeFilter)) {
    echo "NO FILTER - User can see ALL offices\n";
} else {
    echo "Filter: " . htmlspecialchars($officeFilter) . "\n";
}

$creatorFilter = getCreatorFilter('dd');
if (empty($creatorFilter)) {
    echo "NO CREATOR FILTER - User can see all dockets in their office\n";
} else {
    echo "Creator Filter: " . htmlspecialchars($creatorFilter) . "\n";
}
echo "</pre>";

// List all offices
echo "<h3>3. All Offices in System:</h3>";
$offices_query = "SELECT office_id, office_name, office_address FROM tbl_offices ORDER BY office_name";
$offices_result = mysqli_query($conn, $offices_query);
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Office ID</th><th>Office Name</th><th>Address</th></tr>";
while ($office = mysqli_fetch_assoc($offices_result)) {
    $highlight = '';
    if (isset($_SESSION['office_id']) && $office['office_id'] == $_SESSION['office_id']) {
        $highlight = 'background-color: #ffeb3b;';
    }
    echo "<tr style='$highlight'>";
    echo "<td>" . $office['office_id'] . "</td>";
    echo "<td>" . htmlspecialchars($office['office_name']) . "</td>";
    echo "<td>" . htmlspecialchars($office['office_address']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check manifests
echo "<h3>4. Manifests in System:</h3>";
$manifest_query = "SELECT m.manifest_id, m.manifest_no, m.office_id, o.office_name, m.created_at,
                   (SELECT COUNT(*) FROM tbl_manifest_details md WHERE md.manifest_id = m.manifest_id) as docket_count
                   FROM tbl_manifest m
                   LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                   ORDER BY m.manifest_id DESC
                   LIMIT 20";
$manifest_result = mysqli_query($conn, $manifest_query);
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Manifest ID</th><th>Manifest No</th><th>Destination Office ID</th><th>Destination Office</th><th>Docket Count</th><th>Created At</th></tr>";
while ($manifest = mysqli_fetch_assoc($manifest_result)) {
    $highlight = '';
    if (isset($_SESSION['office_id']) && $manifest['office_id'] == $_SESSION['office_id']) {
        $highlight = 'background-color: #c8e6c9;';
    }
    echo "<tr style='$highlight'>";
    echo "<td>" . $manifest['manifest_id'] . "</td>";
    echo "<td>" . htmlspecialchars($manifest['manifest_no']) . "</td>";
    echo "<td>" . $manifest['office_id'] . "</td>";
    echo "<td>" . htmlspecialchars($manifest['office_name']) . "</td>";
    echo "<td>" . $manifest['docket_count'] . "</td>";
    echo "<td>" . $manifest['created_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check dockets by office_id
echo "<h3>5. Dockets by Office ID:</h3>";
$docket_count_query = "SELECT dd.office_id, o.office_name, COUNT(*) as count
                       FROM docket_details dd
                       LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
                       GROUP BY dd.office_id, o.office_name
                       ORDER BY count DESC";
$docket_count_result = mysqli_query($conn, $docket_count_query);
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Office ID</th><th>Office Name</th><th>Docket Count</th></tr>";
while ($row = mysqli_fetch_assoc($docket_count_result)) {
    $highlight = '';
    if (isset($_SESSION['office_id']) && $row['office_id'] == $_SESSION['office_id']) {
        $highlight = 'background-color: #c8e6c9;';
    }
    echo "<tr style='$highlight'>";
    echo "<td>" . ($row['office_id'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['office_name'] ?? 'No Office') . "</td>";
    echo "<td>" . $row['count'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check what dockets current user can see
echo "<h3>6. Dockets Current User Can See (Filtered):</h3>";
$combinedFilter = $officeFilter . $creatorFilter;
$whereClause = !empty($combinedFilter) ? "WHERE 1=1 $combinedFilter" : "";
$user_dockets_query = "SELECT dd.doc_no, dd.office_id, o.office_name, dd.status, dd.created_by, u.username as creator,
                       dd.manifest_id, dd.created_at
                       FROM docket_details dd
                       LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
                       LEFT JOIN tbl_users u ON dd.created_by = u.user_id
                       $whereClause
                       ORDER BY dd.docket_id DESC
                       LIMIT 50";
$user_dockets_result = mysqli_query($conn, $user_dockets_query);
$count = mysqli_num_rows($user_dockets_result);
echo "<p><strong>Total dockets visible to current user: $count</strong></p>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Doc No</th><th>Office ID</th><th>Office Name</th><th>Status</th><th>Manifest ID</th><th>Created By</th><th>Created At</th></tr>";
while ($docket = mysqli_fetch_assoc($user_dockets_result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($docket['doc_no']) . "</td>";
    echo "<td>" . ($docket['office_id'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($docket['office_name'] ?? 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars($docket['status']) . "</td>";
    echo "<td>" . ($docket['manifest_id'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($docket['creator'] ?? 'N/A') . "</td>";
    echo "<td>" . $docket['created_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Recommendations
echo "<hr>";
echo "<h3>7. Diagnosis & Recommendations:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";

if (isset($_SESSION['office_id']) && !empty($_SESSION['office_id'])) {
    $user_office_id = $_SESSION['office_id'];
    
    // Check if there are dockets for this office
    $check_query = "SELECT COUNT(*) as count FROM docket_details WHERE office_id = $user_office_id";
    $check_result = mysqli_query($conn, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['count'] == 0) {
        echo "<p><strong>⚠️ PROBLEM FOUND:</strong> No dockets have office_id = $user_office_id</p>";
        echo "<p><strong>Possible Causes:</strong></p>";
        echo "<ul>";
        echo "<li>No manifests have been sent TO this office yet</li>";
        echo "<li>Dockets for this office haven't been created yet</li>";
        echo "<li>Office ID mismatch in manifest creation</li>";
        echo "</ul>";
        
        echo "<p><strong>✅ SOLUTION:</strong></p>";
        echo "<ul>";
        echo "<li>When creating a manifest, ensure the 'office_id' field in tbl_manifest is set to the DESTINATION office (e.g., Bardhaman office ID)</li>";
        echo "<li>The manifest_save.php script should update docket_details.office_id to the destination office</li>";
        echo "<li>Check if manifests are being created with the correct office_id</li>";
        echo "</ul>";
    } else {
        echo "<p><strong>✅ Good:</strong> Found " . $check_row['count'] . " dockets for office ID $user_office_id</p>";
        
        if ($count == 0 && !empty($creatorFilter)) {
            echo "<p><strong>⚠️ ISSUE:</strong> You have creator filter but no dockets created by you</p>";
            echo "<p>This means you're an individual user (not office-level) and you haven't created any dockets yet.</p>";
        }
    }
} else {
    echo "<p><strong>ℹ️ INFO:</strong> You have no office restriction (can see all offices)</p>";
}

echo "</div>";

mysqli_close($conn);
?>
