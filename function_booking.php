<?php
require 'admin/config/dbcon.php';


// function addNotification($con, $message, $type, $user_id = NULL)
// {
//     $stmt = $con->prepare("
//         INSERT INTO notifications (user_id, message, type, status, is_read)
//         VALUES (?, ?, ?, 'pending', 0)
//     ");
//     $stmt->bind_param("iss", $user_id, $message, $type);
//     $stmt->execute();
// }

function cleanup_expired_cash_reservations($con){
    $now = time();
    $res_q = mysqli_query($con,"SELECT * FROM reservations WHERE payment_method='cash' AND payment_status='pending'");
    while($res = mysqli_fetch_assoc($res_q)){
        $expiry = strtotime($res['payment_date'].' +3 days');
        if($now > $expiry){
            // Update unit to available
            mysqli_query($con,"UPDATE units SET status='Available' WHERE id='{$res['unit_id']}'");
            // Mark reservation as cancelled
            mysqli_query($con,"UPDATE reservations SET payment_status='cancelled' WHERE id='{$res['id']}'");
        }
    }
}


// function filteration($data){
//     global $con;
//     if(is_array($data)){
//         foreach($data as $key=>$value){
//             $data[$key] = mysqli_real_escape_string($con, htmlspecialchars(trim($value)));
//         }
//     }
//     return $data;
// }



