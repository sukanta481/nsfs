<!-- filter_form_manifest.php -->
<form action="" method="get" id="forms" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
    <input type="hidden" name="type" value="list_manifest">

    <div>
        <label style="font-size: 12px; margin-bottom: 2px; display: block;">From Date:</label>
        <input type="date" name="fromdate" id="fromdate" value="<?= htmlspecialchars($_REQUEST['fromdate'] ?? '') ?>" class="form-control" style="width: 150px;">
    </div>

    <div>
        <label style="font-size: 12px; margin-bottom: 2px; display: block;">To Date:</label>
        <input type="date" name="todate" id="todate" value="<?= htmlspecialchars($_REQUEST['todate'] ?? '') ?>" class="form-control" style="width: 150px;">
    </div>

    <div>
        <label style="font-size: 12px; margin-bottom: 2px; display: block;">Branch Office:</label>
        <select name="office_id" id="office_id" class="form-control" style="width: 180px;">
            <option value="">All Offices</option>
            <?php
            $offices_query = "SELECT office_id, office_name FROM tbl_offices ORDER BY office_name ASC";
            $offices_result = mysqli_query($conn, $offices_query);
            while ($office = mysqli_fetch_assoc($offices_result)):
            ?>
            <option value="<?= $office['office_id'] ?>" <?= (($_REQUEST['office_id'] ?? '') == $office['office_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($office['office_name']) ?>
            </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div>
        <label style="font-size: 12px; margin-bottom: 2px; display: block;">Manifest No:</label>
        <input type="text" name="manifest_no" placeholder="Enter manifest number" value="<?= htmlspecialchars($_REQUEST['manifest_no'] ?? '') ?>" class="form-control" style="width: 180px;">
    </div>

    <div style="margin-top: 18px;">
        <button type="submit" name="submit_filters" class="btn btn-primary">
            <i class="fa fa-search"></i> Search
        </button>
        <button type="button" id="resetBtn" class="btn btn-default">
            <i class="fa fa-refresh"></i> Reset
        </button>
        <button type="button" id="exportExcelBtn" class="btn btn-success">
            <i class="fa fa-file-excel-o"></i> Export Excel
        </button>
    </div>
</form>

<script>
$(function() {
    $('#exportExcelBtn').click(function() {
        var params = $('#forms').serialize();
        window.location.href = 'manifest_export_excel.php?' + params;
    });
});
</script>
