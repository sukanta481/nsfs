<?php
require_once 'conn.php';

echo "<h2>tbl_roles Table Structure</h2>";
$result = mysqli_query($conn, "DESCRIBE tbl_roles");
if (!$result) {
    die("Error: " . mysqli_error($conn));
}
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Key</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table>";

echo "<h2>tbl_users Table Structure</h2>";
$result2 = mysqli_query($conn, "DESCRIBE tbl_users");
if (!$result2) {
    die("Error: " . mysqli_error($conn));
}
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Key</th></tr>";
while($row = mysqli_fetch_assoc($result2)) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table>";

echo "<h2>Sample Roles Data</h2>";
$roles = mysqli_query($conn, "SELECT * FROM tbl_roles");
if (!$roles) {
    die("Error: " . mysqli_error($conn));
}
echo "<pre>";
while($r = mysqli_fetch_assoc($roles)) {
    print_r($r);
}
echo "</pre>";
?>
