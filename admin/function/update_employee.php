<?php
session_start();
require '../config/dbcon.php';

if (isset($_POST['update_employee'])) {
    $id          = intval($_POST['id']);
    $first_name  = trim($_POST['first_name']);
    $last_name   = trim($_POST['last_name']);
    $middle_name = trim($_POST['middle_name']);
    $contact     = trim($_POST['contact']);
    $email       = trim($_POST['email']);
    $address     = trim($_POST['address']);
    $branch_id   = intval($_POST['branch_id']);

    // Basic required fields
    if ($first_name == "" || $last_name == "" || $contact == "" || $email == "" || $address == "" || $branch_id == 0) {
        $_SESSION['error'] = "All required fields must be filled!";
        header("Location: ../employees.php");
        exit(0);
    }

    // Validate names (letters, spaces, period only)
    if (!preg_match("/^[a-zA-Z\s.]+$/", $first_name)) {
        $_SESSION['error'] = "First name should contain only letters, spaces, or period.";
        header("Location: ../employees.php");
        exit(0);
    }
    if (!preg_match("/^[a-zA-Z\s.]+$/", $last_name)) {
        $_SESSION['error'] = "Last name should contain only letters, spaces, or period.";
        header("Location: ../employees.php");
        exit(0);
    }
    if ($middle_name != "" && !preg_match("/^[a-zA-Z\s.]+$/", $middle_name)) {
        $_SESSION['error'] = "Middle name should contain only letters, spaces, or period.";
        header("Location: ../employees.php");
        exit(0);
    }

    // Validate PH contact number
    if (!preg_match("/^(09\d{9}|\+639\d{9})$/", $contact)) {
        $_SESSION['error'] = "Contact number must be valid PH format (09XXXXXXXXX or +639XXXXXXXXX).";
        header("Location: ../employees.php");
        exit(0);
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../employees.php");
        exit(0);
    }

    // Check if employee exists and is Active
    $check_sql = "SELECT status FROM users WHERE id=? AND role='employee' LIMIT 1";
    $stmt = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['error'] = "Employee not found!";
        header("Location: ../employees.php");
        exit(0);
    }

    $row = mysqli_fetch_assoc($result);
    if ($row['status'] != "Active") {
        $_SESSION['error'] = "Inactive employees cannot be updated!";
        header("Location: ../employees.php");
        exit(0);
    }

    // Check for duplicate email/contact (excluding current employee)
    $check_dup = "SELECT id FROM users WHERE (email=? OR contact=?) AND id<>? LIMIT 1";
    $stmt = mysqli_prepare($con, $check_dup);
    mysqli_stmt_bind_param($stmt, "ssi", $email, $contact, $id);
    mysqli_stmt_execute($stmt);
    $dup_result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($dup_result) > 0) {
        $_SESSION['error'] = "Email or contact already exists!";
        header("Location: ../employees.php");
        exit(0);
    }

    // Update employee
    $update_sql = "UPDATE users SET first_name=?, last_name=?, middle_name=?, contact=?, email=?, address=?, branch_id=? WHERE id=? AND role='employee'";
    $stmt = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt, "ssssssii", $first_name, $last_name, $middle_name, $contact, $email, $address, $branch_id, $id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Employee updated successfully!";
    } else {
        $_SESSION['error'] = "Database error: " . mysqli_error($con);
    }

    header("Location: ../employees.php");
    exit(0);
} else {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../employees.php");
    exit(0);
}
