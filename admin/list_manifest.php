<?php
include('conn.php');
?>

<div class="x_panel">
    <div class="x_title">
        <h2>Manifest List</h2>
        <div style="position: absolute; right: 20px; top: 15px;">
            <a href="manifest.php?type=create_manifest" class="btn btn-success">
                <i class="fa fa-plus"></i> Create New Manifest
            </a>
        </div>
        <div class="clearfix"></div>
    </div>

    <div style="padding: 15px; background: white;">
        <?php include('filter_form_manifest.php'); ?>
    </div>

    <div id="posts">
        <?php include('list_manifest_table.php'); ?>
    </div>
</div>

<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script>
$(function () {
    $('#forms').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            type: 'get',
            url: 'list_manifest_table.php',
            data: $('#forms').serialize(),
            success: function (data) {
                $('#posts').html(data);
            }
        });
    });

    $("#resetBtn").click(function (e) {
        e.preventDefault();
        $("#forms")[0].reset();
        window.location.href = "manifest.php?type=list_manifest";
    });
});

function delconfirmmanifest(manifest_id) {
    var c = confirm("Are you sure you want to delete this manifest?");
    if (c == true) {
        location.href = 'action_handler.php?action=delete_manifest&manifest_id=' + manifest_id;
    }
}
</script>
