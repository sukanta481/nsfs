<?php
// Detect AJAX
$is_ajax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ||
    (isset($_POST['ajax']) && $_POST['ajax'] == '1')
);

// Only include header/footer if not AJAX
if (!$is_ajax) include("include/header.php");

$doc_no = isset($_GET['doc_no']) ? mysqli_real_escape_string($conn, trim($_GET['doc_no'])) : '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['doc_no'])) {
    $doc_no = mysqli_real_escape_string($conn, trim($_POST['doc_no']));
}
$get_shipping_details_row = false;
if ($doc_no) {
    $get_shipping_details_sql = "SELECT * FROM tbl_shipping_details WHERE doc='" . $doc_no . "'";
    $get_shipping_details_rs = mysqli_query($conn, $get_shipping_details_sql);
    $get_shipping_details_row = mysqli_fetch_assoc($get_shipping_details_rs);
}
if ($get_shipping_details_row) {
    $sid = $get_shipping_details_row['shipping_details_id'];
    $status_sql = "SELECT * FROM tbl_trip_status WHERE ship_id='$sid' ORDER BY updateddate ASC";
    $status_rs = mysqli_query($conn, $status_sql);
    $timeline = [
        ['label' => 'Shipment Created',   'db_status' => 'Created',      'desc' => '', 'time' => null],
        ['label' => 'Picked Up',          'db_status' => 'Picked Up',    'desc' => $get_shipping_details_row['client_address'], 'time' => null],
        ['label' => 'In Transit',         'db_status' => 'In Transit',   'desc' => '', 'time' => null],
        ['label' => 'Out for Delivery',   'db_status' => 'Out for Delivery', 'desc' => '', 'time' => null],
        ['label' => 'Delivered',          'db_status' => 'Delivered',    'desc' => '', 'time' => null],
    ];
    $history = [];
    while ($row = mysqli_fetch_assoc($status_rs)) $history[] = $row;
    foreach ($timeline as $k => $step) {
        foreach ($history as $h) {
            if (strcasecmp($h['status'], $step['db_status']) == 0) {
                $timeline[$k]['time'] = date('d M Y, h:i A', strtotime($h['updateddate']));
            }
        }
    }
    $car_no = $delivery_agent = $contact_number = $client_name = '-';
    $client_name = $get_shipping_details_row['client_name'] ?? '-';
    $get_car_sql = "SELECT * FROM tbl_car WHERE car_id=(SELECT car_id FROM tbl_register WHERE register_id='" . $get_shipping_details_row['register_id'] . "')";
    $get_car_rs = mysqli_query($conn, $get_car_sql);
    if ($car_row = mysqli_fetch_assoc($get_car_rs)) $car_no = $car_row['car_number'];
    $get_helper_sql = "SELECT * FROM tbl_helper WHERE helper_id=(SELECT helper_id FROM tbl_register WHERE register_id='" . $get_shipping_details_row['register_id'] . "')";
    $get_helper_rs = mysqli_query($conn, $get_helper_sql);
    if ($helper_row = mysqli_fetch_assoc($get_helper_rs)) {
        $delivery_agent = $helper_row['helper_name'];
        $contact_number = $helper_row['helper_number'];
    }
    $pickup_date = !empty($get_shipping_details_row['pickup_dates']) ? date('d M Y', strtotime($get_shipping_details_row['pickup_dates'])) : '-';
    $last_done_idx = -1;
    foreach ($timeline as $i => $step) { if ($step['time']) $last_done_idx = $i; }
} else {
    $timeline = [];
    $car_no = $delivery_agent = $contact_number = $client_name = $pickup_date = '-';
    $last_done_idx = -1;
}

