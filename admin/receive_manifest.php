<?php
/**
 * Receive Manifested Dockets
 * Interface for destination office to mark dockets as received
 */

require 'check_auth.php';
requirePermission('manifest_receive', 'docket_view_all');
require 'conn.php';

$action = $_GET['action'] ?? '';
$manifest_id = isset($_GET['manifest_id']) ? intval($_GET['manifest_id']) : 0;

// Handle receive action
if ($action == 'receive' && $manifest_id > 0 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['username'] ?? $_SESSION['full_name'] ?? 'Unknown';
    $received_at = date('Y-m-d H:i:s');
    
    // Get manifest info
    $manifest_query = "SELECT m.manifest_no, o.office_name 
                       FROM tbl_manifest m
                       LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                       WHERE m.manifest_id = $manifest_id";
    $manifest_result = mysqli_query($conn, $manifest_query);
    $manifest_info = mysqli_fetch_assoc($manifest_result);
    $manifest_no = $manifest_info['manifest_no'] ?? '';
    $office_name = $manifest_info['office_name'] ?? 'Destination Office';
    
    // Get dockets from manifest that belong to this office and are "In Transit"
    $office_filter = getOfficeFilter('dd');
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get dockets that will be updated
        $dockets_query = "SELECT dd.docket_id, dd.doc_no 
                         FROM docket_details dd
                         INNER JOIN tbl_manifest_details md ON dd.doc_no = md.doc_no
                         WHERE md.manifest_id = $manifest_id
                         AND dd.status = 'In Transit'
                         $office_filter";
        $dockets_result = mysqli_query($conn, $dockets_query);
        $dockets_to_update = [];
        while ($row = mysqli_fetch_assoc($dockets_result)) {
            $dockets_to_update[] = $row;
        }
        
        // Update docket status
        $update_query = "UPDATE docket_details dd
                         INNER JOIN tbl_manifest_details md ON dd.doc_no = md.doc_no
                         SET dd.status = 'Received at Destination',
                             dd.received_at_destination = '$received_at',
                             dd.received_by_user_id = $user_id,
                             dd.received_by_name = '$user_name',
                             dd.received_notes = '$notes',
                             dd.last_status_update = '$received_at'
                         WHERE md.manifest_id = $manifest_id
                         AND dd.status = 'In Transit'
                         $office_filter";
        
        if (!mysqli_query($conn, $update_query)) {
            throw new Exception('Failed to update docket status: ' . mysqli_error($conn));
        }
        
        $affected = mysqli_affected_rows($conn);
        
        // Add status history entries for each docket
        $history_notes = "Parcel received at $office_name office via Manifest #$manifest_no";
        if (!empty($notes)) {
            $history_notes .= ". Notes: $notes";
        }
        $history_notes .= ". Received by: $user_name";
        
        foreach ($dockets_to_update as $docket) {
            $history_insert = "INSERT INTO docket_status_history 
                              (docket_id, old_status, new_status, changed_by, changed_at, notes, location) 
                              VALUES 
                              ({$docket['docket_id']}, 'In Transit', 'Received at Destination', 
                               $user_id, '$received_at', '$history_notes', '$office_name')";
            
            if (!mysqli_query($conn, $history_insert)) {
                // Don't fail if history insert fails, just log it
                error_log("Failed to insert status history for docket {$docket['doc_no']}: " . mysqli_error($conn));
            }
        }
        
        mysqli_commit($conn);
        $_SESSION['success_message'] = "✅ Marked $affected docket(s) as received!";
        header("Location: ?");
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = $e->getMessage();
    }
}

