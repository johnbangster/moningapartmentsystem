<?php

 if($_SESSION['auth_role'] !="admin")
    {
        
        $_SESSION['message'] = "You are not Authorised as ADMIN for this page";
        header("Location: index.php");
        exit(0);
    }

?>