// Output main HTML container only on non-AJAX
if (!$is_ajax) { ?>
<section style="background: #f5f8fd; min-height:80vh; padding:42px 0;">
  <div class="container">
    <div class="row justify-content-center">
      <!-- Search Bar -->
      <div class="col-lg-11 mb-4">
        <form class="track-searchbar" id="trackingSearchForm" autocomplete="off" style="margin-bottom:36px;">
            <input type="text" name="doc_no" class="form-control" placeholder="Enter Tracking ID (Doc No.)" value="<?= htmlspecialchars($doc_no) ?>" required style="max-width:340px; display:inline-block;">
            <button type="submit" class="btn btn-primary">Track</button>
        </form>
      </div>
      <div class="col-lg-12" id="trackingResultBox">
<?php } // End if !ajax ?>

        <div class="row justify-content-center">
          <!-- Timeline/Left Panel -->
          <div class="col-lg-7 col-md-8 mb-4">
            <div class="track-box">
              <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <h2 class="track-title mb-0">Arriving By</h2>
                    <div style="margin-top:22px;" class="track-date"><?= $pickup_date ?></div>
                </div>
                <img src="<?= SITE_URL ?>assets/images/delhivery_logo.png" alt="Delhivery" style="height:36px;margin-top:6px;">
              </div>
              <div class="track-timeline">
                <?php foreach ($timeline as $i => $step):
                    $done = $step['time'];
                    $is_last_done = ($i == $last_done_idx && $done);
                ?>
                  <div class="track-step <?= $done ? 'done' : '' ?> <?= $is_last_done ? 'active' : '' ?>">
                    <div class="track-circle">
                        <?php if ($done): ?>
                            <span class="checkmark">&#10003;</span>
                        <?php else: ?>
                            <?= $i+1 ?>
                        <?php endif; ?>
                    </div>
                    <div class="track-info">
                        <div class="track-label"><?= $step['label'] ?></div>
                        <?php if (!empty($step['desc']) && $done): ?>
                          <div class="track-desc"><?= htmlspecialchars($step['desc']) ?></div>
                        <?php endif; ?>
                        <?php if ($done): ?>
                          <div class="track-time"><?= $step['time'] ?></div>
                        <?php endif; ?>
                        <?php if ($is_last_done && $done): ?>
                            <div>
                              <a href="#" class="track-see-all" id="seeAllUpdatesBtn">See all updates</a>
                            </div>
                        <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
                <!-- Animated fill line (JS will adjust the height) -->
                <div class="track-timeline-fill"></div>
              </div>
            </div>
          </div>
          <!-- Shipment Details/Right Panel -->
          <div class="col-lg-5 col-md-7">
            <div class="shipment-card">
              <div class="shipment-title"><i class="fa fa-archive"></i> Shipment Details</div>
              <table class="table table-borderless mb-0">
                <tr><td>Shipment ID</td><td><?= $get_shipping_details_row['doc'] ?? '-' ?></td></tr>
                <tr><td>Order Number</td><td>-</td></tr>
                <tr><td>Order Date</td><td><?= $pickup_date ?></td></tr>
                <tr><td>Order Items</td><td><?= htmlspecialchars($client_name) ?></td></tr>
                <tr><td>Delivery Agent</td><td><?= htmlspecialchars($delivery_agent) ?></td></tr>
                <tr><td>Contact Number</td><td><?= htmlspecialchars($contact_number) ?></td></tr>
                <tr><td>Car No.</td><td><?= htmlspecialchars($car_no) ?></td></tr>
                <tr><td>Mode of Payment</td><td>-</td></tr>
                <tr><td>Order Value</td><td>-</td></tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Modal for All Updates -->
        <div id="allUpdatesModal" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px;">
            <div class="modal-content">
              <div class="modal-header" style="position:relative; border-bottom:none;">
                <h5 class="modal-title">All Updates</h5>
                <button type="button" class="close-modal" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="modal-timeline">
                  <?php foreach ($timeline as $i => $step): if ($step['time']) { ?>
                    <div class="modal-step">
                      <div class="modal-step-label"><?= $step['label'] ?></div>
                      <?php if (!empty($step['desc'])): ?>
                        <div class="modal-step-desc"><?= htmlspecialchars($step['desc']) ?></div>
                      <?php endif; ?>
                      <div class="modal-step-time"><?= $step['time'] ?></div>
                    </div>
                  <?php } endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
