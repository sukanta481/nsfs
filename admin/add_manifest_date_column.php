<?php
/**
 * Add manifest_date column to tbl_manifest
 * Run this script ONCE to add the custom date field
 * 
 * This allows users to specify a manifest date (current or previous)
 * instead of always using the created_at timestamp
 */

require_once 'conn.php';

echo "<h2>Adding manifest_date Column to tbl_manifest</h2>";

// Check if column already exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM tbl_manifest LIKE 'manifest_date'");
if (mysqli_num_rows($check_column) > 0) {
    echo "<p style='color: green;'>✅ Column 'manifest_date' already exists. No changes needed.</p>";
} else {
    // Add the column
    $sql = "ALTER TABLE tbl_manifest ADD COLUMN manifest_date DATE NULL AFTER driver_id";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Successfully added 'manifest_date' column to tbl_manifest</p>";
        
        // Update existing records to use the date portion of created_at
        $update_sql = "UPDATE tbl_manifest SET manifest_date = DATE(created_at) WHERE manifest_date IS NULL";
        if (mysqli_query($conn, $update_sql)) {
            $affected = mysqli_affected_rows($conn);
            echo "<p style='color: green;'>✅ Updated $affected existing records with manifest_date from created_at</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Could not update existing records: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error adding column: " . mysqli_error($conn) . "</p>";
    }
}

// Display current table structure
echo "<h3>Current tbl_manifest Structure:</h3>";
$result = mysqli_query($conn, "DESCRIBE tbl_manifest");
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    $highlight = ($row['Field'] === 'manifest_date') ? "style='background: #d4edda;'" : "";
    echo "<tr $highlight>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Sample Data (First 5 Records):</h3>";
$sample = mysqli_query($conn, "SELECT manifest_id, manifest_no, manifest_date, created_at FROM tbl_manifest ORDER BY manifest_id DESC LIMIT 5");
if ($sample && mysqli_num_rows($sample) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Manifest No</th><th>Manifest Date</th><th>Created At</th></tr>";
    while ($row = mysqli_fetch_assoc($sample)) {
        echo "<tr>";
        echo "<td>{$row['manifest_id']}</td>";
        echo "<td>{$row['manifest_no']}</td>";
        echo "<td>{$row['manifest_date']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No manifest records found.</p>";
}

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>The manifest creation form now has a date picker for selecting manifest date</li>";
echo "<li>Users can select today's date or any previous date</li>";
echo "<li>The manifest list will show the manifest_date instead of created_at</li>";
echo "</ol>";
echo "<p><a href='manifest_new_entry.php'>Go to Create Manifest</a> | <a href='list_manifest.php'>View Manifests</a></p>";
?>
