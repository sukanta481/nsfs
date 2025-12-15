<?php
/**
 * Check Manifest Dockets Update
 * Verify if dockets are properly linked to manifest and have correct office_id
 */

require 'conn.php';

// Get manifest ID from URL or check latest manifests
$manifest_id = isset($_GET['manifest_id']) ? intval($_GET['manifest_id']) : 0;

if ($manifest_id) {
    echo "<h2>Checking Manifest #$manifest_id</h2>";
} else {
    echo "<h2>Recent Manifests (Click to check details)</h2>";
    $recent = mysqli_query($conn, "SELECT m.manifest_id, m.manifest_no, m.office_id, o.office_name, m.created_at
                                    FROM tbl_manifest m
                                    LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                                    ORDER BY m.manifest_id DESC LIMIT 10");
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($recent)) {
        echo "<li><a href='?manifest_id=" . $row['manifest_id'] . "'>" . 
             htmlspecialchars($row['manifest_no']) . " - To: " . htmlspecialchars($row['office_name']) . 
             " (Office ID: " . $row['office_id'] . ") - " . $row['created_at'] . "</a></li>";
    }
    echo "</ul>";
    exit;
}

echo "<hr>";

// Get manifest details
$manifest_query = "SELECT m.*, o.office_name
                   FROM tbl_manifest m
                   LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                   WHERE m.manifest_id = $manifest_id";
$manifest_result = mysqli_query($conn, $manifest_query);
$manifest = mysqli_fetch_assoc($manifest_result);

if (!$manifest) {
    die("Manifest not found!");
}

echo "<h3>Manifest Info:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Value</th></tr>";
echo "<tr><td>Manifest No</td><td>" . htmlspecialchars($manifest['manifest_no']) . "</td></tr>";
echo "<tr><td>Destination Office ID</td><td><strong>" . $manifest['office_id'] . "</strong></td></tr>";
echo "<tr><td>Destination Office Name</td><td><strong>" . htmlspecialchars($manifest['office_name']) . "</strong></td></tr>";
echo "<tr><td>Created At</td><td>" . $manifest['created_at'] . "</td></tr>";
echo "<tr><td>Total Gross</td><td>₹" . number_format($manifest['total_gross'], 2) . "</td></tr>";
echo "<tr><td>Net Total</td><td>₹" . number_format($manifest['net_total'], 2) . "</td></tr>";
echo "</table>";

echo "<hr>";

// Get dockets in tbl_manifest_details
echo "<h3>Dockets in Manifest (from tbl_manifest_details):</h3>";
$details_query = "SELECT doc_no FROM tbl_manifest_details WHERE manifest_id = $manifest_id";
$details_result = mysqli_query($conn, $details_query);
$manifest_dockets = [];
while ($row = mysqli_fetch_assoc($details_result)) {
    $manifest_dockets[] = $row['doc_no'];
}

echo "<p><strong>Total dockets in manifest:</strong> " . count($manifest_dockets) . "</p>";

