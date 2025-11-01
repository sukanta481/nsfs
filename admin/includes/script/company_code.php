<?php
$companymsg = '';
$message = '';

// Fix: Set $type only if set, otherwise default to ''
$type = isset($_GET['type']) ? $_GET['type'] : '';

ini_set("post_max_size", "10M");
ini_set("upload_max_filesize", "128M");
ini_set("max_input_time", "300");
ini_set("max_execution_time", "300");
ini_set("memory_limit", -1);

// Only process if set and matches
if (isset($_POST['save_company']) && $_POST['save_company'] == "Save") {
    $company_title   = mysqli_real_escape_string($conn, $_POST['company_title'] ?? '');
    $company_address = mysqli_real_escape_string($conn, $_POST['company_address'] ?? '');
    $company_phone   = mysqli_real_escape_string($conn, $_POST['company_phone'] ?? '');
    $company_email   = mysqli_real_escape_string($conn, $_POST['company_email'] ?? '');
    $ser_alias       = alias($company_title);

    // Check for duplicate alias
    $ser_sql = "SELECT * FROM tbl_company WHERE alise='" . $ser_alias . "'";
    $ser_res = mysqli_query($conn, $ser_sql);
    $ser_num = mysqli_num_rows($ser_res);

    if ($ser_num < 1) {
        // Handle image upload if needed
        if (!empty($_FILES['company_image']['name'])) {
            $image_name = time() . $_FILES['company_image']['name'];
            $temp_name = $_FILES['company_image']['tmp_name'];
            $dir = "post_img/";
            $uploadimage = $dir . $image_name;
            $upload = move_uploaded_file($temp_name, $uploadimage);
        } else {
            $image_name = 'noimage.jpg';
        }

        $add_company_sql = "INSERT INTO tbl_company SET
            company_title='$company_title',
            company_address='$company_address',
            company_phone='$company_phone',
            company_email='$company_email',
            alise='$ser_alias',
            company_image='$image_name'";
        $add_company_exe = mysqli_query($conn, $add_company_sql) or die(mysqli_error($conn));
        if ($add_company_exe) {
            $companymsg .= showMessage("Company has been added successfully", 'success');
            echo "<script>alert('Company added successfully!');window.location='company.php?type=list_company';</script>";
            exit;
        }
    } else {
        $companymsg .= showMessage('There is an item with same name.', 'error');
    }
}

// ...rest of your edit/delete code as before
?>
