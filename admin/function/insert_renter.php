<?php
session_start();
header('Content-Type: application/json');
require('../config/dbcon.php');

if (isset($_POST['add_renter'])) {
    $fname         = trim($_POST['fname']);
    $lname         = trim($_POST['lname']);
    $middle_name   = trim($_POST['middle_name']);
    $contact       = trim($_POST['contact']);
    $email         = trim($_POST['email']);
    $address       = trim($_POST['address']);
    $unit_id       = intval($_POST['units']);
    $move_in_date  = trim($_POST['move_in_date']);
    $lease_term    = intval($_POST['lease_term']);
    $defaultPassword = "renter123";
    $passwordHash  = password_hash($defaultPassword, PASSWORD_DEFAULT);
    $status        = "Active";
    $role          = "renter";
    $finalImage    = ''; // Placeholder for future image upload

    $errors = [];

    // Validation
    if (!preg_match("/^[a-zA-Z ]+$/", $fname)) $errors[] = "First name must contain only letters.";
    if (!preg_match("/^[a-zA-Z ]+$/", $lname)) $errors[] = "Last name must contain only letters.";
    if (!preg_match("/^[a-zA-Z. ]*$/", $middle_name)) $errors[] = "Middle name must contain only letters or periods.";
    if (!preg_match("/^[0-9]{11}$/", $contact)) $errors[] = "Contact number must be exactly 11 digits.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (!in_array($lease_term, [6, 12])) $errors[] = "Lease term must be 6 or 12 months.";
    if (!strtotime($move_in_date)) $errors[] = "Invalid move-in date.";
    if (empty($address)) $errors[] = "Address is required.";

    if (!empty($errors)) {
        echo json_encode([
            "status" => "error",
            "message" => "error: "  . implode("<br>• ", $errors)
        ]);
        // $_SESSION['error'] = "❌ " . implode("<br>• ", $errors);
        // header("Location: ../renter.php");
        exit();
    }

    // Check for duplicate contact
    $check_user_sql = "SELECT id FROM users WHERE contact = ?";
    $stmt = mysqli_prepare($con, $check_user_sql);
    mysqli_stmt_bind_param($stmt, "s", $contact);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Contact number already registered. " . mysqli_error($con)
        ]);
        // $_SESSION['error'] = "❌ Contact number already registered.";
        // header("Location: ../renter.php");
        exit();
    }
    mysqli_stmt_close($stmt);

    // Insert into users
    $user_sql = "INSERT INTO users (first_name, last_name, middle_name, contact, email, address, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $user_sql);
    mysqli_stmt_bind_param($stmt, "sssssssss", $fname, $lname, $middle_name, $contact, $email, $address, $passwordHash, $role, $status);

    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to create user: " . mysqli_error($con)
        ]);
        // $_SESSION['error'] = "Failed to create user: " . mysqli_stmt_error($stmt);
        // header("Location: ../renter.php");
        exit();
    }

    $user_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    // Insert into renters (no password column here)
    $renter_sql = "INSERT INTO renters (user_id, first_name, last_name, middle_name, contacts, email, address, status, move_in_date, lease_term, unit_id, image) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $renter_sql);
    mysqli_stmt_bind_param($stmt, "isssssssssis", 
        $user_id, $fname, $lname, $middle_name, $contact, $email, $address, $status, $move_in_date, $lease_term, $unit_id, $finalImage);

    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to add renter: " . mysqli_error($con)
        ]);
        // $_SESSION['error'] = "Failed to add renter: " . mysqli_stmt_error($stmt);
        // header("Location: ../renter.php");
        exit();
    }

    $renter_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    // Mark unit as occupied
    $update_unit_sql = "UPDATE units SET status = 'occupied' WHERE id = ?";
    $stmt = mysqli_prepare($con, $update_unit_sql);
    mysqli_stmt_bind_param($stmt, "i", $unit_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Insert family members
    if (!empty($_POST['member_name']) && !empty($_POST['member_type_id'])) {
        $member_names = $_POST['member_name'];
        $member_types = $_POST['member_type_id'];

        for ($i = 0; $i < count($member_names); $i++) {
            $member_name = trim($member_names[$i]);
            $member_type_id = intval($member_types[$i]);

            if (!preg_match("/^[A-Za-z ]+$/", $member_name)) continue;

            $member_sql = "INSERT INTO renter_members (renter_id, member_name, member_type_id) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($con, $member_sql);
            mysqli_stmt_bind_param($stmt, "isi", $renter_id, $member_name, $member_type_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
  
     echo json_encode([
        "status" => "success",
        "message" => "Renter added successfully. Default password: renter123"
    ]);
    // $_SESSION['success'] = "Renter added successfully. Default password: renter123";
    // header("Location: ../renter.php");
    // header("Location: ../agreement.php?renter_id=$renter_id&unit_id=$unit_id");
    exit();
}
?>
