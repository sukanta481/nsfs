<?php
include 'conn.php';

echo "=== Tables with 'client' ===\n";
$r = $conn->query("SHOW TABLES LIKE '%client%'");
if ($r) {
    while($row = $r->fetch_row()) {
        echo $row[0] . "\n";
    }
}

echo "\n=== DISTINCT company_name from docket_details ===\n";
$r = $conn->query("SELECT DISTINCT company_name FROM docket_details LIMIT 5");
if ($r) {
    while($row = $r->fetch_assoc()) {
        echo $row['company_name'] . "\n";
    }
}

echo "\n=== DISTINCT client_name from docket_details ===\n";
$r = $conn->query("SELECT DISTINCT client_name FROM docket_details LIMIT 5");
if ($r) {
    while($row = $r->fetch_assoc()) {
        echo $row['client_name'] . "\n";
    }
}
