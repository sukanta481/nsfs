<?php
/**
 * Setup Manifest Receive Feature
 * Adds ability for destination office to mark manifested dockets as "Received"
 */

require 'conn.php';

echo "<h2>Setup: Manifest Receive Feature</h2>";
echo "<hr>";

$steps_completed = 0;
$total_steps = 4;

// Step 1: Add "Received at Destination" status to enum (if not exists)
echo "<h3>Step 1: Check/Update Status Column</h3>";
$check_status = mysqli_query($conn, "SHOW COLUMNS FROM docket_details WHERE Field = 'status'");
$status_col = mysqli_fetch_assoc($check_status);
$status_type = $status_col['Type'] ?? '';

echo "<p>Current status enum: <code>" . htmlspecialchars($status_type) . "</code></p>";

if (empty($status_type)) {
    echo "<p style='color: red;'>❌ Error: Could not find status column</p>";
} elseif (strpos($status_type, 'Received at Destination') === false) {
    echo "<p>Adding 'Received at Destination' status...</p>";
    
    // Check if status is enum or varchar
    if (strpos($status_type, 'enum') === 0) {
        // Get current enum values
        preg_match("/^enum\((.*)\)$/", $status_type, $matches);
        if (isset($matches[1])) {
            $enum_string = str_replace("'", "", $matches[1]);
            $enum_values = explode(",", $enum_string);
            
            // Add new value after "In Transit"
            $key = array_search('In Transit', $enum_values);
            if ($key !== false) {
                array_splice($enum_values, $key + 1, 0, 'Received at Destination');
            } else {
                $enum_values[] = 'Received at Destination';
            }
            
            $new_enum = "enum('" . implode("','", $enum_values) . "')";
            
            $alter_query = "ALTER TABLE docket_details MODIFY COLUMN status $new_enum DEFAULT 'Pending'";
            
            if (mysqli_query($conn, $alter_query)) {
                echo "<p style='color: green;'>✅ Added 'Received at Destination' status</p>";
                $steps_completed++;
            } else {
                echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Error: Could not parse enum values</p>";
        }
    } else {
        // VARCHAR column - just note that the value can be used
        echo "<p style='color: green;'>✅ Status column is VARCHAR - 'Received at Destination' can be used</p>";
        $steps_completed++;
    }
} else {
    echo "<p style='color: green;'>✅ Status already exists</p>";
    $steps_completed++;
}

// Step 2: Add received tracking columns
echo "<h3>Step 2: Add Tracking Columns</h3>";

$columns_to_add = [
    ['name' => 'received_at_destination', 'type' => 'DATETIME NULL', 'after' => 'delivery_datetime'],
    ['name' => 'received_by_user_id', 'type' => 'INT(11) NULL', 'after' => 'received_at_destination'],
    ['name' => 'received_by_name', 'type' => 'VARCHAR(255) NULL', 'after' => 'received_by_user_id'],
    ['name' => 'received_notes', 'type' => 'TEXT NULL', 'after' => 'received_by_name']
];

foreach ($columns_to_add as $col) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM docket_details WHERE Field = '{$col['name']}'");
    if (mysqli_num_rows($check) == 0) {
        $add_col = "ALTER TABLE docket_details ADD COLUMN {$col['name']} {$col['type']} AFTER {$col['after']}";
        if (mysqli_query($conn, $add_col)) {
            echo "<p style='color: green;'>✅ Added column: {$col['name']}</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding {$col['name']}: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Column already exists: {$col['name']}</p>";
    }
}
$steps_completed++;

// Step 3: Create permission for receiving manifests
echo "<h3>Step 3: Create Permission</h3>";

// Check if tbl_permissions table exists and has required columns
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_permissions'");
if (mysqli_num_rows($check_table) == 0) {
    echo "<p style='color: red;'>❌ Error: tbl_permissions table doesn't exist</p>";
} else {
    // Check if description column exists
    $check_desc = mysqli_query($conn, "SHOW COLUMNS FROM tbl_permissions LIKE 'description'");
    $has_description = mysqli_num_rows($check_desc) > 0;
    
    $check_perm = mysqli_query($conn, "SELECT permission_id FROM tbl_permissions WHERE permission_key = 'manifest_receive'");
    if (mysqli_num_rows($check_perm) == 0) {
        if ($has_description) {
            $insert_perm = "INSERT INTO tbl_permissions (permission_key, permission_name, description) 
                            VALUES ('manifest_receive', 'Receive Manifested Dockets', 'Can mark manifested dockets as received at destination office')";
        } else {
            $insert_perm = "INSERT INTO tbl_permissions (permission_key, permission_name) 
                            VALUES ('manifest_receive', 'Receive Manifested Dockets')";
        }
        
        if (mysqli_query($conn, $insert_perm)) {
            echo "<p style='color: green;'>✅ Created 'manifest_receive' permission</p>";
            $steps_completed++;
        } else {
            echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Permission already exists</p>";
        $steps_completed++;
    }
}

// Step 4: Show summary
echo "<h3>Step 4: Setup Summary</h3>";
echo "<div style='background: " . ($steps_completed == $total_steps ? '#c8e6c9' : '#fff3cd') . "; padding: 15px; border-left: 4px solid " . ($steps_completed == $total_steps ? '#4caf50' : '#ff9800') . ";'>";
echo "<h4>" . ($steps_completed == $total_steps ? '✅ Setup Complete!' : '⚠️ Partial Setup') . "</h4>";
echo "<p>Completed $steps_completed of $total_steps steps</p>";

if ($steps_completed == $total_steps) {
    echo "<h4>Next Steps:</h4>";
    echo "<ol>";
    echo "<li>Run <code>create_receive_manifest_page.php</code> to create the receive interface</li>";
    echo "<li>Assign 'manifest_receive' permission to office roles</li>";
    echo "<li>Test by manifesting dockets to an office and having them mark as received</li>";
    echo "</ol>";
}
echo "</div>";

echo "<hr>";
echo "<h3>How It Works:</h3>";
echo "<div style='background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3;'>";
echo "<ol>";
echo "<li><strong>Barasat creates manifest</strong> → Dockets status = 'In Transit', office_id = Bardhaman</li>";
echo "<li><strong>Bardhaman sees the dockets</strong> on their dashboard (with 'In Transit' status)</li>";
echo "<li><strong>Bardhaman clicks 'Mark as Received'</strong> → Status changes to 'Received at Destination'</li>";
echo "<li><strong>System records:</strong> Received date/time, who received it, and any notes</li>";
echo "<li><strong>After received:</strong> Bardhaman can then update to 'Out for Delivery', 'Delivered', etc.</li>";
echo "</ol>";
echo "</div>";

mysqli_close($conn);
?>
