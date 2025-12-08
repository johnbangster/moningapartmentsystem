<?php
session_start();
require '../config/dbcon.php';

if (isset($_POST['add_employee'])) {
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
        header("Location: ../add_employee.php");
        exit(0);
    }

    // Validate names (letters, spaces, period only)
    if (!preg_match("/^[a-zA-Z\s.]+$/", $first_name)) {
        $_SESSION['error'] = "First name should contain only letters, spaces, or period.";
        header("Location: ../add_employee.php");
        exit(0);
    }
    if (!preg_match("/^[a-zA-Z\s.]+$/", $last_name)) {
        $_SESSION['error'] = "Last name should contain only letters, spaces, or period.";
        header("Location: ../add_employee.php");
        exit(0);
    }
    if ($middle_name != "" && !preg_match("/^[a-zA-Z\s.]+$/", $middle_name)) {
        $_SESSION['error'] = "Middle name should contain only letters, spaces, or period.";
        header("Location: ../add_employee.php");
        exit(0);
    }

    // Validate PH contact number
    if (!preg_match("/^(09\d{9}|\+639\d{9})$/", $contact)) {
        $_SESSION['error'] = "Contact number must be valid PH format (09XXXXXXXXX or +639XXXXXXXXX).";
        header("Location: ../add_employee.php");
        exit(0);
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../add_employee.php");
        exit(0);
    }

    // Check for duplicate email/contact
    $check_sql = "SELECT id FROM users WHERE email = ? OR contact = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $contact);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Email or contact already exists!";
        header("Location: ../add_employee.php");
        exit(0);
    }

    // Default password
    $password = password_hash("emp123", PASSWORD_DEFAULT);

    // Insert employee
    $insert_sql = "INSERT INTO users (first_name, last_name, middle_name, contact, email, address, password, role, status, created_at, branch_id) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, 'employee', 'Active', NOW(), ?)";
    $stmt = mysqli_prepare($con, $insert_sql);
    mysqli_stmt_bind_param($stmt, "sssssssi", $first_name, $last_name, $middle_name, $contact, $email, $address, $password, $branch_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Employee added successfully!";
        header("Location: ../add_employee.php");
        exit(0);
    } else {
        $_SESSION['error'] = "Database error: " . mysqli_error($con);
        header("Location: ../add_employee.php");
        exit(0);
    }
} else {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../add_employee.php");
    exit(0);
}
