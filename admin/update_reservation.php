<?php
require 'admin/config/dbcon.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header('Content-Type: application/json');
ob_start(); // buffer any accidental output

$data = json_decode(file_get_contents('php://input'), true);

// Validation
if(empty($data['reservation_id']) || empty($data['payment_status']) || empty($data['transaction_id'])){
    echo json_encode(['success'=>false, 'message'=>"Missing required data"]);
    exit;
}

$reservation_id = intval($data['reservation_id']);
$payment_status = filter_var($data['payment_status'], FILTER_SANITIZE_STRING);
$payer_name = isset($data['payer_name']) ? filter_var($data['payer_name'], FILTER_SANITIZE_STRING) : '';
$payer_email = isset($data['payer_email']) ? filter_var($data['payer_email'], FILTER_SANITIZE_EMAIL) : '';
$transaction_id = filter_var($data['transaction_id'], FILTER_SANITIZE_STRING);

// Update reservation
$update_q = mysqli_query($con, "UPDATE reservations 
SET status='paid', payer_name='$payer_name', payer_email='$payer_email', transaction_id='$transaction_id' 
WHERE id='$reservation_id'");

if($update_q){
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'message'=>"Failed to update reservation"]);
}
?>
