<?php
require 'conn.php';

echo "<h2>Testing Car & Driver Queries</h2>";

echo "<h3>Cars (active_status=1):</h3>";
$cars = mysqli_query($conn, "SELECT car_id, car_number FROM tbl_car WHERE active_status=1 ORDER BY car_number ASC");
if (!$cars) {
    echo "ERROR: " . mysqli_error($conn) . "<br>";
} else {
    echo "Found " . mysqli_num_rows($cars) . " active cars:<br>";
    while($car = mysqli_fetch_assoc($cars)) {
        echo "- Car ID: {$car['car_id']}, Number: {$car['car_number']}<br>";
    }
}

echo "<hr>";

echo "<h3>Drivers (active_status=1):</h3>";
$drivers = mysqli_query($conn, "SELECT driver_id, driver_name FROM tbl_driver WHERE active_status=1 ORDER BY driver_name ASC");
if (!$drivers) {
    echo "ERROR: " . mysqli_error($conn) . "<br>";
} else {
    echo "Found " . mysqli_num_rows($drivers) . " active drivers:<br>";
    while($driver = mysqli_fetch_assoc($drivers)) {
        echo "- Driver ID: {$driver['driver_id']}, Name: {$driver['driver_name']}<br>";
    }
}

echo "<hr>";

echo "<h3>All Cars (no filter):</h3>";
$cars_all = mysqli_query($conn, "SELECT car_id, car_number, active_status FROM tbl_car ORDER BY car_number ASC");
if (!$cars_all) {
    echo "ERROR: " . mysqli_error($conn) . "<br>";
} else {
    echo "Found " . mysqli_num_rows($cars_all) . " total cars:<br>";
    while($car = mysqli_fetch_assoc($cars_all)) {
        echo "- Car ID: {$car['car_id']}, Number: {$car['car_number']}, Active: {$car['active_status']}<br>";
    }
}

echo "<hr>";

echo "<h3>All Drivers (no filter):</h3>";
$drivers_all = mysqli_query($conn, "SELECT driver_id, driver_name, active_status FROM tbl_driver ORDER BY driver_name ASC");
if (!$drivers_all) {
    echo "ERROR: " . mysqli_error($conn) . "<br>";
} else {
    echo "Found " . mysqli_num_rows($drivers_all) . " total drivers:<br>";
    while($driver = mysqli_fetch_assoc($drivers_all)) {
        echo "- Driver ID: {$driver['driver_id']}, Name: {$driver['driver_name']}, Active: {$driver['active_status']}<br>";
    }
}
?>
