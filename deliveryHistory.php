<?php
include("include/header.php"); // Parent header
$doc_no = mysqli_real_escape_string($conn, trim($_REQUEST['doc_no']));

// Get shipping details for CN/doc_no
$get_shipping_details_sql = "SELECT * FROM tbl_shipping_details WHERE doc='" . $doc_no . "'";
$get_shipping_details_rs = mysqli_query($conn, $get_shipping_details_sql);
$get_shipping_details_row = mysqli_fetch_assoc($get_shipping_details_rs);

if ($get_shipping_details_row) {
    $sid = $get_shipping_details_row['shipping_details_id'];
    // Status history for this shipment (sorted by update)
    $status_sql = "SELECT * FROM tbl_trip_status WHERE ship_id='$sid' ORDER BY updateddate ASC";
    $status_rs = mysqli_query($conn, $status_sql);

    // Build status timeline (match these to your admin flow)
    $timeline = [
        ['label' => 'Shipment Created',   'db_status' => 'Created',      'desc' => '', 'time' => null],
        ['label' => 'Picked Up',          'db_status' => 'Picked Up',    'desc' => $get_shipping_details_row['client_address'], 'time' => null],
        ['label' => 'In Transit',         'db_status' => 'In Transit',   'desc' => '', 'time' => null],
        ['label' => 'Out for Delivery',   'db_status' => 'Out for Delivery', 'desc' => '', 'time' => null],
        ['label' => 'Delivered',          'db_status' => 'Delivered',    'desc' => '', 'time' => null],
    ];

    // Map statuses to timeline steps
    $history = [];
    while ($row = mysqli_fetch_assoc($status_rs)) {
        $history[] = $row;
    }
    foreach ($timeline as $k => $step) {
        foreach ($history as $h) {
            if (strcasecmp($h['status'], $step['db_status']) == 0) {
                $timeline[$k]['time'] = date('d M Y, h:i A', strtotime($h['updateddate']));
            }
        }
    }

    // Fetch car/helper details for right panel
    $car_no = '-';
    $delivery_agent = '-';
    $contact_number = '-';
    $client_name = $get_shipping_details_row['client_name'] ?? '-';

    $get_car_sql = "SELECT * FROM tbl_car WHERE car_id=(SELECT car_id FROM tbl_register WHERE register_id='" . $get_shipping_details_row['register_id'] . "')";
    $get_car_rs = mysqli_query($conn, $get_car_sql);
    if ($car_row = mysqli_fetch_assoc($get_car_rs)) {
        $car_no = $car_row['car_number'];
    }
    $get_helper_sql = "SELECT * FROM tbl_helper WHERE helper_id=(SELECT helper_id FROM tbl_register WHERE register_id='" . $get_shipping_details_row['register_id'] . "')";
    $get_helper_rs = mysqli_query($conn, $get_helper_sql);
    if ($helper_row = mysqli_fetch_assoc($get_helper_rs)) {
        $delivery_agent = $helper_row['helper_name'];
        $contact_number = $helper_row['helper_number'];
    }
    $pickup_date = !empty($get_shipping_details_row['pickup_dates']) ? date('d M Y', strtotime($get_shipping_details_row['pickup_dates'])) : '-';
} else {
    $timeline = [];
    $car_no = $delivery_agent = $contact_number = $client_name = $pickup_date = '-';
}
?>
<section style="background: #f5f8fd; min-height:80vh; padding:42px 0;">
  <div class="container">
    <div class="row justify-content-center">
      <!-- Tracking Steps -->
      <div class="col-lg-6 col-md-8 mb-4">
        <div class="track-box">
          <h2 class="track-title mb-0">Arriving By</h2>
          <div class="track-date"><?= $pickup_date ?></div>
            <div class="track-timeline">
            <?php foreach ($timeline as $i => $step): ?>
                <div class="track-step <?= $step['time'] ? 'done' : '' ?> <?= ($step['time'] && (!isset($timeline[$i+1]['time']) || !$timeline[$i+1]['time'])) ? 'active' : '' ?>">
                <div class="track-circle"><?= $i+1 ?></div>
                <div class="track-info">
                    <div class="track-label"><?= $step['label'] ?></div>
                    <?php if (!empty($step['desc']) && $step['time']): ?>
                    <div class="track-desc"><?= htmlspecialchars($step['desc']) ?></div>
                    <?php endif; ?>
                    <?php if ($step['time']): ?>
                    <div class="track-time"><?= $step['time'] ?></div>
                    <?php endif; ?>
                </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
      </div>
      <!-- Shipment Details Card -->
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
  </div>