if (count($manifest_dockets) > 0) {
    // Check if these dockets exist in docket_details and their office_id
    echo "<h3>Docket Status in docket_details Table:</h3>";
    
    $doc_nos_escaped = array_map(function($doc) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $doc) . "'";
    }, $manifest_dockets);
    $doc_nos_list = implode(',', $doc_nos_escaped);
    
    $check_query = "SELECT doc_no, office_id, manifest_id, status, created_by, created_at
                    FROM docket_details
                    WHERE doc_no IN ($doc_nos_list)";
    $check_result = mysqli_query($conn, $check_query);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Doc No</th><th>office_id in docket_details</th><th>manifest_id</th><th>Status</th><th>Created By</th><th>Match?</th></tr>";
    
    $found = 0;
    $correct_office = 0;
    $correct_manifest = 0;
    $checked_dockets = [];
    
    while ($row = mysqli_fetch_assoc($check_result)) {
        $found++;
        $checked_dockets[] = $row['doc_no'];
        
        $office_match = ($row['office_id'] == $manifest['office_id']);
        $manifest_match = ($row['manifest_id'] == $manifest_id);
        
        if ($office_match) $correct_office++;
        if ($manifest_match) $correct_manifest++;
        
        $style = '';
        if (!$office_match || !$manifest_match) {
            $style = 'background-color: #ffcdd2;'; // Red if mismatch
        } else {
            $style = 'background-color: #c8e6c9;'; // Green if match
        }
        
        echo "<tr style='$style'>";
        echo "<td>" . htmlspecialchars($row['doc_no']) . "</td>";
        echo "<td>" . ($row['office_id'] ?? 'NULL') . ($office_match ? ' ✓' : ' ✗ WRONG!') . "</td>";
        echo "<td>" . ($row['manifest_id'] ?? 'NULL') . ($manifest_match ? ' ✓' : ' ✗ WRONG!') . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . ($row['created_by'] ?? 'NULL') . "</td>";
        echo "<td>" . ($office_match && $manifest_match ? '✓ OK' : '✗ PROBLEM') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>Summary:</h3>";
    echo "<ul>";
    echo "<li>Dockets in manifest: <strong>" . count($manifest_dockets) . "</strong></li>";
    echo "<li>Found in docket_details: <strong>$found</strong></li>";
    echo "<li>With correct office_id: <strong>$correct_office</strong></li>";
    echo "<li>With correct manifest_id: <strong>$correct_manifest</strong></li>";
    echo "</ul>";
    
    // Check for missing dockets
    $missing = array_diff($manifest_dockets, $checked_dockets);
    if (count($missing) > 0) {
        echo "<div style='background: #ffebee; padding: 15px; border-left: 4px solid #f44336;'>";
        echo "<h4>⚠️ CRITICAL: Missing Dockets!</h4>";
        echo "<p>These dockets are in tbl_manifest_details but NOT in docket_details:</p>";
        echo "<ul>";
        foreach ($missing as $doc) {
            echo "<li>" . htmlspecialchars($doc) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    // Final verdict
    echo "<hr>";
    if ($found == count($manifest_dockets) && $correct_office == $found && $correct_manifest == $found) {
        echo "<div style='background: #c8e6c9; padding: 15px; border-left: 4px solid #4caf50;'>";
        echo "<h4>✅ ALL GOOD!</h4>";
        echo "<p>All dockets are properly linked to manifest and have correct office_id.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ff9800;'>";
        echo "<h4>⚠️ ISSUES FOUND!</h4>";
        
        if ($found < count($manifest_dockets)) {
            echo "<p><strong>Problem:</strong> Some dockets are missing from docket_details table!</p>";
            echo "<p><strong>Solution:</strong> The manifest was created in manual mode but dockets were not inserted into docket_details.</p>";
        }
        
        if ($correct_office < $found) {
            echo "<p><strong>Problem:</strong> " . ($found - $correct_office) . " docket(s) have WRONG office_id!</p>";
            echo "<p><strong>Expected office_id:</strong> " . $manifest['office_id'] . " (" . htmlspecialchars($manifest['office_name']) . ")</p>";
            echo "<p><strong>Solution:</strong> Run the fix query below to update office_id.</p>";
            
            // Generate fix query
            echo "<h4>Fix Query:</h4>";
            echo "<textarea style='width: 100%; height: 100px; font-family: monospace;'>";
            echo "UPDATE docket_details SET office_id = " . $manifest['office_id'] . " WHERE doc_no IN ($doc_nos_list);";
            echo "</textarea>";
        }
        
        if ($correct_manifest < $found) {
            echo "<p><strong>Problem:</strong> " . ($found - $correct_manifest) . " docket(s) have WRONG manifest_id!</p>";
            echo "<p><strong>Expected manifest_id:</strong> " . $manifest_id . "</p>";
            echo "<p><strong>Solution:</strong> Run the fix query below to update manifest_id.</p>";
            
            // Generate fix query
            echo "<h4>Fix Query:</h4>";
            echo "<textarea style='width: 100%; height: 100px; font-family: monospace;'>";
            echo "UPDATE docket_details SET manifest_id = " . $manifest_id . " WHERE doc_no IN ($doc_nos_list);";
            echo "</textarea>";
        }
        
        echo "</div>";
    }
    
} else {
    echo "<p style='color: red;'>No dockets found in this manifest!</p>";
}

mysqli_close($conn);
?>