require 'top_header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <?php require 'left_panel.php';?>
      <?php require 'header_banner.php';?>
      
      <div class="right_col" role="main">
        <div class="page-title">
          <div class="title_left">
            <h3><i class="fa fa-inbox"></i> Receive Manifested Dockets</h3>
          </div>
        </div>
        
        <div class="clearfix"></div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <?= $_SESSION['success_message'] ?>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
          <strong>Error:</strong> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <div class="row">
          <div class="col-md-12">
            <div class="x_panel">
              <div class="x_title">
                <h2><i class="fa fa-truck"></i> Incoming Manifests</h2>
                <div class="clearfix"></div>
              </div>
              
              <div class="x_content">
                <?php
                // Get user's office filter
                $office_filter = getOfficeFilter('m');
                
                // Get manifests sent to this office with "In Transit" dockets
                $manifests_query = "SELECT m.manifest_id, m.manifest_no, m.created_at, m.office_id,
                                    o.office_name,
                                    (SELECT COUNT(*) 
                                     FROM docket_details dd
                                     INNER JOIN tbl_manifest_details md ON dd.doc_no = md.doc_no
                                     WHERE md.manifest_id = m.manifest_id 
                                     AND dd.status = 'In Transit'
                                     " . str_replace('m.', 'dd.', $office_filter) . ") as pending_count,
                                    (SELECT COUNT(*) 
                                     FROM docket_details dd
                                     INNER JOIN tbl_manifest_details md ON dd.doc_no = md.doc_no
                                     WHERE md.manifest_id = m.manifest_id 
                                     AND dd.status = 'Received at Destination'
                                     " . str_replace('m.', 'dd.', $office_filter) . ") as received_count,
                                    (SELECT COUNT(*) 
                                     FROM tbl_manifest_details md 
                                     WHERE md.manifest_id = m.manifest_id) as total_count
                                    FROM tbl_manifest m
                                    LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                                    WHERE 1=1 $office_filter
                                    ORDER BY m.created_at DESC
                                    LIMIT 50";
                
                $manifests_result = mysqli_query($conn, $manifests_query);
                
                if (mysqli_num_rows($manifests_result) == 0): ?>
                  <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> No manifests found for your office.
                  </div>
                <?php else: ?>
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Manifest No</th>
                        <th>Destination Office</th>
                        <th>Created Date</th>
                        <th>Total Dockets</th>
                        <th>Pending</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($manifest = mysqli_fetch_assoc($manifests_result)): 
                        $pending = $manifest['pending_count'];
                        $received = $manifest['received_count'];
                        $total = $manifest['total_count'];
                        
                        if ($pending == 0 && $received > 0) {
                          $status = '<span class="label label-success">All Received</span>';
                        } elseif ($received > 0) {
                          $status = '<span class="label label-warning">Partially Received</span>';
                        } else {
                          $status = '<span class="label label-primary">In Transit</span>';
                        }
                      ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($manifest['manifest_no']) ?></strong></td>
                        <td><?= htmlspecialchars($manifest['office_name']) ?></td>
                        <td><?= date('d M Y, h:i A', strtotime($manifest['created_at'])) ?></td>
                        <td><?= $total ?></td>
                        <td><span class="badge bg-blue"><?= $pending ?></span></td>
                        <td><span class="badge bg-green"><?= $received ?></span></td>
                        <td><?= $status ?></td>
                        <td>
                          <?php if ($pending > 0): ?>
                            <a href="?action=view&manifest_id=<?= $manifest['manifest_id'] ?>" 
                               class="btn btn-primary btn-sm">
                              <i class="fa fa-check-circle"></i> Mark as Received
                            </a>
                          <?php else: ?>
                            <a href="manifest_dockets_list.php?manifest_id=<?= $manifest['manifest_id'] ?>" 
                               class="btn btn-default btn-sm">
                              <i class="fa fa-eye"></i> View
                            </a>
                          <?php endif; ?>
                        </td>
                      </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        
        <?php if ($action == 'view' && $manifest_id > 0): 
          // Get manifest details
          $manifest_query = "SELECT m.*, o.office_name 
                            FROM tbl_manifest m
                            LEFT JOIN tbl_offices o ON m.office_id = o.office_id
                            WHERE m.manifest_id = $manifest_id";
          $manifest_result = mysqli_query($conn, $manifest_query);
          $manifest = mysqli_fetch_assoc($manifest_result);
          
          if ($manifest):
            // Get pending dockets
            $office_filter_dd = getOfficeFilter('dd');
            $dockets_query = "SELECT dd.*, md.box, md.weight, md.amount
                             FROM docket_details dd
                             INNER JOIN tbl_manifest_details md ON dd.doc_no = md.doc_no
                             WHERE md.manifest_id = $manifest_id
                             AND dd.status = 'In Transit'
                             $office_filter_dd
                             ORDER BY dd.doc_no";
            $dockets_result = mysqli_query($conn, $dockets_query);
            $pending_count = mysqli_num_rows($dockets_result);
        ?>
        
        <div class="row">
          <div class="col-md-12">
            <div class="x_panel">
              <div class="x_title">
                <h2><i class="fa fa-clipboard"></i> Receive Manifest: <?= htmlspecialchars($manifest['manifest_no']) ?></h2>
                <div class="clearfix"></div>
              </div>
              
              <div class="x_content">
                <div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #2196f3; margin-bottom: 20px;">
                  <h4>Manifest Information</h4>
                  <p><strong>Destination Office:</strong> <?= htmlspecialchars($manifest['office_name']) ?></p>
                  <p><strong>Created:</strong> <?= date('d M Y, h:i A', strtotime($manifest['created_at'])) ?></p>
                  <p><strong>Pending Dockets:</strong> <?= $pending_count ?></p>
                </div>
                
                <?php if ($pending_count == 0): ?>
                  <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i> All dockets from this manifest have been received!
                  </div>
                  <a href="?" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to List</a>
                <?php else: ?>
                  
                  <h4>Dockets to Receive:</h4>
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Doc No</th>
                        <th>Client Name</th>
                        <th>Item</th>
                        <th>Box</th>
                        <th>Weight</th>
                        <th>Amount</th>
                        <th>Current Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while ($docket = mysqli_fetch_assoc($dockets_result)): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($docket['doc_no']) ?></strong></td>
                        <td><?= htmlspecialchars($docket['client_name']) ?></td>
                        <td><?= htmlspecialchars($docket['item']) ?></td>
                        <td><?= $docket['box'] ?></td>
                        <td><?= $docket['weight'] ?> kg</td>
                        <td>₹<?= number_format($docket['amount'], 2) ?></td>
                        <td><span class="label label-primary"><?= htmlspecialchars($docket['status']) ?></span></td>
                      </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                  
                  <form method="POST" action="?action=receive&manifest_id=<?= $manifest_id ?>" onsubmit="return confirm('Mark all <?= $pending_count ?> docket(s) as received?');">
                    <div class="form-group">
                      <label>Notes (Optional)</label>
                      <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes about receiving this manifest..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg">
                      <i class="fa fa-check-circle"></i> Mark All as Received (<?= $pending_count ?> dockets)
                    </button>
                    <a href="?" class="btn btn-default btn-lg">
                      <i class="fa fa-times"></i> Cancel
                    </a>
                  </form>
                  
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        
        <?php 
          endif;
        endif; ?>
        
      </div>
      
      <?php require 'footer.php'; ?>
    </div>
  </div>
</body>
</html>
