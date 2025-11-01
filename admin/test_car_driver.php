<?php
require 'conn.php';

echo "<h2>Testing Car & Driver Queries</h2>";
echo "<style>body{font-family:Arial;padding:20px;} h3{color:#333;border-bottom:2px solid #007bff;padding-bottom:5px;} .error{color:red;} .success{color:green;}</style>";

// Test 1: Check if tables exist
echo "<h3>1. Table Structure Check</h3>";
$tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_car'");
if (mysqli_num_rows($tables_check) > 0) {
    echo "<span class='success'>✓ tbl_car exists</span><br>";
    
    // Show columns
    $columns = mysqli_query($conn, "SHOW COLUMNS FROM tbl_car");
    echo "<strong>Columns in tbl_car:</strong><br>";
    while($col = mysqli_fetch_assoc($columns)) {
        echo "- {$col['Field']} ({$col['Type']})<br>";
    }
} else {
    echo "<span class='error'>✗ tbl_car does NOT exist</span><br>";
}

$tables_check2 = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_driver'");
if (mysqli_num_rows($tables_check2) > 0) {
    echo "<span class='success'>✓ tbl_driver exists</span><br>";
    
    // Show columns
    $columns = mysqli_query($conn, "SHOW COLUMNS FROM tbl_driver");
    echo "<strong>Columns in tbl_driver:</strong><br>";
    while($col = mysqli_fetch_assoc($columns)) {
        echo "- {$col['Field']} ({$col['Type']})<br>";
    }
} else {
    echo "<span class='error'>✗ tbl_driver does NOT exist</span><br>";
}

echo "<hr>";

// Test 2: All Cars
echo "<h3>2. All Cars (no filter):</h3>";
$cars_all = mysqli_query($conn, "SELECT car_id, car_number, active_status FROM tbl_car ORDER BY car_number ASC");
if (!$cars_all) {
    echo "<span class='error'>ERROR: " . mysqli_error($conn) . "</span><br>";
} else {
    $count = mysqli_num_rows($cars_all);
    echo "<span class='success'>Found {$count} total cars:</span><br>";
    if ($count > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
        echo "<tr><th>Car ID</th><th>Car Number</th><th>Active Status</th></tr>";
        while($car = mysqli_fetch_assoc($cars_all)) {
            $status_color = $car['active_status'] == 1 ? 'green' : 'red';
            echo "<tr><td>{$car['car_id']}</td><td><strong>{$car['car_number']}</strong></td><td style='color:{$status_color};'>{$car['active_status']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<span class='error'>⚠️ No cars found in database!</span><br>";
    }
}

echo "<hr>";

// Test 3: Active Cars Only
echo "<h3>3. Active Cars (active_status=1):</h3>";
$cars_active = mysqli_query($conn, "SELECT car_id, car_number FROM tbl_car WHERE active_status=1 ORDER BY car_number ASC");
if (!$cars_active) {
    echo "<span class='error'>ERROR: " . mysqli_error($conn) . "</span><br>";
} else {
    $count = mysqli_num_rows($cars_active);
    if ($count > 0) {
        echo "<span class='success'>✓ Found {$count} active cars - These will appear in dropdown:</span><br>";
        while($car = mysqli_fetch_assoc($cars_active)) {
            echo "- <strong>{$car['car_number']}</strong> (ID: {$car['car_id']})<br>";
        }
    } else {
        echo "<span class='error'>⚠️ No ACTIVE cars found! All cars have active_status=0 or NULL</span><br>";
    }
}

echo "<hr>";

// Test 4: All Drivers
echo "<h3>4. All Drivers (no filter):</h3>";
$drivers_all = mysqli_query($conn, "SELECT driver_id, driver_name, active_status FROM tbl_driver ORDER BY driver_name ASC");
if (!$drivers_all) {
    echo "<span class='error'>ERROR: " . mysqli_error($conn) . "</span><br>";
} else {
    $count = mysqli_num_rows($drivers_all);
    echo "<span class='success'>Found {$count} total drivers:</span><br>";
    if ($count > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
        echo "<tr><th>Driver ID</th><th>Driver Name</th><th>Active Status</th></tr>";
        while($driver = mysqli_fetch_assoc($drivers_all)) {
            $status_color = $driver['active_status'] == 1 ? 'green' : 'red';
            echo "<tr><td>{$driver['driver_id']}</td><td><strong>{$driver['driver_name']}</strong></td><td style='color:{$status_color};'>{$driver['active_status']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<span class='error'>⚠️ No drivers found in database!</span><br>";
    }
}

echo "<hr>";

// Test 5: Active Drivers Only
echo "<h3>5. Active Drivers (active_status=1):</h3>";
$drivers_active = mysqli_query($conn, "SELECT driver_id, driver_name FROM tbl_driver WHERE active_status=1 ORDER BY driver_name ASC");
if (!$drivers_active) {
    echo "<span class='error'>ERROR: " . mysqli_error($conn) . "</span><br>";
} else {
    $count = mysqli_num_rows($drivers_active);
    if ($count > 0) {
        echo "<span class='success'>✓ Found {$count} active drivers - These will appear in dropdown:</span><br>";
        while($driver = mysqli_fetch_assoc($drivers_active)) {
            echo "- <strong>{$driver['driver_name']}</strong> (ID: {$driver['driver_id']})<br>";
        }
    } else {
        echo "<span class='error'>⚠️ No ACTIVE drivers found! All drivers have active_status=0 or NULL</span><br>";
    }
}

echo "<hr>";
echo "<h3>Recommendation:</h3>";
echo "<p>If no active cars/drivers are showing above, you need to:</p>";
echo "<ol>";
echo "<li>Add new cars/drivers to the database, OR</li>";
echo "<li>Update existing records to set active_status=1</li>";
echo "</ol>";
echo "<p><strong>Quick Fix Query:</strong></p>";
echo "<code style='background:#f0f0f0;padding:10px;display:block;'>UPDATE tbl_car SET active_status=1;<br>UPDATE tbl_driver SET active_status=1;</code>";
?>
