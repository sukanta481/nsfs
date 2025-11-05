<?php
require 'conn.php';

// Check if permission already exists
$check = mysqli_query($conn, "SELECT * FROM tbl_permissions WHERE permission_key='docket_status_update'");

if (mysqli_num_rows($check) > 0) {
    echo "✅ Permission 'docket_status_update' already exists in the database.<br>";
} else {
    // Add the permission
    $insert = "INSERT INTO tbl_permissions (permission_key, permission_name, module_name, permission_description) 
               VALUES ('docket_status_update', 'Update Docket Status', 'Dockets', 'Access to update delivery status page for dockets')";
    
    if (mysqli_query($conn, $insert)) {
        echo "✅ Permission 'docket_status_update' has been added successfully!<br>";
    } else {
        echo "❌ Error adding permission: " . mysqli_error($conn) . "<br>";
    }
}

// Show all docket-related permissions
echo "<br><h3>All Docket Permissions:</h3>";
$result = mysqli_query($conn, "SELECT * FROM tbl_permissions WHERE module_name='Dockets' ORDER BY permission_name");

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Permission Key</th><th>Permission Name</th><th>Description</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($row['permission_key']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['permission_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['permission_description']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No docket permissions found.</p>";
}

mysqli_close($conn);
?>

<br><br>
<a href="roles.php" style="display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">
    Go to Roles Management
</a>
<a href="delivery_status.php" style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">
    Go to Update Status Page
</a>
