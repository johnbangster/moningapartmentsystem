<?php
session_start();  
// require('../config/dbcon.php');
require('../../admin/config/dbcon.php');


if(isset($_POST['pass_form'])) {

    $new_pass = $_POST['new_pass'] ?? '';
    $confirm_pass = $_POST['confirm_pass'] ?? '';

    // Server-side validation
    if($new_pass !== $confirm_pass){
        echo 'mismatch';
        exit;
    }

    if(strlen($new_pass) < 6){
        echo 'short';
        exit;
    }

    // Check if user is logged in
    if(!isset($_SESSION['auth_user']['user_id'])){
        echo 'no_session';
        exit;
    }

    $user_id = (int)$_SESSION['auth_user']['user_id'];

    // Hash the password
    $enc_pass = password_hash($new_pass, PASSWORD_BCRYPT);

    // Update password using raw mysqli prepared statement
    $stmt = mysqli_prepare($con, "UPDATE users SET password=? WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "si", $enc_pass, $user_id);

    if(mysqli_stmt_execute($stmt)){
        if(mysqli_stmt_affected_rows($stmt) > 0){
            echo 1; // Success
        } else {
            echo 0; // Password not changed (maybe same as old)
        }
    } else {
        echo 0; // Failed to execute
        // echo mysqli_error($con); // Uncomment for debugging
    }

    mysqli_stmt_close($stmt);
}

// if(isset($_POST['pass_form']))
// {
//     $frm_data = filteration($_POST);

//     // Fix mismatch check
//     if($frm_data['new_pass'] != $frm_data['confirm_pass']){
//         echo 'mismatch';
//         exit;
//     }

//     // Encrypt password
//     $enc_pass = password_hash($frm_data['new_pass'], PASSWORD_BCRYPT);

//     // Correct session variable
//     $user_id = $_SESSION['auth_user']['user_id'];

//     $query = "UPDATE `users` SET `password`=? WHERE `id`=? LIMIT 1";
//     $values = [$enc_pass, $user_id];

//     // Correct parameter type: string + integer → si
//     if(update($query, $values, 'si')){
//         echo 1;
//     } else {
//         echo 0;
//     }
// }

// if(isset($_POST['pass_form']))
// {
//     $frm_data = filteration($_POST);
//     session_start();

//     if($frm_data['new_pass']!=$frm_data['confirm_pass']){
//         echo 'mismatch';
//         exit;
//     }

//     $enc_pass = password_hash($frm_data['new_pass'], PASSWORD_BCRYPT);

//     $query = "UPDATE `users` SET `password`=? WHERE `id`=? LIMIT 1";
//     $values =[$enc_pass, $_SESSION['user_id']];

//     if(update($query,$values, 'ss')){
//         echo 1;
//     }
//     else{
//          echo 0;
//     }
// }
?>