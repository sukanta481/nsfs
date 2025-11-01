<?php
include('conn.php');
// Receive the pending_pod_mode from trip.php context
$pending_pod_mode = isset($pending_pod_mode) ? $pending_pod_mode : false;
?>

<div class="x_panel">
    <div class="x_title">
        <h2>
          <?php echo $pending_pod_mode ? 'Pending POD Docs' : 'List Docs'; ?>
        </h2>
        <div>
            <?php include('filter_form_trip.php'); ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div id="posts">
        <?php
        // Pass flag via global for AJAX fallback
        $GLOBALS['pending_pod_mode'] = $pending_pod_mode;
        include('list_trip_table.php');
        ?>
    </div>
</div>

<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script>
$(function () {
    $('#forms').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            type: 'get',
            url: 'list_trip_table.php',
            data: $('#forms').serialize() + '&pending_pod_mode=<?= $pending_pod_mode ? 1 : 0 ?>',
            success: function (data) {
                $('#posts').html(data);
            }
        });
    });
    $("#resetBtn").click(function (e) {
        e.preventDefault();
        $("#forms")[0].reset();
        window.location.href = "trip.php?type=list_trip";
    });
});
function delconfirmshipping(shipping_details_id) {
    var c = confirm("Are you sure to delete?");
    if (c == true) {
        var search = window.location.search.replace(/^\?/, '');
        if (search) {
            search = search.replace(/([&\?])msg=deleted/, '').replace(/([&\?])action=delete_shipping_details/, '').replace(/([&\?])shipping_details_id=\d+/, '');
            location.href = 'action_handler.php?action=delete_shipping_details&shipping_details_id=' + shipping_details_id + '&' + search;
        } else {
            location.href = 'action_handler.php?action=delete_shipping_details&shipping_details_id=' + shipping_details_id;
        }
    }
}
</script>
