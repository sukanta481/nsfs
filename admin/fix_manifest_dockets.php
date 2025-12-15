<?php
/**
 * Fix Manifest Dockets
 * Creates missing dockets in docket_details from tbl_manifest_details
 */

require 'conn.php';

echo "<h2>Fix Manifest Dockets - Create Missing Dockets</h2>";
echo "<hr>";

// Get manifest ID from URL
$manifest_id = isset($_GET['manifest_id']) ? intval($_GET['manifest_id']) : 0;

if ($manifest_id) {
    echo "<h3>Processing Manifest ID: $manifest_id</h3>";
    
    // Get manifest info
    $manifest_query = "SELECT m.*, o.office_name
                       FROM tbl_manifest m
                       LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                       WHERE m.manifest_id = $manifest_id";
    $manifest_result = mysqli_query($conn, $manifest_query);
    $manifest = mysqli_fetch_assoc($manifest_result);
    
    if (!$manifest) {
        die("<p style='color: red;'>Manifest not found!</p>");
    }
    
    echo "<p><strong>Manifest:</strong> " . htmlspecialchars($manifest['manifest_no']) . "</p>";
    echo "<p><strong>Destination Office:</strong> " . htmlspecialchars($manifest['office_name']) . " (ID: " . $manifest['office_id'] . ")</p>";
    echo "<hr>";
    
    // Find missing dockets
    $check_query = "SELECT md.*
                    FROM tbl_manifest_details md
                    LEFT JOIN docket_details dd ON md.doc_no = dd.doc_no
                    WHERE md.manifest_id = $manifest_id
                    AND dd.doc_no IS NULL";
    $check_result = mysqli_query($conn, $check_query);
    $missing_count = mysqli_num_rows($check_result);
    
    if ($missing_count == 0) {
        echo "<div style='background: #c8e6c9; padding: 15px; border-left: 4px solid #4caf50;'>";
        echo "<h4>✅ All Good!</h4>";
        echo "<p>All dockets in this manifest already exist in docket_details table.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ff9800;'>";
        echo "<h4>⚠️ Found $missing_count Missing Dockets!</h4>";
        echo "<p>These dockets are in tbl_manifest_details but NOT in docket_details.</p>";
        echo "</div>";
        
        if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
            // Create the missing dockets
            echo "<h3>Creating Missing Dockets...</h3>";
            
            mysqli_begin_transaction($conn);
            try {
                $insert_query = "INSERT INTO docket_details (
                    doc_no, doc_type, manifest_id, status, created_at, pickup_datetime,
                    company_name, client_name, client_address, item, box, weight, rate, amount, pay_to, eway_bill,
                    office_id, branch_office, service_type
                )
                SELECT 
                    md.doc_no,
                    'NON-DRS',
                    md.manifest_id,
                    'In Transit',
                    m.created_at,
                    m.created_at,
                    'Manual Entry',
                    md.client_name,
                    md.client_address,
                    md.item,
                    md.box,
                    md.weight,
                    md.rate,
                    md.amount,
                    md.pay_to,
                    md.eway_bill,
                    m.office_id,
                    o.office_name,
                    'Manual Manifest Entry'
                FROM tbl_manifest_details md
                INNER JOIN tbl_manifest m ON md.manifest_id = m.manifest_id
                LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                WHERE md.manifest_id = $manifest_id
                AND NOT EXISTS (
                    SELECT 1 FROM docket_details dd WHERE dd.doc_no = md.doc_no
                )";
                
                if (mysqli_query($conn, $insert_query)) {
                    $inserted = mysqli_affected_rows($conn);
                    mysqli_commit($conn);
                    
                    echo "<div style='background: #c8e6c9; padding: 15px; border-left: 4px solid #4caf50;'>";
                    echo "<h4>✅ SUCCESS!</h4>";
                    echo "<p>Created <strong>$inserted</strong> dockets in docket_details table.</p>";
                    echo "<p>All dockets now have office_id = " . $manifest['office_id'] . " (" . htmlspecialchars($manifest['office_name']) . ")</p>";
                    echo "</div>";
                    
                    echo "<hr>";
                    echo "<h3>Verification:</h3>";
                    $verify_query = "SELECT doc_no, office_id, manifest_id, status FROM docket_details WHERE manifest_id = $manifest_id";
                    $verify_result = mysqli_query($conn, $verify_query);
                    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
                    echo "<tr><th>Doc No</th><th>Office ID</th><th>Manifest ID</th><th>Status</th></tr>";
                    while ($row = mysqli_fetch_assoc($verify_result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['doc_no']) . "</td>";
                        echo "<td>" . $row['office_id'] . "</td>";
                        echo "<td>" . $row['manifest_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    
                    echo "<hr>";
                    echo "<p><a href='index.php' style='padding: 10px 20px; background: #2196f3; color: white; text-decoration: none; border-radius: 4px;'>Go to Dashboard</a></p>";
                } else {
                    throw new Exception(mysqli_error($conn));
                }
            } catch (Exception $e) {
                mysqli_rollback($conn);
                echo "<div style='background: #ffcdd2; padding: 15px; border-left: 4px solid #f44336;'>";
                echo "<h4>❌ ERROR!</h4>";
                echo "<p>" . $e->getMessage() . "</p>";
                echo "</div>";
            }
        } else {
            // Show preview and confirm button
            echo "<h3>Dockets to be Created:</h3>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>Doc No</th><th>Client Name</th><th>Item</th><th>Box</th><th>Amount</th></tr>";
            
            mysqli_data_seek($check_result, 0);
            while ($row = mysqli_fetch_assoc($check_result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['doc_no']) . "</td>";
                echo "<td>" . htmlspecialchars($row['client_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['item']) . "</td>";
                echo "<td>" . $row['box'] . "</td>";
                echo "<td>₹" . number_format($row['amount'], 2) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<hr>";
            echo "<div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3;'>";
            echo "<h4>ℹ️ What will happen:</h4>";
            echo "<ul>";
            echo "<li>These $missing_count dockets will be created in <code>docket_details</code> table</li>";
            echo "<li>They will have <code>office_id = " . $manifest['office_id'] . "</code> (Bardhaman)</li>";
            echo "<li>They will have <code>manifest_id = $manifest_id</code></li>";
            echo "<li>Status will be set to <code>In Transit</code></li>";
            echo "<li>Bardhaman office users will be able to see them</li>";
            echo "</ul>";
            echo "</div>";
            
            echo "<hr>";
            echo "<p><a href='?manifest_id=$manifest_id&confirm=yes' style='padding: 15px 30px; background: #4caf50; color: white; text-decoration: none; border-radius: 4px; font-size: 16px; display: inline-block;'>✓ CREATE THESE DOCKETS</a></p>";
            echo "<p><a href='?' style='padding: 10px 20px; background: #666; color: white; text-decoration: none; border-radius: 4px;'>Cancel</a></p>";
        }
    }
    
} else {
    // List all manifests with missing dockets
    echo "<h3>Checking All Manifests...</h3>";
    
    $all_manifests_query = "SELECT m.manifest_id, m.manifest_no, m.office_id, o.office_name, m.created_at,
                            (SELECT COUNT(*) FROM tbl_manifest_details md WHERE md.manifest_id = m.manifest_id) as total_dockets,
                            (SELECT COUNT(*) 
                             FROM tbl_manifest_details md 
                             LEFT JOIN docket_details dd ON md.doc_no = dd.doc_no 
                             WHERE md.manifest_id = m.manifest_id AND dd.doc_no IS NULL) as missing_dockets
                            FROM tbl_manifest m
                            LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                            ORDER BY m.manifest_id DESC";
    $all_result = mysqli_query($conn, $all_manifests_query);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Manifest No</th><th>Destination Office</th><th>Total Dockets</th><th>Missing in docket_details</th><th>Status</th><th>Action</th></tr>";
    
    $problems_found = 0;
    while ($row = mysqli_fetch_assoc($all_result)) {
        $has_problem = $row['missing_dockets'] > 0;
        $style = $has_problem ? 'background-color: #ffcdd2;' : 'background-color: #c8e6c9;';
        if ($has_problem) $problems_found++;
        
        echo "<tr style='$style'>";
        echo "<td>" . htmlspecialchars($row['manifest_no']) . "</td>";
        echo "<td>" . htmlspecialchars($row['office_name']) . " (ID: " . $row['office_id'] . ")</td>";
        echo "<td>" . $row['total_dockets'] . "</td>";
        echo "<td><strong>" . $row['missing_dockets'] . "</strong></td>";
        echo "<td>" . ($has_problem ? '❌ PROBLEM' : '✅ OK') . "</td>";
        echo "<td>";
        if ($has_problem) {
            echo "<a href='?manifest_id=" . $row['manifest_id'] . "' style='padding: 5px 10px; background: #ff9800; color: white; text-decoration: none; border-radius: 3px;'>Fix Now</a>";
        } else {
            echo "-";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    if ($problems_found > 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ff9800;'>";
        echo "<h4>⚠️ Found $problems_found Manifest(s) with Missing Dockets</h4>";
        echo "<p>Click 'Fix Now' to create the missing dockets for each manifest.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #c8e6c9; padding: 15px; border-left: 4px solid #4caf50;'>";
        echo "<h4>✅ All Manifests are OK!</h4>";
        echo "<p>No missing dockets found.</p>";
        echo "</div>";
    }
}

mysqli_close($conn);
?>
