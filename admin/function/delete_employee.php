<?php
session_start();
require '../config/dbcon.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Check if employee exists and is active
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
        $_SESSION['error'] = "Employee is already inactive!";
        header("Location: ../employees.php");
        exit(0);
    }

    // Soft delete: set status to 'Inactive'
    $delete_sql = "UPDATE users SET status='Inactive' WHERE id=? AND role='employee'";
    $stmt = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Employee has been deactivated successfully!";
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
