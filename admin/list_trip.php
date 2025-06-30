<?php
include('conn.php');
?>

<div class="x_panel">
    <div class="x_title">
        <h2>List Docs</h2>
        <div>
            <?php include('filter_form_trip.php'); ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div id="posts">
        <?php include('list_trip_table.php'); ?>
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
            data: $('#forms').serialize(),
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
