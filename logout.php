<?php
session_start();

    if(isset($_POST['logout']))
    {

        //session destroy
        unset($_SESSION['auth']);
        unset($_SESSION['auth_role']);
        unset($_SESSION['auth_user']);

        $_SESSION['message'] = "Logged out successfully";
        header("Location: /login.php");
        exit(0);

    }
?>