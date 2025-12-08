<?php
// pay_cash.php
require 'config/dbcon.php';
require 'config/code.php';

$data = json_decode(file_get_contents('php://input'), true);
$reservation_id = intval($data['reservation_id'] ?? 0);

if(!$reservation_id){
    echo json_encode(['success'=>false,'message'=>'Invalid reservation ID']);
    exit;
}

// Fetch reservation details
$res_q = mysqli_query($con, "SELECT * FROM reservations WHERE id='$reservation_id' AND payment_method='cash' AND payment_status='pending'");
$res_data = mysqli_fetch_assoc($res_q);

if(!$res_data){
    echo json_encode(['success'=>false,'message'=>'Reservation not found or already paid']);
    exit;
}

// Update reservation as paid
$update = mysqli_query($con, "
    UPDATE reservations 
    SET payment_status='paid', payment_date=NOW() 
    WHERE id='$reservation_id'
");

if($update){
    // Redirect URL to create renter page with reservation info
    $createRenterUrl = "create_renter.php?reservation_id=" . $reservation_id;
    echo json_encode([
        'success'=>true,
        'message'=>'Reservation marked as paid successfully.',
        'redirect'=>$createRenterUrl
    ]);
} else {
    echo json_encode(['success'=>false,'message'=>'Unable to update reservation.']);
}
