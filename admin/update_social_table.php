<?php
/**
 * Database Update Script for Social Links Table
 * This script adds missing social media columns to tbl_social
 */

require 'conn.php';

echo "<h2>Social Media Table Update Script</h2>";
echo "<hr>";

// Check if table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_social'");

if (mysqli_num_rows($table_check) == 0) {
    echo "<p style='color: orange;'>Table 'tbl_social' does not exist. Creating table...</p>";

    $create_table = "CREATE TABLE `tbl_social` (
        `social_id` int(11) NOT NULL AUTO_INCREMENT,
        `social_facebook` varchar(255) DEFAULT NULL,
        `social_twitter` varchar(255) DEFAULT NULL,
        `social_linkedin` varchar(255) DEFAULT NULL,
        `social_instagram` varchar(255) DEFAULT NULL,
        `social_youtube` varchar(255) DEFAULT NULL,
        `social_whatsapp` varchar(255) DEFAULT NULL,
        `social_pinterest` varchar(255) DEFAULT NULL,
        `social_tiktok` varchar(255) DEFAULT NULL,
        `social_telegram` varchar(255) DEFAULT NULL,
        `social_snapchat` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`social_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (mysqli_query($conn, $create_table)) {
        echo "<p style='color: green;'>✓ Table created successfully!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating table: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ Table 'tbl_social' exists</p>";
}

// Array of columns that should exist
$required_columns = [
    'social_facebook' => 'varchar(255)',
    'social_twitter' => 'varchar(255)',
    'social_linkedin' => 'varchar(255)',
    'social_instagram' => 'varchar(255)',
    'social_youtube' => 'varchar(255)',
    'social_whatsapp' => 'varchar(255)',
    'social_pinterest' => 'varchar(255)',
    'social_tiktok' => 'varchar(255)',
    'social_telegram' => 'varchar(255)',
    'social_snapchat' => 'varchar(255)'
];

echo "<br><h3>Checking columns...</h3>";

// Get existing columns
$columns_query = mysqli_query($conn, "SHOW COLUMNS FROM tbl_social");
$existing_columns = [];

while ($row = mysqli_fetch_assoc($columns_query)) {
    $existing_columns[] = $row['Field'];
}

// Add missing columns
foreach ($required_columns as $column_name => $column_type) {
    if (!in_array($column_name, $existing_columns)) {
        echo "<p style='color: orange;'>Adding column: $column_name...</p>";

        $alter_query = "ALTER TABLE `tbl_social` ADD `$column_name` $column_type DEFAULT NULL";

        if (mysqli_query($conn, $alter_query)) {
            echo "<p style='color: green;'>✓ Column '$column_name' added successfully!</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding column '$column_name': " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: #666;'>✓ Column '$column_name' already exists</p>";
    }
}

echo "<br><hr>";
echo "<h3 style='color: green;'>Database update completed!</h3>";
echo "<p><a href='social_links_modern.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Go to Social Links Manager</a></p>";

mysqli_close($conn);
?>
