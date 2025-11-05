<!DOCTYPE html>
<html>
<head>
    <title>Filter Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #3498db; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .info { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Docket Filter Test Page</h1>
        
        <?php
        include('conn.php');
        
        // Get parameters from URL
        $fromdate = $_GET['fromdate'] ?? '2025-11-03';
        $todate = $_GET['todate'] ?? '2025-11-05';
        $status = $_GET['status'] ?? '';
        
        echo "<div class='info'>";
        echo "<h3>Filter Parameters:</h3>";
        echo "<strong>From Date:</strong> $fromdate<br>";
        echo "<strong>To Date:</strong> $todate<br>";
        echo "<strong>Status:</strong> " . ($status ?: 'All') . "<br>";
        echo "</div>";
        
        // Build WHERE clause - Same as list_register.php
        $where = [];
        
        if (!empty($fromdate) && !empty($todate)) {
            $fromDateTime = mysqli_real_escape_string($conn, $fromdate) . ' 00:00:00';
            $toDateTime = mysqli_real_escape_string($conn, $todate) . ' 23:59:59';
            $where[] = "(dd.pickup_datetime >= '$fromDateTime' AND dd.pickup_datetime <= '$toDateTime')";
        }
        
        if (!empty($status)) {
            $where[] = "dd.status='".mysqli_real_escape_string($conn, $status)."'";
        }
        
        $whereSQL = (count($where) > 0) ? ("WHERE " . implode(" AND ", $where)) : "";
        
        // Execute same query as list_register.php
        $sql = "SELECT dd.*, 
                       o.office_name as branch_office_name
                FROM docket_details dd
                LEFT JOIN tbl_offices o ON dd.office_id = o.office_id
                $whereSQL
                ORDER BY dd.docket_id DESC";
        
        echo "<h3>SQL Query:</h3>";
        echo "<pre>" . htmlspecialchars($sql) . "</pre>";
        
        $result = mysqli_query($conn, $sql);
        
        if(!$result) {
            echo "<p class='error'>❌ SQL ERROR: " . mysqli_error($conn) . "</p>";
        } else {
            $totalRecords = mysqli_num_rows($result);
            echo "<p class='success'>✅ Query executed successfully!</p>";
            echo "<p><strong>Total Records Found:</strong> " . $totalRecords . "</p>";
            
            if($totalRecords > 0) {
                echo "<table>";
                echo "<tr>";
                echo "<th>#</th>";
                echo "<th>Doc No</th>";
                echo "<th>Pickup DateTime</th>";
                echo "<th>Company</th>";
                echo "<th>Client</th>";
                echo "<th>Status</th>";
                echo "<th>Office</th>";
                echo "</tr>";
                
                $sl = 1;
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $sl++ . "</td>";
                    echo "<td><strong>" . htmlspecialchars($row['doc_no']) . "</strong></td>";
                    echo "<td>" . htmlspecialchars($row['pickup_datetime']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['company_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['client_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['branch_office_name'] ?? 'N/A') . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<div class='info'>";
                echo "<h3>📭 No Records Found</h3>";
                echo "<p>The query executed successfully but returned no results with the current filters.</p>";
                echo "</div>";
            }
        }
        ?>
        
        <div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
            <h3>🔗 Test Different Filters:</h3>
            <p><a href="?fromdate=2025-11-03&todate=2025-11-03">Nov 3 to Nov 3</a></p>
            <p><a href="?fromdate=2025-11-03&todate=2025-11-05">Nov 3 to Nov 5</a></p>
            <p><a href="?fromdate=2025-11-04&todate=2025-11-04">Nov 4 to Nov 4</a></p>
            <p><a href="?">All Records (No Filter)</a></p>
        </div>
    </div>
</body>
</html>
