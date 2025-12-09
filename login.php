<?php
    session_start();
    
    if(isset($_SESSION['auth']))
    {   
        if(!isset($_SESSION['message'])){
                    
            $_SESSION['message'] = "You are already logged In";
        }
        header("Location: /login.php");
        exit(0);
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monings Rental</title>

    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    
    <link rel="stylesheet" href="css/custom.css">
    <style>
        div.login-form{
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%,-50%);
            width: 400px;
        }

         div.login-form {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
        }

        #loader {
            display: none;
            text-align: center;
        }

        #loader img {
            width: 80px;
        }
    </style>
</head>
<body class="bg-light">

        
        <!-- Full-screen Loading GIF -->
        <div id="loader" style="
            position: fixed;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;">
            <img src="assets/img/loader.gif" alt="Loading..." style="width: 80px;">
        </div>

    <div class="login-form text-center rounded bg-white shadow overflow-hidden">

         <?php 
         include('admin/message.php');
         ?>

        <form action="logincode.php" method="post" id="loginForm" >
            <h4 class="bg-dark text-white py-3">LOGIN</h4>
            <div class="p-4">
                <div class="mb-3">
                    <input name="email" required type="text" class="form-control shadow-none text-center" placeholder="Email">
                </div>
                <div class="mb-4">
                    <input name="password" required type="password" class="form-control shadow-none text-center" placeholder="Password">
                </div>
                <button type="submit" name="login_btn"  class="btn text-white custom-bg shadow-none">LOGIN</button>
            </div>
        </form>
    </div>

   
<script>
    document.querySelector('form').addEventListener('submit', function () {
        document.getElementById('loader').style.display = 'flex';
    });
</script>


</body>
</html>

