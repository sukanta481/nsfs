<?php
require 'conn.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 6098;

$result = mysqli_query($conn, "SELECT shipping_details_id, doc_no, status FROM tbl_shipping_details WHERE shipping_details_id = $id");

if ($row = mysqli_fetch_assoc($result)) {
    echo "<h2>Docket Status Check</h2>";
    echo "<p><strong>ID:</strong> " . $row['shipping_details_id'] . "</p>";
    echo "<p><strong>Doc No:</strong> " . $row['doc_no'] . "</p>";
    echo "<p><strong>Status:</strong> " . $row['status'] . "</p>";
    echo "<p><a href='trip.php?type=view_trip&id=$id'>View Docket</a></p>";
} else {
    echo "Docket not found!";
}
?>
