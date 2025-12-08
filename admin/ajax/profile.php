<?php
session_start();  
require('../config/dbcon.php');
require('../config/code.php');


//Check if form is submitted
//This checks whether the password form has been submitted.
//The pass_form key comes from the FormData you sent via AJAX:
if(isset($_POST['pass_form']))
{
    //Filter incoming data
    //filteration() is presumably a custom function to sanitize input (remove HTML tags, escape harmful characters, etc.).
    //Ensures user input (new_pass and confirm_pass) is safe.
    $frm_data = filteration($_POST);

    // Check mismatch. Check if passwords match
    //If the new password and confirm password are not identical, it returns 'mismatch' and stops execution.
    //Prevents updating the database with mismatched passwords.
    if($frm_data['new_pass'] != $frm_data['confirm_pass']){
        echo 'mismatch';
        exit;
    }

    // Encrypt password
    //Uses bcrypt hashing to safely store passwords.
    //Hashing ensures the password is secure and not stored in plain text.
    $enc_pass = password_hash($frm_data['new_pass'], PASSWORD_BCRYPT);

    //Check if user is logged in
    // Get correct session id. Ensures the current session has a logged-in user.
    //Prevents unauthorized users from updating a password.
    //$user_id is used in the WHERE clause of the SQL query.
    if(!isset($_SESSION['auth_user']['user_id'])){
        echo 0;
        exit;
    }

    $renter_id = $_SESSION['auth_user']['user_id'];

    // Update query
    //Uses a prepared statement style with placeholders ? to prevent SQL injection.
    //Updates the password field for the current user only (LIMIT 1 ensures only one row is affected).
    $query = "UPDATE `users` SET `password`=? WHERE `id`=? LIMIT 1";
    $values = [$enc_pass, $renter_id];

    //Execute the update
    // Correct param type. indicates parameter types: 'si' (string, integer)
    if(update($query, $values, 'si')){//'s' → string ($enc_pass) 'i' → integer ($user_id)
        echo 1;
    } else {
        echo 0;
    }
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