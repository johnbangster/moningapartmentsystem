<?php
session_start();
require('admin/config/dbcon.php'); // Secure DB connection

if (isset($_POST['login_btn'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate empty fields
    if (empty($email) || empty($password)) {
        $_SESSION['message'] = "Email and password are required.";
        header("Location: login.php");
        exit();
    }

    // Prepared statement to prevent SQL Injection
    $stmt = $con->prepare("SELECT * FROM users WHERE email = ? AND status = 'Active' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If user found
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify hashed password using password_verify()
        if (password_verify($password, $user['password'])) {

            // Set session details
            $_SESSION['auth'] = true;
            $_SESSION['auth_role'] = $user['role']; // 'admin', 'employee', or 'renter'
            $_SESSION['auth_user'] = [
                'user_id'    => $user['id'],
                'user_name'  => $user['first_name'] . ' ' . $user['last_name'],
                'user_email' => $user['email']
            ];

            // If logged-in user is a renter, find renter_id from renters table
            if ($user['role'] == 'renter') {
                $renterStmt = $con->prepare("SELECT id FROM renters WHERE user_id = ? LIMIT 1");
                $renterStmt->bind_param("i", $user['id']);
                $renterStmt->execute();
                $renterResult = $renterStmt->get_result();

                if ($renterData = $renterResult->fetch_assoc()) {
                    $_SESSION['auth_user']['renter_id'] = $renterData['id'];
                } else {
                    $_SESSION['message'] = "Renter profile not found. Contact support.";
                    header("Location: login.php");
                    exit();
                }
            }

            // Redirect user based on role
            switch ($user['role']) {
                case 'admin':
                case 'employee':
                    $_SESSION['message'] = "Welcome to the Admin Dashboard!";
                    header("Location: admin/index.php");
                    break;
                case 'renter':
                    $_SESSION['message'] = "Welcome to your Renter Dashboard!";
                    header("Location: renter/index.php");
                    break;
                default:
                    $_SESSION['message'] = "Invalid user role.";
                    header("Location: login.php");
                    break;
            }
            exit();

        } else {
            // Incorrect password
            $_SESSION['message'] = "Invalid email or password.";
            header("Location: login.php");
            exit();
        }
    } else {
        // No user found or inactive
        $_SESSION['message'] = "Invalid email or inactive account.";
        header("Location: login.php");
        exit();
    }
} else {
    $_SESSION['message'] = "Unauthorized access!";
    header("Location: login.php");
    exit();
}
?>
