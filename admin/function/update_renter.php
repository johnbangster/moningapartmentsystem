<?php 
session_start();
require ('../config/dbcon.php');

if (isset($_POST['update_renter'])) {

    $id = intval($_POST['renter_id']);
    $unit_id = !empty($_POST['unit_id']) ? intval($_POST['unit_id']) : NULL;
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $middle = $_POST['middle_name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $status = $_POST['status'];

    // ✅ Get old unit
    $oldUnitQuery = mysqli_query($con, "SELECT unit_id FROM renters WHERE id = '$id'");
    $oldUnit = mysqli_fetch_assoc($oldUnitQuery)['unit_id'];

    // ✅ If no unit selected → keep old unit
    if (empty($unit_id)) {
        $unit_id = $oldUnit;
    }

    // ✅ Update renter
    $query = "UPDATE renters 
              SET unit_id = ?, first_name = ?, last_name = ?, middle_name = ?, 
                  contacts = ?, email = ?, address = ?, status = ? 
              WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);

    if (!$stmt) {
        $_SESSION['message'] = "SQL Error: " . mysqli_error($con);
        header('Location: ../renter.php');
        exit();
    }

    mysqli_stmt_bind_param($stmt, "isssssssi", 
        $unit_id, $fname, $lname, $middle, $contact, $email, $address, $status, $id);

    mysqli_stmt_execute($stmt);

    // ✅ Update unit availability
    if ($oldUnit != $unit_id) {
        if (!empty($oldUnit)) {
            mysqli_query($con, "UPDATE units SET status = 'available' WHERE id = '$oldUnit'");
        }
        if (!empty($unit_id)) {
            mysqli_query($con, "UPDATE units SET status = 'occupied' WHERE id = '$unit_id'");
        }
    }

    $_SESSION['message'] = "Renter updated successfully!";
    header('Location: ../renter.php');
    exit();
}
?>
