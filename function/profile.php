<?php
require('../admin/config/dbcon.php');
require('../admin/config/code.php');

date_default_timezone_set("Asia/Manila");

if(isset($_POST['info_form']))
{
    $frm_data = filteration($_POST);
    session_start();

    $exist = select("SELECT * FROM `users` WHERE `contact`=?  AND `id`!=? LIMIT 1",[$frm_data ['contact'], $_SESSION['auth_user']['user_id']], "ss");

    if(mysqli_num_rows($exist) !==0){
        echo 'phone_already';
        exit;
    }

    $query = "UPDATE `users` SET `first_name`=?,`last_name`=?, `middle_name`=?,`contact`=?,`email`=?,`address`=? 
                WHERE `id`=?";
    $values = [$frm_data['first_name'], $frm_data['last_name'], $frm_data['middle_name'],
                $frm_data['contact'], $frm_data['email'], $frm_data['address'], $_SESSION['auth_user']['user_id']];
    
    if(update($query,$values,'sssssss')){
        // $_SESSION['user_name'] = $frm_data['first_name'];
        $_SESSION['auth_user']['user_name'] = $frm_data['first_name'];
        $_SESSION['auth_user']['user_name'] = $frm_data['last_name'];

        echo 1;
    }else{
        echo 0;
    }
}

if(isset($_POST['pass_form']))
{
    $frm_data = filteration($_POST);
    session_start();

     if($frm_data['new_pass']!=$frm_data['confirm_pass']){
        echo 'mismatch';
        exit;
    }

    $enc_pass = password_hash($frm_data['new_pass'],PASSWORD_BCRYPT);

    $query = "UPDATE `users` SET `password`=?
                WHERE `id`=? LIMIT 1";
    $values = [$enc_pass, $_SESSION['auth_user']['user_id']];
    
    if(update($query,$values,'ss')){
        echo 1;
    }else{
        echo 0;
    }
}



?>