<?php
/**
 * Setup Manifest Tables
 * Run this once to ensure manifest tables exist with correct structure
 */

require 'conn.php';

echo "<h2>Setting up Manifest Tables...</h2>";

// Create tbl_manifest
$create_manifest = "CREATE TABLE IF NOT EXISTS `tbl_manifest` (
  `manifest_id` int(11) NOT NULL AUTO_INCREMENT,
  `manifest_no` varchar(60) NOT NULL,
  `office_id` int(11) NOT NULL,
  `car_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `total_gross` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_pay_to` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`manifest_id`),
  UNIQUE KEY `manifest_no` (`manifest_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if (mysqli_query($conn, $create_manifest)) {
    echo "<p style='color:green;'>✓ tbl_manifest table created/verified</p>";
} else {
    echo "<p style='color:red;'>✗ Error creating tbl_manifest: " . mysqli_error($conn) . "</p>";
}

// Create tbl_manifest_details
$create_details = "CREATE TABLE IF NOT EXISTS `tbl_manifest_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manifest_id` int(11) NOT NULL,
  `doc_no` varchar(255) NOT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `item` varchar(255) DEFAULT NULL,
  `client_address` text,
  `box` int(11) DEFAULT 0,
  `weight` decimal(10,2) DEFAULT 0.00,
  `rate` decimal(12,2) DEFAULT 0.00,
  `amount` decimal(12,2) DEFAULT 0.00,
  `eway_bill` varchar(255) DEFAULT NULL,
  `pay_to` decimal(12,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `manifest_id_idx` (`manifest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if (mysqli_query($conn, $create_details)) {
    echo "<p style='color:green;'>✓ tbl_manifest_details table created/verified</p>";
} else {
    echo "<p style='color:red;'>✗ Error creating tbl_manifest_details: " . mysqli_error($conn) . "</p>";
}

echo "<h3>Setup Complete!</h3>";
echo "<p><a href='manifest.php'>Go to Manifest Management</a></p>";
?>
