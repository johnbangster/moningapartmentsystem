<?php
session_start();
require('config/dbcon.php');

if (isset($_POST['update_bill'])) {
    $bill_id = mysqli_real_escape_string($con, $_POST['bill_id']);
    $total_amount = mysqli_real_escape_string($con, $_POST['total_amount']);
    $status = mysqli_real_escape_string($con, $_POST['status']);

    $update = mysqli_query($con, "
        UPDATE bills 
        SET total_amount='$total_amount', status='$status' 
        WHERE id='$bill_id'
    ");

    if ($update) {
        $_SESSION['message'] = "Bill updated successfully!";
    } else {
        $_SESSION['message'] = "Error updating bill: " . mysqli_error($con);
    }

    header("Location: billing.php");
    exit();
}
