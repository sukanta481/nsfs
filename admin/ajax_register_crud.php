<?php

require 'conn.php';

// --- List All Entries
if ($_GET['action'] == 'list') {
    $q = mysqli_query($conn, "SELECT s.*, c.company_title FROM tbl_shipping_details s LEFT JOIN tbl_company c ON s.company_id=c.company_id ORDER BY s.shipping_details_id DESC");
    while($row = mysqli_fetch_assoc($q)){
        echo "<tr>
        <td>{$row['shipping_details_id']}</td>
        <td>".htmlspecialchars($row['doc_no'])."</td>
        <td>".htmlspecialchars($row['company_title'])."</td>
        <td>".htmlspecialchars($row['client_name'])."</td>
        <td>".htmlspecialchars($row['car_number'])."</td>
        <td>".htmlspecialchars($row['driver_name'])."</td>
        <td>".htmlspecialchars($row['helper_name'])."</td>
        <td>".htmlspecialchars($row['box'])."</td>
        <td>".htmlspecialchars($row['weight'])."</td>
        <td>
          <button class='btn btn-sm btn-info editBtn' data-id='{$row['shipping_details_id']}'>Edit</button>
          <button class='btn btn-sm btn-danger deleteBtn' data-id='{$row['shipping_details_id']}'>Delete</button>
        </td>
        </tr>";
    }
    exit;
}

// --- Fetch One Entry for Edit
if ($_POST['action']=='fetch') {
    $id = intval($_POST['id']);
    $r = mysqli_query($conn, "SELECT * FROM tbl_shipping_details WHERE shipping_details_id=$id");
    $d = mysqli_fetch_assoc($r);
    echo json_encode($d);
    exit;
}

// --- Save (Add or Update)
if ($_POST['action']=='save') {
    $id = intval($_POST['shipping_details_id']);
    $doc_no = $_POST['doc_no'];
    $company_id = $_POST['company_id'];
    $client_name = $_POST['client_name'];
    $box = $_POST['box'];
    $weight = $_POST['weight'];
    $pay_to = $_POST['pay_to'];
    $rented_car = $_POST['rented_car'];

    // car/driver/helper
    if ($rented_car == '1') {
        $car_id = '';
        $car_number = $_POST['car_number'];
        $driver_id = '';
        $driver_name = $_POST['driver_name'];
        $driver_number = $_POST['driver_number_rent'];
        $helper_id = '';
        $helper_name = $_POST['helper_name'];
        $helper_number = $_POST['helper_number'];
    } else {
        $car_id = $_POST['car_id'];
        $car_number = '';
        $driver_id = $_POST['driver_id'];
        $driver_name = '';
        $driver_number = $_POST['driver_number'];
        $helper_id = $_POST['helper_id'];
        $helper_name = '';
        $helper_number = $_POST['helper_number'];
    }

    if ($id) {
        // Update
        $sql = "UPDATE tbl_shipping_details SET
            doc_no='".mysqli_real_escape_string($conn,$doc_no)."',
            company_id='".mysqli_real_escape_string($conn,$company_id)."',
            client_name='".mysqli_real_escape_string($conn,$client_name)."',
            box='".mysqli_real_escape_string($conn,$box)."',
            weight='".mysqli_real_escape_string($conn,$weight)."',
            pay_to='".mysqli_real_escape_string($conn,$pay_to)."',
            rented_car='".mysqli_real_escape_string($conn,$rented_car)."',
            car_id='".mysqli_real_escape_string($conn,$car_id)."',
            car_number='".mysqli_real_escape_string($conn,$car_number)."',
            driver_id='".mysqli_real_escape_string($conn,$driver_id)."',
            driver_name='".mysqli_real_escape_string($conn,$driver_name)."',
            driver_number='".mysqli_real_escape_string($conn,$driver_number)."',
            helper_id='".mysqli_real_escape_string($conn,$helper_id)."',
            helper_name='".mysqli_real_escape_string($conn,$helper_name)."',
            helper_number='".mysqli_real_escape_string($conn,$helper_number)."'
            WHERE shipping_details_id=$id";
    } else {
        // Insert
        $sql = "INSERT INTO tbl_shipping_details SET
            doc_no='".mysqli_real_escape_string($conn,$doc_no)."',
            company_id='".mysqli_real_escape_string($conn,$company_id)."',
            client_name='".mysqli_real_escape_string($conn,$client_name)."',
            box='".mysqli_real_escape_string($conn,$box)."',
            weight='".mysqli_real_escape_string($conn,$weight)."',
            pay_to='".mysqli_real_escape_string($conn,$pay_to)."',
            rented_car='".mysqli_real_escape_string($conn,$rented_car)."',
            car_id='".mysqli_real_escape_string($conn,$car_id)."',
            car_number='".mysqli_real_escape_string($conn,$car_number)."',
            driver_id='".mysqli_real_escape_string($conn,$driver_id)."',
            driver_name='".mysqli_real_escape_string($conn,$driver_name)."',
            driver_number='".mysqli_real_escape_string($conn,$driver_number)."',
            helper_id='".mysqli_real_escape_string($conn,$helper_id)."',
            helper_name='".mysqli_real_escape_string($conn,$helper_name)."',
            helper_number='".mysqli_real_escape_string($conn,$helper_number)."'";
    }

    if (mysqli_query($conn, $sql)) {
        echo 'success';
    } else {
        echo 'fail';
    }
    exit;
}

// --- Delete
if ($_POST['action']=='delete') {
    $id = intval($_POST['id']);
    mysqli_query($conn, "DELETE FROM tbl_shipping_details WHERE shipping_details_id=$id");
    echo 'success'; exit;
}