<?php
// End main container only on non-AJAX
if (!$is_ajax) { ?>
      </div><!-- /trackingResultBox -->
    </div>
  </div>
</section>
<?php include("include/footer.php"); } ?>
<style>
body { font-family: 'Inter', 'Segoe UI', Arial, sans-serif; background:#f5f8fd;}
.track-box {
    background: #fff;
    border-radius: 20px;
    padding: 32px 36px;
    min-width: 320px;
    box-shadow: 0 2px 12px #bbb2ff10;
}
.track-title {
    color: #5551c0;
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 0px;
    letter-spacing: -.5px;
}
.track-date {
    font-size: 1.13rem;
    color: #3a4256;
    margin-bottom: 22px;
    margin-top: 8px;
    font-weight: 500;
}
.track-timeline {
    margin-top: 24px;
    margin-left: 12px;
    position: relative;
    padding-left: 32px;
}
.track-timeline-fill {
    position: absolute;
    left: 4px;
    top: 18px;
    width: 3px;
    background: #5551c0;
    border-radius: 2px;
    z-index: 0;
    transition: height 0.5s cubic-bezier(.42,0,.58,1);
    height: 0;
}
.track-timeline:before {
    content: '';
    position: absolute;
    left: 4px;
    top: 18px;
    bottom: 10px;
    width: 3px;
    background: #e3e6fc;
    z-index: 0;
    border-radius: 2px;
}
.track-step {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 36px;
    z-index: 1;
}
.track-step:last-child { margin-bottom: 0; }
.track-circle {
    width: 32px; height: 32px; border-radius: 50%;
    border: 2.5px solid #d7d7e4;
    color: #5551c0;
    background: #fff;
    font-weight: 600;
    text-align: center;
    line-height: 29px;
    font-size: 1.22rem;
    margin-top: 2px;
    z-index: 2;
    transition: background .2s, color .2s, border .2s;
    box-shadow: 0 0 0 4px #fff;
    display: flex;
    align-items: center;
    justify-content: center;
}
.track-step.done .track-circle {
    background: #5551c0;
    color: #fff;
    border-color: #5551c0;
}
.track-step.active .track-circle {
    box-shadow: 0 0 0 4px #e4e6fb, 0 0 0 8px #f5f8fd;
    background: #fff;
    color: #5551c0;
    border-color: #5551c0;
    font-weight: 900;
}
.checkmark {
    font-size: 1.4rem;
    line-height: 1;
    margin-top: -2px;
}
.track-step .track-info {
    padding-top: 1px;
}
.track-label {
    font-weight: 700;
    color: #232351;
    font-size: 1.09rem;
    margin-bottom: 1px;
}
.track-step.done .track-label,
.track-step.active .track-label {
    color: #5551c0;
}
.track-desc {
    font-size: .97rem;
    color: #8a91ab;
    margin-bottom: 1px;
}
.track-time {
    font-size: .94rem;
    color: #7c7d8a;
    margin-bottom: 1px;
}
.track-see-all {
    color: #5551c0;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: underline;
    cursor: pointer;
}
.track-see-all:hover { color: #2a248b; }
.shipment-card {
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 2px 12px #bbb2ff22;
    padding: 18px 16px 22px 16px;
}
.shipment-title {
    font-weight: 700;
    color: #232351;
    font-size: 1.15rem;
    margin-bottom: 16px;
    letter-spacing: -.3px;
    display: flex;
    align-items: center;
    gap: 7px;
}
.shipment-card table {
    width: 100%;
}
.shipment-card td {
    padding: 8px 0;
    border-bottom: 1px solid #f2f2f2;
    color: #303055;
    font-size: 1.04rem;
    vertical-align: middle;
}
.shipment-card td:first-child {
    font-weight: 600;
    color: #6f6bb1;
    width: 45%;
}
.shipment-card td:last-child {
    text-align: right;
    color: #232351;
}
.modal-timeline {
    margin: 0 4px;
}
.modal-step {
    display: flex;
    flex-direction: column;
    margin-bottom: 20px;
    border-left: 3px solid #e3e6fc;
    padding-left: 14px;
    position: relative;
}
.modal-step:before {
    content: "";
    position: absolute;
    left: -7px;
    top: 6px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #5551c0;
}
.modal-step-label { font-weight: 700; color: #232351; }
.modal-step-desc { font-size: .98rem; color: #8a91ab; }
.modal-step-time { font-size: .95rem; color: #7c7d8a; }
@media (max-width: 900px) {
    .track-box, .shipment-card { min-width: unset; }
    .track-title { font-size: 1.4rem;}
    .track-date { font-size: 1rem;}
}
.track-searchbar {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0;
}
.track-searchbar input[type="text"] {
    border-radius: 8px;
    border: 1.3px solid #e3e6fc;
    font-size: 1.1rem;
    padding: 10px 14px;
    background: #fff;
    margin-right: 5px;
}
.track-searchbar .btn {
    background: #5551c0;
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 8px 22px;
    font-size: 1.09rem;
    font-weight: 600;
}
.track-searchbar .btn:hover {
    background: #2a248b;
    color: #fff;
}

/* Modal Close Button */
.close-modal {
    position: absolute;
    right: 18px;
    top: 15px;
    background: transparent;
    border: none;
    font-size: 2rem;
    color: #646493;
    cursor: pointer;
    line-height: 1;
    opacity: .7;
    z-index: 2;
    transition: color 0.2s;
}
.close-modal:hover {
    color: #232351;
    opacity: 1;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate the blue fill line to the last completed step
    function animateFillLine() {
        var timeline = document.querySelector('.track-timeline');
        var fillLine = document.querySelector('.track-timeline-fill');
        if (!timeline || !fillLine) return;
        var steps = timeline.querySelectorAll('.track-step.done .track-circle');
        if (steps.length > 0) {
            var first = steps[0].getBoundingClientRect().top + (steps[0].offsetHeight/2);
            var last = steps[steps.length-1].getBoundingClientRect().top + (steps[0].offsetHeight/2);
            var parentTop = timeline.getBoundingClientRect().top;
            fillLine.style.height = (last - parentTop - 18) + 'px';
        }
    }
    setTimeout(animateFillLine, 250);
    window.addEventListener('resize', animateFillLine);

    // AJAX tracking search
    var searchForm = document.getElementById('trackingSearchForm');
    if (searchForm) {
        searchForm.onsubmit = function(e){
            e.preventDefault();
            var doc_no = this.doc_no.value.trim();
            if(!doc_no) return false;
            var formData = new FormData();
            formData.append('ajax', '1');
            formData.append('doc_no', doc_no);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'deliveryHistory.php', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onreadystatechange = function(){
                if(xhr.readyState === 4 && xhr.status === 200){
                    document.getElementById('trackingResultBox').innerHTML = xhr.responseText;
                    if (history.pushState) {
                        var url = 'deliveryHistory.php?doc_no=' + encodeURIComponent(doc_no);
                        window.history.pushState({doc_no:doc_no}, '', url);
                    }
                    setTimeout(animateFillLine, 250);
                }
            };
            xhr.send(formData);
        };
    }

    // Modal popup for "See all updates"
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'seeAllUpdatesBtn') {
            e.preventDefault();
            $('#allUpdatesModal').modal('show');
        }
    });
});
</script>
