<?php
session_start();
require '../config/dbcon.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $con->prepare("UPDATE users SET status='Active' WHERE id=? AND role='employee'");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Employee reactivated successfully!";
    } else {
        $_SESSION['error'] = "Employee not found or already active.";
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request!";
}

header("Location: ../employees.php");
exit();
?>
