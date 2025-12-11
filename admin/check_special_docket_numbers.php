<?php
// Check and optionally reset special docket numbering to SP 3456050
require 'conn.php';

echo "=== Special Docket Number Check ===\n\n";

// Check existing special dockets
$query = "SELECT doc_no FROM docket_details WHERE doc_no LIKE 'SP %' ORDER BY docket_id DESC LIMIT 10";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "Existing special dockets found:\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "  - " . $row['doc_no'] . "\n";
    }
    echo "\n";
    
    // Get the last one
    mysqli_data_seek($result, 0);
    $last = mysqli_fetch_assoc($result);
    $lastNo = str_replace('SP ', '', $last['doc_no']);
    $nextNo = intval($lastNo) + 1;
    
    echo "Current next number would be: SP $nextNo\n";
    echo "Desired starting number: SP 3456050\n\n";
    
    if ($nextNo < 3456050) {
        echo "⚠️  The next docket number ($nextNo) is less than desired (3456050).\n";
        echo "The system will continue from the existing series unless you manually update.\n\n";
        echo "Options:\n";
        echo "1. Delete existing SP dockets if they were test data\n";
        echo "2. Manually insert a dummy docket with number SP 3456049\n";
        echo "3. Accept current numbering and continue from SP $nextNo\n";
    } else {
        echo "✅ Numbering is already at or above SP 3456050\n";
    }
} else {
    echo "No special dockets found in database.\n";
    echo "Next docket will start from SP 3456050 (as configured in code).\n";
}

mysqli_close($conn);
?>
