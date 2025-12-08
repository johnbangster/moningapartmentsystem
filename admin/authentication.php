
<!--this code is for roles and permission to auth users-->
<?php
session_start();
include('config/dbcon.php');


// Check if user is logged in
if (!isset($_SESSION['auth'])) {
    $_SESSION['message'] = "Please login to access the dashboard.";
    header("Location: ../login.php");
    exit(0);
}

// Allow only 'admin' or 'employee'
$allowed_roles = ['admin', 'employee'];
$user_role = $_SESSION['auth_role'] ?? '';

if (!in_array($user_role, $allowed_roles)) {
    $_SESSION['message'] = "You are not authorized to access this page.";
    header("Location: ../login.php");
    exit(0);
}


// if(!isset($_SESSION['auth']))
// {
//     $_SESSION['message'] = "Login to Acces Dashboard";
//     header("Location: ../login.php");
//     exit(0);
// }
// else
// {
//     if($_SESSION['auth_role'] !="1" && $_SESSION['auth_role'] !="2")
//     {
        
//         $_SESSION['message'] = "You are not Authorised as ADMIN";
//         header("Location: ../login.php");
//         exit(0);
//     }

// }
?>