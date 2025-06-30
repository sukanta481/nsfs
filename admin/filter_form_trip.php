<!-- filter_form_trip.php -->
<form action="" method="get" id="forms" style="float: right;">
    From: <input type="date" name="fromdate" id="fromdate" value="<?= htmlspecialchars($_REQUEST['fromdate'] ?? '') ?>">
    To: <input type="date" name="todate" id="todate" value="<?= htmlspecialchars($_REQUEST['todate'] ?? '') ?>">

    <select name="doc_type" id="doc_type">
        <option value="">All DRS Type</option>
        <option value="DRS" <?= (($_REQUEST['doc_type'] ?? '') == 'DRS') ? 'selected' : '' ?>>DRS</option>
        <option value="NON-DRS" <?= (($_REQUEST['doc_type'] ?? '') == 'NON-DRS') ? 'selected' : '' ?>>NON-DRS</option>
    </select>

    <select id="status" name="status">
        <option value="">All Status</option>
        <option value="Processing" <?= (($_REQUEST['status'] ?? '') == 'Processing') ? 'selected' : '' ?>>Processing</option>
        <option value="Delivered" <?= (($_REQUEST['status'] ?? '') == 'Delivered') ? 'selected' : '' ?>>Delivered</option>
        <option value="Picked Up" <?= (($_REQUEST['status'] ?? '') == 'Picked Up') ? 'selected' : '' ?>>Picked Up</option>
        <option value="Delayed" <?= (($_REQUEST['status'] ?? '') == 'Delayed') ? 'selected' : '' ?>>Delayed</option>
        <option value="In Transit" <?= (($_REQUEST['status'] ?? '') == 'In Transit') ? 'selected' : '' ?>>In Transit</option>
        <option value="Out for Delivery" <?= (($_REQUEST['status'] ?? '') == 'Out for Delivery') ? 'selected' : '' ?>>Out for Delivery</option>
    </select>

    <select id="type" name="type">
        <option value="">Select Type</option>
        <option value="doc" <?= (($_REQUEST['type'] ?? '') == 'doc') ? 'selected' : '' ?>>Doc No</option>
        <option value="box" <?= (($_REQUEST['type'] ?? '') == 'box') ? 'selected' : '' ?>>Box/Unit</option>
    </select>
    <input type="text" id="typedata" name="typedata" placeholder="Enter value" value="<?= htmlspecialchars($_REQUEST['typedata'] ?? '') ?>" style="width:110px;">

    <input type="text" name="consignor" placeholder="Consignor Company" value="<?= htmlspecialchars($_REQUEST['consignor'] ?? '') ?>" style="width:140px;">
    <input type="text" name="consignee" placeholder="Consignee Name" value="<?= htmlspecialchars($_REQUEST['consignee'] ?? '') ?>" style="width:140px;">

    <input type="submit" name="submit_filters" value="Search" class="btn btn-success btn-submit">
    <button id="resetBtn" class="btn btn-success">Reset Form</button>
</form>