</section>
<?php include("include/footer.php"); ?>

<style>
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
    margin-bottom: 2px;
    letter-spacing: -.5px;
}
.track-date {
    font-size: 1.1rem;
    color: #3a4256;
    margin-bottom: 22px;
}
.track-timeline {
    margin-top: 12px;
    margin-left: 8px;
    position: relative;
    padding-left: 20px;
}
.track-timeline:before {
    content: '';
    position: absolute;
    left: 13px;
    top: 15px;
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
    margin-bottom: 34px;
    z-index: 1;
}
.track-step:last-child { margin-bottom: 0; }
.track-circle {
    width: 28px; height: 28px; border-radius: 50%;
    border: 2.5px solid #d7d7e4;
    color: #5551c0;
    background: #fff;
    font-weight: 600;
    text-align: center;
    line-height: 25px;
    font-size: 1.3rem;
    margin-top: 2px;
    z-index: 2;
    transition: background .2s, color .2s, border .2s;
    box-shadow: 0 0 0 4px #fff;
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
.track-step .track-info {
    padding-top: 1px;
}
.track-label {
    font-weight: 600;
    color: #222;
    font-size: 1.08rem;
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
    font-size: .92rem;
    color: #7c7d8a;
    margin-bottom: 1px;
}
/* Animate the fill line behind the circles (blue up to last done step) */
.track-timeline:after {
    content: '';
    position: absolute;
    left: 13px;
    top: 15px;
    width: 3px;
    background: #5551c0;
    border-radius: 2px;
    z-index: 1;
    transition: height 0.5s cubic-bezier(.42,0,.58,1);
    /* Will set height dynamically with JS */
    height: 0;
}

/* Card styles below are same as before */
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
    padding: 6px 0;
    border-bottom: 1px solid #f2f2f2;
    color: #303055;
    font-size: 1.04rem;
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
@media (max-width: 900px) {
    .track-box, .shipment-card { min-width: unset; }
    .track-title { font-size: 1.4rem;}
    .track-date { font-size: 1rem;}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate the blue fill line to the last completed step
    var timeline = document.querySelector('.track-timeline');
    if (timeline) {
        var steps = timeline.querySelectorAll('.track-step.done');
        if (steps.length > 0) {
            var last = steps[steps.length-1];
            var timelineTop = timeline.getBoundingClientRect().top;
            var lastCircle = last.querySelector('.track-circle');
            var lastTop = lastCircle.getBoundingClientRect().top + (lastCircle.offsetHeight/2);
            var fillLine = timeline.querySelector(':after');
            // But pseudo elements can't be selected directly. So instead:
            var fillLineElem = document.createElement('div');
            fillLineElem.style.position = 'absolute';
            fillLineElem.style.left = '13px';
            fillLineElem.style.top = '15px';
            fillLineElem.style.width = '3px';
            fillLineElem.style.background = '#5551c0';
            fillLineElem.style.borderRadius = '2px';
            fillLineElem.style.zIndex = '1';
            fillLineElem.style.transition = 'height 0.5s cubic-bezier(.42,0,.58,1)';
            // Calculate height from top to last done circle
            var height = (lastTop - timelineTop - 15) + 'px';
            fillLineElem.style.height = height;
            timeline.appendChild(fillLineElem);
        }
    }
});
</script>
